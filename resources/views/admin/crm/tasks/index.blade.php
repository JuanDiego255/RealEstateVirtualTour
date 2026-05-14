@extends('admin.main')
@section('title', 'Tareas del CRM')
@section('content')

@include('admin.crm.layouts._crm-styles')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        <strong>{{ session('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
    </div>
@endif

<div class="crm-page">

    {{-- Header --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-tasks"></i> Tareas del CRM</h2>
            <div class="sub">Gestión de tareas y seguimiento de leads</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.tasks.create') }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nueva Tarea
            </a>
            <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary">
                <i class="fa fa-users"></i> Ver Leads
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
        <div class="stat-card">
            <div class="sc-icon gold"><i class="fa fa-tasks"></i></div>
            <div class="sc-value">{{ $stats['total'] }}</div>
            <div class="sc-label">Total tareas</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon blue"><i class="fa fa-clock-o"></i></div>
            <div class="sc-value">{{ $stats['pending'] }}</div>
            <div class="sc-label">Pendientes</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon red"><i class="fa fa-exclamation-triangle"></i></div>
            <div class="sc-value">{{ $stats['overdue'] }}</div>
            <div class="sc-label">Vencidas</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon orange"><i class="fa fa-calendar"></i></div>
            <div class="sc-value">{{ $stats['today'] }}</div>
            <div class="sc-label">Para hoy</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon green"><i class="fa fa-check-circle"></i></div>
            <div class="sc-value">{{ $stats['completed'] }}</div>
            <div class="sc-label">Completadas</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="dashboard-card" style="margin-bottom:18px;">
        <div class="dc-body" style="padding:14px 18px;">
            <form method="GET" action="{{ route('admin.crm.tasks.index') }}" class="d-flex flex-wrap gap-2 align-items-end" style="gap:10px;">
                <div style="flex:0 0 auto;">
                    <label style="font-size:11px;color:#888;display:block;margin-bottom:3px;text-transform:uppercase;font-weight:600;">Estado</label>
                    <select name="status" class="form-control form-control-sm" style="min-width:140px;">
                        <option value="">Todos los estados</option>
                        @foreach(\App\LeadTask::getStatuses() as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:0 0 auto;">
                    <label style="font-size:11px;color:#888;display:block;margin-bottom:3px;text-transform:uppercase;font-weight:600;">Asignado a</label>
                    <select name="assigned_to" class="form-control form-control-sm" style="min-width:160px;">
                        <option value="">Todos los agentes</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ ($filters['assigned_to'] ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:0 0 auto;">
                    <label style="font-size:11px;color:#888;display:block;margin-bottom:3px;text-transform:uppercase;font-weight:600;">Vencimiento</label>
                    <select name="due_filter" class="form-control form-control-sm" style="min-width:140px;">
                        <option value="">Todos</option>
                        <option value="today"    {{ ($filters['due_filter'] ?? '') === 'today'    ? 'selected' : '' }}>Hoy</option>
                        <option value="overdue"  {{ ($filters['due_filter'] ?? '') === 'overdue'  ? 'selected' : '' }}>Vencidas</option>
                        <option value="upcoming" {{ ($filters['due_filter'] ?? '') === 'upcoming' ? 'selected' : '' }}>Próximas</option>
                    </select>
                </div>
                <div style="flex:1 1 200px;">
                    <label style="font-size:11px;color:#888;display:block;margin-bottom:3px;text-transform:uppercase;font-weight:600;">Buscar</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Buscar por título o descripción..."
                           value="{{ $filters['search'] ?? '' }}">
                </div>
                <div style="flex:0 0 auto;display:flex;gap:6px;align-items:flex-end;">
                    <button type="submit" class="action-btn primary" style="padding:6px 14px;">
                        <i class="fa fa-search"></i> Filtrar
                    </button>
                    @if(array_filter($filters))
                        <a href="{{ route('admin.crm.tasks.index') }}" class="action-btn secondary" style="padding:6px 14px;">
                            <i class="fa fa-times"></i> Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="dashboard-card">
        <div class="dc-header">
            <h5><i class="fa fa-list"></i> Tareas ({{ $tasks->total() }})</h5>
            <span style="font-size:12px;color:#888;">{{ $tasks->firstItem() }}–{{ $tasks->lastItem() }} de {{ $tasks->total() }}</span>
        </div>
        <div class="dc-body" style="padding:0;">
            @if($tasks->isEmpty())
                <div style="text-align:center;padding:40px;color:#999;">
                    <i class="fa fa-tasks" style="font-size:36px;opacity:.3;display:block;margin-bottom:10px;"></i>
                    No hay tareas que coincidan con los filtros.
                    <br><a href="{{ route('admin.crm.tasks.create') }}" class="action-btn primary" style="margin-top:14px;display:inline-flex;">
                        <i class="fa fa-plus"></i> Crear primera tarea
                    </a>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Tarea</th>
                                <th>Lead</th>
                                <th>Asignado</th>
                                <th>Prioridad</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                                @php
                                    $rowClass = '';
                                    if ($task->is_overdue)   $rowClass = 'row-danger';
                                    elseif ($task->is_due_today) $rowClass = 'row-warning';

                                    $statusBadge = match($task->status) {
                                        'pending'     => 'new',
                                        'in_progress' => 'contacted',
                                        'completed'   => 'won',
                                        'cancelled'   => 'lost',
                                        default       => 'new',
                                    };
                                    $statusLabel = \App\LeadTask::getStatuses()[$task->status] ?? $task->status;

                                    $typeInfo = \App\LeadTask::getTypes()[$task->type] ?? ['icon' => 'fa-tasks', 'label' => $task->type, 'color' => 'gray'];
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span style="color:#{{ $typeInfo['color'] === 'blue' ? '3b82f6' : ($typeInfo['color'] === 'green' ? '22c55e' : ($typeInfo['color'] === 'orange' ? 'f97316' : ($typeInfo['color'] === 'purple' ? 'a855f7' : ($typeInfo['color'] === 'yellow' ? 'eab308' : ($typeInfo['color'] === 'indigo' ? '6366f1' : '94a3b8'))))) }};">
                                                <i class="fa {{ $typeInfo['icon'] }}"></i>
                                            </span>
                                            <div>
                                                <div style="font-weight:600;font-size:13px;color:#1a1a2e;">{{ Str::limit($task->title, 45) }}</div>
                                                @if($task->description)
                                                    <div style="font-size:11px;color:#888;">{{ Str::limit($task->description, 60) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($task->lead)
                                            <a href="{{ route('admin.crm.leads.show', $task->lead) }}"
                                               style="color:#1a1a2e;font-weight:500;font-size:13px;text-decoration:none;">
                                                {{ $task->lead->name }}
                                            </a>
                                            <div style="margin-top:2px;">
                                                <span class="crm-badge {{ $task->lead->status }}">{{ $task->lead->status_label }}</span>
                                            </div>
                                        @else
                                            <span style="color:#bbb;font-size:12px;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->assignee)
                                            <div style="display:flex;align-items:center;gap:6px;">
                                                <div style="width:28px;height:28px;border-radius:50%;background:#1a1a2e;color:#c2ac1f;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">
                                                    {{ strtoupper(substr($task->assignee->name, 0, 2)) }}
                                                </div>
                                                <span style="font-size:12px;color:#444;">{{ Str::limit($task->assignee->name, 18) }}</span>
                                            </div>
                                        @else
                                            <span style="color:#bbb;font-size:12px;">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="crm-badge {{ $task->priority }}">
                                            {{ \App\LeadTask::getPriorities()[$task->priority] ?? $task->priority }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($task->due_at)
                                            @php
                                                $dueCls = $task->is_overdue ? 'fu-overdue' : ($task->is_due_today ? 'fu-today' : 'fu-ok');
                                                $dueIcon = $task->is_overdue ? 'fa-exclamation-circle' : ($task->is_due_today ? 'fa-clock-o' : 'fa-calendar-o');
                                            @endphp
                                            <div class="{{ $dueCls }}">
                                                <i class="fa {{ $dueIcon }}"></i>
                                                {{ $task->due_at->format('d/m/Y') }}
                                            </div>
                                            <div style="font-size:11px;color:#aaa;">{{ $task->due_at->format('H:i') }}</div>
                                        @else
                                            <span style="color:#bbb;font-size:12px;">Sin fecha</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="crm-badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                                            @if(!in_array($task->status, ['completed', 'cancelled']))
                                                <button type="button"
                                                        class="action-btn success btn-complete-task"
                                                        style="padding:4px 9px;font-size:11px;"
                                                        data-id="{{ $task->id }}"
                                                        data-url="{{ route('admin.crm.tasks.complete', $task) }}"
                                                        title="Marcar completada">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('admin.crm.tasks.edit', $task) }}" class="action-btn view" title="Editar">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.crm.tasks.destroy', $task) }}" method="POST"
                                                  onsubmit="return confirm('¿Eliminar esta tarea?');" style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="action-btn danger" style="padding:4px 9px;font-size:11px;" title="Eliminar">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($tasks->hasPages())
                    <div id="pagination-wrap" style="padding:14px 18px;border-top:1px solid #f0f0f0;">
                        {{ $tasks->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

</div>

{{-- Complete Task Modal --}}
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header" style="background:#1a1a2e;color:#fff;border:none;padding:14px 18px;">
                <h5 class="modal-title" style="font-size:15px;margin:0;"><i class="fa fa-check-circle" style="color:#c2ac1f;"></i> Completar Tarea</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.7;">×</button>
            </div>
            <div class="modal-body" style="padding:18px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-size:12px;color:#666;font-weight:600;">Notas de cierre (opcional)</label>
                    <textarea id="completion-notes" rows="3" class="form-control" placeholder="Resultado, observaciones..."></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border:none;padding:10px 18px 16px;">
                <button type="button" class="action-btn secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="action-btn primary" id="confirm-complete">
                    <i class="fa fa-check"></i> Marcar Completada
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let activeUrl = null;
    let activeRow = null;

    document.querySelectorAll('.btn-complete-task').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activeUrl = this.dataset.url;
            activeRow = this.closest('tr');
            document.getElementById('completion-notes').value = '';
            $('#completeModal').modal('show');
        });
    });

    document.getElementById('confirm-complete').addEventListener('click', function () {
        if (!activeUrl) return;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando...';

        fetch(activeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ completion_notes: document.getElementById('completion-notes').value })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                $('#completeModal').modal('hide');
                // Update row visually
                if (activeRow) {
                    activeRow.classList.remove('row-danger', 'row-warning');
                    const statusCell = activeRow.querySelector('td:nth-child(6)');
                    if (statusCell) statusCell.innerHTML = '<span class="crm-badge won">Completada</span>';
                    const actionsCell = activeRow.querySelector('td:nth-child(7)');
                    if (actionsCell) {
                        const completeBtn = actionsCell.querySelector('.btn-complete-task');
                        if (completeBtn) completeBtn.remove();
                    }
                }
            }
        })
        .catch(() => { location.reload(); })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check"></i> Marcar Completada';
        });
    });
});
</script>
@endpush

@endsection
