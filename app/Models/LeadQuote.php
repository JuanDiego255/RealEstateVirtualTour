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
    ];

    protected $casts = [
        'items'        => 'array',
        'sent_at'      => 'datetime',
        'viewed_at'    => 'datetime',
        'responded_at' => 'datetime',
        'subtotal'     => 'decimal:2',
        'discount_pct' => 'decimal:2',
        'total'        => 'decimal:2',
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

    public function lead()     { return $this->belongsTo(\App\Lead::class); }
    public function property() { return $this->belongsTo(\App\Properties::class, 'property_id'); }
    public function creator()  { return $this->belongsTo(\App\User::class, 'user_id'); }
    public function company()  { return $this->belongsTo(\App\Company::class); }

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
}
