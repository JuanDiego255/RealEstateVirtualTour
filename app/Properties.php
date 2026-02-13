<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Properties extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'code', 'rooms', 'bathrooms', 'garage',
        'floor_levels', 'construction', 'land', 'construction_year',
        'maintenance', 'price', 'image'
    ];

    /**
     * Get the category this property belongs to
     */
    public function category()
    {
        return $this->belongsTo('App\Category', 'category_id');
    }

    /**
     * Get the scenes for this property
     */
    public function scenes()
    {
        return $this->hasMany('App\Scene', 'property_id');
    }
}
