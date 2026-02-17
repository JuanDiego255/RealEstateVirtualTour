<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CommissionRequest extends Model
{
    protected $fillable = [
        'property_id',
        'requester_id',
        'requester_company_id',
        'owner_id',
        'owner_company_id',
        'proposed_commission',
        'final_commission',
        'status',
        'initial_message',
        'responded_at',
        'expires_at',
        'completed_at',
    ];

    protected $casts = [
        'proposed_commission' => 'decimal:2',
        'final_commission' => 'decimal:2',
        'responded_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Estados
     */
    const STATUS_PENDING = 'pending';
    const STATUS_NEGOTIATING = 'negotiating';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Propiedad relacionada
     */
    public function property()
    {
        return $this->belongsTo(Properties::class, 'property_id');
    }

    /**
     * Usuario solicitante
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Empresa solicitante
     */
    public function requesterCompany()
    {
        return $this->belongsTo(Company::class, 'requester_company_id');
    }

    /**
     * Usuario propietario del inmueble
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Empresa propietaria del inmueble
     */
    public function ownerCompany()
    {
        return $this->belongsTo(Company::class, 'owner_company_id');
    }

    /**
     * Mensajes de negociación
     */
    public function negotiations()
    {
        return $this->hasMany(CommissionNegotiation::class)->orderBy('created_at');
    }

    /**
     * Último mensaje de negociación
     */
    public function lastNegotiation()
    {
        return $this->negotiations()->latest()->first();
    }

    /**
     * Venta asociada (si se completó)
     */
    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    /**
     * Obtener nombre del estado
     */
    public function getStatusNameAttribute(): string
    {
        $names = [
            'pending' => 'Pendiente',
            'negotiating' => 'En negociación',
            'accepted' => 'Aceptada',
            'rejected' => 'Rechazada',
            'expired' => 'Expirada',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
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
            'negotiating' => 'info',
            'accepted' => 'success',
            'rejected' => 'danger',
            'expired' => 'secondary',
            'completed' => 'primary',
            'cancelled' => 'dark',
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Verificar si está pendiente
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verificar si está en negociación
     */
    public function isNegotiating(): bool
    {
        return $this->status === self::STATUS_NEGOTIATING;
    }

    /**
     * Verificar si fue aceptada
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Verificar si se puede responder
     */
    public function canRespond(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_NEGOTIATING]);
    }

    /**
     * Verificar si está expirada
     */
    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }
        if ($this->expires_at && $this->expires_at < Carbon::now()) {
            return true;
        }
        return false;
    }

    /**
     * Aceptar solicitud
     */
    public function accept(?float $finalCommission = null): bool
    {
        $this->status = self::STATUS_ACCEPTED;
        $this->final_commission = $finalCommission ?? $this->proposed_commission;
        $this->responded_at = Carbon::now();
        return $this->save();
    }

    /**
     * Rechazar solicitud
     */
    public function reject(): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->responded_at = Carbon::now();
        return $this->save();
    }

    /**
     * Marcar como en negociación
     */
    public function startNegotiation(): bool
    {
        $this->status = self::STATUS_NEGOTIATING;
        $this->responded_at = Carbon::now();
        return $this->save();
    }

    /**
     * Marcar como completada (venta realizada)
     */
    public function complete(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = Carbon::now();
        return $this->save();
    }

    /**
     * Cancelar solicitud
     */
    public function cancel(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    /**
     * Obtener mensajes no leídos para un usuario
     */
    public function getUnreadCountFor(User $user): int
    {
        return $this->negotiations()
            ->where('to_user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Marcar mensajes como leídos
     */
    public function markAsReadFor(User $user): void
    {
        $this->negotiations()
            ->where('to_user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now(),
            ]);
    }

    /**
     * Scope para solicitudes pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope para solicitudes activas (pendientes o en negociación)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_NEGOTIATING]);
    }

    /**
     * Scope para solicitudes aceptadas
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope para un usuario específico (como solicitante o propietario)
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('requester_id', $user->id)
                ->orWhere('owner_id', $user->id);
        });
    }

    /**
     * Calcular comisión estimada
     */
    public function calculateCommission(float $salePrice): array
    {
        $commission = $this->final_commission ?? $this->proposed_commission;
        $totalCommission = $salePrice * ($commission / 100);

        return [
            'percentage' => $commission,
            'total' => $totalCommission,
        ];
    }
}
