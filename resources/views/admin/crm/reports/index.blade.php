@extends('admin.main')
@section('title', 'CRM — Reportería')
@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fa fa-file-pdf-o text-danger"></i> Reportería CRM</h4>
        <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Volver a Leads
        </a>
    </div>

    {{-- ── Filtros globales ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <strong><i class="fa fa-filter"></i> Filtros para el reporte</strong>
            <small class="text-muted ml-2">(aplican a todos los reportes)</small>
        </div>
        <div class="card-body">
            <form id="report-filters" method="GET" target="_blank">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="small text-muted mb-1">Agente</label>
                        <select name="user_id" class="form-control form-control-sm">
                            <option value="">Todos los agentes</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="small text-muted mb-1">Estado del lead</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Todos los estados</option>
                            @foreach(\App\Lead::getStatuses() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="small text-muted mb-1">Fuente</label>
                        <select name="source" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            @foreach(\App\Lead::getSources() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="small text-muted mb-1">Prioridad</label>
                        <select name="priority" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            @foreach(\App\Lead::getPriorities() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="small text-muted mb-1">Desde (creación)</label>
                        <input type="date" name="date_from" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="small text-muted mb-1">Hasta (creación)</label>
                        <input type="date" name="date_to" class="form-control form-control-sm">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Tarjetas de reportes ── --}}
    <div class="row">

        {{-- 3.1 Detalle por lead --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <span class="bg-primary text-white rounded p-2 mr-3">
                            <i class="fa fa-user fa-lg"></i>
                        </span>
                        <div>
                            <h6 class="mb-1">Detalle por Lead</h6>
                            <small class="text-muted">Historial completo: actividades, citas, recordatorios, cambios de estado.</small>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small text-muted mb-1">Seleccionar lead</label>
                        <div class="position-relative">
                            <input type="text" id="lead-search-input" class="form-control form-control-sm"
                                   placeholder="Buscar lead por nombre..." autocomplete="off">
                            <div id="lead-search-dropdown"
                                 class="list-group shadow-sm"
                                 style="position:absolute;top:100%;left:0;right:0;z-index:100;display:none;max-height:200px;overflow-y:auto;">
                            </div>
                        </div>
                        <input type="hidden" id="selected-lead-id">
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex" style="gap:8px;">
                    <button class="btn btn-outline-primary btn-sm flex-fill" onclick="openLeadReport(false)">
                        <i class="fa fa-eye"></i> Ver PDF
                    </button>
                    <button class="btn btn-primary btn-sm flex-fill" onclick="openLeadReport(true)">
                        <i class="fa fa-download"></i> Descargar
                    </button>
                </div>
            </div>
        </div>

        {{-- 3.2 Pipeline --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <span class="bg-dark text-white rounded p-2 mr-3">
                            <i class="fa fa-columns fa-lg"></i>
                        </span>
                        <div>
                            <h6 class="mb-1">Reporte Pipeline</h6>
                            <small class="text-muted">Leads agrupados por etapa/estado, cantidades, montos y actividad.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex" style="gap:8px;">
                    <button class="btn btn-outline-dark btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.pipeline') }}', false)">
                        <i class="fa fa-eye"></i> Ver PDF
                    </button>
                    <button class="btn btn-dark btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.pipeline') }}', true)">
                        <i class="fa fa-download"></i> Descargar
                    </button>
                </div>
            </div>
        </div>

        {{-- 3.3 General de leads --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <span class="bg-info text-white rounded p-2 mr-3">
                            <i class="fa fa-list fa-lg"></i>
                        </span>
                        <div>
                            <h6 class="mb-1">General de Leads</h6>
                            <small class="text-muted">Todos los leads con información general, actividad reciente, estado y responsable.</small>
                        </div>
                    </div>
                    <div class="d-flex" style="gap:12px;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="chk-no-followup" name="no_followup" value="1">
                            <label class="custom-control-label small" for="chk-no-followup">Sin seguimiento</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="chk-overdue" name="overdue" value="1">
                            <label class="custom-control-label small" for="chk-overdue">Solo vencidos</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex" style="gap:8px;">
                    <button class="btn btn-outline-info btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.leads-general') }}', false, {no_followup: document.getElementById('chk-no-followup').checked ? 1 : '', overdue: document.getElementById('chk-overdue').checked ? 1 : ''})">
                        <i class="fa fa-eye"></i> Ver PDF
                    </button>
                    <button class="btn btn-info btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.leads-general') }}', true, {no_followup: document.getElementById('chk-no-followup').checked ? 1 : '', overdue: document.getElementById('chk-overdue').checked ? 1 : ''})">
                        <i class="fa fa-download"></i> Descargar
                    </button>
                </div>
            </div>
        </div>

        {{-- 3.5 Citas --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <span class="bg-success text-white rounded p-2 mr-3">
                            <i class="fa fa-calendar fa-lg"></i>
                        </span>
                        <div>
                            <h6 class="mb-1">Reporte de Citas</h6>
                            <small class="text-muted">Citas por lead, rango de fechas, responsable y estado.</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="small text-muted mb-1">Tipo de cita</label>
                            <select id="apt-type" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach(\App\Appointment::getTypes() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="small text-muted mb-1">Estado</label>
                            <select id="apt-status" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach(\App\Appointment::getStatuses() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex" style="gap:8px;">
                    <button class="btn btn-outline-success btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.appointments') }}', false, {type: document.getElementById('apt-type').value, status: document.getElementById('apt-status').value})">
                        <i class="fa fa-eye"></i> Ver PDF
                    </button>
                    <button class="btn btn-success btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.appointments') }}', true, {type: document.getElementById('apt-type').value, status: document.getElementById('apt-status').value})">
                        <i class="fa fa-download"></i> Descargar
                    </button>
                </div>
            </div>
        </div>

        {{-- 3.6 Seguimientos --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <span class="bg-warning text-dark rounded p-2 mr-3">
                            <i class="fa fa-clock-o fa-lg"></i>
                        </span>
                        <div>
                            <h6 class="mb-1">Reporte de Seguimientos</h6>
                            <small class="text-muted">Leads con seguimientos: fecha, responsable, tipo, resultado y próxima acción.</small>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small text-muted mb-1">Filtrar por tipo</label>
                        <select id="followup-type" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="overdue">Solo vencidos</option>
                            <option value="today">Solo de hoy</option>
                            <option value="upcoming">Próximos</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex" style="gap:8px;">
                    <button class="btn btn-outline-warning btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.follow-ups') }}', false, {followup_type: document.getElementById('followup-type').value})">
                        <i class="fa fa-eye"></i> Ver PDF
                    </button>
                    <button class="btn btn-warning btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.follow-ups') }}', true, {followup_type: document.getElementById('followup-type').value})">
                        <i class="fa fa-download"></i> Descargar
                    </button>
                </div>
            </div>
        </div>

        {{-- Extra: Sin seguimiento --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <span class="bg-danger text-white rounded p-2 mr-3">
                            <i class="fa fa-exclamation-triangle fa-lg"></i>
                        </span>
                        <div>
                            <h6 class="mb-1">Leads Sin Seguimiento</h6>
                            <small class="text-muted">Leads activos sin fecha de seguimiento asignada. Útil para detectar leads abandonados.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex" style="gap:8px;">
                    <button class="btn btn-outline-danger btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.no-followup') }}', false)">
                        <i class="fa fa-eye"></i> Ver PDF
                    </button>
                    <button class="btn btn-danger btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.no-followup') }}', true)">
                        <i class="fa fa-download"></i> Descargar
                    </button>
                </div>
            </div>
        </div>

        {{-- Extra: Por agente --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <span style="background:#6f42c1;" class="text-white rounded p-2 mr-3">
                            <i class="fa fa-users fa-lg"></i>
                        </span>
                        <div>
                            <h6 class="mb-1">Productividad por Agente</h6>
                            <small class="text-muted">Leads asignados, conversiones, tasa de cierre y listado por vendedor.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex" style="gap:8px;">
                    <button class="btn btn-outline-secondary btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.by-agent') }}', false)">
                        <i class="fa fa-eye"></i> Ver PDF
                    </button>
                    <button class="btn btn-secondary btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.by-agent') }}', true)">
                        <i class="fa fa-download"></i> Descargar
                    </button>
                </div>
            </div>
        </div>

        {{-- Extra: Recordatorios vencidos --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <span class="bg-secondary text-white rounded p-2 mr-3">
                            <i class="fa fa-bell fa-lg"></i>
                        </span>
                        <div>
                            <h6 class="mb-1">Recordatorios Vencidos</h6>
                            <small class="text-muted">Recordatorios pendientes y vencidos del equipo. Identifica alertas sin atender.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex" style="gap:8px;">
                    <button class="btn btn-outline-secondary btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.overdue-reminders') }}', false)">
                        <i class="fa fa-eye"></i> Ver PDF
                    </button>
                    <button class="btn btn-secondary btn-sm flex-fill"
                            onclick="openReport('{{ route('admin.crm.reports.overdue-reminders') }}', true)">
                        <i class="fa fa-download"></i> Descargar
                    </button>
                </div>
            </div>
        </div>

    </div>{{-- /row --}}
</div>

@endsection

@push('script')
<script>
(function () {
    const SEARCH_URL = '{{ route('admin.crm.leads.quick-search') }}';

    /* ── Leer filtros globales del formulario ── */
    function globalFilters(extra) {
        const form   = document.getElementById('report-filters');
        const params = new URLSearchParams();
        new FormData(form).forEach((v, k) => { if (v) params.set(k, v); });
        if (extra) Object.entries(extra).forEach(([k, v]) => { if (v) params.set(k, v); });
        return params;
    }

    /* ── Abrir reporte en nueva pestaña ── */
    window.openReport = function (baseUrl, download, extra) {
        const params = globalFilters(extra || {});
        if (download) params.set('download', '1');
        window.open(baseUrl + '?' + params.toString(), '_blank');
    };

    /* ── Reporte de lead individual ── */
    window.openLeadReport = function (download) {
        const leadId = document.getElementById('selected-lead-id').value;
        if (!leadId) { alert('Selecciona un lead primero.'); return; }
        const params = new URLSearchParams();
        if (download) params.set('download', '1');
        const url = '{{ url('admin/crm/reports/lead') }}/' + leadId + '?' + params.toString();
        window.open(url, '_blank');
    };

    /* ── Autocomplete de leads ── */
    const input    = document.getElementById('lead-search-input');
    const dropdown = document.getElementById('lead-search-dropdown');
    let timer = null;

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { dropdown.style.display = 'none'; return; }
        timer = setTimeout(() => {
            fetch(SEARCH_URL + '?q=' + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(results => {
                if (!results.length) {
                    dropdown.innerHTML = '<a class="list-group-item list-group-item-action disabled small text-muted py-2">Sin resultados</a>';
                } else {
                    dropdown.innerHTML = results.map(l =>
                        `<a href="#" class="list-group-item list-group-item-action py-2 px-3 lead-option"
                            data-id="${l.id}" data-name="${l.name.replace(/"/g,'&quot;')}"
                            style="font-size:0.85rem;">
                            <strong>${esc(l.name)}</strong>
                            <span class="badge badge-${l.color} ml-1">${esc(l.status)}</span>
                            <br><small class="text-muted">${esc(l.phone || l.email || '')}</small>
                        </a>`
                    ).join('');
                    dropdown.querySelectorAll('.lead-option').forEach(el => {
                        el.addEventListener('click', e => {
                            e.preventDefault();
                            document.getElementById('selected-lead-id').value = el.dataset.id;
                            input.value = el.dataset.name;
                            dropdown.style.display = 'none';
                        });
                    });
                }
                dropdown.style.display = 'block';
            });
        }, 300);
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
})();
</script>
@endpush
