<?php

namespace App\Services;

use App\Models\CompanyAiSetting;
use App\Models\CompanyWhatsappBot;
use App\Models\WhatsappBotSetting;
use App\Models\WhatsappChat;
use App\Models\WhatsappConversation;
use App\Models\WhatsappBotUsage;
use App\Models\WhatsappBotPromotion;
use App\Models\VehicleQuote;
use App\Services\TestDriveScheduler;
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
    const MAX_TOOL_LOOPS = 4;
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
        $memory   = $this->buildMemory($bot->company_id, $chat->phone); // 2º bloque, no cacheado
        $messages = $this->buildHistory($bot->company_id, $chat->phone);
        $tools    = $this->buildTools($bot);

        $handoffReason = null;
        $mediaToSend   = []; // cada item: ['url' => ..., 'caption' => ?string, 'vehicle_id' => ?int]
        $shownVehicles = []; // id => datos distintivos de los vehículos mostrados este turno
        $usedTools     = [];
        $finalText     = '';

        for ($loop = 0; $loop < self::MAX_TOOL_LOOPS; $loop++) {
            $resp = $this->callAnthropic($ai->api_key, $system, $memory, $messages, $tools);
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
                // Red de seguridad: si el modelo prometió algo (datos o una cita) sin
                // ejecutar la herramienta correspondiente, lo empujamos a hacerlo (una vez).
                $nudge = null;
                if ($loop < self::MAX_TOOL_LOOPS - 1) {
                    if ($this->announcedWithoutDelivering($finalText, $usedTools)) {
                        $nudge = 'Entregá AHORA la información que ofreciste usando la herramienta correspondiente '
                            . '(get_vehicle_detail, search_vehicles o quote_financing) e incluí los datos en tu respuesta. '
                            . 'No prometas enviarla: mostrala.';
                    } elseif ($this->confirmedScheduleWithoutTool($finalText, $usedTools)) {
                        $nudge = 'No des por confirmada la cita sin agendarla: llamá schedule_test_drive AHORA con el vehículo, '
                            . 'la fecha y hora exactas (ISO 8601) y el nombre. Si falta algún dato, pedilo en vez de confirmar.';
                    }
                }
                if ($nudge !== null) {
                    $messages[] = ['role' => 'assistant', 'content' => $content];
                    $messages[] = ['role' => 'user', 'content' => $nudge];
                    continue;
                }
                break; // el modelo devolvió texto: terminamos
            }

            // Echo del turno del asistente (obligatorio para continuar el tool loop)
            $messages[] = ['role' => 'assistant', 'content' => $content];

            $toolResults = [];
            foreach ($toolUses as $tu) {
                $usedTools[] = $tu['name'] ?? '';
                $result = $this->executeTool($tu['name'] ?? '', $tu['input'] ?? [], $search, $bot, $chat, $handoffReason, $mediaToSend, $shownVehicles);
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
        if ($finalText !== '' && HandoffPolicy::botPromiseWithoutTool($finalText, $usedTools) && !$handoffReason) {
            $handoffReason = 'El bot prometió una acción (apartar/crédito/cita) sin ejecutar la herramienta.';
        }

        if ($finalText === '') {
            $finalText = 'Disculpá, no pude procesar eso ahora. En un momento te atiende una persona del equipo.';
            $handoffReason = $handoffReason ?: 'Respuesta vacía del modelo.';
        }

        $this->sendReply($bot, $chat, $finalText, $mediaToSend, $shownVehicles);

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
            '- ENTREGÁ, NO ANUNCIES: nunca digas que vas a mandar algo y termines (nada de "te paso la ficha", "ahora te doy la cuota", "aquí tienes:"). Si necesitás datos, llamá la herramienta en ESTE mismo turno e incluí el resultado en tu respuesta.',
            '- Si piden la ficha o el detalle de un vehículo, llamá get_vehicle_detail y entregá los datos. Las fotos se envían solas: nunca pegues links de imágenes.',
            '- En listados, las fotos de cada vehículo con su descripción se envían automáticamente; tu texto debe ser una intro breve, sin repetir todo ni pegar links.',
            '- Recordá lo que ya mostraste: si el cliente dice "el rojo", "el segundo", "el del 2021" o responde citando una foto, se refiere a un vehículo que ya le mostraste (está en la memoria). Usá su id con get_vehicle_detail; no busques de cero ni digas que no lo encontrás. Si dos se parecen, diferencialos por color, año o marca.',
        ];
        $hard[] = '- Para agendar una prueba de manejo pedí día, hora y nombre; luego llamá schedule_test_drive OBLIGATORIAMENTE.'
            . ' Nunca digas "nos vemos", "te espero", "quedó agendada" ni des por confirmada una fecha sin haber ejecutado schedule_test_drive en este mismo turno.'
            . ' La cita queda tentativa hasta que un asesor la confirme.';
        if (!$bot->allow_financing_quote) {
            $hard[] = '- No cotices cuotas, plazos ni tasas, y NO pidas prima ni plazo. Si preguntan por financiamiento, pedí solo nombre y teléfono y llamá handoff_to_human de una vez.';
        } else {
            $hard[] = '- Para financiamiento llamá quote_financing con vehículo, prima y plazo, y entregá la cuota en el mismo mensaje, aclarando que es estimada y sujeta a aprobación. No digas "te paso la cuota" sin el número.';
        }
        $blocks[] = implode("\n", $hard);

        $promos = $this->currentPromotions($bot->company_id);
        if ($promos !== '') {
            $blocks[] = "PROMOCIONES VIGENTES (mencionalas cuando venga al caso, no las inventes ni las cambies):\n" . $promos;
        }

        $blocks[] = HandoffPolicy::promptSection($cfg);

        if ($settings->custom_rules) {
            $blocks[] = "REGLAS DEL NEGOCIO:\n" . $settings->custom_rules;
        }
        if ($settings->order_instructions) {
            $blocks[] = "CÓMO SE CIERRA UNA COMPRA:\n" . $settings->order_instructions;
        }

        $now = now();
        $blocks[] = 'CONTEXTO: hoy es ' . $now->translatedFormat('l d/m/Y') . ' y son las ' . $now->format('H:i') . '.'
            . ' Al agendar, convertí expresiones como "mañana" o "el sábado" a una fecha y hora concretas (formato ISO 8601).';

        return implode("\n\n", $blocks);
    }

    /**
     * ¿El modelo "anunció" que iba a entregar algo (ficha, cuota, lista) pero no
     * ejecutó ninguna herramienta de datos? En ese caso hay que empujarlo a
     * llamar la herramienta en lugar de terminar el turno con una promesa vacía.
     */
    private function announcedWithoutDelivering(string $text, array $usedTools): bool
    {
        if (trim($text) === '') {
            return false;
        }
        $dataTools = ['search_vehicles', 'get_vehicle_detail', 'quote_financing', 'check_vehicle_status'];
        if (array_intersect($usedTools, $dataTools)) {
            return false; // ya entregó datos vía herramienta
        }

        $announce = '/(te (paso|env[ií]o|comparto|mando|doy|muestro|adjunto)'
            . '|ahora (s[ií]|te|mismo)\b'
            . '|ac[aá] (te|van|est|ten[ée]s)'
            . '|aqu[ií] (te|est|ten[ée]s)'
            . '|dame un momento|un momento y te)/iu';

        return (bool) preg_match($announce, $text) || (bool) preg_match('/:\s*$/', $text);
    }

    /**
     * ¿El modelo dio por confirmada una cita/prueba sin ejecutar schedule_test_drive?
     */
    private function confirmedScheduleWithoutTool(string $text, array $usedTools): bool
    {
        if (trim($text) === '' || in_array('schedule_test_drive', $usedTools, true)) {
            return false;
        }

        $pattern = '/(nos vemos|te espero|te esperamos'
            . '|qued[oó] (agendad|reservad|confirmad)'
            . '|agend[ée]\b'
            . '|te confirmo la cita'
            . '|(asesor|equipo)[^.]{0,30}(confirm|coordin)[^.]{0,20}(cita|prueba)'
            . '|cita (qued|confirmad|reservad|para el|para mañana|para hoy|para las|es el|es mañana))/iu';

        return (bool) preg_match($pattern, $text);
    }

    private function currentPromotions(int $companyId): string
    {
        if (!WhatsappBotPromotion::available()) {
            return '';
        }
        $promos = WhatsappBotPromotion::forCompany($companyId)->current()->latest()->limit(5)->get();
        return $promos->map(fn($p) => '- ' . $p->title . ': ' . $p->description)->implode("\n");
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

        $tools[] = [
            'name'        => 'schedule_test_drive',
            'description' => 'Registra una solicitud de prueba de manejo (vehículo, fecha/hora y nombre) para que un asesor la confirme. No agenda en firme.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'vehicle_id'         => ['type' => 'integer'],
                    'preferred_datetime' => ['type' => 'string', 'description' => 'Fecha y hora en ISO 8601, ej: 2026-08-20T15:00:00'],
                    'client_name'        => ['type' => 'string'],
                    'client_email'       => ['type' => 'string'],
                    'duration_minutes'   => ['type' => 'integer', 'description' => 'Opcional, por defecto 45'],
                    'notes'              => ['type' => 'string'],
                ],
                'required' => ['vehicle_id', 'preferred_datetime'],
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

    private function executeTool(string $name, array $input, VehicleSearchService $search, CompanyWhatsappBot $bot, WhatsappChat $chat, ?string &$handoffReason, array &$mediaToSend, array &$shownVehicles)
    {
        switch ($name) {
            case 'search_vehicles':
                $results = $search->search($input, $bot->max_vehicles_per_reply ?: 3);
                // Cada vehículo se envía como foto + descripción breve al pie.
                foreach ($results as $v) {
                    $this->rememberVehicle($shownVehicles, $v, false);
                    if (!empty($v['main_image'])) {
                        $mediaToSend[] = ['url' => $v['main_image'], 'caption' => $this->listingCaption($v), 'vehicle_id' => $v['id'] ?? null];
                    }
                }
                return [
                    'results' => array_map(fn($v) => $this->stripImageUrls($v), $results),
                    'note'    => 'Las fotos de cada vehículo con su descripción YA se envían al cliente. En tu respuesta hacé solo una introducción breve y ofrecé ayuda; no repitas los detalles ni pegues links de imágenes.',
                ];

            case 'get_vehicle_detail':
                $detail = $search->detail((int) ($input['id'] ?? 0));
                if (!$detail) {
                    return ['error' => 'No encontré ese vehículo.'];
                }
                // El cliente pidió su ficha: queda marcado como el que le interesa.
                $this->rememberVehicle($shownVehicles, $detail, true);
                $this->markLeadInterest($chat, (int) $detail['id']);
                $imgs = $detail['images'] ?? [];
                foreach (array_values(array_slice($imgs, 0, 3)) as $i => $img) {
                    $mediaToSend[] = ['url' => $img, 'caption' => $i === 0 ? $this->detailCaption($detail) : null, 'vehicle_id' => $detail['id'] ?? null];
                }
                return $this->stripImageUrls($detail)
                    + ['note' => 'Ya envío la(s) foto(s) del vehículo al cliente; no pegues links de imágenes en tu respuesta.'];

            case 'check_vehicle_status':
                return $search->status((int) ($input['id'] ?? 0));

            case 'handoff_to_human':
                $handoffReason = trim((string) ($input['reason'] ?? 'El cliente necesita atención humana.'));
                return ['ok' => true, 'message' => 'Un momento, te paso con una persona del equipo.'];

            case 'schedule_test_drive':
                $res = (new TestDriveScheduler($bot))->propose($input, $chat->phone, $chat->contact_name, $chat->id);
                if (!$res['ok'] && !empty($res['needs_human'])) {
                    $handoffReason = $res['error'];
                }
                return $res['ok']
                    ? ['ok' => true, 'when' => $res['when'],
                        'message' => 'Solicitud registrada' . (!empty($res['when']) ? ' para ' . $res['when'] : '')
                            . '. Confirmá al cliente que un asesor le confirma la cita en breve; NO afirmes que ya quedó agendada.']
                    : ['ok' => false, 'error' => $res['error']];

            case 'quote_financing':
                return $this->quoteFinancing($search, $input);

            default:
                return ['error' => 'Herramienta desconocida.'];
        }
    }

    /**
     * Descripción breve al pie de la foto en un listado. El pie ES la memoria:
     * incluye color, specs y código para poder resolver "el rojo", "el segundo".
     */
    private function listingCaption(array $v): string
    {
        $lines = ['*' . trim($v['title'] ?? 'Vehículo') . '*'];
        $lines[] = trim((!empty($v['price']) ? $v['price'] . ' · ' : '') . 'Cód. #' . ($v['id'] ?? ''));
        $specs = array_filter([
            !empty($v['color']) ? 'Color: ' . $v['color'] : null,
            !empty($v['mileage_km']) ? $v['mileage_km'] . ' km' : null,
            $v['transmission'] ?? null,
            $v['fuel_type'] ?? null,
        ]);
        if ($specs) {
            $lines[] = implode(' · ', $specs);
        }
        if (!empty($v['status'])) {
            $lines[] = 'Estado: ' . ucfirst($v['status']);
        }
        return mb_substr(implode("\n", $lines), 0, 1024);
    }

    /**
     * Pie de foto para el detalle de un vehículo (breve; el texto lo da el bot).
     */
    private function detailCaption(array $v): string
    {
        $line = '*' . trim($v['title'] ?? 'Vehículo') . '*'
            . (!empty($v['price']) ? "\n" . $v['price'] : '')
            . (!empty($v['color']) ? ' · ' . $v['color'] : '')
            . "\nCód. #" . ($v['id'] ?? '');
        return mb_substr($line, 0, 1024);
    }

    /**
     * Guarda un vehículo mostrado (con sus datos distintivos). Una vez marcado
     * como "elegido", no se desmarca.
     */
    private function rememberVehicle(array &$shown, array $v, bool $elegido): void
    {
        if (empty($v['id'])) {
            return;
        }
        $id = (int) $v['id'];
        $shown[$id] = [
            'id'      => $id,
            'title'   => $v['title'] ?? trim(($v['brand'] ?? '') . ' ' . ($v['model'] ?? '')),
            'brand'   => $v['brand'] ?? null,
            'year'    => $v['year'] ?? null,
            'color'   => $v['color'] ?? null,
            'price'   => $v['price'] ?? null,
            'status'  => $v['status'] ?? null,
            'elegido' => $elegido || !empty($shown[$id]['elegido']),
        ];
    }

    /**
     * Marca el vehículo de interés del lead (para el CRM y el panel).
     */
    private function markLeadInterest(WhatsappChat $chat, int $vehicleId): void
    {
        try {
            $leadId = $chat->lead_id;
            if (!$leadId) {
                $leadId = optional(WhatsAppLeadService::findByPhone($chat->company_id, $chat->phone))->id;
            }
            if ($leadId && $vehicleId) {
                \App\Lead::where('id', $leadId)->update(['vehicle_id' => $vehicleId]);
            }
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->warning('No se pudo marcar interés del lead', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Segundo bloque de system (no cacheado): qué vehículos ya se le mostraron al
     * cliente y a cuál respondió citándolo. Es lo que resuelve "el rojo".
     */
    private function buildMemory(int $companyId, string $phone): string
    {
        $shown = [];
        try {
            $rows = WhatsappConversation::forCompany($companyId)->forPhone($phone)
                ->where('direction', WhatsappConversation::DIRECTION_OUTBOUND)
                ->whereNotNull('metadata')
                ->where('created_at', '>=', now()->subDay())
                ->orderByDesc('id')->limit(12)->get(['metadata']);
            foreach ($rows as $row) {
                foreach (($row->metadata['vehicles'] ?? []) as $v) {
                    if (empty($v['id']) || isset($shown[$v['id']])) {
                        continue;
                    }
                    $shown[$v['id']] = $v;
                    if (count($shown) >= 12) {
                        break 2;
                    }
                }
            }
        } catch (\Throwable $e) {
            return '';
        }

        // Contexto de respuesta citada (el cliente respondió a una foto puntual).
        $replyLine = '';
        try {
            $lastIn = WhatsappConversation::forCompany($companyId)->forPhone($phone)
                ->where('direction', WhatsappConversation::DIRECTION_INBOUND)
                ->orderByDesc('id')->first(['metadata']);
            $rid = $lastIn->metadata['reply_to_vehicle_id'] ?? null;
            if ($rid) {
                $name = $shown[$rid]['title'] ?? ('vehículo id ' . $rid);
                $replyLine = "EL ÚLTIMO MENSAJE DEL CLIENTE RESPONDE/CITA al vehículo id {$rid} ({$name}). "
                    . "Interpretá su mensaje sobre ESE vehículo; usá get_vehicle_detail o check_vehicle_status con id {$rid} si hace falta.\n\n";
            }
        } catch (\Throwable $e) {
            // sin contexto de reply, seguimos
        }

        if (!$shown && $replyLine === '') {
            return '';
        }

        $lines = array_map(function ($v) {
            $bits = array_filter([
                $v['title'] ?? null,
                !empty($v['color']) ? 'color ' . $v['color'] : null,
                !empty($v['year']) ? (string) $v['year'] : null,
                $v['price'] ?? null,
                !empty($v['elegido']) ? '⭐ le interesó' : null,
            ]);
            return '- id ' . $v['id'] . ' · ' . implode(' · ', $bits);
        }, array_values($shown));

        return $replyLine
            . "VEHÍCULOS QUE YA LE MOSTRASTE A ESTE CLIENTE\n"
            . implode("\n", $lines)
            . "\n\nSi el cliente se refiere a uno de estos (\"el rojo\", \"el del 2021\", \"el segundo\", \"el KIA\"), es uno de la lista: "
            . "usá su id directamente con get_vehicle_detail o check_vehicle_status. No busques de cero ni digas que no lo encontrás, "
            . "porque vos mismo se lo mostraste. Si dos se parecen, diferencialos por color, año o marca, nunca solo por el precio.";
    }

    /**
     * Quita las URLs de imágenes de lo que ve el modelo (para que no pegue links).
     */
    private function stripImageUrls(array $v): array
    {
        unset($v['main_image'], $v['images']);
        return $v;
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

    private function callAnthropic(string $apiKey, string $system, string $memory, array $messages, array $tools): ?array
    {
        try {
            // Dos bloques de system: el prompt estable (cacheado) y la memoria de
            // la conversación (liviana y variable → NO cacheada, no invalida el caché).
            $systemBlocks = array_values(array_filter([
                ['type' => 'text', 'text' => $system, 'cache_control' => ['type' => 'ephemeral']],
                $memory !== '' ? ['type' => 'text', 'text' => $memory] : null,
            ]));

            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(40)->post(self::ANTHROPIC_URL, [
                'model'      => self::CHAT_MODEL,
                'max_tokens' => self::MAX_TOKENS,
                'system'     => $systemBlocks,
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

    /**
     * @param  array  $media  Items ['url'=>string, 'caption'=>?string, 'vehicle_id'=>?int]
     * @param  array  $shownVehicles  id => datos, para guardar en la metadata del texto (memoria)
     */
    private function sendReply(CompanyWhatsappBot $bot, WhatsappChat $chat, string $text, array $media, array $shownVehicles = []): void
    {
        $cloud = app(WhatsAppCloudService::class);
        $result = $cloud->sendText($bot, $chat->phone, $text);

        // El texto guarda en su metadata los vehículos mostrados (bloque de ids).
        WhatsappConversation::create([
            'company_id'   => $bot->company_id,
            'phone'        => $chat->phone,
            'contact_name' => $chat->contact_name,
            'direction'    => WhatsappConversation::DIRECTION_OUTBOUND,
            'message'      => $text,
            'message_type' => 'text',
            'is_human'     => false,
            'wam_id'       => $result['wam_id'] ?? null,
            'metadata'     => !empty($shownVehicles) ? ['vehicles' => array_values($shownVehicles)] : null,
        ]);

        // Fotos con su descripción al pie, deduplicadas por URL. Cada imagen guarda
        // su wam_id y vehicle_id → así un "responder" del cliente resuelve el vehículo.
        $seen = [];
        foreach ($media as $item) {
            $url = is_array($item) ? ($item['url'] ?? null) : $item;
            if (!$url || in_array($url, $seen, true)) {
                continue;
            }
            $seen[] = $url;
            if (count($seen) > 12) {
                break; // tope de seguridad contra acumulación desmedida
            }
            $caption   = is_array($item) ? ($item['caption'] ?? null) : null;
            $vehicleId = is_array($item) ? ($item['vehicle_id'] ?? null) : null;
            $imgRes    = $cloud->sendImage($bot, $chat->phone, $url, $caption);

            WhatsappConversation::create([
                'company_id'   => $bot->company_id,
                'phone'        => $chat->phone,
                'contact_name' => $chat->contact_name,
                'direction'    => WhatsappConversation::DIRECTION_OUTBOUND,
                'message'      => $caption ?: '[imagen]',
                'message_type' => 'image',
                'is_human'     => false,
                'wam_id'       => $imgRes['wam_id'] ?? null,
                'metadata'     => $vehicleId ? ['vehicle_id' => (int) $vehicleId, 'elegido' => !empty($shownVehicles[$vehicleId]['elegido'])] : null,
            ]);
        }

        $chat->update(['last_message_at' => now()]);
    }
}
