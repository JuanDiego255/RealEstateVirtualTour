<?php

namespace App\Console\Commands;

use App\ApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateApiClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tour:client {name : Nombre del sistema consumidor, ej: "Real Estate"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un token de API para que un sistema externo consuma los tours virtuales';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        // Token plano que se mostrará UNA sola vez
        $plain = Str::random(64);

        $client = ApiClient::create([
            'name'       => $name,
            'token_hash' => ApiClient::hashToken($plain),
            'is_active'  => true,
        ]);

        $this->info('Cliente de API creado correctamente.');
        $this->line('');
        $this->line('  ID:     ' . $client->id);
        $this->line('  Nombre: ' . $client->name);
        $this->line('');
        $this->warn('Token (cópielo ahora, no se volverá a mostrar):');
        $this->line('');
        $this->line('  ' . $plain);
        $this->line('');
        $this->comment('Guárdelo en el .env del sistema consumidor como TOUR_API_TOKEN');
        $this->comment('y envíelo en el header: Authorization: Bearer <token>');

        return 0;
    }
}
