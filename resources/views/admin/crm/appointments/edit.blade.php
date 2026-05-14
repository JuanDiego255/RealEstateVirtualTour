@extends('admin.main')
@section('title', 'Editar Cita')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-edit"></i> Editar Cita</h4>
            <a href="{{ request('_back') ?: route('admin.crm.appointments.show', $appointment) }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.crm.appointments.update', $appointment) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Título <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $appointment->title) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo <span class="text-danger">*</span></label>
                                <select name="type" class="form-control" required>
                                    @foreach(\App\Appointment::getTypes() as $key => $label)
                                        <option value="{{ $key }}" {{ old('type', $appointment->type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="starts_at_date" class="form-control" value="{{ old('starts_at_date', $appointment->starts_at->format('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Hora <span class="text-danger">*</span></label>
                                <input type="time" name="starts_at_time" class="form-control" value="{{ old('starts_at_time', $appointment->starts_at->format('H:i')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Duración (minutos) <span class="text-danger">*</span></label>
                                <select name="duration" class="form-control" required>
                                    @php $currentDuration = $appointment->duration_in_minutes; @endphp
                                    <option value="30" {{ $currentDuration == 30 ? 'selected' : '' }}>30 minutos</option>
                                    <option value="60" {{ $currentDuration == 60 ? 'selected' : '' }}>1 hora</option>
                                    <option value="90" {{ $currentDuration == 90 ? 'selected' : '' }}>1.5 horas</option>
                                    <option value="120" {{ $currentDuration == 120 ? 'selected' : '' }}>2 horas</option>
                                    <option value="180" {{ $currentDuration == 180 ? 'selected' : '' }}>3 horas</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Estado <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    @foreach(\App\Appointment::getStatuses() as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', $appointment->status) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Cliente</h5>

                            <div class="form-group">
                                <label>Lead Asociado</label>
                                <select name="lead_id" class="form-control">
                                    <option value="">-- Ninguno --</option>
                                    @foreach($leads as $lead)
                                        <option value="{{ $lead->id }}" {{ old('lead_id', $appointment->lead_id) == $lead->id ? 'selected' : '' }}>
                                            {{ $lead->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Nombre del Cliente</label>
                                <input type="text" name="client_name" class="form-control" value="{{ old('client_name', $appointment->client_name) }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="text" name="client_phone" class="form-control" value="{{ old('client_phone', $appointment->client_phone) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="client_email" class="form-control" value="{{ old('client_email', $appointment->client_email) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5 class="mb-3">Ubicación</h5>

                            <div class="form-group">
                                <label>Lugar</label>
                                <input type="text" name="location" class="form-control" value="{{ old('location', $appointment->location) }}">
                            </div>

                            <div class="form-group">
                                <label>Dirección</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address', $appointment->address) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Propiedad</label>
                                <select name="property_id" class="form-control">
                                    <option value="">-- Ninguna --</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}" {{ old('property_id', $appointment->property_id) == $property->id ? 'selected' : '' }}>{{ $property->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Vehículo</label>
                                <select name="vehicle_id" class="form-control">
                                    <option value="">-- Ninguno --</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $appointment->vehicle_id) == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Agente</label>
                                <select name="user_id" class="form-control">
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ old('user_id', $appointment->user_id) == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notas / Descripción</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $appointment->description) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Color</label>
                                <input type="color" name="color" class="form-control" value="{{ old('color', $appointment->color ?? '#4CAF50') }}" style="height: 38px;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Recordatorio</label>
                                <select name="reminder_minutes" class="form-control">
                                    <option value="15" {{ $appointment->reminder_minutes == 15 ? 'selected' : '' }}>15 minutos antes</option>
                                    <option value="30" {{ $appointment->reminder_minutes == 30 ? 'selected' : '' }}>30 minutos antes</option>
                                    <option value="60" {{ $appointment->reminder_minutes == 60 ? 'selected' : '' }}>1 hora antes</option>
                                    <option value="120" {{ $appointment->reminder_minutes == 120 ? 'selected' : '' }}>2 horas antes</option>
                                    <option value="1440" {{ $appointment->reminder_minutes == 1440 ? 'selected' : '' }}>1 día antes</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <form action="{{ route('admin.crm.appointments.destroy', $appointment) }}" method="POST" onsubmit="return confirm('¿Eliminar esta cita?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Eliminar</button>
                        </form>
                        <div>
                            <a href="{{ request('_back') ?: route('admin.crm.appointments.show', $appointment) }}" class="btn btn-secondary mr-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar Cambios</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
