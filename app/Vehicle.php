<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'brand', 'model', 'year', 'color',
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
     * Get the scenes for this vehicle
     */
    public function scenes()
    {
        return $this->hasMany('App\Scene', 'vehicle_id');
    }
}
