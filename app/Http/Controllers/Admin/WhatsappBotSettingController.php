<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappBotSetting;
use App\Models\WhatsappBotPromotion;
use App\Services\BotTrainingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Configuración del bot que administra el NEGOCIO (no el superadmin): tono,
 * reglas, cierre, relevo y promociones. El entrenamiento por capturas usa
 * visión para destilar el perfil de tono.
 */
class WhatsappBotSettingController extends Controller
{
    private function guardCompany(): int
    {
        $user = auth()->user();
        abort_unless($user->canAccessModule('whatsapp'), 403, 'Sin permiso para el bot de WhatsApp.');
        abort_if(empty($user->company_id), 403, 'Tu usuario no está asociado a una empresa.');
        return (int) $user->company_id;
    }

    public function edit()
    {
        $companyId = $this->guardCompany();

        $settings   = WhatsappBotSetting::firstOrNew(['company_id' => $companyId]);
        $handoff    = $settings->handoffConfig();
        $promotions = WhatsappBotPromotion::available()
            ? WhatsappBotPromotion::forCompany($companyId)->latest()->get()
            : collect();

        return view('admin.whatsapp.settings', compact('settings', 'handoff', 'promotions'));
    }

    public function update(Request $request)
    {
        $companyId = $this->guardCompany();

        $data = $request->validate([
            'store_name'         => 'nullable|string|max:150',
            'notify_email'       => 'nullable|email|max:150',
            'training_profile'   => 'nullable|string|max:8000',
            'custom_rules'       => 'nullable|string|max:8000',
            'order_instructions' => 'nullable|string|max:8000',
            'handoff'            => 'nullable|array',
        ]);

        $settings = WhatsappBotSetting::firstOrNew(['company_id' => $companyId]);
        $settings->fill([
            'company_id'         => $companyId,
            'store_name'         => $data['store_name'] ?? null,
            'notify_email'       => $data['notify_email'] ?? null,
            'training_profile'   => $data['training_profile'] ?? null,
            'custom_rules'       => $data['custom_rules'] ?? null,
            'order_instructions' => $data['order_instructions'] ?? null,
            'handoff'            => $this->normalizeHandoff($request->input('handoff', [])),
        ]);
        $settings->save();

        return back()->with('success', 'Configuración del bot actualizada.');
    }

    /**
     * Entrenamiento por capturas: sube imágenes, la IA destila el perfil de tono.
     */
    public function train(Request $request)
    {
        $companyId = $this->guardCompany();

        $request->validate([
            'screenshots'   => 'required|array|min:1|max:8',
            'screenshots.*' => 'image|mimes:jpeg,png,webp,gif|max:5120',
            'notes'         => 'nullable|string|max:1000',
            'replace'       => 'nullable|boolean',
        ], [], ['screenshots.*' => 'imagen']);

        $settings = WhatsappBotSetting::firstOrNew(['company_id' => $companyId]);

        $stored = [];
        $paths  = [];
        foreach ($request->file('screenshots') as $file) {
            $rel = $file->store('whatsapp/training/' . $companyId, 'public');
            $stored[] = $rel;
            $paths[]  = Storage::disk('public')->path($rel);
        }

        $result = app(BotTrainingService::class)->buildProfile(
            $companyId,
            $paths,
            $request->boolean('replace') ? null : $settings->training_profile,
            $request->input('notes')
        );

        // Las capturas son datos sensibles: se borran tras el análisis.
        foreach ($stored as $rel) {
            Storage::disk('public')->delete($rel);
        }

        if (!$result['ok']) {
            return back()->with('error', 'No se pudo generar el perfil: ' . $result['error']);
        }

        $settings->fill(['company_id' => $companyId, 'training_profile' => $result['profile']])->save();

        return back()->with('success', sprintf(
            'Perfil de tono generado a partir de %d captura(s). Costo aprox: $%.4f. Revisalo y ajustalo si hace falta.',
            count($stored),
            $result['cost']
        ));
    }

    /* ── Promociones ── */

    public function storePromotion(Request $request)
    {
        $companyId = $this->guardCompany();

        $data = $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'required|string|max:1000',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
        ]);
        $data['company_id'] = $companyId;
        $data['active']     = true;

        WhatsappBotPromotion::create($data);

        return back()->with('success', 'Promoción agregada.');
    }

    public function togglePromotion(WhatsappBotPromotion $promotion)
    {
        $companyId = $this->guardCompany();
        abort_if($promotion->company_id !== $companyId, 403);

        $promotion->update(['active' => !$promotion->active]);

        return back()->with('success', 'Promoción actualizada.');
    }

    public function destroyPromotion(WhatsappBotPromotion $promotion)
    {
        $companyId = $this->guardCompany();
        abort_if($promotion->company_id !== $companyId, 403);

        $promotion->delete();

        return back()->with('success', 'Promoción eliminada.');
    }

    /**
     * Normaliza los campos del formulario de relevo a la forma de DEFAULT_HANDOFF.
     */
    private function normalizeHandoff(array $input): array
    {
        $bools = ['asks_for_human', 'complaint', 'asks_past_order', 'sends_receipt', 'sends_voice_note', 'not_found'];
        $out = [];
        foreach ($bools as $b) {
            $out[$b] = !empty($input[$b]);
        }
        $out['keywords']        = trim((string) ($input['keywords'] ?? ''));
        $out['resume_after_h']  = max(0, (int) ($input['resume_after_h'] ?? 2));
        $out['max_messages']    = max(0, (int) ($input['max_messages'] ?? 0));
        $out['handoff_message'] = trim((string) ($input['handoff_message'] ?? WhatsappBotSetting::DEFAULT_HANDOFF['handoff_message']));

        return array_merge(WhatsappBotSetting::DEFAULT_HANDOFF, $out);
    }
}
