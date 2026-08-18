<?php

namespace App\Services;

use App\Models\CompanyMailSetting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;

/**
 * Enruta los correos por la cuenta SMTP de cada empresa. Registra el mailer en
 * runtime (config('mail.mailers.company_{id}')) y lo selecciona en el mensaje.
 * Si la empresa no tiene SMTP propio, no toca nada y se usa el mailer por defecto.
 */
class CompanyMailer
{
    /** Empresas ya registradas en esta request, para no recalcular. */
    private static array $registered = [];

    /**
     * Registra (si hace falta) el mailer de la empresa y devuelve su nombre, o
     * null si la empresa no tiene SMTP propio configurado.
     */
    public static function mailerName(?int $companyId): ?string
    {
        if (!$companyId || !CompanyMailSetting::available()) {
            return null;
        }

        $name = 'company_' . $companyId;
        if (array_key_exists($companyId, self::$registered)) {
            return self::$registered[$companyId];
        }

        $setting = CompanyMailSetting::where('company_id', $companyId)->first();
        if (!$setting || !$setting->isUsable()) {
            return self::$registered[$companyId] = null;
        }

        config(['mail.mailers.' . $name => $setting->mailerConfig()]);

        return self::$registered[$companyId] = $name;
    }

    /**
     * Dirección "from" propia de la empresa, o null si no aplica.
     *
     * @return array{0:string,1:?string}|null  [address, name]
     */
    public static function from(?int $companyId): ?array
    {
        if (!self::mailerName($companyId)) {
            return null;
        }
        $setting = CompanyMailSetting::where('company_id', $companyId)->first();
        if (!$setting || !$setting->from_address) {
            return null;
        }
        return [$setting->from_address, $setting->from_name];
    }

    /**
     * Envía un correo de TEXTO PLANO por la cuenta de la empresa (o el mailer por
     * defecto si no tiene). Texto plano = mejor entregabilidad en hosting
     * compartido (evita filtros de correo saliente que descartan HTML).
     */
    public static function sendPlain(?int $companyId, string $toEmail, string $subject, string $body): void
    {
        $mailerName = self::mailerName($companyId) ?: config('mail.default');
        $from       = self::from($companyId);

        $mailer = Mail::mailer($mailerName);
        $mailer->raw($body, function ($m) use ($toEmail, $subject, $from) {
            $m->to($toEmail)->subject($subject);
            if ($from) {
                $m->from($from[0], $from[1]);
            }
        });

        // En CLI, SwiftMailer no lanza excepción si el SMTP rechaza al destinatario:
        // deja los "failedRecipients" y sigue. Lo detectamos para no marcar como
        // enviado algo que en realidad no salió.
        $failures = method_exists($mailer, 'failures') ? ($mailer->failures() ?: []) : [];
        if (!empty($failures)) {
            throw new \RuntimeException('El servidor SMTP no aceptó al destinatario: ' . implode(', ', $failures));
        }
    }

    /**
     * Aplica el mailer y el remitente de la empresa a una notificación por correo.
     * Si la empresa no tiene SMTP propio, devuelve el mensaje sin cambios.
     */
    public static function applyTo(MailMessage $mail, ?int $companyId): MailMessage
    {
        $name = self::mailerName($companyId);
        if (!$name) {
            return $mail;
        }
        $mail->mailer($name);
        if ($from = self::from($companyId)) {
            $mail->from($from[0], $from[1]);
        }
        return $mail;
    }
}
