<?php

namespace App\Notifications;

use App\CommissionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommissionRequestReceived extends Notification
{
    use Queueable;

    protected $commissionRequest;

    public function __construct(CommissionRequest $commissionRequest)
    {
        $this->commissionRequest = $commissionRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $property = $this->commissionRequest->property;
        $requester = $this->commissionRequest->requesterCompany;

        return (new MailMessage)
            ->subject('Nueva Solicitud de Comisión - ' . ($property->name ?? 'Propiedad'))
            ->greeting('Hola ' . $notifiable->name)
            ->line('Has recibido una nueva solicitud de comisión en la Bolsa Inmobiliaria.')
            ->line('**Propiedad:** ' . ($property->name ?? 'N/A'))
            ->line('**Solicitante:** ' . ($requester->name ?? 'N/A'))
            ->line('**Comisión propuesta:** ' . $this->commissionRequest->proposed_percentage . '%')
            ->when($this->commissionRequest->message, function ($message) {
                return $message->line('**Mensaje:** ' . $this->commissionRequest->message);
            })
            ->action('Ver Solicitud', url('/admin/bolsa/incoming'))
            ->line('Revisa la solicitud y decide si aceptar o negociar.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'commission_request',
            'request_id' => $this->commissionRequest->id,
            'property_name' => $this->commissionRequest->property->name ?? 'N/A',
            'requester' => $this->commissionRequest->requesterCompany->name ?? 'N/A',
            'percentage' => $this->commissionRequest->proposed_percentage,
            'message' => 'Nueva solicitud de comisión para ' . ($this->commissionRequest->property->name ?? 'una propiedad'),
        ];
    }
}
