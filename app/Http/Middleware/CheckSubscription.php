<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $feature  Optional feature to check (e.g., 'tours', 'commission')
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $feature = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Super admin bypasses subscription checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verificar que el usuario tiene una empresa
        if (!$user->company) {
            return redirect()->route('home')
                ->with('error', 'Debe estar asociado a una empresa para acceder a esta sección.');
        }

        // Verificar suscripción activa
        if (!$user->hasActiveSubscription()) {
            return redirect()->route('subscription.required')
                ->with('error', 'Su suscripción ha expirado o no está activa. Por favor renueve para continuar.');
        }

        // Verificar feature específico si se solicitó
        if ($feature) {
            $subscription = $user->getActiveSubscription();
            $package = $subscription->package;

            switch ($feature) {
                case 'tours':
                    if (!$package->allows_tours) {
                        return redirect()->back()
                            ->with('error', 'Su paquete no incluye la creación de tours virtuales. Actualice su plan para acceder a esta función.');
                    }
                    break;

                case 'commission':
                case 'bolsa':
                    if (!$package->allows_commission) {
                        return redirect()->back()
                            ->with('error', 'Su paquete no incluye acceso a la Bolsa Inmobiliaria. Actualice su plan para acceder a esta función.');
                    }
                    break;
            }
        }

        return $next($request);
    }
}
