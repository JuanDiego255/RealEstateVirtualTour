<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $fillable = [
        'company_id', 'name', 'channel', 'stage',
        'subject', 'body', 'is_active', 'sort_order',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function company() { return $this->belongsTo(\App\Company::class); }

    public function scopeActive($q)            { return $q->where('is_active', true); }
    public function scopeForChannel($q, $ch)   { return $q->where('channel', $ch); }
    public function scopeForStage($q, $stage)  {
        return $q->where(fn($q) => $q->whereNull('stage')->orWhere('stage', $stage));
    }

    public static function getChannels(): array
    {
        return [
            'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'fa-whatsapp', 'color' => '#25d366'],
            'email'    => ['label' => 'Email',    'icon' => 'fa-envelope',  'color' => '#3b82f6'],
        ];
    }

    /**
     * Interpolate variables for a given lead and agent.
     */
    public function interpolate(\App\Lead $lead, \App\User $agent, ?string $portalUrl = null): string
    {
        $vars = [
            '{{nombre}}'    => $lead->name,
            '{{name}}'      => $lead->name,
            '{{agente}}'    => $agent->name,
            '{{agent}}'     => $agent->name,
            '{{propiedad}}' => $lead->property?->title ?? '',
            '{{property}}'  => $lead->property?->title ?? '',
            '{{fecha}}'     => now()->format('d/m/Y'),
            '{{date}}'      => now()->format('d/m/Y'),
            '{{enlace}}'    => $portalUrl ?? '',
            '{{link}}'      => $portalUrl ?? '',
            '{{empresa}}'   => $lead->user?->company?->name ?? '',
        ];

        return str_replace(array_keys($vars), array_values($vars), $this->body);
    }
}
