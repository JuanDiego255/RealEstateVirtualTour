<?php

namespace App\Notifications;

use App\Reminder;
use App\Lead;
use App\Services\CompanyMailer;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso de un recordatorio del CRM que ya venció. Se envía por email (si el
 * recordatorio lo pide y el asesor tiene correo) y siempre queda en la campana.
 */
class ReminderDueNotification extends Notification
{
    public function __construct(protected Reminder $reminder)
    {
    }

    public function via($notifiable): array
    {
        $channels = ['database'];
        if ($this->reminder->email_notification && !empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Recordatorio: ' . $this->reminder->title)
            ->greeting('Hola ' . $notifiable->name)
            ->line($this->reminder->description ?: 'Tenés un recordatorio pendiente.');

        if ($this->reminder->related_item_name) {
            $mail->line('**Relacionado con:** ' . $this->reminder->related_item_name);
        }
        $mail->line('**Programado para:** ' . optional($this->reminder->remind_at)->format('d/m/Y H:i'));
        $mail->action('Ver recordatorios', $this->targetUrl());

        return CompanyMailer::applyTo($mail, $notifiable->company_id);
    }

    public function toArray($notifiable): array
    {
        return [
            'type'          => 'reminder_due',
            'reminder_id'   => $this->reminder->id,
            'title'         => $this->reminder->title,
            'related'       => $this->reminder->related_item_name,
            'remind_at'     => optional($this->reminder->remind_at)->format('d/m/Y H:i'),
            'url'           => $this->targetUrl(),
            'message'       => 'Recordatorio: ' . $this->reminder->title,
        ];
    }

    private function targetUrl(): string
    {
        if ($this->reminder->remindable instanceof Lead) {
            return url('/admin/crm/leads/' . $this->reminder->remindable_id);
        }
        return url('/admin/crm/reminders');
    }
}
