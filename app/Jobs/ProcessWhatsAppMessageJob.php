<?php

namespace App\Jobs;

use App\Models\CompanyWhatsappBot;
use App\Models\WhatsappChat;
use App\Services\WhatsAppBotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Procesa la respuesta del bot para un chat. En hosting compartido se despacha
 * con dispatchSync() desde app()->terminating(), de modo que corre DESPUÉS de
 * que Meta ya recibió su 200. Re-verifica el estado real antes de responder.
 */
class ProcessWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $companyId, public string $phone)
    {
    }

    public function handle(): void
    {
        try {
            $bot = CompanyWhatsappBot::where('company_id', $this->companyId)->first();
            if (!$bot || !$bot->isUsable()) {
                return;
            }

            $chat = WhatsappChat::where('company_id', $this->companyId)->where('phone', $this->phone)->first();
            if (!$chat || $chat->bot_paused) {
                return; // una persona tomó el control en el ínterin
            }

            app(WhatsAppBotService::class)->respond($bot, $chat);
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->error('Job de respuesta falló', [
                'company_id' => $this->companyId,
                'phone'      => $this->phone,
                'message'    => $e->getMessage(),
            ]);
        }
    }
}
