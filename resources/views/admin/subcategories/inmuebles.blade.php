@extends('admin.main')
@section('title', 'Publicaciones - ' . $subcategory->name)
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4><i class="fa fa-list"></i> Publicaciones de <strong>{{ $subcategory->name }}</strong></h4>
                <small class="text-muted">
                    Sucursal: {{ $category->name }} &middot;
                    {{ $properties->count() }} publicaciones
                </small>
            </div>
            <div>
                <a href="{{ route('admin.subcategories.index', $category) }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Volver a Categorías
                </a>
            </div>
        </div>

        {{-- PUBLICACIONES (propiedades y vehículos unificados) --}}
        @if($properties->count() > 0)
            <div class="row mb-4">
                @foreach($properties as $property)
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            @if($property->image)
                                <img src="{{ route('file', $property->image) }}" class="card-img-top" style="height: 160px; object-fit: cover;">
                            @else
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                                    <i class="fa fa-{{ ($property->property_type ?? '') === 'vehicle' ? 'car' : 'home' }} fa-3x text-muted"></i>
                                </div>
                            @endif
                            <div class="card-body p-3">
                                <h6 class="card-title mb-1">{{ $property->name }}</h6>
                                @if($property->property_type)
                                    <span class="badge badge-light mb-1">{{ $property->property_type_name }}</span>
                                @endif

                                @if(($property->property_type ?? '') === 'vehicle')
                                    <p class="text-muted small mb-1">{{ $property->brand }} {{ $property->model }} {{ $property->year }}</p>
                                @else
                                    <p class="text-muted small mb-1">{{ $property->code }}</p>
                                @endif

                                <p class="font-weight-bold mb-1" style="color: #c2ac1f;">
                                    {{ $property->formatted_price ?? (($property->currency ?? 'CRC') === 'USD' ? '$' : '₡') . number_format($property->price) }}
                                </p>

                                <div class="small text-muted">
                                    @if(($property->property_type ?? '') === 'vehicle')
                                        @if($property->transmission)<i class="fa fa-cog mr-1"></i>{{ $property->transmission }} @endif
                                        @if($property->fuel_type)<i class="fa fa-tint mr-1"></i>{{ $property->fuel_type }} @endif
                                        @if($property->mileage_km)<i class="fa fa-road mr-1"></i>{{ number_format($property->mileage_km) }} km @endif
                                    @else
                                        @if($property->rooms)<i class="fa fa-bed mr-1"></i>{{ $property->rooms }} @endif
                                        @if($property->bathrooms)<i class="fa fa-bath mr-1"></i>{{ $property->bathrooms }} @endif
                                        @if($property->construction)<i class="fa fa-expand mr-1"></i>{{ $property->construction }} Mt² @endif
                                    @endif
                                </div>

                                @if($property->status)
                                    <span class="badge badge-{{ $property->status == 'available' ? 'success' : ($property->status == 'sold' ? 'danger' : 'warning') }} mt-1">
                                        {{ $property->status_name ?? $property->status }}
                                    </span>
                                @endif
                            </div>
                            <div class="card-footer bg-white p-2">
                                <a href="{{ route('property') }}" class="btn btn-sm btn-outline-primary btn-block">
                                    <i class="fa fa-edit mr-1"></i> Administrar
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- VACÍO --}}
        @if($properties->count() == 0)
            <div class="text-center py-5">
                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay publicaciones en esta subcategoría</h5>
                <p class="text-muted">Agregue publicaciones desde el módulo de <a href="{{ route('property') }}">Publicaciones</a> y asócielas a esta subcategoría.</p>
            </div>
        @endif
    </div>
@endsection
