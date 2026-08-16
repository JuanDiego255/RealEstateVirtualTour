<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Promociones que el bot puede mencionar. El negocio las administra; el bot
 * solo cita las vigentes (activas y dentro de fechas).
 */
class WhatsappBotPromotion extends Model
{
    protected $fillable = [
        'company_id', 'title', 'description', 'active', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'active'    => 'boolean',
        'starts_at' => 'date',
        'ends_at'   => 'date',
    ];

    public static function available(): bool
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Schema::hasTable('whatsapp_bot_promotions');
        }
        return $cache;
    }

    /**
     * Promociones vigentes de una empresa (activas y dentro del rango de fechas).
     */
    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('active', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $today))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $today));
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
