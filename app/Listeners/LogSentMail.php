<?php

namespace App\Listeners;

use App\Models\MailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

/**
 * Registra cada correo saliente (cualquier servicio) atribuyéndolo a la empresa
 * dueña del remitente. Escucha MessageSent, así captura todo lo que de verdad
 * salió, sin importar quién lo envió. Nunca debe romper el envío: todo protegido.
 */
class LogSentMail
{
    public function handle(MessageSent $event): void
    {
        try {
            [$from, $to, $subject] = $this->extract($event);

            $companyId = MailLog::companyIdFromAddress($from);
            if (!$companyId) {
                return; // solo registramos lo que sale de una configuración de empresa
            }

            foreach ($to as $email => $name) {
                MailLog::recordSent($companyId, (string) $email, $name ?: null, $subject, $from, 'company_' . $companyId);
            }
        } catch (\Throwable $e) {
            Log::error('No se pudo registrar el correo saliente', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Extrae remitente, destinatarios y asunto tanto de Swift (L8) como de
     * Symfony Mailer (L9+), sin asumir una sola API.
     *
     * @return array{0:?string,1:array,2:?string}
     */
    private function extract(MessageSent $event): array
    {
        $msg = $event->message;

        // SwiftMailer (Laravel 8)
        if (method_exists($msg, 'getTo')) {
            $from    = $this->firstKey($msg->getFrom());
            $to      = $this->normalize($msg->getTo());
            $subject = method_exists($msg, 'getSubject') ? $msg->getSubject() : null;
            return [$from, $to, $subject];
        }

        // Symfony Mailer (Laravel 9+)
        if (method_exists($msg, 'getTo') === false && method_exists($msg, 'getEnvelope') === false) {
            return [null, [], null];
        }

        $from = null;
        $to = [];
        $subject = null;
        try {
            $fromAddrs = $msg->getFrom();
            if (!empty($fromAddrs)) {
                $from = $fromAddrs[0]->getAddress();
            }
            foreach ($msg->getTo() as $addr) {
                $to[$addr->getAddress()] = $addr->getName() ?: null;
            }
            $subject = $msg->getSubject();
        } catch (\Throwable $e) {
            // ignorar: devolvemos lo que se pudo
        }

        return [$from, $to, $subject];
    }

    private function firstKey($arr): ?string
    {
        if (!is_array($arr) || empty($arr)) {
            return null;
        }
        return (string) array_key_first($arr);
    }

    private function normalize($arr): array
    {
        return is_array($arr) ? $arr : [];
    }
}
