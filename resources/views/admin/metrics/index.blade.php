@extends('admin.main')
@section('title', 'Métricas de Agentes')
@section('content')

@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <strong>{{ Session::get('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
    </div>
@endif

<div class="container-fluid">

    {{-- ===== FILTROS ===== --}}
    <div class="row mb-3">
        <div class="col-12">
            <form method="GET" class="form-inline">
                @if(Auth::user()->isSuperAdmin())
                    <input type="number" name="company_id" value="{{ $companyId }}" class="form-control form-control-sm mr-2" placeholder="Company ID">
                @endif
                <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm mr-2">
                <button class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
                <a href="{{ route('admin.metrics.goals.index', ['month' => $month]) }}" class="btn btn-sm btn-secondary ml-2">
                    <i class="fa fa-bullseye"></i> Gestionar Metas
                </a>
                <a href="{{ route('admin.rewards.index') }}" class="btn btn-sm btn-warning ml-2">
                    <i class="fa fa-trophy"></i> Recompensas
                </a>
            </form>
        </div>
    </div>

    {{-- ===== TARJETAS DE RESUMEN ===== --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body py-3 text-center">
                    <h2 class="mb-0 font-weight-bold">{{ $funnel['captured'] }}</h2>
                    <small><i class="fa fa-users"></i> Leads Capturados</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body py-3 text-center">
                    <h2 class="mb-0 font-weight-bold">{{ $funnel['in_progress'] }}</h2>
                    <small><i class="fa fa-spinner"></i> En Proceso</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body py-3 text-center">
                    <h2 class="mb-0 font-weight-bold">{{ $funnel['converted'] }}</h2>
                    <small><i class="fa fa-check-circle"></i> Conversiones</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body py-3 text-center">
                    <h2 class="mb-0 font-weight-bold">{{ $funnel['lost'] }}</h2>
                    <small><i class="fa fa-times-circle"></i> Perdidos</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- ===== FUNNEL DE CONVERSIÓN ===== --}}
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="fa fa-filter"></i> Embudo de Conversión — {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                </div>
                <div class="card-body">
                    @php
                        $funnelSteps = [
                            ['label' => 'Capturados',   'count' => $funnel['captured'],    'color' => 'primary'],
                            ['label' => 'Contactados',  'count' => $funnel['contacted'],   'color' => 'info'],
                            ['label' => 'En Proceso',   'count' => $funnel['in_progress'], 'color' => 'warning'],
                            ['label' => 'Convertidos',  'count' => $funnel['converted'],   'color' => 'success'],
                            ['label' => 'Perdidos',     'count' => $funnel['lost'],        'color' => 'danger'],
                        ];
                        $base = max($funnel['captured'], 1);
                    @endphp
                    @foreach($funnelSteps as $step)
                    @php $pct = round(($step['count'] / $base) * 100); @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="font-weight-bold">{{ $step['label'] }}</small>
                            <small>{{ $step['count'] }} ({{ $pct }}%)</small>
                        </div>
                        <div class="progress" style="height:18px;">
                            <div class="progress-bar bg-{{ $step['color'] }}" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                    <canvas id="funnelChart" height="160" class="mt-3"></canvas>
                </div>
            </div>
        </div>

        {{-- ===== LEADS SIN SEGUIMIENTO ===== --}}
        <div class="col-lg-7 mb-4">
            @php
                $idleLeads = $agentMetrics->sum('idle_leads');
            @endphp
            @if($idleLeads > 0)
            <div class="card border-danger shadow-sm">
                <div class="card-header bg-danger text-white">
                    <i class="fa fa-exclamation-triangle"></i> Leads Sin Seguimiento (+5 días): {{ $idleLeads }}
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-borderless mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Agente</th>
                                <th class="text-center">Leads Idle</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($agentMetrics->where('idle_leads', '>', 0) as $m)
                            <tr>
                                <td>{{ $m['agent']->name }}</td>
                                <td class="text-center">
                                    <span class="badge badge-danger">{{ $m['idle_leads'] }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.metrics.agent', [$m['agent']->id, 'month' => $month]) }}" class="btn btn-xs btn-outline-primary">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="card border-success shadow-sm">
                <div class="card-body text-center text-success py-4">
                    <i class="fa fa-check-circle fa-2x"></i>
                    <p class="mt-2 mb-0">Todos los leads tienen seguimiento actualizado.</p>
                </div>
            </div>
            @endif
        </div>

    </div>

    {{-- ===== TABLA LEADERBOARD ===== --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span><i class="fa fa-trophy"></i> Rendimiento de Agentes</span>
            <small class="text-muted">{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Agente</th>
                            <th class="text-center">Leads</th>
                            <th class="text-center">Cotizaciones</th>
                            <th class="text-center">Conversiones</th>
                            <th class="text-center">Tasa Conv.</th>
                            <th class="text-center">Días Prom.</th>
                            <th class="text-center">Recompensa</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($agentMetrics as $i => $m)
                        @php
                            $agent = $m['agent'];
                            $rank = $i + 1;
                            $rankIcon = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : "#$rank"));
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold">{{ $rankIcon }}</td>
                            <td>
                                <strong>{{ $agent->name }}</strong>
                                @if($m['idle_leads'] > 0)
                                    <span class="badge badge-danger ml-1" title="Leads sin seguimiento">{{ $m['idle_leads'] }} idle</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div>{{ $m['leads'] }}
                                @if($m['leads_pct'] !== null)
                                    <small class="text-muted">/ {{ $m['goal']->leads_goal }}</small>
                                @endif
                                </div>
                                @if($m['leads_pct'] !== null)
                                <div class="progress mt-1" style="height:6px;" title="{{ $m['leads_pct'] }}%">
                                    <div class="progress-bar {{ $m['leads_pct'] >= 100 ? 'bg-success' : ($m['leads_pct'] >= 70 ? 'bg-warning' : 'bg-danger') }}"
                                         style="width:{{ $m['leads_pct'] }}%"></div>
                                </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <div>{{ $m['quotes'] }}
                                @if($m['quotes_pct'] !== null)
                                    <small class="text-muted">/ {{ $m['goal']->quotes_goal }}</small>
                                @endif
                                </div>
                                @if($m['quotes_pct'] !== null)
                                <div class="progress mt-1" style="height:6px;" title="{{ $m['quotes_pct'] }}%">
                                    <div class="progress-bar {{ $m['quotes_pct'] >= 100 ? 'bg-success' : ($m['quotes_pct'] >= 70 ? 'bg-warning' : 'bg-danger') }}"
                                         style="width:{{ $m['quotes_pct'] }}%"></div>
                                </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <div>{{ $m['conversions'] }}
                                @if($m['conv_pct'] !== null)
                                    <small class="text-muted">/ {{ $m['goal']->conversions_goal }}</small>
                                @endif
                                </div>
                                @if($m['conv_pct'] !== null)
                                <div class="progress mt-1" style="height:6px;" title="{{ $m['conv_pct'] }}%">
                                    <div class="progress-bar {{ $m['conv_pct'] >= 100 ? 'bg-success' : ($m['conv_pct'] >= 70 ? 'bg-warning' : 'bg-danger') }}"
                                         style="width:{{ $m['conv_pct'] }}%"></div>
                                </div>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $cr = $m['conv_rate'];
                                    $crColor = $cr >= 30 ? 'success' : ($cr >= 15 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge badge-{{ $crColor }}">{{ $m['conv_rate'] }}%</span>
                            </td>
                            <td class="text-center">
                                @if($m['avg_days'] !== null)
                                    <span class="badge badge-secondary">{{ $m['avg_days'] }}d</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($m['eligible_reward'])
                                    <span class="badge badge-warning" title="{{ $m['eligible_reward']->name }}">
                                        <i class="fa fa-trophy"></i> {{ $m['eligible_reward']->formatted_value }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.metrics.agent', [$agent->id, 'month' => $month]) }}"
                                   class="btn btn-xs btn-outline-primary">Detalle</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No hay agentes registrados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== RECOMPENSAS OTORGADAS RECIENTEMENTE ===== --}}
    @if($recentGrants->count() > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <i class="fa fa-trophy"></i> Recompensas Otorgadas Recientemente
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Agente</th>
                        <th>Recompensa</th>
                        <th>Mes</th>
                        <th>Conversiones</th>
                        <th>Otorgado</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentGrants as $grant)
                    <tr>
                        <td>{{ $grant->agent?->name ?? '—' }}</td>
                        <td>
                            <span class="badge badge-warning">{{ $grant->reward?->name ?? '—' }}</span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($grant->month)->format('M Y') }}</td>
                        <td class="text-center">{{ $grant->conversions_count }}</td>
                        <td>
                            <small class="text-muted">{{ $grant->granted_at?->diffForHumans() }}</small>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('admin.rewards.grants.index') }}" class="btn btn-sm btn-warning">
                Ver todos los grants <i class="fa fa-arrow-right"></i>
            </a>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
(function () {
    const ctx = document.getElementById('funnelChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Capturados','Contactados','En Proceso','Convertidos','Perdidos'],
            datasets: [{
                label: 'Leads',
                data: [{{ $funnel['captured'] }}, {{ $funnel['contacted'] }}, {{ $funnel['in_progress'] }}, {{ $funnel['converted'] }}, {{ $funnel['lost'] }}],
                backgroundColor: ['#4e73df','#36b9cc','#f6c23e','#1cc88a','#e74a3b'],
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
