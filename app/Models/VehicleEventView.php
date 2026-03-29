<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Properties;

class VehicleEventView extends Model
{
    protected $fillable = [
        'property_id', 'vehicle_id', 'session_id', 'source', 'view_duration_seconds',
        'spin_interacted', 'compared', 'quoted', 'lead_captured',
        'device_type', 'event_name'
    ];

    protected $casts = [
        'spin_interacted' => 'boolean',
        'compared' => 'boolean',
        'quoted' => 'boolean',
        'lead_captured' => 'boolean',
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

    // Scope para filtrar por evento
    public function scopeForEvent($query, $eventName)
    {
        return $query->where('event_name', $eventName);
    }

    // Scope para filtrar por fuente
    public function scopeFromSource($query, $source)
    {
        return $query->where('source', $source);
    }

    // Top vehiculos mas vistos
    public static function topViewed($eventName = null, $limit = 5)
    {
        $query = self::selectRaw('property_id, COUNT(*) as views, SUM(view_duration_seconds) as total_duration')
            ->whereNotNull('property_id')
            ->groupBy('property_id')
            ->orderByDesc('views')
            ->limit($limit);

        if ($eventName) {
            $query->where('event_name', $eventName);
        }

        return $query->with('property')->get();
    }
}
