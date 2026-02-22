@extends('admin.main')
@section('title', 'Propiedades Disponibles - Bolsa')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-th-list"></i> Publicaciones Disponibles para Comisión</h4>
            <a href="{{ route('admin.bolsa.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Dashboard</a>
        </div>

        {{-- Filtros --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row align-items-end">
                    <div class="form-group col-md-3 mb-2">
                        <label class="small">Sector</label>
                        <select name="sector_id" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($sectors ?? [] as $s)
                                <option value="{{ $s->id }}" {{ request('sector_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <label class="small">Rango precio</label>
                        <select name="price_range" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="0-50000000" {{ request('price_range') == '0-50000000' ? 'selected' : '' }}>Hasta ₡50M</option>
                            <option value="50000000-100000000" {{ request('price_range') == '50000000-100000000' ? 'selected' : '' }}>₡50M - ₡100M</option>
                            <option value="100000000+" {{ request('price_range') == '100000000+' ? 'selected' : '' }}>Más de ₡100M</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <label class="small">Comisión mín.</label>
                        <input type="number" name="min_commission" class="form-control form-control-sm" value="{{ request('min_commission') }}" placeholder="%" step="0.5">
                    </div>
                    <div class="form-group col-md-2 mb-2">
                        <label class="small">Tipo</label>
                        <select name="property_type" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="house" {{ request('property_type') == 'house' ? 'selected' : '' }}>Casa</option>
                            <option value="apartment" {{ request('property_type') == 'apartment' ? 'selected' : '' }}>Apartamento</option>
                            <option value="land" {{ request('property_type') == 'land' ? 'selected' : '' }}>Lote</option>
                            <option value="vehicle" {{ request('property_type') == 'vehicle' ? 'selected' : '' }}>Vehículo</option>
                            <option value="commercial" {{ request('property_type') == 'commercial' ? 'selected' : '' }}>Comercial</option>
                        </select>
                    </div>
                    <div class="form-group col-md-1 mb-2">
                        <label class="small">&nbsp;</label><br>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></button>
                        <a href="{{ route('admin.bolsa.available') }}" class="btn btn-sm btn-secondary"><i class="fa fa-times"></i></a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Lista de propiedades --}}
        <div class="row">
            @forelse($properties as $prop)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        @if($prop->image)
                            <img src="{{ $prop->image_url }}" class="card-img-top" style="height: 180px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="fa fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $prop->name }}</h5>
                            @if(($prop->property_type ?? '') === 'vehicle' && $prop->brand)
                                <p class="text-muted small mb-1">{{ $prop->brand }} {{ $prop->model }} {{ $prop->year }}</p>
                            @endif
                            <h4 class="text-primary">{{ $prop->formatted_price }}</h4>

                            <div class="mb-2">
                                <span class="badge badge-light">{{ $prop->property_type_name ?? 'Propiedad' }}</span>
                                <span class="badge badge-success"><i class="fa fa-percent"></i> {{ $prop->commission_percentage }}% comisión</span>
                                @if($prop->has_virtual_tour) <span class="badge badge-info"><i class="fa fa-eye"></i> Tour 360</span> @endif
                            </div>

                            @if(($prop->property_type ?? '') === 'vehicle')
                                <small class="text-muted d-block">
                                    @if($prop->transmission)<i class="fa fa-cog mr-1"></i>{{ $prop->transmission }} @endif
                                    @if($prop->fuel_type)| <i class="fa fa-tint mr-1"></i>{{ $prop->fuel_type }} @endif
                                    @if($prop->mileage_km)| <i class="fa fa-road mr-1"></i>{{ number_format($prop->mileage_km) }} km @endif
                                </small>
                            @endif

                            @if($prop->category)
                                <small class="text-muted d-block">
                                    <i class="fa fa-folder"></i> {{ $prop->category->name }}
                                    @if($prop->category->company)
                                        — {{ $prop->category->company->name }}
                                    @endif
                                </small>
                            @endif

                            @if($prop->location)
                                <small class="text-muted d-block"><i class="fa fa-map-marker"></i> {{ $prop->location }}</small>
                            @endif

                            @if($prop->commission_notes)
                                <small class="text-muted d-block mt-2"><i class="fa fa-info-circle"></i> {{ Str::limit($prop->commission_notes, 80) }}</small>
                            @endif
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.bolsa.create-request', $prop) }}" class="btn btn-success btn-sm btn-block">
                                <i class="fa fa-handshake-o"></i> Solicitar Comisión
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fa fa-search fa-3x text-muted mb-3"></i>
                            <h5>No hay propiedades disponibles</h5>
                            <p class="text-muted">No se encontraron propiedades que acepten comisiones externas</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if(method_exists($properties, 'links'))
            {{ $properties->withQueryString()->links() }}
        @endif
    </div>
@endsection
