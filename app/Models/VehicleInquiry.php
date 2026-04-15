<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleInquiry extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'vehicle_description',
    ];
}
