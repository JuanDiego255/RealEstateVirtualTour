<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Subcategory;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'subcategory_id', 'name', 'brand', 'model', 'year', 'color',
        'mileage_km', 'fuel_tank_capacity', 'fuel_type', 'engine_cc',
        'doors', 'passengers', 'tires', 'drivetrain', 'transmission',
        'price', 'condition', 'plate', 'image', 'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the category this vehicle belongs to
     */
    public function category()
    {
        return $this->belongsTo('App\Category', 'category_id');
    }

    /**
     * Subcategoría del vehículo
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Get the scenes for this vehicle
     */
    public function scenes()
    {
        return $this->hasMany('App\Scene', 'vehicle_id');
    }

    /**
     * Obtener el spin activo a través de las escenas (para compatibilidad con vista de subcategoría)
     */
    public function getActiveSpinAttribute()
    {
        // Buscar escena con spin_id asignado
        $scene = $this->scenes()->whereNotNull('spin_id')->first();
        if (!$scene) return null;

        // Buscar el spin con estado 'ready'
        $spin = \App\Models\Spin::where('id', $scene->spin_id)
            ->where('status', 'ready')
            ->first();

        return $spin;
    }

    /**
     * Tipo de propiedad para compatibilidad con vista unificada
     */
    public function getPropertyTypeAttribute()
    {
        return 'vehicle';
    }

    /**
     * Precio formateado para compatibilidad con vista unificada
     */
    public function getFormattedPriceAttribute(): string
    {
        $symbol = ($this->currency ?? 'CRC') === 'USD' ? '$' : '₡';
        return $symbol . number_format((float)$this->price, 0, ',', '.');
    }
}
