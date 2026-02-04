<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScenePolygon extends Model
{
    protected $table = 'scene_polygons';

    protected $fillable = [
        'scene_id',
        'name',
        'fill_color',
        'fill_opacity',
        'stroke_color',
        'stroke_width',
        'points',
        'edge_labels',
        'interior_text'
    ];

    protected $casts = [
        'points' => 'array',
        'edge_labels' => 'array',
        'fill_opacity' => 'float',
        'stroke_width' => 'integer'
    ];

    public function scene()
    {
        return $this->belongsTo('App\Scene', 'scene_id');
    }
}
