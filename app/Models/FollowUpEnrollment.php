<?php

namespace App\Models;

use App\Lead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * La inscripción de un lead en una secuencia: por dónde va y cuándo toca el
 * próximo envío.
 */
class FollowUpEnrollment extends Model
{
    protected $fillable = [
        'sequence_id', 'lead_id', 'company_id', 'current_position',
        'enrolled_at', 'next_run_at', 'last_sent_at', 'status', 'stopped_reason',
    ];

    protected $casts = [
        'current_position' => 'integer',
        'enrolled_at'      => 'datetime',
        'next_run_at'      => 'datetime',
        'last_sent_at'     => 'datetime',
    ];

    const STATUS_ACTIVE    = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_STOPPED   = 'stopped';

    public function sequence()
    {
        return $this->belongsTo(FollowUpSequence::class, 'sequence_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function scopeDue($q)
    {
        return $q->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now());
    }

    public static function available(): bool
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Schema::hasTable('follow_up_enrollments');
        }
        return $cache;
    }
}
