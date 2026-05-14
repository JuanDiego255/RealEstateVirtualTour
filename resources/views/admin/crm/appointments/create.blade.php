@extends('admin.main')
@section('title', 'Nueva Cita')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-calendar-plus-o"></i> Nueva Cita</h4>
            <a href="{{ request('_back') ?: route('admin.crm.appointments.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.crm.appointments.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Título <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="Ej: Visita a Casa en Escazú">
                                @error('title') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo <span class="text-danger">*</span></label>
                                <select name="type" class="form-control" required>
                                    @foreach(\App\Appointment::getTypes() as $key => $label)
                                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="starts_at_date" class="form-control" value="{{ old('starts_at_date', $defaultDate) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Hora <span class="text-danger">*</span></label>
                                <input type="time" name="starts_at_time" class="form-control" value="{{ old('starts_at_time', $defaultTime) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Duración (minutos) <span class="text-danger">*</span></label>
                                <select name="duration" class="form-control" required>
                                    <option value="30" {{ old('duration') == 30 ? 'selected' : '' }}>30 minutos</option>
                                    <option value="60" {{ old('duration', 60) == 60 ? 'selected' : '' }}>1 hora</option>
                                    <option value="90" {{ old('duration') == 90 ? 'selected' : '' }}>1.5 horas</option>
                                    <option value="120" {{ old('duration') == 120 ? 'selected' : '' }}>2 horas</option>
                                    <option value="180" {{ old('duration') == 180 ? 'selected' : '' }}>3 horas</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Recordatorio</label>
                                <select name="reminder_minutes" class="form-control">
                                    <option value="15">15 minutos antes</option>
                                    <option value="30">30 minutos antes</option>
                                    <option value="60" selected>1 hora antes</option>
                                    <option value="120">2 horas antes</option>
                                    <option value="1440">1 día antes</option>
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
                                <select name="lead_id" class="form-control" id="leadSelect">
                                    <option value="">-- Seleccionar Lead --</option>
                                    @foreach($leads as $lead)
                                        <option value="{{ $lead->id }}"
                                            data-name="{{ $lead->name }}"
                                            data-phone="{{ $lead->phone }}"
                                            data-email="{{ $lead->email }}"
                                            {{ old('lead_id', $selectedLead->id ?? '') == $lead->id ? 'selected' : '' }}>
                                            {{ $lead->name }} {{ $lead->phone ? '(' . $lead->phone . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Si seleccionas un lead, se usarán sus datos de contacto</small>
                            </div>

                            <div id="manualClientFields">
                                <div class="form-group">
                                    <label>Nombre del Cliente</label>
                                    <input type="text" name="client_name" class="form-control" value="{{ old('client_name', $selectedLead->name ?? '') }}">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Teléfono</label>
                                            <input type="text" name="client_phone" class="form-control" value="{{ old('client_phone', $selectedLead->phone ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="client_email" class="form-control" value="{{ old('client_email', $selectedLead->email ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5 class="mb-3">Ubicación</h5>

                            <div class="form-group">
                                <label>Lugar</label>
                                <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="Ej: Oficina, Casa del cliente, Propiedad">
                            </div>

                            <div class="form-group">
                                <label>Dirección</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="Dirección detallada">{{ old('address') }}</textarea>
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
                                        <option value="{{ $property->id }}" {{ old('property_id', $selectedLead->property_id ?? '') == $property->id ? 'selected' : '' }}>{{ $property->title }}</option>
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
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $selectedLead->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Agente Asignado</label>
                                <select name="user_id" class="form-control">
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ old('user_id', auth()->id()) == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notas / Descripción</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Detalles adicionales sobre la cita...">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Color en Calendario</label>
                                <input type="color" name="color" class="form-control" value="{{ old('color', '#4CAF50') }}" style="height: 38px;">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end">
                        <a href="{{ request('_back') ?: route('admin.crm.appointments.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Programar Cita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    document.getElementById('leadSelect').addEventListener('change', function() {
        var option = this.options[this.selectedIndex];
        if (this.value) {
            document.querySelector('input[name="client_name"]').value = option.dataset.name || '';
            document.querySelector('input[name="client_phone"]').value = option.dataset.phone || '';
            document.querySelector('input[name="client_email"]').value = option.dataset.email || '';
        }
    });
</script>
@endpush
