<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadQuote extends Model
{
    protected $fillable = [
        'company_id', 'lead_id', 'user_id', 'property_id',
        'quote_number', 'title', 'notes', 'validity_days', 'currency',
        'items', 'subtotal', 'discount_pct', 'total', 'status',
        'sent_at', 'viewed_at', 'responded_at',
        'quote_type', 'vehicle_quote_id',
        'vq_vehicle_price', 'vq_down_payment', 'vq_down_payment_pct',
        'vq_term_months', 'vq_interest_rate', 'vq_payment_frequency',
        'vq_monthly_payment', 'vq_total_interest', 'vq_total_amount',
    ];

    protected $casts = [
        'items'               => 'array',
        'sent_at'             => 'datetime',
        'viewed_at'           => 'datetime',
        'responded_at'        => 'datetime',
        'subtotal'            => 'decimal:2',
        'discount_pct'        => 'decimal:2',
        'total'               => 'decimal:2',
        'quote_type'          => 'string',
        'vq_vehicle_price'    => 'decimal:2',
        'vq_down_payment'     => 'decimal:2',
        'vq_down_payment_pct' => 'decimal:2',
        'vq_interest_rate'    => 'decimal:4',
        'vq_monthly_payment'  => 'decimal:2',
        'vq_total_interest'   => 'decimal:2',
        'vq_total_amount'     => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($quote) {
            // Generate quote number after insert (use a counter per company)
            $count = static::where('company_id', $quote->company_id)->count() + 1;
            $quote->quote_number = 'COT-' . str_pad($count, 5, '0', STR_PAD_LEFT);
        });
    }

    public function lead()         { return $this->belongsTo(\App\Lead::class); }
    public function property()     { return $this->belongsTo(\App\Properties::class, 'property_id'); }
    public function creator()      { return $this->belongsTo(\App\User::class, 'user_id'); }
    public function company()      { return $this->belongsTo(\App\Company::class); }
    public function vehicleQuote() { return $this->belongsTo(\App\Models\VehicleQuote::class, 'vehicle_quote_id'); }

    public static function getStatuses(): array
    {
        return [
            'draft'    => 'Borrador',
            'sent'     => 'Enviada',
            'viewed'   => 'Vista',
            'accepted' => 'Aceptada',
            'rejected' => 'Rechazada',
        ];
    }

    public static function getStatusColors(): array
    {
        return [
            'draft'    => 'secondary',
            'sent'     => 'primary',
            'viewed'   => 'info',
            'accepted' => 'won',
            'rejected' => 'lost',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::getStatusColors()[$this->status] ?? 'secondary';
    }

    public function getFormattedTotalAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '₡';
        return $symbol . ' ' . number_format($this->total, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '₡';
        return $symbol . ' ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getValidUntilAttribute(): string
    {
        return $this->created_at
            ? $this->created_at->addDays($this->validity_days)->format('d/m/Y')
            : '—';
    }

    public function getFormattedVqMonthlyPaymentAttribute(): string
    {
        if (!$this->vq_monthly_payment) return '—';
        $symbol = $this->currency === 'USD' ? '$' : '₡';
        return $symbol . number_format((float)$this->vq_monthly_payment, 2, '.', ',');
    }
}
