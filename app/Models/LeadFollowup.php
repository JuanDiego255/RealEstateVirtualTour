<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadFollowup extends Model
{
    protected $table = 'lead_followups';

    protected $fillable = [
        'event_lead_id', 'user_id', 'action', 'outcome', 'notes', 'next_followup_at',
    ];

    protected $casts = [
        'next_followup_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(EventLead::class, 'event_lead_id');
    }

    public function agent()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public static function actions(): array
    {
        return [
            'call'        => ['label' => 'Llamada',         'icon' => 'fa-phone'],
            'whatsapp'    => ['label' => 'WhatsApp',         'icon' => 'fa-whatsapp'],
            'email'       => ['label' => 'Email',            'icon' => 'fa-envelope'],
            'meeting'     => ['label' => 'Reunión presencial','icon' => 'fa-users'],
            'demo'        => ['label' => 'Demo/Prueba',      'icon' => 'fa-car'],
            'quote_sent'  => ['label' => 'Cotización enviada','icon' => 'fa-calculator'],
            'other'       => ['label' => 'Otro',             'icon' => 'fa-ellipsis-h'],
        ];
    }

    public static function outcomes(): array
    {
        return [
            'no_answer'        => ['label' => 'No contestó',        'color' => 'secondary'],
            'interested'       => ['label' => 'Interesado',         'color' => 'success'],
            'not_interested'   => ['label' => 'No interesado',      'color' => 'danger'],
            'thinking'         => ['label' => 'Lo está pensando',   'color' => 'warning'],
            'follow_up_later'  => ['label' => 'Seguimiento futuro', 'color' => 'info'],
            'converted'        => ['label' => 'Venta concretada',   'color' => 'success'],
            'lost'             => ['label' => 'Lead perdido',       'color' => 'danger'],
        ];
    }
}
