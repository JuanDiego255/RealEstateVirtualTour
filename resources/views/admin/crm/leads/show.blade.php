@extends('admin.main')
@section('title', 'Lead: ' . $lead->name)
@section('content')

    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <strong>{{ Session::get('success') }}</strong>
            <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
        </div>
    @endif

    <style>
        /* ── Lead Detail — basado en event-dashboard ── */
        .lead-detail-wrap {
            padding: 20px;
        }

        /* Header */
        .lead-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 26px;
            flex-wrap: wrap;
            gap: 14px;
        }

        .lead-detail-header h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            margin: 0 0 5px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .lead-detail-header h2 i {
            color: #c2ac1f;
        }

        .lead-detail-header .sub {
            font-size: 13px;
            color: #888;
        }

        /* Badges */
        .crm-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .crm-badge.new {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .crm-badge.contacted {
            background: #e0e7ff;
            color: #4338ca;
        }

        .crm-badge.qualified {
            background: #ede9fe;
            color: #7c3aed;
        }

        .crm-badge.proposal {
            background: #fff3cd;
            color: #b45309;
        }

        .crm-badge.negotiation {
            background: #fef3c7;
            color: #92400e;
        }

        .crm-badge.won {
            background: #d1fae5;
            color: #065f46;
        }

        .crm-badge.lost {
            background: #fee2e2;
            color: #991b1b;
        }

        .crm-badge.low {
            background: #f1f5f9;
            color: #64748b;
        }

        .crm-badge.medium {
            background: #dbeafe;
            color: #1e40af;
        }

        .crm-badge.high {
            background: #fef3c7;
            color: #92400e;
        }

        .crm-badge.urgent {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Action buttons header */
        .action-btn {
            padding: 7px 14px;
            border: none;
            border-radius: 9px;
            cursor: pointer;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .15s;
            text-decoration: none;
            font-weight: 500;
            line-height: 1;
        }

        .action-btn.primary {
            background: #1a1a2e;
            color: #fff;
        }

        .action-btn.primary:hover {
            background: #2d2d4e;
            color: #fff;
        }

        .action-btn.success {
            background: #d1fae5;
            color: #065f46;
        }

        .action-btn.success:hover {
            background: #a7f3d0;
            color: #065f46;
        }

        .action-btn.danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-btn.danger:hover {
            background: #fecaca;
            color: #991b1b;
        }

        .action-btn.secondary {
            background: #f1f5f9;
            color: #475569;
        }

        .action-btn.secondary:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .action-btn.gold {
            background: #fef9e7;
            color: #92400e;
            border: 1px solid #c2ac1f;
        }

        .action-btn.gold:hover {
            background: #c2ac1f;
            color: #fff;
        }

        /* Lead switcher */
        .lead-switcher-wrap {
            position: relative;
            min-width: 230px;
        }

        .lead-switcher-wrap input {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 7px 12px 7px 34px;
            font-size: 13px;
            background: #fafafa;
            outline: none;
            transition: border-color .15s;
        }

        .lead-switcher-wrap input:focus {
            border-color: #c2ac1f;
            background: #fff;
        }

        .lead-switcher-wrap .sw-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 13px;
            pointer-events: none;
        }

        #lead-switcher-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            z-index: 1050;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .13);
            display: none;
            max-height: 260px;
            overflow-y: auto;
        }

        .sw-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-bottom: 1px solid #f5f5f5;
            text-decoration: none;
            color: #1a1a2e;
            font-size: 13px;
            transition: background .1s;
        }

        .sw-item:last-child {
            border-bottom: none;
        }

        .sw-item:hover {
            background: #fffdf0;
        }

        .sw-item strong {
            font-weight: 600;
        }

        .sw-item small {
            color: #888;
            font-size: 11px;
        }

        .sw-no-result {
            padding: 14px;
            text-align: center;
            color: #aaa;
            font-size: 13px;
        }

        /* Layout */
        .detail-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 22px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Cards */
        .dashboard-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .07);
            overflow: hidden;
            margin-bottom: 18px;
        }

        .dashboard-card .dc-header {
            padding: 14px 18px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-card .dc-header h5 {
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            color: #1a1a2e;
        }

        .dashboard-card .dc-header h5 i {
            color: #c2ac1f;
        }

        .dashboard-card .dc-body {
            padding: 16px 18px;
        }

        /* Detail list */
        .detail-list {
            width: 100%;
            font-size: 13px;
            border-collapse: collapse;
        }

        .detail-list tr td {
            padding: 7px 0;
            vertical-align: top;
            border: none;
        }

        .detail-list tr td:first-child {
            color: #999;
            width: 42%;
            font-size: 12px;
            padding-right: 8px;
        }

        /* Status change form */
        .status-select {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 8px 12px;
            font-size: 13px;
            background: #fafafa;
            margin-bottom: 10px;
            outline: none;
        }

        .status-select:focus {
            border-color: #c2ac1f;
        }

        .status-textarea {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 8px 12px;
            font-size: 13px;
            resize: vertical;
            min-height: 62px;
            margin-bottom: 10px;
            outline: none;
        }

        .status-textarea:focus {
            border-color: #c2ac1f;
        }

        /* Reminder item */
        .reminder-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 12px;
            border-radius: 10px;
            border: 1px solid #f0f0f0;
            margin-bottom: 8px;
            transition: background .1s;
        }

        .reminder-item.due {
            background: #fff9e6;
            border-color: #fde68a;
        }

        .reminder-item .ri-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a2e;
        }

        .reminder-item .ri-date {
            font-size: 11px;
            color: #888;
        }

        /* Appointment item */
        .appt-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 12px;
            border-radius: 10px;
            border: 1px solid #f0f0f0;
            margin-bottom: 8px;
        }

        .appt-item .ai-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a2e;
        }

        .appt-item .ai-date {
            font-size: 11px;
            color: #888;
        }

        /* Activity form */
        .act-select,
        .act-input {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 8px 12px;
            font-size: 13px;
            background: #fafafa;
            outline: none;
        }

        .act-select:focus,
        .act-input:focus {
            border-color: #c2ac1f;
            background: #fff;
        }

        .act-textarea {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 8px 12px;
            font-size: 13px;
            resize: vertical;
            min-height: 70px;
            background: #fafafa;
            outline: none;
        }

        .act-textarea:focus {
            border-color: #c2ac1f;
            background: #fff;
        }

        /* Activity timeline */
        .activity-item {
            display: flex;
            gap: 14px;
            padding-bottom: 20px;
            position: relative;
        }

        .activity-item::before {
            content: '';
            position: absolute;
            left: 18px;
            top: 38px;
            bottom: 0;
            width: 2px;
            background: #f0f0f0;
        }

        .activity-item:last-child::before {
            display: none;
        }

        .act-icon-wrap {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .act-icon-wrap.call {
            background: #d1fae5;
            color: #065f46;
        }

        .act-icon-wrap.email {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .act-icon-wrap.meeting {
            background: #ede9fe;
            color: #7c3aed;
        }

        .act-icon-wrap.note {
            background: #fef3c7;
            color: #92400e;
        }

        .act-icon-wrap.task {
            background: #f1f5f9;
            color: #475569;
        }

        .act-icon-wrap.whatsapp {
            background: #d1fae5;
            color: #15803d;
        }

        .act-icon-wrap.visit {
            background: #fce7f3;
            color: #9d174d;
        }

        .act-icon-wrap.status_change {
            background: #e0e7ff;
            color: #4338ca;
        }

        .act-icon-wrap.other {
            background: #f1f5f9;
            color: #64748b;
        }

        .act-body {
            flex: 1;
        }

        .act-type {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a2e;
        }

        .act-time {
            font-size: 11px;
            color: #aaa;
            margin-left: 8px;
        }

        .act-subject {
            font-size: 13px;
            color: #333;
            font-weight: 500;
            margin-top: 2px;
        }

        .act-desc {
            font-size: 13px;
            color: #666;
            margin-top: 3px;
            line-height: 1.5;
        }

        .act-by {
            font-size: 11px;
            color: #bbb;
            margin-top: 4px;
        }
    </style>

    <div class="lead-detail-wrap">

        {{-- ── Header ── --}}
        <div class="lead-detail-header">
            <div>
                <h2>
                    <i class="fa fa-user-circle-o"></i>
                    {{ $lead->name }}
                    <span class="crm-badge {{ $lead->status }}">{{ $lead->status_label }}</span>
                    <span class="crm-badge {{ $lead->priority }}">{{ $lead->priority_label }}</span>
                </h2>
                <div class="sub">
                    Creado {{ $lead->created_at->diffForHumans() }}
                    &nbsp;·&nbsp; Agente: <strong>{{ $lead->user->name ?? 'Sin asignar' }}</strong>
                    @if ($lead->next_follow_up)
                        &nbsp;·&nbsp;
                        <span class="{{ $lead->isOverdueForFollowUp() ? 'text-danger font-weight-bold' : '' }}">
                            Próx. seguim.: {{ $lead->next_follow_up->format('d/m/Y') }}
                        </span>
                    @endif
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                {{-- Buscador rápido --}}
                <div class="lead-switcher-wrap">
                    <i class="fa fa-search sw-icon"></i>
                    <input type="text" id="lead-switcher-input" placeholder="Ir a otro lead..." autocomplete="off">
                    <div id="lead-switcher-dropdown"></div>
                </div>

                {{-- Score badge --}}
                @if ($lead->score > 0)
                    <div
                        style="display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:5px 12px;font-size:12px;font-weight:600;color:{{ $lead->score_color }};">
                        <i class="fa fa-fire"></i>
                        Score {{ $lead->score }}/100
                        <span style="font-weight:400;color:#aaa;">· {{ $lead->score_label }}</span>
                    </div>
                @endif

                {{-- Acciones --}}
                <a href="{{ route('admin.crm.leads.edit', $lead) }}?_back={{ urlencode(url()->current()) }}"
                    class="action-btn primary">
                    <i class="fa fa-edit"></i> Editar
                </a>
                <a href="{{ route('admin.crm.appointments.create', ['lead_id' => $lead->id]) }}?_back={{ urlencode(url()->current()) }}"
                    class="action-btn success">
                    <i class="fa fa-calendar-plus-o"></i> Nueva Cita
                </a>
                @if (isset($portalUrl))
                    <a href="{{ $portalUrl }}" target="_blank" class="action-btn"
                        style="background:#ede9fe;color:#6d28d9;" title="Portal del cliente">
                        <i class="fa fa-external-link"></i> Portal
                    </a>
                @else
                    <form action="{{ route('admin.crm.leads.generate-portal', $lead) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="action-btn" style="background:#ede9fe;color:#6d28d9;"
                            title="Generar portal del cliente">
                            <i class="fa fa-share-square-o"></i> Portal
                        </button>
                    </form>
                @endif
                <button class="action-btn" style="background:#dbeafe;color:#1d4ed8;" id="btn-send-email"
                    title="Enviar email">
                    <i class="fa fa-envelope"></i> Email
                </button>
                <a href="{{ route('admin.crm.reports.lead-detail', $lead) }}" target="_blank" class="action-btn gold">
                    <i class="fa fa-file-pdf-o"></i> PDF
                </a>
                <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary">
                    <i class="fa fa-arrow-left"></i> Volver
                </a>
                <button type="button" class="action-btn danger" data-toggle="modal" data-target="#modal-eliminar-lead" title="Eliminar lead">
                    <i class="fa fa-trash"></i> Eliminar
                </button>
            </div>
        </div>

        {{-- ── Detail Grid ── --}}
        <div class="detail-grid">

            {{-- ────── Columna izquierda ────── --}}
            <div>

                {{-- Contacto --}}
                <div class="dashboard-card">
                    <div class="dc-header">
                        <h5><i class="fa fa-address-card-o"></i> Contacto</h5>
                    </div>
                    <div class="dc-body">
                        <table class="detail-list">
                            @if ($lead->email)
                                <tr>
                                    <td><i class="fa fa-envelope" style="color:#aaa;"></i> Email</td>
                                    <td><a href="mailto:{{ $lead->email }}"
                                            style="color:#1a1a2e;">{{ $lead->email }}</a></td>
                                </tr>
                            @endif
                            @if ($lead->phone)
                                <tr>
                                    <td><i class="fa fa-phone" style="color:#aaa;"></i> Teléfono</td>
                                    <td><a href="tel:{{ $lead->phone }}" style="color:#1a1a2e;">{{ $lead->phone }}</a>
                                    </td>
                                </tr>
                            @endif
                            @if ($lead->whatsapp)
                                <tr>
                                    <td><i class="fa fa-whatsapp" style="color:#25d366;"></i> WhatsApp</td>
                                    <td><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp) }}"
                                            target="_blank" style="color:#25d366;">{{ $lead->whatsapp }}</a></td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- Detalles --}}
                <div class="dashboard-card">
                    <div class="dc-header">
                        <h5><i class="fa fa-info-circle"></i> Detalles</h5>
                    </div>
                    <div class="dc-body">
                        <table class="detail-list">
                            <tr>
                                <td>Fuente</td>
                                <td>{{ $lead->source_label }}</td>
                            </tr>
                            <tr>
                                <td>Interés</td>
                                <td>{{ $lead->interest_type_label }}</td>
                            </tr>
                            <tr>
                                <td>Presupuesto</td>
                                <td>{{ $lead->budget_range }}</td>
                            </tr>
                            @if ($lead->property)
                                <tr>
                                    <td>Propiedad</td>
                                    <td>{{ $lead->property->title }}</td>
                                </tr>
                            @endif
                            @if ($lead->vehicle)
                                <tr>
                                    <td>Vehículo</td>
                                    <td>{{ $lead->vehicle->name }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td>Primer contacto</td>
                                <td>{{ $lead->first_contact_at ? $lead->first_contact_at->format('d/m/Y H:i') : '—' }}</td>
                            </tr>
                            <tr>
                                <td>Último contacto</td>
                                <td>{{ $lead->last_contact_at ? $lead->last_contact_at->format('d/m/Y H:i') : '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Cambiar Estado --}}
                <div class="dashboard-card">
                    <div class="dc-header">
                        <h5><i class="fa fa-exchange"></i> Cambiar Estado</h5>
                    </div>
                    <div class="dc-body">
                        <form action="{{ route('admin.crm.leads.update-status', $lead) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="status-select">
                                @foreach (\App\Lead::getStatuses() as $key => $label)
                                    <option value="{{ $key }}" {{ $lead->status == $key ? 'selected' : '' }}>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                            <textarea name="note" class="status-textarea" placeholder="Nota (opcional)"></textarea>
                            <button type="submit" class="action-btn primary" style="width:100%; justify-content:center;">
                                <i class="fa fa-check"></i> Actualizar Estado
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Recordatorios --}}
                @if ($lead->reminders->count() > 0)
                    <div class="dashboard-card">
                        <div class="dc-header">
                            <h5><i class="fa fa-bell"></i> Recordatorios</h5>
                            <span style="font-size:12px; color:#aaa;">{{ $lead->reminders->count() }}</span>
                        </div>
                        <div class="dc-body">
                            @foreach ($lead->reminders as $reminder)
                                <div class="reminder-item {{ $reminder->isDue() ? 'due' : '' }}">
                                    <div>
                                        <div class="ri-title">{{ $reminder->title }}</div>
                                        <div class="ri-date"><i class="fa fa-clock-o"></i>
                                            {{ $reminder->remind_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <form action="{{ route('admin.crm.reminders.complete', $reminder) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="action-btn success" title="Completar"
                                            style="padding:5px 9px;">
                                            <i class="fa fa-check"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Citas --}}
                @if ($lead->appointments->count() > 0)
                    <div class="dashboard-card">
                        <div class="dc-header">
                            <h5><i class="fa fa-calendar"></i> Citas</h5>
                            <span style="font-size:12px; color:#aaa;">{{ $lead->appointments->count() }}</span>
                        </div>
                        <div class="dc-body">
                            @foreach ($lead->appointments as $appointment)
                                <div class="appt-item">
                                    <div>
                                        <div class="ai-title">{{ $appointment->title }}</div>
                                        <div class="ai-date"><i class="fa fa-clock-o"></i>
                                            {{ $appointment->starts_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <span
                                        class="crm-badge {{ $appointment->status }}">{{ $appointment->status_label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- ────── Columna derecha ────── --}}
            <div>

                {{-- Notas / Requerimientos --}}
                @if ($lead->requirements || $lead->notes)
                    <div class="dashboard-card">
                        <div class="dc-body">
                            @if ($lead->requirements)
                                <h6 style="font-size:13px; font-weight:600; color:#1a1a2e; margin-bottom:6px;">
                                    <i class="fa fa-list-ul" style="color:#c2ac1f;"></i> Lo que busca
                                </h6>
                                <p style="font-size:13px; color:#555; margin-bottom:{{ $lead->notes ? '14px' : '0' }};">
                                    {{ $lead->requirements }}</p>
                            @endif
                            @if ($lead->notes)
                                <h6 style="font-size:13px; font-weight:600; color:#1a1a2e; margin-bottom:6px;">
                                    <i class="fa fa-sticky-note-o" style="color:#c2ac1f;"></i> Notas internas
                                </h6>
                                <p style="font-size:13px; color:#555; margin:0;">{{ $lead->notes }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Property Matching --}}
                @if (isset($matchedProperties) && $matchedProperties->count() > 0)
                    <div class="dashboard-card" style="margin-bottom:18px;">
                        <div class="dc-header" style="background:#1a1a2e;">
                            <h5 style="color:#fff;"><i class="fa fa-home" style="color:#c2ac1f;"></i> Propiedades
                                Sugeridas</h5>
                            <span style="font-size:12px;color:#aaa;">{{ $matchedProperties->count() }}
                                coincidencias</span>
                        </div>
                        @foreach ($matchedProperties->take(3) as $pm)
                            <div
                                style="display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid #f5f5f5;">
                                <div style="flex:1;min-width:0;">
                                    <div
                                        style="font-weight:600;color:#1a1a2e;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $pm['property']->title ?? ($pm['property']->name ?? 'Propiedad') }}
                                    </div>
                                    <div style="font-size:11px;color:#aaa;margin-top:2px;">
                                        {{ $pm['property']->sector?->name ?? '' }}
                                        @if ($pm['property']->bedrooms)
                                            · {{ $pm['property']->bedrooms }} hab.
                                        @endif
                                    </div>
                                </div>
                                <div style="text-align:right;flex-shrink:0;">
                                    <div
                                        style="font-size:11px;font-weight:700;color:{{ $pm['score'] >= 70 ? '#22c55e' : ($pm['score'] >= 40 ? '#f59e0b' : '#94a3b8') }};">
                                        {{ $pm['score'] }}% match
                                    </div>
                                    <div
                                        style="height:4px;width:60px;background:#f0f0f0;border-radius:4px;margin-top:3px;overflow:hidden;">
                                        <div
                                            style="height:100%;width:{{ $pm['score'] }}%;background:{{ $pm['score'] >= 70 ? '#22c55e' : ($pm['score'] >= 40 ? '#f59e0b' : '#94a3b8') }};">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if ($matchedProperties->count() > 3)
                            <div style="padding:10px 18px;text-align:center;">
                                <a href="{{ route('admin.crm.leads.matching', $lead) }}"
                                    style="font-size:12px;color:#c2ac1f;">
                                    Ver todas ({{ $matchedProperties->count() }})
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Tasks preview --}}
                @if (isset($pendingTasks) && $pendingTasks->count() > 0)
                    <div class="dashboard-card" style="margin-bottom:18px;">
                        <div class="dc-header">
                            <h5><i class="fa fa-tasks"></i> Tareas Pendientes</h5>
                            <a href="{{ route('admin.crm.tasks.create', ['lead_id' => $lead->id]) }}"
                                class="action-btn success" style="padding:4px 10px;font-size:11px;">
                                <i class="fa fa-plus"></i>
                            </a>
                        </div>
                        @foreach ($pendingTasks as $task)
                            <div
                                style="display:flex;align-items:center;gap:10px;padding:10px 18px;border-bottom:1px solid #f5f5f5;">
                                <form action="{{ route('admin.crm.tasks.complete', $task) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit"
                                        style="width:18px;height:18px;border-radius:4px;border:2px solid #d1d5db;background:transparent;cursor:pointer;"
                                        title="Completar"></button>
                                </form>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:500;color:#1a1a2e;">{{ $task->title }}</div>
                                    @if ($task->due_at)
                                        <div class="fu-{{ $task->is_overdue ? 'overdue' : ($task->is_due_today ? 'today' : 'ok') }}"
                                            style="font-size:11px;margin-top:1px;">
                                            <i class="fa fa-clock-o"></i> {{ $task->due_at->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </div>
                                <span class="crm-badge {{ $task->priority }}">
                                    {{ \App\LeadTask::getPriorities()[$task->priority]['label'] }}
                                </span>
                            </div>
                        @endforeach
                        @if ($lead->tasks()->pending()->count() > $pendingTasks->count())
                            <div style="padding:8px 18px;text-align:center;">
                                <a href="{{ route('admin.crm.tasks.index', ['lead_id' => $lead->id]) }}"
                                    style="font-size:12px;color:#c2ac1f;">
                                    Ver todas
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="dashboard-card" style="margin-bottom:18px;">
                        <div class="dc-header">
                            <h5><i class="fa fa-tasks"></i> Tareas</h5>
                            <a href="{{ route('admin.crm.tasks.create', ['lead_id' => $lead->id]) }}"
                                class="action-btn success" style="padding:4px 10px;font-size:11px;">
                                <i class="fa fa-plus"></i> Nueva
                            </a>
                        </div>
                        <div style="padding:16px 18px;text-align:center;color:#ccc;font-size:13px;">
                            Sin tareas pendientes
                        </div>
                    </div>
                @endif

                {{-- Cotizaciones --}}
                <div class="dashboard-card" style="margin-bottom:18px;">
                    <div class="dc-header">
                        <h5><i class="fa fa-file-text-o"></i> Cotizaciones</h5>
                        <a href="{{ route('admin.crm.quotes.create', ['lead_id' => $lead->id]) }}"
                            class="action-btn primary" style="font-size:12px;padding:5px 10px;">
                            <i class="fa fa-plus"></i> Nueva Cotización
                        </a>
                    </div>
                    <div class="dc-body" style="padding:0;">
                        @forelse($lead->quotes()->orderByDesc('created_at')->take(5)->get() as $q)
                            <div
                                style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid #f5f5f5;">
                                <div>
                                    <div style="font-weight:600;font-size:13px;color:#1a1a2e;">{{ $q->quote_number }} —
                                        {{ Str::limit($q->title, 35) }}</div>
                                    <div style="font-size:11px;color:#888;margin-top:2px;">{{ $q->formatted_total }} ·
                                        {{ $q->created_at->format('d/m/Y') }}</div>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="crm-badge {{ $q->status_color }}">{{ $q->status_label }}</span>
                                    <a href="{{ route('admin.crm.quotes.show', $q) }}" class="action-btn view"
                                        style="padding:4px 8px;font-size:11px;"><i class="fa fa-eye"></i></a>
                                    <a href="{{ route('admin.crm.quotes.pdf', $q) }}" class="action-btn secondary"
                                        target="_blank" style="padding:4px 8px;font-size:11px;"><i
                                            class="fa fa-file-pdf-o"></i></a>
                                </div>
                            </div>
                        @empty
                            <div style="padding:20px;text-align:center;color:#aaa;font-size:13px;">Sin cotizaciones aún.
                            </div>
                        @endforelse
                        @if ($lead->quotes()->count() > 5)
                            <div style="padding:10px 16px;text-align:center;">
                                <a href="{{ route('admin.crm.quotes.index', ['lead_id' => $lead->id]) }}"
                                    style="font-size:12px;color:#c2ac1f;">Ver todas ({{ $lead->quotes()->count() }})</a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Registrar Actividad --}}
                <div class="dashboard-card">
                    <div class="dc-header">
                        <h5><i class="fa fa-plus-circle"></i> Registrar Actividad</h5>
                    </div>
                    <div class="dc-body">
                        <form action="{{ route('admin.crm.leads.add-activity', $lead) }}" method="POST">
                            @csrf
                            <div style="display:grid; grid-template-columns:1fr 2fr; gap:10px; margin-bottom:10px;">
                                <select name="type" class="act-select" required>
                                    @foreach (\App\LeadActivity::getTypes() as $key => $label)
                                        @if ($key !== 'status_change')
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <input type="text" name="subject" class="act-input" placeholder="Asunto (opcional)">
                            </div>
                            <textarea name="description" class="act-textarea" placeholder="Descripción de la actividad..."
                                style="margin-bottom:10px;"></textarea>

                            <div id="call-fields"
                                style="display:none; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                                <select name="call_result" class="act-select">
                                    <option value="">Resultado de llamada</option>
                                    @foreach (\App\LeadActivity::getCallResults() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="call_duration" class="act-input"
                                    placeholder="Duración (seg.)" min="0">
                            </div>

                            <button type="submit" class="action-btn primary">
                                <i class="fa fa-save"></i> Registrar
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Timeline de Actividades --}}
                <div class="dashboard-card">
                    <div class="dc-header">
                        <h5><i class="fa fa-history"></i> Historial de Actividades</h5>
                        <span style="font-size:12px; color:#aaa;">{{ $lead->activities->count() }} registros</span>
                    </div>
                    <div class="dc-body">
                        @forelse($lead->activities as $activity)
                            <div class="activity-item">
                                <div class="act-icon-wrap {{ $activity->type }}">
                                    <i class="{{ $activity->type_icon }}"></i>
                                </div>
                                <div class="act-body">
                                    <div>
                                        <span class="act-type">{{ $activity->type_label }}</span>
                                        <span class="act-time">{{ $activity->activity_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if ($activity->subject)
                                        <div class="act-subject">{{ $activity->subject }}</div>
                                    @endif
                                    @if ($activity->type === 'status_change')
                                        <div class="act-desc">
                                            <span class="crm-badge {{ $activity->old_status ?? '' }}"
                                                style="font-size:10px;">{{ \App\Lead::getStatuses()[$activity->old_status] ?? $activity->old_status }}</span>
                                            <i class="fa fa-arrow-right"
                                                style="color:#aaa; margin:0 4px; font-size:10px;"></i>
                                            <span class="crm-badge {{ $activity->new_status ?? '' }}"
                                                style="font-size:10px;">{{ \App\Lead::getStatuses()[$activity->new_status] ?? $activity->new_status }}</span>
                                            @if ($activity->description)
                                                <div style="margin-top:4px; color:#666;">{{ $activity->description }}
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($activity->description)
                                        <div class="act-desc">{{ $activity->description }}</div>
                                    @endif
                                    @if ($activity->call_result)
                                        <div class="act-desc" style="color:#888;">
                                            <i class="fa fa-phone"></i> {{ $activity->call_result_label }}
                                            @if ($activity->call_duration_formatted)
                                                ({{ $activity->call_duration_formatted }})
                                            @endif
                                        </div>
                                    @endif
                                    <div class="act-by">Por: {{ $activity->user->name ?? 'Sistema' }}</div>
                                </div>
                            </div>
                        @empty
                            <div style="text-align:center; padding:36px 0; color:#ccc;">
                                <i class="fa fa-history" style="font-size:32px; display:block; margin-bottom:10px;"></i>
                                <span style="font-size:13px; color:#aaa;">No hay actividades registradas</span>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

{{-- Modal: Eliminar Lead con motivo de auditoría --}}
<div class="modal fade" id="modal-eliminar-lead" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:12px; border:none;">
            <div class="modal-header" style="background:#dc2626; border-radius:12px 12px 0 0; padding:16px 20px;">
                <h5 class="modal-title" style="color:#fff; font-weight:700;"><i class="fa fa-trash"></i> Eliminar Lead</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
            </div>
            <form method="POST" action="{{ route('admin.crm.leads.destroy', $lead) }}">
                @csrf @method('DELETE')
                <div class="modal-body" style="padding:24px;">
                    <div style="background:#fff1f2;border-left:4px solid #ef4444;border-radius:6px;padding:12px 16px;margin-bottom:16px;">
                        <strong>¿Eliminar a {{ $lead->name }}?</strong><br>
                        <span style="font-size:13px;">Esta acción es permanente. Se eliminarán todas las actividades, tareas y citas asociadas.</span>
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-weight:600; font-size:13px; color:#444;">Motivo de eliminación <span class="text-danger">*</span></label>
                        <textarea name="reason" rows="3" class="form-control" required style="border-radius:8px;"
                            placeholder="Ej: Lead duplicado, cliente no interesado, datos incorrectos..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f0f0f0; padding:14px 20px; display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="action-btn secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="action-btn danger">
                        <i class="fa fa-trash"></i> Confirmar Eliminación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('script')
    <script>
        /* ── Mostrar campos de llamada ── */
        document.querySelector('select[name="type"]').addEventListener('change', function() {
            const cf = document.getElementById('call-fields');
            if (this.value === 'call') {
                cf.style.display = 'grid';
            } else {
                cf.style.display = 'none';
            }
        });

        /* ── Lead switcher ── */
        (function() {
            const input = document.getElementById('lead-switcher-input');
            const dropdown = document.getElementById('lead-switcher-dropdown');
            const SEARCH_URL = '{{ route('admin.crm.leads.quick-search') }}';
            let timer = null;

            input.addEventListener('input', function() {
                clearTimeout(timer);
                const q = this.value.trim();
                if (q.length < 2) {
                    dropdown.style.display = 'none';
                    dropdown.innerHTML = '';
                    return;
                }
                timer = setTimeout(() => {
                    fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.json())
                        .then(results => {
                            if (!results.length) {
                                dropdown.innerHTML =
                                    '<div class="sw-no-result">Sin resultados</div>';
                            } else {
                                dropdown.innerHTML = results.map(lead => `
                        <a href="${lead.url}" class="sw-item">
                            <div>
                                <strong>${esc(lead.name)}</strong>
                                <br><small>${esc(lead.phone || lead.email || '')}</small>
                            </div>
                            <span class="crm-badge ${esc(lead.status_key || '')}" style="font-size:10px;">${esc(lead.status)}</span>
                        </a>
                    `).join('');
                            }
                            dropdown.style.display = 'block';
                        });
                }, 320);
            });

            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });

            function esc(str) {
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
                    '&quot;');
            }
        })();
    </script>
    {{-- Email Modal --}}
    <div class="modal fade" id="modal-send-email" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
                <div class="modal-header"
                    style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;padding:16px 20px;">
                    <h6 class="modal-title mb-0"><i class="fa fa-envelope"></i> Enviar Email — {{ $lead->name }}</h6>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="email-form" action="{{ route('admin.crm.leads.send-email', $lead) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted mb-1 d-block">Para</label>
                            <input type="email" name="to" value="{{ $lead->email }}"
                                class="form-control form-control-sm" style="border-radius:8px;" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted mb-1 d-block">Asunto <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control form-control-sm"
                                style="border-radius:8px;" required placeholder="Asunto del mensaje">
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-muted mb-1 d-block">Mensaje <span
                                    class="text-danger">*</span></label>
                            <textarea name="body" class="form-control form-control-sm" rows="5" style="border-radius:8px;resize:none;"
                                required placeholder="Escribe tu mensaje aquí..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border:none;padding:12px 20px;">
                        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm"
                            style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;border-radius:8px;">
                            <i class="fa fa-paper-plane"></i> Enviar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('script')
    <script>
        document.getElementById('btn-send-email')?.addEventListener('click', function() {
            $('#modal-send-email').modal('show');
        });
    </script>
@endpush
