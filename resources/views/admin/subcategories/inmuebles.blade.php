@extends('admin.main')
@section('title', 'Inmuebles - ' . $subcategory->name)
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4><i class="fa fa-list"></i> Inmuebles de <strong>{{ $subcategory->name }}</strong></h4>
                <small class="text-muted">
                    Sucursal: {{ $category->name }} &middot;
                    {{ $properties->count() }} propiedades &middot;
                    {{ $vehicles->count() }} vehículos
                </small>
            </div>
            <div>
                <a href="{{ route('admin.subcategories.index', $category) }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Volver a Categorías
                </a>
            </div>
        </div>

        {{-- PROPIEDADES --}}
        @if($properties->count() > 0)
            <h5 class="mb-3"><i class="fa fa-building mr-2 text-primary"></i>Propiedades ({{ $properties->count() }})</h5>
            <div class="row mb-4">
                @foreach($properties as $property)
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            @if($property->image)
                                <img src="{{ route('file', $property->image) }}" class="card-img-top" style="height: 160px; object-fit: cover;">
                            @else
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                                    <i class="fa fa-home fa-3x text-muted"></i>
                                </div>
                            @endif
                            <div class="card-body p-3">
                                <h6 class="card-title mb-1">{{ $property->name }}</h6>
                                <p class="text-muted small mb-1">{{ $property->code }}</p>
                                <p class="font-weight-bold mb-1" style="color: #c2ac1f;">
                                    {{ $property->formatted_price ?? '₡' . number_format($property->price) }}
                                </p>
                                <div class="small text-muted">
                                    @if($property->rooms)<i class="fa fa-bed mr-1"></i>{{ $property->rooms }} @endif
                                    @if($property->bathrooms)<i class="fa fa-bath mr-1"></i>{{ $property->bathrooms }} @endif
                                    @if($property->construction)<i class="fa fa-expand mr-1"></i>{{ $property->construction }} Mt² @endif
                                </div>
                                @if($property->status)
                                    <span class="badge badge-{{ $property->status == 'available' ? 'success' : ($property->status == 'sold' ? 'danger' : 'warning') }} mt-1">
                                        {{ $property->status_name ?? $property->status }}
                                    </span>
                                @endif
                            </div>
                            <div class="card-footer bg-white p-2">
                                <a href="{{ url('property/edit/' . $property->id) }}" class="btn btn-sm btn-outline-primary btn-block" data-toggle="modal" data-target="#editProperty{{ $property->id }}">
                                    <i class="fa fa-edit mr-1"></i> Editar
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- VEHÍCULOS --}}
        @if($vehicles->count() > 0)
            <h5 class="mb-3"><i class="fa fa-car mr-2 text-info"></i>Vehículos ({{ $vehicles->count() }})</h5>
            <div class="row mb-4">
                @foreach($vehicles as $vehicle)
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            @if($vehicle->image)
                                <img src="{{ route('file', $vehicle->image) }}" class="card-img-top" style="height: 160px; object-fit: cover;">
                            @else
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                                    <i class="fa fa-car fa-3x text-muted"></i>
                                </div>
                            @endif
                            <div class="card-body p-3">
                                <h6 class="card-title mb-1">{{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->year }}</h6>
                                <p class="text-muted small mb-1">{{ $vehicle->name }}</p>
                                <p class="font-weight-bold mb-1" style="color: #c2ac1f;">
                                    ₡{{ number_format($vehicle->price) }}
                                </p>
                                <div class="small text-muted">
                                    @if($vehicle->transmission)<i class="fa fa-cog mr-1"></i>{{ $vehicle->transmission }} @endif
                                    @if($vehicle->fuel_type)<i class="fa fa-tint mr-1"></i>{{ $vehicle->fuel_type }} @endif
                                    @if($vehicle->mileage_km)<i class="fa fa-road mr-1"></i>{{ number_format($vehicle->mileage_km) }} km @endif
                                </div>
                                @if($vehicle->condition)
                                    <span class="badge badge-{{ $vehicle->condition == 'Nuevo' ? 'success' : 'secondary' }} mt-1">
                                        {{ $vehicle->condition }}
                                    </span>
                                @endif
                            </div>
                            <div class="card-footer bg-white p-2">
                                <a href="#" class="btn btn-sm btn-outline-primary btn-block" data-toggle="modal" data-target="#editVehicle{{ $vehicle->id }}">
                                    <i class="fa fa-edit mr-1"></i> Editar
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- VACÍO --}}
        @if($properties->count() == 0 && $vehicles->count() == 0)
            <div class="text-center py-5">
                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay inmuebles en esta subcategoría</h5>
                <p class="text-muted">Agregue propiedades o vehículos desde sus respectivos módulos y asócielos a esta subcategoría.</p>
            </div>
        @endif
    </div>
@endsection
