@extends('admin.main')
@section('title', 'Editar Recordatorio')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-edit"></i> Editar Recordatorio</h4>
            <a href="{{ request('_back') ?: route('admin.crm.reminders.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.crm.reminders.update', $reminder) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if($reminder->remindable)
                        <div class="alert alert-info">
                            <i class="fa fa-link"></i> Relacionado con: <strong>{{ $reminder->related_item_name }}</strong>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Título <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $reminder->title) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Prioridad <span class="text-danger">*</span></label>
                                <select name="priority" class="form-control" required>
                                    @foreach(\App\Reminder::getPriorities() as $key => $label)
                                        <option value="{{ $key }}" {{ old('priority', $reminder->priority) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $reminder->description) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="remind_at_date" class="form-control" value="{{ old('remind_at_date', $reminder->remind_at->format('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Hora <span class="text-danger">*</span></label>
                                <input type="time" name="remind_at_time" class="form-control" value="{{ old('remind_at_time', $reminder->remind_at->format('H:i')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="email_notification" name="email_notification" value="1" {{ old('email_notification', $reminder->email_notification) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="email_notification">Notificación por Email</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="fa fa-refresh"></i> Recurrencia</h5>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring', $reminder->is_recurring) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_recurring">Es recurrente</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Frecuencia</label>
                                <select name="recurrence_type" class="form-control">
                                    @foreach(\App\Reminder::getRecurrenceTypes() as $key => $label)
                                        <option value="{{ $key }}" {{ old('recurrence_type', $reminder->recurrence_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Cada</label>
                                <input type="number" name="recurrence_interval" class="form-control" value="{{ old('recurrence_interval', $reminder->recurrence_interval ?? 1) }}" min="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Hasta</label>
                                <input type="date" name="recurrence_ends_at" class="form-control" value="{{ old('recurrence_ends_at', $reminder->recurrence_ends_at ? $reminder->recurrence_ends_at->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <form action="{{ route('admin.crm.reminders.destroy', $reminder) }}" method="POST" onsubmit="return confirm('¿Eliminar este recordatorio?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Eliminar</button>
                        </form>
                        <div>
                            <a href="{{ request('_back') ?: route('admin.crm.reminders.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar Cambios</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
