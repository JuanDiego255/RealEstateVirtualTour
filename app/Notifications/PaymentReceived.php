<?php

namespace App\Notifications;

use App\SubscriptionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    protected $payment;

    public function __construct(SubscriptionPayment $payment)
    {
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $subscription = $this->payment->subscription;
        $company = $subscription->company ?? null;

        return (new MailMessage)
            ->subject('Nuevo Pago Recibido - Requiere Verificación')
            ->greeting('Nuevo pago pendiente de verificación')
            ->line('Se ha registrado un nuevo comprobante de pago.')
            ->line('**Empresa:** ' . ($company->name ?? 'N/A'))
            ->line('**Monto:** ₡' . number_format($this->payment->amount, 2))
            ->line('**Método:** ' . ucfirst($this->payment->payment_method))
            ->line('**Referencia:** ' . ($this->payment->reference ?? 'Sin referencia'))
            ->action('Verificar Pago', url('/admin/subscriptions/pending-payments'))
            ->line('Por favor, verifica el comprobante y aprueba o rechaza el pago.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'payment_received',
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'company' => $this->payment->subscription->company->name ?? 'N/A',
            'message' => 'Nuevo pago de ₡' . number_format($this->payment->amount, 2) . ' pendiente de verificación.',
        ];
    }
}
