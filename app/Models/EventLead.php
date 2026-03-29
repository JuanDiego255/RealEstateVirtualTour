<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Vehicle;
use App\Company;

class EventLead extends Model
{
    protected $fillable = [
        'vehicle_id', 'company_id', 'name', 'email', 'phone', 'notes',
        'source', 'event_name', 'interest_level', 'contacted', 'contacted_at',
        'contacted_by', 'vehicles_viewed', 'vehicles_compared', 'quotes_requested',
        'follow_up_status', 'follow_up_date'
    ];

    protected $casts = [
        'contacted' => 'boolean',
        'contacted_at' => 'datetime',
        'follow_up_date' => 'datetime',
        'vehicles_viewed' => 'array',
        'vehicles_compared' => 'array',
        'quotes_requested' => 'array',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Niveles de interés
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
            'qr' => 'Código QR',
            'kiosk' => 'Kiosko',
            'compare' => 'Comparador',
            'quote' => 'Cotización',
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
     * Scope para leads del día
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
     * Agregar vehículo visto
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
     * Estadísticas rápidas
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
