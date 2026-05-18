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

        /* ── Lead Tabs ── */
        .crm-lead-tabs {
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 22px;
            gap: 4px;
            flex-wrap: wrap;
        }
        .crm-lead-tabs .nav-link {
            border: none;
            border-radius: 10px 10px 0 0;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 600;
            color: #888;
            background: transparent;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .15s;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        .crm-lead-tabs .nav-link:hover {
            color: #1a1a2e;
            background: #f8f9fa;
        }
        .crm-lead-tabs .nav-link.active {
            color: #1a1a2e;
            background: #fff;
            border-bottom: 3px solid #c2ac1f;
        }
        .crm-lead-tabs .tab-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            border-radius: 9px;
            font-size: 10px;
            font-weight: 700;
            padding: 0 5px;
            background: #e5e7eb;
            color: #6b7280;
        }
        .crm-lead-tabs .tab-badge.danger {
            background: #fee2e2;
            color: #dc2626;
        }
        .crm-tab-content .tab-pane { padding-top: 4px; }
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
                <a href="#" class="action-btn secondary" data-toggle="modal" data-target="#modal-nueva-cita">
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

        {{-- ── Tab Nav ── --}}
        @php
            $overdueTaskCount = isset($pendingTasks) ? $pendingTasks->where('is_overdue', true)->count() : 0;
            $activeRemCount   = $lead->reminders->count();
            $urgentCount      = $overdueTaskCount + ($lead->appointments->where('status','scheduled')->count());
            $quotesCount      = $lead->quotes()->count();
        @endphp
        <ul class="nav crm-lead-tabs" id="leadTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link" id="tab-resumen-lnk" data-toggle="tab" href="#tab-resumen" role="tab">
                    <i class="fa fa-user"></i> Resumen
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-actividad-lnk" data-toggle="tab" href="#tab-actividad" role="tab">
                    <i class="fa fa-history"></i> Actividad
                    @if($lead->activities->count() > 0)
                        <span class="tab-badge">{{ $lead->activities->count() }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-citas-lnk" data-toggle="tab" href="#tab-citas" role="tab">
                    <i class="fa fa-calendar"></i> Citas & Tareas
                    @if($urgentCount > 0)
                        <span class="tab-badge danger">{{ $urgentCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-cotizaciones-lnk" data-toggle="tab" href="#tab-cotizaciones" role="tab">
                    <i class="fa fa-file-text-o"></i> Cotizaciones
                    @if($quotesCount > 0)
                        <span class="tab-badge">{{ $quotesCount }}</span>
                    @endif
                </a>
            </li>
        </ul>

        <div class="tab-content crm-tab-content" id="leadTabContent">

            {{-- ══════════ TAB: RESUMEN ══════════ --}}
            <div class="tab-pane fade" id="tab-resumen" role="tabpanel">
                <div class="detail-grid">
                    {{-- Columna izquierda: Contacto + Detalles --}}
                    <div>
                        {{-- Contacto --}}
                        <div class="dashboard-card">
                            <div class="dc-header">
                                <h5><i class="fa fa-address-card-o"></i> Contacto</h5>
                                @if($lead->whatsapp)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp) }}" target="_blank"
                                   class="action-btn success" style="padding:4px 10px;font-size:12px;">
                                    <i class="fa fa-whatsapp"></i> WhatsApp
                                </a>
                                @endif
                            </div>
                            <div class="dc-body">
                                <table class="detail-list">
                                    @if ($lead->email)
                                        <tr>
                                            <td><i class="fa fa-envelope" style="color:#aaa;"></i> Email</td>
                                            <td><a href="mailto:{{ $lead->email }}" style="color:#1a1a2e;">{{ $lead->email }}</a></td>
                                        </tr>
                                    @endif
                                    @if ($lead->phone)
                                        <tr>
                                            <td><i class="fa fa-phone" style="color:#aaa;"></i> Teléfono</td>
                                            <td><a href="tel:{{ $lead->phone }}" style="color:#1a1a2e;">{{ $lead->phone }}</a></td>
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
                                    <tr><td>Fuente</td><td>{{ $lead->source_label }}</td></tr>
                                    <tr><td>Interés</td><td>{{ $lead->interest_type_label }}</td></tr>
                                    <tr><td>Presupuesto</td><td>{{ $lead->budget_range }}</td></tr>
                                    @if ($lead->property)
                                        <tr><td>Propiedad</td><td>{{ $lead->property->title }}</td></tr>
                                    @endif
                                    @if ($lead->vehicle)
                                        <tr><td>Vehículo</td><td>{{ $lead->vehicle->name }}</td></tr>
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
                    </div>

                    {{-- Columna derecha: Cambiar Estado + Property Matching --}}
                    <div>
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
                                            <option value="{{ $key }}" {{ $lead->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <textarea name="note" class="status-textarea" placeholder="Nota (opcional)"></textarea>
                                    <button type="submit" class="action-btn primary" style="width:100%; justify-content:center;">
                                        <i class="fa fa-check"></i> Actualizar Estado
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Property / Vehicle Matching --}}
                        @if (isset($matchedProperties) && $matchedProperties->count() > 0)
                            <div class="dashboard-card" style="margin-bottom:18px;">
                                <div class="dc-header" style="background:#1a1a2e;">
                                    <h5 style="color:#fff;">
                                        <i class="fa fa-home" style="color:#c2ac1f;"></i>
                                        {{ $lead->preferred_asset_type === 'vehicle' ? 'Vehículos Sugeridos' : 'Propiedades Sugeridas' }}
                                    </h5>
                                    <span style="font-size:12px;color:#aaa;">{{ $matchedProperties->count() }} coincidencias</span>
                                </div>
                                @foreach ($matchedProperties->take(3) as $pm)
                                    <div style="display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid #f5f5f5;">
                                        <div style="flex:1;min-width:0;">
                                            <div style="font-weight:600;color:#1a1a2e;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                {{ $pm['property']->title ?? ($pm['property']->name ?? 'Propiedad') }}
                                            </div>
                                            <div style="font-size:11px;color:#aaa;margin-top:2px;">
                                                {{ $pm['property']->sector?->name ?? '' }}
                                                @if ($pm['property']->bedrooms)· {{ $pm['property']->bedrooms }} hab.@endif
                                            </div>
                                        </div>
                                        <div style="text-align:right;flex-shrink:0;">
                                            <div style="font-size:11px;font-weight:700;color:{{ $pm['score'] >= 70 ? '#22c55e' : ($pm['score'] >= 40 ? '#f59e0b' : '#94a3b8') }};">
                                                {{ $pm['score'] }}% match
                                            </div>
                                            <div style="height:4px;width:60px;background:#f0f0f0;border-radius:4px;margin-top:3px;overflow:hidden;">
                                                <div style="height:100%;width:{{ $pm['score'] }}%;background:{{ $pm['score'] >= 70 ? '#22c55e' : ($pm['score'] >= 40 ? '#f59e0b' : '#94a3b8') }};"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @if ($matchedProperties->count() > 3)
                                    <div style="padding:10px 18px;text-align:center;">
                                        <a href="{{ route('admin.crm.leads.matching', $lead) }}" style="font-size:12px;color:#c2ac1f;">
                                            Ver todas ({{ $matchedProperties->count() }})
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>{{-- /tab-resumen --}}

            {{-- ══════════ TAB: ACTIVIDAD ══════════ --}}
            <div class="tab-pane fade" id="tab-actividad" role="tabpanel">
                <div class="detail-grid">
                    {{-- Registrar Actividad --}}
                    <div>
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
                                    <textarea name="description" class="act-textarea" placeholder="Descripción de la actividad..." style="margin-bottom:10px;"></textarea>
                                    <div id="call-fields" style="display:none; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                                        <select name="call_result" class="act-select">
                                            <option value="">Resultado de llamada</option>
                                            @foreach (\App\LeadActivity::getCallResults() as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" name="call_duration" class="act-input" placeholder="Duración (seg.)" min="0">
                                    </div>
                                    <button type="submit" class="action-btn primary">
                                        <i class="fa fa-save"></i> Registrar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Timeline de Actividades --}}
                    <div>
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
                                                    <span class="crm-badge {{ $activity->old_status ?? '' }}" style="font-size:10px;">{{ \App\Lead::getStatuses()[$activity->old_status] ?? $activity->old_status }}</span>
                                                    <i class="fa fa-arrow-right" style="color:#aaa; margin:0 4px; font-size:10px;"></i>
                                                    <span class="crm-badge {{ $activity->new_status ?? '' }}" style="font-size:10px;">{{ \App\Lead::getStatuses()[$activity->new_status] ?? $activity->new_status }}</span>
                                                    @if ($activity->description)
                                                        <div style="margin-top:4px; color:#666;">{{ $activity->description }}</div>
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
            </div>{{-- /tab-actividad --}}

            {{-- ══════════ TAB: CITAS & TAREAS ══════════ --}}
            <div class="tab-pane fade" id="tab-citas" role="tabpanel">
                <div class="detail-grid">
                    {{-- Columna izquierda: Recordatorios + Citas --}}
                    <div>
                        {{-- Recordatorios --}}
                        <div class="dashboard-card">
                            <div class="dc-header">
                                <h5><i class="fa fa-bell"></i> Recordatorios</h5>
                                <span style="font-size:12px; color:#aaa;">{{ $lead->reminders->count() }}</span>
                            </div>
                            <div class="dc-body">
                                @forelse($lead->reminders as $reminder)
                                    <div class="reminder-item {{ $reminder->isDue() ? 'due' : '' }}">
                                        <div>
                                            <div class="ri-title">{{ $reminder->title }}</div>
                                            <div class="ri-date"><i class="fa fa-clock-o"></i> {{ $reminder->remind_at->format('d/m/Y H:i') }}</div>
                                        </div>
                                        <form action="{{ route('admin.crm.reminders.complete', $reminder) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="action-btn success" title="Completar" style="padding:5px 9px;">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <div style="padding:16px 18px;text-align:center;color:#ccc;font-size:13px;">Sin recordatorios</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Citas --}}
                        <div class="dashboard-card">
                            <div class="dc-header">
                                <h5><i class="fa fa-calendar"></i> Citas</h5>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="font-size:12px; color:#aaa;">{{ $lead->appointments->count() }}</span>
                                    <a href="#" class="action-btn primary" data-toggle="modal" data-target="#modal-nueva-cita" style="padding:4px 10px;font-size:11px;">
                                        <i class="fa fa-plus"></i> Nueva
                                    </a>
                                </div>
                            </div>
                            <div class="dc-body">
                                @forelse($lead->appointments as $appointment)
                                    <div class="appt-item" style="cursor:pointer;" data-toggle="modal" data-target="#modal-cita-{{ $appointment->id }}">
                                        <div>
                                            <div class="ai-title">{{ $appointment->title }}</div>
                                            <div class="ai-date"><i class="fa fa-clock-o"></i> {{ $appointment->starts_at->format('d/m/Y H:i') }}</div>
                                        </div>
                                        <span class="crm-badge {{ $appointment->status }}">{{ $appointment->status_label }}</span>
                                    </div>
                                @empty
                                    <div style="padding:16px 18px;text-align:center;color:#ccc;font-size:13px;">Sin citas agendadas</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Columna derecha: Tareas --}}
                    <div>
                        <div class="dashboard-card">
                            <div class="dc-header">
                                <h5><i class="fa fa-tasks"></i> Tareas</h5>
                                <a href="#" class="action-btn primary" data-toggle="modal" data-target="#modal-nueva-tarea" style="padding:4px 10px;font-size:11px;">
                                    <i class="fa fa-plus"></i> Nueva
                                </a>
                            </div>
                            @if (isset($pendingTasks) && $pendingTasks->count() > 0)
                                @foreach ($pendingTasks as $task)
                                    @php
                                        $taskCreatorIsAdmin = $task->creator?->role === 'company_admin';
                                        $canEdit = Auth::user()->isAdmin() || !$taskCreatorIsAdmin;
                                    @endphp
                                    <div style="display:flex;align-items:center;gap:10px;padding:10px 18px;border-bottom:1px solid #f5f5f5;">
                                        <form action="{{ route('admin.crm.tasks.complete', $task) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" style="width:18px;height:18px;border-radius:4px;border:2px solid #d1d5db;background:transparent;cursor:pointer;" title="Completar"></button>
                                        </form>
                                        <div style="flex:1;min-width:0;">
                                            <div style="font-size:13px;font-weight:500;color:#1a1a2e;">{{ $task->title }}</div>
                                            @if ($task->due_at)
                                                <div class="fu-{{ $task->is_overdue ? 'overdue' : ($task->is_due_today ? 'today' : 'ok') }}" style="font-size:11px;margin-top:1px;">
                                                    <i class="fa fa-clock-o"></i> {{ $task->due_at->format('d/m/Y H:i') }}
                                                </div>
                                            @endif
                                        </div>
                                        <span class="crm-badge {{ $task->priority }}">{{ \App\LeadTask::getPriorities()[$task->priority]['label'] }}</span>
                                        @if($canEdit)
                                            <a href="{{ route('admin.crm.tasks.edit', $task) }}" class="action-btn secondary" style="padding:3px 7px;font-size:11px;" title="Editar"><i class="fa fa-pencil"></i></a>
                                            <form action="{{ route('admin.crm.tasks.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta tarea?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="action-btn danger" style="padding:3px 7px;font-size:11px;" title="Eliminar"><i class="fa fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                                @if ($lead->tasks()->pending()->count() > $pendingTasks->count())
                                    <div style="padding:8px 18px;text-align:center;">
                                        <a href="{{ route('admin.crm.tasks.index', ['lead_id' => $lead->id]) }}" style="font-size:12px;color:#c2ac1f;">Ver todas</a>
                                    </div>
                                @endif
                            @else
                                <div style="padding:20px 18px;text-align:center;color:#ccc;font-size:13px;">
                                    <i class="fa fa-check-circle" style="font-size:24px;display:block;margin-bottom:8px;color:#d1fae5;"></i>
                                    Sin tareas pendientes
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>{{-- /tab-citas --}}

            {{-- ══════════ TAB: COTIZACIONES ══════════ --}}
            <div class="tab-pane fade" id="tab-cotizaciones" role="tabpanel">
                <div class="dashboard-card">
                    <div class="dc-header">
                        <h5><i class="fa fa-file-text-o"></i> Cotizaciones</h5>
                        <a href="#" class="action-btn primary" data-toggle="modal" data-target="#modal-nueva-cotizacion" style="font-size:12px;padding:5px 10px;">
                            <i class="fa fa-plus"></i> Nueva Cotización
                        </a>
                    </div>
                    <div class="dc-body" style="padding:0;">
                        @forelse($lead->quotes()->orderByDesc('created_at')->get() as $q)
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-bottom:1px solid #f5f5f5;">
                                <div>
                                    <div style="font-weight:600;font-size:13px;color:#1a1a2e;">{{ $q->quote_number }} — {{ Str::limit($q->title, 40) }}</div>
                                    <div style="font-size:11px;color:#888;margin-top:2px;">{{ $q->formatted_total }} · {{ $q->created_at->format('d/m/Y') }}</div>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                    <span class="crm-badge {{ $q->status_color }}">{{ $q->status_label }}</span>
                                    <a href="{{ route('admin.crm.quotes.show', $q) }}" class="action-btn view" style="padding:4px 8px;font-size:11px;" title="Ver detalle"><i class="fa fa-eye"></i></a>
                                    <a href="{{ route('admin.crm.quotes.pdf', $q) }}" class="action-btn secondary" target="_blank" style="padding:4px 8px;font-size:11px;" title="Ver PDF"><i class="fa fa-file-pdf-o"></i></a>
                                    @if(Auth::user()->isAdmin())
                                    <form action="{{ route('admin.crm.quotes.destroy', $q) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta cotización?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn danger" style="padding:4px 8px;font-size:11px;" title="Eliminar"><i class="fa fa-trash"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div style="padding:36px;text-align:center;color:#aaa;">
                                <i class="fa fa-file-text-o" style="font-size:28px;display:block;margin-bottom:10px;"></i>
                                Sin cotizaciones aún.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>{{-- /tab-cotizaciones --}}

        </div>{{-- /tab-content --}}
    </div>{{-- /lead-detail-wrap --}}

{{-- ══════════════════════════════════════════════════════
     MODALS: Nueva Tarea / Nueva Cita / Nueva Cotización / Detalle Citas
════════════════════════════════════════════════════════ --}}

{{-- Modal: Nueva Tarea --}}
<div class="modal fade" id="modal-nueva-tarea" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:12px; border:none; box-shadow:0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background:#1a1a2e; border-radius:12px 12px 0 0; padding:16px 20px;">
                <h5 class="modal-title" style="color:#fff; font-weight:700;"><i class="fa fa-tasks" style="color:#c2ac1f;"></i> Nueva Tarea</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
            </div>
            <form method="POST" action="{{ route('admin.crm.tasks.store') }}">
                @csrf
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                <input type="hidden" name="_back" value="{{ url()->current() }}?tab=citas">
                <div class="modal-body" style="padding:24px;">
                    <div class="form-group mb-3">
                        <label style="font-weight:600; font-size:13px; color:#444;">Título <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="Ej: Llamar al cliente para agendar visita" style="border-radius:8px;">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label style="font-weight:600; font-size:13px; color:#444;">Tipo</label>
                                <select name="type" class="form-control" style="border-radius:8px;">
                                    @foreach(\App\LeadTask::getTypes() as $key => $info)
                                        <option value="{{ $key }}" {{ $key === 'call' ? 'selected' : '' }}>{{ $info['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label style="font-weight:600; font-size:13px; color:#444;">Prioridad</label>
                                <select name="priority" class="form-control" style="border-radius:8px;">
                                    @foreach(\App\LeadTask::getPriorities() as $key => $info)
                                        <option value="{{ $key }}" {{ $key === 'medium' ? 'selected' : '' }}>{{ is_array($info) ? $info['label'] : $info }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label style="font-weight:600; font-size:13px; color:#444;">Fecha límite</label>
                        <input type="datetime-local" name="due_at" class="form-control" style="border-radius:8px;">
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-weight:600; font-size:13px; color:#444;">Descripción</label>
                        <textarea name="description" rows="3" class="form-control" style="border-radius:8px;" placeholder="Detalles o instrucciones..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f0f0f0; padding:14px 20px; gap:8px; display:flex; justify-content:flex-end;">
                    <button type="button" class="action-btn secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="action-btn" style="background:linear-gradient(135deg,#c2ac1f,#a89617); color:#000; font-weight:700;">
                        <i class="fa fa-save"></i> Guardar Tarea
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Nueva Cita --}}
<div class="modal fade" id="modal-nueva-cita" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:12px; border:none; box-shadow:0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background:#1a1a2e; border-radius:12px 12px 0 0; padding:16px 20px;">
                <h5 class="modal-title" style="color:#fff; font-weight:700;"><i class="fa fa-calendar-plus-o" style="color:#c2ac1f;"></i> Nueva Cita</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
            </div>
            <form method="POST" action="{{ route('admin.crm.appointments.store') }}">
                @csrf
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                <input type="hidden" name="user_id" value="{{ $lead->user_id }}">
                <input type="hidden" name="_back" value="{{ url()->current() }}?tab=citas">
                <div class="modal-body" style="padding:24px;">
                    <div class="form-group mb-3">
                        <label style="font-weight:600; font-size:13px; color:#444;">Título <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="Ej: Visita a propiedad en Escazú" style="border-radius:8px;">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label style="font-weight:600; font-size:13px; color:#444;">Tipo</label>
                                <select name="type" class="form-control" style="border-radius:8px;">
                                    @foreach(\App\Appointment::getTypes() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label style="font-weight:600; font-size:13px; color:#444;">Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="starts_at_date" class="form-control" required style="border-radius:8px;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label style="font-weight:600; font-size:13px; color:#444;">Hora inicio <span class="text-danger">*</span></label>
                                <input type="time" name="starts_at_time" class="form-control" required style="border-radius:8px;" value="09:00">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label style="font-weight:600; font-size:13px; color:#444;">Duración <span class="text-danger">*</span></label>
                                <select name="duration" class="form-control" style="border-radius:8px;">
                                    <option value="30">30 minutos</option>
                                    <option value="60" selected>1 hora</option>
                                    <option value="90">1 hora 30 min</option>
                                    <option value="120">2 horas</option>
                                    <option value="180">3 horas</option>
                                    <option value="240">4 horas</option>
                                    <option value="480">Todo el día</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label style="font-weight:600; font-size:13px; color:#444;">Ubicación</label>
                                <input type="text" name="location" class="form-control" style="border-radius:8px;" placeholder="Dirección o enlace de reunión">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-weight:600; font-size:13px; color:#444;">Descripción / Notas</label>
                        <textarea name="description" rows="2" class="form-control" style="border-radius:8px;" placeholder="Notas sobre la cita..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f0f0f0; padding:14px 20px; display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="action-btn secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="action-btn" style="background:linear-gradient(135deg,#c2ac1f,#a89617); color:#000; font-weight:700;">
                        <i class="fa fa-save"></i> Guardar Cita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Nueva Cotización --}}
<div class="modal fade" id="modal-nueva-cotizacion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:12px; border:none; box-shadow:0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background:#1a1a2e; border-radius:12px 12px 0 0; padding:16px 20px;">
                <h5 class="modal-title" style="color:#fff; font-weight:700;"><i class="fa fa-file-text-o" style="color:#c2ac1f;"></i> Nueva Cotización</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
            </div>
            <form method="POST" action="{{ route('admin.crm.quotes.store') }}">
                @csrf
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                <input type="hidden" name="validity_days" value="30">
                <input type="hidden" name="currency" value="CRC">
                <input type="hidden" name="_back" value="{{ url()->current() }}?tab=cotizaciones">
                <div class="modal-body" style="padding:24px;">

                    {{-- Título --}}
                    <div class="form-group mb-3">
                        <label style="font-weight:600; font-size:13px; color:#444;">Título <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="Ej: Cotización Casa Escazú" style="border-radius:8px;">
                    </div>

                    {{-- Toggle tipo --}}
                    <div class="form-group mb-3">
                        <label style="font-weight:600; font-size:13px; color:#444;">Tipo de cotización</label>
                        <div style="display:flex; gap:16px; margin-top:6px;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#333;">
                                <input type="radio" name="quote_type" value="property" id="mqt-property" checked onchange="toggleModalQuoteType(this.value)">
                                <i class="fa fa-home" style="color:#1a1a2e;"></i> Inmueble / Propiedad
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#333;">
                                <input type="radio" name="quote_type" value="vehicle" id="mqt-vehicle" onchange="toggleModalQuoteType(this.value)">
                                <i class="fa fa-car" style="color:#c2ac1f;"></i> Vehículo
                            </label>
                        </div>
                    </div>

                    {{-- Sección Vehículo con calculadora (oculta por defecto) --}}
                    <div id="mq-vehicle-section" style="display:none;">
                        <div style="background:#1a1a2e; border-radius:10px 10px 0 0; padding:12px 16px; margin-bottom:0;">
                            <span style="color:#fff; font-weight:700; font-size:13px;"><i class="fa fa-car" style="color:#c2ac1f;"></i> Datos del Vehículo y Financiamiento</span>
                        </div>
                        <div style="border:1px solid #e5e7eb; border-top:none; border-radius:0 0 10px 10px; padding:20px; margin-bottom:16px;">
                            {{-- Selector vehículo --}}
                            <div class="form-group mb-3">
                                <label style="font-weight:600; font-size:13px; color:#444;">Vehículo <span class="text-danger" id="mq-veh-req">*</span></label>
                                <select name="property_id" id="mq-vehicle-select" class="form-control" style="border-radius:8px;">
                                    <option value="">— Seleccionar vehículo —</option>
                                    @foreach(\App\Properties::vehicles()->whereHas('category', fn($q) => $q->where('company_id', auth()->user()->company_id))->get() as $veh)
                                        <option value="{{ $veh->id }}">
                                            {{ $veh->brand ?? '' }} {{ $veh->model ?? '' }}{{ ($veh->year ?? '') ? ' ('.$veh->year.')' : '' }} — {{ $veh->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label style="font-weight:600; font-size:13px; color:#444;">Precio del vehículo</label>
                                        <input type="number" name="vq_vehicle_price" id="mq-price" class="form-control" step="0.01" min="0" placeholder="0.00" style="border-radius:8px;" oninput="calcModalQuote()">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label style="font-weight:600; font-size:13px; color:#444;">Prima (%)</label>
                                        <input type="number" name="vq_down_payment_pct" id="mq-pct" class="form-control" step="0.1" min="0" max="100" value="50" style="border-radius:8px;" oninput="calcModalQuote()">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label style="font-weight:600; font-size:13px; color:#444;">Prima (monto)</label>
                                        <input type="number" name="vq_down_payment" id="mq-down" class="form-control" step="0.01" readonly style="border-radius:8px; background:#f8f8f8;">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label style="font-weight:600; font-size:13px; color:#444;">Plazo (meses)</label>
                                        <select name="vq_term_months" id="mq-term" class="form-control" style="border-radius:8px;" onchange="calcModalQuote()">
                                            <option value="12">12 meses</option>
                                            <option value="24">24 meses</option>
                                            <option value="36">36 meses</option>
                                            <option value="48">48 meses</option>
                                            <option value="60" selected>60 meses</option>
                                            <option value="72">72 meses</option>
                                            <option value="84">84 meses</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label style="font-weight:600; font-size:13px; color:#444;">Tasa de interés (anual %)</label>
                                        <input type="number" name="vq_interest_rate_pct" id="mq-rate-pct" class="form-control" step="0.01" min="0" max="100" placeholder="Ej: 12.5" style="border-radius:8px;" oninput="calcModalQuote()">
                                        <input type="hidden" name="vq_interest_rate" id="mq-rate">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label style="font-weight:600; font-size:13px; color:#444;">Frecuencia de pago</label>
                                        <select name="vq_payment_frequency" id="mq-freq" class="form-control" style="border-radius:8px;" onchange="calcModalQuote()">
                                            <option value="monthly">Mensual</option>
                                            <option value="annual">Anual</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Resultado del cálculo --}}
                            <div id="mq-result" style="display:none; background:linear-gradient(135deg,#1a1a2e,#2d2d4e); border-radius:10px; padding:18px; margin-top:4px;">
                                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; text-align:center;">
                                    <div>
                                        <div style="font-size:10px; color:#aaa; text-transform:uppercase; margin-bottom:4px;">Cuota estimada</div>
                                        <div id="mq-payment-display" style="font-size:20px; font-weight:700; color:#c2ac1f;"></div>
                                    </div>
                                    <div>
                                        <div style="font-size:10px; color:#aaa; text-transform:uppercase; margin-bottom:4px;">Total intereses</div>
                                        <div id="mq-interest-display" style="font-size:16px; font-weight:600; color:#fff;"></div>
                                    </div>
                                    <div>
                                        <div style="font-size:10px; color:#aaa; text-transform:uppercase; margin-bottom:4px;">Total a pagar</div>
                                        <div id="mq-total-display" style="font-size:16px; font-weight:600; color:#fff;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notas --}}
                    <div class="form-group mb-0">
                        <label style="font-weight:600; font-size:13px; color:#444;">Notas</label>
                        <textarea name="notes" rows="2" class="form-control" style="border-radius:8px;" placeholder="Condiciones, observaciones..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f0f0f0; padding:14px 20px; display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="action-btn secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="action-btn" style="background:linear-gradient(135deg,#c2ac1f,#a89617); color:#000; font-weight:700;">
                        <i class="fa fa-save"></i> Guardar Cotización
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modals: Detalle de Citas --}}
@foreach($lead->appointments as $appointment)
<div class="modal fade" id="modal-cita-{{ $appointment->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:12px; border:none; box-shadow:0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background:#1a1a2e; border-radius:12px 12px 0 0; padding:16px 20px;">
                <h5 class="modal-title" style="color:#fff; font-weight:700;">
                    <i class="fa fa-calendar" style="color:#c2ac1f;"></i>
                    {{ $appointment->title }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <div style="font-size:11px; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Tipo</div>
                        <div style="font-size:14px; color:#333;">{{ $appointment->type_label }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Estado</div>
                        <span class="crm-badge {{ $appointment->status }}">{{ $appointment->status_label }}</span>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Inicio</div>
                        <div style="font-size:14px; color:#333;">{{ $appointment->starts_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Fin</div>
                        <div style="font-size:14px; color:#333;">{{ $appointment->ends_at ? $appointment->ends_at->format('d/m/Y H:i') : '—' }}</div>
                    </div>
                </div>
                @if($appointment->location ?? $appointment->address ?? null)
                <div style="margin-bottom:12px;">
                    <div style="font-size:11px; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Ubicación</div>
                    <div style="font-size:13px; color:#333;">{{ $appointment->location ?? $appointment->address }}</div>
                </div>
                @endif
                @if($appointment->description)
                <div style="margin-bottom:12px;">
                    <div style="font-size:11px; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Descripción</div>
                    <div style="font-size:13px; color:#333; line-height:1.5;">{{ $appointment->description }}</div>
                </div>
                @endif
                @if($appointment->outcome_notes ?? null)
                <div style="background:#f0f9ff; border-left:4px solid #3b82f6; border-radius:6px; padding:10px 14px; margin-top:12px; font-size:13px;">
                    <strong style="color:#1d4ed8;">Resultado:</strong> {{ $appointment->outcome_notes }}
                </div>
                @endif
            </div>
            <div class="modal-footer" style="border-top:1px solid #f0f0f0; padding:14px 20px; display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('admin.crm.appointments.edit', $appointment) }}" class="action-btn secondary" style="padding:6px 14px; font-size:13px;">
                    <i class="fa fa-pencil"></i> Editar
                </a>
                @if($appointment->status === 'scheduled')
                <form method="POST" action="{{ route('admin.crm.appointments.complete', $appointment) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <button type="submit" class="action-btn" style="background:#22c55e; color:#fff; padding:6px 14px; font-size:13px;">
                        <i class="fa fa-check"></i> Completar
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.crm.appointments.cancel', $appointment) }}" style="display:inline;" onsubmit="return confirm('¿Cancelar esta cita?');">
                    @csrf @method('PATCH')
                    <button type="submit" class="action-btn danger" style="padding:6px 14px; font-size:13px;">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                </form>
                @endif
                <button type="button" class="action-btn secondary" data-dismiss="modal" style="margin-left:auto;">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endforeach

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

@push('script')
<script>
// Modal cotización: toggle tipo inmueble / vehículo
function toggleModalQuoteType(val) {
    var vSec = document.getElementById('mq-vehicle-section');
    var vSel = document.getElementById('mq-vehicle-select');
    var isVehicle = val === 'vehicle';
    if (vSec) vSec.style.display = isVehicle ? 'block' : 'none';
    if (vSel) vSel.required = isVehicle;
}

// Calculadora de financiamiento del modal
function calcModalQuote() {
    var price   = parseFloat(document.getElementById('mq-price').value) || 0;
    var pct     = parseFloat(document.getElementById('mq-pct').value) || 50;
    var term    = parseInt(document.getElementById('mq-term').value) || 60;
    var ratePct = parseFloat(document.getElementById('mq-rate-pct').value) || 0;
    var freq    = document.getElementById('mq-freq').value;

    var downPayment = price * (pct / 100);
    document.getElementById('mq-down').value = downPayment.toFixed(2);

    var rate = ratePct / 100;
    document.getElementById('mq-rate').value = rate;

    var principal = price - downPayment;
    var periodsPerYear = freq === 'annual' ? 1 : 12;
    var nPeriods = freq === 'annual' ? Math.ceil(term / 12) : term;
    var periodicRate = rate / periodsPerYear;

    var payment;
    if (rate > 0 && nPeriods > 0) {
        payment = principal * (periodicRate * Math.pow(1 + periodicRate, nPeriods)) / (Math.pow(1 + periodicRate, nPeriods) - 1);
    } else {
        payment = nPeriods > 0 ? principal / nPeriods : 0;
    }

    var totalPaid     = payment * nPeriods;
    var totalInterest = Math.max(totalPaid - principal, 0);
    var grandTotal    = downPayment + totalPaid;

    var fmt = function(n) { return n.toLocaleString('es-CR', {minimumFractionDigits:2, maximumFractionDigits:2}); };

    if (price > 0) {
        document.getElementById('mq-result').style.display = 'block';
        document.getElementById('mq-payment-display').textContent  = '₡' + fmt(payment);
        document.getElementById('mq-interest-display').textContent = '₡' + fmt(totalInterest);
        document.getElementById('mq-total-display').textContent    = '₡' + fmt(grandTotal);
    }
}

// Inicializar al abrir el modal
document.getElementById('modal-nueva-cotizacion')?.addEventListener('shown.bs.modal', function() {
    toggleModalQuoteType('property');
});

// ── Tab persistence via ?tab= URL param ──
(function() {
    var tabMap = {
        'resumen':       '#tab-resumen-lnk',
        'actividad':     '#tab-actividad-lnk',
        'citas':         '#tab-citas-lnk',
        'cotizaciones':  '#tab-cotizaciones-lnk',
    };
    // Read ?tab= from URL and activate that tab
    var params = new URLSearchParams(window.location.search);
    var activeTab = params.get('tab');
    if (activeTab && tabMap[activeTab]) {
        $(tabMap[activeTab]).tab('show');
    } else {
        // Default: show Resumen
        $('#tab-resumen-lnk').tab('show');
    }
    // Update URL when user clicks a tab (no page reload)
    Object.keys(tabMap).forEach(function(key) {
        var el = document.querySelector(tabMap[key]);
        if (!el) return;
        el.addEventListener('shown.bs.tab', function() {
            var url = new URL(window.location.href);
            url.searchParams.set('tab', key);
            history.replaceState(null, '', url.toString());
        });
    });
})();
</script>
@endpush
