<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un mensaje de WhatsApp (entrante o saliente) de una empresa.
 */
class WhatsappConversation extends Model
{
    protected $fillable = [
        'company_id', 'phone', 'contact_name', 'direction',
        'message', 'message_type', 'is_human', 'wam_id',
        'window_started_at', 'metadata',
    ];

    protected $casts = [
        'is_human'          => 'boolean',
        'window_started_at' => 'datetime',
        'metadata'          => 'array',
    ];

    const DIRECTION_INBOUND  = 'inbound';
    const DIRECTION_OUTBOUND = 'outbound';

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForPhone($query, string $phone)
    {
        return $query->where('phone', $phone);
    }
}
