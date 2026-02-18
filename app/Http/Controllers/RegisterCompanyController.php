<?php

namespace App\Http\Controllers;

use App\Company;
use App\Package;
use App\Subscription;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RegisterCompanyController extends Controller
{
    /**
     * Mostrar la página de planes/pricing.
     */
    public function pricing()
    {
        $packages = Package::active()->ordered()->get();
        return view('frontend.pricing', compact('packages'));
    }

    /**
     * Mostrar el formulario de registro de empresa.
     */
    public function showForm(Request $request)
    {
        $packages = Package::active()->ordered()->get();
        $selectedPackage = null;

        if ($request->filled('package')) {
            $selectedPackage = Package::active()->find($request->package);
        }

        return view('frontend.register-company', compact('packages', 'selectedPackage'));
    }

    /**
     * Procesar el registro de empresa.
     */
    public function register(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_phone' => 'required|string|max:50',
            'company_email' => 'required|email|max:255',
            'company_address' => 'nullable|string|max:500',
            'tax_id' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:6|confirmed',
            'package_id' => 'required|exists:packages,id',
        ], [
            'company_name.required' => 'El nombre de la empresa es requerido.',
            'company_phone.required' => 'El teléfono de la empresa es requerido.',
            'company_email.required' => 'El correo de la empresa es requerido.',
            'name.required' => 'Su nombre es requerido.',
            'email.required' => 'Su correo electrónico es requerido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es requerida.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'package_id.required' => 'Debe seleccionar un plan.',
        ]);

        $package = Package::active()->findOrFail($request->package_id);

        DB::beginTransaction();
        try {
            // Crear usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => User::ROLE_COMPANY_ADMIN,
                'status' => User::STATUS_ACTIVE,
            ]);

            // Crear empresa
            $company = Company::create([
                'name' => $request->company_name,
                'phone' => $request->company_phone,
                'email' => $request->company_email,
                'address' => $request->company_address,
                'tax_id' => $request->tax_id,
                'owner_id' => $user->id,
                'status' => Company::STATUS_ACTIVE,
            ]);

            // Asignar empresa al usuario
            $user->update(['company_id' => $company->id]);

            // Crear suscripción
            $startsAt = Carbon::now();
            $endsAt = match($package->billing_period) {
                'monthly' => $startsAt->copy()->addMonth(),
                'quarterly' => $startsAt->copy()->addMonths(3),
                'yearly' => $startsAt->copy()->addYear(),
                default => $startsAt->copy()->addMonth(),
            };

            $status = $package->trial_days > 0 ? 'trial' : 'pending';

            $subscription = Subscription::create([
                'company_id' => $company->id,
                'package_id' => $package->id,
                'starts_at' => $startsAt,
                'ends_at' => $package->trial_days > 0
                    ? $startsAt->copy()->addDays($package->trial_days)
                    : $endsAt,
                'status' => $status,
                'payment_method' => 'transfer',
                'created_by' => $user->id,
            ]);

            // Si tiene trial, iniciar trial
            if ($package->trial_days > 0 && method_exists($subscription, 'startTrial')) {
                $subscription->startTrial();
            }

            DB::commit();

            // Iniciar sesión automáticamente
            Auth::login($user);

            if ($status === 'trial') {
                return redirect()->route('home')
                    ->with('success', '¡Bienvenido! Su empresa ha sido registrada con un período de prueba de ' . $package->trial_days . ' días.');
            }

            return redirect()->route('home')
                ->with('success', '¡Bienvenido! Su empresa ha sido registrada. Su suscripción está pendiente de aprobación.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Ocurrió un error al registrar la empresa. Intente nuevamente.')
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }
}
