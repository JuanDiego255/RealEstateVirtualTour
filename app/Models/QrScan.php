<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Vehicle;

class QrScan extends Model
{
    protected $fillable = [
        'vehicle_id', 'qr_code', 'scan_count', 'last_scanned_at',
        'event_name', 'scan_history'
    ];

    protected $casts = [
        'scan_history' => 'array',
        'last_scanned_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Generar código QR único para un vehículo
     */
    public static function generateForVehicle($vehicleId, $eventName = null)
    {
        $existing = self::where('vehicle_id', $vehicleId)
            ->where('event_name', $eventName)
            ->first();

        if ($existing) {
            return $existing;
        }

        return self::create([
            'vehicle_id' => $vehicleId,
            'qr_code' => 'VH-' . strtoupper(Str::random(8)),
            'event_name' => $eventName,
            'scan_history' => [],
        ]);
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
        return url("/kiosk/vehicle/{$this->vehicle_id}?qr={$this->qr_code}");
    }

    /**
     * Top vehículos por escaneos QR
     */
    public static function topScanned($eventName = null, $limit = 5)
    {
        $query = self::orderByDesc('scan_count')->limit($limit);

        if ($eventName) {
            $query->where('event_name', $eventName);
        }

        return $query->with('vehicle')->get();
    }
}
