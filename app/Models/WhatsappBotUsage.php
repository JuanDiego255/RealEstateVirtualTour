<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Consumo y facturación por conversación. La unidad de cobro es la conversación
 * (ventana de 24 h de WhatsApp), no el mensaje: un cliente que manda 15 mensajes
 * en una tarde es UNA conversación.
 */
class WhatsappBotUsage extends Model
{
    protected $fillable = [
        'company_id', 'phone', 'period', 'window_started_at', 'window_expires_at',
        'anthropic_cost', 'whatsapp_cost', 'tokens_in', 'tokens_out', 'messages_count',
    ];

    protected $casts = [
        'window_started_at' => 'datetime',
        'window_expires_at' => 'datetime',
        'anthropic_cost'    => 'decimal:6',
        'whatsapp_cost'     => 'decimal:6',
        'tokens_in'         => 'integer',
        'tokens_out'        => 'integer',
        'messages_count'    => 'integer',
    ];

    public static function available(): bool
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Schema::hasTable('whatsapp_bot_usages');
        }
        return $cache;
    }

    /**
     * Abre una ventana de 24 h para el número o reutiliza la vigente.
     */
    public static function touchWindow(int $companyId, string $phone): ?self
    {
        if (!static::available()) {
            return null;
        }

        $active = static::where('company_id', $companyId)
            ->where('phone', $phone)
            ->where('window_expires_at', '>', now())
            ->latest('window_started_at')
            ->first();

        if ($active) {
            return $active;
        }

        return static::create([
            'company_id'        => $companyId,
            'phone'             => $phone,
            'period'            => now()->format('Y-m'),
            'window_started_at' => now(),
            'window_expires_at' => now()->addHours(24),
        ]);
    }

    /**
     * Suma el costo de una llamada a la IA.
     */
    public function addAnthropicCost(float $cost, int $in, int $out): void
    {
        $this->increment('anthropic_cost', $cost);
        $this->increment('tokens_in', $in);
        $this->increment('tokens_out', $out);
        $this->increment('messages_count');
    }

    /**
     * Facturación del periodo para un bot (lo que muestran los paneles y el fusible).
     *
     * @return array
     */
    public static function billing(CompanyWhatsappBot $bot, ?string $period = null): array
    {
        $period = $period ?: now()->format('Y-m');

        $rows = static::available()
            ? static::where('company_id', $bot->company_id)->where('period', $period)->get()
            : collect();

        $used        = $rows->count();
        $included    = $bot->included_conversations ?? 0;
        $extras      = max(0, $used - $included);
        $extraPrice  = (float) ($bot->extra_conversation_price_usd ?? 0);
        $planPrice   = (float) ($bot->plan_price_usd ?? 0);
        $cap         = (float) ($bot->overage_cap_usd ?? 0);

        $extrasCost  = $bot->allow_overage ? $extras * $extraPrice : 0.0;
        $capReached  = $bot->allow_overage && $cap > 0 && $extrasCost >= $cap;

        $realCost    = (float) $rows->sum('anthropic_cost') + (float) $rows->sum('whatsapp_cost');
        $total       = $planPrice + $extrasCost;   // lo que se le cobra
        $profit      = $total - $realCost;

        // Sin excedentes habilitados, agotar el cupo PAUSA el bot.
        $exceeded = $capReached || (!$bot->allow_overage && $included > 0 && $used >= $included);

        return compact('period', 'used', 'included', 'extras', 'extrasCost',
            'realCost', 'total', 'profit', 'capReached', 'exceeded');
    }

    /**
     * ¿El bot está bloqueado por cuota/tope este periodo? (el fusible)
     */
    public static function isBlocked(CompanyWhatsappBot $bot): bool
    {
        if (!static::available()) {
            return false;
        }
        return static::billing($bot)['exceeded'];
    }
}
