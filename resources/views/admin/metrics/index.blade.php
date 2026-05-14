@extends('admin.main')
@section('title', 'Métricas de Agentes')
@section('content')

@include('admin.metrics.layouts._metrics-styles')

<style>
/* ── Filtros ── */
.crm-filters-bar {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(0,0,0,.07);
    padding: 14px 20px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.crm-filters-bar label {
    font-size: 12px; font-weight: 700; color: #888;
    margin: 0; text-transform: uppercase; letter-spacing: .4px;
}
.crm-filters-bar input[type="month"],
.crm-filters-bar input[type="number"] {
    border: 1.5px solid #e5e7eb; border-radius: 9px;
    padding: 6px 12px; font-size: 13px; color: #1a1a2e;
    outline: none; transition: border-color .15s;
    background: #fafafa;
}
.crm-filters-bar input:focus { border-color: #c2ac1f; background: #fff; }

/* ── Section label ── */
.section-label {
    font-size: 11px; font-weight: 700; color: #888;
    text-transform: uppercase; letter-spacing: .6px;
    margin-bottom: 10px;
    display: flex; align-items: center; gap: 7px;
}
.section-label i { color: #c2ac1f; }

/* ── Progress bars ── */
.mini-progress {
    height: 5px; border-radius: 3px;
    background: #f0f0f0; overflow: hidden; margin-top: 5px;
}
.mini-progress-bar { height: 100%; border-radius: 3px; transition: width .4s; }

/* ── Leaderboard rank cell ── */
.rank-cell { font-size: 17px; line-height: 1; }

/* ── Idle / grants card header variants ── */
.dc-header-danger  { background: #fff5f5; border-bottom-color: #fecaca; }
.dc-header-dark    { background: linear-gradient(135deg, #1a1a2e, #2d2d4e); border-bottom: none; }
.dc-header-dark h5 { color: #fff; }
.dc-header-dark h5 i { color: #c2ac1f; }
.dc-header-dark .dc-month { font-size: 12px; color: #c2ac1f; font-weight: 500; }

/* ── Two-column funnel/idle layout ── */
.funnel-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}
@media (max-width: 768px) { .funnel-grid { grid-template-columns: 1fr; } }

/* ── Idle card accent border ── */
.card-idle  { border-left: 4px solid #ef4444; }
.card-allok { border-left: 4px solid #22c55e; }

/* ── Alert ── */
.metrics-alert {
    background: #fff;
    border: 1px solid #d1fae5;
    border-left: 4px solid #22c55e;
    border-radius: 12px;
    padding: 12px 18px;
    margin-bottom: 16px;
    display: flex; align-items: center; gap: 10px;
    font-size: 13.5px; color: #065f46;
}
</style>

@if(Session::has('success'))
<div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:12px;padding:12px 18px;margin-bottom:16px;display:flex;align-items:center;gap:10px;color:#065f46;font-size:13.5px;">
    <i class="fa fa-check-circle" style="color:#22c55e;"></i>
    <strong>{{ Session::get('success') }}</strong>
</div>
@endif

<div class="crm-page">

    {{-- ══════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════ --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-trophy"></i> Métricas de Agentes</h2>
            <div class="sub">
                <i class="fa fa-calendar"></i>
                {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
            </div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.metrics.goals.index', ['month' => $month]) }}"
               class="action-btn gold">
                <i class="fa fa-bullseye"></i> Metas
            </a>
            <a href="{{ route('admin.rewards.index') }}"
               class="action-btn warning">
                <i class="fa fa-trophy"></i> Recompensas
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         FILTER BAR
    ══════════════════════════════════════════ --}}
    <form method="GET">
        <div class="crm-filters-bar">
            <label><i class="fa fa-filter"></i> Período</label>
            <input type="month" name="month" value="{{ $month }}">
            @if(Auth::user()->isSuperAdmin())
                <input type="number" name="company_id"
                       value="{{ $companyId ?? '' }}"
                       placeholder="Company ID"
                       style="width:130px;">
            @endif
            <button type="submit" class="action-btn primary">
                <i class="fa fa-search"></i> Filtrar
            </button>
        </div>
    </form>

    {{-- ══════════════════════════════════════════
         STAT CARDS — EVENTOS / KIOSKO
    ══════════════════════════════════════════ --}}
    <div class="section-label">
        <i class="fa fa-calendar-check-o"></i> Eventos / Kiosko
    </div>
    <div class="stats-grid" style="margin-bottom:10px;">

        <div class="stat-card">
            <div class="sc-icon blue"><i class="fa fa-users"></i></div>
            <div class="sc-value">{{ $funnel['captured'] }}</div>
            <div class="sc-label">Capturados</div>
        </div>

        <div class="stat-card">
            <div class="sc-icon yellow"><i class="fa fa-spinner"></i></div>
            <div class="sc-value">{{ $funnel['in_progress'] }}</div>
            <div class="sc-label">En Proceso</div>
        </div>

        <div class="stat-card">
            <div class="sc-icon green"><i class="fa fa-check-circle"></i></div>
            <div class="sc-value">{{ $funnel['converted'] }}</div>
            <div class="sc-label">Convertidos</div>
        </div>

        <div class="stat-card">
            <div class="sc-icon red"><i class="fa fa-times-circle"></i></div>
            <div class="sc-value">{{ $funnel['lost'] }}</div>
            <div class="sc-label">Perdidos</div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         STAT CARDS — CRM
    ══════════════════════════════════════════ --}}
    <div class="section-label" style="margin-top:22px;">
        <i class="fa fa-users"></i> CRM
    </div>
    <div class="stats-grid">

        <div class="stat-card">
            <div class="sc-icon indigo"><i class="fa fa-user-plus"></i></div>
            <div class="sc-value">{{ $crmFunnel['total'] ?? 0 }}</div>
            <div class="sc-label">Total CRM</div>
        </div>

        <div class="stat-card">
            <div class="sc-icon orange"><i class="fa fa-fire"></i></div>
            <div class="sc-value">{{ $crmFunnel['active'] ?? 0 }}</div>
            <div class="sc-label">Activos</div>
        </div>

        <div class="stat-card">
            <div class="sc-icon green"><i class="fa fa-handshake-o"></i></div>
            <div class="sc-value">{{ $crmFunnel['won'] ?? 0 }}</div>
            <div class="sc-label">Ganados</div>
        </div>

        <div class="stat-card">
            <div class="sc-icon red"><i class="fa fa-ban"></i></div>
            <div class="sc-value">{{ $crmFunnel['lost'] ?? 0 }}</div>
            <div class="sc-label">Perdidos</div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         FUNNEL CHART  +  IDLE LEADS
    ══════════════════════════════════════════ --}}
    @php $idleTotal = $agentMetrics->sum('idle_leads'); @endphp

    <div class="funnel-grid">

        {{-- Funnel Chart ── --}}
        <div class="dashboard-card">
            <div class="dc-header">
                <h5><i class="fa fa-filter"></i> Embudo de Conversión</h5>
                <span style="font-size:12px;color:#aaa;">
                    {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                </span>
            </div>
            <div class="dc-body">
                @php
                    $funnelSteps = [
                        ['label' => 'Capturados',  'count' => $funnel['captured'],    'hex' => '#3b82f6'],
                        ['label' => 'Contactados', 'count' => $funnel['contacted'],   'hex' => '#6366f1'],
                        ['label' => 'En Proceso',  'count' => $funnel['in_progress'], 'hex' => '#eab308'],
                        ['label' => 'Convertidos', 'count' => $funnel['converted'],   'hex' => '#22c55e'],
                        ['label' => 'Perdidos',    'count' => $funnel['lost'],        'hex' => '#ef4444'],
                    ];
                    $funnelBase = max($funnel['captured'], 1);
                @endphp
                @foreach($funnelSteps as $step)
                    @php $pct = round(($step['count'] / $funnelBase) * 100); @endphp
                    <div style="margin-bottom:12px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                            <span style="font-size:12px;font-weight:600;color:#555;">{{ $step['label'] }}</span>
                            <span style="font-size:12px;color:#888;">{{ $step['count'] }} <span style="color:#ccc;">({{ $pct }}%)</span></span>
                        </div>
                        <div class="mini-progress">
                            <div class="mini-progress-bar"
                                 style="width:{{ $pct }}%; background:{{ $step['hex'] }};"></div>
                        </div>
                    </div>
                @endforeach
                <canvas id="funnelChart" height="160" style="margin-top:18px;"></canvas>
            </div>
        </div>

        {{-- Idle Leads ── --}}
        @if($idleTotal > 0)
        <div class="dashboard-card card-idle">
            <div class="dc-header dc-header-danger">
                <h5 style="color:#991b1b;">
                    <i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i>
                    Leads Sin Seguimiento
                    <span style="font-size:11px;color:#ef4444;font-weight:400;">(+5 días)</span>
                </h5>
                <span style="background:#fee2e2;color:#991b1b;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                    {{ $idleTotal }}
                </span>
            </div>
            <div style="overflow-x:auto;">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th>Agente</th>
                            <th style="text-align:center;">Idle</th>
                            <th style="width:56px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($agentMetrics->where('idle_leads', '>', 0) as $m)
                        <tr class="row-danger">
                            <td>
                                <strong style="color:#1a1a2e;">{{ $m['agent']->name }}</strong>
                            </td>
                            <td style="text-align:center;">
                                <span class="crm-badge lost">{{ $m['idle_leads'] }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.metrics.agent', [$m['agent']->id, 'month' => $month]) }}"
                                   class="action-btn view">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="dashboard-card card-allok">
            <div class="dc-body" style="text-align:center;padding:48px 24px;">
                <div style="width:56px;height:56px;background:rgba(34,197,94,.13);border-radius:50%;
                            display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fa fa-check-circle" style="font-size:26px;color:#22c55e;"></i>
                </div>
                <div style="font-weight:700;font-size:15px;color:#1a1a2e;margin-bottom:6px;">¡Todo al día!</div>
                <div style="font-size:13px;color:#888;line-height:1.5;">
                    Todos los leads tienen seguimiento actualizado.
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /.funnel-grid --}}

    {{-- ══════════════════════════════════════════
         LEADERBOARD TABLE
    ══════════════════════════════════════════ --}}
    <div class="dashboard-card" style="margin-bottom:24px;">

        <div class="dc-header dc-header-dark">
            <h5>
                <i class="fa fa-trophy"></i>
                Rendimiento de Agentes
            </h5>
            <span class="dc-month">
                {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
            </span>
        </div>

        <div style="overflow-x:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th style="width:46px;text-align:center;">#</th>
                        <th>Agente</th>
                        <th style="text-align:center;">Leads</th>
                        <th style="text-align:center;">Cotizaciones</th>
                        <th style="text-align:center;">Conversiones</th>
                        <th style="text-align:center;">Tasa Conv.</th>
                        <th style="text-align:center;">Días Prom.</th>
                        <th style="text-align:center;">Recompensa</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($agentMetrics as $i => $m)
                    @php
                        $agent    = $m['agent'];
                        $rank     = $i + 1;
                        $rankIcon = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : '#' . $rank));
                        $cr       = $m['conv_rate'];
                        $crBadge  = $cr >= 30 ? 'won' : ($cr >= 15 ? 'proposal' : 'lost');
                    @endphp
                    <tr>

                        {{-- Rank --}}
                        <td style="text-align:center;" class="rank-cell">{{ $rankIcon }}</td>

                        {{-- Agent name --}}
                        <td>
                            <strong style="color:#1a1a2e;">{{ $agent->name }}</strong>
                            @if($m['idle_leads'] > 0)
                                <span class="crm-badge lost" style="margin-left:6px;"
                                      title="Leads sin seguimiento">
                                    {{ $m['idle_leads'] }} idle
                                </span>
                            @endif
                        </td>

                        {{-- Leads --}}
                        <td style="text-align:center;min-width:82px;">
                            <div style="font-weight:600;color:#1a1a2e;">
                                {{ $m['leads'] }}
                                @if($m['leads_pct'] !== null)
                                    <span style="font-size:11px;color:#bbb;font-weight:400;">
                                        / {{ $m['goal']->leads_goal }}
                                    </span>
                                @endif
                            </div>
                            @if($m['leads_pct'] !== null)
                                <div class="mini-progress">
                                    <div class="mini-progress-bar"
                                         style="width:{{ min(100, $m['leads_pct']) }}%;
                                                background:{{ $m['leads_pct'] >= 100 ? '#22c55e' : ($m['leads_pct'] >= 70 ? '#eab308' : '#ef4444') }};"></div>
                                </div>
                            @endif
                        </td>

                        {{-- Cotizaciones --}}
                        <td style="text-align:center;min-width:92px;">
                            <div style="font-weight:600;color:#1a1a2e;">
                                {{ $m['quotes'] }}
                                @if($m['quotes_pct'] !== null)
                                    <span style="font-size:11px;color:#bbb;font-weight:400;">
                                        / {{ $m['goal']->quotes_goal }}
                                    </span>
                                @endif
                            </div>
                            @if($m['quotes_pct'] !== null)
                                <div class="mini-progress">
                                    <div class="mini-progress-bar"
                                         style="width:{{ min(100, $m['quotes_pct']) }}%;
                                                background:{{ $m['quotes_pct'] >= 100 ? '#22c55e' : ($m['quotes_pct'] >= 70 ? '#eab308' : '#ef4444') }};"></div>
                                </div>
                            @endif
                        </td>

                        {{-- Conversiones --}}
                        <td style="text-align:center;min-width:92px;">
                            <div style="font-weight:600;color:#1a1a2e;">
                                {{ $m['conversions'] }}
                                @if($m['conv_pct'] !== null)
                                    <span style="font-size:11px;color:#bbb;font-weight:400;">
                                        / {{ $m['goal']->conversions_goal }}
                                    </span>
                                @endif
                            </div>
                            @if($m['conv_pct'] !== null)
                                <div class="mini-progress">
                                    <div class="mini-progress-bar"
                                         style="width:{{ min(100, $m['conv_pct']) }}%;
                                                background:{{ $m['conv_pct'] >= 100 ? '#22c55e' : ($m['conv_pct'] >= 70 ? '#eab308' : '#ef4444') }};"></div>
                                </div>
                            @endif
                        </td>

                        {{-- Tasa conversión --}}
                        <td style="text-align:center;">
                            <span class="crm-badge {{ $crBadge }}">{{ $cr }}%</span>
                        </td>

                        {{-- Días promedio --}}
                        <td style="text-align:center;">
                            @if($m['avg_days'] !== null)
                                <span style="font-size:13px;color:#555;font-weight:600;">
                                    {{ $m['avg_days'] }}<span style="font-size:11px;font-weight:400;color:#aaa;">d</span>
                                </span>
                            @else
                                <span style="color:#ccc;">—</span>
                            @endif
                        </td>

                        {{-- Recompensa elegible --}}
                        <td style="text-align:center;">
                            @if($m['eligible_reward'])
                                <span class="crm-badge proposal"
                                      style="display:inline-flex;align-items:center;gap:4px;"
                                      title="{{ $m['eligible_reward']->name }}">
                                    <i class="fa fa-trophy"></i>
                                    {{ $m['eligible_reward']->formatted_value }}
                                </span>
                            @else
                                <span style="color:#ccc;">—</span>
                            @endif
                        </td>

                        {{-- Detalle --}}
                        <td>
                            <a href="{{ route('admin.metrics.agent', [$agent->id, 'month' => $month]) }}"
                               class="action-btn view">
                                <i class="fa fa-bar-chart"></i> Detalle
                            </a>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;color:#aaa;padding:36px;">
                            <i class="fa fa-users" style="font-size:22px;display:block;margin-bottom:8px;"></i>
                            No hay agentes registrados.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>{{-- /.leaderboard card --}}

    {{-- ══════════════════════════════════════════
         RECOMPENSAS RECIENTES
    ══════════════════════════════════════════ --}}
    @if($recentGrants->count() > 0)
    <div class="dashboard-card">
        <div class="dc-header dc-header-dark">
            <h5>
                <i class="fa fa-trophy"></i>
                Recompensas Otorgadas Recientemente
            </h5>
            <a href="{{ route('admin.rewards.grants.index') }}"
               class="action-btn gold" style="font-size:12px;padding:5px 10px;">
                Ver todas <i class="fa fa-arrow-right"></i>
            </a>
        </div>
        <div style="overflow-x:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Agente</th>
                        <th>Recompensa</th>
                        <th>Mes</th>
                        <th style="text-align:center;">Conversiones</th>
                        <th>Otorgado</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentGrants as $grant)
                    <tr>
                        <td><strong>{{ $grant->agent?->name ?? '—' }}</strong></td>
                        <td>
                            <span class="crm-badge proposal"
                                  style="display:inline-flex;align-items:center;gap:4px;">
                                <i class="fa fa-trophy"></i>
                                {{ $grant->reward?->name ?? '—' }}
                            </span>
                        </td>
                        <td style="font-size:13px;color:#555;">
                            {{ \Carbon\Carbon::parse($grant->month)->translatedFormat('M Y') }}
                        </td>
                        <td style="text-align:center;font-weight:600;">
                            {{ $grant->conversions_count }}
                        </td>
                        <td style="font-size:12px;color:#999;">
                            {{ $grant->granted_at?->diffForHumans() }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>{{-- /.crm-page --}}

@push('script')
<script>
(function () {
    const ctx = document.getElementById('funnelChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Capturados', 'Contactados', 'En Proceso', 'Convertidos', 'Perdidos'],
            datasets: [{
                label: 'Leads',
                data: [
                    {{ $funnel['captured'] }},
                    {{ $funnel['contacted'] }},
                    {{ $funnel['in_progress'] }},
                    {{ $funnel['converted'] }},
                    {{ $funnel['lost'] }}
                ],
                backgroundColor: ['#3b82f6', '#6366f1', '#eab308', '#22c55e', '#ef4444'],
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
})();
</script>
@endpush

@endsection
