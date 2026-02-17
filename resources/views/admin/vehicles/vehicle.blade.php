@extends('admin.main')

@section('title', 'Vehículos')

@section('content')
    @if ($message = Session::has('success'))
        <div class="alert-dismiss">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>{{ Session::get('success') }}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span class="fa fa-times"></span>
                </button>
            </div>
        </div>
    @endif
    @if ($message = Session::has('error'))
        <div class="alert-dismiss">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>{{ Session::get('error') }}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span class="fa fa-times"></span>
                </button>
            </div>
        </div>
    @endif
    @include('admin.vehicles.add')

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-dark"><i class="fa fa-car mr-2"></i>Gestión de Vehículos</h4>
            <button type="button" class="btn btn-rounded btn-outline-info" data-toggle="modal"
                data-target="#addVehicle">
                <i class="fa fa-plus mr-1"></i> Nuevo Vehículo
            </button>
        </div>

        {{-- Filtro por categoría --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <form method="GET" action="{{ route('vehicles') }}">
                    <select name="category_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Todas las Categorías --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="row mt-3">
            @if (count($vehicles) > 0)
                @foreach ($vehicles as $vehicle)
                    @include('admin.vehicles.edit')
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="position-relative">
                                <img class="card-img-top" style="height: 170px; object-fit: cover;"
                                    src="{{ isset($vehicle->image) ? route('file', $vehicle->image) : url('images/producto-sin-imagen.PNG') }}">
                                <span class="badge badge-dark position-absolute" style="top: 10px; left: 10px;">
                                    {{ $vehicle->year }}
                                </span>
                                @if ($vehicle->condition)
                                    <span
                                        class="badge badge-{{ $vehicle->condition == 'Nuevo' ? 'success' : 'warning' }} position-absolute"
                                        style="top: 10px; right: 10px;">
                                        {{ $vehicle->condition }}
                                    </span>
                                @endif
                            </div>
                            <div class="card-body p-3">
                                <h6 class="card-title mb-1 font-weight-bold">
                                    <a href="{{ route('configTyped', ['type' => 'vehicle', 'id' => $vehicle->id]) }}">
                                        {{ $vehicle->brand }} {{ $vehicle->model }}
                                    </a>
                                </h6>
                                <p class="text-muted small mb-2">{{ $vehicle->name }}</p>

                                <div class="mb-2">
                                    <span class="text-primary font-weight-bold">
                                        ₡{{ number_format($vehicle->price) }}
                                    </span>
                                </div>

                                <div class="row small text-muted">
                                    @if ($vehicle->mileage_km)
                                        <div class="col-6 mb-1">
                                            <i class="fa fa-tachometer-alt mr-1"></i>{{ $vehicle->mileage_km }} km
                                        </div>
                                    @endif
                                    @if ($vehicle->fuel_type)
                                        <div class="col-6 mb-1">
                                            <i class="fa fa-gas-pump mr-1"></i>{{ $vehicle->fuel_type }}
                                        </div>
                                    @endif
                                    @if ($vehicle->transmission)
                                        <div class="col-6 mb-1">
                                            <i class="fa fa-cogs mr-1"></i>{{ $vehicle->transmission }}
                                        </div>
                                    @endif
                                    @if ($vehicle->engine_cc)
                                        <div class="col-6 mb-1">
                                            <i class="fa fa-bolt mr-1"></i>{{ $vehicle->engine_cc }} CC
                                        </div>
                                    @endif
                                    @if ($vehicle->doors)
                                        <div class="col-6 mb-1">
                                            <i class="fa fa-door-open mr-1"></i>{{ $vehicle->doors }} puertas
                                        </div>
                                    @endif
                                    @if ($vehicle->passengers)
                                        <div class="col-6 mb-1">
                                            <i class="fa fa-users mr-1"></i>{{ $vehicle->passengers }} pasajeros
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between p-2">
                                <button class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                    data-target="#editVehicle{{ $vehicle->id }}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <a onclick="if (confirm('¿Deseas borrar este vehículo?')) {
                                    document.getElementById('deleteVehicle{{ $vehicle->id }}').submit();
                                }"
                                    href="#" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i>
                                </a>
                                <form id="deleteVehicle{{ $vehicle->id }}" method="post"
                                    action="{{ url('/delete/vehicle/' . $vehicle->id) }}">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12 text-center mt-5">
                    <i class="fa fa-car fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay vehículos registrados</h5>
                    <p class="text-muted">Crea un sector automotriz y categorías primero, luego agrega vehículos.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
