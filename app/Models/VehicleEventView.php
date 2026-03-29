<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Vehicle;

class VehicleEventView extends Model
{
    protected $fillable = [
        'vehicle_id', 'session_id', 'source', 'view_duration_seconds',
        'spin_interacted', 'compared', 'quoted', 'lead_captured',
        'device_type', 'event_name'
    ];

    protected $casts = [
        'spin_interacted' => 'boolean',
        'compared' => 'boolean',
        'quoted' => 'boolean',
        'lead_captured' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
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

    // Top vehículos más vistos
    public static function topViewed($eventName = null, $limit = 5)
    {
        $query = self::selectRaw('vehicle_id, COUNT(*) as views, SUM(view_duration_seconds) as total_duration')
            ->groupBy('vehicle_id')
            ->orderByDesc('views')
            ->limit($limit);

        if ($eventName) {
            $query->where('event_name', $eventName);
        }

        return $query->with('vehicle')->get();
    }
}
