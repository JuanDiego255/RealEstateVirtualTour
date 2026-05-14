@extends('admin.main')
@section('title', 'CRM — Reportería')
@section('content')

@include('admin.crm.layouts._crm-styles')
<style>
/* Report cards */
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}
.report-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,.07);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}
.report-card:hover { transform: translateY(-2px); box-shadow: 0 6px 22px rgba(0,0,0,.11); }
.report-card .rc-body { padding: 20px; flex: 1; }
.report-card .rc-icon {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; margin-bottom: 14px; flex-shrink: 0;
}
.report-card h6 { font-size: 14px; font-weight: 700; color: #1a1a2e; margin-bottom: 5px; }
.report-card p  { font-size: 12.5px; color: #888; margin: 0; line-height: 1.6; }
.report-card .rc-extras { margin-top: 14px; }
.report-card .rc-footer {
    padding: 12px 16px;
    background: #fafafa;
    border-top: 1px solid #f0f0f0;
    display: flex; gap: 8px;
}
.report-card .rc-footer .btn-pdf {
    flex: 1; padding: 7px; border-radius: 8px; font-size: 12.5px; font-weight: 600;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    border: none; cursor: pointer; transition: all .15s; text-decoration: none;
}

/* Filtros card */
.filters-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,.07);
    margin-bottom: 22px;
    overflow: hidden;
}
.filters-card .fc-header {
    padding: 14px 20px;
    border-bottom: 1px solid #f0f0f0;
    display: flex; align-items: center; gap: 10px;
}
.filters-card .fc-header span { font-size: 14px; font-weight: 600; color: #1a1a2e; }
.filters-card .fc-header i { color: #c2ac1f; }
.filters-card .fc-body { padding: 16px 20px; }

.filter-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 5px; display: block; }
.filter-select, .filter-input {
    width: 100%; border: 1px solid #e5e7eb; border-radius: 9px;
    padding: 8px 12px; font-size: 13px; background: #fafafa; outline: none;
    transition: border-color .15s;
}
.filter-select:focus, .filter-input:focus { border-color: #c2ac1f; background: #fff; }

/* Lead switcher in report */
.lead-sw-wrap { position: relative; }
#lead-search-dropdown {
    position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 200;
    background: #fff; border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,.13);
    display: none; max-height: 200px; overflow-y: auto;
}
.lsd-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 9px 14px; border-bottom: 1px solid #f5f5f5;
    cursor: pointer; font-size: 13px; color: #1a1a2e;
    transition: background .1s;
}
.lsd-item:last-child { border-bottom: none; }
.lsd-item:hover { background: #fffdf0; }
</style>

<div class="crm-page">

    {{-- Header --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-file-pdf-o"></i> Reportería CRM</h2>
            <div class="sub">Genera PDFs con filtros aplicados · Se abren en nueva pestaña</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver a Leads
            </a>
        </div>
    </div>

    {{-- Filtros globales --}}
    <div class="filters-card">
        <div class="fc-header">
            <i class="fa fa-filter"></i>
            <span>Filtros globales</span>
            <span style="font-size:12px; color:#aaa; margin-left:6px;">aplican a todos los reportes</span>
        </div>
        <div class="fc-body">
            <form id="report-filters">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:14px;">
                    <div>
                        <label class="filter-label">Agente</label>
                        <select name="user_id" class="filter-select">
                            <option value="">Todos los agentes</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="filter-label">Estado del lead</label>
                        <select name="status" class="filter-select">
                            <option value="">Todos</option>
                            @foreach(\App\Lead::getStatuses() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="filter-label">Fuente</label>
                        <select name="source" class="filter-select">
                            <option value="">Todas</option>
                            @foreach(\App\Lead::getSources() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="filter-label">Prioridad</label>
                        <select name="priority" class="filter-select">
                            <option value="">Todas</option>
                            @foreach(\App\Lead::getPriorities() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="filter-label">Desde (creación)</label>
                        <input type="date" name="date_from" class="filter-input">
                    </div>
                    <div>
                        <label class="filter-label">Hasta (creación)</label>
                        <input type="date" name="date_to" class="filter-input">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tarjetas de reportes --}}
    <div class="reports-grid">

        {{-- Detalle por Lead --}}
        <div class="report-card">
            <div class="rc-body">
                <div class="rc-icon" style="background:rgba(59,130,246,.13); color:#3b82f6;">
                    <i class="fa fa-user"></i>
                </div>
                <h6>Detalle por Lead</h6>
                <p>Historial completo del lead: actividades, citas, recordatorios y cambios de estado.</p>
                <div class="rc-extras">
                    <label class="filter-label">Seleccionar lead</label>
                    <div class="lead-sw-wrap">
                        <input type="text" id="lead-search-input" class="filter-input" placeholder="Buscar por nombre...">
                        <div id="lead-search-dropdown"></div>
                        <input type="hidden" id="selected-lead-id">
                    </div>
                </div>
            </div>
            <div class="rc-footer">
                <button class="btn-pdf" style="background:#dbeafe; color:#1d4ed8;" onclick="openLeadReport(false)">
                    <i class="fa fa-eye"></i> Ver PDF
                </button>
                <button class="btn-pdf" style="background:#3b82f6; color:#fff;" onclick="openLeadReport(true)">
                    <i class="fa fa-download"></i> Descargar
                </button>
            </div>
        </div>

        {{-- Pipeline --}}
        <div class="report-card">
            <div class="rc-body">
                <div class="rc-icon" style="background:#f1f5f9; color:#475569;">
                    <i class="fa fa-columns"></i>
                </div>
                <h6>Reporte Pipeline</h6>
                <p>Leads agrupados por etapa/estado, cantidades y resumen de actividad por columna.</p>
            </div>
            <div class="rc-footer">
                <button class="btn-pdf" style="background:#f1f5f9; color:#475569;" onclick="openReport('{{ route('admin.crm.reports.pipeline') }}', false)">
                    <i class="fa fa-eye"></i> Ver PDF
                </button>
                <button class="btn-pdf" style="background:#1a1a2e; color:#fff;" onclick="openReport('{{ route('admin.crm.reports.pipeline') }}', true)">
                    <i class="fa fa-download"></i> Descargar
                </button>
            </div>
        </div>

        {{-- General de leads --}}
        <div class="report-card">
            <div class="rc-body">
                <div class="rc-icon" style="background:rgba(99,102,241,.13); color:#6366f1;">
                    <i class="fa fa-list"></i>
                </div>
                <h6>General de Leads</h6>
                <p>Todos los leads con información, actividad registrada, estado y responsable.</p>
                <div class="rc-extras" style="display:flex; gap:16px; margin-top:12px;">
                    <label style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:#555; cursor:pointer;">
                        <input type="checkbox" id="chk-no-followup" style="accent-color:#c2ac1f;"> Sin seguimiento
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:#555; cursor:pointer;">
                        <input type="checkbox" id="chk-overdue" style="accent-color:#c2ac1f;"> Solo vencidos
                    </label>
                </div>
            </div>
            <div class="rc-footer">
                <button class="btn-pdf" style="background:#e0e7ff; color:#4338ca;"
                    onclick="openReport('{{ route('admin.crm.reports.leads-general') }}', false, {no_followup: document.getElementById('chk-no-followup').checked ? 1 : '', overdue: document.getElementById('chk-overdue').checked ? 1 : ''})">
                    <i class="fa fa-eye"></i> Ver PDF
                </button>
                <button class="btn-pdf" style="background:#6366f1; color:#fff;"
                    onclick="openReport('{{ route('admin.crm.reports.leads-general') }}', true, {no_followup: document.getElementById('chk-no-followup').checked ? 1 : '', overdue: document.getElementById('chk-overdue').checked ? 1 : ''})">
                    <i class="fa fa-download"></i> Descargar
                </button>
            </div>
        </div>

        {{-- Citas --}}
        <div class="report-card">
            <div class="rc-body">
                <div class="rc-icon" style="background:rgba(34,197,94,.13); color:#22c55e;">
                    <i class="fa fa-calendar"></i>
                </div>
                <h6>Reporte de Citas</h6>
                <p>Citas por agente, rango de fechas, tipo y estado. Incluye resultado de cada cita.</p>
                <div class="rc-extras" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:12px;">
                    <div>
                        <label class="filter-label">Tipo</label>
                        <select id="apt-type" class="filter-select">
                            <option value="">Todos</option>
                            @foreach(\App\Appointment::getTypes() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="filter-label">Estado</label>
                        <select id="apt-status" class="filter-select">
                            <option value="">Todos</option>
                            @foreach(\App\Appointment::getStatuses() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="rc-footer">
                <button class="btn-pdf" style="background:#d1fae5; color:#065f46;"
                    onclick="openReport('{{ route('admin.crm.reports.appointments') }}', false, {type: document.getElementById('apt-type').value, status: document.getElementById('apt-status').value})">
                    <i class="fa fa-eye"></i> Ver PDF
                </button>
                <button class="btn-pdf" style="background:#22c55e; color:#fff;"
                    onclick="openReport('{{ route('admin.crm.reports.appointments') }}', true, {type: document.getElementById('apt-type').value, status: document.getElementById('apt-status').value})">
                    <i class="fa fa-download"></i> Descargar
                </button>
            </div>
        </div>

        {{-- Seguimientos --}}
        <div class="report-card">
            <div class="rc-body">
                <div class="rc-icon" style="background:rgba(234,179,8,.13); color:#eab308;">
                    <i class="fa fa-clock-o"></i>
                </div>
                <h6>Reporte de Seguimientos</h6>
                <p>Leads con fecha de seguimiento: vencidos, de hoy o próximos, con historial reciente.</p>
                <div class="rc-extras" style="margin-top:12px;">
                    <label class="filter-label">Filtrar tipo</label>
                    <select id="followup-type" class="filter-select">
                        <option value="">Todos</option>
                        <option value="overdue">Solo vencidos</option>
                        <option value="today">Solo de hoy</option>
                        <option value="upcoming">Próximos</option>
                    </select>
                </div>
            </div>
            <div class="rc-footer">
                <button class="btn-pdf" style="background:#fef3c7; color:#92400e;"
                    onclick="openReport('{{ route('admin.crm.reports.follow-ups') }}', false, {followup_type: document.getElementById('followup-type').value})">
                    <i class="fa fa-eye"></i> Ver PDF
                </button>
                <button class="btn-pdf" style="background:#f59e0b; color:#fff;"
                    onclick="openReport('{{ route('admin.crm.reports.follow-ups') }}', true, {followup_type: document.getElementById('followup-type').value})">
                    <i class="fa fa-download"></i> Descargar
                </button>
            </div>
        </div>

        {{-- Sin seguimiento --}}
        <div class="report-card">
            <div class="rc-body">
                <div class="rc-icon" style="background:rgba(239,68,68,.13); color:#ef4444;">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <h6>Leads Sin Seguimiento</h6>
                <p>Leads activos sin fecha de seguimiento asignada. Detecta leads abandonados o sin atender.</p>
            </div>
            <div class="rc-footer">
                <button class="btn-pdf" style="background:#fee2e2; color:#991b1b;"
                    onclick="openReport('{{ route('admin.crm.reports.no-followup') }}', false)">
                    <i class="fa fa-eye"></i> Ver PDF
                </button>
                <button class="btn-pdf" style="background:#ef4444; color:#fff;"
                    onclick="openReport('{{ route('admin.crm.reports.no-followup') }}', true)">
                    <i class="fa fa-download"></i> Descargar
                </button>
            </div>
        </div>

        {{-- Por agente --}}
        <div class="report-card">
            <div class="rc-body">
                <div class="rc-icon" style="background:rgba(168,85,247,.13); color:#a855f7;">
                    <i class="fa fa-users"></i>
                </div>
                <h6>Productividad por Agente</h6>
                <p>Leads asignados, conversiones, tasa de cierre y listado detallado por vendedor.</p>
            </div>
            <div class="rc-footer">
                <button class="btn-pdf" style="background:#ede9fe; color:#7c3aed;"
                    onclick="openReport('{{ route('admin.crm.reports.by-agent') }}', false)">
                    <i class="fa fa-eye"></i> Ver PDF
                </button>
                <button class="btn-pdf" style="background:#a855f7; color:#fff;"
                    onclick="openReport('{{ route('admin.crm.reports.by-agent') }}', true)">
                    <i class="fa fa-download"></i> Descargar
                </button>
            </div>
        </div>

        {{-- Recordatorios vencidos --}}
        <div class="report-card">
            <div class="rc-body">
                <div class="rc-icon" style="background:rgba(249,115,22,.13); color:#f97316;">
                    <i class="fa fa-bell"></i>
                </div>
                <h6>Recordatorios Vencidos</h6>
                <p>Recordatorios pendientes y vencidos del equipo. Identifica alertas sin atender.</p>
            </div>
            <div class="rc-footer">
                <button class="btn-pdf" style="background:#ffedd5; color:#c2410c;"
                    onclick="openReport('{{ route('admin.crm.reports.overdue-reminders') }}', false)">
                    <i class="fa fa-eye"></i> Ver PDF
                </button>
                <button class="btn-pdf" style="background:#f97316; color:#fff;"
                    onclick="openReport('{{ route('admin.crm.reports.overdue-reminders') }}', true)">
                    <i class="fa fa-download"></i> Descargar
                </button>
            </div>
        </div>

    </div>{{-- /reports-grid --}}

</div>

@endsection

@push('script')
<script>
(function () {
    const SEARCH_URL = '{{ route('admin.crm.leads.quick-search') }}';

    function globalFilters(extra) {
        const form   = document.getElementById('report-filters');
        const params = new URLSearchParams();
        new FormData(form).forEach((v, k) => { if (v) params.set(k, v); });
        if (extra) Object.entries(extra).forEach(([k, v]) => { if (v !== '' && v != null) params.set(k, v); });
        return params;
    }

    window.openReport = function (baseUrl, download, extra) {
        const params = globalFilters(extra || {});
        if (download) params.set('download', '1');
        window.open(baseUrl + '?' + params.toString(), '_blank');
    };

    window.openLeadReport = function (download) {
        const leadId = document.getElementById('selected-lead-id').value;
        if (!leadId) { alert('Selecciona un lead primero.'); return; }
        const params = new URLSearchParams();
        if (download) params.set('download', '1');
        window.open('{{ url('admin/crm/reports/lead') }}/' + leadId + '?' + params.toString(), '_blank');
    };

    /* Lead autocomplete */
    const input    = document.getElementById('lead-search-input');
    const dropdown = document.getElementById('lead-search-dropdown');
    let timer = null;

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { dropdown.style.display = 'none'; return; }
        timer = setTimeout(() => {
            fetch(SEARCH_URL + '?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(results => {
                if (!results.length) {
                    dropdown.innerHTML = '<div class="lsd-item" style="color:#aaa;">Sin resultados</div>';
                } else {
                    dropdown.innerHTML = results.map(l =>
                        `<div class="lsd-item lead-opt" data-id="${l.id}" data-name="${esc(l.name)}">
                            <div>
                                <strong>${esc(l.name)}</strong>
                                <div style="font-size:11px;color:#aaa;">${esc(l.phone || l.email || '')}</div>
                            </div>
                            <span class="crm-badge ${esc(l.status_key || '')}" style="font-size:10px;">${esc(l.status)}</span>
                        </div>`
                    ).join('');
                    dropdown.querySelectorAll('.lead-opt').forEach(el => {
                        el.addEventListener('click', () => {
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
        if (!input.contains(e.target) && !dropdown.contains(e.target)) dropdown.style.display = 'none';
    });

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>
@endpush
