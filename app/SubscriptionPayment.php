<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'subscription_id',
        'amount',
        'currency',
        'payment_method',
        'payment_reference',
        'proof_image',
        'payment_date',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Estados
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Suscripción del pago
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Usuario que revisó el pago
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Obtener monto formateado
     */
    public function getFormattedAmountAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '₡';
        return $symbol . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Obtener nombre del método de pago
     */
    public function getPaymentMethodNameAttribute(): string
    {
        $names = [
            'transfer' => 'Transferencia',
            'sinpe' => 'SINPE Móvil',
            'card' => 'Tarjeta',
        ];
        return $names[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Obtener nombre del estado
     */
    public function getStatusNameAttribute(): string
    {
        $names = [
            'pending' => 'Pendiente',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
        ];
        return $names[$this->status] ?? $this->status;
    }

    /**
     * Obtener color del badge según estado
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * URL del comprobante
     */
    public function getProofUrlAttribute(): ?string
    {
        if ($this->proof_image) {
            return asset('storage/' . $this->proof_image);
        }
        return null;
    }

    /**
     * Verificar si está pendiente
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verificar si fue aprobado
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Verificar si fue rechazado
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Aprobar pago
     */
    public function approve(User $reviewer, ?string $notes = null): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->reviewed_by = $reviewer->id;
        $this->reviewed_at = Carbon::now();
        if ($notes) {
            $this->notes = $notes;
        }
        return $this->save();
    }

    /**
     * Rechazar pago
     */
    public function reject(User $reviewer, string $reason): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->reviewed_by = $reviewer->id;
        $this->reviewed_at = Carbon::now();
        $this->rejection_reason = $reason;
        return $this->save();
    }

    /**
     * Scope para pagos pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope para pagos aprobados
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
