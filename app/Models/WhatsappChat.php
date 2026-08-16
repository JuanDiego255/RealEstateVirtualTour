<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Estado del chat de un número con una empresa: si el bot está pausado (una
 * persona tomó el control) y si pidió relevo humano. Sostiene el relevo: sin
 * bot_paused/paused_at no hay forma de que una persona atienda sin que el bot
 * le siga hablando al cliente encima.
 */
class WhatsappChat extends Model
{
    protected $fillable = [
        'company_id', 'phone', 'contact_name',
        'bot_paused', 'paused_at',
        'needs_human_at', 'needs_human_reason',
        'last_message_at', 'last_seen_at',
    ];

    protected $casts = [
        'bot_paused'      => 'boolean',
        'paused_at'       => 'datetime',
        'needs_human_at'  => 'datetime',
        'last_message_at' => 'datetime',
        'last_seen_at'    => 'datetime',
    ];

    /**
     * ¿Existe la tabla? (guarda de existencia para ambientes sin migrar).
     */
    public static function available(): bool
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Schema::hasTable('whatsapp_chats');
        }
        return $cache;
    }

    /**
     * ¿El bot puede responder este chat? Ante "no sé", el comportamiento seguro
     * es el previo (permitir), no el nuevo.
     */
    public static function botAllowedFor(int $companyId, string $phone): bool
    {
        if (!static::available()) {
            return true;
        }
        $chat = static::where('company_id', $companyId)->where('phone', $phone)->first();
        if (!$chat) {
            return true;
        }
        return !$chat->bot_paused;
    }

    /**
     * Pausar el bot (una persona toma el control). Solo sella paused_at la
     * primera vez, para no romper una eventual reanudación automática.
     */
    public function pauseBot(?string $reason = null): void
    {
        $this->update([
            'bot_paused'         => true,
            'paused_at'          => $this->bot_paused ? $this->paused_at : now(),
            'needs_human_at'     => $this->needs_human_at ?: now(),
            'needs_human_reason' => $reason ?: $this->needs_human_reason,
        ]);
    }

    /**
     * Devolver el chat al bot.
     */
    public function resumeBot(): void
    {
        $this->update([
            'bot_paused'         => false,
            'paused_at'          => null,
            'needs_human_at'     => null,
            'needs_human_reason' => null,
        ]);
    }
}
