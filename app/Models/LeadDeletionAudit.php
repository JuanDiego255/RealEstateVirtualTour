<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadDeletionAudit extends Model
{
    protected $table = 'lead_deletion_audit';

    protected $fillable = [
        'company_id', 'deleted_by', 'lead_name', 'lead_email',
        'lead_phone', 'lead_status', 'lead_score', 'reason', 'deleted_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function deletedBy()
    {
        return $this->belongsTo(\App\User::class, 'deleted_by');
    }
}
