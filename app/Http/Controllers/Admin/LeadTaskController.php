<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\LeadTask;
use App\Lead;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeadTaskController extends Controller
{
    // ── Authorization ─────────────────────────────────────────────────────────

    private function authorizeAccess(LeadTask $task): void
    {
        if ($task->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }

    // ── Validation rules ──────────────────────────────────────────────────────

    private function validationRules(): array
    {
        return [
            'title'       => 'required|string|max:200',
            'type'        => 'required|in:call,email,whatsapp,visit,meeting,document,other',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_at'      => 'nullable|date',
            'description' => 'nullable|string|max:1000',
        ];
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user      = auth()->user();
        $companyId = $user->company_id;

        $query = LeadTask::with(['lead', 'assignee'])
            ->where('company_id', $companyId);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->filled('due_filter')) {
            match ($request->due_filter) {
                'today'    => $query->whereDate('due_at', today()),
                'overdue'  => $query->where('due_at', '<', now())
                                    ->whereNotIn('status', ['completed', 'cancelled']),
                'upcoming' => $query->where('due_at', '>', now()),
                default    => null,
            };
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->orderByRaw("FIELD(priority,'urgent','high','medium','low')")
                       ->orderBy('due_at')
                       ->paginate(20)
                       ->withQueryString();

        // Stats
        $base = LeadTask::where('company_id', $companyId);
        $stats = [
            'total'     => (clone $base)->count(),
            'pending'   => (clone $base)->whereIn('status', ['pending', 'in_progress'])->count(),
            'overdue'   => (clone $base)->where('due_at', '<', now())
                                        ->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'today'     => (clone $base)->whereDate('due_at', today())
                                        ->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
        ];

        $agents = User::where('company_id', $companyId)
                      ->where('status', 'active')
                      ->orderBy('name')
                      ->get();

        $filters = $request->only(['status', 'assigned_to', 'lead_id', 'due_filter', 'search']);

        return view('admin.crm.tasks.index', compact('tasks', 'stats', 'agents', 'filters'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $user      = auth()->user();
        $companyId = $user->company_id;

        $leads = Lead::where('company_id', $companyId)
                     ->orderBy('name')
                     ->get();

        $agents = User::where('company_id', $companyId)
                      ->where('status', 'active')
                      ->orderBy('name')
                      ->get();

        $lead = null;
        if ($request->filled('lead_id')) {
            $lead = Lead::where('company_id', $companyId)
                        ->find($request->lead_id);
        }

        return view('admin.crm.tasks.create', compact('leads', 'agents', 'lead'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge($this->validationRules(), [
            'lead_id' => 'required|exists:leads,id',
            'status'  => 'nullable|in:pending,in_progress,completed,cancelled',
        ]));

        $user = auth()->user();

        // Ensure the lead belongs to this company
        $lead = Lead::where('company_id', $user->company_id)->findOrFail($validated['lead_id']);

        LeadTask::create(array_merge($validated, [
            'company_id'  => $user->company_id,
            'created_by'  => $user->id,
            'status'      => $validated['status'] ?? 'pending',
        ]));

        if ($request->filled('_back')) {
            return redirect($request->_back)->with('success', 'Tarea creada correctamente.');
        }

        if ($request->filled('lead_id')) {
            return redirect()->route('admin.crm.leads.show', $validated['lead_id'])
                             ->with('success', 'Tarea creada correctamente.')
                             ->withFragment('tab=citas');
        }

        return redirect()->route('admin.crm.tasks.index')
                         ->with('success', 'Tarea creada correctamente.');
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(LeadTask $task)
    {
        $this->authorizeAccess($task);

        $user      = auth()->user();
        $companyId = $user->company_id;

        $leads = Lead::where('company_id', $companyId)
                     ->orderBy('name')
                     ->get();

        $agents = User::where('company_id', $companyId)
                      ->where('status', 'active')
                      ->orderBy('name')
                      ->get();

        $task->load(['lead', 'assignee', 'creator']);

        return view('admin.crm.tasks.edit', compact('task', 'leads', 'agents'));
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, LeadTask $task)
    {
        $this->authorizeAccess($task);

        $validated = $request->validate(array_merge($this->validationRules(), [
            'status'           => 'nullable|in:pending,in_progress,completed,cancelled',
            'completion_notes' => 'nullable|string|max:1000',
        ]));

        if (($validated['status'] ?? $task->status) === 'completed' && !$task->completed_at) {
            $validated['completed_at'] = now();
        }

        $task->update($validated);

        return redirect()->route('admin.crm.tasks.edit', $task)
                         ->with('success', 'Tarea actualizada correctamente.');
    }

    // ── Complete ──────────────────────────────────────────────────────────────

    public function complete(Request $request, LeadTask $task)
    {
        $this->authorizeAccess($task);

        $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
        ]);

        $task->update([
            'status'           => 'completed',
            'completed_at'     => now(),
            'completion_notes' => $request->completion_notes,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tarea marcada como completada.',
                'task'    => [
                    'id'           => $task->id,
                    'status'       => 'completed',
                    'completed_at' => $task->completed_at->format('d/m/Y H:i'),
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Tarea marcada como completada.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(LeadTask $task)
    {
        $this->authorizeAccess($task);

        $leadId = $task->lead_id;
        $task->delete();

        return redirect()->back()->with('success', 'Tarea eliminada.');
    }
}
