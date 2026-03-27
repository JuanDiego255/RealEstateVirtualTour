@extends('frontend.front')

@section('title', 'Búsqueda Avanzada de Propiedades')

@section('content')
<div class="container" style="margin-top: 120px; padding-top: 20px;">
    <div class="row">
        {{-- Sidebar de Filtros --}}
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm sticky-top" style="top: 100px; z-index: 100;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-filter"></i> Filtros de Búsqueda
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('search') }}" method="GET" id="searchForm">
                        {{-- Término de búsqueda --}}
                        <div class="form-group">
                            <label><i class="fa fa-search"></i> Buscar</label>
                            <input type="text"
                                   name="q"
                                   class="form-control"
                                   placeholder="Nombre, código, ubicación..."
                                   value="{{ request('q') }}">
                        </div>

                        {{-- Sector --}}
                        <div class="form-group">
                            <label><i class="fa fa-th-large"></i> Sector</label>
                            <select name="sector_id" class="form-control">
                                <option value="">Todos los sectores</option>
                                @foreach($sectors as $sector)
                                    <option value="{{ $sector->id }}" {{ request('sector_id') == $sector->id ? 'selected' : '' }}>
                                        {{ $sector->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Categoría/Sucursal --}}
                        <div class="form-group">
                            <label><i class="fa fa-building"></i> Categoría</label>
                            <select name="category_id" class="form-control">
                                <option value="">Todas las categorías</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }} ({{ $category->sector->name ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tipo de propiedad --}}
                        <div class="form-group">
                            <label><i class="fa fa-home"></i> Tipo</label>
                            <select name="property_type" class="form-control">
                                <option value="">Todos los tipos</option>
                                @foreach($propertyTypes as $value => $label)
                                    <option value="{{ $value }}" {{ request('property_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <hr>

                        {{-- Rango de Precio --}}
                        <div class="form-group">
                            <label><i class="fa fa-money"></i> Precio</label>
                            <div class="row">
                                <div class="col-6 pr-1">
                                    <input type="number"
                                           name="min_price"
                                           class="form-control form-control-sm"
                                           placeholder="Mínimo"
                                           value="{{ request('min_price') }}">
                                </div>
                                <div class="col-6 pl-1">
                                    <input type="number"
                                           name="max_price"
                                           class="form-control form-control-sm"
                                           placeholder="Máximo"
                                           value="{{ request('max_price') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Ubicación --}}
                        <div class="form-group">
                            <label><i class="fa fa-map-marker"></i> Ubicación</label>
                            <input type="text"
                                   name="location"
                                   class="form-control"
                                   placeholder="Ciudad, provincia..."
                                   value="{{ request('location') }}">
                        </div>

                        <hr>

                        {{-- Características (solo para propiedades no-vehículo) --}}
                        <div class="form-group">
                            <label><i class="fa fa-bed"></i> Habitaciones (mín)</label>
                            <select name="min_rooms" class="form-control">
                                <option value="">Cualquiera</option>
                                @for($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}" {{ request('min_rooms') == $i ? 'selected' : '' }}>
                                        {{ $i }}+
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fa fa-bath"></i> Baños (mín)</label>
                            <select name="min_bathrooms" class="form-control">
                                <option value="">Cualquiera</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ request('min_bathrooms') == $i ? 'selected' : '' }}>
                                        {{ $i }}+
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fa fa-arrows"></i> Construcción (m² mín)</label>
                            <input type="number"
                                   name="min_construction"
                                   class="form-control"
                                   placeholder="Ej: 100"
                                   value="{{ request('min_construction') }}">
                        </div>

                        <hr>

                        {{-- Estado --}}
                        <div class="form-group">
                            <label><i class="fa fa-info-circle"></i> Estado</label>
                            <select name="status" class="form-control">
                                <option value="">Todos</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Opciones adicionales --}}
                        <div class="form-check mb-2">
                            <input type="checkbox"
                                   name="has_tour"
                                   value="1"
                                   class="form-check-input"
                                   id="hasTour"
                                   {{ request('has_tour') ? 'checked' : '' }}>
                            <label class="form-check-label" for="hasTour">
                                Solo con Tour 360°
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox"
                                   name="featured"
                                   value="1"
                                   class="form-check-input"
                                   id="featured"
                                   {{ request('featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="featured">
                                Solo destacadas
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox"
                                   name="accepts_commission"
                                   value="1"
                                   class="form-check-input"
                                   id="acceptsCommission"
                                   {{ request('accepts_commission') ? 'checked' : '' }}>
                            <label class="form-check-label" for="acceptsCommission">
                                Acepta comisión
                            </label>
                        </div>

                        <hr>

                        {{-- Ordenamiento --}}
                        <div class="form-group">
                            <label><i class="fa fa-sort"></i> Ordenar por</label>
                            <select name="sort_by" class="form-control">
                                <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>Más recientes</option>
                                <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>Más antiguos</option>
                                <option value="price_asc" {{ request('sort_by') == 'price_asc' ? 'selected' : '' }}>Precio: menor a mayor</option>
                                <option value="price_desc" {{ request('sort_by') == 'price_desc' ? 'selected' : '' }}>Precio: mayor a menor</option>
                                <option value="most_viewed" {{ request('sort_by') == 'most_viewed' ? 'selected' : '' }}>Más vistas</option>
                            </select>
                        </div>

                        {{-- Botones --}}
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-search"></i> Buscar
                        </button>

                        @if($hasFilters)
                            <a href="{{ route('search') }}" class="btn btn-outline-secondary btn-block mt-2">
                                <i class="fa fa-times"></i> Limpiar Filtros
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        {{-- Área de Resultados --}}
        <div class="col-lg-9">
            {{-- Header con contador y vista --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1">
                        @if($hasFilters)
                            Resultados de Búsqueda
                        @else
                            Todas las Propiedades
                        @endif
                    </h3>
                    <p class="text-muted mb-0">
                        {{ $properties->total() }} {{ $properties->total() == 1 ? 'propiedad encontrada' : 'propiedades encontradas' }}
                    </p>
                </div>
            </div>

            {{-- Resultados --}}
            @if($properties->count() > 0)
                @include('frontend._spin-card-styles')
                <div class="row">
                    @foreach($properties as $property)
                        @php
                            $spin = $property->active_spin;
                            $hasSpin = !empty($spin);
                        @endphp
                        <div class="col-md-4 mb-4">
                            <div class="card spin-card ftco-animate h-100">
                                {{-- SPIN VIEWER O IMAGEN --}}
                                @if($hasSpin)
                                    <div class="spin-viewer-wrap" id="spinViewer{{ $property->id }}"
                                         data-frames-dir="{{ $spin->frames_dir }}"
                                         data-frames-count="{{ $spin->frames_count }}"
                                         data-auto-rotate="{{ $property->spin_auto_rotate ? '1' : '0' }}">
                                        <canvas></canvas>
                                        <div class="spin-overlay">
                                            <span class="spin-arrows">&larr;</span>
                                            <span>Spin 360</span>
                                            <span class="spin-arrows">&rarr;</span>
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ route('property.show', $property->id) }}">
                                        <div class="spin-img-wrap"
                                            style="background-image: url('{{ $property->image_url }}')">
                                        </div>
                                    </a>
                                @endif

                                {{-- Botón de favorito --}}
                                @auth
                                    @php
                                        $isFav = \App\Favorite::isFavorite(auth()->id(), $property->id);
                                    @endphp
                                    <button onclick="toggleFavorite({{ $property->id }}, this)"
                                            class="btn btn-sm btn-link position-absolute"
                                            style="top: 10px; right: 10px; font-size: 1.5rem; color: #dc3545; text-shadow: 0 0 3px rgba(255,255,255,0.9);"
                                            title="{{ $isFav ? 'Quitar de favoritos' : 'Agregar a favoritos' }}">
                                        <i class="fa fa-heart{{ $isFav ? '' : '-o' }}"></i>
                                    </button>
                                @endauth

                                {{-- INFO --}}
                                <div class="card-info">
                                    <div class="price-row">
                                        <span class="price-main">{{ $property->formatted_price }}</span>
                                        @if(!$property->isVehicle() && $property->maintenance)
                                            <span class="price-sub">₡{{ number_format($property->maintenance) }}/mo</span>
                                        @endif
                                    </div>

                                    @if($property->isVehicle())
                                        <ul class="prop-features">
                                            @if($property->engine_cc)<li><i class="fa fa-cog"></i>{{ $property->engine_cc }} CC</li>@endif
                                            @if($property->transmission)<li><i class="fa fa-exchange"></i>{{ $property->transmission }}</li>@endif
                                            @if($property->fuel_type)<li><i class="fa fa-tint"></i>{{ $property->fuel_type }}</li>@endif
                                        </ul>
                                        <h5><a href="{{ route('property.show', $property->id) }}">{{ $property->brand }} {{ $property->model }} {{ $property->year }}</a></h5>
                                        <span class="location-text">
                                            @if($property->mileage_km){{ number_format($property->mileage_km) }} km @endif
                                            @if($property->doors)| {{ $property->doors }} puertas @endif
                                            @if($property->passengers)| {{ $property->passengers }} pasajeros @endif
                                        </span>
                                    @else
                                        <ul class="prop-features">
                                            <li><span class="flaticon-bed"></span>{{ $property->rooms }}</li>
                                            <li><span class="flaticon-bathtub"></span>{{ $property->bathrooms }}</li>
                                            <li><span class="flaticon-floor-plan"></span>{{ $property->construction }} m²</li>
                                        </ul>
                                        <h5><a href="{{ route('property.show', $property->id) }}">{{ $property->name }}</a></h5>
                                        <span class="location-text">{{ $property->location ?? 'Ubicación no disponible' }}</span>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <a href="{{ route('property.show', $property->id) }}" class="btn-detail">
                                            <i class="fa fa-info-circle mr-1"></i> Detalle
                                        </a>
                                        @if($property->has_virtual_tour)
                                            <a href="{{ route('virtual-tour', $property->id) }}" class="btn-tour">
                                                <i class="fa fa-play-circle mr-1"></i> Tour 360°
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @include('frontend._spin-viewer-script')

                {{-- Paginación --}}
                <div class="d-flex justify-content-center mt-4">
                    {{ $properties->links() }}
                </div>
            @else
                {{-- Sin resultados --}}
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fa fa-search fa-4x text-muted mb-3"></i>
                        <h4>No se encontraron propiedades</h4>
                        <p class="text-muted">Intenta ajustar tus filtros de búsqueda</p>
                        <a href="{{ route('search') }}" class="btn btn-primary mt-3">
                            <i class="fa fa-refresh"></i> Ver Todas las Propiedades
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/spin-viewer.css') }}">
<style>
    .sticky-top {
        position: sticky;
        top: 100px;
        z-index: 100;
    }

    @media (max-width: 991px) {
        .sticky-top {
            position: relative;
            top: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/favorites.js') }}"></script>
<script src="{{ asset('js/spin-viewer.js') }}"></script>
<script>
    $(document).ready(function() {
        // Inicializar spin viewers
        $('.spin-viewer-wrap').each(function() {
            const $wrap = $(this);
            const framesDir = $wrap.data('frames-dir');
            const framesCount = parseInt($wrap.data('frames-count'));
            const autoRotate = $wrap.data('auto-rotate') === '1';

            if (framesDir && framesCount > 0 && typeof SpinViewer !== 'undefined') {
                new SpinViewer({
                    element: this,
                    framesDir: framesDir,
                    framesCount: framesCount,
                    autoRotate: autoRotate
                });
            }
        });

        // Auto-submit al cambiar ordenamiento
        $('select[name="sort_by"]').on('change', function() {
            $('#searchForm').submit();
        });
    });
</script>
@endpush
