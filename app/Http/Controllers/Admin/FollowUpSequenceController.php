<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FollowUpSequence;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

/**
 * CRUD de secuencias de seguimiento (nurturing) por empresa. Cada secuencia tiene
 * pasos (demora + canal + mensaje). La administra el admin de la empresa.
 */
class FollowUpSequenceController extends Controller
{
    private function guardCompany(): int
    {
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403, 'Solo un administrador puede configurar secuencias.');
        abort_if(empty($user->company_id), 403, 'Tu usuario no está asociado a una empresa.');
        return (int) $user->company_id;
    }

    private function authorizeOwnership(FollowUpSequence $sequence, int $companyId): void
    {
        abort_if($sequence->company_id !== $companyId, 403);
    }

    public function index()
    {
        $companyId = $this->guardCompany();

        $sequences = FollowUpSequence::forCompany($companyId)
            ->withCount(['steps', 'enrollments'])
            ->latest()
            ->get();

        return view('admin.crm.followups.index', compact('sequences'));
    }

    public function create()
    {
        $companyId = $this->guardCompany();
        $sequence  = new FollowUpSequence(['trigger' => FollowUpSequence::TRIGGER_LEAD_CREATED, 'is_active' => true, 'stop_on_reply' => true]);
        $templates = $this->templates($companyId);

        return view('admin.crm.followups.form', compact('sequence', 'templates'));
    }

    public function store(Request $request)
    {
        $companyId = $this->guardCompany();
        $data = $this->validated($request);

        $sequence = FollowUpSequence::create([
            'company_id'    => $companyId,
            'name'          => $data['name'],
            'trigger'       => $data['trigger'],
            'is_active'     => $request->boolean('is_active'),
            'stop_on_reply' => $request->boolean('stop_on_reply'),
        ]);
        $this->syncSteps($sequence, $request->input('steps', []));

        return redirect()->route('admin.crm.followups.index')->with('success', 'Secuencia creada.');
    }

    public function edit(FollowUpSequence $followup)
    {
        $companyId = $this->guardCompany();
        $this->authorizeOwnership($followup, $companyId);
        $sequence  = $followup->load('steps');
        $templates = $this->templates($companyId);

        return view('admin.crm.followups.form', compact('sequence', 'templates'));
    }

    public function update(Request $request, FollowUpSequence $followup)
    {
        $companyId = $this->guardCompany();
        $this->authorizeOwnership($followup, $companyId);
        $data = $this->validated($request);

        $followup->update([
            'name'          => $data['name'],
            'trigger'       => $data['trigger'],
            'is_active'     => $request->boolean('is_active'),
            'stop_on_reply' => $request->boolean('stop_on_reply'),
        ]);
        $this->syncSteps($followup, $request->input('steps', []));

        return redirect()->route('admin.crm.followups.index')->with('success', 'Secuencia actualizada.');
    }

    public function toggle(FollowUpSequence $followup)
    {
        $companyId = $this->guardCompany();
        $this->authorizeOwnership($followup, $companyId);
        $followup->update(['is_active' => !$followup->is_active]);

        return back()->with('success', 'Secuencia actualizada.');
    }

    public function destroy(FollowUpSequence $followup)
    {
        $companyId = $this->guardCompany();
        $this->authorizeOwnership($followup, $companyId);
        $followup->steps()->delete();
        $followup->enrollments()->delete();
        $followup->delete();

        return back()->with('success', 'Secuencia eliminada.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'                     => 'required|string|max:150',
            'trigger'                  => 'required|in:lead_created,manual',
            'steps'                    => 'required|array|min:1',
            'steps.*.delay_hours'      => 'required|integer|min:0|max:8760',
            'steps.*.channel'          => 'required|in:whatsapp,email',
            'steps.*.message_template_id' => 'nullable|integer',
            'steps.*.subject'          => 'nullable|string|max:190',
            'steps.*.body'             => 'nullable|string|max:2000',
        ]);

        // Cada paso debe tener plantilla o cuerpo.
        foreach ($data['steps'] as $i => $step) {
            if (empty($step['message_template_id']) && empty(trim($step['body'] ?? ''))) {
                abort(422, 'Cada paso necesita una plantilla o un mensaje.');
            }
        }
        return $data;
    }

    private function syncSteps(FollowUpSequence $sequence, array $steps): void
    {
        $sequence->steps()->delete();
        $position = 0;
        foreach ($steps as $step) {
            $sequence->steps()->create([
                'position'            => $position++,
                'delay_hours'         => (int) ($step['delay_hours'] ?? 0),
                'channel'             => $step['channel'] ?? 'whatsapp',
                'message_template_id' => $step['message_template_id'] ?: null,
                'subject'             => $step['subject'] ?? null,
                'body'                => $step['body'] ?? null,
            ]);
        }
    }

    private function templates(int $companyId)
    {
        return MessageTemplate::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
    }
}
