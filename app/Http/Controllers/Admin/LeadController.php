<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Lead;
use App\LeadActivity;
use App\Properties;
use App\Reminder;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class LeadController extends Controller
{
    /**
     * Display a listing of leads (also handles AJAX partial requests).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Lead::with(['user', 'property', 'vehicle'])
            ->byCompany($user->company_id);

        // Agents can only see their own leads unless they have admin rights
        if (!$user->isAdmin()) {
            $query->byUser($user->id);
        }

        // Filtro por origen (evento vs agencia)
        if ($request->filled('origin')) {
            if ($request->origin === 'event') {
                $query->fromEvent();
            } elseif ($request->origin === 'agency') {
                $query->fromAgency();
            }
        }

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

        if ($request->boolean('hot_leads')) {
            $query->where('score', '>=', 70);
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $leads = $query->paginate(20)->withQueryString();

        // Estadísticas
        $baseQuery = Lead::byCompany($user->company_id);

        if ($request->filled('origin')) {
            if ($request->origin === 'event') {
                $baseQuery = Lead::byCompany($user->company_id)->fromEvent();
            } elseif ($request->origin === 'agency') {
                $baseQuery = Lead::byCompany($user->company_id)->fromAgency();
            }
        }

        $stats = [
            'total'          => (clone $baseQuery)->count(),
            'new'            => (clone $baseQuery)->byStatus('new')->count(),
            'active'         => (clone $baseQuery)->active()->count(),
            'won'            => (clone $baseQuery)->byStatus('won')->count(),
            'lost'           => (clone $baseQuery)->byStatus('lost')->count(),
            'needs_follow_up'=> (clone $baseQuery)->needsFollowUp()->count(),
            'from_events'    => Lead::byCompany($user->company_id)->fromEvent()->count(),
        ];

        $agents = User::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Respuesta parcial para filtros AJAX en tiempo real
        if ($request->ajax() || $request->boolean('ajax')) {
            $tableHtml      = view('admin.crm.leads._table', compact('leads', 'agents'))->render();
            $paginationHtml = $leads->links()->toHtml();
            return response()->json([
                'html'       => $tableHtml,
                'pagination' => $paginationHtml,
                'stats'      => $stats,
            ]);
        }

        // Conteos por estado para sidebar del pipeline
        $pipelineCounts = Lead::byCompany($user->company_id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.crm.leads.index', compact('leads', 'stats', 'agents', 'pipelineCounts'));
    }

    /**
     * Autocomplete search for quick lead switcher.
     */
    public function quickSearch(Request $request)
    {
        $user   = auth()->user();
        $search = $request->get('q', '');

        $leads = Lead::byCompany($user->company_id)
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone', 'status']);

        return response()->json($leads->map(fn($l) => [
            'id'         => $l->id,
            'name'       => $l->name,
            'email'      => $l->email ?? '',
            'phone'      => $l->phone ?? '',
            'status'     => $l->status_label,
            'status_key' => $l->status,
            'color'      => $l->status_color,
            'url'        => route('admin.crm.leads.show', $l),
        ]));
    }

    /**
     * Quick status change via AJAX from lead list.
     */
    public function quickStatus(Request $request, Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Lead::getStatuses())),
            'note'   => 'nullable|string|max:1000',
        ]);

        $lead->changeStatus($request->status, $request->note);
        $lead->refresh();

        return response()->json([
            'success'      => true,
            'status'       => $lead->status,
            'status_label' => $lead->status_label,
            'status_color' => $lead->status_color,
            'message'      => 'Estado actualizado correctamente.',
        ]);
    }

    /**
     * Quick agent reassignment via AJAX from lead list.
     */
    public function quickAgent(Request $request, Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        // Solo administradores pueden reasignar el agente de un lead
        abort_unless(auth()->user()->isAdmin(), 403, 'Sin permiso para reasignar agentes.');

        $request->validate([
            'user_id' => 'nullable|exists:users,id',
        ]);

        // El agente destino debe pertenecer a la misma empresa
        if ($request->filled('user_id')) {
            $agent = User::where('id', $request->user_id)
                ->where('company_id', auth()->user()->company_id)
                ->first();

            if (!$agent) {
                return response()->json([
                    'success' => false,
                    'message' => 'El agente seleccionado no pertenece a la empresa.',
                ], 422);
            }
        }

        $lead->update(['user_id' => $request->user_id ?: null]);
        $lead->refresh();

        $lead->logActivity('note', [
            'subject'     => 'Agente reasignado',
            'description' => 'Lead asignado a: ' . ($lead->user->name ?? 'Sin asignar'),
            'activity_at' => now(),
        ]);

        // Avisar al nuevo asesor (si no es quien hizo la reasignación).
        \App\Services\CrmNotifier::leadAssigned($lead, auth()->id());

        return response()->json([
            'success'    => true,
            'agent_name' => $lead->user->name ?? 'Sin asignar',
            'message'    => 'Agente actualizado correctamente.',
        ]);
    }

    /**
     * Quick activity log via AJAX from lead list.
     */
    public function quickActivity(Request $request, Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        $request->validate([
            'type'        => 'required|in:' . implode(',', array_keys(LeadActivity::getTypes())),
            'subject'     => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
        ]);

        $lead->logActivity($request->type, [
            'subject'     => $request->subject,
            'description' => $request->description,
            'activity_at' => now(),
        ]);

        if ($request->filled('next_follow_up')) {
            $lead->update(['next_follow_up' => $request->next_follow_up]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Actividad registrada correctamente.',
        ]);
    }

    /**
     * Quick reminder creation via AJAX from lead list.
     */
    public function quickReminder(Request $request, Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        $request->validate([
            'title'     => 'required|string|max:255',
            'remind_at' => 'required|date',
            'priority'  => 'nullable|in:low,medium,high,urgent',
            'description' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        $lead->reminders()->create([
            'user_id'    => $user->id,
            'company_id' => $user->company_id,
            'title'      => $request->title,
            'description'=> $request->description,
            'remind_at'  => $request->remind_at,
            'priority'   => $request->priority ?? 'medium',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recordatorio creado correctamente.',
        ]);
    }

    /**
     * Quick set next_follow_up date via AJAX from lead list.
     */
    public function quickFollowup(Request $request, Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        $request->validate([
            'next_follow_up' => 'required|date',
            'note'           => 'nullable|string|max:500',
        ]);

        $lead->update(['next_follow_up' => $request->next_follow_up]);

        if ($request->filled('note')) {
            $lead->activities()->create([
                'user_id'     => auth()->id(),
                'type'        => 'note',
                'description' => 'Seguimiento programado: ' . $request->note,
                'activity_at' => now(),
                'subject'     => 'Próximo seguimiento: ' . \Carbon\Carbon::parse($request->next_follow_up)->format('d/m/Y'),
            ]);
        }

        return response()->json([
            'success'        => true,
            'message'        => 'Fecha de seguimiento actualizada.',
            'next_follow_up' => \Carbon\Carbon::parse($request->next_follow_up)->format('d/m/Y'),
        ]);
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

        $properties = Properties::realEstate()->whereHas('category', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->where('status', 'available')->get();

        $vehicles = Properties::vehicles()->whereHas('category', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->whereIn('status', ['available', 'reserved', 'negotiating'])
          ->with(['images' => fn($q) => $q->orderBy('sort_order')->limit(1)])
          ->get();

        return view('admin.crm.leads.create', compact('agents', 'properties', 'vehicles'));
    }

    /**
     * Store a newly created lead.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->canAccessModule('crm', 'view'), 403, 'Sin permiso para gestionar leads.');
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
            'vehicle_id' => ['nullable', Rule::exists('properties', 'id')->where('property_type', 'vehicle')],
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'budget_currency' => 'nullable|in:CRC,USD',
            'notes' => 'nullable|string',
            'requirements' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
            'preferred_asset_type'           => 'nullable|in:property,vehicle',
            'preferred_vehicle_brand'        => 'nullable|string|max:100',
            'preferred_vehicle_max_price'    => 'nullable|numeric|min:0',
            'preferred_vehicle_min_year'     => 'nullable|integer|min:1990|max:2030',
            'preferred_vehicle_max_year'     => 'nullable|integer|min:1990|max:2030',
            'preferred_vehicle_fuel_type'    => 'nullable|string|max:50',
            'preferred_vehicle_transmission' => 'nullable|string|max:50',
        ]);

        $user = auth()->user();

        // Handle preferred_zones (comma-separated → array)
        $preferredZones = array_values(array_filter(array_map('trim', explode(',', $request->input('preferred_zones', '')))));

        $lead = Lead::create([
            'company_id' => $user->company_id,
            'user_id' => ($user->role === 'company_admin' || $user->isSuperAdmin()) ? ($request->user_id ?? $user->id) : $user->id,
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
            'preferred_zones' => $preferredZones,
            'preferred_property_types' => $request->input('preferred_property_types', []),
            'preferred_bedrooms_min' => $request->input('preferred_bedrooms_min') ?: null,
            'preferred_bedrooms_max' => $request->input('preferred_bedrooms_max') ?: null,
            'preferred_asset_type'           => $request->input('preferred_asset_type', 'property'),
            'preferred_vehicle_brand'        => $request->input('preferred_vehicle_brand') ?: null,
            'preferred_vehicle_max_price'    => $request->input('preferred_vehicle_max_price') ?: null,
            'preferred_vehicle_min_year'     => $request->input('preferred_vehicle_min_year') ?: null,
            'preferred_vehicle_max_year'     => $request->input('preferred_vehicle_max_year') ?: null,
            'preferred_vehicle_fuel_type'    => $request->input('preferred_vehicle_fuel_type') ?: null,
            'preferred_vehicle_transmission' => $request->input('preferred_vehicle_transmission') ?: null,
        ]);

        // Registrar actividad de creación
        $lead->logActivity('note', [
            'subject' => 'Lead creado',
            'description' => 'Nuevo lead registrado en el sistema.',
        ]);

        // Avisar al asesor si el lead quedó asignado a otra persona.
        \App\Services\CrmNotifier::leadAssigned($lead, $user->id);

        // Inscribir en secuencias de seguimiento automáticas.
        \App\Services\FollowUpService::enrollNewLead($lead);

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
            'reminders'    => fn($q) => $q->pending()->orderBy('remind_at'),
        ]);

        // Recalculate score on view (lightweight, cached by score_updated_at)
        if (!$lead->score_updated_at || $lead->score_updated_at->diffInHours(now()) > 6) {
            $lead->recalculateScore();
            $lead->refresh();
        }

        // Portal URL if token exists and valid
        $portalUrl = $lead->isPortalValid() ? $lead->portal_url : null;

        // Pending tasks preview (up to 10, with creator to check permissions)
        $pendingTasks = $lead->tasks()
            ->with('creator')
            ->where('status', 'pending')
            ->orderBy('due_at')
            ->limit(10)
            ->get();

        // Property matching
        $matchedProperties = collect();
        $user = auth()->user();
        if ($lead->budget_max || $lead->preferred_zones || $lead->preferred_bedrooms_min) {
            $properties = \App\Properties::realEstate()
                ->whereHas('category', fn($q) => $q->where('company_id', $user->company_id))
                ->with('category.sector')
                ->limit(30)
                ->get();
            $matchedProperties = $properties->map(fn($p) => [
                'property' => $p,
                'score'    => $lead->matchScore($p),
            ])->filter(fn($m) => $m['score'] >= 20)->sortByDesc('score');
        }

        // Pipeline automation alerts
        $pipelineAlerts = \App\PipelineRule::where('company_id', $user->company_id)
            ->active()
            ->get()
            ->filter(fn($rule) => $rule->triggersFor($lead));

        return view('admin.crm.leads.show', compact(
            'lead', 'portalUrl', 'pendingTasks', 'matchedProperties', 'pipelineAlerts'
        ));
    }

    /**
     * Generate a shareable portal token for the lead.
     */
    public function generatePortal(Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);
        $lead->generatePortalToken();

        return back()->with('success', 'Portal del cliente generado. Enlace válido por 30 días.');
    }

    /**
     * Send an email to the lead and log it as activity.
     */
    public function sendEmail(Request $request, Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);

        $request->validate([
            'to'      => 'required|email',
            'subject' => 'required|string|max:200',
            'body'    => 'required|string|max:5000',
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw($request->body, function ($msg) use ($request, $lead) {
                $msg->to($request->to, $lead->name)
                    ->subject($request->subject)
                    ->replyTo(auth()->user()->email, auth()->user()->name);
            });

            $lead->activities()->create([
                'user_id'     => auth()->id(),
                'type'        => 'email',
                'subject'     => $request->subject,
                'description' => $request->body,
                'activity_at' => now(),
            ]);

            $lead->update(['last_contact_at' => now()]);

            return back()->with('success', 'Email enviado a ' . $request->to);
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'No se pudo enviar el email: ' . $e->getMessage()]);
        }
    }

    /**
     * Show all matched properties for a lead.
     */
    public function matching(Lead $lead)
    {
        $this->authorizeCompanyAccess($lead);
        $companyId = auth()->user()->company_id;

        // Load correct asset type based on lead preference
        $assetType = $lead->preferred_asset_type ?? 'property';

        $query = \App\Properties::with('category.sector')
            ->where('company_id', $companyId)
            ->where('status', 'available');

        if ($assetType === 'vehicle') {
            $query->where('property_type', 'vehicle');
        } else {
            $query->where('property_type', '!=', 'vehicle');
        }

        $matched = $query->get()
            ->map(fn($p) => ['property' => $p, 'score' => $lead->matchScore($p)])
            ->sortByDesc('score')
            ->values();

        return view('admin.crm.leads.matching', compact('lead', 'matched'));
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

        $properties = Properties::realEstate()->whereHas('category', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->get();

        $vehicles = Properties::vehicles()->whereHas('category', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->with(['images' => fn($q) => $q->orderBy('sort_order')->limit(1)])
          ->get();

        return view('admin.crm.leads.edit', compact('lead', 'agents', 'properties', 'vehicles'));
    }

    /**
     * Update the specified lead.
     */
    public function update(Request $request, Lead $lead)
    {
        abort_unless(auth()->user()->canAccessModule('crm', 'view'), 403, 'Sin permiso para gestionar leads.');
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
            'vehicle_id' => ['nullable', Rule::exists('properties', 'id')->where('property_type', 'vehicle')],
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'budget_currency' => 'nullable|in:CRC,USD',
            'notes' => 'nullable|string',
            'requirements' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
            'preferred_asset_type'           => 'nullable|in:property,vehicle',
            'preferred_vehicle_brand'        => 'nullable|string|max:100',
            'preferred_vehicle_max_price'    => 'nullable|numeric|min:0',
            'preferred_vehicle_min_year'     => 'nullable|integer|min:1990|max:2030',
            'preferred_vehicle_max_year'     => 'nullable|integer|min:1990|max:2030',
            'preferred_vehicle_fuel_type'    => 'nullable|string|max:50',
            'preferred_vehicle_transmission' => 'nullable|string|max:50',
        ]);

        $user = auth()->user();

        // Handle preferred_zones (comma-separated → array)
        $preferredZones = array_values(array_filter(array_map('trim', explode(',', $request->input('preferred_zones', '')))));

        $data = array_merge($request->only([
            'name', 'email', 'phone', 'whatsapp', 'source', 'priority',
            'interest_type', 'property_id', 'vehicle_id',
            'budget_min', 'budget_max', 'budget_currency', 'notes',
            'requirements', 'next_follow_up',
        ]), [
            'preferred_zones' => $preferredZones,
            'preferred_property_types' => $request->input('preferred_property_types', []),
            'preferred_bedrooms_min' => $request->input('preferred_bedrooms_min') ?: null,
            'preferred_bedrooms_max' => $request->input('preferred_bedrooms_max') ?: null,
            'preferred_asset_type'           => $request->input('preferred_asset_type', 'property'),
            'preferred_vehicle_brand'        => $request->input('preferred_vehicle_brand') ?: null,
            'preferred_vehicle_max_price'    => $request->input('preferred_vehicle_max_price') ?: null,
            'preferred_vehicle_min_year'     => $request->input('preferred_vehicle_min_year') ?: null,
            'preferred_vehicle_max_year'     => $request->input('preferred_vehicle_max_year') ?: null,
            'preferred_vehicle_fuel_type'    => $request->input('preferred_vehicle_fuel_type') ?: null,
            'preferred_vehicle_transmission' => $request->input('preferred_vehicle_transmission') ?: null,
        ]);

        if ($user->role === 'company_admin' || $user->isSuperAdmin()) {
            $data['user_id'] = $request->user_id ?? $lead->user_id;
        }

        $lead->update($data);

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
    public function destroy(Request $request, Lead $lead)
    {
        abort_unless(auth()->user()->canAccessModule('crm', 'view'), 403, 'Sin permiso para gestionar leads.');
        $this->authorizeCompanyAccess($lead);

        // Audit log before deletion
        \App\Models\LeadDeletionAudit::create([
            'company_id'  => $lead->company_id,
            'deleted_by'  => auth()->id(),
            'lead_name'   => $lead->name,
            'lead_email'  => $lead->email,
            'lead_phone'  => $lead->phone,
            'lead_status' => $lead->status,
            'lead_score'  => $lead->score ?? 0,
            'reason'      => $request->input('reason'),
            'deleted_at'  => now(),
        ]);

        $lead->delete();

        return redirect()->route('admin.crm.leads.index')
            ->with('success', 'Lead eliminado correctamente.');
    }

    /**
     * Audit log of deleted leads (company_admin and super_admin only).
     */
    public function auditIndex()
    {
        $user = auth()->user();
        if ($user->role !== 'company_admin' && !$user->isSuperAdmin()) {
            abort(403, 'Solo administradores pueden ver el registro de auditoría.');
        }
        $audits = \App\Models\LeadDeletionAudit::where('company_id', $user->company_id)
            ->with('deletedBy')
            ->orderByDesc('deleted_at')
            ->paginate(30);
        return view('admin.crm.leads.audit', compact('audits'));
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
     * Show CSV import form.
     */
    public function importForm()
    {
        $columnGuide = [
            'nombre / name',
            'correo / email',
            'telefono / phone',
            'whatsapp',
            'fuente / source',
            'estado / status',
            'prioridad / priority',
            'notas / notes',
            'budget_min',
            'budget_max',
        ];

        return view('admin.crm.leads.import', compact('columnGuide'));
    }

    /**
     * Preview CSV import.
     */
    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $columnGuide = [
            'nombre / name',
            'correo / email',
            'telefono / phone',
            'whatsapp',
            'fuente / source',
            'estado / status',
            'prioridad / priority',
            'notas / notes',
            'budget_min',
            'budget_max',
        ];

        // Header → internal field mapping
        $headerMap = [
            'name'      => 'name',   'nombre'    => 'name',
            'email'     => 'email',  'correo'    => 'email',
            'phone'     => 'phone',  'telefono'  => 'phone',  'teléfono' => 'phone',
            'whatsapp'  => 'whatsapp',
            'source'    => 'source', 'fuente'    => 'source',
            'status'    => 'status', 'estado'    => 'status',
            'priority'  => 'priority', 'prioridad' => 'priority',
            'notes'     => 'notes',  'notas'     => 'notes',
            'budget_min' => 'budget_min',
            'budget_max' => 'budget_max',
        ];

        // Store the file
        $request->file('file')->storeAs('imports', 'leads_import_' . auth()->id() . '.csv', 'local');

        // Re-open for parsing
        $filePath = storage_path('app/imports/leads_import_' . auth()->id() . '.csv');
        $handle = fopen($filePath, 'r');

        // Parse headers
        $rawHeaders = fgetcsv($handle);
        $headers = array_map(fn($h) => strtolower(trim($h)), $rawHeaders);

        $mappedColumns = [];
        foreach ($headers as $h) {
            $mappedColumns[$h] = $headerMap[$h] ?? null;
        }

        // Read preview rows and count total
        $preview    = [];
        $totalRows  = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $totalRows++;
            if (count($preview) < 10) {
                $assoc = [];
                foreach ($headers as $i => $h) {
                    $field = $mappedColumns[$h];
                    if ($field) {
                        $assoc[$field] = $row[$i] ?? '';
                    }
                }
                $preview[] = $assoc;
            }
        }
        fclose($handle);

        return view('admin.crm.leads.import', compact('columnGuide', 'preview', 'totalRows', 'mappedColumns'));
    }

    /**
     * Execute CSV import.
     */
    public function importExecute(Request $request)
    {
        $request->validate([
            'confirm' => 'required|in:1',
        ]);

        $filePath = storage_path('app/imports/leads_import_' . auth()->id() . '.csv');
        $handle   = fopen($filePath, 'r');

        // Header → field map
        $headerMap = [
            'name'      => 'name',   'nombre'    => 'name',
            'email'     => 'email',  'correo'    => 'email',
            'phone'     => 'phone',  'telefono'  => 'phone',  'teléfono' => 'phone',
            'whatsapp'  => 'whatsapp',
            'source'    => 'source', 'fuente'    => 'source',
            'status'    => 'status', 'estado'    => 'status',
            'priority'  => 'priority', 'prioridad' => 'priority',
            'notes'     => 'notes',  'notas'     => 'notes',
            'budget_min' => 'budget_min',
            'budget_max' => 'budget_max',
        ];

        $rawHeaders = fgetcsv($handle);
        $headers    = array_map(fn($h) => strtolower(trim($h)), $rawHeaders);

        $mappedColumns = [];
        foreach ($headers as $h) {
            $mappedColumns[$h] = $headerMap[$h] ?? null;
        }

        $companyId = auth()->user()->company_id;
        $userId    = auth()->id();
        $now       = now();

        $batch       = [];
        $importCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = [];
            foreach ($headers as $i => $h) {
                $field = $mappedColumns[$h];
                if ($field) {
                    $data[$field] = trim($row[$i] ?? '');
                }
            }

            // Skip rows with no name
            if (empty($data['name'])) {
                continue;
            }

            $batch[] = [
                'company_id'  => $companyId,
                'user_id'     => $userId,
                'name'        => $data['name'],
                'email'       => $data['email']      ?? null,
                'phone'       => $data['phone']      ?? null,
                'whatsapp'    => $data['whatsapp']   ?? null,
                'source'      => !empty($data['source'])   ? $data['source']   : 'other',
                'status'      => !empty($data['status'])   ? $data['status']   : 'new',
                'priority'    => !empty($data['priority']) ? $data['priority'] : 'medium',
                'notes'       => $data['notes']      ?? null,
                'budget_min'  => !empty($data['budget_min']) ? $data['budget_min'] : null,
                'budget_max'  => !empty($data['budget_max']) ? $data['budget_max'] : null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            $importCount++;

            if (count($batch) >= 50) {
                Lead::insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            Lead::insert($batch);
        }

        fclose($handle);
        @unlink($filePath);

        return redirect()->route('admin.crm.leads.index')
            ->with('success', "{$importCount} leads importados correctamente.");
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
