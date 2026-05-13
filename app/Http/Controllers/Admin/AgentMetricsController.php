<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Lead;
use App\Models\AgentGoal;
use App\Models\AgentReward;
use App\Models\AgentRewardGrant;
use App\Models\EventLead;
use App\Models\LeadFollowup;
use App\Models\VehicleQuote;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentMetricsController extends Controller
{
    /**
     * Dashboard principal de métricas de agentes.
     */
    public function index(Request $request)
    {
        $user      = Auth::user();
        $companyId = $user->isSuperAdmin()
            ? $request->get('company_id', $user->company_id)
            : $user->company_id;

        $month = $request->get('month', now()->format('Y-m'));
        $from  = Carbon::parse($month . '-01')->startOfMonth();
        $to    = Carbon::parse($month . '-01')->endOfMonth();

        // Agentes de la empresa
        $agents = User::where('company_id', $companyId)
            ->where('role', User::ROLE_AGENT)
            ->with('modulePermissions')
            ->get();

        // Construir tabla de métricas por agente
        $agentMetrics = $agents->map(function (User $agent) use ($from, $to, $month, $companyId) {
            // ── EventLeads (eventos/kiosko) ──────────────────────────────────
            $eventLeads  = EventLead::where('captured_by_user_id', $agent->id)
                ->whereBetween('created_at', [$from, $to])
                ->get();

            $quotes = VehicleQuote::where('captured_by_user_id', $agent->id)
                ->whereBetween('created_at', [$from, $to])
                ->count();

            $eventConversions = $eventLeads->where('sale_status', 'converted')->count();
            $eventTotal       = $eventLeads->count();

            // Días promedio de conversión (EventLeads)
            $convertedEventLeads = $eventLeads->where('sale_status', 'converted')
                ->filter(fn($l) => $l->converted_at);
            $avgDaysEvent = $convertedEventLeads->count() > 0
                ? round($convertedEventLeads->avg(fn($l) => $l->created_at->diffInDays($l->converted_at)), 1)
                : null;

            // Leads sin seguimiento en los últimos 5 días (EventLeads)
            $idleLeads = EventLead::where('captured_by_user_id', $agent->id)
                ->whereIn('sale_status', ['open', 'in_progress'])
                ->where('created_at', '<=', now()->subDays(5))
                ->whereDoesntHave('followups', fn($q) => $q->where('created_at', '>=', now()->subDays(5)))
                ->count();

            // ── CRM Leads (módulo CRM principal) ────────────────────────────
            $crmLeads = Lead::where('user_id', $agent->id)
                ->where('company_id', $companyId)
                ->whereBetween('created_at', [$from, $to])
                ->get(['id', 'status', 'created_at', 'converted_at']);

            $crmTotal       = $crmLeads->count();
            $crmWon         = $crmLeads->where('status', 'won')->count();
            $crmLost        = $crmLeads->where('status', 'lost')->count();
            $crmActive      = $crmLeads->whereIn('status', ['contacted', 'qualified', 'proposal', 'negotiation'])->count();

            // Días promedio de conversión (CRM)
            $convertedCrm = $crmLeads->where('status', 'won')->filter(fn($l) => $l->converted_at);
            $avgDaysCrm   = $convertedCrm->count() > 0
                ? round($convertedCrm->avg(fn($l) => $l->created_at->diffInDays($l->converted_at)), 1)
                : null;

            // Totales combinados para metas y recompensas
            $totalLeads   = $eventTotal + $crmTotal;
            $totalConv    = $eventConversions + $crmWon;
            $convRate     = $totalLeads > 0 ? round(($totalConv / $totalLeads) * 100, 1) : 0;
            $avgDays      = collect(array_filter([$avgDaysEvent, $avgDaysCrm]))->avg() ?: null;

            // Meta del mes
            $goal = AgentGoal::where('user_id', $agent->id)
                ->where('month', $from->format('Y-m-d'))
                ->first();

            return [
                'agent'             => $agent,
                // EventLead metrics (legado)
                'leads'             => $eventTotal,
                'quotes'            => $quotes,
                'conversions'       => $eventConversions,
                // CRM Lead metrics (nuevo)
                'crm_leads'         => $crmTotal,
                'crm_won'           => $crmWon,
                'crm_lost'          => $crmLost,
                'crm_active'        => $crmActive,
                // Combinados
                'total_leads'       => $totalLeads,
                'total_conversions' => $totalConv,
                'conv_rate'         => $convRate,
                'avg_days'          => $avgDays,
                'idle_leads'        => $idleLeads,
                'goal'              => $goal,
                'leads_pct'         => $goal && $goal->leads_goal > 0       ? min(100, round(($totalLeads / $goal->leads_goal) * 100)) : null,
                'quotes_pct'        => $goal && $goal->quotes_goal > 0      ? min(100, round(($quotes / $goal->quotes_goal) * 100)) : null,
                'conv_pct'          => $goal && $goal->conversions_goal > 0 ? min(100, round(($totalConv / $goal->conversions_goal) * 100)) : null,
                // Distribución
                'categories'        => $eventLeads->groupBy('lead_category')->map->count(),
                'statuses'          => $eventLeads->groupBy('sale_status')->map->count(),
                'crm_statuses'      => $crmLeads->groupBy('status')->map->count(),
                // Recompensa (basada en conversiones combinadas)
                'eligible_reward'   => AgentReward::where('is_active', true)
                    ->get()
                    ->first(fn($r) => $r->appliesTo($totalConv)),
            ];
        })->sortByDesc('total_conversions')->values();

        // Funnel global de la empresa en el mes (EventLeads)
        $funnel = [
            'captured'    => EventLead::where('company_id', $companyId)->whereBetween('created_at', [$from, $to])->count(),
            'contacted'   => EventLead::where('company_id', $companyId)->whereBetween('created_at', [$from, $to])->where('contacted', true)->count(),
            'in_progress' => EventLead::where('company_id', $companyId)->whereBetween('created_at', [$from, $to])->where('sale_status', 'in_progress')->count(),
            'converted'   => EventLead::where('company_id', $companyId)->whereBetween('created_at', [$from, $to])->where('sale_status', 'converted')->count(),
            'lost'        => EventLead::where('company_id', $companyId)->whereBetween('created_at', [$from, $to])->where('sale_status', 'lost')->count(),
        ];

        // Resumen CRM (módulo Lead) para el período seleccionado
        $crmFunnel = [
            'total'       => Lead::where('company_id', $companyId)->whereBetween('created_at', [$from, $to])->count(),
            'new'         => Lead::where('company_id', $companyId)->whereBetween('created_at', [$from, $to])->where('status', 'new')->count(),
            'active'      => Lead::where('company_id', $companyId)->whereBetween('created_at', [$from, $to])->whereIn('status', ['contacted','qualified','proposal','negotiation'])->count(),
            'won'         => Lead::where('company_id', $companyId)->whereBetween('created_at', [$from, $to])->where('status', 'won')->count(),
            'lost'        => Lead::where('company_id', $companyId)->whereBetween('created_at', [$from, $to])->where('status', 'lost')->count(),
        ];

        // Historial de recompensas otorgadas
        $recentGrants = AgentRewardGrant::with(['agent', 'reward'])
            ->whereHas('agent', fn($q) => $q->where('company_id', $companyId))
            ->latest('granted_at')
            ->limit(10)
            ->get();

        return view('admin.metrics.index', compact(
            'agentMetrics', 'funnel', 'crmFunnel', 'recentGrants', 'month', 'from', 'to', 'companyId'
        ));
    }

    /**
     * Vista detallada de un agente.
     */
    public function agentDetail(Request $request, User $user)
    {
        $this->authorizeAccess($user);

        $month = $request->get('month', now()->format('Y-m'));
        $from  = Carbon::parse($month . '-01')->startOfMonth();
        $to    = Carbon::parse($month . '-01')->endOfMonth();

        $leads = EventLead::where('captured_by_user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->with(['property', 'followups.agent'])
            ->latest()
            ->get();

        $quotes = VehicleQuote::where('captured_by_user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->with('property')
            ->latest()
            ->get();

        $goal = AgentGoal::where('user_id', $user->id)
            ->where('month', $from->format('Y-m-d'))
            ->first();

        // Historial de todos los meses
        $history = AgentGoal::where('user_id', $user->id)
            ->orderByDesc('month')
            ->limit(12)
            ->get()
            ->map(fn($g) => array_merge(['goal' => $g], $g->metrics()));

        // Recompensas recibidas
        $grants = AgentRewardGrant::where('user_id', $user->id)
            ->with('reward')
            ->orderByDesc('month')
            ->get();

        // Leads por categoría
        $byCategory = $leads->groupBy('lead_category')->map->count();
        // Leads por interés
        $byInterest = $leads->groupBy('interest_level')->map->count();
        // Timeline semanal
        $weeklyLeads = $leads->groupBy(fn($l) => $l->created_at->startOfWeek()->format('d/m'))->map->count();

        return view('admin.metrics.agent', compact(
            'user', 'leads', 'quotes', 'goal', 'history',
            'grants', 'byCategory', 'byInterest', 'weeklyLeads',
            'month', 'from', 'to'
        ));
    }

    /**
     * Otorgar recompensa manualmente a un agente.
     */
    public function grantReward(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'reward_id' => 'required|exists:agent_rewards,id',
            'month'     => 'required|date_format:Y-m',
            'notes'     => 'nullable|string',
        ]);

        $month = Carbon::parse($request->month . '-01');
        $from  = $month->copy()->startOfMonth();
        $to    = $month->copy()->endOfMonth();

        $eventConversions = EventLead::where('captured_by_user_id', $request->user_id)
            ->where('sale_status', 'converted')
            ->whereBetween('converted_at', [$from, $to])
            ->count();

        $crmConversions = Lead::where('user_id', $request->user_id)
            ->where('status', 'won')
            ->whereBetween('converted_at', [$from, $to])
            ->count();

        $conversions = $eventConversions + $crmConversions;

        $leads = EventLead::where('captured_by_user_id', $request->user_id)
            ->whereBetween('created_at', [$from, $to])
            ->count()
            + Lead::where('user_id', $request->user_id)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        AgentRewardGrant::updateOrCreate(
            ['user_id' => $request->user_id, 'reward_id' => $request->reward_id, 'month' => $month->format('Y-m-d')],
            [
                'conversions_count' => $conversions,
                'leads_count'       => $leads,
                'notes'             => $request->notes,
                'granted_by'        => Auth::id(),
                'granted_at'        => now(),
            ]
        );

        return back()->with('success', 'Recompensa otorgada correctamente.');
    }

    private function authorizeAccess(User $targetUser): void
    {
        $me = Auth::user();
        if ($me->isSuperAdmin()) return;
        if ($me->isCompanyAdmin() && $targetUser->company_id === $me->company_id) return;
        abort(403);
    }
}
