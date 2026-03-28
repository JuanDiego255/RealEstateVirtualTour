@extends('admin.main')
@section('title', 'Agenda - Lista de Citas')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-list"></i> Lista de Citas</h4>
            <div>
                <a href="{{ route('admin.crm.appointments.index') }}" class="btn btn-secondary"><i class="fa fa-calendar"></i> Calendario</a>
                <a href="{{ route('admin.crm.appointments.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Nueva Cita</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Fecha/Hora</th>
                            <th>Cliente</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th>Agente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            <tr class="{{ $appointment->isPast() && $appointment->status === 'scheduled' ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ $appointment->title }}</strong>
                                    @if($appointment->property)
                                        <br><small class="text-muted"><i class="fa fa-home"></i> {{ Str::limit($appointment->property->title, 20) }}</small>
                                    @endif
                                    @if($appointment->vehicle)
                                        <br><small class="text-muted"><i class="fa fa-car"></i> {{ Str::limit($appointment->vehicle->name, 20) }}</small>
                                    @endif
                                </td>
                                <td>{{ $appointment->type_label }}</td>
                                <td>
                                    {{ $appointment->starts_at->format('d/m/Y') }}
                                    <br><small class="text-muted">{{ $appointment->starts_at->format('H:i') }} - {{ $appointment->ends_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    {{ $appointment->client_display_name }}
                                    @if($appointment->client_display_phone)
                                        <br><small class="text-muted">{{ $appointment->client_display_phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($appointment->location)
                                        {{ Str::limit($appointment->location, 30) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><span class="badge badge-{{ $appointment->status_color }}">{{ $appointment->status_label }}</span></td>
                                <td>{{ $appointment->user->name ?? '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.crm.appointments.show', $appointment) }}" class="btn btn-outline-info" title="Ver"><i class="fa fa-eye"></i></a>
                                        <a href="{{ route('admin.crm.appointments.edit', $appointment) }}" class="btn btn-outline-primary" title="Editar"><i class="fa fa-edit"></i></a>
                                        @if($appointment->canBeCancelled())
                                            <form action="{{ route('admin.crm.appointments.cancel', $appointment) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?');">
                                                @csrf
                                                <button class="btn btn-outline-danger" title="Cancelar"><i class="fa fa-times"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No hay citas registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $appointments->links() }}
            </div>
        </div>
    </div>
@endsection
