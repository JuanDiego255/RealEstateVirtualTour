<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of users for the company.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            // Super admin sees all users
            $users = User::with('company')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            $companies = Company::orderBy('name')->get();
        } else {
            // Company admin sees only their company's users
            $users = User::where('company_id', $user->company_id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            $companies = collect();
        }

        return view('admin.users.index', compact('users', 'companies'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $user = Auth::user();

        // Check if can add more users
        if (!$user->isSuperAdmin()) {
            $company = $user->company;
            if ($company && !$company->canAddUser()) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Ha alcanzado el límite de usuarios de su plan. Actualice para agregar más.');
            }
        }

        $companies = $user->isSuperAdmin() ? Company::orderBy('name')->get() : collect();
        $roles = $this->getAvailableRoles($user);

        return view('admin.users.create', compact('companies', 'roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $authUser = Auth::user();

        // Validate
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:' . implode(',', array_keys($this->getAvailableRoles($authUser))),
            'phone' => 'nullable|string|max:50',
        ];

        if ($authUser->isSuperAdmin()) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        $request->validate($rules);

        // Determine company_id
        $companyId = $authUser->isSuperAdmin()
            ? $request->company_id
            : $authUser->company_id;

        // Check user limit for company
        if (!$authUser->isSuperAdmin()) {
            $company = Company::find($companyId);
            if ($company && !$company->canAddUser()) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Ha alcanzado el límite de usuarios de su plan.');
            }
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'company_id' => $companyId,
            'phone' => $request->phone,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Show user details.
     */
    public function show(User $user)
    {
        $this->authorizeUser($user);

        $user->load('company');

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing user.
     */
    public function edit(User $targetUser)
    {
        $this->authorizeUser($targetUser);

        $authUser = Auth::user();
        $companies = $authUser->isSuperAdmin() ? Company::orderBy('name')->get() : collect();
        $roles = $this->getAvailableRoles($authUser);

        return view('admin.users.edit', compact('targetUser', 'companies', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $targetUser)
    {
        $this->authorizeUser($targetUser);

        $authUser = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $targetUser->id,
            'email' => 'required|email|max:255|unique:users,email,' . $targetUser->id,
            'role' => 'required|in:' . implode(',', array_keys($this->getAvailableRoles($authUser))),
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,suspended',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }

        if ($authUser->isSuperAdmin()) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        $request->validate($rules);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($authUser->isSuperAdmin()) {
            $data['company_id'] = $request->company_id;
        }

        $targetUser->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(User $targetUser)
    {
        $this->authorizeUser($targetUser);

        // Cannot deactivate yourself
        if ($targetUser->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puede desactivar su propia cuenta.');
        }

        $newStatus = $targetUser->status === 'active' ? 'inactive' : 'active';
        $targetUser->update(['status' => $newStatus]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Estado del usuario actualizado.');
    }

    /**
     * Remove user (soft: set to inactive).
     */
    public function destroy(User $targetUser)
    {
        $this->authorizeUser($targetUser);

        // Cannot delete yourself
        if ($targetUser->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puede eliminar su propia cuenta.');
        }

        // Cannot delete super_admin
        if ($targetUser->role === User::ROLE_SUPER_ADMIN) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puede eliminar un Super Administrador.');
        }

        $targetUser->update(['status' => 'inactive']);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario desactivado correctamente.');
    }

    /**
     * Get available roles based on auth user.
     */
    private function getAvailableRoles(User $authUser): array
    {
        if ($authUser->isSuperAdmin()) {
            return [
                User::ROLE_SUPER_ADMIN => 'Super Administrador',
                User::ROLE_COMPANY_ADMIN => 'Administrador de Empresa',
                User::ROLE_AGENT => 'Agente',
            ];
        }

        // Company admin can only create agents or other company admins
        return [
            User::ROLE_COMPANY_ADMIN => 'Administrador de Empresa',
            User::ROLE_AGENT => 'Agente',
        ];
    }

    /**
     * Check authorization.
     */
    private function authorizeUser(User $targetUser)
    {
        $authUser = Auth::user();

        if ($authUser->isSuperAdmin()) {
            return; // Super admin can access any user
        }

        // Company admin can only access users from their company
        if ($targetUser->company_id !== $authUser->company_id) {
            abort(403, 'No tiene permisos para acceder a este usuario.');
        }
    }
}
