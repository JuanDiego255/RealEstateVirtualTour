@extends('admin.main')
@section('title', 'Recordatorios')
@section('content')

@include('admin.crm.layouts._crm-styles')
<style>
.reminder-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 18px; }
.reminder-tab {
    padding: 7px 18px; border-radius: 20px; font-size: 13px; font-weight: 500;
    text-decoration: none; transition: all .15s; border: 2px solid transparent;
}
.reminder-tab.active-pending   { background: #1a1a2e; color: #fff; border-color: #1a1a2e; }
.reminder-tab.active-due       { background: #fef3c7; color: #92400e; border-color: #f59e0b; }
.reminder-tab.active-today     { background: #dbeafe; color: #1d4ed8; border-color: #3b82f6; }
.reminder-tab.active-overdue   { background: #fee2e2; color: #991b1b; border-color: #ef4444; }
.reminder-tab.active-completed { background: #d1fae5; color: #065f46; border-color: #22c55e; }
.reminder-tab.inactive { background: #f5f5f5; color: #666; }
.reminder-tab.inactive:hover { background: #ebebeb; color: #333; text-decoration: none; }

.reminder-item {
    display: flex; align-items: flex-start; gap: 14px;
    padding: 16px 18px; border-bottom: 1px solid #f5f5f5; transition: background .1s;
}
.reminder-item:hover { background: #fffdf0; }
.reminder-item.overdue-bg { background: #fff8f8; }
.reminder-item.today-bg   { background: #fffcf0; }
.ri-icon {
    flex-shrink: 0; width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 16px;
}
.ri-body { flex: 1; }
.ri-title   { font-size: 14px; font-weight: 600; color: #1a1a2e; margin-bottom: 3px; }
.ri-desc    { font-size: 12.5px; color: #888; margin-bottom: 6px; line-height: 1.5; }
.ri-related { font-size: 12px; color: #6366f1; margin-bottom: 6px; }
.ri-date    { font-size: 12px; font-weight: 600; }
.ri-actions { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
</style>

@if (Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        <strong>{{ Session::get('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
    </div>
@endif

<div class="crm-page">

    {{-- Header --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-bell"></i> Recordatorios</h2>
            <div class="sub">Gestión de alertas y recordatorios del equipo</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.reminders.create') }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nuevo Recordatorio
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
        <div class="stat-card">
            <div class="sc-icon blue"><i class="fa fa-bell"></i></div>
            <div class="sc-value">{{ $counts['pending'] }}</div>
            <div class="sc-label">Pendientes</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon yellow"><i class="fa fa-exclamation-circle"></i></div>
            <div class="sc-value">{{ $counts['due'] }}</div>
            <div class="sc-label">Vencidos hoy</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon orange"><i class="fa fa-sun-o"></i></div>
            <div class="sc-value">{{ $counts['today'] }}</div>
            <div class="sc-label">Para hoy</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon red"><i class="fa fa-exclamation-triangle"></i></div>
            <div class="sc-value">{{ $counts['overdue'] }}</div>
            <div class="sc-label">Atrasados</div>
        </div>
    </div>

    {{-- Tabs de filtro --}}
    <div class="reminder-tabs">
        @php
        $tabs = [
            'pending'   => ['Pendientes',   $counts['pending']],
            'due'       => ['Vencidos hoy', $counts['due']],
            'today'     => ['Hoy',          $counts['today']],
            'overdue'   => ['Atrasados',    $counts['overdue']],
            'completed' => ['Completados',  ''],
        ];
        @endphp
        @foreach($tabs as $key => [$label, $count])
        <a href="{{ route('admin.crm.reminders.index', ['filter' => $key]) }}"
           class="reminder-tab {{ $filter === $key ? 'active-'.$key : 'inactive' }}">
            {{ $label }}
            @if($count !== '') <span style="font-size:11px; opacity:.75; margin-left:3px;">({{ $count }})</span> @endif
        </a>
        @endforeach
    </div>

    {{-- Lista --}}
    <div class="dashboard-card">
        <div class="dc-header">
            <h5><i class="fa fa-bell-o"></i> Recordatorios
                @if($filter) — <span style="font-weight:400; color:#888;">{{ $tabs[$filter][0] ?? '' }}</span> @endif
            </h5>
            <a href="{{ route('admin.crm.reminders.create') }}" class="action-btn success" style="padding:5px 12px; font-size:12px;">
                <i class="fa fa-plus"></i> Nuevo
            </a>
        </div>

        @forelse($reminders as $reminder)
        @php
            $isOverdue = $reminder->isOverdue();
            $isToday   = !$isOverdue && $reminder->remind_at->isToday();
            $bgClass   = $isOverdue ? 'overdue-bg' : ($isToday ? 'today-bg' : '');
            $iconStyle = $isOverdue
                ? 'background:#fee2e2;color:#991b1b;'
                : ($isToday ? 'background:#fef3c7;color:#92400e;' : 'background:#dbeafe;color:#1d4ed8;');
        @endphp
        <div class="reminder-item {{ $bgClass }}">
            <div class="ri-icon" style="{{ $iconStyle }}">
                <i class="fa fa-bell{{ $reminder->is_completed ? '-slash' : '' }}"></i>
            </div>
            <div class="ri-body">
                <div class="ri-title">
                    {{ $reminder->title }}
                    @if($reminder->is_recurring)
                        <span class="crm-badge" style="background:#e0e7ff;color:#4338ca;font-size:9px;margin-left:6px;">
                            <i class="fa fa-refresh"></i> {{ $reminder->recurrence_type_label }}
                        </span>
                    @endif
                </div>
                @if($reminder->description)
                    <div class="ri-desc">{{ Str::limit($reminder->description, 120) }}</div>
                @endif
                @if($reminder->related_item_name)
                    <div class="ri-related"><i class="fa fa-link"></i> {{ $reminder->related_item_name }}</div>
                @endif
                <div class="ri-date {{ $isOverdue ? 'fu-overdue' : ($isToday ? 'fu-today' : 'fu-ok') }}">
                    @if($isOverdue) <i class="fa fa-exclamation-circle"></i>
                    @elseif($isToday) <i class="fa fa-clock-o"></i>
                    @else <i class="fa fa-calendar"></i>
                    @endif
                    {{ $reminder->remind_at->format('d/m/Y H:i') }}
                    <span style="font-size:11px; font-weight:400; color:#aaa; margin-left:6px;">{{ $reminder->remind_at->diffForHumans() }}</span>
                </div>
                <div class="ri-actions">
                    @if(!$reminder->is_completed && !$reminder->is_dismissed)
                        <form action="{{ route('admin.crm.reminders.complete', $reminder) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="action-btn success" style="padding:5px 11px; font-size:12px;">
                                <i class="fa fa-check"></i> Completar
                            </button>
                        </form>
                        <form action="{{ route('admin.crm.reminders.snooze', $reminder) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="minutes" value="30">
                            <button type="submit" class="action-btn warning" style="padding:5px 11px; font-size:12px;">
                                <i class="fa fa-clock-o"></i> +30 min
                            </button>
                        </form>
                        <form action="{{ route('admin.crm.reminders.dismiss', $reminder) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="action-btn secondary" style="padding:5px 11px; font-size:12px;">
                                <i class="fa fa-times"></i> Descartar
                            </button>
                        </form>
                    @else
                        @if($reminder->is_completed)
                            <span class="crm-badge won"><i class="fa fa-check"></i> Completado {{ $reminder->completed_at?->format('d/m/Y H:i') }}</span>
                        @endif
                        @if($reminder->is_dismissed)
                            <span class="crm-badge lost"><i class="fa fa-times"></i> Descartado</span>
                        @endif
                    @endif
                    <a href="{{ route('admin.crm.reminders.edit', $reminder) }}?_back={{ urlencode(url()->current()) }}" class="action-btn view" style="padding:5px 11px; font-size:12px;">
                        <i class="fa fa-edit"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div style="text-align:center; padding:60px 20px;">
            <i class="fa fa-bell-slash" style="font-size:42px; display:block; margin-bottom:14px; color:#ddd;"></i>
            <div style="font-size:15px; font-weight:600; color:#aaa; margin-bottom:6px;">No hay recordatorios</div>
            <div style="font-size:13px; color:#ccc; margin-bottom:20px;">Crea uno para no olvidar tareas importantes.</div>
            <a href="{{ route('admin.crm.reminders.create') }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nuevo Recordatorio
            </a>
        </div>
        @endforelse

        @if($reminders->hasPages())
        <div id="pagination-wrap" style="padding:14px 18px; border-top:1px solid #f5f5f5;">
            {{ $reminders->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
