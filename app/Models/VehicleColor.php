<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Vehicle;

class VehicleColor extends Model
{
    protected $fillable = [
        'vehicle_id', 'color_name', 'color_code', 'preview_image',
        'spin_frames_dir', 'is_default', 'additional_price', 'available', 'order'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'available' => 'boolean',
        'additional_price' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Obtener colores disponibles para un vehículo
     */
    public static function getForVehicle($vehicleId)
    {
        return self::where('vehicle_id', $vehicleId)
            ->where('available', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * Obtener color por defecto
     */
    public static function getDefault($vehicleId)
    {
        return self::where('vehicle_id', $vehicleId)
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
