<?php

namespace App\Notifications;

use App\Lead;
use App\Services\CompanyMailer;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso al asesor cuando se le asigna un lead (manual o automático, incluidos
 * los que genera el bot de WhatsApp). Email + campana.
 */
class LeadAssignedNotification extends Notification
{
    public function __construct(protected Lead $lead)
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
        $l = $this->lead;
        $mail = (new MailMessage)
            ->subject('Nuevo lead asignado: ' . ($l->name ?: 'Sin nombre'))
            ->greeting('Hola ' . $notifiable->name)
            ->line('Te asignaron un lead.')
            ->line('**Nombre:** ' . ($l->name ?: 'Sin nombre'))
            ->line('**Origen:** ' . (Lead::getSources()[$l->source] ?? $l->source))
            ->line($l->phone ? '**Teléfono:** ' . $l->phone : '')
            ->action('Ver lead', url('/admin/crm/leads/' . $l->id))
            ->line('Contactalo pronto para no perder la oportunidad.');

        return CompanyMailer::applyTo($mail, $notifiable->company_id);
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
