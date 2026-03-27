{{--
    Componente reutilizable de tarjeta de propiedad
    Variables requeridas:
    - $property: objeto Properties
    - $showFavorite: boolean (opcional, default true) - mostrar botón de favorito
    - $isFavorite: boolean (opcional, default false) - estado inicial del favorito
--}}

@php
    $showFavorite = $showFavorite ?? true;
    $isFavorite = $isFavorite ?? (auth()->check() ? \App\Favorite::isFavorite(auth()->id(), $property->id) : false);
    $spin = $property->active_spin;
    $hasSpin = !empty($spin);
@endphp

<div class="col-md-4 col-lg-3 mb-4 property-card-col">
    <div class="card h-100 shadow-sm" style="border-radius: 10px; overflow: hidden; transition: transform 0.3s;">
        {{-- Imagen o Spin 360 --}}
        <div class="position-relative">
            @if($hasSpin)
                <div class="spin-viewer-wrap" id="spinViewer{{ $property->id }}"
                     data-frames-dir="{{ $spin->frames_dir }}"
                     data-frames-count="{{ $spin->frames_count }}"
                     data-auto-rotate="{{ $property->spin_auto_rotate ? '1' : '0' }}"
                     style="height: 220px;">
                    <canvas></canvas>
                    <div class="spin-overlay">
                        <span class="spin-arrows">&larr;</span>
                        <span>Spin 360</span>
                        <span class="spin-arrows">&rarr;</span>
                    </div>
                </div>
            @else
                <a href="{{ route('property.show', $property->id) }}">
                    <img src="{{ $property->image_url }}"
                         class="card-img-top"
                         alt="{{ $property->name }}"
                         style="height: 220px; object-fit: cover;">
                </a>
            @endif

            {{-- Badge de estado --}}
            <span class="badge badge-{{ $property->status_color }} position-absolute"
                  style="top: 10px; left: 10px; font-size: 0.75rem;">
                {{ $property->status_name }}
            </span>

            {{-- Botón de favorito --}}
            @if($showFavorite && auth()->check())
                <button type="button"
                        class="btn btn-sm {{ $isFavorite ? 'btn-danger' : 'btn-outline-danger' }} position-absolute"
                        style="top: 10px; right: 10px; border-radius: 50%; width: 36px; height: 36px; padding: 0; background-color: rgba(255,255,255,0.9);"
                        onclick="toggleFavorite({{ $property->id }}, this)"
                        data-toggle="tooltip"
                        title="{{ $isFavorite ? 'Quitar de favoritos' : 'Agregar a favoritos' }}">
                    <i class="fa {{ $isFavorite ? 'fa-heart' : 'fa-heart-o' }}"></i>
                </button>
            @endif
        </div>

        {{-- Información --}}
        <div class="card-body">
            {{-- Tipo de propiedad --}}
            <div class="mb-2">
                <span class="badge badge-light">
                    <i class="fa {{ $property->isVehicle() ? 'fa-car' : 'fa-home' }}"></i>
                    {{ $property->property_type_name }}
                </span>
            </div>

            {{-- Nombre --}}
            <h5 class="card-title mb-2">
                <a href="{{ route('property.show', $property->id) }}" class="text-dark text-decoration-none">
                    {{ \Illuminate\Support\Str::limit($property->name, 45) }}
                </a>
            </h5>

            {{-- Ubicación --}}
            @if($property->location)
                <p class="text-muted small mb-3">
                    <i class="fa fa-map-marker"></i>
                    {{ \Illuminate\Support\Str::limit($property->location, 40) }}
                </p>
            @endif

            {{-- Características --}}
            @if($property->isVehicle())
                <ul class="list-unstyled small mb-3">
                    @if($property->year)
                        <li><i class="fa fa-calendar text-muted"></i> Año: {{ $property->year }}</li>
                    @endif
                    @if($property->mileage_km)
                        <li><i class="fa fa-tachometer text-muted"></i> {{ number_format($property->mileage_km) }} km</li>
                    @endif
                    @if($property->transmission)
                        <li><i class="fa fa-gears text-muted"></i> {{ ucfirst($property->transmission) }}</li>
                    @endif
                </ul>
            @else
                <ul class="list-inline mb-3">
                    @if($property->rooms)
                        <li class="list-inline-item">
                            <i class="fa fa-bed text-muted"></i> {{ $property->rooms }}
                        </li>
                    @endif
                    @if($property->bathrooms)
                        <li class="list-inline-item">
                            <i class="fa fa-bath text-muted"></i> {{ $property->bathrooms }}
                        </li>
                    @endif
                    @if($property->construction)
                        <li class="list-inline-item">
                            <i class="fa fa-arrows text-muted"></i> {{ $property->construction }} m²
                        </li>
                    @endif
                </ul>
            @endif

            {{-- Precio --}}
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="text-primary mb-0">
                    {{ $property->formatted_price }}
                </h4>
                @if($property->maintenance && !$property->isVehicle())
                    <small class="text-muted">+ ₡{{ number_format($property->maintenance) }}/mes</small>
                @endif
            </div>
        </div>

        {{-- Footer con botones --}}
        <div class="card-footer bg-white border-top-0 d-flex justify-content-between">
            <a href="{{ route('property.show', $property->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-info-circle"></i> Ver Detalle
            </a>
            @if($property->has_virtual_tour)
                <a href="{{ route('virtual-tour', $property->id) }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-play-circle"></i> Tour 360°
                </a>
            @endif
        </div>
    </div>
</div>

@once
    @push('styles')
    <style>
        .property-card-col .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
        }
    </style>
    @endpush
@endonce
