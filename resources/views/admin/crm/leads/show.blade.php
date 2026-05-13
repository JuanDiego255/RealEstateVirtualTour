@extends('admin.main')
@section('title', 'Lead: ' . $lead->name)
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        {{-- ── Header con buscador rápido de leads ── --}}
        <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap" style="gap:12px;">
            <div>
                <h4 class="mb-0">
                    <i class="fa fa-user"></i> {{ $lead->name }}
                    <span class="badge badge-{{ $lead->status_color }} ml-2">{{ $lead->status_label }}</span>
                    <span class="badge badge-{{ $lead->priority_color }}">{{ $lead->priority_label }}</span>
                </h4>
                <small class="text-muted">Creado {{ $lead->created_at->diffForHumans() }} · Agente: {{ $lead->user->name ?? 'Sin asignar' }}</small>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                {{-- Buscador rápido de lead ─────────────────────── --}}
                <div class="position-relative" style="min-width:230px;">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0">
                                <i class="fa fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text" id="lead-switcher-input" class="form-control form-control-sm border-left-0"
                               placeholder="Ir a otro lead..." autocomplete="off" style="border-left:0;">
                    </div>
                    <div id="lead-switcher-dropdown"
                         class="list-group shadow-sm"
                         style="position:absolute;top:100%;left:0;right:0;z-index:1050;display:none;max-height:260px;overflow-y:auto;">
                    </div>
                </div>
                {{-- Acciones ──────────────────────────────────────── --}}
                <a href="{{ route('admin.crm.leads.edit', $lead) }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-edit"></i> Editar
                </a>
                <a href="{{ route('admin.crm.appointments.create', ['lead_id' => $lead->id]) }}" class="btn btn-success btn-sm">
                    <i class="fa fa-calendar-plus-o"></i> Nueva Cita
                </a>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.crm.reports.lead-detail', $lead) }}" target="_blank" class="btn btn-outline-danger">
                        <i class="fa fa-file-pdf-o"></i> PDF
                    </a>
                    <a href="{{ route('admin.crm.reports.lead-detail', $lead) }}?download=1" class="btn btn-outline-danger">
                        <i class="fa fa-download"></i>
                    </a>
                </div>
                <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="row">
            {{-- Columna izquierda: Info del lead --}}
            <div class="col-md-4">
                {{-- Info de contacto --}}
                <div class="card mb-4">
                    <div class="card-header"><strong><i class="fa fa-address-card"></i> Contacto</strong></div>
                    <div class="card-body">
                        @if($lead->email)
                            <p class="mb-2"><i class="fa fa-envelope text-muted"></i> <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></p>
                        @endif
                        @if($lead->phone)
                            <p class="mb-2"><i class="fa fa-phone text-muted"></i> <a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></p>
                        @endif
                        @if($lead->whatsapp)
                            <p class="mb-2"><i class="fa fa-whatsapp text-success"></i> <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp) }}" target="_blank">{{ $lead->whatsapp }}</a></p>
                        @endif
                    </div>
                </div>

                {{-- Detalles --}}
                <div class="card mb-4">
                    <div class="card-header"><strong><i class="fa fa-info-circle"></i> Detalles</strong></div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted">Fuente:</td><td>{{ $lead->source_label }}</td></tr>
                            <tr><td class="text-muted">Interés:</td><td>{{ $lead->interest_type_label }}</td></tr>
                            <tr><td class="text-muted">Presupuesto:</td><td>{{ $lead->budget_range }}</td></tr>
                            @if($lead->property)
                            <tr><td class="text-muted">Propiedad:</td><td><a href="#">{{ $lead->property->title }}</a></td></tr>
                            @endif
                            @if($lead->vehicle)
                            <tr><td class="text-muted">Vehículo:</td><td><a href="#">{{ $lead->vehicle->name }}</a></td></tr>
                            @endif
                            <tr><td class="text-muted">Primer contacto:</td><td>{{ $lead->first_contact_at ? $lead->first_contact_at->format('d/m/Y H:i') : '-' }}</td></tr>
                            <tr><td class="text-muted">Último contacto:</td><td>{{ $lead->last_contact_at ? $lead->last_contact_at->format('d/m/Y H:i') : '-' }}</td></tr>
                        </table>
                    </div>
                </div>

                {{-- Cambiar estado --}}
                <div class="card mb-4">
                    <div class="card-header"><strong><i class="fa fa-exchange"></i> Cambiar Estado</strong></div>
                    <div class="card-body">
                        <form action="{{ route('admin.crm.leads.update-status', $lead) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="form-group">
                                <select name="status" class="form-control">
                                    @foreach(\App\Lead::getStatuses() as $key => $label)
                                        <option value="{{ $key }}" {{ $lead->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea name="note" class="form-control" rows="2" placeholder="Nota (opcional)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Actualizar Estado</button>
                        </form>
                    </div>
                </div>

                {{-- Recordatorios pendientes --}}
                @if($lead->reminders->count() > 0)
                <div class="card mb-4">
                    <div class="card-header"><strong><i class="fa fa-bell"></i> Recordatorios</strong></div>
                    <div class="card-body">
                        @foreach($lead->reminders as $reminder)
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded {{ $reminder->isDue() ? 'bg-warning' : '' }}">
                                <div>
                                    <strong>{{ $reminder->title }}</strong>
                                    <br><small class="text-muted">{{ $reminder->remind_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <form action="{{ route('admin.crm.reminders.complete', $reminder) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Completar"><i class="fa fa-check"></i></button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Próximas citas --}}
                @if($lead->appointments->count() > 0)
                <div class="card mb-4">
                    <div class="card-header"><strong><i class="fa fa-calendar"></i> Próximas Citas</strong></div>
                    <div class="card-body">
                        @foreach($lead->appointments as $appointment)
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                <div>
                                    <strong>{{ $appointment->title }}</strong>
                                    <br><small class="text-muted">{{ $appointment->starts_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <span class="badge badge-{{ $appointment->status_color }}">{{ $appointment->status_label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Columna derecha: Actividades --}}
            <div class="col-md-8">
                {{-- Notas y requerimientos --}}
                @if($lead->requirements || $lead->notes)
                <div class="card mb-4">
                    <div class="card-body">
                        @if($lead->requirements)
                            <h6><i class="fa fa-list-ul"></i> Lo que busca:</h6>
                            <p>{{ $lead->requirements }}</p>
                        @endif
                        @if($lead->notes)
                            <h6><i class="fa fa-sticky-note"></i> Notas:</h6>
                            <p class="mb-0">{{ $lead->notes }}</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Registrar actividad --}}
                <div class="card mb-4">
                    <div class="card-header"><strong><i class="fa fa-plus-circle"></i> Registrar Actividad</strong></div>
                    <div class="card-body">
                        <form action="{{ route('admin.crm.leads.add-activity', $lead) }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select name="type" class="form-control" required>
                                            @foreach(\App\LeadActivity::getTypes() as $key => $label)
                                                @if($key !== 'status_change')
                                                <option value="{{ $key }}">{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <input type="text" name="subject" class="form-control" placeholder="Asunto (opcional)">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <textarea name="description" class="form-control" rows="2" placeholder="Descripción de la actividad..."></textarea>
                            </div>
                            <div class="row" id="call-fields" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <select name="call_result" class="form-control">
                                            <option value="">Resultado de llamada</option>
                                            @foreach(\App\LeadActivity::getCallResults() as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="number" name="call_duration" class="form-control" placeholder="Duración (segundos)" min="0">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Registrar</button>
                        </form>
                    </div>
                </div>

                {{-- Timeline de actividades --}}
                <div class="card">
                    <div class="card-header"><strong><i class="fa fa-history"></i> Historial de Actividades</strong></div>
                    <div class="card-body">
                        @forelse($lead->activities as $activity)
                            <div class="d-flex mb-3 pb-3 border-bottom">
                                <div class="mr-3">
                                    <span class="badge badge-{{ $activity->type_color }} p-2">
                                        <i class="{{ $activity->type_icon }}"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $activity->type_label }}</strong>
                                        <small class="text-muted">{{ $activity->activity_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    @if($activity->subject)
                                        <div><strong>{{ $activity->subject }}</strong></div>
                                    @endif
                                    @if($activity->description)
                                        <p class="mb-1">{{ $activity->description }}</p>
                                    @endif
                                    @if($activity->type === 'status_change')
                                        <small class="text-muted">
                                            {{ \App\Lead::getStatuses()[$activity->old_status] ?? $activity->old_status }}
                                            <i class="fa fa-arrow-right"></i>
                                            {{ \App\Lead::getStatuses()[$activity->new_status] ?? $activity->new_status }}
                                        </small>
                                    @endif
                                    @if($activity->call_result)
                                        <small class="text-muted d-block">Resultado: {{ $activity->call_result_label }} {{ $activity->call_duration_formatted ? '(' . $activity->call_duration_formatted . ')' : '' }}</small>
                                    @endif
                                    <small class="text-muted">Por: {{ $activity->user->name ?? 'Sistema' }}</small>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-4">No hay actividades registradas</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    /* ── Mostrar campos de llamada según tipo ── */
    document.querySelector('select[name="type"]').addEventListener('change', function() {
        document.getElementById('call-fields').style.display = this.value === 'call' ? 'flex' : 'none';
    });

    /* ── Buscador rápido de leads ── */
    (function () {
        const input    = document.getElementById('lead-switcher-input');
        const dropdown = document.getElementById('lead-switcher-dropdown');
        const SEARCH_URL = '{{ route('admin.crm.leads.quick-search') }}';
        let timer = null;

        input.addEventListener('input', function () {
            clearTimeout(timer);
            const q = this.value.trim();
            if (q.length < 2) { dropdown.style.display = 'none'; dropdown.innerHTML = ''; return; }
            timer = setTimeout(() => {
                fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(results => {
                    if (!results.length) {
                        dropdown.innerHTML = '<a class="list-group-item list-group-item-action disabled small text-muted py-2">Sin resultados</a>';
                    } else {
                        dropdown.innerHTML = results.map(lead => `
                            <a href="${lead.url}" class="list-group-item list-group-item-action py-2 px-3" style="font-size:0.85rem;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>${escHtml(lead.name)}</strong>
                                    <span class="badge badge-${lead.color} ml-2">${escHtml(lead.status)}</span>
                                </div>
                                <small class="text-muted">${escHtml(lead.phone || lead.email || '')}</small>
                            </a>
                        `).join('');
                    }
                    dropdown.style.display = 'block';
                });
            }, 320);
        });

        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        function escHtml(str) {
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
    })();
</script>
@endpush
