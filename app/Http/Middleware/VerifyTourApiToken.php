<?php

namespace App\Http\Middleware;

use App\ApiClient;
use Closure;
use Illuminate\Http\Request;

class VerifyTourApiToken
{
    /**
     * Verifica el token de API (tabla api_clients) para el consumo externo
     * de tours virtuales. Acepta el token vía:
     *   - Authorization: Bearer <token>
     *   - Header X-API-Token: <token>
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken() ?: $request->header('X-API-Token');

        $client = ApiClient::findByToken($token);

        if (!$client) {
            return response()->json([
                'message' => 'Token de API inválido o inactivo.',
            ], 401);
        }

        // Registrar último uso (sin disparar eventos/timestamps de update masivo)
        $client->forceFill(['last_used_at' => now()])->saveQuietly();

        // Exponer el cliente autenticado por si el controlador lo necesita
        $request->attributes->set('api_client', $client);

        return $next($request);
    }
}
