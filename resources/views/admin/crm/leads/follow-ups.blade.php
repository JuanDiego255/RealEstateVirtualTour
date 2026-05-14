@extends('admin.main')
@section('title', 'CRM — Seguimientos')
@section('content')

@include('admin.crm.layouts._crm-styles')

<div class="crm-page">

    {{-- Header --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-clock-o"></i> Seguimientos Pendientes</h2>
            <div class="sub">
                Leads con fecha de seguimiento próxima o vencida ·
                <strong>{{ $leads->total() }}</strong> encontrados
            </div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver a Leads
            </a>
            <a href="{{ route('admin.crm.leads.create') }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nuevo Lead
            </a>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $overdue  = $leads->getCollection()->filter(fn($l) => $l->isOverdueForFollowUp())->count();
        $today    = $leads->getCollection()->filter(fn($l) => $l->needsFollowUpToday())->count();
        $upcoming = $leads->getCollection()->filter(fn($l) => !$l->isOverdueForFollowUp() && !$l->needsFollowUpToday())->count();
    @endphp
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
        <div class="stat-card">
            <div class="sc-icon red"><i class="fa fa-exclamation-triangle"></i></div>
            <div class="sc-value">{{ $overdue }}</div>
            <div class="sc-label">Vencidos</div>
            <div class="sc-sub">Requieren atención inmediata</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon yellow"><i class="fa fa-sun-o"></i></div>
            <div class="sc-value">{{ $today }}</div>
            <div class="sc-label">Hoy</div>
            <div class="sc-sub">Seguimiento programado hoy</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon blue"><i class="fa fa-calendar"></i></div>
            <div class="sc-value">{{ $upcoming }}</div>
            <div class="sc-label">Próximos</div>
            <div class="sc-sub">En los próximos días</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon green"><i class="fa fa-users"></i></div>
            <div class="sc-value">{{ $leads->total() }}</div>
            <div class="sc-label">Total en lista</div>
            <div class="sc-sub">Con seguimiento asignado</div>
        </div>
    </div>

    {{-- ¿Cómo funcionan los seguimientos? --}}
    <div class="dashboard-card" style="margin-bottom:18px; border-left: 4px solid #c2ac1f;">
        <div class="dc-body" style="padding:14px 18px;">
            <div style="display:flex; align-items:flex-start; gap:12px;">
                <div style="flex-shrink:0; width:36px; height:36px; background:rgba(194,172,31,.13); border-radius:9px; display:flex; align-items:center; justify-content:center; color:#c2ac1f;">
                    <i class="fa fa-info-circle" style="font-size:17px;"></i>
                </div>
                <div>
                    <strong style="font-size:13px; color:#1a1a2e;">¿Cómo funciona el seguimiento?</strong>
                    <p style="font-size:12.5px; color:#666; margin:4px 0 0; line-height:1.6;">
                        La <strong>fecha de próximo seguimiento</strong> se configura al crear o editar un lead (campo <em>"Próx. seguimiento"</em>),
                        o al registrar una actividad desde el listado de leads o el detalle del lead.
                        Esta página muestra todos los leads que tienen una fecha asignada y que están
                        <span style="color:#dc2626; font-weight:600;">vencidos</span>,
                        <span style="color:#d97706; font-weight:600;">programados para hoy</span>, o
                        <span style="color:#3b82f6; font-weight:600;">próximos</span>.
                        Al contactar al cliente, registra una actividad y actualiza la siguiente fecha de seguimiento.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="dashboard-card">
        <div class="dc-header">
            <h5><i class="fa fa-list"></i> Leads con Seguimiento</h5>
            <a href="{{ route('admin.crm.reports.follow-ups') }}" target="_blank" class="action-btn gold" style="padding:5px 11px; font-size:12px;">
                <i class="fa fa-file-pdf-o"></i> PDF
            </a>
        </div>

        @forelse($leads as $lead)
        @php
            $rowClass = $lead->isOverdueForFollowUp() ? 'row-danger' : ($lead->needsFollowUpToday() ? 'row-warning' : '');
        @endphp
        <div style="padding:14px 18px; border-bottom:1px solid #f5f5f5; {{ $lead->isOverdueForFollowUp() ? 'background:#fff8f8;' : ($lead->needsFollowUpToday() ? 'background:#fffcf0;' : '') }}"
             class="followup-row">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">

                {{-- Info del lead --}}
                <div style="flex:1; min-width:200px;">
                    <a href="{{ route('admin.crm.leads.show', $lead) }}"
                       style="font-size:14px; font-weight:600; color:#1a1a2e; text-decoration:none;">
                        {{ $lead->name }}
                    </a>
                    <div style="margin-top:4px; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                        <span class="crm-badge {{ $lead->status }}">{{ $lead->status_label }}</span>
                        <span class="crm-badge {{ $lead->priority }}">{{ $lead->priority_label }}</span>
                        @if($lead->property)
                            <span style="font-size:11px; color:#888;"><i class="fa fa-home"></i> {{ Str::limit($lead->property->title, 22) }}</span>
                        @endif
                    </div>
                    @if($lead->phone || $lead->email)
                    <div style="margin-top:6px; font-size:12px; color:#666;">
                        @if($lead->phone)
                            <a href="tel:{{ $lead->phone }}" style="color:#555; text-decoration:none; margin-right:10px;">
                                <i class="fa fa-phone" style="color:#aaa;"></i> {{ $lead->phone }}
                            </a>
                        @endif
                        @if($lead->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp) }}" target="_blank"
                               style="color:#25d366; text-decoration:none;">
                                <i class="fa fa-whatsapp"></i> WhatsApp
                            </a>
                        @endif
                        @if(!$lead->phone && $lead->email)
                            <span style="color:#888;"><i class="fa fa-envelope" style="color:#aaa;"></i> {{ $lead->email }}</span>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Fechas --}}
                <div style="display:flex; gap:24px; flex-wrap:wrap; align-items:flex-start;">
                    {{-- Próx. seguimiento --}}
                    <div style="text-align:center; min-width:100px;">
                        <div style="font-size:10px; color:#aaa; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;">Próx. seguimiento</div>
                        @if($lead->isOverdueForFollowUp())
                            <div class="fu-overdue">
                                <i class="fa fa-exclamation-triangle"></i>
                                {{ $lead->next_follow_up->format('d/m/Y') }}
                            </div>
                            <div style="font-size:10px; color:#dc2626;">{{ $lead->next_follow_up->diffForHumans() }}</div>
                        @elseif($lead->needsFollowUpToday())
                            <div class="fu-today"><i class="fa fa-sun-o"></i> Hoy</div>
                            <div style="font-size:10px; color:#d97706;">{{ $lead->next_follow_up->format('H:i') ?: 'Todo el día' }}</div>
                        @else
                            <div class="fu-ok">{{ $lead->next_follow_up->format('d/m/Y') }}</div>
                            <div style="font-size:10px; color:#aaa;">{{ $lead->next_follow_up->diffForHumans() }}</div>
                        @endif
                    </div>

                    {{-- Último contacto --}}
                    <div style="text-align:center; min-width:100px;">
                        <div style="font-size:10px; color:#aaa; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;">Último contacto</div>
                        @if($lead->last_contact_at)
                            <div class="fu-ok">{{ $lead->last_contact_at->format('d/m/Y') }}</div>
                            <div style="font-size:10px; color:#aaa;">{{ $lead->last_contact_at->diffForHumans() }}</div>
                        @else
                            <div style="color:#ccc; font-size:12px;">—</div>
                        @endif
                    </div>

                    {{-- Agente --}}
                    <div style="text-align:center; min-width:80px;">
                        <div style="font-size:10px; color:#aaa; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;">Agente</div>
                        <div style="font-size:12px; color:#555;">{{ $lead->user->name ?? '—' }}</div>
                    </div>

                    {{-- Acciones --}}
                    <div style="display:flex; gap:6px; align-items:center; padding-top:16px;">
                        <a href="{{ route('admin.crm.leads.show', $lead) }}" class="action-btn view" title="Ver lead">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.crm.appointments.create', ['lead_id' => $lead->id]) }}" class="action-btn success" style="padding:5px 9px; font-size:12px;" title="Agendar cita">
                            <i class="fa fa-calendar-plus-o"></i>
                        </a>
                        @if($lead->phone)
                        <a href="tel:{{ $lead->phone }}" class="action-btn secondary" style="padding:5px 9px; font-size:12px;" title="Llamar">
                            <i class="fa fa-phone"></i>
                        </a>
                        @endif
                        @if($lead->whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp) }}" target="_blank" class="action-btn" style="background:#d1fae5;color:#15803d;padding:5px 9px;font-size:12px;" title="WhatsApp">
                            <i class="fa fa-whatsapp"></i>
                        </a>
                        @endif
                    </div>
                </div>

            </div>
        </div>
        @empty
        <div style="text-align:center; padding:60px 20px; color:#ccc;">
            <i class="fa fa-check-circle" style="font-size:48px; display:block; margin-bottom:12px; color:#22c55e;"></i>
            <div style="font-size:16px; font-weight:600; color:#aaa;">¡Todo al día!</div>
            <div style="font-size:13px; color:#ccc; margin-top:6px;">No hay seguimientos pendientes en este momento.</div>
        </div>
        @endforelse

        @if($leads->hasPages())
        <div id="pagination-wrap" style="padding:14px 18px; border-top:1px solid #f5f5f5;">
            {{ $leads->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
