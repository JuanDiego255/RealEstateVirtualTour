<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Properties;

class VehicleColor extends Model
{
    protected $fillable = [
        'property_id', 'vehicle_id', 'color_name', 'color_code', 'preview_image',
        'spin_frames_dir', 'is_default', 'additional_price', 'available', 'order'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'available' => 'boolean',
        'additional_price' => 'decimal:2',
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
     * Obtener colores disponibles para una propiedad (vehiculo)
     */
    public static function getForProperty($propertyId)
    {
        return self::where('property_id', $propertyId)
            ->where('available', true)
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
     * Obtener color por defecto
     */
    public static function getDefault($propertyId)
    {
        return self::where('property_id', $propertyId)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Formato de precio adicional
     */
    public function getFormattedAdditionalPriceAttribute()
    {
        if ($this->additional_price <= 0) {
            return 'Sin costo adicional';
        }
        return '+₡' . number_format($this->additional_price, 0, ',', '.');
    }
}
