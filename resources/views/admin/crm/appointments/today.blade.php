@extends('admin.main')
@section('title', 'Citas de Hoy')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-sun-o"></i> Citas de Hoy - {{ now()->format('l, d \d\e F') }}</h4>
            <div>
                <a href="{{ route('admin.crm.appointments.index') }}" class="btn btn-secondary"><i class="fa fa-calendar"></i> Calendario</a>
                <a href="{{ route('admin.crm.appointments.create', ['date' => now()->format('Y-m-d')]) }}" class="btn btn-primary"><i class="fa fa-plus"></i> Nueva Cita</a>
            </div>
        </div>

        @if($appointments->count() > 0)
            <div class="row">
                @foreach($appointments as $appointment)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100" style="border-left: 4px solid {{ $appointment->calendar_color }};">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0">{{ $appointment->title }}</h5>
                                    <span class="badge badge-{{ $appointment->status_color }}">{{ $appointment->status_label }}</span>
                                </div>

                                <p class="text-muted mb-2">
                                    <i class="fa fa-clock-o"></i>
                                    {{ $appointment->starts_at->format('H:i') }} - {{ $appointment->ends_at->format('H:i') }}
                                </p>

                                <p class="mb-2"><small class="text-muted">{{ $appointment->type_label }}</small></p>

                                <hr>

                                <p class="mb-1"><strong>Cliente:</strong> {{ $appointment->client_display_name }}</p>
                                @if($appointment->client_display_phone)
                                    <p class="mb-1">
                                        <a href="tel:{{ $appointment->client_display_phone }}"><i class="fa fa-phone"></i> {{ $appointment->client_display_phone }}</a>
                                    </p>
                                @endif

                                @if($appointment->location)
                                    <p class="mb-1"><i class="fa fa-map-marker text-muted"></i> {{ $appointment->location }}</p>
                                @endif

                                @if($appointment->property)
                                    <p class="mb-1"><small><i class="fa fa-home text-primary"></i> {{ Str::limit($appointment->property->title, 30) }}</small></p>
                                @endif

                                @if($appointment->vehicle)
                                    <p class="mb-1"><small><i class="fa fa-car text-primary"></i> {{ Str::limit($appointment->vehicle->name, 30) }}</small></p>
                                @endif
                            </div>
                            <div class="card-footer bg-white">
                                <div class="btn-group btn-group-sm w-100">
                                    <a href="{{ route('admin.crm.appointments.show', $appointment) }}" class="btn btn-outline-info"><i class="fa fa-eye"></i> Ver</a>
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
                                            <button type="submit" class="btn btn-outline-success"><i class="fa fa-check"></i> Confirmar</button>
                                        </form>
                                    @endif
                                    @if($appointment->client_display_phone)
                                        <a href="tel:{{ $appointment->client_display_phone }}" class="btn btn-outline-primary"><i class="fa fa-phone"></i> Llamar</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fa fa-calendar-check-o fa-4x text-muted mb-3"></i>
                    <h5>No tienes citas programadas para hoy</h5>
                    <p class="text-muted">Disfruta tu día libre o programa una nueva cita</p>
                    <a href="{{ route('admin.crm.appointments.create', ['date' => now()->format('Y-m-d')]) }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Programar Cita
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
