<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Properties;
use App\Company;

class EventLead extends Model
{
    protected $fillable = [
        'property_id', 'vehicle_id', 'company_id', 'captured_by_user_id',
        'name', 'email', 'phone', 'notes', 'description', 'photo_path',
        'source', 'event_name', 'interest_level', 'lead_category', 'sale_status',
        'contacted', 'contacted_at', 'contacted_by', 'converted_at',
        'vehicles_viewed', 'vehicles_compared', 'quotes_requested',
        'follow_up_status', 'follow_up_date',
    ];

    protected $casts = [
        'contacted'    => 'boolean',
        'contacted_at' => 'datetime',
        'converted_at' => 'datetime',
        'follow_up_date' => 'datetime',
        'vehicles_viewed'   => 'array',
        'vehicles_compared' => 'array',
        'quotes_requested'  => 'array',
    ];

    /**
     * Relacion con property (vehiculo)
     */
    public function property()
    {
        return $this->belongsTo(Properties::class, 'property_id');
    }

    /**
     * Alias para compatibilidad
     */
    public function vehicle()
    {
        return $this->property();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function capturedBy()
    {
        return $this->belongsTo(\App\User::class, 'captured_by_user_id');
    }

    public function followups()
    {
        return $this->hasMany(LeadFollowup::class)->latest();
    }

    /**
     * Categorías de prospecto
     */
    public static function leadCategories(): array
    {
        return [
            'prospect'        => ['label' => 'Prospecto',         'color' => 'success',  'icon' => 'fa-star',         'desc' => 'Posible venta activa'],
            'exploring'       => ['label' => 'Explorando',        'color' => 'info',     'icon' => 'fa-search',       'desc' => 'Solo revisando opciones'],
            'comparing'       => ['label' => 'Comparando',        'color' => 'primary',  'icon' => 'fa-balance-scale','desc' => 'Comparando precios/modelos'],
            'future_interest' => ['label' => 'Interés a futuro',  'color' => 'warning',  'icon' => 'fa-clock-o',      'desc' => 'Interesado pero no ahora'],
            'referral'        => ['label' => 'Referido',          'color' => 'purple',   'icon' => 'fa-share-alt',    'desc' => 'Fue referido por otro cliente'],
            'returning'       => ['label' => 'Cliente frecuente', 'color' => 'dark',     'icon' => 'fa-repeat',       'desc' => 'Ya ha comprado antes'],
        ];
    }

    /**
     * Estados de la venta
     */
    public static function saleStatuses(): array
    {
        return [
            'open'        => ['label' => 'Abierto',       'color' => 'secondary', 'icon' => 'fa-circle-o'],
            'in_progress' => ['label' => 'En proceso',    'color' => 'info',      'icon' => 'fa-spinner'],
            'converted'   => ['label' => 'Convertido',    'color' => 'success',   'icon' => 'fa-check-circle'],
            'lost'        => ['label' => 'Perdido',        'color' => 'danger',    'icon' => 'fa-times-circle'],
            'dormant'     => ['label' => 'Dormido',        'color' => 'warning',   'icon' => 'fa-moon-o'],
        ];
    }

    /**
     * Marcar lead como convertido (venta concretada)
     */
    public function markAsConverted(?string $notes = null): self
    {
        $this->update([
            'sale_status'  => 'converted',
            'converted_at' => now(),
        ]);
        if ($notes) {
            $this->followups()->create([
                'user_id'  => auth()->id(),
                'action'   => 'other',
                'outcome'  => 'converted',
                'notes'    => $notes,
            ]);
        }
        return $this;
    }

    /**
     * Marcar lead como perdido
     */
    public function markAsLost(?string $notes = null): self
    {
        $this->update(['sale_status' => 'lost']);
        if ($notes) {
            $this->followups()->create([
                'user_id'  => auth()->id(),
                'action'   => 'other',
                'outcome'  => 'lost',
                'notes'    => $notes,
            ]);
        }
        return $this;
    }

    /**
     * Niveles de interes
     */
    public static function interestLevels()
    {
        return [
            'low' => ['label' => 'Bajo', 'color' => 'secondary', 'icon' => 'thermometer-empty'],
            'medium' => ['label' => 'Medio', 'color' => 'info', 'icon' => 'thermometer-half'],
            'high' => ['label' => 'Alto', 'color' => 'warning', 'icon' => 'thermometer-three-quarters'],
            'hot' => ['label' => 'Muy interesado', 'color' => 'danger', 'icon' => 'fire'],
        ];
    }

    /**
     * Fuentes de leads
     */
    public static function sources()
    {
        return [
            'event' => 'Evento presencial',
            'qr' => 'Codigo QR',
            'kiosk' => 'Kiosko',
            'compare' => 'Comparador',
            'quote' => 'Cotizacion',
        ];
    }

    /**
     * Scope para leads pendientes de seguimiento
     */
    public function scopePendingFollowUp($query)
    {
        return $query->where('follow_up_status', 'pending')
            ->where('contacted', false);
    }

    /**
     * Scope para leads del dia
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Marcar como contactado
     */
    public function markAsContacted($contactedBy = null)
    {
        $this->update([
            'contacted' => true,
            'contacted_at' => now(),
            'contacted_by' => $contactedBy,
            'follow_up_status' => 'completed',
        ]);

        return $this;
    }

    /**
     * Agregar vehiculo visto
     */
    public function addViewedVehicle($vehicleId)
    {
        $viewed = $this->vehicles_viewed ?? [];
        if (!in_array($vehicleId, $viewed)) {
            $viewed[] = $vehicleId;
            $this->update(['vehicles_viewed' => $viewed]);
        }
        return $this;
    }

    /**
     * Estadisticas rapidas
     */
    public static function eventStats($eventName)
    {
        return [
            'total' => self::where('event_name', $eventName)->count(),
            'today' => self::where('event_name', $eventName)->today()->count(),
            'hot' => self::where('event_name', $eventName)->where('interest_level', 'hot')->count(),
            'pending' => self::where('event_name', $eventName)->pendingFollowUp()->count(),
            'contacted' => self::where('event_name', $eventName)->where('contacted', true)->count(),
        ];
    }
}
