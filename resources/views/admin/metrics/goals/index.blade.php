@extends('admin.main')
@section('title', 'Metas de Agentes')
@section('content')

@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <strong>{{ Session::get('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
    </div>
@endif

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('admin.metrics.index', ['month' => $month]) }}" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left"></i> Dashboard
            </a>
            <span class="ml-2 h5 font-weight-bold">Metas Mensuales</span>
        </div>
        <form method="GET" class="form-inline">
            @if(Auth::user()->isSuperAdmin())
                <input type="number" name="company_id" value="{{ $companyId }}" class="form-control form-control-sm mr-2" placeholder="Company ID">
            @endif
            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm mr-2">
            <button class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="fa fa-bullseye"></i>
            Metas para {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
        </div>
        <div class="card-body p-0">
            <form method="POST" action="{{ route('admin.metrics.goals.bulk-store') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Agente</th>
                                <th class="text-center" style="min-width:130px;">
                                    <i class="fa fa-users text-primary"></i> Leads
                                    <br><small class="text-muted font-weight-normal">Meta / Real</small>
                                </th>
                                <th class="text-center" style="min-width:130px;">
                                    <i class="fa fa-calculator text-info"></i> Cotizaciones
                                    <br><small class="text-muted font-weight-normal">Meta / Real</small>
                                </th>
                                <th class="text-center" style="min-width:130px;">
                                    <i class="fa fa-check-circle text-success"></i> Conversiones
                                    <br><small class="text-muted font-weight-normal">Meta / Real</small>
                                </th>
                                <th class="text-center">Acción rápida</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($agents as $agent)
                            @php
                                $goal = $goals->get($agent->id);
                                $metrics = $goal ? $goal->metrics() : ['leads'=>0,'quotes'=>0,'conversions'=>0];
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $agent->name }}</strong><br>
                                    <small class="text-muted">{{ $agent->email }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="input-group input-group-sm justify-content-center" style="max-width:110px;margin:auto;">
                                        <input type="number" name="goals[{{ $agent->id }}][leads_goal]"
                                               value="{{ $goal?->leads_goal ?? 0 }}"
                                               class="form-control text-center" min="0" max="9999">
                                    </div>
                                    <small class="text-muted">Real: <strong>{{ $metrics['leads'] }}</strong></small>
                                    @if($goal && $goal->leads_goal > 0)
                                    @php $pct = min(100, round(($metrics['leads']/$goal->leads_goal)*100)); @endphp
                                    <div class="progress mt-1" style="height:4px;">
                                        <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : ($pct >= 70 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $pct }}%"></div>
                                    </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="input-group input-group-sm justify-content-center" style="max-width:110px;margin:auto;">
                                        <input type="number" name="goals[{{ $agent->id }}][quotes_goal]"
                                               value="{{ $goal?->quotes_goal ?? 0 }}"
                                               class="form-control text-center" min="0" max="9999">
                                    </div>
                                    <small class="text-muted">Real: <strong>{{ $metrics['quotes'] }}</strong></small>
                                    @if($goal && $goal->quotes_goal > 0)
                                    @php $pct = min(100, round(($metrics['quotes']/$goal->quotes_goal)*100)); @endphp
                                    <div class="progress mt-1" style="height:4px;">
                                        <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : ($pct >= 70 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $pct }}%"></div>
                                    </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="input-group input-group-sm justify-content-center" style="max-width:110px;margin:auto;">
                                        <input type="number" name="goals[{{ $agent->id }}][conversions_goal]"
                                               value="{{ $goal?->conversions_goal ?? 0 }}"
                                               class="form-control text-center" min="0" max="9999">
                                    </div>
                                    <small class="text-muted">Real: <strong>{{ $metrics['conversions'] }}</strong></small>
                                    @if($goal && $goal->conversions_goal > 0)
                                    @php $pct = min(100, round(($metrics['conversions']/$goal->conversions_goal)*100)); @endphp
                                    <div class="progress mt-1" style="height:4px;">
                                        <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : ($pct >= 70 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $pct }}%"></div>
                                    </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.metrics.agent', [$agent->id, 'month' => $month]) }}"
                                       class="btn btn-xs btn-outline-primary">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay agentes registrados en esta empresa.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($agents->count() > 0)
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Guardar Todas las Metas
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>

</div>
@endsection
