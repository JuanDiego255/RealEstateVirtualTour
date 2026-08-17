@extends('admin.main')
@section('title', 'Dashboard CRM — Hoy')
@section('content')

<style>
    .crm-dashboard { padding: 20px; }

    /* Stat cards row */
    .crm-stat-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }
    .crm-stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px 22px;
        box-shadow: 0 2px 12px rgba(0,0,0,.07);
        display: flex;
        align-items: center;
        gap: 16px;
        border-left: 4px solid transparent;
    }
    .crm-stat-card.appointments { border-color: #3b82f6; }
    .crm-stat-card.tasks        { border-color: #ef4444; }
    .crm-stat-card.reminders    { border-color: #f59e0b; }
    .crm-stat-card.attention    { border-color: #8b5cf6; }
    .crm-stat-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .crm-stat-card.appointments .crm-stat-icon { background:#dbeafe; color:#1d4ed8; }
    .crm-stat-card.tasks        .crm-stat-icon { background:#fee2e2; color:#dc2626; }
    .crm-stat-card.reminders    .crm-stat-icon { background:#fef3c7; color:#b45309; }
    .crm-stat-card.attention    .crm-stat-icon { background:#ede9fe; color:#7c3aed; }
    .crm-stat-number { font-size: 28px; font-weight: 800; color: #1a1a2e; line-height: 1; }
    .crm-stat-label  { font-size: 12px; color: #888; margin-top: 2px; }

    /* Section cards */
    .crm-section {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,.07);
        margin-bottom: 22px;
        overflow: hidden;
    }
    .crm-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #f0f0f0;
    }
    .crm-section-header h5 {
        font-size: 14px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .crm-section-header h5 i { color: #c2ac1f; }
    .crm-section-body { padding: 0; }

    /* Row items */
    .crm-row-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        border-bottom: 1px solid #f5f5f5;
        text-decoration: none;
        color: inherit;
        transition: background .12s;
    }
    .crm-row-item:last-child { border-bottom: none; }
    .crm-row-item:hover { background: #fafafa; text-decoration: none; color: inherit; }
    .crm-row-icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; flex-shrink: 0;
    }
    .crm-row-main { flex: 1; min-width: 0; }
    .crm-row-title {
        font-weight: 600; font-size: 13px; color: #1a1a2e;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .crm-row-sub { font-size: 11px; color: #888; margin-top: 2px; }
    .crm-row-right { text-align: right; flex-shrink: 0; }

    .crm-badge {
        display: inline-block; padding: 3px 9px; border-radius: 20px;
        font-size: 11px; font-weight: 600;
    }
    .crm-badge.scheduled  { background:#dbeafe; color:#1d4ed8; }
    .crm-badge.confirmed  { background:#d1fae5; color:#065f46; }
    .crm-badge.completed  { background:#f1f5f9; color:#475569; }
    .crm-badge.cancelled  { background:#fee2e2; color:#991b1b; }
    .crm-badge.overdue    { background:#fee2e2; color:#991b1b; }
    .crm-badge.due-today  { background:#fef3c7; color:#92400e; }
    .crm-badge.urgent     { background:#fee2e2; color:#991b1b; }
    .crm-badge.high       { background:#fef3c7; color:#92400e; }
    .crm-badge.medium     { background:#dbeafe; color:#1e40af; }
    .crm-badge.low        { background:#f1f5f9; color:#64748b; }
    .crm-badge.attention  { background:#ede9fe; color:#7c3aed; }

    .action-btn {
        padding: 6px 12px; border: none; border-radius: 8px; cursor: pointer;
        font-size: 12px; display: inline-flex; align-items: center; gap: 5px;
        transition: all .15s; text-decoration: none; font-weight: 500;
    }
    .action-btn.primary { background:#1a1a2e; color:#fff; }
    .action-btn.primary:hover { background:#2d2d4e; color:#fff; }
    .action-btn.success { background:#d1fae5; color:#065f46; }
    .action-btn.success:hover { background:#a7f3d0; }
    .action-btn.secondary { background:#f1f5f9; color:#475569; }
    .action-btn.secondary:hover { background:#e2e8f0; color:#1e293b; }

    .empty-state {
        padding: 32px;
        text-align: center;
        color: #ccc;
        font-size: 13px;
    }
    .empty-state i { font-size: 28px; display: block; margin-bottom: 8px; }

    /* Two-col grid for sections */
    .crm-two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px;
    }
    @media(max-width:768px) { .crm-two-col { grid-template-columns: 1fr; } }

    /* Timeline for attention leads */
    .attention-time {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 8px;
    }
    .attention-time.overdue { background:#fee2e2; color:#dc2626; }
    .attention-time.ok      { background:#f1f5f9; color:#64748b; }
</style>

<div class="crm-dashboard">

    {{-- ── Page Header ── --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="font-size:20px; font-weight:800; color:#1a1a2e; margin:0;">
                <i class="fa fa-sun-o" style="color:#c2ac1f;"></i>
                Dashboard — Hoy
            </h2>
            <p style="font-size:13px; color:#888; margin:4px 0 0;">
                {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
            </p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary">
                <i class="fa fa-users"></i> Todos los Leads
            </a>
            <a href="{{ route('admin.crm.appointments.index') }}" class="action-btn secondary">
                <i class="fa fa-calendar"></i> Agenda Completa
            </a>
        </div>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="crm-stat-row">
        <div class="crm-stat-card appointments">
            <div class="crm-stat-icon"><i class="fa fa-calendar-check-o"></i></div>
            <div>
                <div class="crm-stat-number">{{ $stats['appointments_today'] }}</div>
                <div class="crm-stat-label">Citas hoy</div>
            </div>
        </div>
        <div class="crm-stat-card tasks">
            <div class="crm-stat-icon"><i class="fa fa-exclamation-circle"></i></div>
            <div>
                <div class="crm-stat-number">{{ $stats['overdue_tasks'] }}</div>
                <div class="crm-stat-label">Tareas vencidas</div>
            </div>
        </div>
        <div class="crm-stat-card reminders">
            <div class="crm-stat-icon"><i class="fa fa-bell"></i></div>
            <div>
                <div class="crm-stat-number">{{ $stats['due_reminders'] }}</div>
                <div class="crm-stat-label">Recordatorios pendientes</div>
            </div>
        </div>
        <div class="crm-stat-card attention">
            <div class="crm-stat-icon"><i class="fa fa-user-times"></i></div>
            <div>
                <div class="crm-stat-number">{{ $stats['needs_attention'] }}</div>
                <div class="crm-stat-label">Leads sin atender</div>
            </div>
        </div>
    </div>

    {{-- ── Two column layout ── --}}
    <div class="crm-two-col">

        {{-- LEFT: Citas de hoy + Tareas vencidas --}}
        <div>
            {{-- Citas de hoy --}}
            <div class="crm-section">
                <div class="crm-section-header">
                    <h5><i class="fa fa-calendar-o"></i> Citas de Hoy</h5>
                    <a href="{{ route('admin.crm.appointments.today') }}" class="action-btn secondary" style="font-size:11px;">
                        Ver todas <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="crm-section-body">
                    @forelse($appointmentsToday as $apt)
                        <a href="{{ route('admin.crm.leads.show', $apt->lead_id) }}?tab=citas"
                           class="crm-row-item">
                            <div class="crm-row-icon" style="background:#dbeafe; color:#1d4ed8;">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <div class="crm-row-main">
                                <div class="crm-row-title">{{ $apt->title }}</div>
                                <div class="crm-row-sub">
                                    <i class="fa fa-user-o"></i> {{ $apt->lead->name ?? '—' }}
                                    &nbsp;·&nbsp;
                                    <i class="fa fa-clock-o"></i> {{ $apt->starts_at->format('H:i') }}
                                    @if($apt->ends_at) – {{ $apt->ends_at->format('H:i') }} @endif
                                </div>
                            </div>
                            <div class="crm-row-right">
                                <span class="crm-badge {{ $apt->status }}">{{ $apt->status_label }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state">
                            <i class="fa fa-calendar-o"></i>
                            Sin citas programadas para hoy
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Tareas con vencimiento hoy --}}
            @if($tasksDueToday->count() > 0)
            <div class="crm-section">
                <div class="crm-section-header">
                    <h5><i class="fa fa-check-square-o"></i> Tareas — Vencen Hoy</h5>
                </div>
                <div class="crm-section-body">
                    @foreach($tasksDueToday as $task)
                        <div class="crm-row-item">
                            <div class="crm-row-icon" style="background:#fef3c7; color:#b45309;">
                                <i class="fa fa-check-square-o"></i>
                            </div>
                            <div class="crm-row-main">
                                <div class="crm-row-title">{{ $task->title }}</div>
                                <div class="crm-row-sub">
                                    <i class="fa fa-user-o"></i>
                                    <a href="{{ route('admin.crm.leads.show', $task->lead_id) }}?tab=citas"
                                       style="color:#c2ac1f;">{{ $task->lead->name ?? '—' }}</a>
                                    @if($task->due_at)
                                        &nbsp;·&nbsp;<i class="fa fa-clock-o"></i> {{ $task->due_at->format('H:i') }}
                                    @endif
                                </div>
                            </div>
                            <div class="crm-row-right" style="display:flex;align-items:center;gap:8px;">
                                <span class="crm-badge {{ $task->priority }}">{{ \App\LeadTask::getPriorities()[$task->priority]['label'] }}</span>
                                <form action="{{ route('admin.crm.tasks.complete', $task) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="action-btn success" title="Completar" style="padding:4px 8px;">
                                        <i class="fa fa-check"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT: Tareas vencidas + Recordatorios + Leads atención --}}
        <div>
            {{-- Tareas vencidas --}}
            <div class="crm-section">
                <div class="crm-section-header">
                    <h5><i class="fa fa-exclamation-triangle"></i> Tareas Vencidas</h5>
                    @if($overdueTasks->count() > 0)
                        <span class="crm-badge overdue">{{ $overdueTasks->count() }}</span>
                    @endif
                </div>
                <div class="crm-section-body">
                    @forelse($overdueTasks as $task)
                        <div class="crm-row-item">
                            <div class="crm-row-icon" style="background:#fee2e2; color:#dc2626;">
                                <i class="fa fa-exclamation-circle"></i>
                            </div>
                            <div class="crm-row-main">
                                <div class="crm-row-title">{{ $task->title }}</div>
                                <div class="crm-row-sub">
                                    <a href="{{ route('admin.crm.leads.show', $task->lead_id) }}?tab=citas"
                                       style="color:#c2ac1f;">{{ $task->lead->name ?? '—' }}</a>
                                    @if($task->due_at)
                                        &nbsp;·&nbsp; Venció {{ $task->due_at->diffForHumans() }}
                                    @endif
                                </div>
                            </div>
                            <div class="crm-row-right" style="display:flex;align-items:center;gap:8px;">
                                <form action="{{ route('admin.crm.tasks.complete', $task) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="action-btn success" title="Completar" style="padding:4px 8px;">
                                        <i class="fa fa-check"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fa fa-check-circle" style="color:#22c55e;"></i>
                            Sin tareas vencidas
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recordatorios pendientes --}}
            <div class="crm-section">
                <div class="crm-section-header">
                    <h5><i class="fa fa-bell"></i> Recordatorios Pendientes</h5>
                    <a href="{{ route('admin.crm.reminders.index') }}" class="action-btn secondary" style="font-size:11px;">
                        Ver todos <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="crm-section-body">
                    @forelse($dueReminders as $reminder)
                        <div class="crm-row-item">
                            <div class="crm-row-icon" style="background:#fef3c7; color:#b45309;">
                                <i class="fa fa-bell"></i>
                            </div>
                            <div class="crm-row-main">
                                <div class="crm-row-title">{{ $reminder->title }}</div>
                                <div class="crm-row-sub">
                                    @if($reminder->remindable instanceof \App\Lead)
                                        <a href="{{ route('admin.crm.leads.show', $reminder->remindable) }}"
                                           style="color:#c2ac1f;">{{ $reminder->remindable->name ?? '—' }}</a>
                                        &nbsp;·&nbsp;
                                    @endif
                                    <i class="fa fa-clock-o"></i> {{ $reminder->remind_at->format('d/m H:i') }}
                                </div>
                            </div>
                            <div class="crm-row-right" style="display:flex;align-items:center;gap:6px;">
                                <form action="{{ route('admin.crm.reminders.complete', $reminder) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="action-btn success" title="Completar" style="padding:4px 8px;">
                                        <i class="fa fa-check"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.crm.reminders.dismiss', $reminder) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="action-btn secondary" title="Descartar" style="padding:4px 8px;">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fa fa-bell-slash-o" style="color:#22c55e;"></i>
                            Sin recordatorios pendientes
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Leads que necesitan atención --}}
            <div class="crm-section">
                <div class="crm-section-header">
                    <h5><i class="fa fa-user-times"></i> Leads sin Atender</h5>
                    <a href="{{ route('admin.crm.leads.follow-ups') }}" class="action-btn secondary" style="font-size:11px;">
                        Ver todos <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="crm-section-body">
                    @forelse($leadsNeedingAttention as $lead)
                        <a href="{{ route('admin.crm.leads.show', $lead) }}" class="crm-row-item">
                            <div class="crm-row-icon" style="background:#ede9fe; color:#7c3aed;">
                                <i class="fa fa-user"></i>
                            </div>
                            <div class="crm-row-main">
                                <div class="crm-row-title">{{ $lead->name }}</div>
                                <div class="crm-row-sub">
                                    <span class="crm-badge {{ $lead->status }}" style="font-size:10px;">{{ $lead->status_label }}</span>
                                    &nbsp;
                                    @if($lead->next_follow_up && $lead->next_follow_up->isPast())
                                        {{-- Follow-up vencido --}}
                                        <span style="color:#dc2626; font-size:11px;">
                                            <i class="fa fa-clock-o"></i>
                                            Seguimiento vencido {{ $lead->next_follow_up->diffForHumans() }} — requiere atención
                                        </span>
                                    @elseif($lead->last_contact_at && $lead->last_contact_at->diffInDays(now()) >= 7)
                                        {{-- Tiene contacto pero fue hace 7+ días --}}
                                        <span style="color:#b45309; font-size:11px;">
                                            <i class="fa fa-calendar-o"></i>
                                            Última actividad hace {{ $lead->last_contact_at->diffInDays(now()) }} días — se recomienda dar seguimiento
                                        </span>
                                    @elseif(!$lead->last_contact_at && $lead->first_contact_at)
                                        {{-- Fue ingresado (ej. kiosco/evento) pero sin actividad manual registrada --}}
                                        <span style="color:#7c3aed; font-size:11px;">
                                            <i class="fa fa-user-plus"></i>
                                            Sin seguimiento desde que fue registrado el {{ $lead->first_contact_at->format('d/m/Y') }}
                                        </span>
                                    @elseif(!$lead->last_contact_at)
                                        <span style="color:#ef4444; font-size:11px;">
                                            <i class="fa fa-exclamation-circle"></i>
                                            Sin ningún contacto registrado
                                        </span>
                                    @else
                                        <span style="color:#888; font-size:11px;">
                                            Último contacto {{ $lead->last_contact_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="crm-row-right">
                                <i class="fa fa-chevron-right" style="color:#ccc; font-size:11px;"></i>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state">
                            <i class="fa fa-thumbs-up" style="color:#22c55e;"></i>
                            Todos los leads están al día
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
