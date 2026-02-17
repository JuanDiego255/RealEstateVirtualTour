<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckCompanyAccess
{
    /**
     * Handle an incoming request.
     * Verifica que el usuario solo acceda a recursos de su propia empresa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Super admin tiene acceso a todo
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verificar que el usuario tiene una empresa
        if (!$user->company_id) {
            return redirect()->route('home')
                ->with('error', 'Debe estar asociado a una empresa para acceder a esta sección.');
        }

        // Verificar que la empresa está activa
        if (!$user->company || !$user->company->isActive()) {
            return redirect()->route('home')
                ->with('error', 'Su empresa está inactiva. Contacte al administrador.');
        }

        // Verificar que el usuario está activo
        if (!$user->isActive()) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Su cuenta ha sido desactivada. Contacte al administrador.');
        }

        return $next($request);
    }
}
