<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    protected $fillable = [
        'company_id',
        'package_id',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'status',
        'payment_method',
        'auto_renew',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'trial_ends_at' => 'date',
        'approved_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    /**
     * Estados
     */
    const STATUS_PENDING = 'pending';
    const STATUS_TRIAL = 'trial';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Métodos de pago
     */
    const PAYMENT_TRANSFER = 'transfer';
    const PAYMENT_SINPE = 'sinpe';
    const PAYMENT_CARD = 'card';

    /**
     * Empresa de la suscripción
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Paquete de la suscripción
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Usuario que creó la suscripción
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que aprobó la suscripción
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Pagos de la suscripción
     */
    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    /**
     * Último pago
     */
    public function lastPayment()
    {
        return $this->payments()->latest()->first();
    }

    /**
     * Verificar si está activa
     */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_TRIAL])
            && $this->ends_at >= Carbon::now();
    }

    /**
     * Verificar si está en período de prueba
     */
    public function isOnTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL
            && $this->trial_ends_at
            && $this->trial_ends_at >= Carbon::now();
    }

    /**
     * Verificar si está expirada
     */
    public function isExpired(): bool
    {
        return $this->ends_at < Carbon::now();
    }

    /**
     * Verificar si está por vencer (próximos 7 días)
     */
    public function isExpiringSoon(): bool
    {
        return $this->ends_at <= Carbon::now()->addDays(7)
            && $this->ends_at > Carbon::now();
    }

    /**
     * Días restantes
     */
    public function getDaysRemainingAttribute(): int
    {
        return max(0, Carbon::now()->diffInDays($this->ends_at, false));
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
            'trial' => 'Período de prueba',
            'active' => 'Activa',
            'expired' => 'Expirada',
            'cancelled' => 'Cancelada',
            'suspended' => 'Suspendida',
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
            'trial' => 'info',
            'active' => 'success',
            'expired' => 'danger',
            'cancelled' => 'secondary',
            'suspended' => 'dark',
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Scope para suscripciones activas
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_TRIAL])
            ->where('ends_at', '>=', Carbon::now());
    }

    /**
     * Scope para suscripciones pendientes de aprobación
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope para suscripciones por vencer
     */
    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_TRIAL])
            ->where('ends_at', '<=', Carbon::now()->addDays($days))
            ->where('ends_at', '>', Carbon::now());
    }

    /**
     * Aprobar suscripción
     */
    public function approve(User $approver): bool
    {
        $this->status = self::STATUS_ACTIVE;
        $this->approved_by = $approver->id;
        $this->approved_at = Carbon::now();
        return $this->save();
    }

    /**
     * Iniciar período de prueba
     */
    public function startTrial(): bool
    {
        if (!$this->package->trial_days) {
            return false;
        }
        $this->status = self::STATUS_TRIAL;
        $this->trial_ends_at = Carbon::now()->addDays($this->package->trial_days);
        $this->starts_at = Carbon::now();
        $this->ends_at = $this->trial_ends_at;
        return $this->save();
    }
}
