<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeadTask extends Model
{
    protected $fillable = [
        'company_id', 'lead_id', 'created_by', 'assigned_to',
        'title', 'type', 'priority', 'status',
        'description', 'due_at', 'completed_at', 'completion_notes',
    ];

    protected $casts = [
        'due_at'       => 'datetime',
        'completed_at' => 'datetime',
    ];

    public static function getTypes(): array
    {
        return [
            'call'     => ['label' => 'Llamada',   'icon' => 'fa-phone',        'color' => '#3b82f6'],
            'email'    => ['label' => 'Email',      'icon' => 'fa-envelope',     'color' => '#6366f1'],
            'whatsapp' => ['label' => 'WhatsApp',   'icon' => 'fa-whatsapp',     'color' => '#25d366'],
            'visit'    => ['label' => 'Visita',     'icon' => 'fa-map-marker',   'color' => '#f59e0b'],
            'meeting'  => ['label' => 'Reunión',    'icon' => 'fa-users',        'color' => '#8b5cf6'],
            'document' => ['label' => 'Documento',  'icon' => 'fa-file-text-o',  'color' => '#64748b'],
            'other'    => ['label' => 'Otro',       'icon' => 'fa-tasks',        'color' => '#94a3b8'],
        ];
    }

    public static function getPriorities(): array
    {
        return [
            'low'    => ['label' => 'Baja',    'color' => 'secondary'],
            'medium' => ['label' => 'Media',   'color' => 'info'],
            'high'   => ['label' => 'Alta',    'color' => 'warning'],
            'urgent' => ['label' => 'Urgente', 'color' => 'danger'],
        ];
    }

    public static function getStatuses(): array
    {
        return [
            'pending'     => 'Pendiente',
            'in_progress' => 'En Proceso',
            'completed'   => 'Completada',
            'cancelled'   => 'Cancelada',
        ];
    }

    // Relationships
    public function lead()     { return $this->belongsTo(Lead::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function company()  { return $this->belongsTo(Company::class); }

    // Scopes
    public function scopePending($q)   { return $q->whereIn('status', ['pending', 'in_progress']); }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }
    public function scopeOverdue($q)   {
        return $q->whereNotNull('due_at')
                 ->where('due_at', '<', now())
                 ->whereNotIn('status', ['completed', 'cancelled']);
    }
    public function scopeDueToday($q)    { return $q->whereDate('due_at', today()); }
    public function scopeByCompany($q, $id) { return $q->where('company_id', $id); }

    // Helpers
    public function isOverdue(): bool
    {
        return $this->due_at && $this->due_at->isPast()
            && !in_array($this->status, ['completed', 'cancelled']);
    }

    public function isDueToday(): bool
    {
        return $this->due_at && $this->due_at->isToday();
    }

    public function getTypeInfoAttribute(): array
    {
        return self::getTypes()[$this->type] ?? ['label' => $this->type, 'icon' => 'fa-tasks', 'color' => '#94a3b8'];
    }

    // Legacy accessors kept for compatibility
    public function getIsOverdueAttribute(): bool
    {
        return $this->isOverdue();
    }

    public function getIsDueTodayAttribute(): bool
    {
        return $this->isDueToday();
    }
}
