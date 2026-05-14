<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeadTask extends Model
{
    protected $fillable = [
        'lead_id', 'company_id', 'created_by', 'assigned_to',
        'title', 'description', 'priority', 'status', 'type',
        'due_at', 'completed_at', 'completion_notes',
    ];

    protected $casts = [
        'due_at'       => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function lead()       { return $this->belongsTo(Lead::class); }
    public function creator()    { return $this->belongsTo(\App\User::class, 'created_by'); }
    public function assignee()   { return $this->belongsTo(\App\User::class, 'assigned_to'); }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopePending($q)    { return $q->where('status', 'pending'); }
    public function scopeOverdue($q)    { return $q->where('status', '!=', 'completed')->where('due_at', '<', now()); }
    public function scopeDueToday($q)   { return $q->whereDate('due_at', today()); }

    // ── Accessors ─────────────────────────────────────────────────
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_at && $this->due_at->isPast() && $this->status !== 'completed' && $this->status !== 'cancelled';
    }

    public function getIsDueTodayAttribute(): bool
    {
        return $this->due_at && $this->due_at->isToday() && !in_array($this->status, ['completed', 'cancelled']);
    }

    // ── Static helpers ────────────────────────────────────────────
    public static function getStatuses(): array
    {
        return [
            'pending'    => 'Pendiente',
            'in_progress'=> 'En progreso',
            'completed'  => 'Completada',
            'cancelled'  => 'Cancelada',
        ];
    }

    public static function getTypes(): array
    {
        return [
            'call'     => ['label' => 'Llamada',      'icon' => 'fa-phone',          'color' => 'blue'],
            'email'    => ['label' => 'Email',         'icon' => 'fa-envelope',       'color' => 'indigo'],
            'whatsapp' => ['label' => 'WhatsApp',      'icon' => 'fa-whatsapp',       'color' => 'green'],
            'visit'    => ['label' => 'Visita',        'icon' => 'fa-map-marker',     'color' => 'orange'],
            'meeting'  => ['label' => 'Reunión',       'icon' => 'fa-users',          'color' => 'purple'],
            'document' => ['label' => 'Documento',     'icon' => 'fa-file-text-o',    'color' => 'yellow'],
            'other'    => ['label' => 'Otro',          'icon' => 'fa-tasks',          'color' => 'gray'],
        ];
    }

    public static function getPriorities(): array
    {
        return [
            'low'    => 'Baja',
            'medium' => 'Media',
            'high'   => 'Alta',
            'urgent' => 'Urgente',
        ];
    }
}
