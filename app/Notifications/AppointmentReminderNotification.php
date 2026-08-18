<?php

namespace App\Notifications;

use App\Appointment;
use Illuminate\Notifications\Notification;

/**
 * Aviso de una cita próxima al asesor responsable. Campana por este canal; el
 * correo se envía aparte en texto plano.
 */
class AppointmentReminderNotification extends Notification
{
    public function __construct(protected Appointment $appointment)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toPlain($notifiable): array
    {
        $a = $this->appointment;
        $body = 'Hola ' . $notifiable->name . ",\n\n"
            . "Tenés una cita próxima.\n\n"
            . $a->title . "\n"
            . 'Cuándo: ' . optional($a->starts_at)->format('d/m/Y H:i') . "\n"
            . 'Cliente: ' . ($a->client_display_name ?: 'Sin cliente') . "\n"
            . ($a->location ? 'Lugar: ' . $a->location . "\n" : '')
            . "\nVer agenda: " . url('/admin/crm/appointments');

        return ['subject' => 'Cita próxima: ' . $a->title, 'body' => $body];
    }

    public function toArray($notifiable): array
    {
        $a = $this->appointment;
        return [
            'type'           => 'appointment_reminder',
            'appointment_id' => $a->id,
            'title'          => $a->title,
            'starts_at'      => optional($a->starts_at)->format('d/m/Y H:i'),
            'client'         => $a->client_display_name,
            'url'            => url('/admin/crm/appointments'),
            'message'        => 'Cita próxima: ' . $a->title,
        ];
    }
}
