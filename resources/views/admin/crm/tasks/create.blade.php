@extends('admin.main')
@section('title', 'Nueva Tarea')
@section('content')

@include('admin.crm.layouts._crm-styles')

<div class="crm-page">

    {{-- Header --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-plus-circle"></i> Nueva Tarea</h2>
            <div class="sub">Crear tarea de seguimiento para un lead</div>
        </div>
        <div class="actions">
            @php $backUrl = request('_back') ?: (isset($lead) ? route('admin.crm.leads.show', $lead) : route('admin.crm.tasks.index')); @endphp
            <a href="{{ $backUrl }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0" style="font-size:13px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.crm.tasks.store') }}" method="POST">
        @csrf
        <input type="hidden" name="_back" value="{{ request('_back') }}">

        <div class="dashboard-card">
            <div class="dc-header">
                <h5><i class="fa fa-tasks"></i> Información de la Tarea</h5>
            </div>
            <div class="dc-body">

                {{-- Title --}}
                <div class="form-group">
                    <label style="font-weight:600;font-size:13px;color:#1a1a2e;">
                        Título <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}"
                           placeholder="Ej: Llamar al cliente para agendar visita"
                           required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Lead --}}
                <div class="form-group">
                    <label style="font-weight:600;font-size:13px;color:#1a1a2e;">
                        Lead <span class="text-danger">*</span>
                    </label>
                    @if(isset($lead))
                        {{-- Pre-filled lead info card --}}
                        <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;display:flex;align-items:center;gap:12px;">
                            <div style="width:38px;height:38px;border-radius:50%;background:#1a1a2e;color:#c2ac1f;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">
                                {{ strtoupper(substr($lead->name, 0, 2)) }}
                            </div>
                            <div>
                                <div style="font-weight:600;font-size:14px;color:#1a1a2e;">{{ $lead->name }}</div>
                                <div style="margin-top:3px;">
                                    <span class="crm-badge {{ $lead->status }}">{{ $lead->status_label }}</span>
                                    @if($lead->phone)
                                        <span style="font-size:11px;color:#888;margin-left:8px;"><i class="fa fa-phone"></i> {{ $lead->phone }}</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('admin.crm.tasks.create') }}" class="action-btn secondary" style="margin-left:auto;padding:4px 10px;font-size:12px;">
                                <i class="fa fa-exchange"></i> Cambiar
                            </a>
                        </div>
                    @else
                        <select name="lead_id" class="form-control @error('lead_id') is-invalid @enderror" required>
                            <option value="">— Seleccionar lead —</option>
                            @foreach($leads as $l)
                                <option value="{{ $l->id }}" {{ old('lead_id') == $l->id ? 'selected' : '' }}>
                                    {{ $l->name }}
                                    @if($l->phone) ({{ $l->phone }}) @endif
                                    — {{ \App\Lead::getStatuses()[$l->status] ?? $l->status }}
                                </option>
                            @endforeach
                        </select>
                        @error('lead_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="row">
                    {{-- Type --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label style="font-weight:600;font-size:13px;color:#1a1a2e;">
                                Tipo <span class="text-danger">*</span>
                            </label>
                            <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                                @foreach(\App\LeadTask::getTypes() as $key => $info)
                                    <option value="{{ $key }}" {{ old('type', 'call') === $key ? 'selected' : '' }}>
                                        {{ $info['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Priority --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label style="font-weight:600;font-size:13px;color:#1a1a2e;">
                                Prioridad <span class="text-danger">*</span>
                            </label>
                            <select name="priority" class="form-control @error('priority') is-invalid @enderror" required>
                                @foreach(\App\LeadTask::getPriorities() as $key => $label)
                                    <option value="{{ $key }}" {{ old('priority', 'medium') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Due At --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Fecha límite</label>
                            <input type="datetime-local" name="due_at"
                                   class="form-control @error('due_at') is-invalid @enderror"
                                   value="{{ old('due_at') }}">
                            @error('due_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Assigned To --}}
                <div class="form-group">
                    <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Asignar a</label>
                    <select name="assigned_to" class="form-control @error('assigned_to') is-invalid @enderror">
                        <option value="">— Sin asignar —</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('assigned_to', auth()->id()) == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Description --}}
                <div class="form-group">
                    <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Descripción</label>
                    <textarea name="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Detalles, instrucciones, contexto...">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:6px;">
            <a href="{{ $backUrl }}" class="action-btn secondary">
                <i class="fa fa-times"></i> Cancelar
            </a>
            <button type="submit" class="action-btn primary">
                <i class="fa fa-save"></i> Guardar Tarea
            </button>
        </div>

    </form>

</div>

@endsection
