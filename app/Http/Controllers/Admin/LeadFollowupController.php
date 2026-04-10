<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventLead;
use App\Models\LeadFollowup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadFollowupController extends Controller
{
    /**
     * Historial de seguimientos de un lead (JSON).
     */
    public function index(EventLead $eventLead)
    {
        $this->authorizeLeadAccess($eventLead);

        $followups = $eventLead->followups()
            ->with('agent:id,name')
            ->get()
            ->map(fn($f) => [
                'id'               => $f->id,
                'action'           => $f->action,
                'action_label'     => LeadFollowup::actions()[$f->action]['label'] ?? $f->action,
                'outcome'          => $f->outcome,
                'outcome_label'    => LeadFollowup::outcomes()[$f->outcome]['label'] ?? $f->outcome,
                'outcome_color'    => LeadFollowup::outcomes()[$f->outcome]['color'] ?? 'secondary',
                'notes'            => $f->notes,
                'next_followup_at' => $f->next_followup_at?->format('d/m/Y H:i'),
                'agent'            => $f->agent?->name ?? 'Sistema',
                'created_at'       => $f->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json(['followups' => $followups]);
    }

    /**
     * Registrar un nuevo seguimiento.
     */
    public function store(Request $request, EventLead $eventLead)
    {
        $this->authorizeLeadAccess($eventLead);

        $request->validate([
            'action'          => 'required|in:' . implode(',', array_keys(LeadFollowup::actions())),
            'outcome'         => 'required|in:' . implode(',', array_keys(LeadFollowup::outcomes())),
            'notes'           => 'nullable|string|max:2000',
            'next_followup_at'=> 'nullable|date',
        ]);

        $followup = LeadFollowup::create([
            'event_lead_id'   => $eventLead->id,
            'user_id'         => Auth::id(),
            'action'          => $request->action,
            'outcome'         => $request->outcome,
            'notes'           => $request->notes,
            'next_followup_at'=> $request->next_followup_at,
        ]);

        // Actualizar estado del lead según el resultado
        if ($request->outcome === 'converted') {
            $eventLead->update([
                'sale_status'  => 'converted',
                'converted_at' => now(),
            ]);
        } elseif ($request->outcome === 'lost') {
            $eventLead->update(['sale_status' => 'lost']);
        } elseif (in_array($request->outcome, ['interested', 'thinking', 'follow_up_later'])) {
            if ($eventLead->sale_status === 'open') {
                $eventLead->update(['sale_status' => 'in_progress']);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success'   => true,
                'followup'  => [
                    'id'               => $followup->id,
                    'action_label'     => LeadFollowup::actions()[$followup->action]['label'] ?? $followup->action,
                    'outcome_label'    => LeadFollowup::outcomes()[$followup->outcome]['label'] ?? $followup->outcome,
                    'outcome_color'    => LeadFollowup::outcomes()[$followup->outcome]['color'] ?? 'secondary',
                    'notes'            => $followup->notes,
                    'next_followup_at' => $followup->next_followup_at?->format('d/m/Y H:i'),
                    'created_at'       => $followup->created_at->format('d/m/Y H:i'),
                ],
                'lead_status' => $eventLead->fresh()->sale_status,
            ]);
        }

        return back()->with('success', 'Seguimiento registrado.');
    }

    private function authorizeLeadAccess(EventLead $lead): void
    {
        $user = Auth::user();
        if ($user->isSuperAdmin()) return;
        if ($user->isCompanyAdmin() && $lead->company_id === $user->company_id) return;
        // Agent can access only their own leads
        if ($user->isAgent() && $lead->captured_by_user_id === $user->id) return;
        abort(403);
    }
}
