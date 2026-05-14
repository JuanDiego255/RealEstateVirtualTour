@extends('admin.main')
@section('title', 'CRM - Leads')
@section('content')

@if (Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        <strong>{{ Session::get('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
    </div>
@endif

<style>
/* ── CRM Dashboard — basado en event-dashboard ── */
.crm-dashboard { padding: 20px; }

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 14px;
}
.dashboard-header h2 {
    font-size: 22px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}
.dashboard-header h2 i { color: #c2ac1f; }

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.07);
    position: relative;
    overflow: hidden;
    cursor: default;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 70px; height: 70px;
    background: linear-gradient(135deg, rgba(194,172,31,.1), transparent);
    border-radius: 0 0 0 100%;
}
.stat-card .sc-icon {
    width: 44px; height: 44px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 12px;
    font-size: 20px;
}
.sc-icon.blue   { background: rgba(59,130,246,.13); color: #3b82f6; }
.sc-icon.green  { background: rgba(34,197,94,.13);  color: #22c55e; }
.sc-icon.yellow { background: rgba(234,179,8,.13);  color: #eab308; }
.sc-icon.red    { background: rgba(239,68,68,.13);  color: #ef4444; }
.sc-icon.purple { background: rgba(168,85,247,.13); color: #a855f7; }
.sc-icon.orange { background: rgba(249,115,22,.13); color: #f97316; }
.sc-icon.gold   { background: rgba(194,172,31,.15); color: #c2ac1f; }
.stat-card .sc-value { font-size: 30px; font-weight: 700; color: #1a1a2e; line-height: 1; }
.stat-card .sc-label { font-size: 13px; color: #666; margin-top: 4px; }
.stat-card .sc-sub   { font-size: 11px; color: #999; margin-top: 8px; }

/* Layout principal */
.crm-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 22px;
    align-items: start;
}
@media (max-width: 1100px) { .crm-grid { grid-template-columns: 1fr; } }

/* Cards */
.dashboard-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.07);
    overflow: hidden;
}
.dashboard-card .dc-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.dashboard-card .dc-header h5 {
    font-size: 15px; font-weight: 600;
    display: flex; align-items: center; gap: 8px;
    margin: 0; color: #1a1a2e;
}
.dashboard-card .dc-header h5 i { color: #c2ac1f; }
.dashboard-card .dc-body { padding: 16px 20px; }

/* Filtros */
.crm-filters-bar {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.07);
    padding: 14px 18px;
    margin-bottom: 18px;
}
.crm-filters-bar input,
.crm-filters-bar select {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    font-size: 13px;
    height: 36px;
    padding: 0 10px;
    background: #fafafa;
    width: 100%;
    transition: border-color .15s;
}
.crm-filters-bar input:focus,
.crm-filters-bar select:focus {
    border-color: #c2ac1f;
    outline: none;
    background: #fff;
}

/* Origin tabs */
.origin-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
.origin-tab {
    padding: 6px 16px;
    border-radius: 20px;
    border: none;
    font-size: 13px; font-weight: 500;
    cursor: pointer;
    transition: all .15s;
}
.origin-tab.active-all    { background: #1a1a2e; color: #fff; }
.origin-tab.inactive-all  { background: #f0f0f0; color: #555; }
.origin-tab.active-event  { background: #c2ac1f; color: #000; }
.origin-tab.inactive-event{ background: #fef9e7; color: #b45309; }
.origin-tab.active-agency { background: #3b82f6; color: #fff; }
.origin-tab.inactive-agency{ background: #eff6ff; color: #1d4ed8; }

/* Tabla leads */
.leads-table { width: 100%; border-collapse: collapse; }
.leads-table th {
    text-align: left; padding: 11px 14px;
    font-size: 11px; text-transform: uppercase;
    letter-spacing: .4px; color: #888;
    border-bottom: 2px solid #f0f0f0;
    background: #fafafa;
    font-weight: 600;
}
.leads-table td {
    padding: 13px 14px;
    border-bottom: 1px solid #f5f5f5;
    font-size: 13.5px;
    vertical-align: middle;
}
.leads-table tr:hover td { background: #fffdf0; }
.leads-table tr.overdue-row td { background: #fff5f5; }

/* Status / priority pills */
.crm-badge {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.crm-badge.new          { background: #dbeafe; color: #1d4ed8; }
.crm-badge.contacted    { background: #e0e7ff; color: #4338ca; }
.crm-badge.qualified    { background: #ede9fe; color: #7c3aed; }
.crm-badge.proposal     { background: #fff3cd; color: #b45309; }
.crm-badge.negotiation  { background: #fef3c7; color: #92400e; }
.crm-badge.won          { background: #d1fae5; color: #065f46; }
.crm-badge.lost         { background: #fee2e2; color: #991b1b; }
.crm-badge.low          { background: #f1f5f9; color: #64748b; }
.crm-badge.medium       { background: #dbeafe; color: #1e40af; }
.crm-badge.high         { background: #fef3c7; color: #92400e; }
.crm-badge.urgent       { background: #fee2e2; color: #991b1b; }

/* Action buttons */
.action-btn {
    padding: 5px 9px;
    border: none;
    border-radius: 7px;
    cursor: pointer;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all .15s;
    text-decoration: none;
    font-weight: 500;
}
.action-btn.view      { background: #f1f5f9; color: #475569; }
.action-btn.view:hover{ background: #e2e8f0; }
.action-btn.status    { background: #dbeafe; color: #1d4ed8; }
.action-btn.status:hover{ background: #bfdbfe; }
.action-btn.activity  { background: #d1fae5; color: #065f46; }
.action-btn.activity:hover{ background: #a7f3d0; }
.action-btn.reminder  { background: #fef3c7; color: #92400e; }
.action-btn.reminder:hover{ background: #fde68a; }
.action-btn.more      { background: #f5f3ff; color: #6d28d9; }
.action-btn.more:hover{ background: #ede9fe; }

/* Follow-up date */
.followup-date { font-size: 12px; }
.followup-date.overdue { color: #dc2626; font-weight: 600; }
.followup-date.today   { color: #d97706; font-weight: 600; }
.followup-date.ok      { color: #6b7280; }

/* Sidebar pipeline */
.pipeline-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 0;
    border-bottom: 1px solid #f5f5f5;
}
.pipeline-item:last-child { border-bottom: none; }
.pipeline-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    margin-right: 10px;
    flex-shrink: 0;
}
.pipeline-label { font-size: 13px; color: #444; flex: 1; }
.pipeline-count {
    font-size: 13px; font-weight: 700;
    background: #f5f5f5;
    padding: 2px 9px;
    border-radius: 12px;
}

/* Sidebar links */
.sidebar-link {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    text-decoration: none;
    color: #333;
    font-size: 13px;
    font-weight: 500;
    transition: background .15s;
    margin-bottom: 4px;
}
.sidebar-link:hover { background: #f5f5f5; color: #1a1a2e; text-decoration: none; }
.sidebar-link i { width: 18px; text-align: center; }

/* Real-time indicator */
.rt-indicator { display: flex; align-items: center; gap: 7px; font-size: 12px; color: #22c55e; }
.rt-dot { width: 7px; height: 7px; background: #22c55e; border-radius: 50%; animation: pulse 1.5s infinite; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.3)} }

/* Toast */
#crm-toast {
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
    display: none; min-width: 260px; max-width: 360px;
    border-radius: 12px; padding: 12px 18px;
    box-shadow: 0 4px 20px rgba(0,0,0,.15);
}

/* Loading */
#leads-loading { text-align: center; padding: 10px; font-size: 13px; color: #888; }
</style>

<div class="crm-dashboard">

    {{-- ── Header ── --}}
    <div class="dashboard-header">
        <h2><i class="fa fa-users"></i> CRM — Gestión de Leads</h2>
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <div class="rt-indicator">
                <span class="rt-dot"></span> Tiempo real
            </div>
            <a href="{{ route('admin.crm.leads.follow-ups') }}" class="action-btn reminder">
                <i class="fa fa-clock-o"></i> Seguimientos
                @if($stats['needs_follow_up'] > 0)
                    <span style="background:#92400e;color:#fff;border-radius:10px;padding:1px 6px;font-size:10px;">{{ $stats['needs_follow_up'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.crm.leads.pipeline') }}" class="action-btn view">
                <i class="fa fa-columns"></i> Pipeline
            </a>
            <a href="{{ route('admin.crm.reports.index') }}" class="action-btn more">
                <i class="fa fa-file-pdf-o"></i> Reportes
            </a>
            @if(Auth::user()->canAccessModule('crm', 'view'))
            <a href="{{ route('admin.crm.leads.create') }}"
               style="background:linear-gradient(135deg,#c2ac1f,#a89617);color:#000;border-radius:10px;padding:7px 16px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                <i class="fa fa-plus"></i> Nuevo Lead
            </a>
            @endif
        </div>
    </div>

    {{-- ── Stats Grid ── --}}
    <div class="stats-grid" id="stats-row">
        <div class="stat-card">
            <div class="sc-icon blue"><i class="fa fa-users"></i></div>
            <div class="sc-value" id="stat-total">{{ $stats['total'] }}</div>
            <div class="sc-label">Total Leads</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon purple"><i class="fa fa-star"></i></div>
            <div class="sc-value" id="stat-new">{{ $stats['new'] }}</div>
            <div class="sc-label">Nuevos</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon yellow"><i class="fa fa-fire"></i></div>
            <div class="sc-value" id="stat-active">{{ $stats['active'] }}</div>
            <div class="sc-label">Activos</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon green"><i class="fa fa-trophy"></i></div>
            <div class="sc-value" id="stat-won">{{ $stats['won'] }}</div>
            <div class="sc-label">Ganados</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon red"><i class="fa fa-times-circle"></i></div>
            <div class="sc-value" id="stat-lost">{{ $stats['lost'] }}</div>
            <div class="sc-label">Perdidos</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon orange"><i class="fa fa-exclamation-triangle"></i></div>
            <div class="sc-value" id="stat-followup">{{ $stats['needs_follow_up'] }}</div>
            <div class="sc-label">Con seguimiento vencido</div>
        </div>
    </div>

    {{-- ── Filtro de origen ── --}}
    <div class="origin-tabs">
        <button type="button" class="origin-tab {{ !request('origin') ? 'active-all' : 'inactive-all' }} origin-btn" data-origin="">
            <i class="fa fa-list"></i> Todos
        </button>
        <button type="button" class="origin-tab {{ request('origin')=='event' ? 'active-event' : 'inactive-event' }} origin-btn" data-origin="event">
            <i class="fa fa-calendar"></i> Eventos
        </button>
        <button type="button" class="origin-tab {{ request('origin')=='agency' ? 'active-agency' : 'inactive-agency' }} origin-btn" data-origin="agency">
            <i class="fa fa-building"></i> Agencia
        </button>
    </div>

    {{-- ── Filtros en tiempo real ── --}}
    <div class="crm-filters-bar mb-3">
        <div class="row align-items-center" style="gap:0; margin:0;">
            <div class="col-md-3 col-sm-6 mb-2 mb-md-0 pr-2">
                <div style="position:relative;">
                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;"><i class="fa fa-search"></i></span>
                    <input type="text" id="filter-search" placeholder="Buscar nombre, email, teléfono..."
                           value="{{ request('search') }}" autocomplete="off"
                           style="padding-left:32px;">
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2 mb-md-0 px-2">
                <select id="filter-status">
                    <option value="">Todos los estados</option>
                    @foreach(\App\Lead::getStatuses() as $key => $label)
                        <option value="{{ $key }}" {{ request('status')==$key?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-6 mb-2 mb-md-0 px-2">
                <select id="filter-source">
                    <option value="">Todas las fuentes</option>
                    @foreach(\App\Lead::getSources() as $key => $label)
                        <option value="{{ $key }}" {{ request('source')==$key?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-6 mb-2 mb-md-0 px-2">
                <select id="filter-priority">
                    <option value="">Todas las prioridades</option>
                    @foreach(\App\Lead::getPriorities() as $key => $label)
                        <option value="{{ $key }}" {{ request('priority')==$key?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-6 mb-2 mb-md-0 px-2">
                <select id="filter-user">
                    <option value="">Todos los agentes</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ request('user_id')==$agent->id?'selected':'' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 col-sm-6 mb-2 mb-md-0 pl-2">
                <button id="btn-clear-filters" class="action-btn view" style="width:100%;justify-content:center;" title="Limpiar">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Grid principal ── --}}
    <div class="crm-grid">

        {{-- Tabla de Leads --}}
        <div class="dashboard-card">
            <div class="dc-header">
                <h5><i class="fa fa-list"></i> Leads</h5>
                <div id="leads-loading" class="d-none" style="font-size:12px;color:#888;">
                    <i class="fa fa-spinner fa-spin"></i> Cargando...
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="leads-table">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th>Prioridad</th>
                            <th>Agente</th>
                            <th>Seguimiento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="leads-tbody">
                        @include('admin.crm.leads._table')
                    </tbody>
                </table>
            </div>
            <div id="leads-pagination" style="padding:12px 18px; border-top:1px solid #f5f5f5;">
                {{ $leads->links() }}
            </div>
        </div>

        {{-- Sidebar --}}
        <div>
            {{-- Pipeline summary --}}
            <div class="dashboard-card" style="margin-bottom:18px;">
                <div class="dc-header">
                    <h5><i class="fa fa-columns"></i> Pipeline</h5>
                    <a href="{{ route('admin.crm.leads.pipeline') }}" style="font-size:12px;color:#c2ac1f;">Ver kanban</a>
                </div>
                <div class="dc-body" style="padding:12px 18px;">
                    @php
                        $pipelineColors = [
                            'new'         => '#3b82f6',
                            'contacted'   => '#6366f1',
                            'qualified'   => '#8b5cf6',
                            'proposal'    => '#f59e0b',
                            'negotiation' => '#eab308',
                            'won'         => '#22c55e',
                            'lost'        => '#ef4444',
                        ];
                    @endphp
                    @foreach(\App\Lead::getStatuses() as $key => $label)
                    <div class="pipeline-item">
                        <span class="pipeline-dot" style="background:{{ $pipelineColors[$key] ?? '#999' }};"></span>
                        <span class="pipeline-label">{{ $label }}</span>
                        <span class="pipeline-count">{{ $pipelineCounts->get($key, 0) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Accesos rápidos --}}
            <div class="dashboard-card" style="margin-bottom:18px;">
                <div class="dc-header">
                    <h5><i class="fa fa-bolt"></i> Acceso rápido</h5>
                </div>
                <div class="dc-body" style="padding:10px 12px;">
                    <a href="{{ route('admin.crm.leads.follow-ups') }}" class="sidebar-link">
                        <i class="fa fa-clock-o" style="color:#f97316;"></i> Seguimientos pendientes
                        @if($stats['needs_follow_up'] > 0)
                            <span style="background:#fee2e2;color:#991b1b;border-radius:10px;padding:1px 7px;font-size:11px;margin-left:auto;">{{ $stats['needs_follow_up'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.crm.appointments.today') }}" class="sidebar-link">
                        <i class="fa fa-calendar-check-o" style="color:#3b82f6;"></i> Citas de hoy
                    </a>
                    <a href="{{ route('admin.crm.reminders.index') }}" class="sidebar-link">
                        <i class="fa fa-bell" style="color:#eab308;"></i> Recordatorios
                    </a>
                    <a href="{{ route('admin.crm.appointments.index') }}" class="sidebar-link">
                        <i class="fa fa-calendar" style="color:#8b5cf6;"></i> Agenda completa
                    </a>
                    <a href="{{ route('admin.crm.reports.index') }}" class="sidebar-link">
                        <i class="fa fa-file-pdf-o" style="color:#ef4444;"></i> Reportería PDF
                    </a>
                    @if(Auth::user()->canAccessModule('crm', 'view'))
                    <a href="{{ route('admin.crm.leads.create') }}" class="sidebar-link">
                        <i class="fa fa-plus-circle" style="color:#22c55e;"></i> Nuevo lead
                    </a>
                    @endif
                </div>
            </div>

            {{-- Estadística de conversión --}}
            @php
                $convRate = $stats['total'] > 0 ? round(($stats['won'] / $stats['total']) * 100, 1) : 0;
            @endphp
            <div class="dashboard-card">
                <div class="dc-header">
                    <h5><i class="fa fa-bar-chart"></i> Conversión</h5>
                </div>
                <div class="dc-body" style="text-align:center; padding:20px;">
                    <div style="font-size:42px;font-weight:700;color:#c2ac1f;line-height:1;">{{ $convRate }}%</div>
                    <div style="font-size:13px;color:#888;margin-top:4px;">tasa de cierre</div>
                    <div style="margin-top:14px;background:#f5f5f5;border-radius:20px;height:8px;overflow:hidden;">
                        <div style="background:linear-gradient(90deg,#c2ac1f,#a89617);height:100%;width:{{ $convRate }}%;border-radius:20px;transition:width .6s;"></div>
                    </div>
                    <div style="font-size:12px;color:#aaa;margin-top:8px;">{{ $stats['won'] }} ganados de {{ $stats['total'] }} totales</div>
                </div>
            </div>
        </div>

    </div>{{-- /crm-grid --}}
</div>{{-- /crm-dashboard --}}

{{-- ═══════════════ MODALES ═══════════════ --}}

{{-- Modal: Cambiar Estado --}}
<div class="modal fade" id="modal-quick-status" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
            <div class="modal-header" style="background:#1a1a2e;color:#fff;padding:16px 20px;">
                <h6 class="modal-title mb-0"><i class="fa fa-exchange"></i> Cambiar Estado — <span id="qs-lead-name"></span></h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="qs-lead-id">
                <input type="hidden" id="qs-url">
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted mb-2 d-block">Nuevo estado</label>
                    <select id="qs-status" class="form-control" style="border-radius:8px;">
                        @foreach(\App\Lead::getStatuses() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="small font-weight-bold text-muted mb-2 d-block">Nota (opcional)</label>
                    <textarea id="qs-note" class="form-control" rows="2" placeholder="Razón del cambio..." style="border-radius:8px;resize:none;"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border:none;padding:12px 20px;">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm" id="btn-save-status"
                    style="background:#1a1a2e;color:#fff;border-radius:8px;">
                    <i class="fa fa-save"></i> Guardar cambio
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Registrar Actividad --}}
<div class="modal fade" id="modal-quick-activity" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;padding:16px 20px;">
                <h6 class="modal-title mb-0"><i class="fa fa-plus-circle"></i> Registrar Actividad — <span id="qa-lead-name"></span></h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="qa-url">
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="small font-weight-bold text-muted mb-2 d-block">Tipo</label>
                        <select id="qa-type" class="form-control form-control-sm" style="border-radius:8px;">
                            @foreach(\App\LeadActivity::getTypes() as $key => $label)
                                @if($key !== 'status_change')
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="small font-weight-bold text-muted mb-2 d-block">Próx. seguimiento</label>
                        <input type="date" id="qa-follow-up" class="form-control form-control-sm" style="border-radius:8px;">
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted mb-2 d-block">Asunto</label>
                    <input type="text" id="qa-subject" class="form-control form-control-sm" placeholder="Ej: Llamada de seguimiento" style="border-radius:8px;">
                </div>
                <div class="form-group mb-0">
                    <label class="small font-weight-bold text-muted mb-2 d-block">Descripción</label>
                    <textarea id="qa-description" class="form-control form-control-sm" rows="3" placeholder="Notas de la actividad..." style="border-radius:8px;resize:none;"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border:none;padding:12px 20px;">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm" id="btn-save-activity"
                    style="background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;border-radius:8px;">
                    <i class="fa fa-save"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Recordatorio --}}
<div class="modal fade" id="modal-quick-reminder" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#c2ac1f,#a89617);color:#000;padding:16px 20px;">
                <h6 class="modal-title mb-0"><i class="fa fa-bell"></i> Recordatorio — <span id="qr-lead-name"></span></h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="qr-url">
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted mb-2 d-block">Título <span class="text-danger">*</span></label>
                    <input type="text" id="qr-title" class="form-control form-control-sm" placeholder="Ej: Llamar mañana" style="border-radius:8px;">
                </div>
                <div class="row mb-3">
                    <div class="col-7">
                        <label class="small font-weight-bold text-muted mb-2 d-block">Fecha y hora <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="qr-remind-at" class="form-control form-control-sm" style="border-radius:8px;">
                    </div>
                    <div class="col-5">
                        <label class="small font-weight-bold text-muted mb-2 d-block">Prioridad</label>
                        <select id="qr-priority" class="form-control form-control-sm" style="border-radius:8px;">
                            <option value="low">Baja</option>
                            <option value="medium" selected>Media</option>
                            <option value="high">Alta</option>
                            <option value="urgent">Urgente</option>
                        </select>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label class="small font-weight-bold text-muted mb-2 d-block">Notas</label>
                    <textarea id="qr-description" class="form-control form-control-sm" rows="2" style="border-radius:8px;resize:none;"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border:none;padding:12px 20px;">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm" id="btn-save-reminder"
                    style="background:linear-gradient(135deg,#c2ac1f,#a89617);color:#000;border-radius:8px;font-weight:600;">
                    <i class="fa fa-bell"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Vista rápida --}}
<div class="modal fade" id="modal-quick-view" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
            <div class="modal-header" style="padding:16px 20px;border-bottom:1px solid #f0f0f0;">
                <h6 class="modal-title mb-0"><i class="fa fa-user" style="color:#c2ac1f;"></i> <span id="qv-lead-name"></span></h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
                    <tr><td style="padding:10px 18px;color:#888;width:38%;border-bottom:1px solid #f5f5f5;">Estado</td><td style="padding:10px 18px;border-bottom:1px solid #f5f5f5;" id="qv-status"></td></tr>
                    <tr><td style="padding:10px 18px;color:#888;border-bottom:1px solid #f5f5f5;">Prioridad</td><td style="padding:10px 18px;border-bottom:1px solid #f5f5f5;" id="qv-priority"></td></tr>
                    <tr><td style="padding:10px 18px;color:#888;border-bottom:1px solid #f5f5f5;">Fuente</td><td style="padding:10px 18px;border-bottom:1px solid #f5f5f5;" id="qv-source"></td></tr>
                    <tr><td style="padding:10px 18px;color:#888;border-bottom:1px solid #f5f5f5;">Teléfono</td><td style="padding:10px 18px;border-bottom:1px solid #f5f5f5;" id="qv-phone"></td></tr>
                    <tr><td style="padding:10px 18px;color:#888;border-bottom:1px solid #f5f5f5;">Email</td><td style="padding:10px 18px;border-bottom:1px solid #f5f5f5;" id="qv-email"></td></tr>
                    <tr><td style="padding:10px 18px;color:#888;border-bottom:1px solid #f5f5f5;">WhatsApp</td><td style="padding:10px 18px;border-bottom:1px solid #f5f5f5;" id="qv-whatsapp"></td></tr>
                    <tr><td style="padding:10px 18px;color:#888;">Agente</td><td style="padding:10px 18px;" id="qv-agent"></td></tr>
                </table>
            </div>
            <div class="modal-footer" style="border:none;padding:12px 20px;gap:8px;">
                <a href="#" id="qv-edit-link" class="action-btn view"><i class="fa fa-edit"></i> Editar</a>
                <a href="#" id="qv-show-link"
                   style="background:linear-gradient(135deg,#c2ac1f,#a89617);color:#000;border-radius:8px;padding:7px 14px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                    <i class="fa fa-eye"></i> Ver detalle
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="crm-toast" class="alert alert-success" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:none;min-width:260px;max-width:360px;border-radius:12px;padding:12px 18px;box-shadow:0 4px 20px rgba(0,0,0,.15);border:none;">
    <i class="fa fa-check-circle"></i> <span id="crm-toast-msg"></span>
</div>

@endsection

@push('script')
<script>
(function () {
    'use strict';
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function showToast(msg, type = 'success') {
        const el  = document.getElementById('crm-toast');
        el.className = `alert alert-${type}`;
        el.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;min-width:260px;max-width:360px;border-radius:12px;padding:12px 18px;box-shadow:0 4px 20px rgba(0,0,0,.15);border:none;display:block;';
        document.getElementById('crm-toast-msg').textContent = msg;
        clearTimeout(el._t);
        el._t = setTimeout(() => { el.style.display = 'none'; }, 3500);
    }

    function postJson(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data),
        }).then(r => r.json());
    }

    /* ── Filtros ── */
    let currentOrigin = '{{ request('origin', '') }}';
    let debounceTimer = null;

    function getFilters(page) {
        const p = new URLSearchParams();
        const v = id => document.getElementById(id)?.value ?? '';
        if (v('filter-search'))   p.set('search',   v('filter-search'));
        if (v('filter-status'))   p.set('status',   v('filter-status'));
        if (v('filter-source'))   p.set('source',   v('filter-source'));
        if (v('filter-priority')) p.set('priority', v('filter-priority'));
        if (v('filter-user'))     p.set('user_id',  v('filter-user'));
        if (currentOrigin)        p.set('origin',   currentOrigin);
        if (page && page > 1)     p.set('page',     page);
        return p;
    }

    function fetchLeads(page) {
        const params = getFilters(page);
        params.set('ajax', '1');
        document.getElementById('leads-loading').classList.remove('d-none');
        fetch('{{ route('admin.crm.leads.index') }}?' + params, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                document.getElementById('leads-tbody').innerHTML = data.html;
                document.getElementById('leads-pagination').innerHTML = data.pagination;
                updateStats(data.stats);
                bindRowActions();
                bindPaginationLinks();
                history.replaceState(null, '', '?' + getFilters(page).toString());
            })
            .catch(() => showToast('Error al cargar leads', 'danger'))
            .finally(() => document.getElementById('leads-loading').classList.add('d-none'));
    }

    function updateStats(s) {
        if (!s) return;
        const map = { total:'stat-total', new:'stat-new', active:'stat-active', won:'stat-won', lost:'stat-lost', needs_follow_up:'stat-followup' };
        Object.entries(map).forEach(([k, id]) => { const el = document.getElementById(id); if (el && s[k] !== undefined) el.textContent = s[k]; });
    }

    function attachFilterListeners() {
        document.getElementById('filter-search').addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchLeads(1), 380);
        });
        ['filter-status','filter-source','filter-priority','filter-user'].forEach(id => {
            document.getElementById(id).addEventListener('change', () => fetchLeads(1));
        });
        document.getElementById('btn-clear-filters').addEventListener('click', () => {
            ['filter-search','filter-status','filter-source','filter-priority','filter-user'].forEach(id => {
                const el = document.getElementById(id);
                el.value = '';
            });
            currentOrigin = '';
            updateOriginTabs('');
            fetchLeads(1);
        });
    }

    function updateOriginTabs(origin) {
        document.querySelectorAll('.origin-btn').forEach(btn => {
            const o = btn.dataset.origin;
            btn.className = btn.className.replace(/active-\w+|inactive-\w+/g, '').trim();
            if (o === '') btn.classList.add(origin === '' ? 'active-all' : 'inactive-all');
            else if (o === 'event') btn.classList.add(origin === 'event' ? 'active-event' : 'inactive-event');
            else if (o === 'agency') btn.classList.add(origin === 'agency' ? 'active-agency' : 'inactive-agency');
        });
    }

    function attachOriginButtons() {
        document.querySelectorAll('.origin-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                currentOrigin = btn.dataset.origin;
                updateOriginTabs(currentOrigin);
                fetchLeads(1);
            });
        });
    }

    function bindPaginationLinks() {
        document.querySelectorAll('#leads-pagination a[href]').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const page = new URL(link.href).searchParams.get('page') ?? 1;
                fetchLeads(page);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }

    /* ── Acciones por fila ── */
    function bindRowActions() {
        document.querySelectorAll('.btn-quick-status').forEach(btn => btn.addEventListener('click', () => openQuickStatus(btn.closest('tr'))));
        document.querySelectorAll('.btn-quick-activity').forEach(btn => btn.addEventListener('click', () => openQuickActivity(btn.closest('tr'))));
        document.querySelectorAll('.btn-quick-reminder').forEach(btn => btn.addEventListener('click', () => openQuickReminder(btn.closest('tr'))));
        document.querySelectorAll('.btn-quick-view').forEach(btn => btn.addEventListener('click', () => openQuickView(btn.closest('tr'))));
    }

    /* ── Modal Estado ── */
    function openQuickStatus(row) {
        document.getElementById('qs-lead-name').textContent = row.dataset.leadName;
        document.getElementById('qs-lead-id').value         = row.dataset.leadId;
        document.getElementById('qs-url').value             = row.dataset.quickStatusUrl;
        document.getElementById('qs-status').value          = row.dataset.leadStatus;
        document.getElementById('qs-note').value            = '';
        $('#modal-quick-status').modal('show');
    }
    document.getElementById('btn-save-status').addEventListener('click', () => {
        const url = document.getElementById('qs-url').value;
        const status = document.getElementById('qs-status').value;
        const note   = document.getElementById('qs-note').value;
        const leadId = document.getElementById('qs-lead-id').value;
        postJson(url, { status, note }).then(data => {
            if (data.success) {
                const badge = document.getElementById(`status-badge-${leadId}`);
                if (badge) {
                    badge.textContent = data.status_label;
                    badge.className = `crm-badge ${data.status}`;
                }
                const row = document.querySelector(`tr[data-lead-id="${leadId}"]`);
                if (row) { row.dataset.leadStatus = data.status; row.dataset.leadStatusLabel = data.status_label; row.dataset.leadStatusColor = data.status_color; }
                $('#modal-quick-status').modal('hide');
                showToast(data.message);
            } else showToast(data.message ?? 'Error', 'danger');
        }).catch(() => showToast('Error de conexión', 'danger'));
    });

    /* ── Modal Actividad ── */
    function openQuickActivity(row) {
        document.getElementById('qa-lead-name').textContent = row.dataset.leadName;
        document.getElementById('qa-url').value             = row.dataset.quickActivityUrl;
        document.getElementById('qa-type').value            = 'note';
        document.getElementById('qa-subject').value         = '';
        document.getElementById('qa-description').value     = '';
        document.getElementById('qa-follow-up').value       = '';
        $('#modal-quick-activity').modal('show');
    }
    document.getElementById('btn-save-activity').addEventListener('click', () => {
        const url = document.getElementById('qa-url').value;
        const type = document.getElementById('qa-type').value;
        const subject = document.getElementById('qa-subject').value;
        const description = document.getElementById('qa-description').value;
        const followUp = document.getElementById('qa-follow-up').value;
        if (!type) { showToast('Selecciona tipo', 'warning'); return; }
        postJson(url, { type, subject, description, next_follow_up: followUp || null }).then(data => {
            if (data.success) {
                $('#modal-quick-activity').modal('hide');
                showToast(data.message);
                if (followUp) fetchLeads(1);
            } else showToast(data.message ?? 'Error', 'danger');
        }).catch(() => showToast('Error de conexión', 'danger'));
    });

    /* ── Modal Recordatorio ── */
    function openQuickReminder(row) {
        document.getElementById('qr-lead-name').textContent = row.dataset.leadName;
        document.getElementById('qr-url').value             = row.dataset.quickReminderUrl;
        document.getElementById('qr-title').value           = '';
        document.getElementById('qr-description').value     = '';
        document.getElementById('qr-priority').value        = 'medium';
        const d = new Date(); d.setDate(d.getDate() + 1); d.setHours(9, 0, 0, 0);
        document.getElementById('qr-remind-at').value = d.toISOString().slice(0, 16);
        $('#modal-quick-reminder').modal('show');
    }
    document.getElementById('btn-save-reminder').addEventListener('click', () => {
        const url = document.getElementById('qr-url').value;
        const title = document.getElementById('qr-title').value.trim();
        const remind_at = document.getElementById('qr-remind-at').value;
        const priority = document.getElementById('qr-priority').value;
        const description = document.getElementById('qr-description').value;
        if (!title)     { showToast('El título es obligatorio', 'warning'); return; }
        if (!remind_at) { showToast('Selecciona fecha y hora', 'warning'); return; }
        postJson(url, { title, remind_at, priority, description }).then(data => {
            if (data.success) { $('#modal-quick-reminder').modal('hide'); showToast(data.message); }
            else showToast(data.message ?? 'Error', 'danger');
        }).catch(() => showToast('Error de conexión', 'danger'));
    });

    /* ── Modal Vista rápida ── */
    function openQuickView(row) {
        document.getElementById('qv-lead-name').textContent = row.dataset.leadName;
        document.getElementById('qv-status').innerHTML      = `<span class="crm-badge ${row.dataset.leadStatus}">${row.dataset.leadStatusLabel}</span>`;
        document.getElementById('qv-priority').textContent  = row.dataset.leadPriority;
        document.getElementById('qv-source').textContent    = row.dataset.leadSource;
        document.getElementById('qv-phone').textContent     = row.dataset.leadPhone     || '—';
        document.getElementById('qv-email').textContent     = row.dataset.leadEmail     || '—';
        document.getElementById('qv-whatsapp').textContent  = row.dataset.leadWhatsapp  || '—';
        document.getElementById('qv-agent').textContent     = row.dataset.leadAgent     || '—';
        document.getElementById('qv-show-link').href        = row.dataset.showUrl;
        document.getElementById('qv-edit-link').href        = row.dataset.editUrl;
        $('#modal-quick-view').modal('show');
    }

    attachFilterListeners();
    attachOriginButtons();
    bindRowActions();
    bindPaginationLinks();
})();
</script>
@endpush
