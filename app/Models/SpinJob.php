<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinJob extends Model
{
    protected $fillable = [
        'spin_id','provider','provider_job_id','status',
        'attempts','last_attempt_at','last_error',
    ];

    public function spin()
    {
        return $this->belongsTo(Spin::class);
    }
}