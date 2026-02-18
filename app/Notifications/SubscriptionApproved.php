<?php

namespace App\Notifications;

use App\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionApproved extends Notification
{
    use Queueable;

    protected $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Suscripción Aprobada - ' . config('app.name'))
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Tu suscripción ha sido aprobada exitosamente.')
            ->line('**Paquete:** ' . ($this->subscription->package->name ?? 'N/A'))
            ->line('**Válida hasta:** ' . $this->subscription->ends_at->format('d/m/Y'))
            ->action('Ir al Panel', url('/admin'))
            ->line('Ya puedes disfrutar de todas las funcionalidades de tu plan.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'subscription_approved',
            'subscription_id' => $this->subscription->id,
            'package_name' => $this->subscription->package->name ?? 'N/A',
            'ends_at' => $this->subscription->ends_at->format('d/m/Y'),
            'message' => 'Tu suscripción al paquete ' . ($this->subscription->package->name ?? '') . ' ha sido aprobada.',
        ];
    }
}
