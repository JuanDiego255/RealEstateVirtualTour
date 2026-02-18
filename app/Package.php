<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_period',
        'max_users',
        'max_categories',
        'max_posts_per_category',
        'max_subcategories_per_category',
        'tour_price',
        'allows_tours',
        'included_tours',
        'allows_commission',
        'trial_days',
        'features',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tour_price' => 'decimal:2',
        'allows_tours' => 'boolean',
        'allows_commission' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    /**
     * Períodos de facturación
     */
    const BILLING_MONTHLY = 'monthly';
    const BILLING_QUARTERLY = 'quarterly';
    const BILLING_YEARLY = 'yearly';

    /**
     * Suscripciones de este paquete
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Scope para paquetes activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para paquetes destacados
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope ordenados
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * Obtener precio formateado
     */
    public function getFormattedPriceAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '₡';
        return $symbol . number_format($this->price, 0, ',', '.');
    }

    /**
     * Obtener precio del tour formateado
     */
    public function getFormattedTourPriceAttribute(): string
    {
        if ($this->tour_price === null || $this->tour_price == 0) {
            return 'Incluido';
        }
        $symbol = $this->currency === 'USD' ? '$' : '₡';
        return $symbol . number_format($this->tour_price, 0, ',', '.');
    }

    /**
     * Obtener nombre del período de facturación
     */
    public function getBillingPeriodNameAttribute(): string
    {
        $names = [
            'monthly' => 'mes',
            'quarterly' => 'trimestre',
            'yearly' => 'año',
        ];
        return $names[$this->billing_period] ?? $this->billing_period;
    }

    /**
     * Verificar si tiene feature específico
     */
    public function hasFeature(string $feature): bool
    {
        return isset($this->features[$feature]) && $this->features[$feature];
    }

    /**
     * Obtener valor de feature
     */
    public function getFeature(string $feature, $default = null)
    {
        return $this->features[$feature] ?? $default;
    }
}
