<?php

namespace App\Services;

use App\Models\CompanyAiSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Entrenamiento por capturas: toma imágenes de chats reales del negocio y, con
 * el modelo de visión (Sonnet), destila un "perfil de tono" editable que luego
 * el bot usa en su prompt. Nunca copia datos de clientes: solo el estilo.
 */
class BotTrainingService
{
    const ANTHROPIC_URL = 'https://api.anthropic.com/v1/messages';
    const MAX_TOKENS    = 1200;
    const MAX_IMAGES    = 8;

    /**
     * @param  string[]  $absolutePaths  Rutas absolutas a las imágenes subidas.
     * @return array{ok: bool, profile: ?string, cost: float, error: ?string}
     */
    public function buildProfile(int $companyId, array $absolutePaths, ?string $currentProfile = null, ?string $notes = null): array
    {
        $ai = CompanyAiSetting::where('company_id', $companyId)->first();
        if (!$ai || !$ai->api_key) {
            return ['ok' => false, 'profile' => null, 'cost' => 0.0, 'error' => 'La empresa no tiene una API key de IA configurada.'];
        }

        $imageBlocks = [];
        foreach (array_slice($absolutePaths, 0, self::MAX_IMAGES) as $path) {
            $block = $this->imageBlock($path);
            if ($block) {
                $imageBlocks[] = $block;
            }
        }
        if (empty($imageBlocks)) {
            return ['ok' => false, 'profile' => null, 'cost' => 0.0, 'error' => 'No se pudieron leer las imágenes.'];
        }

        $userBlocks = $imageBlocks;
        $instruction = 'Estas son capturas de conversaciones reales de WhatsApp de un negocio de vehículos.'
            . ' Analizá SOLO el estilo con que atiende el negocio (no los datos de los clientes) y devolvé un'
            . " PERFIL DE TONO breve y accionable en español, con estos puntos:\n"
            . "- Trato (tuteo/usted/vos) y nivel de formalidad\n"
            . "- Uso de emojis y signos (cuánto, cuáles)\n"
            . "- Saludo y despedida típicos\n"
            . "- Frases y muletillas propias del negocio\n"
            . "- Cómo ofrece y cómo cierra (llamado a la acción)\n"
            . "- Qué evita (lo que nunca hace)\n"
            . 'Escribilo como instrucciones para que otro asistente imite ese tono. No inventes datos.';
        if ($currentProfile) {
            $instruction .= "\n\nPerfil actual a mejorar (respetalo y refinalo):\n" . $currentProfile;
        }
        if ($notes) {
            $instruction .= "\n\nIndicaciones del negocio:\n" . $notes;
        }
        $userBlocks[] = ['type' => 'text', 'text' => $instruction];

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $ai->api_key,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(90)->post(self::ANTHROPIC_URL, [
                'model'      => $ai->modelKey(),
                'max_tokens' => self::MAX_TOKENS,
                'system'     => 'Sos un analista de comunicación comercial. Extraés perfiles de tono de atención al cliente.',
                'messages'   => [['role' => 'user', 'content' => $userBlocks]],
            ]);
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->error('Entrenamiento visión excepción', ['message' => $e->getMessage()]);
            return ['ok' => false, 'profile' => null, 'cost' => 0.0, 'error' => 'Error al contactar la IA: ' . $e->getMessage()];
        }

        if (!$response->successful()) {
            Log::channel('whatsapp')->error('Entrenamiento visión error', ['status' => $response->status(), 'body' => $response->body()]);
            return ['ok' => false, 'profile' => null, 'cost' => 0.0, 'error' => 'La IA devolvió un error (' . $response->status() . ').'];
        }

        $json = $response->json();
        $text = trim(implode("\n", array_map(
            fn($b) => $b['text'] ?? '',
            array_filter(data_get($json, 'content', []), fn($b) => ($b['type'] ?? '') === 'text')
        )));

        $in   = (int) data_get($json, 'usage.input_tokens', 0);
        $out  = (int) data_get($json, 'usage.output_tokens', 0);
        $cost = CompanyAiSetting::costFor($ai->modelKey(), $in, $out);

        if ($text === '') {
            return ['ok' => false, 'profile' => null, 'cost' => $cost, 'error' => 'La IA no devolvió un perfil legible.'];
        }

        return ['ok' => true, 'profile' => $text, 'cost' => $cost, 'error' => null];
    }

    /**
     * Construye el bloque de imagen (base64) para la API, o null si falla.
     */
    private function imageBlock(string $path): ?array
    {
        if (!is_file($path) || filesize($path) > 5 * 1024 * 1024) {
            return null;
        }
        $data = @file_get_contents($path);
        if ($data === false) {
            return null;
        }
        $mime = @mime_content_type($path) ?: 'image/jpeg';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            return null;
        }
        return [
            'type'   => 'image',
            'source' => ['type' => 'base64', 'media_type' => $mime, 'data' => base64_encode($data)],
        ];
    }
}
