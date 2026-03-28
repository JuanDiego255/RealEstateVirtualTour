<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Lead;
use App\LeadActivity;
use App\Properties;
use App\Vehicle;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeadController extends Controller
{
    /**
     * Display a listing of leads.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Lead::with(['user', 'property', 'vehicle'])
            ->byCompany($user->company_id);

        // Filtros
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $leads = $query->paginate(20)->withQueryString();

        // Estadísticas
        $stats = [
            'total' => Lead::byCompany($user->company_id)->count(),
            'new' => Lead::byCompany($user->company_id)->byStatus('new')->count(),
            'active' => Lead::byCompany($user->company_id)->active()->count(),
            'won' => Lead::byCompany($user->company_id)->byStatus('won')->count(),
            'needs_follow_up' => Lead::byCompany($user->company_id)->needsFollowUp()->count(),
        ];

        $agents = User::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.crm.leads.index', compact('leads', 'stats', 'agents'));
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create()
    {
        $user = auth()->user();

        $agents = User::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $properties = Properties::whereHas('category', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->where('status', 'available')->get();

        $vehicles = Vehicle::whereHas('category', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->where('status', 'available')->get();

        return view('admin.crm.leads.create', compact('agents', 'properties', 'vehicles'));
    }

    /**
     * Store a newly created lead.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'source' => 'required|in:' . implode(',', array_keys(Lead::getSources())),
            'priority' => 'required|in:' . implode(',', array_keys(Lead::getPriorities())),
            'interest_type' => 'required|in:buy,rent,sell,other',
            'user_id' => 'nullable|exists:users,id',
            'property_id' => 'nullable|exists:properties,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'budget_currency' => 'nullable|in:CRC,USD',
            'notes' => 'nullable|string',
            'requirements' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
        ]);

        $user = auth()->user();

        $lead = Lead::create([
            'company_id' => $user->company_id,
            'user_id' => $request->user_id ?? $user->id,
            'property_id' => $request->property_id,
            'vehicle_id' => $request->vehicle_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'status' => 'new',
            'source' => $request->source,
            'priority' => $request->priority,
            'interest_type' => $request->interest_type,
            'budget_min' => $request->budget_min,
            'budget_max' => $request->budget_max,
            'budget_currency' => $request->budget_currency ?? 'CRC',
            'notes' => $request->notes,
            'requirements' => $request->requirements,
            'next_follow_up' => $request->next_follow_up,
            'first_contact_at' => now(),
        ]);

        // Registrar actividad de creación
        $lead->logActivity('note', [
            'subject' => 'Lead creado',
            'description' => 'Nuevo lead registrado en el sistema.',
        ]);

        return redirect()->route('admin.crm.leads.show', $lead)
            ->with('success', 'Lead creado correctamente.');
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        $lead->load([
            'user',
            'property',
            'vehicle',
            'activities.user',
            'appointments' => fn($q) => $q->orderBy('starts_at', 'desc')->limit(5),
            'reminders' => fn($q) => $q->pending()->orderBy('remind_at'),
        ]);

        return view('admin.crm.leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the lead.
     */
    public function edit(Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        $user = auth()->user();

        $agents = User::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $properties = Properties::whereHas('category', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->get();

        $vehicles = Vehicle::whereHas('category', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->get();

        return view('admin.crm.leads.edit', compact('lead', 'agents', 'properties', 'vehicles'));
    }

    /**
     * Update the specified lead.
     */
    public function update(Request $request, Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'source' => 'required|in:' . implode(',', array_keys(Lead::getSources())),
            'priority' => 'required|in:' . implode(',', array_keys(Lead::getPriorities())),
            'interest_type' => 'required|in:buy,rent,sell,other',
            'user_id' => 'nullable|exists:users,id',
            'property_id' => 'nullable|exists:properties,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'budget_currency' => 'nullable|in:CRC,USD',
            'notes' => 'nullable|string',
            'requirements' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
        ]);

        $lead->update($request->only([
            'name', 'email', 'phone', 'whatsapp', 'source', 'priority',
            'interest_type', 'user_id', 'property_id', 'vehicle_id',
            'budget_min', 'budget_max', 'budget_currency', 'notes',
            'requirements', 'next_follow_up',
        ]));

        return redirect()->route('admin.crm.leads.show', $lead)
            ->with('success', 'Lead actualizado correctamente.');
    }

    /**
     * Update lead status.
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Lead::getStatuses())),
            'note' => 'nullable|string',
        ]);

        $lead->changeStatus($request->status, $request->note);

        return redirect()->back()
            ->with('success', 'Estado actualizado correctamente.');
    }

    /**
     * Add activity to lead.
     */
    public function addActivity(Request $request, Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(LeadActivity::getTypes())),
            'subject' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'call_result' => 'nullable|in:' . implode(',', array_keys(LeadActivity::getCallResults())),
            'call_duration' => 'nullable|integer|min:0',
            'activity_at' => 'nullable|date',
        ]);

        $lead->logActivity($request->type, [
            'subject' => $request->subject,
            'description' => $request->description,
            'call_result' => $request->call_result,
            'call_duration' => $request->call_duration,
            'property_id' => $request->property_id,
            'vehicle_id' => $request->vehicle_id,
            'activity_at' => $request->activity_at ?? now(),
        ]);

        return redirect()->back()
            ->with('success', 'Actividad registrada correctamente.');
    }

    /**
     * Remove the specified lead.
     */
    public function destroy(Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        $lead->delete();

        return redirect()->route('admin.crm.leads.index')
            ->with('success', 'Lead eliminado correctamente.');
    }

    /**
     * Leads pipeline view (Kanban).
     */
    public function pipeline()
    {
        $user = auth()->user();

        $statuses = Lead::getStatuses();
        $leadsByStatus = [];

        foreach (array_keys($statuses) as $status) {
            $leadsByStatus[$status] = Lead::with(['user', 'property', 'vehicle'])
                ->byCompany($user->company_id)
                ->byStatus($status)
                ->orderBy('updated_at', 'desc')
                ->limit(50)
                ->get();
        }

        return view('admin.crm.leads.pipeline', compact('statuses', 'leadsByStatus'));
    }

    /**
     * Update lead status via API (for Kanban drag-drop).
     */
    public function updateStatusApi(Request $request, Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Lead::getStatuses())),
        ]);

        $lead->changeStatus($request->status);

        return response()->json(['success' => true]);
    }

    /**
     * Leads that need follow-up today.
     */
    public function followUps()
    {
        $user = auth()->user();

        $leads = Lead::with(['user', 'property', 'vehicle'])
            ->byCompany($user->company_id)
            ->needsFollowUp()
            ->orderBy('next_follow_up', 'asc')
            ->paginate(20);

        return view('admin.crm.leads.follow-ups', compact('leads'));
    }

    /**
     * Verify company access.
     */
    private function authorizeCompanyAccess(Lead $lead): void
    {
        if ($lead->company_id !== auth()->user()->company_id) {
            abort(403, 'No tienes acceso a este lead.');
        }
    }
}
