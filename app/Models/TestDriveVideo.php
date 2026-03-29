<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Vehicle;

class TestDriveVideo extends Model
{
    protected $fillable = [
        'vehicle_id', 'title', 'description', 'video_path', 'thumbnail',
        'engine_audio', 'duration_seconds', 'video_type', 'autoplay',
        'loop', 'status', 'order', 'views_count'
    ];

    protected $casts = [
        'autoplay' => 'boolean',
        'loop' => 'boolean',
        'status' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Tipos de video disponibles
     */
    public static function videoTypes()
    {
        return [
            'exterior' => 'Exterior en movimiento',
            'interior' => 'Interior / Cabina',
            'engine' => 'Motor / Arranque',
            'features' => 'Características especiales',
            'offroad' => 'Todo terreno',
            'city' => 'Conducción urbana',
        ];
    }

    /**
     * Obtener videos activos para un vehículo
     */
    public static function getForVehicle($vehicleId)
    {
        return self::where('vehicle_id', $vehicleId)
            ->where('status', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * Incrementar contador de vistas
     */
    public function incrementViews()
    {
        $this->increment('views_count');
        return $this;
    }

    /**
     * Duración formateada
     */
    public function getFormattedDurationAttribute()
    {
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
