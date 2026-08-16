<?php

namespace App\Notifications;

use App\Appointment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso de una cita próxima al asesor responsable (según reminder_minutes de la
 * cita). Email + campana.
 */
class AppointmentReminderNotification extends Notification
{
    public function __construct(protected Appointment $appointment)
    {
    }

    public function via($notifiable): array
    {
        $channels = ['database'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $a = $this->appointment;
        return (new MailMessage)
            ->subject('Cita próxima: ' . $a->title)
            ->greeting('Hola ' . $notifiable->name)
            ->line('Tenés una cita próxima.')
            ->line('**' . $a->title . '**')
            ->line('**Cuándo:** ' . optional($a->starts_at)->format('d/m/Y H:i'))
            ->line('**Cliente:** ' . ($a->client_display_name ?: 'Sin cliente'))
            ->line($a->location ? '**Lugar:** ' . $a->location : '')
            ->action('Ver agenda', url('/admin/crm/appointments'));
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
