<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spin extends Model
{
    protected $fillable = [
        'vehicle_id','video_path','frames_dir','frames_count',
        'status','cloudconvert_job_id','error_message',
    ];

    public function jobs()
    {
        return $this->hasMany(SpinJob::class);
    }
}