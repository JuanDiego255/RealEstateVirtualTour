<?php

namespace App\Notifications;

use App\Reminder;
use App\Lead;
use Illuminate\Notifications\Notification;

/**
 * Aviso de un recordatorio del CRM que ya venció. La campana (database) va por
 * este canal; el correo se envía aparte en TEXTO PLANO (mejor entregabilidad).
 */
class ReminderDueNotification extends Notification
{
    public function __construct(protected Reminder $reminder)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Contenido del correo en texto plano.
     *
     * @return array{subject:string, body:string}
     */
    public function toPlain($notifiable): array
    {
        $r = $this->reminder;
        $body = 'Hola ' . $notifiable->name . ",\n\n"
            . ($r->description ?: 'Tenés un recordatorio pendiente.') . "\n\n"
            . ($r->related_item_name ? 'Relacionado con: ' . $r->related_item_name . "\n" : '')
            . 'Programado para: ' . optional($r->remind_at)->format('d/m/Y H:i') . "\n\n"
            . 'Ver: ' . $this->targetUrl();

        return ['subject' => 'Recordatorio: ' . $r->title, 'body' => $body];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'        => 'reminder_due',
            'reminder_id' => $this->reminder->id,
            'title'       => $this->reminder->title,
            'related'     => $this->reminder->related_item_name,
            'remind_at'   => optional($this->reminder->remind_at)->format('d/m/Y H:i'),
            'url'         => $this->targetUrl(),
            'message'     => 'Recordatorio: ' . $this->reminder->title,
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
