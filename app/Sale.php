<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sale extends Model
{
    protected $fillable = [
        'property_id',
        'seller_id',
        'seller_company_id',
        'external_agent_id',
        'external_company_id',
        'commission_request_id',
        'sale_price',
        'currency',
        'total_commission_percentage',
        'total_commission_amount',
        'owner_share_percentage',
        'owner_share_amount',
        'agent_share_percentage',
        'agent_share_amount',
        'buyer_name',
        'buyer_phone',
        'buyer_email',
        'buyer_id_number',
        'sale_date',
        'notes',
        'status',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'total_commission_percentage' => 'decimal:2',
        'total_commission_amount' => 'decimal:2',
        'owner_share_percentage' => 'decimal:2',
        'owner_share_amount' => 'decimal:2',
        'agent_share_percentage' => 'decimal:2',
        'agent_share_amount' => 'decimal:2',
        'sale_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Estados
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Propiedad vendida
     */
    public function property()
    {
        return $this->belongsTo(Properties::class, 'property_id');
    }

    /**
     * Vendedor (dueño del inmueble)
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Empresa vendedora
     */
    public function sellerCompany()
    {
        return $this->belongsTo(Company::class, 'seller_company_id');
    }

    /**
     * Agente externo (si aplica)
     */
    public function externalAgent()
    {
        return $this->belongsTo(User::class, 'external_agent_id');
    }

    /**
     * Empresa del agente externo
     */
    public function externalCompany()
    {
        return $this->belongsTo(Company::class, 'external_company_id');
    }

    /**
     * Solicitud de comisión relacionada
     */
    public function commissionRequest()
    {
        return $this->belongsTo(CommissionRequest::class);
    }

    /**
     * Usuario que confirmó la venta
     */
    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Obtener precio de venta formateado
     */
    public function getFormattedSalePriceAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '₡';
        return $symbol . number_format($this->sale_price, 0, ',', '.');
    }

    /**
     * Obtener comisión total formateada
     */
    public function getFormattedCommissionAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '₡';
        return $symbol . number_format($this->total_commission_amount, 0, ',', '.');
    }

    /**
     * Obtener monto del propietario formateado
     */
    public function getFormattedOwnerShareAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '₡';
        return $symbol . number_format($this->owner_share_amount, 0, ',', '.');
    }

    /**
     * Obtener monto del agente formateado
     */
    public function getFormattedAgentShareAttribute(): string
    {
        if (!$this->agent_share_amount) {
            return 'N/A';
        }
        $symbol = $this->currency === 'USD' ? '$' : '₡';
        return $symbol . number_format($this->agent_share_amount, 0, ',', '.');
    }

    /**
     * Obtener nombre del estado
     */
    public function getStatusNameAttribute(): string
    {
        $names = [
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmada',
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
            'confirmed' => 'success',
            'cancelled' => 'danger',
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Verificar si tiene agente externo
     */
    public function hasExternalAgent(): bool
    {
        return $this->external_agent_id !== null;
    }

    /**
     * Verificar si está pendiente
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verificar si está confirmada
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Confirmar venta
     */
    public function confirm(User $confirmer): bool
    {
        $this->status = self::STATUS_CONFIRMED;
        $this->confirmed_by = $confirmer->id;
        $this->confirmed_at = Carbon::now();

        if ($this->save()) {
            // Marcar la propiedad como vendida
            $this->property->update([
                'status' => 'sold',
                'sold_at' => $this->sale_date,
            ]);

            // Completar la solicitud de comisión si existe
            if ($this->commissionRequest) {
                $this->commissionRequest->complete();
            }

            return true;
        }
        return false;
    }

    /**
     * Cancelar venta
     */
    public function cancel(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    /**
     * Calcular distribución de comisiones
     *
     * @param float $salePrice Precio de venta
     * @param float $totalCommission % de comisión total
     * @param float|null $agentShare % para el agente externo (del total de la comisión)
     */
    public static function calculateCommissionSplit(
        float $salePrice,
        float $totalCommission,
        ?float $agentShare = null
    ): array {
        $totalAmount = $salePrice * ($totalCommission / 100);

        if ($agentShare === null) {
            return [
                'total_commission_percentage' => $totalCommission,
                'total_commission_amount' => $totalAmount,
                'owner_share_percentage' => 100,
                'owner_share_amount' => $totalAmount,
                'agent_share_percentage' => null,
                'agent_share_amount' => null,
            ];
        }

        $ownerSharePercent = 100 - $agentShare;
        $agentAmount = $totalAmount * ($agentShare / 100);
        $ownerAmount = $totalAmount * ($ownerSharePercent / 100);

        return [
            'total_commission_percentage' => $totalCommission,
            'total_commission_amount' => $totalAmount,
            'owner_share_percentage' => $ownerSharePercent,
            'owner_share_amount' => $ownerAmount,
            'agent_share_percentage' => $agentShare,
            'agent_share_amount' => $agentAmount,
        ];
    }

    /**
     * Scope para ventas confirmadas
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope para ventas pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope para una empresa específica
     */
    public function scopeForCompany($query, Company $company)
    {
        return $query->where(function ($q) use ($company) {
            $q->where('seller_company_id', $company->id)
                ->orWhere('external_company_id', $company->id);
        });
    }

    /**
     * Scope para un período específico
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }
}
