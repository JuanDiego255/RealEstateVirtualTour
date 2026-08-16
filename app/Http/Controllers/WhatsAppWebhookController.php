<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWhatsAppMessageJob;
use App\Models\CompanyWhatsappBot;
use App\Models\WhatsappBotSetting;
use App\Models\WhatsappBotUsage;
use App\Models\WhatsappChat;
use App\Models\WhatsappConversation;
use App\Services\HandoffPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Webhook único de WhatsApp para toda la plataforma. Resuelve la empresa por
 * phone_number_id, valida la firma HMAC con el App Secret de esa empresa,
 * guarda el mensaje de forma idempotente (por wam_id) y SIEMPRE responde 200.
 *
 * Por ahora solo recibe y guarda (Etapa 1). La respuesta del bot se agrega en
 * la Etapa 3 vía app()->terminating().
 */
class WhatsAppWebhookController extends Controller
{
    /**
     * GET /webhook/whatsapp — verificación de Meta (responde hub_challenge).
     */
    public function verify(Request $request)
    {
        $mode      = $request->get('hub_mode');
        $token     = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if ($mode !== 'subscribe' || !$token) {
            return response('Bad Request', 400);
        }

        // Coincide con el token global o con el de algún bot configurado
        $globalToken = config('whatsapp.verify_token');
        $valid = ($globalToken && hash_equals($globalToken, $token))
            || CompanyWhatsappBot::where('verify_token', $token)->exists();

        if ($valid) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * POST /webhook/whatsapp — eventos entrantes.
     */
    public function handle(Request $request)
    {
        // Cuerpo CRUDO: la firma se valida sobre estos bytes exactos.
        $rawBody   = $request->getContent();
        $signature = $request->header('X-Hub-Signature-256', '');

        try {
            $payload = json_decode($rawBody, true) ?: [];

            foreach (data_get($payload, 'entry', []) as $entry) {
                foreach (data_get($entry, 'changes', []) as $change) {
                    $value = $change['value'] ?? [];
                    $this->processValue($value, $rawBody, $signature);
                }
            }
        } catch (\Throwable $e) {
            // Nunca devolver 500: Meta reintentaría sin parar.
            Log::channel('whatsapp')->error('Excepción en webhook', ['message' => $e->getMessage()]);
        }

        return response('', 200);
    }

    /**
     * Procesa un bloque "value" del webhook (mensajes de un número).
     */
    private function processValue(array $value, string $rawBody, string $signature): void
    {
        $messages = $value['messages'] ?? [];
        if (empty($messages)) {
            return; // estados de entrega u otros eventos que no procesamos aún
        }

        $phoneNumberId = data_get($value, 'metadata.phone_number_id');
        $bot = CompanyWhatsappBot::resolveByPhoneNumberId($phoneNumberId);

        if (!$bot) {
            Log::channel('whatsapp')->warning('phone_number_id sin bot', ['pnid' => $phoneNumberId]);
            return;
        }

        // Sin App Secret no se puede validar la firma: se rechaza (endpoint público).
        if (!$bot->app_secret) {
            Log::channel('whatsapp')->warning('Bot sin app_secret; mensaje rechazado', ['company_id' => $bot->company_id]);
            return;
        }

        if (!$this->signatureValid($rawBody, $signature, $bot->app_secret)) {
            Log::channel('whatsapp')->warning('Firma inválida', ['company_id' => $bot->company_id]);
            return;
        }

        $contactName = data_get($value, 'contacts.0.profile.name');

        foreach ($messages as $message) {
            $this->storeInbound($bot, $message, $contactName);
        }
    }

    /**
     * Guarda un mensaje entrante (idempotente por wam_id) y actualiza el chat.
     */
    private function storeInbound(CompanyWhatsappBot $bot, array $message, ?string $contactName): void
    {
        $wamId = $message['id'] ?? null;
        if (!$wamId) {
            return;
        }

        // Idempotencia: Meta reintenta.
        if (WhatsappConversation::where('wam_id', $wamId)->exists()) {
            return;
        }

        $from = $message['from'] ?? null;
        if (!$from) {
            return;
        }

        $type = $message['type'] ?? 'text';
        $text = $this->extractText($message, $type);

        WhatsappConversation::create([
            'company_id'        => $bot->company_id,
            'phone'             => $from,
            'contact_name'      => $contactName,
            'direction'         => WhatsappConversation::DIRECTION_INBOUND,
            'message'           => $text,
            'message_type'      => $type,
            'is_human'          => false,
            'wam_id'            => $wamId,
            'window_started_at' => now(),
            'metadata'          => ['raw' => $message],
        ]);

        $chat = WhatsappChat::updateOrCreate(
            ['company_id' => $bot->company_id, 'phone' => $from],
            [
                'contact_name'    => $contactName,
                'last_message_at' => now(),
                'last_seen_at'    => now(),
            ]
        );

        Log::channel('whatsapp')->info('Mensaje entrante guardado', [
            'company_id' => $bot->company_id,
            'phone'      => $from,
            'type'       => $type,
        ]);

        $this->maybeRespond($bot, $chat, $type, $text);
    }

    /**
     * Decide si el bot debe responder y, de ser así, lo despacha DESPUÉS de la
     * respuesta HTTP (app()->terminating() → dispatchSync). Hosting compartido:
     * la respuesta es inmediata, sin cron.
     */
    private function maybeRespond(CompanyWhatsappBot $bot, WhatsappChat $chat, string $type, ?string $text): void
    {
        if (!$bot->enabled || !$bot->isUsable()) {
            return;
        }
        if ($bot->activation_mode === CompanyWhatsappBot::MODE_MANUAL) {
            return; // nunca se activa solo
        }
        if ($chat->bot_paused) {
            return; // una persona está atendiendo
        }

        $cfg = WhatsappBotSetting::firstOrNew(['company_id' => $bot->company_id])->handoffConfig();

        // Nota de voz: el bot no la escucha → relevo.
        if ($type === 'audio' && !empty($cfg['sends_voice_note'])) {
            $chat->pauseBot('Nota de voz (el bot no la escucha)');
            return;
        }

        // Palabra clave de relevo en el mensaje entrante.
        if ($text && HandoffPolicy::inboundTriggersHandoff($text, $cfg)) {
            $chat->pauseBot('Palabra clave de relevo');
            return;
        }

        // Fusible: cuota/tope agotado.
        if (WhatsappBotUsage::isBlocked($bot)) {
            return;
        }

        $companyId = $bot->company_id;
        $phone     = $chat->phone;
        app()->terminating(function () use ($companyId, $phone) {
            ProcessWhatsAppMessageJob::dispatchSync($companyId, $phone);
        });
    }

    /**
     * Extrae el texto legible del mensaje según su tipo.
     */
    private function extractText(array $message, string $type): ?string
    {
        switch ($type) {
            case 'text':
                return data_get($message, 'text.body');
            case 'button':
                return data_get($message, 'button.text');
            case 'interactive':
                return data_get($message, 'interactive.button_reply.title')
                    ?? data_get($message, 'interactive.list_reply.title');
            case 'image':
                return data_get($message, 'image.caption') ?: '[imagen]';
            case 'audio':
                return '[nota de voz]';
            default:
                return '[' . $type . ']';
        }
    }

    /**
     * Valida la firma HMAC-SHA256 del cuerpo crudo con el App Secret.
     */
    private function signatureValid(string $rawBody, string $signature, string $appSecret): bool
    {
        if (empty($signature) || strpos($signature, 'sha256=') !== 0) {
            return false;
        }
        $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $appSecret);
        return hash_equals($expected, $signature);
    }
}
