<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserPermission;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RolePermissionsController extends Controller
{
    /**
     * Listar agentes de la empresa con sus permisos actuales.
     */
    public function index()
    {
        $agents = User::where('company_id', Auth::user()->company_id)
            ->where('role', User::ROLE_AGENT)
            ->with('modulePermissions')
            ->get();

        $modules = UserPermission::modules();

        return view('admin.roles.index', compact('agents', 'modules'));
    }

    /**
     * Guardar permisos para un agente.
     */
    public function update(Request $request, User $user)
    {
        // Solo puede editar agentes de su misma empresa
        if ($user->company_id !== Auth::user()->company_id || $user->role !== User::ROLE_AGENT) {
            abort(403);
        }

        $modules  = UserPermission::modules();
        $perms    = [];

        foreach ($modules as $module => $config) {
            foreach (array_keys($config['actions']) as $action) {
                $perms[$module][$action] = (bool) $request->input("permissions.{$module}.{$action}", false);
            }
        }

        UserPermission::updateOrCreate(
            ['user_id' => $user->id],
            ['permissions' => $perms]
        );

        return back()->with('success', "Permisos de {$user->name} actualizados correctamente.");
    }
}
