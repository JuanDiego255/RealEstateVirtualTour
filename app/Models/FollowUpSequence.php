<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Secuencia de seguimiento (nurturing): una serie de pasos que se envían con
 * demoras para no perder al lead. Se dispara al crear el lead o a mano.
 */
class FollowUpSequence extends Model
{
    protected $fillable = [
        'company_id', 'name', 'trigger', 'is_active', 'stop_on_reply',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'stop_on_reply' => 'boolean',
    ];

    const TRIGGER_LEAD_CREATED = 'lead_created';
    const TRIGGER_MANUAL       = 'manual';

    public function steps()
    {
        return $this->hasMany(FollowUpStep::class, 'sequence_id')->orderBy('position');
    }

    public function enrollments()
    {
        return $this->hasMany(FollowUpEnrollment::class, 'sequence_id');
    }

    public function scopeForCompany($q, int $companyId)
    {
        return $q->where('company_id', $companyId);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public static function available(): bool
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Schema::hasTable('follow_up_sequences');
        }
        return $cache;
    }
}
