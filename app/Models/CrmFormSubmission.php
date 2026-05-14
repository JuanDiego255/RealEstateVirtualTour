<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmFormSubmission extends Model
{
    protected $fillable = [
        'crm_form_id', 'lead_id', 'data', 'ip_address', 'user_agent', 'referrer',
    ];

    protected $casts = ['data' => 'array'];

    public function form() { return $this->belongsTo(CrmForm::class, 'crm_form_id'); }
    public function lead() { return $this->belongsTo(\App\Lead::class); }
}
