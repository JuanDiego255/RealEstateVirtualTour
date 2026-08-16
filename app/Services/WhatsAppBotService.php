<?php

namespace App\Services;

use App\Models\CompanyAiSetting;
use App\Models\CompanyWhatsappBot;
use App\Models\WhatsappBotSetting;
use App\Models\WhatsappChat;
use App\Models\WhatsappConversation;
use App\Models\WhatsappBotUsage;
use App\Models\VehicleQuote;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * El cerebro del bot: arma el prompt, expone las herramientas del catálogo,
 * corre el bucle de tool-use contra Anthropic y envía la respuesta. Regla de
 * oro: solo afirma lo que devuelven las herramientas o el prompt.
 */
class WhatsAppBotService
{
    const CHAT_MODEL     = 'claude-haiku-4-5'; // conversar: volumen alto, costo bajo
    const MAX_TOOL_LOOPS = 3;
    const HISTORY_LIMIT  = 10;
    const MAX_TOKENS     = 700;
    const ANTHROPIC_URL  = 'https://api.anthropic.com/v1/messages';

    /**
     * Genera y envía la respuesta del bot para el último mensaje del chat.
     */
    public function respond(CompanyWhatsappBot $bot, WhatsappChat $chat): void
    {
        $ai = CompanyAiSetting::where('company_id', $bot->company_id)->first();
        if (!$ai || !$ai->enabled || !$ai->api_key) {
            Log::channel('whatsapp')->warning('IA no configurada; sin respuesta', ['company_id' => $bot->company_id]);
            return;
        }

        // Fusible: cuota o tope de gasto agotado.
        if (WhatsappBotUsage::isBlocked($bot)) {
            Log::channel('whatsapp')->warning('Bot bloqueado por cuota/tope', ['company_id' => $bot->company_id]);
            return;
        }

        $usage    = WhatsappBotUsage::touchWindow($bot->company_id, $chat->phone);
        $settings = WhatsappBotSetting::firstOrNew(['company_id' => $bot->company_id]);
        $search   = new VehicleSearchService($bot->company_id);

        $system   = $this->buildSystemPrompt($bot, $settings);
        $messages = $this->buildHistory($bot->company_id, $chat->phone);
        $tools    = $this->buildTools($bot);

        $handoffReason = null;
        $imagesToSend  = [];
        $finalText     = '';

        for ($loop = 0; $loop < self::MAX_TOOL_LOOPS; $loop++) {
            $resp = $this->callAnthropic($ai->api_key, $system, $messages, $tools);
            if ($resp === null) {
                break;
            }

            // Registrar consumo
            if ($usage) {
                $in  = (int) data_get($resp, 'usage.input_tokens', 0);
                $out = (int) data_get($resp, 'usage.output_tokens', 0);
                $usage->addAnthropicCost(CompanyAiSetting::costFor(self::CHAT_MODEL, $in, $out), $in, $out);
            }

            $content   = data_get($resp, 'content', []);
            $toolUses  = array_values(array_filter($content, fn($b) => ($b['type'] ?? '') === 'tool_use'));
            $textParts = array_filter($content, fn($b) => ($b['type'] ?? '') === 'text');
            $finalText = trim(implode("\n", array_map(fn($b) => $b['text'] ?? '', $textParts)));

            if (empty($toolUses)) {
                break; // el modelo devolvió texto: terminamos
            }

            // Echo del turno del asistente (obligatorio para continuar el tool loop)
            $messages[] = ['role' => 'assistant', 'content' => $content];

            $toolResults = [];
            foreach ($toolUses as $tu) {
                $result = $this->executeTool($tu['name'] ?? '', $tu['input'] ?? [], $search, $bot, $chat, $handoffReason, $imagesToSend);
                $toolResults[] = [
                    'type'        => 'tool_result',
                    'tool_use_id' => $tu['id'] ?? '',
                    'content'     => json_encode($result, JSON_UNESCAPED_UNICODE),
                ];
            }
            $messages[] = ['role' => 'user', 'content' => $toolResults];

            if ($loop === self::MAX_TOOL_LOOPS - 1 && $finalText === '') {
                Log::channel('whatsapp')->warning('Se agotaron las vueltas de tools sin texto', [
                    'company_id' => $bot->company_id, 'phone' => $chat->phone,
                ]);
            }
        }

        // Red de seguridad: promesa sin herramienta → forzar relevo.
        if ($finalText !== '' && HandoffPolicy::botPromiseWithoutTool($finalText) && !$handoffReason) {
            $handoffReason = 'El bot prometió una acción (apartar/crédito/cita) sin ejecutar la herramienta.';
        }

        if ($finalText === '') {
            $finalText = 'Disculpá, no pude procesar eso ahora. En un momento te atiende una persona del equipo.';
            $handoffReason = $handoffReason ?: 'Respuesta vacía del modelo.';
        }

        $this->sendReply($bot, $chat, $finalText, $imagesToSend);

        if ($handoffReason) {
            $chat->pauseBot($handoffReason);
            Log::channel('whatsapp')->info('Handoff', ['company_id' => $bot->company_id, 'phone' => $chat->phone, 'reason' => $handoffReason]);
        }
    }

    /* ── Prompt ── */

    private function buildSystemPrompt(CompanyWhatsappBot $bot, WhatsappBotSetting $settings): string
    {
        $store = $settings->store_name ?: optional($bot->company)->name ?: 'nuestro concesionario';
        $cfg   = $settings->handoffConfig();

        $blocks = [];
        $blocks[] = "Sos el asistente de ventas por WhatsApp de {$store}."
            . ($bot->business_type ? ' Contexto: ' . $bot->business_type . '.' : '')
            . ' Atendés a clientes que escriben interesados en vehículos.';

        if ($settings->training_profile) {
            $blocks[] = "CÓMO HABLA ESTE NEGOCIO:\n" . $settings->training_profile;
        }

        $hard = [
            'REGLAS (no negociables):',
            '- Nunca inventes vehículos, precios ni existencias: mostrá solo lo que devuelvan las herramientas.',
            '- El precio, el kilometraje y el año son datos duros de la herramienta; si no los trae, decí que los confirmás, no los estimes.',
            '- No negociés ni des descuentos: eso lo hace un vendedor.',
            '- Si un vehículo está apartado o vendido, decilo y ofrecé las alternativas que devuelva la herramienta.',
            '- Respondé breve y claro, en el tono del negocio.',
        ];
        if (!$bot->allow_financing_quote) {
            $hard[] = '- Nunca cotices cuotas, plazos ni tasas. Si preguntan por financiamiento, tomá los datos y llamá handoff_to_human.';
        } else {
            $hard[] = '- Para financiamiento podés usar quote_financing con prima y plazo; aclará que la cuota es estimada y está sujeta a aprobación.';
        }
        $blocks[] = implode("\n", $hard);

        $blocks[] = HandoffPolicy::promptSection($cfg);

        if ($settings->custom_rules) {
            $blocks[] = "REGLAS DEL NEGOCIO:\n" . $settings->custom_rules;
        }
        if ($settings->order_instructions) {
            $blocks[] = "CÓMO SE CIERRA UNA COMPRA:\n" . $settings->order_instructions;
        }

        return implode("\n\n", $blocks);
    }

    private function buildTools(CompanyWhatsappBot $bot): array
    {
        $tools = [
            [
                'name'        => 'search_vehicles',
                'description' => 'Busca vehículos en el catálogo con filtros. Devuelve una lista breve.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'query'        => ['type' => 'string', 'description' => 'Texto libre (marca, modelo, etc.)'],
                        'brand'        => ['type' => 'string'],
                        'model'        => ['type' => 'string'],
                        'year_min'     => ['type' => 'integer'],
                        'year_max'     => ['type' => 'integer'],
                        'price_min'    => ['type' => 'number'],
                        'price_max'    => ['type' => 'number'],
                        'transmission' => ['type' => 'string'],
                        'fuel_type'    => ['type' => 'string'],
                        'max_mileage'  => ['type' => 'integer'],
                    ],
                ],
            ],
            [
                'name'        => 'get_vehicle_detail',
                'description' => 'Ficha completa de un vehículo por su id (incluye fotos).',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => ['id' => ['type' => 'integer']],
                    'required' => ['id'],
                ],
            ],
            [
                'name'        => 'check_vehicle_status',
                'description' => 'Estado (disponible/apartado/vendido) de un vehículo y alternativas si no está.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => ['id' => ['type' => 'integer']],
                    'required' => ['id'],
                ],
            ],
            [
                'name'        => 'handoff_to_human',
                'description' => 'Pasa la conversación a una persona del equipo. Usalo según la política de relevo.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => ['reason' => ['type' => 'string', 'description' => 'Motivo y datos recabados.']],
                    'required' => ['reason'],
                ],
            ],
        ];

        if ($bot->allow_financing_quote) {
            $tools[] = [
                'name'        => 'quote_financing',
                'description' => 'Calcula una cuota estimada (sujeta a aprobación) para un vehículo.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'vehicle_id'   => ['type' => 'integer'],
                        'down_payment' => ['type' => 'number', 'description' => 'Prima en la moneda del vehículo'],
                        'term_months'  => ['type' => 'integer'],
                        'interest_rate'=> ['type' => 'number', 'description' => 'Tasa anual %, opcional'],
                    ],
                    'required' => ['vehicle_id', 'down_payment', 'term_months'],
                ],
            ];
        }

        return $tools;
    }

    /* ── Ejecución de herramientas ── */

    private function executeTool(string $name, array $input, VehicleSearchService $search, CompanyWhatsappBot $bot, WhatsappChat $chat, ?string &$handoffReason, array &$imagesToSend)
    {
        switch ($name) {
            case 'search_vehicles':
                return ['results' => $search->search($input, $bot->max_vehicles_per_reply ?: 3)];

            case 'get_vehicle_detail':
                $detail = $search->detail((int) ($input['id'] ?? 0));
                if ($detail && !empty($detail['images'])) {
                    foreach (array_slice($detail['images'], 0, 3) as $img) {
                        $imagesToSend[] = $img;
                    }
                }
                return $detail ?: ['error' => 'No encontré ese vehículo.'];

            case 'check_vehicle_status':
                return $search->status((int) ($input['id'] ?? 0));

            case 'handoff_to_human':
                $handoffReason = trim((string) ($input['reason'] ?? 'El cliente necesita atención humana.'));
                return ['ok' => true, 'message' => 'Un momento, te paso con una persona del equipo.'];

            case 'quote_financing':
                return $this->quoteFinancing($search, $input);

            default:
                return ['error' => 'Herramienta desconocida.'];
        }
    }

    private function quoteFinancing(VehicleSearchService $search, array $input): array
    {
        $detail = $search->detail((int) ($input['vehicle_id'] ?? 0));
        if (!$detail || !isset($detail['price_raw'])) {
            return ['error' => 'No encontré el vehículo para cotizar.'];
        }
        $price = (float) $detail['price_raw'];
        $down  = (float) ($input['down_payment'] ?? 0);
        $term  = (int) ($input['term_months'] ?? 0);
        $rate  = isset($input['interest_rate']) ? (float) $input['interest_rate'] : 1.0;

        if ($term <= 0 || $price <= 0) {
            return ['error' => 'Datos insuficientes para la cotización.'];
        }

        try {
            $q = VehicleQuote::generateQuote($price, $down, $term, $rate, 'monthly');
        } catch (\Throwable $e) {
            return ['error' => 'No pude calcular la cuota; paso el chat a un asesor.'];
        }

        return [
            'vehicle'         => $detail['title'],
            'monthly_payment' => $q['monthly_payment'] ?? null,
            'term_months'     => $term,
            'down_payment'    => $down,
            'disclaimer'      => 'Cuota estimada, sujeta a aprobación crediticia.',
        ];
    }

    /* ── Anthropic ── */

    private function callAnthropic(string $apiKey, string $system, array $messages, array $tools): ?array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(40)->post(self::ANTHROPIC_URL, [
                'model'      => self::CHAT_MODEL,
                'max_tokens' => self::MAX_TOKENS,
                'system'     => [
                    ['type' => 'text', 'text' => $system, 'cache_control' => ['type' => 'ephemeral']],
                ],
                'tools'      => $tools,
                'messages'   => $messages,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::channel('whatsapp')->error('Anthropic error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->error('Anthropic excepción', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /* ── Historial y envío ── */

    private function buildHistory(int $companyId, string $phone): array
    {
        $rows = WhatsappConversation::forCompany($companyId)->forPhone($phone)
            ->orderByDesc('id')->limit(self::HISTORY_LIMIT)->get()->reverse()->values();

        $messages = [];
        foreach ($rows as $row) {
            $role = $row->direction === WhatsappConversation::DIRECTION_INBOUND ? 'user' : 'assistant';
            $text = trim((string) $row->message);
            if ($text === '') {
                continue;
            }
            $messages[] = ['role' => $role, 'content' => $text];
        }

        // Debe empezar con 'user'
        while (!empty($messages) && $messages[0]['role'] !== 'user') {
            array_shift($messages);
        }

        if (empty($messages)) {
            $messages[] = ['role' => 'user', 'content' => 'Hola'];
        }

        return $messages;
    }

    private function sendReply(CompanyWhatsappBot $bot, WhatsappChat $chat, string $text, array $images): void
    {
        $cloud = app(WhatsAppCloudService::class);
        $result = $cloud->sendText($bot, $chat->phone, $text);

        WhatsappConversation::create([
            'company_id'   => $bot->company_id,
            'phone'        => $chat->phone,
            'contact_name' => $chat->contact_name,
            'direction'    => WhatsappConversation::DIRECTION_OUTBOUND,
            'message'      => $text,
            'message_type' => 'text',
            'is_human'     => false,
            'wam_id'       => $result['wam_id'] ?? null,
        ]);

        foreach (array_slice(array_unique($images), 0, 3) as $img) {
            $cloud->sendImage($bot, $chat->phone, $img);
        }

        $chat->update(['last_message_at' => now()]);
    }
}
