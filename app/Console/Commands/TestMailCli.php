<?php

namespace App\Console\Commands;

use App\Models\CompanyMailSetting;
use App\Services\CompanyMailer;
use Illuminate\Console\Command;

/**
 * Prueba el envío de correo por SMTP DESDE CLI (el mismo contexto que el cron),
 * para aislar si el problema es del entorno de línea de comandos.
 *
 *   php artisan mail:test-cli destino@correo.com [company_id]
 */
class TestMailCli extends Command
{
    protected $signature   = 'mail:test-cli {email} {company?}';
    protected $description = 'Envía un correo de prueba por el SMTP de la empresa desde CLI y reporta el resultado real';

    public function handle(): int
    {
        $companyId = $this->argument('company')
            ?: CompanyMailSetting::where('enabled', true)->value('company_id');

        if (!$companyId) {
            $this->error('No hay una empresa con SMTP habilitado. Pasá el company_id como segundo argumento.');
            return 1;
        }

        $this->line("Enviando desde CLI (empresa {$companyId}) a {$this->argument('email')}...");

        try {
            CompanyMailer::sendPlain(
                (int) $companyId,
                $this->argument('email'),
                'Prueba CLI — CRM',
                "Este correo se envió desde la línea de comandos (mismo contexto que el cron).\n"
                    . 'Si lo recibiste, el SMTP funciona también desde CLI.'
            );
            $this->info('OK: el SMTP aceptó el correo sin destinatarios fallidos. Revisá la bandeja.');
            return 0;
        } catch (\Throwable $e) {
            $this->error('FALLÓ: ' . $e->getMessage());
            return 1;
        }
    }
}
