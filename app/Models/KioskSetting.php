<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Company;

class KioskSetting extends Model
{
    protected $fillable = [
        'company_id', 'event_name', 'logo', 'background_color', 'accent_color',
        'auto_rotate_seconds', 'idle_timeout_seconds', 'show_price', 'show_specs',
        'enable_compare', 'enable_quote', 'enable_qr', 'enable_lead_capture',
        'featured_vehicle_ids', 'excluded_vehicle_ids', 'welcome_message',
        'contact_info', 'status'
    ];

    protected $casts = [
        'show_price' => 'boolean',
        'show_specs' => 'boolean',
        'enable_compare' => 'boolean',
        'enable_quote' => 'boolean',
        'enable_qr' => 'boolean',
        'enable_lead_capture' => 'boolean',
        'status' => 'boolean',
        'featured_vehicle_ids' => 'array',
        'excluded_vehicle_ids' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Obtener configuración activa para una empresa
     */
    public static function getActiveForCompany($companyId)
    {
        return self::where('company_id', $companyId)
            ->where('status', true)
            ->first();
    }

    /**
     * Configuración por defecto
     */
    public static function defaults()
    {
        return [
            'background_color' => '#0b0f14',
            'accent_color' => '#c2ac1f',
            'auto_rotate_seconds' => 15,
            'idle_timeout_seconds' => 60,
            'show_price' => true,
            'show_specs' => true,
            'enable_compare' => true,
            'enable_quote' => true,
            'enable_qr' => true,
            'enable_lead_capture' => true,
        ];
    }

    /**
     * Obtener CSS personalizado
     */
    public function getCustomCssAttribute()
    {
        return ":root {
            --kiosk-bg: {$this->background_color};
            --kiosk-accent: {$this->accent_color};
        }";
    }
}
