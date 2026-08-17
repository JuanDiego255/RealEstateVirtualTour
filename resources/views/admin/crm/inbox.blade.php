@extends('admin.main')
@section('title', 'Bandeja — Sin atender')
@section('content')
@include('admin.crm._ui')

<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-inbox"></i> Bandeja de trabajo</h2>
            <p class="sub">{{ $isAdmin ? 'Toda la empresa' : 'Tus pendientes' }} · {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}</p>
        </div>
        <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary"><i class="fa fa-users"></i> Todos los leads</a>
    </div>

    @if(session('success'))<div class="crm-alert success">{{ session('success') }}</div>@endif

    {{-- Stat cards --}}
    <div class="crm-stat-row">
        <div class="crm-stat-card bd-violet">
            <div class="crm-stat-icon ic-violet"><i class="fa fa-user-times"></i></div>
            <div><div class="crm-stat-number">{{ $counts['uncontacted'] }}</div><div class="crm-stat-label">Sin contactar</div></div>
        </div>
        <div class="crm-stat-card bd-red">
            <div class="crm-stat-icon ic-red"><i class="fa fa-exclamation-circle"></i></div>
            <div><div class="crm-stat-number">{{ $counts['tasks'] }}</div><div class="crm-stat-label">Tareas vencidas</div></div>
        </div>
        <div class="crm-stat-card bd-amber">
            <div class="crm-stat-icon ic-amber"><i class="fa fa-bell"></i></div>
            <div><div class="crm-stat-number">{{ $counts['reminders'] }}</div><div class="crm-stat-label">Recordatorios vencidos</div></div>
        </div>
    </div>

    {{-- Leads sin contactar --}}
    <div class="crm-section">
        <div class="crm-section-header">
            <h5><i class="fa fa-user-plus"></i> Leads sin contactar</h5>
            <span class="hint">ordenados por score</span>
        </div>
        <div class="crm-section-body">
            <div class="crm-table-wrap">
                <table class="crm-table">
                    <thead><tr>
                        <th>Lead</th><th>Origen</th><th>Estado</th>@if($isAdmin)<th>Asesor</th>@endif<th class="num">Score</th><th>Ingreso</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse($uncontacted as $lead)
                            <tr>
                                <td>
                                    <div style="font-weight:600;">{{ $lead->name ?: 'Sin nombre' }}</div>
                                    @if($lead->phone)<div class="muted">{{ $lead->phone }}</div>@endif
                                </td>
                                <td><span class="crm-badge slate">{{ \App\Lead::getSources()[$lead->source] ?? $lead->source }}</span></td>
                                <td><span class="crm-badge blue">{{ \App\Lead::getStatuses()[$lead->status] ?? $lead->status }}</span></td>
                                @if($isAdmin)<td class="muted">{{ optional($lead->user)->name ?: '—' }}</td>@endif
                                <td class="num"><strong>{{ (int) $lead->score }}</strong></td>
                                <td class="muted">{{ optional($lead->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="num"><a href="{{ route('admin.crm.leads.show', $lead) }}" class="action-btn primary xs">Atender</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $isAdmin ? 7 : 6 }}"><div class="empty-state"><i class="fa fa-thumbs-up" style="color:#22c55e;"></i>Nada sin contactar</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="crm-two-col">
        {{-- Tareas vencidas --}}
        <div class="crm-section">
            <div class="crm-section-header"><h5><i class="fa fa-exclamation-triangle"></i> Tareas vencidas</h5></div>
            <div class="crm-section-body">
                @forelse($overdueTasks as $task)
                    <div class="crm-row-item">
                        <div class="crm-row-icon ic-red"><i class="fa fa-check-square-o"></i></div>
                        <div class="crm-row-main">
                            <div class="crm-row-title">{{ $task->title }}</div>
                            <div class="crm-row-sub">
                                {{ optional($task->lead)->name }} · venció {{ optional($task->due_at)->format('d/m/Y H:i') }}
                                @if($isAdmin && $task->assignee) · {{ $task->assignee->name }}@endif
                            </div>
                        </div>
                        <div class="crm-row-right">
                            @if($task->lead)<a href="{{ route('admin.crm.leads.show', $task->lead) }}" class="action-btn danger xs">Ver</a>@endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state"><i class="fa fa-check-circle" style="color:#22c55e;"></i>Sin tareas vencidas</div>
                @endforelse
            </div>
        </div>

        {{-- Recordatorios vencidos --}}
        <div class="crm-section">
            <div class="crm-section-header"><h5><i class="fa fa-bell"></i> Recordatorios vencidos</h5></div>
            <div class="crm-section-body">
                @forelse($dueReminders as $rem)
                    <div class="crm-row-item">
                        <div class="crm-row-icon ic-amber"><i class="fa fa-bell"></i></div>
                        <div class="crm-row-main">
                            <div class="crm-row-title">{{ $rem->title }}</div>
                            <div class="crm-row-sub">
                                {{ $rem->related_item_name }} · {{ optional($rem->remind_at)->format('d/m/Y H:i') }}
                                @if($isAdmin && $rem->user) · {{ $rem->user->name }}@endif
                            </div>
                        </div>
                        <div class="crm-row-right">
                            @if($rem->remindable instanceof \App\Lead)
                                <a href="{{ route('admin.crm.leads.show', $rem->remindable) }}" class="action-btn warning xs">Ver</a>
                            @else
                                <a href="{{ route('admin.crm.reminders.index') }}" class="action-btn warning xs">Ver</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state"><i class="fa fa-bell-slash-o" style="color:#22c55e;"></i>Sin recordatorios vencidos</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
