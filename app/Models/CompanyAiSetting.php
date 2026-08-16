<?php

namespace App\Models;

use App\Company;
use Illuminate\Database\Eloquent\Model;

/**
 * Credenciales y plan de IA (Anthropic) de una empresa. Separado del bot de
 * WhatsApp a propósito: la misma key puede alimentar otras funciones a futuro.
 * La administra el superadmin.
 */
class CompanyAiSetting extends Model
{
    protected $fillable = [
        'company_id', 'enabled', 'api_key', 'model',
        'plan', 'included_generations', 'plan_price_usd', 'extra_generation_price_usd',
        'allow_overage', 'overage_cap_usd',
        'brand_voice', 'audience', 'language', 'max_hashtags', 'system_prompt',
    ];

    protected $casts = [
        'enabled'                    => 'boolean',
        'allow_overage'              => 'boolean',
        'included_generations'       => 'integer',
        'max_hashtags'               => 'integer',
        'plan_price_usd'             => 'decimal:2',
        'extra_generation_price_usd' => 'decimal:4',
        'overage_cap_usd'            => 'decimal:2',
    ];

    protected $hidden = ['api_key'];

    /**
     * Catálogo de modelos disponibles. La etiqueta lleva el precio a propósito.
     */
    public const MODELS = [
        'claude-haiku-4-5' => 'Haiku 4.5 — Rápido y económico ($1/$5 por 1M tokens)',
        'claude-sonnet-5'  => 'Sonnet 5 — Mejor balance calidad/precio ($3/$15) [Recomendado]',
        'claude-opus-4-8'  => 'Opus 4.8 — Máxima calidad ($5/$25 por 1M tokens)',
    ];

    /** Precio por 1M de tokens [input, output] en USD. Mantener a mano. */
    public const PRICING = [
        'claude-haiku-4-5' => [1.00, 5.00],
        'claude-sonnet-5'  => [3.00, 15.00],
        'claude-opus-4-8'  => [5.00, 25.00],
    ];

    /** Margen aplicado sobre el costo real para facturar (50%). */
    public const MARGIN = 0.50;

    const DEFAULT_MODEL = 'claude-sonnet-5';

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Modelo válido (validado contra el catálogo).
     */
    public function modelKey(): string
    {
        return array_key_exists($this->model, self::MODELS) ? $this->model : self::DEFAULT_MODEL;
    }

    /**
     * Costo real de una llamada, en USD.
     */
    public static function costFor(string $model, int $in, int $out): float
    {
        [$pIn, $pOut] = self::PRICING[$model] ?? self::PRICING[self::DEFAULT_MODEL];
        return ($in / 1_000_000) * $pIn + ($out / 1_000_000) * $pOut;
    }
}
