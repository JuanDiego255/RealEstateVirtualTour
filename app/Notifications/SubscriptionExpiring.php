<?php

namespace App\Notifications;

use App\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiring extends Notification
{
    use Queueable;

    protected $subscription;
    protected $daysLeft;

    public function __construct(Subscription $subscription, int $daysLeft)
    {
        $this->subscription = $subscription;
        $this->daysLeft = $daysLeft;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Tu suscripción vence pronto - ' . config('app.name'))
            ->greeting('Hola ' . $notifiable->name)
            ->line('Tu suscripción está por vencer.')
            ->line('**Paquete:** ' . ($this->subscription->package->name ?? 'N/A'))
            ->line('**Vence en:** ' . $this->daysLeft . ' días (' . $this->subscription->ends_at->format('d/m/Y') . ')')
            ->action('Renovar Suscripción', url('/admin/subscriptions/my'))
            ->line('Renueva a tiempo para no perder acceso a las funcionalidades de tu plan.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'subscription_expiring',
            'subscription_id' => $this->subscription->id,
            'days_left' => $this->daysLeft,
            'ends_at' => $this->subscription->ends_at->format('d/m/Y'),
            'message' => 'Tu suscripción vence en ' . $this->daysLeft . ' días.',
        ];
    }
}
