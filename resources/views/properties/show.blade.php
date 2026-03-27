@extends('frontend.front')

@section('title', $property->name)

@section('content')
<div class="container mt-5 pt-5">
    <div class="row">
        {{-- Columna Principal --}}
        <div class="col-lg-8">
            {{-- Galería de Imágenes --}}
            <div class="card mb-4 shadow-sm">
                <div id="propertyGallery" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        {{-- Imagen principal --}}
                        <div class="carousel-item active">
                            <img src="{{ $property->image_url }}"
                                 class="d-block w-100"
                                 alt="{{ $property->name }}"
                                 style="height: 500px; object-fit: cover;">
                        </div>

                        {{-- Imágenes adicionales --}}
                        @foreach($property->images as $image)
                            <div class="carousel-item">
                                <img src="{{ route('file', $image->image) }}"
                                     class="d-block w-100"
                                     alt="{{ $image->caption ?? $property->name }}"
                                     style="height: 500px; object-fit: cover;">
                            </div>
                        @endforeach
                    </div>

                    @if($property->images->count() > 0)
                        <a class="carousel-control-prev" href="#propertyGallery" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        </a>
                        <a class="carousel-control-next" href="#propertyGallery" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        </a>
                    @endif
                </div>

                {{-- Thumbnails --}}
                @if($property->images->count() > 0)
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2">
                                <img src="{{ $property->image_url }}"
                                     class="img-thumbnail cursor-pointer"
                                     data-target="#propertyGallery"
                                     data-slide-to="0"
                                     style="height: 80px; object-fit: cover; cursor: pointer;">
                            </div>
                            @foreach($property->images as $index => $image)
                                <div class="col-2">
                                    <img src="{{ route('file', $image->image) }}"
                                         class="img-thumbnail cursor-pointer"
                                         data-target="#propertyGallery"
                                         data-slide-to="{{ $index + 1 }}"
                                         style="height: 80px; object-fit: cover; cursor: pointer;">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Información Principal --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2 class="mb-2">{{ $property->name }}</h2>
                            @if($property->code)
                                <p class="text-muted mb-2">
                                    <i class="fa fa-tag"></i> Código: <strong>{{ $property->code }}</strong>
                                </p>
                            @endif
                            @if($property->location)
                                <p class="text-muted mb-2">
                                    <i class="fa fa-map-marker"></i> {{ $property->location }}
                                </p>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="badge badge-{{ $property->status_color }} badge-lg">
                                {{ $property->status_name }}
                            </span>
                        </div>
                    </div>

                    <hr>

                    {{-- Precio --}}
                    <div class="mb-4">
                        <h3 class="text-primary mb-1">{{ $property->formatted_price }}</h3>
                        @if($property->maintenance && !$property->isVehicle())
                            <p class="text-muted">Mantenimiento: ₡{{ number_format($property->maintenance) }}/mes</p>
                        @endif
                    </div>

                    {{-- Descripción --}}
                    @if($property->description)
                        <div class="mb-4">
                            <h5><i class="fa fa-align-left"></i> Descripción</h5>
                            <p class="text-justify">{{ $property->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Características --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa fa-list"></i> Características</h5>
                </div>
                <div class="card-body">
                    @if($property->isVehicle())
                        {{-- Características de Vehículo --}}
                        <div class="row">
                            @if($property->brand)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-car"></i> Marca:</strong><br>
                                    {{ $property->brand }}
                                </div>
                            @endif
                            @if($property->model)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-tag"></i> Modelo:</strong><br>
                                    {{ $property->model }}
                                </div>
                            @endif
                            @if($property->year)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-calendar"></i> Año:</strong><br>
                                    {{ $property->year }}
                                </div>
                            @endif
                            @if($property->color)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-paint-brush"></i> Color:</strong><br>
                                    {{ $property->color }}
                                </div>
                            @endif
                            @if($property->mileage_km)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-tachometer"></i> Kilometraje:</strong><br>
                                    {{ number_format($property->mileage_km) }} km
                                </div>
                            @endif
                            @if($property->transmission)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-gears"></i> Transmisión:</strong><br>
                                    {{ ucfirst($property->transmission) }}
                                </div>
                            @endif
                            @if($property->fuel_type)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-filter"></i> Combustible:</strong><br>
                                    {{ ucfirst($property->fuel_type) }}
                                </div>
                            @endif
                            @if($property->engine_cc)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-cog"></i> Cilindraje:</strong><br>
                                    {{ $property->engine_cc }} cc
                                </div>
                            @endif
                            @if($property->doors)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-th"></i> Puertas:</strong><br>
                                    {{ $property->doors }}
                                </div>
                            @endif
                            @if($property->passengers)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-users"></i> Pasajeros:</strong><br>
                                    {{ $property->passengers }}
                                </div>
                            @endif
                            @if($property->plate)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-id-card"></i> Placa:</strong><br>
                                    {{ $property->plate }}
                                </div>
                            @endif
                            @if($property->condition)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-check-circle"></i> Condición:</strong><br>
                                    {{ ucfirst($property->condition) }}
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Características de Propiedad Inmobiliaria --}}
                        <div class="row">
                            @if($property->rooms)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-bed"></i> Habitaciones:</strong><br>
                                    {{ $property->rooms }}
                                </div>
                            @endif
                            @if($property->bathrooms)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-bath"></i> Baños:</strong><br>
                                    {{ $property->bathrooms }}
                                </div>
                            @endif
                            @if($property->garage)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-car"></i> Parqueos:</strong><br>
                                    {{ $property->garage }}
                                </div>
                            @endif
                            @if($property->construction)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-arrows"></i> Construcción:</strong><br>
                                    {{ number_format($property->construction) }} m²
                                </div>
                            @endif
                            @if($property->land)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-th-large"></i> Terreno:</strong><br>
                                    {{ number_format($property->land) }} m²
                                </div>
                            @endif
                            @if($property->floor_levels)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-building"></i> Pisos:</strong><br>
                                    {{ $property->floor_levels }}
                                </div>
                            @endif
                            @if($property->construction_year)
                                <div class="col-md-4 mb-3">
                                    <strong><i class="fa fa-calendar"></i> Año construcción:</strong><br>
                                    {{ $property->construction_year }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Mapa (si hay coordenadas) --}}
            @if($property->latitude && $property->longitude)
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fa fa-map"></i> Ubicación</h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="map" style="height: 350px; width: 100%;"></div>
                    </div>
                </div>
            @endif

            {{-- Propiedades Similares --}}
            @if($similarProperties->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-th"></i> Propiedades Similares</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($similarProperties->take(4) as $similar)
                                @include('partials.property-card', ['property' => $similar, 'showFavorite' => auth()->check()])
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Columna Lateral --}}
        <div class="col-lg-4">
            {{-- Acciones Rápidas --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa fa-hand-pointer-o"></i> Acciones</h5>

                    {{-- Tour Virtual --}}
                    @if($property->has_virtual_tour)
                        <a href="{{ route('virtual-tour', $property->id) }}" class="btn btn-primary btn-block mb-2">
                            <i class="fa fa-play-circle"></i> Ver Tour Virtual 360°
                        </a>
                    @endif

                    {{-- Favorito --}}
                    @auth
                        <button type="button"
                                class="btn btn-block mb-2 {{ $isFavorite ? 'btn-danger' : 'btn-outline-danger' }}"
                                onclick="toggleFavorite({{ $property->id }}, this)">
                            <i class="fa {{ $isFavorite ? 'fa-heart' : 'fa-heart-o' }}"></i>
                            {{ $isFavorite ? 'Quitar de Favoritos' : 'Guardar en Favoritos' }}
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-danger btn-block mb-2">
                            <i class="fa fa-heart-o"></i> Iniciar sesión para guardar
                        </a>
                    @endauth

                    {{-- Compartir --}}
                    <button class="btn btn-outline-secondary btn-block mb-2" onclick="shareProperty()">
                        <i class="fa fa-share-alt"></i> Compartir Propiedad
                    </button>

                    {{-- Contactar --}}
                    <a href="#contactSection" class="btn btn-success btn-block">
                        <i class="fa fa-envelope"></i> Contactar Agente
                    </a>
                </div>
            </div>

            {{-- Información del Agente --}}
            @if($property->user)
                <div class="card mb-4 shadow-sm" id="contactSection">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fa fa-user"></i> Agente</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ $property->user->avatar_url }}"
                             class="rounded-circle mb-3"
                             alt="{{ $property->user->name }}"
                             style="width: 100px; height: 100px; object-fit: cover;">
                        <h5 class="mb-1">{{ $property->user->name }}</h5>
                        @if($property->user->phone)
                            <p class="text-muted mb-2">
                                <i class="fa fa-phone"></i> {{ $property->user->phone }}
                            </p>
                        @endif
                        @if($property->user->email)
                            <p class="text-muted mb-3">
                                <i class="fa fa-envelope"></i> {{ $property->user->email }}
                            </p>
                        @endif

                        {{-- Mostrar rating si existe --}}
                        @if($property->user->reviews_count > 0)
                            <div class="mb-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa fa-star{{ $i <= $property->user->average_rating ? '' : '-o' }} text-warning"></i>
                                @endfor
                                <br>
                                <small class="text-muted">
                                    {{ number_format($property->user->average_rating, 1) }}/5
                                    ({{ $property->user->reviews_count }} {{ $property->user->reviews_count == 1 ? 'reseña' : 'reseñas' }})
                                </small>
                            </div>
                        @endif

                        <p class="text-muted small">
                            <i class="fa fa-building"></i>
                            {{ $property->category->name ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Información Adicional --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-info-circle"></i> Información</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>Tipo:</strong> {{ $property->property_type_name }}
                        </li>
                        <li class="mb-2">
                            <strong>Sector:</strong> {{ $property->category->sector->name ?? 'N/A' }}
                        </li>
                        <li class="mb-2">
                            <strong>Categoría:</strong> {{ $property->category->name ?? 'N/A' }}
                        </li>
                        @if($property->subcategory)
                            <li class="mb-2">
                                <strong>Subcategoría:</strong> {{ $property->subcategory->name }}
                            </li>
                        @endif
                        <li class="mb-2">
                            <strong>Publicado:</strong>
                            {{ $property->published_at ? $property->published_at->format('d/m/Y') : 'N/A' }}
                        </li>
                        <li class="mb-2">
                            <strong>Vistas:</strong> {{ number_format($property->views_count) }}
                        </li>

                        @if($property->canAcceptCommission())
                            <li class="mt-3 text-center">
                                <span class="badge badge-success badge-lg">
                                    <i class="fa fa-percent"></i>
                                    Acepta Comisión: {{ $property->commission_percentage }}%
                                </span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/spin-viewer.css') }}">
<style>
    .badge-lg {
        font-size: 0.95rem;
        padding: 0.5rem 1rem;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/favorites.js') }}"></script>

{{-- Google Maps --}}
@if($property->latitude && $property->longitude)
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
<script>
    function initMap() {
        const position = {
            lat: {{ $property->latitude }},
            lng: {{ $property->longitude }}
        };

        const map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: position
        });

        new google.maps.Marker({
            position: position,
            map: map,
            title: '{{ $property->name }}'
        });
    }

    $(document).ready(function() {
        initMap();
    });
</script>
@endif

{{-- Compartir propiedad --}}
<script>
    function shareProperty() {
        const url = '{{ $property->share_url ?? url()->current() }}';
        const title = '{{ $property->name }}';

        if (navigator.share) {
            navigator.share({
                title: title,
                url: url
            }).catch(err => console.log('Error sharing:', err));
        } else {
            // Fallback: copiar al portapapeles
            const temp = $('<input>');
            $('body').append(temp);
            temp.val(url).select();
            document.execCommand('copy');
            temp.remove();

            alert('Enlace copiado al portapapeles:\n' + url);
        }
    }
</script>
@endpush
