@extends('admin.main')
@section('title', 'Métricas — ' . $user->name)
@section('content')

@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <strong>{{ Session::get('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
    </div>
@endif

<div class="container-fluid">

    {{-- Header del agente --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('admin.metrics.index', ['month' => $month]) }}" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
            <span class="ml-2 h5 font-weight-bold">{{ $user->name }}</span>
            <span class="badge badge-secondary ml-1">Agente</span>
        </div>
        <form method="GET" class="form-inline">
            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm mr-2">
            <button class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
        </form>
    </div>

    {{-- ===== TARJETAS DE PROGRESO ===== --}}
    <div class="row mb-4">
        @php
            $stats = [
                ['label'=>'Leads Capturados',  'count'=>$leads->count(),        'goal'=>$goal?->leads_goal,       'color'=>'primary',  'icon'=>'fa-users'],
                ['label'=>'Cotizaciones',       'count'=>$quotes->count(),       'goal'=>$goal?->quotes_goal,      'color'=>'info',     'icon'=>'fa-calculator'],
                ['label'=>'Conversiones',       'count'=>$leads->where('sale_status','converted')->count(), 'goal'=>$goal?->conversions_goal, 'color'=>'success', 'icon'=>'fa-check-circle'],
            ];
        @endphp
        @foreach($stats as $s)
        @php
            $pct = ($s['goal'] > 0) ? min(100, round(($s['count']/$s['goal'])*100)) : null;
            $barColor = $pct === null ? 'secondary' : ($pct >= 100 ? 'success' : ($pct >= 70 ? 'warning' : 'danger'));
        @endphp
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">{{ $s['label'] }}</p>
                            <h3 class="font-weight-bold mb-0 text-{{ $s['color'] }}">{{ $s['count'] }}</h3>
                            @if($s['goal'])
                                <small class="text-muted">Meta: {{ $s['goal'] }}</small>
                            @else
                                <small class="text-muted">Sin meta asignada</small>
                            @endif
                        </div>
                        <div class="align-self-center">
                            <i class="fa {{ $s['icon'] }} fa-2x text-{{ $s['color'] }}"></i>
                        </div>
                    </div>
                    @if($pct !== null)
                    <div class="mt-2">
                        <div class="d-flex justify-content-between"><small>Progreso</small><small>{{ $pct }}%</small></div>
                        <div class="progress mt-1" style="height:8px;">
                            <div class="progress-bar bg-{{ $barColor }}" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row">

        {{-- ===== HISTORIAL DE LEADS ===== --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <span><i class="fa fa-users"></i> Leads Capturados</span>
                    <span class="badge badge-light text-primary">{{ $leads->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:500px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light" style="position:sticky;top:0;">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Estado</th>
                                    <th>Interés</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Seguimientos</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($leads as $lead)
                                @php
                                    $cats = \App\Models\EventLead::leadCategories();
                                    $statuses = \App\Models\EventLead::saleStatuses();
                                    $catLabel = $cats[$lead->lead_category]['label'] ?? ($lead->lead_category ?? '—');
                                    $statusInfo = $statuses[$lead->sale_status] ?? null;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $lead->name }}</strong><br>
                                        <small class="text-muted">{{ $lead->phone }}</small>
                                    </td>
                                    <td>
                                        @if($lead->lead_category)
                                            <span class="badge badge-secondary">{{ $catLabel }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($statusInfo)
                                            <span class="badge badge-{{ $statusInfo['color'] }}">{{ $statusInfo['label'] }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $lead->sale_status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $lead->interest_level === 'hot' ? 'danger' : ($lead->interest_level === 'high' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($lead->interest_level) }}
                                        </span>
                                    </td>
                                    <td><small>{{ $lead->created_at->format('d/m/Y') }}</small></td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ $lead->followups->count() }}</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-outline-primary"
                                            data-toggle="modal"
                                            data-target="#followupModal"
                                            data-lead-id="{{ $lead->id }}"
                                            data-lead-name="{{ $lead->name }}"
                                            data-lead-status="{{ $lead->sale_status }}">
                                            <i class="fa fa-plus"></i> Seguimiento
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">No hay leads para este mes.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== PANEL DERECHO ===== --}}
        <div class="col-lg-4">

            {{-- Distribución por categoría --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <i class="fa fa-pie-chart"></i> Por Categoría
                </div>
                <div class="card-body p-2">
                    @forelse($byCategory as $cat => $cnt)
                        @php $cats = \App\Models\EventLead::leadCategories(); $lbl = $cats[$cat]['label'] ?? ucfirst($cat); @endphp
                        <div class="d-flex justify-content-between mb-1">
                            <small>{{ $lbl }}</small>
                            <span class="badge badge-secondary">{{ $cnt }}</span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Sin datos</p>
                    @endforelse
                    <canvas id="categoryChart" height="160" class="mt-2"></canvas>
                </div>
            </div>

            {{-- Recompensas recibidas --}}
            @if($grants->count() > 0)
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-warning text-dark">
                    <i class="fa fa-trophy"></i> Recompensas
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        @foreach($grants as $grant)
                        <tr>
                            <td>{{ $grant->reward?->name ?? '—' }}</td>
                            <td><small class="text-muted">{{ \Carbon\Carbon::parse($grant->month)->format('M Y') }}</small></td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            @endif

            {{-- Historial de meses --}}
            @if($history->count() > 0)
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <i class="fa fa-history"></i> Historial (12 meses)
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Mes</th>
                                <th class="text-center">Leads</th>
                                <th class="text-center">Conv.</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($history as $h)
                            <tr>
                                <td><small>{{ \Carbon\Carbon::parse($h['goal']->month)->format('M Y') }}</small></td>
                                <td class="text-center"><small>{{ $h['leads'] }}</small></td>
                                <td class="text-center"><small>{{ $h['conversions'] }}</small></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>

{{-- ===== MODAL DE SEGUIMIENTO ===== --}}
<div class="modal fade" id="followupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-phone"></i> Registrar Seguimiento</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="followupForm">
                @csrf
                <input type="hidden" id="followupLeadId" name="lead_id">
                <div class="modal-body">
                    <p class="mb-3"><strong id="followupLeadName"></strong></p>

                    <div class="form-group">
                        <label>Tipo de contacto *</label>
                        <select name="action" class="form-control" required>
                            @foreach(\App\Models\LeadFollowup::actions() as $key => $action)
                                <option value="{{ $key }}">{{ $action['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Resultado *</label>
                        <select name="outcome" class="form-control" required>
                            @foreach(\App\Models\LeadFollowup::outcomes() as $key => $outcome)
                                <option value="{{ $key }}">{{ $outcome['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Notas</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Detalles del contacto..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Próximo seguimiento</label>
                        <input type="datetime-local" name="next_followup_at" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Seguimiento Modal
$('#followupModal').on('show.bs.modal', function(e) {
    var btn = $(e.relatedTarget);
    $('#followupLeadId').val(btn.data('lead-id'));
    $('#followupLeadName').text(btn.data('lead-name'));
});

$('#followupForm').on('submit', function(e) {
    e.preventDefault();
    var leadId = $('#followupLeadId').val();
    var url = '/admin/event-leads/' + leadId + '/followups';
    var data = $(this).serialize();

    $.post(url, data, function(res) {
        if (res.success) {
            $('#followupModal').modal('hide');
            location.reload();
        }
    }).fail(function(xhr) {
        alert('Error al guardar: ' + (xhr.responseJSON?.message || 'Error desconocido'));
    });
});

// Gráfica de categorías
@php
    $catKeys = $byCategory->keys()->toArray();
    $catVals = $byCategory->values()->toArray();
    $cats = \App\Models\EventLead::leadCategories();
    $catLabels = array_map(fn($k) => $cats[$k]['label'] ?? ucfirst($k), $catKeys);
@endphp
(function () {
    const ctx = document.getElementById('categoryChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($catLabels),
            datasets: [{
                data: @json($catVals),
                backgroundColor: ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796'],
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
})();
</script>
@endpush

@endsection
