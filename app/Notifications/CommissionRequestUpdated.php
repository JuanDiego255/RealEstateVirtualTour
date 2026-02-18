<?php

namespace App\Notifications;

use App\CommissionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommissionRequestUpdated extends Notification
{
    use Queueable;

    protected $commissionRequest;
    protected $action;

    public function __construct(CommissionRequest $commissionRequest, string $action)
    {
        $this->commissionRequest = $commissionRequest;
        $this->action = $action;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $property = $this->commissionRequest->property;
        $statusLabels = [
            'accepted' => 'aceptada',
            'rejected' => 'rechazada',
            'counter_offer' => 'contra-ofertada',
        ];
        $statusLabel = $statusLabels[$this->action] ?? $this->action;

        $mail = (new MailMessage)
            ->subject('Solicitud de Comisión ' . ucfirst($statusLabel) . ' - ' . ($property->name ?? ''))
            ->greeting('Hola ' . $notifiable->name)
            ->line('Tu solicitud de comisión ha sido **' . $statusLabel . '**.')
            ->line('**Propiedad:** ' . ($property->name ?? 'N/A'));

        if ($this->action === 'counter_offer') {
            $mail->line('**Nueva comisión propuesta:** ' . $this->commissionRequest->proposed_percentage . '%')
                 ->line('Puedes aceptar, rechazar o enviar una contra-oferta.');
        }

        if ($this->action === 'accepted') {
            $mail->line('**Comisión acordada:** ' . $this->commissionRequest->agreed_percentage . '%')
                 ->line('¡Ya puedes gestionar la venta de esta propiedad!');
        }

        return $mail
            ->action('Ver Detalle', url('/admin/bolsa/my-requests'))
            ->line('Ingresa a la plataforma para más detalles.');
    }

    public function toArray($notifiable)
    {
        $statusLabels = [
            'accepted' => 'aceptada',
            'rejected' => 'rechazada',
            'counter_offer' => 'contra-ofertada',
        ];

        return [
            'type' => 'commission_request_updated',
            'request_id' => $this->commissionRequest->id,
            'action' => $this->action,
            'property_name' => $this->commissionRequest->property->name ?? 'N/A',
            'message' => 'Tu solicitud de comisión para ' . ($this->commissionRequest->property->name ?? 'una propiedad') . ' ha sido ' . ($statusLabels[$this->action] ?? $this->action),
        ];
    }
}
