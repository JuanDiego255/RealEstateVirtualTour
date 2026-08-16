<?php

namespace App\Models;

use App\Company;
use Illuminate\Database\Eloquent\Model;

/**
 * Configuración técnica del bot de WhatsApp de una empresa (la administra el
 * superadmin). Contiene las credenciales de Meta, el plan y las reglas de
 * cuándo responde. Es el equivalente company-scoped de tenant_whatsapp_bots.
 */
class CompanyWhatsappBot extends Model
{
    protected $fillable = [
        'company_id', 'enabled',
        'phone_number_id', 'waba_id', 'display_phone',
        'access_token', 'app_secret', 'verify_token', 'graph_version', 'business_type',
        'plan', 'included_conversations', 'plan_price_usd', 'extra_conversation_price_usd',
        'allow_overage', 'overage_cap_usd', 'max_vehicles_per_reply',
        'activation_mode', 'delay_minutes', 'business_hours_start', 'business_hours_end',
        'instant_outside_hours', 'notes',
    ];

    protected $casts = [
        'enabled'                      => 'boolean',
        'allow_overage'                => 'boolean',
        'instant_outside_hours'        => 'boolean',
        'included_conversations'       => 'integer',
        'max_vehicles_per_reply'       => 'integer',
        'delay_minutes'                => 'integer',
        'plan_price_usd'               => 'decimal:2',
        'extra_conversation_price_usd' => 'decimal:4',
        'overage_cap_usd'              => 'decimal:2',
    ];

    protected $hidden = [
        'access_token', 'app_secret', 'verify_token',
    ];

    const MODE_IMMEDIATE = 'immediate';
    const MODE_DELAYED   = 'delayed';
    const MODE_MANUAL    = 'manual';

    const ACTIVATION_MODES = [
        self::MODE_IMMEDIATE => 'Responder de inmediato',
        self::MODE_DELAYED   => 'Esperar unos minutos (dar chance a una persona)',
        self::MODE_MANUAL    => 'Solo manual (nunca se activa solo)',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * ¿Está listo para recibir y responder mensajes?
     */
    public function isUsable(): bool
    {
        return (bool) ($this->enabled && $this->phone_number_id && $this->access_token);
    }

    /**
     * Versión del Graph API a usar (config global si no hay override).
     */
    public function graphVersion(): string
    {
        return $this->graph_version ?: config('whatsapp.graph_version', 'v21.0');
    }

    /**
     * Resolver el bot dueño de un phone_number_id (llave del webhook).
     */
    public static function resolveByPhoneNumberId(?string $phoneNumberId): ?self
    {
        if (empty($phoneNumberId)) {
            return null;
        }
        return static::where('phone_number_id', $phoneNumberId)->first();
    }
}
