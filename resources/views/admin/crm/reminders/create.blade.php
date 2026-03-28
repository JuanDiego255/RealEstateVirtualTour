@extends('admin.main')
@section('title', 'Nuevo Recordatorio')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-bell"></i> Nuevo Recordatorio</h4>
            <a href="{{ route('admin.crm.reminders.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.crm.reminders.store') }}" method="POST">
                    @csrf

                    @if($relatedItem)
                        <div class="alert alert-info">
                            <i class="fa fa-link"></i> Recordatorio relacionado con:
                            <strong>
                                @if($relatedType === 'lead')
                                    Lead: {{ $relatedItem->name }}
                                @elseif($relatedType === 'appointment')
                                    Cita: {{ $relatedItem->title }}
                                @endif
                            </strong>
                            <input type="hidden" name="remindable_type" value="{{ $relatedType === 'lead' ? 'App\\Lead' : 'App\\Appointment' }}">
                            <input type="hidden" name="remindable_id" value="{{ $relatedItem->id }}">
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Título <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="Ej: Llamar a cliente, Enviar propuesta...">
                                @error('title') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Prioridad <span class="text-danger">*</span></label>
                                <select name="priority" class="form-control" required>
                                    @foreach(\App\Reminder::getPriorities() as $key => $label)
                                        <option value="{{ $key }}" {{ old('priority', 'medium') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Detalles adicionales...">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="remind_at_date" class="form-control" value="{{ old('remind_at_date', now()->format('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Hora <span class="text-danger">*</span></label>
                                <input type="time" name="remind_at_time" class="form-control" value="{{ old('remind_at_time', '09:00') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="email_notification" name="email_notification" value="1" {{ old('email_notification', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="email_notification">Notificación por Email</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!$relatedItem && $leads->count() > 0)
                    <div class="form-group">
                        <label>Relacionar con Lead (opcional)</label>
                        <select name="remindable_id" class="form-control">
                            <option value="">-- Ninguno --</option>
                            @foreach($leads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="remindable_type" value="App\Lead">
                    </div>
                    @endif

                    <hr>

                    <h5 class="mb-3"><i class="fa fa-refresh"></i> Recurrencia (opcional)</h5>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_recurring">Es recurrente</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Frecuencia</label>
                                <select name="recurrence_type" class="form-control">
                                    @foreach(\App\Reminder::getRecurrenceTypes() as $key => $label)
                                        <option value="{{ $key }}" {{ old('recurrence_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Cada</label>
                                <input type="number" name="recurrence_interval" class="form-control" value="{{ old('recurrence_interval', 1) }}" min="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Hasta (opcional)</label>
                                <input type="date" name="recurrence_ends_at" class="form-control" value="{{ old('recurrence_ends_at') }}">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('admin.crm.reminders.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Crear Recordatorio</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
