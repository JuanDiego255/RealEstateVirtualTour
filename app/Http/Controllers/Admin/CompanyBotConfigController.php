<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Models\CompanyAiSetting;
use App\Models\CompanyWhatsappBot;
use Illuminate\Http\Request;

/**
 * Configuración por empresa del bot de WhatsApp y de la IA (Anthropic).
 * Solo la administra el superadmin (el grupo de rutas ya está gateado con
 * role:super_admin). Los secretos solo se reemplazan si se envían.
 */
class CompanyBotConfigController extends Controller
{
    /* ─────────────── WhatsApp ─────────────── */

    public function whatsappEdit(Company $company)
    {
        $bot = CompanyWhatsappBot::firstOrNew(['company_id' => $company->id]);

        return view('admin.companies.whatsapp', [
            'company'    => $company,
            'bot'        => $bot,
            'webhookUrl' => url('/webhook/whatsapp'),
            'verifyToken'=> config('whatsapp.verify_token'),
            'graph'      => config('whatsapp.graph_version', 'v21.0'),
        ]);
    }

    public function whatsappUpdate(Request $request, Company $company)
    {
        $this->normalizeTimes($request);

        $request->validate([
            'phone_number_id'      => 'nullable|string|max:100',
            'business_hours_start' => 'nullable|date_format:H:i',
            'business_hours_end'   => 'nullable|date_format:H:i',
            'activation_mode'      => 'nullable|in:immediate,delayed,manual',
        ]);

        // phone_number_id único entre empresas (mensaje claro además del índice)
        if ($request->filled('phone_number_id')) {
            $enUso = CompanyWhatsappBot::where('phone_number_id', $request->phone_number_id)
                ->where('company_id', '!=', $company->id)
                ->value('company_id');
            if ($enUso) {
                return back()->withInput()->with([
                    'error' => "Ese Phone Number ID ya está asignado a otra empresa (#{$enUso}).",
                ]);
            }
        }

        $bot = CompanyWhatsappBot::firstOrNew(['company_id' => $company->id]);

        $bot->company_id      = $company->id;
        $bot->enabled         = $request->boolean('enabled');
        $bot->phone_number_id = $request->input('phone_number_id') ?: null;
        $bot->waba_id         = $request->input('waba_id') ?: null;
        $bot->display_phone   = $request->input('display_phone') ?: null;
        $bot->graph_version   = $request->input('graph_version') ?: null;
        $bot->business_type   = $request->input('business_type');
        $bot->notes           = $request->input('notes');

        // Secretos: solo se reemplazan si se envían
        if ($request->filled('access_token')) {
            $bot->access_token = trim($request->access_token);
        }
        if ($request->filled('app_secret')) {
            $bot->app_secret = trim($request->app_secret);
        }
        if ($request->filled('verify_token')) {
            $bot->verify_token = trim($request->verify_token);
        }

        // Plan y cuotas
        $bot->plan                         = $request->input('plan');
        $bot->included_conversations       = $request->filled('included_conversations') ? max(0, (int) $request->included_conversations) : null;
        $bot->plan_price_usd               = $request->filled('plan_price_usd') ? (float) $request->plan_price_usd : null;
        $bot->extra_conversation_price_usd = $request->filled('extra_conversation_price_usd') ? (float) $request->extra_conversation_price_usd : null;
        $bot->allow_overage                = $request->boolean('allow_overage');
        $bot->overage_cap_usd              = $request->filled('overage_cap_usd') ? (float) $request->overage_cap_usd : null;
        $bot->max_vehicles_per_reply       = $request->filled('max_vehicles_per_reply') ? max(1, (int) $request->max_vehicles_per_reply) : 3;
        $bot->allow_financing_quote        = $request->boolean('allow_financing_quote');

        // Cuándo responde
        $bot->activation_mode        = $request->input('activation_mode', 'immediate');
        $bot->delay_minutes          = $request->filled('delay_minutes') ? max(0, (int) $request->delay_minutes) : 10;
        $bot->business_hours_start   = $request->input('business_hours_start') ?: null;
        $bot->business_hours_end     = $request->input('business_hours_end') ?: null;
        $bot->instant_outside_hours  = $request->boolean('instant_outside_hours');

        $bot->save();

        $aviso = $bot->isUsable()
            ? 'Guardado. El bot está listo para recibir mensajes.'
            : 'Guardado. Faltan datos de conexión o está apagado, así que el bot no responderá.';

        return redirect()->route('admin.companies.whatsapp.edit', $company)->with('success', $aviso);
    }

    /* ─────────────── IA (Anthropic) ─────────────── */

    public function aiEdit(Company $company)
    {
        $ai = CompanyAiSetting::firstOrNew(['company_id' => $company->id]);

        return view('admin.companies.ai', [
            'company' => $company,
            'ai'      => $ai,
            'models'  => CompanyAiSetting::MODELS,
        ]);
    }

    public function aiUpdate(Request $request, Company $company)
    {
        $ai = CompanyAiSetting::firstOrNew(['company_id' => $company->id]);

        $ai->company_id = $company->id;
        $ai->enabled    = $request->boolean('enabled');

        // Modelo validado contra el catálogo
        $ai->model = array_key_exists($request->model, CompanyAiSetting::MODELS)
            ? $request->model
            : CompanyAiSetting::DEFAULT_MODEL;

        // API key: solo se reemplaza si se envía
        if ($request->filled('api_key')) {
            $ai->api_key = trim($request->api_key);
        }

        // Plan y cuota (null = heredá del plan)
        $ai->plan                       = $request->input('plan');
        $ai->included_generations       = $request->filled('included_generations') ? max(0, (int) $request->included_generations) : null;
        $ai->plan_price_usd             = $request->filled('plan_price_usd') ? (float) $request->plan_price_usd : null;
        $ai->extra_generation_price_usd = $request->filled('extra_generation_price_usd') ? (float) $request->extra_generation_price_usd : null;
        $ai->allow_overage              = $request->boolean('allow_overage');
        $ai->overage_cap_usd            = $request->filled('overage_cap_usd') ? (float) $request->overage_cap_usd : null;

        // Personalización
        $ai->brand_voice   = $request->input('brand_voice');
        $ai->audience      = $request->input('audience');
        $ai->language      = $request->input('language');
        $ai->max_hashtags  = $request->filled('max_hashtags') ? max(0, (int) $request->max_hashtags) : null;
        $ai->system_prompt = $request->input('system_prompt');

        $ai->save();

        return redirect()->route('admin.companies.ai.edit', $company)->with('success', 'Configuración de IA guardada.');
    }

    /* ─────────────── Helpers ─────────────── */

    /**
     * MySQL devuelve TIME como HH:MM:SS y date_format:H:i lo rechaza al reguardar.
     */
    private function normalizeTimes(Request $request): void
    {
        foreach (['business_hours_start', 'business_hours_end'] as $campo) {
            $valor = $request->input($campo);
            if (is_string($valor) && preg_match('/^\s*(\d{1,2}):(\d{2})/', $valor, $m)) {
                $request->merge([$campo => sprintf('%02d:%02d', (int) $m[1], (int) $m[2])]);
            }
        }
    }
}
