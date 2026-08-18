<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de correos salientes. Captura TODO lo que sale (cualquier servicio:
 * recordatorios, avisos, seguimientos, pruebas) vía el evento MessageSent, y los
 * fallos que reportan los puntos de envío. Se atribuye a la empresa por el
 * remitente configurado en company_mail_settings.
 */
class MailLog extends Model
{
    protected $fillable = [
        'company_id', 'to_email', 'to_name', 'from_email',
        'subject', 'mailer', 'context', 'status', 'error',
    ];

    const STATUS_SENT   = 'sent';
    const STATUS_FAILED = 'failed';

    public static function available(): bool
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Schema::hasTable('mail_logs');
        }
        return $cache;
    }

    public function scopeForCompany($q, int $companyId)
    {
        return $q->where('company_id', $companyId);
    }

    /**
     * Empresa dueña de una dirección "from" (según su SMTP configurado).
     */
    public static function companyIdFromAddress(?string $from): ?int
    {
        if (!$from || !Schema::hasTable('company_mail_settings')) {
            return null;
        }
        static $cache = [];
        $from = mb_strtolower(trim($from));
        if (array_key_exists($from, $cache)) {
            return $cache[$from];
        }
        $id = CompanyMailSetting::whereRaw('LOWER(from_address) = ?', [$from])->value('company_id');
        return $cache[$from] = ($id ? (int) $id : null);
    }

    public static function recordSent(?int $companyId, string $toEmail, ?string $toName, ?string $subject, ?string $from, ?string $mailer = null, ?string $context = null): void
    {
        if (!static::available()) {
            return;
        }
        static::create([
            'company_id' => $companyId,
            'to_email'   => $toEmail,
            'to_name'    => $toName,
            'from_email' => $from,
            'subject'    => $subject,
            'mailer'     => $mailer,
            'context'    => $context,
            'status'     => self::STATUS_SENT,
        ]);
    }

    public static function recordFailed(?int $companyId, ?string $toEmail, ?string $subject, string $error, ?string $context = null): void
    {
        if (!static::available()) {
            return;
        }
        static::create([
            'company_id' => $companyId,
            'to_email'   => $toEmail,
            'subject'    => $subject,
            'context'    => $context,
            'status'     => self::STATUS_FAILED,
            'error'      => mb_substr($error, 0, 2000),
        ]);
    }
}
