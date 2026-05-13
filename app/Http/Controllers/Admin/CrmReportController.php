<?php

namespace App\Http\Controllers\Admin;

use App\Appointment;
use App\Http\Controllers\Controller;
use App\Lead;
use App\Reminder;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CrmReportController extends Controller
{
    /**
     * Selector de reportes (UI web)
     */
    public function index(Request $request)
    {
        $user   = auth()->user();
        $agents = User::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.crm.reports.index', compact('agents'));
    }

    /**
     * 3.1 Reporte detallado por lead
     */
    public function leadDetail(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);

        $lead->load([
            'user',
            'property',
            'vehicle',
            'company',
            'activities'    => fn($q) => $q->orderBy('activity_at', 'desc')->with('user'),
            'appointments'  => fn($q) => $q->orderBy('starts_at', 'desc')->with('user'),
            'reminders'     => fn($q) => $q->orderBy('remind_at'),
        ]);

        $company    = $this->company();
        $logoPath   = $this->logoPath($company);
        $filters    = ['Lead' => $lead->name];
        $printDate  = now()->format('d/m/Y H:i');

        return $this->render(
            'admin.crm.reports.pdf.lead-detail',
            compact('lead', 'company', 'logoPath', 'filters', 'printDate'),
            'lead-' . $lead->id . '-' . str($lead->name)->slug(),
            $request->boolean('download')
        );
    }

    /**
     * 3.2 Reporte estilo pipeline
     */
    public function pipeline(Request $request)
    {
        $user     = auth()->user();
        $statuses = Lead::getStatuses();
        $query    = Lead::with(['user'])->byCompany($user->company_id);

        $this->applyCommonFilters($query, $request);

        $leads    = $query->orderBy('status')->orderBy('updated_at', 'desc')->get();
        $byStatus = $leads->groupBy('status');

        $company   = $this->company();
        $logoPath  = $this->logoPath($company);
        $filters   = $this->buildFilterLabels($request);
        $printDate = now()->format('d/m/Y H:i');

        return $this->render(
            'admin.crm.reports.pdf.pipeline',
            compact('leads', 'byStatus', 'statuses', 'company', 'logoPath', 'filters', 'printDate'),
            'pipeline-crm-' . now()->format('Ymd'),
            $request->boolean('download')
        );
    }

    /**
     * 3.3 Reporte general de leads
     */
    public function leadsGeneral(Request $request)
    {
        $user  = auth()->user();
        $query = Lead::with([
            'user',
            'activities' => fn($q) => $q->latest('activity_at')->limit(1)->with('user'),
        ])->byCompany($user->company_id);

        $this->applyCommonFilters($query, $request);

        if ($request->boolean('no_followup')) {
            $query->whereNull('next_follow_up');
        }
        if ($request->boolean('overdue')) {
            $query->needsFollowUp();
        }

        $leads     = $query->orderBy('created_at', 'desc')->get();
        $company   = $this->company();
        $logoPath  = $this->logoPath($company);
        $filters   = $this->buildFilterLabels($request);
        $printDate = now()->format('d/m/Y H:i');

        if ($request->boolean('no_followup')) $filters['Sin seguimiento'] = 'Sí';
        if ($request->boolean('overdue'))     $filters['Solo vencidos']   = 'Sí';

        return $this->render(
            'admin.crm.reports.pdf.leads-general',
            compact('leads', 'company', 'logoPath', 'filters', 'printDate'),
            'leads-general-' . now()->format('Ymd'),
            $request->boolean('download')
        );
    }

    /**
     * 3.5 Reporte de citas
     */
    public function appointments(Request $request)
    {
        $user  = auth()->user();
        $query = Appointment::with(['user', 'lead'])
            ->where('company_id', $user->company_id);

        if ($request->filled('user_id'))   $query->where('user_id', $request->user_id);
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('type'))      $query->where('type', $request->type);
        if ($request->filled('date_from')) $query->whereDate('starts_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('starts_at', '<=', $request->date_to);

        $appointments = $query->orderBy('starts_at', 'desc')->get();
        $company      = $this->company();
        $logoPath     = $this->logoPath($company);
        $filters      = $this->buildFilterLabels($request);
        $printDate    = now()->format('d/m/Y H:i');

        if ($request->filled('type'))   $filters['Tipo']        = Appointment::getTypes()[$request->type]     ?? $request->type;
        if ($request->filled('status')) $filters['Estado cita'] = Appointment::getStatuses()[$request->status] ?? $request->status;

        return $this->render(
            'admin.crm.reports.pdf.appointments',
            compact('appointments', 'company', 'logoPath', 'filters', 'printDate'),
            'citas-crm-' . now()->format('Ymd'),
            $request->boolean('download')
        );
    }

    /**
     * 3.6 Reporte de seguimientos
     */
    public function followUps(Request $request)
    {
        $user  = auth()->user();
        $query = Lead::with([
            'user',
            'activities' => fn($q) => $q->latest('activity_at')->limit(3)->with('user'),
        ])
            ->byCompany($user->company_id)
            ->whereNotNull('next_follow_up');

        $this->applyCommonFilters($query, $request);

        $type = $request->get('followup_type');
        if ($type === 'overdue')  $query->where('next_follow_up', '<', now()->startOfDay());
        if ($type === 'today')    $query->whereDate('next_follow_up', today());
        if ($type === 'upcoming') $query->where('next_follow_up', '>', now()->startOfDay());

        $leads     = $query->orderBy('next_follow_up', 'asc')->get();
        $company   = $this->company();
        $logoPath  = $this->logoPath($company);
        $filters   = $this->buildFilterLabels($request);
        $printDate = now()->format('d/m/Y H:i');

        $typeLabels = ['overdue' => 'Solo vencidos', 'today' => 'Solo hoy', 'upcoming' => 'Próximos'];
        if ($type && isset($typeLabels[$type])) $filters['Tipo seguimiento'] = $typeLabels[$type];

        return $this->render(
            'admin.crm.reports.pdf.follow-ups',
            compact('leads', 'company', 'logoPath', 'filters', 'printDate'),
            'seguimientos-crm-' . now()->format('Ymd'),
            $request->boolean('download')
        );
    }

    /**
     * Extra: Leads sin seguimiento
     */
    public function noFollowup(Request $request)
    {
        $user  = auth()->user();
        $query = Lead::with(['user'])
            ->byCompany($user->company_id)
            ->whereNull('next_follow_up')
            ->active();

        $this->applyCommonFilters($query, $request);

        $leads     = $query->orderBy('created_at', 'desc')->get();
        $company   = $this->company();
        $logoPath  = $this->logoPath($company);
        $filters   = array_merge($this->buildFilterLabels($request), ['Condición' => 'Sin fecha de seguimiento']);
        $printDate = now()->format('d/m/Y H:i');

        return $this->render(
            'admin.crm.reports.pdf.no-followup',
            compact('leads', 'company', 'logoPath', 'filters', 'printDate'),
            'leads-sin-seguimiento-' . now()->format('Ymd'),
            $request->boolean('download')
        );
    }

    /**
     * Extra: Productividad por agente
     */
    public function byAgent(Request $request)
    {
        $user    = auth()->user();
        $agents  = User::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $leadsQuery = Lead::where('company_id', $user->company_id);
        if ($request->filled('status'))    $leadsQuery->where('status', $request->status);
        if ($request->filled('date_from')) $leadsQuery->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $leadsQuery->whereDate('created_at', '<=', $request->date_to);

        $allLeads    = $leadsQuery->get();
        $leadsByUser = $allLeads->groupBy('user_id');

        $agentData = $agents->map(function (User $agent) use ($leadsByUser) {
            $leads = $leadsByUser->get($agent->id, collect());
            return [
                'agent'       => $agent,
                'total'       => $leads->count(),
                'won'         => $leads->where('status', 'won')->count(),
                'lost'        => $leads->where('status', 'lost')->count(),
                'active'      => $leads->whereIn('status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation'])->count(),
                'conv_rate'   => $leads->count() > 0 ? round(($leads->where('status', 'won')->count() / $leads->count()) * 100, 1) : 0,
                'leads'       => $leads->sortByDesc('created_at'),
            ];
        })->filter(fn($a) => $a['total'] > 0)->values();

        $company   = $this->company();
        $logoPath  = $this->logoPath($company);
        $filters   = $this->buildFilterLabels($request);
        $printDate = now()->format('d/m/Y H:i');

        return $this->render(
            'admin.crm.reports.pdf.by-agent',
            compact('agentData', 'company', 'logoPath', 'filters', 'printDate'),
            'leads-por-agente-' . now()->format('Ymd'),
            $request->boolean('download')
        );
    }

    /**
     * Extra: Recordatorios vencidos
     */
    public function overdueReminders(Request $request)
    {
        $user      = auth()->user();
        $reminders = Reminder::with(['remindable', 'user'])
            ->where('company_id', $user->company_id)
            ->where('is_completed', false)
            ->where('is_dismissed', false)
            ->where('remind_at', '<', now())
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->orderBy('remind_at', 'asc')
            ->get();

        $company   = $this->company();
        $logoPath  = $this->logoPath($company);
        $filters   = $this->buildFilterLabels($request);
        $filters['Condición'] = 'Recordatorios vencidos';
        $printDate = now()->format('d/m/Y H:i');

        return $this->render(
            'admin.crm.reports.pdf.overdue-reminders',
            compact('reminders', 'company', 'logoPath', 'filters', 'printDate'),
            'recordatorios-vencidos-' . now()->format('Ymd'),
            $request->boolean('download')
        );
    }

    // ── Helpers privados ────────────────────────────────────────────────────

    private function company()
    {
        return auth()->user()->load('company')->company;
    }

    private function logoPath($company): ?string
    {
        if (!$company || !$company->logo) return null;
        $path = public_path('storage/' . $company->logo);
        return file_exists($path) ? $path : null;
    }

    private function applyCommonFilters($query, Request $request): void
    {
        if ($request->filled('status'))    $query->byStatus($request->status);
        if ($request->filled('source'))    $query->where('source', $request->source);
        if ($request->filled('priority'))  $query->where('priority', $request->priority);
        if ($request->filled('user_id'))   $query->byUser($request->user_id);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('created_at', '<=', $request->date_to);
    }

    private function buildFilterLabels(Request $request): array
    {
        $labels = [];
        if ($request->filled('status'))    $labels['Estado']     = Lead::getStatuses()[$request->status]     ?? $request->status;
        if ($request->filled('source'))    $labels['Fuente']     = Lead::getSources()[$request->source]      ?? $request->source;
        if ($request->filled('priority'))  $labels['Prioridad']  = Lead::getPriorities()[$request->priority] ?? $request->priority;
        if ($request->filled('user_id'))   $labels['Agente']     = User::find($request->user_id)?->name ?? $request->user_id;
        if ($request->filled('date_from')) $labels['Desde']      = Carbon::parse($request->date_from)->format('d/m/Y');
        if ($request->filled('date_to'))   $labels['Hasta']      = Carbon::parse($request->date_to)->format('d/m/Y');
        return $labels;
    }

    private function render(string $view, array $data, string $filename, bool $download): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = Pdf::loadView($view, $data)
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'dpi'           => 110,
                'defaultFont'   => 'helvetica',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
            ]);

        return $download
            ? $pdf->download($filename . '.pdf')
            : $pdf->stream($filename . '.pdf');
    }

    private function authorizeLead(Lead $lead): void
    {
        if ($lead->company_id !== auth()->user()->company_id) abort(403);
    }
}
