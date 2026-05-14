<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PipelineRule extends Model
{
    protected $fillable = [
        'company_id', 'name', 'trigger_stage',
        'days_inactive', 'action', 'action_message', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function company() { return $this->belongsTo(\App\Company::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }

    public static function getActions(): array
    {
        return [
            'alert'           => 'Mostrar alerta en el lead',
            'create_reminder' => 'Crear recordatorio automático',
            'notify_admin'    => 'Notificar al administrador',
        ];
    }

    /**
     * Check if a lead triggers this rule.
     */
    public function triggersFor(\App\Lead $lead): bool
    {
        if ($lead->status !== $this->trigger_stage) {
            return false;
        }
        $since = $lead->stage_entered_at ?? $lead->created_at;
        $daysSinceEntry = $since->diffInDays(now());

        // Check for recent activity
        $lastActivity = $lead->activities()->latest('activity_at')->first();
        $daysSinceActivity = $lastActivity
            ? $lastActivity->activity_at->diffInDays(now())
            : $daysSinceEntry;

        return $daysSinceActivity >= $this->days_inactive;
    }
}
