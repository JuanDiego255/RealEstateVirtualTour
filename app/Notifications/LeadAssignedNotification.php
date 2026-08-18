<?php

namespace App\Notifications;

use App\Lead;
use Illuminate\Notifications\Notification;

/**
 * Aviso al asesor cuando se le asigna un lead. Campana por este canal; el correo
 * se envía aparte en texto plano.
 */
class LeadAssignedNotification extends Notification
{
    public function __construct(protected Lead $lead)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toPlain($notifiable): array
    {
        $l = $this->lead;
        $body = 'Hola ' . $notifiable->name . ",\n\n"
            . "Te asignaron un lead.\n\n"
            . 'Nombre: ' . ($l->name ?: 'Sin nombre') . "\n"
            . 'Origen: ' . (Lead::getSources()[$l->source] ?? $l->source) . "\n"
            . ($l->phone ? 'Teléfono: ' . $l->phone . "\n" : '')
            . "\nContactalo pronto para no perder la oportunidad.\n\n"
            . 'Ver lead: ' . url('/admin/crm/leads/' . $l->id);

        return ['subject' => 'Nuevo lead asignado: ' . ($l->name ?: 'Sin nombre'), 'body' => $body];
    }

    public function toArray($notifiable): array
    {
        $l = $this->lead;
        return [
            'type'    => 'lead_assigned',
            'lead_id' => $l->id,
            'name'    => $l->name,
            'source'  => $l->source,
            'url'     => url('/admin/crm/leads/' . $l->id),
            'message' => 'Nuevo lead asignado: ' . ($l->name ?: 'Sin nombre'),
        ];
    }
}
