@extends('admin.main')
@section('title', 'CRM - Leads')
@section('content')

@if (Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <strong>{{ Session::get('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
    </div>
@endif

<div class="container-fluid">

    {{-- ── Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fa fa-users"></i> Gestión de Leads</h4>
        <div class="d-flex" style="gap:8px;">
            <a href="{{ route('admin.crm.leads.follow-ups') }}" class="btn btn-warning btn-sm">
                <i class="fa fa-clock-o"></i> Seguimientos
                @if($stats['needs_follow_up'] > 0)
                    <span class="badge badge-light ml-1">{{ $stats['needs_follow_up'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.crm.leads.pipeline') }}" class="btn btn-dark btn-sm">
                <i class="fa fa-columns"></i> Pipeline
            </a>
            @if(Auth::user()->canAccessModule('crm', 'view'))
            <a href="{{ route('admin.crm.leads.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Nuevo Lead
            </a>
            @endif
        </div>
    </div>

    {{-- ── Stats Cards ── --}}
    <div class="row mb-3" id="stats-row">
        <div class="col-6 col-sm-4 col-lg-2 mb-2">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="card-body p-2">
                    <h4 class="mb-0 text-primary font-weight-bold" id="stat-total">{{ $stats['total'] }}</h4>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2 mb-2">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="card-body p-2">
                    <h4 class="mb-0 text-info font-weight-bold" id="stat-new">{{ $stats['new'] }}</h4>
                    <small class="text-muted">Nuevos</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2 mb-2">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="card-body p-2">
                    <h4 class="mb-0 text-warning font-weight-bold" id="stat-active">{{ $stats['active'] }}</h4>
                    <small class="text-muted">Activos</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2 mb-2">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="card-body p-2">
                    <h4 class="mb-0 text-success font-weight-bold" id="stat-won">{{ $stats['won'] }}</h4>
                    <small class="text-muted">Ganados</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2 mb-2">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="card-body p-2">
                    <h4 class="mb-0 text-danger font-weight-bold" id="stat-lost">{{ $stats['lost'] }}</h4>
                    <small class="text-muted">Perdidos</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2 mb-2">
            <a href="{{ route('admin.crm.leads.index', ['origin' => 'event']) }}"
               class="card border-0 shadow-sm text-center py-2 text-decoration-none">
                <div class="card-body p-2">
                    <h4 class="mb-0 text-secondary font-weight-bold">{{ $stats['from_events'] ?? 0 }}</h4>
                    <small class="text-muted"><i class="fa fa-calendar"></i> Eventos</small>
                </div>
            </a>
        </div>
    </div>

    {{-- ── Filtro de origen (tabs) ── --}}
    <div class="mb-3">
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-{{ !request('origin') ? 'primary' : 'outline-primary' }} origin-btn" data-origin="">
                <i class="fa fa-list"></i> Todos
            </button>
            <button type="button" class="btn btn-{{ request('origin') == 'event' ? 'warning' : 'outline-warning' }} origin-btn" data-origin="event">
                <i class="fa fa-calendar"></i> Eventos
            </button>
            <button type="button" class="btn btn-{{ request('origin') == 'agency' ? 'info' : 'outline-info' }} origin-btn" data-origin="agency">
                <i class="fa fa-building"></i> Agencia
            </button>
        </div>
    </div>

    {{-- ── Filtros en tiempo real ── --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <div class="row align-items-center" style="gap:0;">
                <div class="col-md-3 col-sm-6 mb-2 mb-md-0 pr-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="filter-search" class="form-control form-control-sm border-left-0"
                               placeholder="Nombre, email, teléfono..."
                               value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0 px-1">
                    <select id="filter-status" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        @foreach(\App\Lead::getStatuses() as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0 px-1">
                    <select id="filter-source" class="form-control form-control-sm">
                        <option value="">Todas las fuentes</option>
                        @foreach(\App\Lead::getSources() as $key => $label)
                            <option value="{{ $key }}" {{ request('source') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0 px-1">
                    <select id="filter-priority" class="form-control form-control-sm">
                        <option value="">Todas las prioridades</option>
                        @foreach(\App\Lead::getPriorities() as $key => $label)
                            <option value="{{ $key }}" {{ request('priority') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0 px-1">
                    <select id="filter-user" class="form-control form-control-sm">
                        <option value="">Todos los agentes</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ request('user_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 col-sm-6 mb-2 mb-md-0 pl-1 text-right">
                    <button id="btn-clear-filters" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabla de Leads ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div id="leads-loading" class="text-center py-3 d-none">
                <i class="fa fa-spinner fa-spin text-muted"></i> Filtrando...
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:0.88rem;">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 pl-3">Lead</th>
                            <th class="border-0">Contacto</th>
                            <th class="border-0">Estado</th>
                            <th class="border-0">Fuente</th>
                            <th class="border-0">Prioridad</th>
                            <th class="border-0">Agente</th>
                            <th class="border-0">Seguimiento</th>
                            <th class="border-0">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="leads-tbody">
                        @include('admin.crm.leads._table')
                    </tbody>
                </table>
            </div>
            <div id="leads-pagination" class="px-3 py-2">
                {{ $leads->links() }}
            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: Cambiar Estado Rápido
═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-quick-status" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white py-3">
                <h6 class="modal-title mb-0"><i class="fa fa-exchange"></i> Cambiar Estado — <span id="qs-lead-name"></span></h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="qs-lead-id">
                <input type="hidden" id="qs-url">
                <div class="form-group mb-3">
                    <label class="small text-muted mb-1">Nuevo estado</label>
                    <select id="qs-status" class="form-control">
                        @foreach(\App\Lead::getStatuses() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="small text-muted mb-1">Nota (opcional)</label>
                    <textarea id="qs-note" class="form-control" rows="2" placeholder="Razón del cambio..."></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info btn-sm" id="btn-save-status">
                    <i class="fa fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: Registrar Actividad Rápida
═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-quick-activity" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-3">
                <h6 class="modal-title mb-0"><i class="fa fa-plus-circle"></i> Registrar Actividad — <span id="qa-lead-name"></span></h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="qa-url">
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="small text-muted mb-1">Tipo de actividad</label>
                        <select id="qa-type" class="form-control form-control-sm">
                            @foreach(\App\LeadActivity::getTypes() as $key => $label)
                                @if($key !== 'status_change')
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted mb-1">Próximo seguimiento</label>
                        <input type="date" id="qa-follow-up" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="small text-muted mb-1">Asunto</label>
                    <input type="text" id="qa-subject" class="form-control form-control-sm" placeholder="Ej: Llamada de seguimiento">
                </div>
                <div class="form-group mb-0">
                    <label class="small text-muted mb-1">Descripción</label>
                    <textarea id="qa-description" class="form-control form-control-sm" rows="3" placeholder="Notas de la actividad..."></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btn-save-activity">
                    <i class="fa fa-save"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: Agregar Recordatorio Rápido
═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-quick-reminder" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark py-3">
                <h6 class="modal-title mb-0"><i class="fa fa-bell"></i> Recordatorio — <span id="qr-lead-name"></span></h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="qr-url">
                <div class="form-group mb-3">
                    <label class="small text-muted mb-1">Título <span class="text-danger">*</span></label>
                    <input type="text" id="qr-title" class="form-control form-control-sm" placeholder="Ej: Llamar al cliente mañana">
                </div>
                <div class="row mb-3">
                    <div class="col-7">
                        <label class="small text-muted mb-1">Fecha y hora <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="qr-remind-at" class="form-control form-control-sm">
                    </div>
                    <div class="col-5">
                        <label class="small text-muted mb-1">Prioridad</label>
                        <select id="qr-priority" class="form-control form-control-sm">
                            <option value="low">Baja</option>
                            <option value="medium" selected>Media</option>
                            <option value="high">Alta</option>
                            <option value="urgent">Urgente</option>
                        </select>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label class="small text-muted mb-1">Notas</label>
                    <textarea id="qr-description" class="form-control form-control-sm" rows="2" placeholder="Opcional..."></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning btn-sm" id="btn-save-reminder">
                    <i class="fa fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: Vista Rápida del Lead
═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-quick-view" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h6 class="modal-title mb-0"><i class="fa fa-user"></i> <span id="qv-lead-name"></span></h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr><td class="text-muted pl-3" style="width:40%">Estado</td><td id="qv-status"></td></tr>
                        <tr><td class="text-muted pl-3">Prioridad</td><td id="qv-priority"></td></tr>
                        <tr><td class="text-muted pl-3">Fuente</td><td id="qv-source"></td></tr>
                        <tr><td class="text-muted pl-3">Teléfono</td><td id="qv-phone"></td></tr>
                        <tr><td class="text-muted pl-3">Email</td><td id="qv-email"></td></tr>
                        <tr><td class="text-muted pl-3">WhatsApp</td><td id="qv-whatsapp"></td></tr>
                        <tr><td class="text-muted pl-3">Agente</td><td id="qv-agent"></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer py-2">
                <a href="#" id="qv-edit-link" class="btn btn-outline-primary btn-sm"><i class="fa fa-edit"></i> Editar</a>
                <a href="#" id="qv-show-link" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> Ver detalle</a>
            </div>
        </div>
    </div>
</div>

{{-- ── Toast de confirmación ── --}}
<div id="crm-toast" class="alert alert-success shadow-sm"
     style="position:fixed;bottom:24px;right:24px;z-index:9999;display:none;min-width:260px;max-width:360px;">
    <i class="fa fa-check-circle"></i> <span id="crm-toast-msg"></span>
</div>

@endsection

@push('script')
<script>
(function () {
    'use strict';

    /* ── Helpers ── */
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function showToast(msg, type = 'success') {
        const el = document.getElementById('crm-toast');
        el.className = `alert alert-${type} shadow-sm`;
        document.getElementById('crm-toast-msg').textContent = msg;
        el.style.display = 'block';
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

    /* ── Estado de filtros ── */
    let currentOrigin = '{{ request('origin', '') }}';
    let debounceTimer = null;

    function getFilters(page) {
        const params = new URLSearchParams();
        const search   = document.getElementById('filter-search').value.trim();
        const status   = document.getElementById('filter-status').value;
        const source   = document.getElementById('filter-source').value;
        const priority = document.getElementById('filter-priority').value;
        const user     = document.getElementById('filter-user').value;

        if (search)   params.set('search', search);
        if (status)   params.set('status', status);
        if (source)   params.set('source', source);
        if (priority) params.set('priority', priority);
        if (user)     params.set('user_id', user);
        if (currentOrigin) params.set('origin', currentOrigin);
        if (page && page > 1) params.set('page', page);

        return params;
    }

    function fetchLeads(page) {
        const params = getFilters(page);
        params.set('ajax', '1');

        document.getElementById('leads-loading').classList.remove('d-none');

        fetch('{{ route('admin.crm.leads.index') }}?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('leads-tbody').innerHTML       = data.html;
            document.getElementById('leads-pagination').innerHTML  = data.pagination;
            updateStats(data.stats);
            bindRowActions();
            bindPaginationLinks();
            history.replaceState(null, '', '?' + getFilters(page).toString());
        })
        .catch(() => showToast('Error al cargar leads', 'danger'))
        .finally(() => document.getElementById('leads-loading').classList.add('d-none'));
    }

    function updateStats(stats) {
        if (!stats) return;
        const map = { total:'stat-total', new:'stat-new', active:'stat-active', won:'stat-won', lost:'stat-lost' };
        Object.entries(map).forEach(([k, id]) => {
            const el = document.getElementById(id);
            if (el && stats[k] !== undefined) el.textContent = stats[k];
        });
    }

    /* ── Filtros en tiempo real ── */
    function attachFilterListeners() {
        const searchInput = document.getElementById('filter-search');
        const selects     = ['filter-status', 'filter-source', 'filter-priority', 'filter-user']
                               .map(id => document.getElementById(id));

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchLeads(1), 380);
        });

        selects.forEach(sel => sel.addEventListener('change', () => fetchLeads(1)));

        document.getElementById('btn-clear-filters').addEventListener('click', () => {
            searchInput.value = '';
            selects.forEach(s => s.value = '');
            currentOrigin = '';
            document.querySelectorAll('.origin-btn').forEach(b => {
                b.className = b.className.replace(/btn-(primary|warning|info)\b/, 'btn-outline-$1');
            });
            fetchLeads(1);
        });
    }

    /* ── Botones de origen ── */
    function attachOriginButtons() {
        document.querySelectorAll('.origin-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                currentOrigin = btn.dataset.origin;
                document.querySelectorAll('.origin-btn').forEach(b => {
                    const color = b.dataset.origin === 'event' ? 'warning' : b.dataset.origin === 'agency' ? 'info' : 'primary';
                    if (b === btn) {
                        b.className = b.className.replace(/btn-outline-\w+/, `btn-${color}`);
                    } else {
                        const c2 = b.dataset.origin === 'event' ? 'warning' : b.dataset.origin === 'agency' ? 'info' : 'primary';
                        b.className = b.className.replace(/btn-\w+(?!\-outline)/, `btn-outline-${c2}`).replace(`btn-${c2}`, `btn-outline-${c2}`);
                    }
                });
                fetchLeads(1);
            });
        });
    }

    /* ── Paginación AJAX ── */
    function bindPaginationLinks() {
        document.querySelectorAll('#leads-pagination a[href]').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const url = new URL(link.href);
                const page = url.searchParams.get('page') ?? 1;
                fetchLeads(page);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }

    /* ── Acciones rápidas por fila ── */
    function bindRowActions() {
        document.querySelectorAll('.btn-quick-status').forEach(btn => {
            btn.addEventListener('click', () => openQuickStatus(btn.closest('tr')));
        });
        document.querySelectorAll('.btn-quick-activity').forEach(btn => {
            btn.addEventListener('click', () => openQuickActivity(btn.closest('tr')));
        });
        document.querySelectorAll('.btn-quick-reminder').forEach(btn => {
            btn.addEventListener('click', () => openQuickReminder(btn.closest('tr')));
        });
        document.querySelectorAll('.btn-quick-view').forEach(btn => {
            btn.addEventListener('click', () => openQuickView(btn.closest('tr')));
        });
    }

    /* ── Modal: Cambiar Estado ── */
    function openQuickStatus(row) {
        document.getElementById('qs-lead-name').textContent = row.dataset.leadName;
        document.getElementById('qs-lead-id').value         = row.dataset.leadId;
        document.getElementById('qs-url').value             = row.dataset.quickStatusUrl;
        document.getElementById('qs-status').value          = row.dataset.leadStatus;
        document.getElementById('qs-note').value            = '';
        $('#modal-quick-status').modal('show');
    }

    document.getElementById('btn-save-status').addEventListener('click', () => {
        const url    = document.getElementById('qs-url').value;
        const status = document.getElementById('qs-status').value;
        const note   = document.getElementById('qs-note').value;
        const leadId = document.getElementById('qs-lead-id').value;

        postJson(url, { status, note })
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById(`status-badge-${leadId}`);
                    if (badge) {
                        badge.textContent = data.status_label;
                        badge.className   = `badge badge-${data.status_color} lead-status-badge`;
                    }
                    const row = document.querySelector(`tr[data-lead-id="${leadId}"]`);
                    if (row) {
                        row.dataset.leadStatus      = data.status;
                        row.dataset.leadStatusLabel = data.status_label;
                        row.dataset.leadStatusColor = data.status_color;
                    }
                    $('#modal-quick-status').modal('hide');
                    showToast(data.message);
                } else {
                    showToast(data.message ?? 'Error al actualizar', 'danger');
                }
            })
            .catch(() => showToast('Error de conexión', 'danger'));
    });

    /* ── Modal: Actividad rápida ── */
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
        const url         = document.getElementById('qa-url').value;
        const type        = document.getElementById('qa-type').value;
        const subject     = document.getElementById('qa-subject').value;
        const description = document.getElementById('qa-description').value;
        const followUp    = document.getElementById('qa-follow-up').value;

        if (!type) { showToast('Selecciona un tipo de actividad', 'warning'); return; }

        postJson(url, { type, subject, description, next_follow_up: followUp || null })
            .then(data => {
                if (data.success) {
                    $('#modal-quick-activity').modal('hide');
                    showToast(data.message);
                    if (followUp) fetchLeads(1); // refrescar para ver nuevo seguimiento
                } else {
                    showToast(data.message ?? 'Error', 'danger');
                }
            })
            .catch(() => showToast('Error de conexión', 'danger'));
    });

    /* ── Modal: Recordatorio rápido ── */
    function openQuickReminder(row) {
        document.getElementById('qr-lead-name').textContent = row.dataset.leadName;
        document.getElementById('qr-url').value             = row.dataset.quickReminderUrl;
        document.getElementById('qr-title').value           = '';
        document.getElementById('qr-description').value     = '';
        document.getElementById('qr-priority').value        = 'medium';
        // Default: mañana a las 9am
        const d = new Date(); d.setDate(d.getDate() + 1); d.setHours(9, 0, 0, 0);
        document.getElementById('qr-remind-at').value = d.toISOString().slice(0, 16);
        $('#modal-quick-reminder').modal('show');
    }

    document.getElementById('btn-save-reminder').addEventListener('click', () => {
        const url         = document.getElementById('qr-url').value;
        const title       = document.getElementById('qr-title').value.trim();
        const remind_at   = document.getElementById('qr-remind-at').value;
        const priority    = document.getElementById('qr-priority').value;
        const description = document.getElementById('qr-description').value;

        if (!title)     { showToast('El título es obligatorio', 'warning'); return; }
        if (!remind_at) { showToast('Selecciona fecha y hora', 'warning'); return; }

        postJson(url, { title, remind_at, priority, description })
            .then(data => {
                if (data.success) {
                    $('#modal-quick-reminder').modal('hide');
                    showToast(data.message);
                } else {
                    showToast(data.message ?? 'Error', 'danger');
                }
            })
            .catch(() => showToast('Error de conexión', 'danger'));
    });

    /* ── Modal: Vista rápida ── */
    function openQuickView(row) {
        document.getElementById('qv-lead-name').textContent  = row.dataset.leadName;
        document.getElementById('qv-status').innerHTML       = `<span class="badge badge-${row.dataset.leadStatusColor}">${row.dataset.leadStatusLabel}</span>`;
        document.getElementById('qv-priority').textContent   = row.dataset.leadPriority;
        document.getElementById('qv-source').textContent     = row.dataset.leadSource;
        document.getElementById('qv-phone').textContent      = row.dataset.leadPhone || '—';
        document.getElementById('qv-email').textContent      = row.dataset.leadEmail || '—';
        document.getElementById('qv-whatsapp').textContent   = row.dataset.leadWhatsapp || '—';
        document.getElementById('qv-agent').textContent      = row.dataset.leadAgent || '—';
        document.getElementById('qv-show-link').href         = row.dataset.showUrl;
        document.getElementById('qv-edit-link').href         = row.dataset.editUrl;
        $('#modal-quick-view').modal('show');
    }

    /* ── Init ── */
    attachFilterListeners();
    attachOriginButtons();
    bindRowActions();
    bindPaginationLinks();

})();
</script>
@endpush
