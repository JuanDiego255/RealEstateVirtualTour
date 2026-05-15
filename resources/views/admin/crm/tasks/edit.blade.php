@extends('admin.main')
@section('title', 'Editar Tarea')
@section('content')

    @include('admin.crm.layouts._crm-styles')

    <div class="crm-page">

        {{-- Header --}}
        <div class="crm-page-header">
            <div>
                <h2><i class="fa fa-pencil"></i> Editar Tarea</h2>
                <div class="sub">
                    @php $typeInfo = \App\LeadTask::getTypes()[$task->type] ?? ['icon' => 'fa-tasks', 'label' => $task->type]; @endphp
                    <i class="fa {{ $typeInfo['icon'] }}"></i> {{ $typeInfo['label'] }}
                    &nbsp;·&nbsp;
                    <span class="crm-badge {{ $task->priority }}">
                        {{ \App\LeadTask::getPriorities()[$task->priority]['label'] }}</span>
                </div>
            </div>
            <div class="actions">
                @php $backUrl = request('_back') ?: (isset($task->lead) ? route('admin.crm.leads.show', $task->lead) : route('admin.crm.tasks.index')); @endphp
                <a href="{{ $backUrl }}" class="action-btn secondary">
                    <i class="fa fa-arrow-left"></i> Volver
                </a>
                @if (!in_array($task->status, ['completed', 'cancelled']))
                    <button type="button" class="action-btn success" data-toggle="modal" data-target="#completeModal">
                        <i class="fa fa-check-circle"></i> Marcar Completada
                    </button>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3">
                <strong>{{ session('success') }}</strong>
                <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0" style="font-size:13px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">

                <form action="{{ route('admin.crm.tasks.update', $task) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="_back" value="{{ request('_back') }}">

                    <div class="dashboard-card">
                        <div class="dc-header">
                            <h5><i class="fa fa-tasks"></i> Información de la Tarea</h5>
                            @php
                                $statusBadge = match ($task->status) {
                                    'pending' => 'new',
                                    'in_progress' => 'contacted',
                                    'completed' => 'won',
                                    'cancelled' => 'lost',
                                    default => 'new',
                                };
                            @endphp
                            <span class="crm-badge {{ $statusBadge }}">
                                {{ \App\LeadTask::getStatuses()[$task->status] ?? $task->status }}
                            </span>
                        </div>
                        <div class="dc-body">

                            {{-- Title --}}
                            <div class="form-group">
                                <label style="font-weight:600;font-size:13px;color:#1a1a2e;">
                                    Título <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $task->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Lead (read-only) --}}
                            <div class="form-group">
                                <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Lead</label>
                                @if ($task->lead)
                                    <div
                                        style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;display:flex;align-items:center;gap:12px;">
                                        <div
                                            style="width:38px;height:38px;border-radius:50%;background:#1a1a2e;color:#c2ac1f;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">
                                            {{ strtoupper(substr($task->lead->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.crm.leads.show', $task->lead) }}"
                                                style="font-weight:600;font-size:14px;color:#1a1a2e;text-decoration:none;">
                                                {{ $task->lead->name }}
                                            </a>
                                            <div style="margin-top:3px;">
                                                <span
                                                    class="crm-badge {{ $task->lead->status }}">{{ $task->lead->status_label }}</span>
                                                @if ($task->lead->phone)
                                                    <span style="font-size:11px;color:#888;margin-left:8px;"><i
                                                            class="fa fa-phone"></i> {{ $task->lead->phone }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="row">
                                {{-- Type --}}
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label style="font-weight:600;font-size:13px;color:#1a1a2e;">
                                            Tipo <span class="text-danger">*</span>
                                        </label>
                                        <select name="type" class="form-control @error('type') is-invalid @enderror"
                                            required>
                                            @foreach (\App\LeadTask::getTypes() as $key => $info)
                                                <option value="{{ $key }}"
                                                    {{ old('type', $task->type) === $key ? 'selected' : '' }}>
                                                    {{ $info['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Priority --}}
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label style="font-weight:600;font-size:13px;color:#1a1a2e;">
                                            Prioridad <span class="text-danger">*</span>
                                        </label>
                                        <select name="priority" class="form-control @error('priority') is-invalid @enderror"
                                            required>
                                            @foreach (\App\LeadTask::getPriorities() as $key => $priority)
                                                <option value="{{ $key }}"
                                                    {{ old('priority', $task->priority) == $key ? 'selected' : '' }}>
                                                    {{ $priority['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Estado</label>
                                        <select name="status" class="form-control">
                                            @foreach (\App\LeadTask::getStatuses() as $key => $label)
                                                <option value="{{ $key }}"
                                                    {{ old('status', $task->status) === $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Due At --}}
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Fecha límite</label>
                                        <input type="datetime-local" name="due_at"
                                            class="form-control @error('due_at') is-invalid @enderror"
                                            value="{{ old('due_at', $task->due_at ? $task->due_at->format('Y-m-d\TH:i') : '') }}">
                                        @error('due_at')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Assigned To --}}
                            <div class="form-group">
                                <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Asignar a</label>
                                <select name="assigned_to" class="form-control @error('assigned_to') is-invalid @enderror">
                                    <option value="">— Sin asignar —</option>
                                    @foreach ($agents as $agent)
                                        <option value="{{ $agent->id }}"
                                            {{ old('assigned_to', $task->assigned_to) == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div class="form-group">
                                <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Descripción</label>
                                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Detalles, instrucciones, contexto...">{{ old('description', $task->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Completion Notes (if completed) --}}
                            @if ($task->status === 'completed')
                                <div class="form-group"
                                    style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px;">
                                    <label style="font-weight:600;font-size:13px;color:#065f46;">
                                        <i class="fa fa-check-circle"></i> Notas de cierre
                                    </label>
                                    <textarea name="completion_notes" rows="2" class="form-control" style="background:#fff;border-color:#bbf7d0;"
                                        placeholder="Resultado de la tarea completada...">{{ old('completion_notes', $task->completion_notes) }}</textarea>
                                    @if ($task->completed_at)
                                        <small style="color:#22c55e;font-size:11px;margin-top:4px;display:block;">
                                            <i class="fa fa-clock-o"></i> Completada el
                                            {{ $task->completed_at->format('d/m/Y \a \l\a\s H:i') }}
                                        </small>
                                    @endif
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- Actions --}}
                    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:6px;">
                        <a href="{{ $backUrl }}" class="action-btn secondary">
                            <i class="fa fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="action-btn primary">
                            <i class="fa fa-save"></i> Guardar Cambios
                        </button>
                    </div>

                </form>

            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">

                {{-- Task meta --}}
                <div class="dashboard-card" style="margin-bottom:18px;">
                    <div class="dc-header">
                        <h5><i class="fa fa-info-circle"></i> Detalles</h5>
                    </div>
                    <div class="dc-body" style="padding:14px 16px;">
                        <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;">
                            <div>
                                <div
                                    style="font-size:11px;color:#888;text-transform:uppercase;font-weight:600;margin-bottom:2px;">
                                    Creada por</div>
                                <div style="color:#1a1a2e;">{{ $task->creator->name ?? '—' }}</div>
                            </div>
                            <div>
                                <div
                                    style="font-size:11px;color:#888;text-transform:uppercase;font-weight:600;margin-bottom:2px;">
                                    Fecha de creación</div>
                                <div style="color:#1a1a2e;">{{ $task->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            @if ($task->due_at)
                                <div>
                                    <div
                                        style="font-size:11px;color:#888;text-transform:uppercase;font-weight:600;margin-bottom:2px;">
                                        Fecha límite</div>
                                    @php
                                        $dueCls = $task->is_overdue
                                            ? 'fu-overdue'
                                            : ($task->is_due_today
                                                ? 'fu-today'
                                                : 'fu-ok');
                                    @endphp
                                    <div class="{{ $dueCls }}">
                                        <i class="fa fa-calendar-o"></i>
                                        {{ $task->due_at->format('d/m/Y H:i') }}
                                        @if ($task->is_overdue)
                                            <span style="font-size:11px;">(Vencida)</span>
                                        @elseif($task->is_due_today)
                                            <span style="font-size:11px;">(Hoy)</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if ($task->assignee)
                                <div>
                                    <div
                                        style="font-size:11px;color:#888;text-transform:uppercase;font-weight:600;margin-bottom:2px;">
                                        Asignado a</div>
                                    <div style="color:#1a1a2e;display:flex;align-items:center;gap:6px;">
                                        <div
                                            style="width:24px;height:24px;border-radius:50%;background:#1a1a2e;color:#c2ac1f;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;">
                                            {{ strtoupper(substr($task->assignee->name, 0, 2)) }}
                                        </div>
                                        {{ $task->assignee->name }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Quick links --}}
                <div class="dashboard-card" style="margin-bottom:18px;">
                    <div class="dc-header">
                        <h5><i class="fa fa-bolt"></i> Acciones rápidas</h5>
                    </div>
                    <div class="dc-body" style="padding:10px 12px;display:flex;flex-direction:column;gap:4px;">
                        @if ($task->lead)
                            <a href="{{ route('admin.crm.leads.show', $task->lead) }}" class="action-btn secondary"
                                style="justify-content:flex-start;">
                                <i class="fa fa-user"></i> Ver lead
                            </a>
                            <a href="{{ route('admin.crm.tasks.create', ['lead_id' => $task->lead_id]) }}"
                                class="action-btn secondary" style="justify-content:flex-start;">
                                <i class="fa fa-plus"></i> Nueva tarea para este lead
                            </a>
                        @endif
                        <a href="{{ route('admin.crm.tasks.index') }}" class="action-btn secondary"
                            style="justify-content:flex-start;">
                            <i class="fa fa-list"></i> Ver todas las tareas
                        </a>
                    </div>
                </div>

                {{-- Delete --}}
                <div class="dashboard-card">
                    <div class="dc-header">
                        <h5 style="color:#991b1b;"><i class="fa fa-trash" style="color:#ef4444;"></i> Zona peligrosa</h5>
                    </div>
                    <div class="dc-body">
                        <p style="font-size:13px;color:#666;margin-bottom:12px;">Eliminar esta tarea de forma permanente.
                        </p>
                        <form action="{{ route('admin.crm.tasks.destroy', $task) }}" method="POST"
                            onsubmit="return confirm('¿Estás seguro de eliminar esta tarea? Esta acción no se puede deshacer.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn danger" style="width:100%;justify-content:center;">
                                <i class="fa fa-trash"></i> Eliminar tarea
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- Complete Modal --}}
    @if (!in_array($task->status, ['completed', 'cancelled']))
        <div class="modal fade" id="completeModal" tabindex="-1">
            <div class="modal-dialog modal-sm">
                <div class="modal-content" style="border-radius:14px;overflow:hidden;">
                    <form action="{{ route('admin.crm.tasks.complete', $task) }}" method="POST">
                        @csrf
                        <div class="modal-header" style="background:#1a1a2e;color:#fff;border:none;padding:14px 18px;">
                            <h5 class="modal-title" style="font-size:15px;margin:0;">
                                <i class="fa fa-check-circle" style="color:#c2ac1f;"></i> Completar Tarea
                            </h5>
                            <button type="button" class="close" data-dismiss="modal"
                                style="color:#fff;opacity:.7;">×</button>
                        </div>
                        <div class="modal-body" style="padding:18px;">
                            <div class="form-group" style="margin-bottom:0;">
                                <label style="font-size:12px;color:#666;font-weight:600;">Notas de cierre
                                    (opcional)</label>
                                <textarea name="completion_notes" rows="3" class="form-control" placeholder="Resultado, observaciones..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer" style="border:none;padding:10px 18px 16px;">
                            <button type="button" class="action-btn secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="action-btn primary">
                                <i class="fa fa-check"></i> Marcar Completada
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection
