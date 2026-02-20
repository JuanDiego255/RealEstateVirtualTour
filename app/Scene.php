<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    protected $table = 'scenes';

    protected $fillable = [
        'title',
        'type',
        'hfov',
        'yaw',
        'pitch',
        'image',
        'video',
        'status',
        'property_id',
        'vehicle_id',
        'image_ref',
        'spin_id'
    ];

    public function hotspots()
    {
        return $this->hasMany('App\Hotspot', 'sourceScene');
    }

    public function polygons()
    {
        return $this->hasMany('App\ScenePolygon', 'scene_id');
    }

    /**
     * Get the property this scene belongs to
     */
    public function property()
    {
        return $this->belongsTo('App\Properties', 'property_id');
    }

    /**
     * Get the vehicle this scene belongs to
     */
    public function vehicle()
    {
        return $this->belongsTo('App\Vehicle', 'vehicle_id');
    }

    public function spin()
    {
        return $this->belongsTo(\App\Models\Spin::class, 'spin_id');
    }
}
