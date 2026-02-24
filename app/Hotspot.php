<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hotspot extends Model
{
    protected $table = 'hotspots';
    protected $fillable = [
        'title', 'type', 'yaw', 'pitch', 'video_time', 'pos_x', 'pos_y', 'info', 'sourceScene', 'targetScene', 'target_yaw', 'target_pitch', 'image'
    ];

    public function scene()
    {
        return $this->belongsTo('App\Scene', 'sourceScene');
    }

    public function targetScene()
    {
        return $this->belongsTo('App\Scene', 'targetScene');
    }
}
