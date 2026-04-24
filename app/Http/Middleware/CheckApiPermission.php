<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Verifica que el usuario autenticado tenga acceso a un módulo/acción específico.
 * Para super_admin y company_admin siempre permite.
 * Para agent revisa la tabla user_permissions.
 *
 * Uso en rutas: middleware('api.permission:kiosk,view')
 */
class CheckApiPermission
{
    public function handle(Request $request, Closure $next, string $module, string $action = 'view')
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if (!$user->canAccessModule($module, $action)) {
            return response()->json([
                'message' => 'No tienes permiso para acceder a este módulo.',
                'module'  => $module,
                'action'  => $action,
            ], 403);
        }

        return $next($request);
    }
}
