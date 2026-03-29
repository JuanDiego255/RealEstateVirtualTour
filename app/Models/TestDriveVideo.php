<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Properties;

class TestDriveVideo extends Model
{
    protected $fillable = [
        'property_id', 'vehicle_id', 'title', 'description', 'video_path', 'thumbnail',
        'engine_audio', 'duration_seconds', 'video_type', 'autoplay',
        'loop', 'status', 'order', 'views_count'
    ];

    protected $casts = [
        'autoplay' => 'boolean',
        'loop' => 'boolean',
        'status' => 'boolean',
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
     * Tipos de video disponibles
     */
    public static function videoTypes()
    {
        return [
            'exterior' => 'Exterior en movimiento',
            'interior' => 'Interior / Cabina',
            'engine' => 'Motor / Arranque',
            'features' => 'Caracteristicas especiales',
            'offroad' => 'Todo terreno',
            'city' => 'Conduccion urbana',
        ];
    }

    /**
     * Obtener videos activos para una propiedad (vehiculo)
     */
    public static function getForProperty($propertyId)
    {
        return self::where('property_id', $propertyId)
            ->where('status', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * Alias para compatibilidad
     */
    public static function getForVehicle($vehicleId)
    {
        return self::getForProperty($vehicleId);
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
     * Duracion formateada
     */
    public function getFormattedDurationAttribute()
    {
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
