<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityTemplate extends Model
{
    protected $fillable = [
        'company_id', 'created_by', 'name', 'type',
        'subject', 'body', 'stage', 'is_active', 'sort_order',
        'auto_create_task', 'task_due_hours',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'auto_create_task' => 'boolean',
    ];

    public function company()  { return $this->belongsTo(\App\Company::class); }
    public function creator()  { return $this->belongsTo(\App\User::class, 'created_by'); }

    public function scopeActive($q)  { return $q->where('is_active', true); }
    public function scopeForStage($q, string $stage) {
        return $q->where(fn($q) => $q->whereNull('stage')->orWhere('stage', $stage));
    }

    /**
     * Interpolate template variables for a given lead.
     */
    public function interpolate(\App\Lead $lead, \App\User $agent): array
    {
        $vars = [
            '{{name}}'     => $lead->name,
            '{{email}}'    => $lead->email ?? '',
            '{{phone}}'    => $lead->phone ?? '',
            '{{agent}}'    => $agent->name,
            '{{property}}' => $lead->property?->title ?? '',
            '{{budget}}'   => $lead->budget_max ? number_format($lead->budget_max, 0, ',', '.') . ' ' . $lead->budget_currency : '',
            '{{status}}'   => $lead->status_label ?? $lead->status,
            '{{date}}'     => now()->format('d/m/Y'),
        ];

        return [
            'subject' => str_replace(array_keys($vars), array_values($vars), $this->subject ?? ''),
            'body'    => str_replace(array_keys($vars), array_values($vars), $this->body),
        ];
    }
}
