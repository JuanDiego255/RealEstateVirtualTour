<?php

namespace App\Console\Commands;

use App\Services\FollowUpService;
use Illuminate\Console\Command;

/**
 * Procesa las secuencias de seguimiento: envía los pasos vencidos por email o
 * WhatsApp (respetando la ventana de 24 h) y avanza cada inscripción.
 */
class RunFollowUps extends Command
{
    protected $signature   = 'crm:run-followups';
    protected $description = 'Envía los pasos vencidos de las secuencias de seguimiento (nurturing)';

    public function handle(): int
    {
        $r = FollowUpService::process();
        $this->info("Seguimientos: {$r['sent']} enviados, {$r['completed']} completados, {$r['stopped']} detenidos.");
        return 0;
    }
}
