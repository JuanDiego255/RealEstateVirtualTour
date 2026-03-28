@extends('admin.main')
@section('title', 'Cita: ' . $appointment->title)
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">
                    <i class="fa fa-calendar"></i> {{ $appointment->title }}
                    <span class="badge badge-{{ $appointment->status_color }} ml-2">{{ $appointment->status_label }}</span>
                </h4>
                <small class="text-muted">{{ $appointment->type_label }} | {{ $appointment->starts_at->format('d/m/Y H:i') }} - {{ $appointment->ends_at->format('H:i') }}</small>
            </div>
            <div>
                <a href="{{ route('admin.crm.appointments.edit', $appointment) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Editar</a>
                <a href="{{ route('admin.crm.appointments.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Volver</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                {{-- Detalles de la cita --}}
                <div class="card mb-4">
                    <div class="card-header"><strong><i class="fa fa-info-circle"></i> Detalles</strong></div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted" width="40%">Tipo:</td><td>{{ $appointment->type_label }}</td></tr>
                            <tr><td class="text-muted">Fecha:</td><td>{{ $appointment->starts_at->format('l, d \d\e F \d\e Y') }}</td></tr>
                            <tr><td class="text-muted">Hora:</td><td>{{ $appointment->starts_at->format('H:i') }} - {{ $appointment->ends_at->format('H:i') }} ({{ $appointment->duration_in_minutes }} min)</td></tr>
                            <tr><td class="text-muted">Agente:</td><td>{{ $appointment->user->name ?? '-' }}</td></tr>
                            @if($appointment->location)
                            <tr><td class="text-muted">Ubicación:</td><td>{{ $appointment->location }}</td></tr>
                            @endif
                            @if($appointment->address)
                            <tr><td class="text-muted">Dirección:</td><td>{{ $appointment->address }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- Cliente --}}
                <div class="card mb-4">
                    <div class="card-header"><strong><i class="fa fa-user"></i> Cliente</strong></div>
                    <div class="card-body">
                        <h5>{{ $appointment->client_display_name }}</h5>
                        @if($appointment->client_display_phone)
                            <p class="mb-1"><i class="fa fa-phone text-muted"></i> <a href="tel:{{ $appointment->client_display_phone }}">{{ $appointment->client_display_phone }}</a></p>
                        @endif
                        @if($appointment->client_email || ($appointment->lead && $appointment->lead->email))
                            <p class="mb-1"><i class="fa fa-envelope text-muted"></i> {{ $appointment->client_email ?? $appointment->lead->email }}</p>
                        @endif
                        @if($appointment->lead)
                            <a href="{{ route('admin.crm.leads.show', $appointment->lead) }}" class="btn btn-sm btn-outline-info mt-2">
                                <i class="fa fa-user"></i> Ver Lead
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Propiedad/Vehículo --}}
                @if($appointment->property || $appointment->vehicle)
                <div class="card mb-4">
                    <div class="card-header"><strong><i class="fa fa-tag"></i> Relacionado</strong></div>
                    <div class="card-body">
                        @if($appointment->property)
                            <p><i class="fa fa-home text-primary"></i> <strong>Propiedad:</strong> {{ $appointment->property->title }}</p>
                        @endif
                        @if($appointment->vehicle)
                            <p><i class="fa fa-car text-primary"></i> <strong>Vehículo:</strong> {{ $appointment->vehicle->name }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-6">
                {{-- Notas --}}
                @if($appointment->description)
                <div class="card mb-4">
                    <div class="card-header"><strong><i class="fa fa-sticky-note"></i> Descripción</strong></div>
                    <div class="card-body">
                        <p class="mb-0">{{ $appointment->description }}</p>
                    </div>
                </div>
                @endif

                {{-- Acciones --}}
                <div class="card mb-4">
                    <div class="card-header"><strong><i class="fa fa-cogs"></i> Acciones</strong></div>
                    <div class="card-body">
                        @if($appointment->status === 'scheduled')
                            <form action="{{ route('admin.crm.appointments.update', $appointment) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="title" value="{{ $appointment->title }}">
                                <input type="hidden" name="type" value="{{ $appointment->type }}">
                                <input type="hidden" name="starts_at_date" value="{{ $appointment->starts_at->format('Y-m-d') }}">
                                <input type="hidden" name="starts_at_time" value="{{ $appointment->starts_at->format('H:i') }}">
                                <input type="hidden" name="duration" value="{{ $appointment->duration_in_minutes }}">
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn btn-success mb-2 btn-block">
                                    <i class="fa fa-check"></i> Confirmar Cita
                                </button>
                            </form>
                        @endif

                        @if(in_array($appointment->status, ['scheduled', 'confirmed', 'in_progress']))
                            <button type="button" class="btn btn-primary mb-2 btn-block" data-toggle="modal" data-target="#completeModal">
                                <i class="fa fa-check-circle"></i> Marcar como Completada
                            </button>

                            <form action="{{ route('admin.crm.appointments.cancel', $appointment) }}" method="POST" onsubmit="return confirm('¿Cancelar esta cita?');">
                                @csrf
                                <button type="submit" class="btn btn-danger mb-2 btn-block">
                                    <i class="fa fa-times"></i> Cancelar Cita
                                </button>
                            </form>
                        @endif

                        @if($appointment->status === 'completed')
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle"></i> Esta cita fue completada
                                @if($appointment->outcome)
                                    <br><strong>Resultado:</strong> {{ $appointment->outcome_label }}
                                @endif
                            </div>
                            @if($appointment->outcome_notes)
                                <p><strong>Notas:</strong> {{ $appointment->outcome_notes }}</p>
                            @endif
                        @endif

                        @if($appointment->status === 'cancelled')
                            <div class="alert alert-danger">
                                <i class="fa fa-times-circle"></i> Esta cita fue cancelada
                                @if($appointment->cancellation_reason)
                                    <br><strong>Razón:</strong> {{ $appointment->cancellation_reason }}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Completar --}}
    <div class="modal fade" id="completeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.crm.appointments.complete', $appointment) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Completar Cita</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Resultado</label>
                            <select name="outcome" class="form-control">
                                <option value="">-- Seleccionar --</option>
                                @foreach(\App\Appointment::getOutcomes() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Notas</label>
                            <textarea name="outcome_notes" class="form-control" rows="3" placeholder="Resumen de la cita..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Completar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
