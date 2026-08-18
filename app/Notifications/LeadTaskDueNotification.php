<?php

namespace App\Notifications;

use App\LeadTask;
use Illuminate\Notifications\Notification;

/**
 * Aviso al asesor cuando una tarea del CRM vence. Campana por este canal; el
 * correo se envía aparte en texto plano.
 */
class LeadTaskDueNotification extends Notification
{
    public function __construct(protected LeadTask $task)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toPlain($notifiable): array
    {
        $t = $this->task;
        $body = 'Hola ' . $notifiable->name . ",\n\n"
            . "Tenés una tarea que vence.\n\n"
            . $t->title . "\n"
            . 'Vence: ' . optional($t->due_at)->format('d/m/Y H:i') . "\n"
            . ($t->lead ? 'Lead: ' . $t->lead->name . "\n" : '')
            . "\nVer tarea: " . url('/admin/crm/leads/' . $t->lead_id);

        return ['subject' => 'Tarea pendiente: ' . $t->title, 'body' => $body];
    }

    public function toArray($notifiable): array
    {
        $t = $this->task;
        return [
            'type'    => 'task_due',
            'task_id' => $t->id,
            'title'   => $t->title,
            'lead_id' => $t->lead_id,
            'due_at'  => optional($t->due_at)->format('d/m/Y H:i'),
            'url'     => url('/admin/crm/leads/' . $t->lead_id),
            'message' => 'Tarea pendiente: ' . $t->title,
        ];
    }
}
