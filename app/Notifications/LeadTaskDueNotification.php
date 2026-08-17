<?php

namespace App\Notifications;

use App\LeadTask;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso al asesor cuando una tarea del CRM llega a su fecha de vencimiento.
 * Email + campana.
 */
class LeadTaskDueNotification extends Notification
{
    public function __construct(protected LeadTask $task)
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
        $t = $this->task;
        return (new MailMessage)
            ->subject('Tarea pendiente: ' . $t->title)
            ->greeting('Hola ' . $notifiable->name)
            ->line('Tenés una tarea que vence.')
            ->line('**' . $t->title . '**')
            ->line('**Vence:** ' . optional($t->due_at)->format('d/m/Y H:i'))
            ->line($t->lead ? '**Lead:** ' . $t->lead->name : '')
            ->action('Ver tarea', url('/admin/crm/leads/' . $t->lead_id));
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
