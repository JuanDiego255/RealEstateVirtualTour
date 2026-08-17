<?php

namespace App\Models;

use App\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Cuenta SMTP con la que una empresa envía sus correos (avisos del CRM). La
 * contraseña es la "clave de aplicaciones de terceros" (app password de Gmail /
 * Outlook), guardada encriptada. Si una empresa no configura esto, los correos
 * caen al mailer por defecto del sistema (.env).
 */
class CompanyMailSetting extends Model
{
    protected $fillable = [
        'company_id', 'enabled', 'from_name', 'from_address',
        'host', 'port', 'encryption', 'username', 'password',
        'last_test_at', 'last_test_ok', 'last_test_error',
    ];

    protected $casts = [
        'enabled'      => 'boolean',
        'password'     => 'encrypted',
        'last_test_at' => 'datetime',
        'last_test_ok' => 'boolean',
    ];

    protected $hidden = ['password'];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public static function available(): bool
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Schema::hasTable('company_mail_settings');
        }
        return $cache;
    }

    /**
     * ¿Está lista para enviar? (habilitada y con lo mínimo indispensable)
     */
    public function isUsable(): bool
    {
        return (bool) ($this->enabled && $this->host && $this->port && $this->from_address);
    }

    /**
     * Config de mailer para inyectar en runtime en config('mail.mailers.*').
     */
    public function mailerConfig(): array
    {
        return [
            'transport'  => 'smtp',
            'host'       => $this->host,
            'port'       => (int) $this->port,
            'encryption' => $this->encryption ?: null,
            'username'   => $this->username,
            'password'   => $this->password,
            'timeout'    => null,
            'auth_mode'  => null,
        ];
    }
}
