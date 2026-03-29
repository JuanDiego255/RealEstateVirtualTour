<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Properties;

class QrScan extends Model
{
    protected $fillable = [
        'property_id', 'vehicle_id', 'qr_code', 'scan_count', 'last_scanned_at',
        'event_name', 'scan_history'
    ];

    protected $casts = [
        'scan_history' => 'array',
        'last_scanned_at' => 'datetime',
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

    /**
     * Generar codigo QR unico para una propiedad (vehiculo)
     */
    public static function generateForProperty($propertyId, $eventName = null)
    {
        $existing = self::where('property_id', $propertyId)
            ->where('event_name', $eventName)
            ->first();

        if ($existing) {
            return $existing;
        }

        return self::create([
            'property_id' => $propertyId,
            'qr_code' => 'VH-' . strtoupper(Str::random(8)),
            'event_name' => $eventName,
            'scan_history' => [],
        ]);
    }

    /**
     * Alias para compatibilidad
     */
    public static function generateForVehicle($vehicleId, $eventName = null)
    {
        return self::generateForProperty($vehicleId, $eventName);
    }

    /**
     * Registrar un escaneo
     */
    public function recordScan()
    {
        $history = $this->scan_history ?? [];
        $history[] = now()->toIso8601String();

        $this->update([
            'scan_count' => $this->scan_count + 1,
            'last_scanned_at' => now(),
            'scan_history' => $history,
        ]);

        return $this;
    }

    /**
     * Obtener URL del tour virtual
     */
    public function getTourUrlAttribute()
    {
        return url("/kiosk/vehicle/{$this->property_id}?qr={$this->qr_code}");
    }

    /**
     * Top vehiculos por escaneos QR
     */
    public static function topScanned($eventName = null, $limit = 5)
    {
        $query = self::whereNotNull('property_id')
            ->orderByDesc('scan_count')
            ->limit($limit);

        if ($eventName) {
            $query->where('event_name', $eventName);
        }

        return $query->with('property')->get();
    }
}
