<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Company;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index()
    {
        $companies = Company::withCount(['users', 'categories', 'subscriptions'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        $owners = User::whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_COMPANY_ADMIN])
            ->orderBy('name')
            ->get();

        return view('admin.companies.create', compact('owners'));
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        $data = $request->only([
            'name', 'legal_name', 'tax_id', 'email', 'phone', 'address', 'owner_id'
        ]);

        $data['slug'] = Str::slug($request->name);
        $data['status'] = 'active';

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('companies/logos', 'public');
        }

        $company = Company::create($data);

        // If owner specified, update their company_id
        if ($request->owner_id) {
            User::where('id', $request->owner_id)->update([
                'company_id' => $company->id,
                'role' => User::ROLE_COMPANY_ADMIN,
            ]);
        }

        return redirect()->route('admin.companies.index')
            ->with('success', 'Empresa creada correctamente.');
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company)
    {
        $company->load(['owner', 'users', 'categories', 'subscriptions.package']);

        return view('admin.companies.show', compact('company'));
    }

    /**
     * Show the form for editing the company.
     */
    public function edit(Company $company)
    {
        $owners = User::whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_COMPANY_ADMIN])
            ->orderBy('name')
            ->get();

        return view('admin.companies.edit', compact('company', 'owners'));
    }

    /**
     * Update the specified company.
     */
    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'owner_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $data = $request->only([
            'name', 'legal_name', 'tax_id', 'email', 'phone', 'address', 'owner_id', 'status'
        ]);
        $data['other_kiosk'] = $request->boolean('other_kiosk');

        // Guardar categorías y PIN del kiosko other dentro del JSON settings
        $currentSettings = $company->settings ?? [];
        $currentSettings['other_kiosk_categories'] = $request->input('other_kiosk_categories', '');
        $currentSettings['agent_kiosk_pin']         = $request->input('agent_kiosk_pin', '');
        $data['settings'] = $currentSettings;

        if ($request->name !== $company->name) {
            $data['slug'] = Str::slug($request->name);
        }

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                \Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('companies/logos', 'public');
        }

        $company->update($data);

        return redirect()->route('admin.companies.index')
            ->with('success', 'Empresa actualizada correctamente.');
    }

    /**
     * Toggle company status.
     */
    public function toggleStatus(Company $company)
    {
        $newStatus = $company->status === 'active' ? 'inactive' : 'active';
        $company->update(['status' => $newStatus]);

        return redirect()->route('admin.companies.index')
            ->with('success', 'Estado de la empresa actualizado.');
    }

    /**
     * Remove the specified company.
     */
    public function destroy(Company $company)
    {
        // Check if company has active subscriptions or users
        if ($company->users()->where('status', 'active')->count() > 0) {
            return redirect()->route('admin.companies.index')
                ->with('error', 'No se puede eliminar: la empresa tiene usuarios activos.');
        }

        if ($company->subscriptions()->whereIn('status', ['active', 'trial'])->count() > 0) {
            return redirect()->route('admin.companies.index')
                ->with('error', 'No se puede eliminar: la empresa tiene suscripciones activas.');
        }

        if ($company->logo) {
            \Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', 'Empresa eliminada correctamente.');
    }
}
