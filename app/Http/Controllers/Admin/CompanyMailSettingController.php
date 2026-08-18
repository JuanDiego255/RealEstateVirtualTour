<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyMailSetting;
use App\Services\CompanyMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Configuración SMTP por empresa: la cuenta desde la que salen los correos del
 * CRM (avisos a asesores). Usa la clave de aplicaciones de terceros (app password
 * de Gmail / Outlook). La administra el admin de la empresa.
 */
class CompanyMailSettingController extends Controller
{
    private function guardCompany(): int
    {
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403, 'Solo un administrador puede configurar el correo.');
        abort_if(empty($user->company_id), 403, 'Tu usuario no está asociado a una empresa.');
        return (int) $user->company_id;
    }

    public function edit()
    {
        $companyId = $this->guardCompany();
        $setting = CompanyMailSetting::firstOrNew(['company_id' => $companyId]);

        $logs = \App\Models\MailLog::available()
            ? \App\Models\MailLog::forCompany($companyId)->latest()->limit(50)->get()
            : collect();

        $lastError = \App\Models\MailLog::available()
            ? \App\Models\MailLog::forCompany($companyId)->where('status', \App\Models\MailLog::STATUS_FAILED)->latest()->first()
            : null;

        return view('admin.mail.settings', compact('setting', 'logs', 'lastError'));
    }

    public function update(Request $request)
    {
        $companyId = $this->guardCompany();

        $data = $request->validate([
            'enabled'      => 'nullable|boolean',
            'from_name'    => 'nullable|string|max:150',
            'from_address' => 'nullable|email|max:150',
            'host'         => 'nullable|string|max:150',
            'port'         => 'nullable|integer|min:1|max:65535',
            'encryption'   => 'nullable|in:tls,ssl,none',
            'username'     => 'nullable|string|max:190',
            'password'     => 'nullable|string|max:190',
        ]);

        $setting = CompanyMailSetting::firstOrNew(['company_id' => $companyId]);
        $setting->company_id   = $companyId;
        $setting->enabled      = $request->boolean('enabled');
        $setting->from_name    = $data['from_name'] ?? null;
        $setting->from_address = $data['from_address'] ?? null;
        $setting->host         = $data['host'] ?? null;
        $setting->port         = $data['port'] ?? null;
        $setting->encryption   = ($data['encryption'] ?? null) === 'none' ? null : ($data['encryption'] ?? null);
        $setting->username      = $data['username'] ?? null;

        // La contraseña (app password) solo se reemplaza si se envía una nueva.
        if ($request->filled('password')) {
            $setting->password = $request->input('password');
        }

        $setting->save();

        return back()->with('success', 'Configuración de correo guardada.');
    }

    /**
     * Envía un correo de prueba con la cuenta configurada y guarda el resultado.
     */
    public function test(Request $request)
    {
        $companyId = $this->guardCompany();
        $setting = CompanyMailSetting::where('company_id', $companyId)->first();

        if (!$setting || !$setting->isUsable()) {
            return back()->with('error', 'Primero completá y habilitá la configuración SMTP.');
        }

        $to = $request->input('test_to') ?: $setting->from_address ?: auth()->user()->email;
        if (!$to) {
            return back()->with('error', 'No hay una dirección de destino para la prueba.');
        }

        $mailerName = CompanyMailer::mailerName($companyId);

        try {
            Mail::mailer($mailerName)->raw(
                'Este es un correo de prueba del CRM. Si lo recibiste, tu configuración SMTP funciona.',
                function ($m) use ($to, $setting) {
                    $m->to($to)->subject('Prueba de correo — CRM');
                    if ($setting->from_address) {
                        $m->from($setting->from_address, $setting->from_name);
                    }
                }
            );
            $setting->update(['last_test_at' => now(), 'last_test_ok' => true, 'last_test_error' => null]);

            return back()->with('success', 'Correo de prueba enviado a ' . $to . '. Revisá la bandeja.');
        } catch (\Throwable $e) {
            $setting->update(['last_test_at' => now(), 'last_test_ok' => false, 'last_test_error' => $e->getMessage()]);
            \App\Models\MailLog::recordFailed($companyId, $to, 'Prueba de correo — CRM', $e->getMessage(), 'test');

            return back()->with('error', 'Falló el envío de prueba: ' . $e->getMessage());
        }
    }
}
