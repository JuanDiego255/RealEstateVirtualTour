<?php

namespace App\Services;

use App\Models\CompanyWhatsappBot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente mínimo de la WhatsApp Cloud API (Graph) para enviar mensajes salientes
 * dentro de la ventana de 24 h. Usado por el panel (respuesta manual) y, más
 * adelante, por el bot.
 */
class WhatsAppCloudService
{
    /**
     * Enviar un mensaje de texto.
     *
     * @return array{ok: bool, wam_id: ?string, error: ?string}
     */
    public function sendText(CompanyWhatsappBot $bot, string $to, string $text): array
    {
        if (!$bot->phone_number_id || !$bot->access_token) {
            return ['ok' => false, 'wam_id' => null, 'error' => 'Bot sin phone_number_id o access_token.'];
        }

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            $bot->graphVersion(),
            $bot->phone_number_id
        );

        try {
            $response = Http::withToken($bot->access_token)
                ->timeout(20)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to'                => $this->normalizePhone($to),
                    'type'              => 'text',
                    'text'              => ['body' => $text],
                ]);

            if ($response->successful()) {
                $wamId = data_get($response->json(), 'messages.0.id');
                return ['ok' => true, 'wam_id' => $wamId, 'error' => null];
            }

            $error = data_get($response->json(), 'error.message', 'Error desconocido de la Cloud API');
            Log::channel('whatsapp')->warning('Envío fallido', [
                'company_id' => $bot->company_id,
                'to'         => $to,
                'status'     => $response->status(),
                'error'      => $error,
            ]);
            return ['ok' => false, 'wam_id' => null, 'error' => $error];
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->error('Excepción al enviar', [
                'company_id' => $bot->company_id,
                'to'         => $to,
                'message'    => $e->getMessage(),
            ]);
            return ['ok' => false, 'wam_id' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar una imagen por URL (con caption opcional).
     *
     * @return array{ok: bool, wam_id: ?string, error: ?string}
     */
    public function sendImage(CompanyWhatsappBot $bot, string $to, string $imageUrl, ?string $caption = null): array
    {
        if (!$bot->phone_number_id || !$bot->access_token) {
            return ['ok' => false, 'wam_id' => null, 'error' => 'Bot sin phone_number_id o access_token.'];
        }

        $url = sprintf('https://graph.facebook.com/%s/%s/messages', $bot->graphVersion(), $bot->phone_number_id);
        $image = ['link' => $imageUrl];
        if ($caption) {
            $image['caption'] = $caption;
        }

        try {
            $response = Http::withToken($bot->access_token)->timeout(20)->post($url, [
                'messaging_product' => 'whatsapp',
                'to'                => $this->normalizePhone($to),
                'type'              => 'image',
                'image'             => $image,
            ]);

            if ($response->successful()) {
                return ['ok' => true, 'wam_id' => data_get($response->json(), 'messages.0.id'), 'error' => null];
            }
            return ['ok' => false, 'wam_id' => null, 'error' => data_get($response->json(), 'error.message', 'Error Cloud API')];
        } catch (\Throwable $e) {
            return ['ok' => false, 'wam_id' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Normaliza el teléfono a solo dígitos (formato E.164 sin '+').
     */
    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }
}
