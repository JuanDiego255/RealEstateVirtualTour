@extends('frontend.front')

@section('content')
    <!DOCTYPE html>
    <html lang="en">
    <style>
        <style>

        /* El hero SI debe tener altura para que todo aparezca */
        .hero-carousel {
            position: relative;
            height: 100vh;
            min-height: 600px;
            overflow: hidden;
        }

        /* Cada slide debe ser relativo para que overlay/fondo absolutosen bien */
        #heroCarousel .carousel-item {
            position: relative;
        }

        /* Fondo ocupa TODO el slide */
        .hero-slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transform: scale(1.03);
            z-index: 0;
        }

        /* Overlay para legibilidad */
        .hero-overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
            background: rgba(0, 0, 0, 0.35);
        }

        /* Contenido centrado encima de todo */
        .hero-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
        }

        /* Fade del carrusel (más suave) */
        .carousel.carousel-fade .carousel-item {
            transition: opacity 1.8s ease-in-out !important;
        }

        /* --- Animación del texto (stagger) --- */
        .hero-title,
        .hero-subtitle {
            opacity: 0;
            transform: translateY(14px);
            transition: opacity 650ms ease, transform 650ms ease;
        }

        /* Título entra primero */
        .carousel-item.active .hero-title {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 220ms;
        }

        /* Subtítulo entra después (stagger) */
        .carousel-item.active .hero-subtitle {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 420ms;
        }
    </style>

    <body>
        {{-- Banner Principal con Carrusel + Texto por Slide (CORREGIDO) --}}
        <div class="hero-wrap ftco-degree-bg hero-carousel">

            <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-ride="carousel" data-interval="6500"
                data-pause="false">

                <div class="carousel-inner h-100">
                    <div class="carousel-item h-100">
                        <div class="hero-slide" style="background-image: url('{{ asset('virtualtour/images/bg_3.jpeg') }}')">
                        </div>
                        <div class="hero-overlay"></div>

                        <div class="hero-content">
                            <div class="hero-caption text-center">
                                <h4 class="mb-3 hero-title text-white">
                                    Automotriz: tours fluidos con hotspots y navegación clara para tus vehículos.
                                </h4>
                                <p class="mb-0 hero-subtitle text-white" style="font-size: 18px">
                                    Más interés, menos fricción, mejores cierres.
                                </p>
                            </div>
                        </div>
                    </div>
                    {{-- Slide 1 --}}
                    <div class="carousel-item active h-100">
                        <div class="hero-slide"
                            style="background-image: url('{{ asset('virtualtour/images/bg_1.jpeg') }}')">
                        </div>
                        <div class="hero-overlay"></div>

                        <div class="hero-content">
                            <div class="hero-caption text-center">
                                <h4 class="mb-3 hero-title text-white">
                                    Explora una experiencia 360 moderna que eleva la forma de mostrar tus espacios.
                                </h4>
                                <p class="mb-0 hero-subtitle text-white" style="font-size: 18px">
                                    Recorre cada detalle desde cualquier dispositivo, en segundos.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Slide 2 --}}
                    <div class="carousel-item h-100">
                        <div class="hero-slide" style="background-image: url('{{ asset('virtualtour/images/bg_2.jpg') }}')">
                        </div>
                        <div class="hero-overlay"></div>

                        <div class="hero-content">
                            <div class="hero-caption text-center">
                                <h4 class="mb-3 hero-title text-white">
                                    Inmobiliaria: tours fluidos con hotspots y navegación clara para tus propiedades.
                                </h4>
                                <p class="mb-0 hero-subtitle text-white" style="font-size: 18px">
                                    Más interés, menos fricción, mejores cierres.
                                </p>
                            </div>
                        </div>
                    </div>


                </div>

                {{-- Controles (opcionales) --}}
            </div>

            <div class="mouse">
                <a href="#" class="mouse-icon">
                    <div class="mouse-wheel">
                        <span class="ion-ios-arrow-round-down"></span>
                    </div>
                </a>
            </div>
        </div>
        {{-- /Banner --}}

        {{-- ACCESO RÁPIDO AL KIOSK --}}
        <section class="py-4" style="background: linear-gradient(135deg, #c2ac1f 0%, #a89518 100%);">
            <div class="container">
                <div class="row align-items-center justify-content-between">
                    <div class="col-md-8">
                        <h4 class="mb-1 text-white"><i class="fa fa-desktop mr-2"></i> Modo Kiosko</h4>
                        <p class="mb-0 text-white" style="opacity: 0.9;">Explora vehículos con spin 360 interactivo</p>
                    </div>
                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <a href="{{ route('kiosk.index') }}" class="btn btn-light btn-lg" style="font-weight: 600;">
                            <i class="fa fa-play-circle mr-2"></i> Ir al Kiosko
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECCIÓN DE BÚSQUEDA RÁPIDA --}}
        <section class="ftco-section bg-light py-5">
            <div class="container">
                <div class="row justify-content-center mb-4">
                    <div class="col-md-10 text-center">
                        <h3 class="mb-3">Encuentra tu Propiedad Ideal</h3>
                        <p class="text-muted">Busca entre miles de propiedades disponibles</p>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <form action="{{ route('search') }}" method="GET" class="card shadow-sm">
                            <div class="card-body py-4">
                                <div class="row align-items-end">
                                    <div class="col-md-3 mb-3 mb-md-0">
                                        <label class="small text-muted mb-1">Tipo de Propiedad</label>
                                        <select name="property_type" class="form-control">
                                            <option value="">Todos los tipos</option>
                                            <option value="house">Casa</option>
                                            <option value="apartment">Apartamento</option>
                                            <option value="land">Lote/Terreno</option>
                                            <option value="vehicle">Vehículo</option>
                                            <option value="commercial">Comercial</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3 mb-md-0">
                                        <label class="small text-muted mb-1">Ubicación</label>
                                        <input type="text" name="location" class="form-control" placeholder="Ciudad, provincia...">
                                    </div>
                                    <div class="col-md-3 mb-3 mb-md-0">
                                        <label class="small text-muted mb-1">Precio Máximo</label>
                                        <input type="number" name="max_price" class="form-control" placeholder="Ej: 50000000">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary btn-block" style="background-color: #c2ac1f; border-color: #c2ac1f;">
                                            <i class="fa fa-search"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="{{ route('search') }}" class="text-muted small">
                                        <i class="fa fa-sliders"></i> Búsqueda avanzada con más filtros
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECCIÓN DE PROPIEDADES DESTACADAS --}}
        @if(isset($featuredProperties) && $featuredProperties->count() > 0)
        <section class="ftco-section pb-5">
            <div class="container">
                <div class="row justify-content-center mb-4">
                    <div class="col-md-12 heading-section text-center ftco-animate">
                        <span class="subheading">Propiedades Destacadas</span>
                        <h2 class="mb-2">Las Mejores Opciones para Ti</h2>
                    </div>
                </div>
                @include('frontend._spin-card-styles')
                <div class="row">
                    @foreach($featuredProperties as $property)
                        @php
                            $spin = $property->active_spin;
                            $hasSpin = !empty($spin);
                        @endphp
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="card spin-card ftco-animate h-100">
                                <div style="position: relative;">
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
                                                style="top: 10px; right: 10px; font-size: 1.5rem; z-index: 10; padding: 5px 10px; background: rgba(255,255,255,0.9); border-radius: 50%; line-height: 1;"
                                                title="{{ $isFav ? 'Quitar de favoritos' : 'Agregar a favoritos' }}">
                                            <i class="fa fa-heart{{ $isFav ? '' : '-o' }}" style="color: #dc3545;"></i>
                                        </button>
                                    @endauth
                                </div>

                                <div class="card-info">
                                    <div class="price-row">
                                        <span class="price-main">{{ $property->formatted_price }}</span>
                                        @if($property->maintenance && !$property->isVehicle())
                                            <span class="price-sub">₡{{ number_format($property->maintenance) }}/mo</span>
                                        @endif
                                    </div>
                                    @if(!$property->isVehicle())
                                        <ul class="prop-features">
                                            <li><span class="flaticon-bed"></span>{{ $property->rooms }}</li>
                                            <li><span class="flaticon-bathtub"></span>{{ $property->bathrooms }}</li>
                                            <li><span class="flaticon-floor-plan"></span>{{ $property->construction }} m²</li>
                                        </ul>
                                    @endif
                                    <h5><a href="{{ route('property.show', $property->id) }}">{{ \Illuminate\Support\Str::limit($property->name, 30) }}</a></h5>
                                    <span class="location-text">{{ $property->location ?? 'Ubicación no disponible' }}</span>
                                    <div class="d-flex justify-content-between">
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
                <div class="text-center mt-4">
                    <a href="{{ route('search', ['featured' => 1]) }}" class="btn btn-outline-primary btn-lg">
                        Ver todas las destacadas <i class="fa fa-arrow-right ml-2"></i>
                    </a>
                </div>
                @include('frontend._spin-viewer-script')
            </div>
        </section>
        @endif

        {{-- SECCIÓN DE SECTORES --}}
        <section class="ftco-section goto-here">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-12 heading-section text-center ftco-animate mb-5">
                        <span class="subheading">Nuestros Sectores</span>
                        <h2 class="mb-2">Elige el sector que deseas explorar</h2>
                    </div>
                </div>

                @if (count($sectors) > 0)
                    <div class="row justify-content-center">
                        @foreach ($sectors as $sector)
                            <div class="col-md-{{ count($sectors) <= 2 ? '5' : '4' }} mb-4">
                                <div class="card ftco-animate h-100"
                                    style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; transition: transform 0.3s;">
                                    {{-- Imagen superior --}}
                                    <a href="{{ route('sector.show', $sector->slug) }}">
                                        <div
                                            style="background-image: url('{{ isset($sector->image) ? route('file', $sector->image) : url('virtualtour/images/bg_1.jpg') }}');
                                                    height: 220px; background-size: cover; background-position: center;">
                                        </div>
                                    </a>

                                    {{-- Contenido --}}
                                    <div class="card-body d-flex flex-column" style="min-height: 180px;">
                                        <a href="{{ route('sector.show', $sector->slug) }}"
                                            class="text-decoration-none text-dark">
                                            <h5 class="card-title mb-2" style="font-weight: 700;">
                                                @if ($sector->icon)
                                                    <i class="fa {{ $sector->icon }} mr-2" style="color: #c2ac1f;"></i>
                                                @endif
                                                {{ $sector->name }}
                                            </h5>
                                        </a>
                                        @if ($sector->description)
                                            <p class="text-muted small mb-2">{{ $sector->description }}</p>
                                        @endif

                                        <div class="mt-auto">
                                            <span class="badge badge-light mb-2" style="font-size: 0.8rem;">
                                                {{ $sector->categories->count() }}
                                                {{ $sector->categories->count() == 1 ? 'sucursal' : 'sucursales' }}
                                                disponibles
                                            </span>
                                            <a href="{{ route('sector.show', $sector->slug) }}"
                                                class="btn btn-sm btn-block"
                                                style="background-color: #c2ac1f; color: #fff; border-radius: 8px;">
                                                Explorar <i class="fa fa-arrow-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Fallback: mostrar propiedades directamente si no hay sectores --}}
                    @include('frontend._spin-card-styles')
                    <div class="row justify-content-center">
                        <div class="col-md-12 heading-section text-center ftco-animate mb-5">
                            <span class="subheading">Tours disponibles</span>
                            <h2 class="mb-2">Explora tu casa a fondo</h2>
                        </div>
                    </div>
                    <div class="row">
                        @if (count($properties) != 0)
                            @foreach ($properties as $property)
                                @php
                                    $spin = $property->active_spin;
                                    $hasSpin = !empty($spin);
                                @endphp
                                <div class="col-md-4 mb-4">
                                    <div class="card spin-card ftco-animate h-100">
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
                                            <a href="{{ route('virtual-tour', $property->id) }}">
                                                <div class="spin-img-wrap"
                                                    style="background-image: url('{{ isset($property->image) ? route('file', $property->image) : url('images/producto-sin-imagen.PNG') }}')">
                                                </div>
                                            </a>
                                        @endif
                                        <div class="card-info">
                                            <div class="price-row">
                                                <span class="price-main">{{ $property->formatted_price ?? '₡' . number_format($property->price) }}</span>
                                                @if($property->maintenance)
                                                    <span class="price-sub">₡{{ number_format($property->maintenance) }}/mo</span>
                                                @endif
                                            </div>
                                            <ul class="prop-features">
                                                <li><span class="flaticon-bed"></span>{{ $property->rooms }}</li>
                                                <li><span class="flaticon-bathtub"></span>{{ $property->bathrooms }}</li>
                                                <li><span class="flaticon-floor-plan"></span>{{ $property->construction }} Mt2</li>
                                            </ul>
                                            <h5><a href="{{ route('virtual-tour', $property->id) }}">{{ $property->name }}</a></h5>
                                            <span class="location-text">{{ $property->location ?? 'Ubicación no disponible' }}</span>
                                            <a href="{{ route('virtual-tour', $property->id) }}" class="btn-tour">
                                                <i class="fa fa-play-circle mr-1"></i> Virtual Tour
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center">
                                <h3 class="text-muted mt-5">No hay propiedades para visualizar</h3>
                            </div>
                        @endif
                    </div>
                    @include('frontend._spin-viewer-script')
                @endif
            </div>
        </section>

        <!-- loader -->
        <div id="ftco-loader" class="show fullscreen">
            <svg class="circular" width="48px" height="48px">
                <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4"
                    stroke="#eeeeee" />
                <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4"
                    stroke-miterlimit="10" stroke="#F96D00" />
            </svg>
        </div>
        @include('frontend.footer')
        <script src="{{ asset('virtualtour/js/jquery.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery-migrate-3.0.1.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/popper.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery.easing.1.3.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery.waypoints.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery.stellar.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/owl.carousel.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery.magnific-popup.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/aos.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery.animateNumber.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/bootstrap-datepicker.js') }}"></script>
        <script src="{{ asset('virtualtour/js/jquery.timepicker.min.js') }}"></script>
        <script src="{{ asset('virtualtour/js/scrollax.min.js') }}"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
        <script src="{{ asset('virtualtour/js/google-map.js') }}"></script>
        <script src="{{ asset('virtualtour/js/main.js') }}"></script>
        <script src="{{ asset('js/favorites.js') }}"></script>


    </body>

    </html>
@endsection
