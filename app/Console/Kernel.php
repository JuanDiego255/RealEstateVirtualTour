<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Verificar suscripciones por vencer (diariamente a las 8am)
        $schedule->command('subscriptions:check-expiring --days=7')->dailyAt('08:00');
        $schedule->command('crm:run-pipeline-rules')->dailyAt('07:00');

        // Despacho de recordatorios y avisos de cita. Corre seguido si el cron
        // ejecuta schedule:run cada minuto; si solo corre una vez al día, igual
        // alcanza a todos los vencidos porque el filtro es "<= ahora".
        $schedule->command('crm:dispatch-reminders')->everyFifteenMinutes()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
