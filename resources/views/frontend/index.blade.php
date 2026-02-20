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
                    <div class="row justify-content-center">
                        <div class="col-md-12 heading-section text-center ftco-animate mb-5">
                            <span class="subheading">Tours disponibles</span>
                            <h2 class="mb-2">Explora tu casa a fondo</h2>
                        </div>
                    </div>
                    <div class="row">
                        @if (count($properties) != 0)
                            @foreach ($properties as $property)
                                <div class="col-md-4">
                                    <div class="property-wrap ftco-animate">
                                        <a href="#" class="img"
                                            style="background-image: url('{{ isset($property->image) ? route('file', $property->image) : url('images/producto-sin-imagen.PNG') }}')"></a>
                                        <div class="text">
                                            <p class="price">
                                                <span class="old-price">₡{{ number_format($property->price) }}</span>
                                                <span
                                                    class="orig-price">₡{{ number_format($property->maintenance) }}<small>/mo</small></span>
                                            </p>
                                            <ul class="property_list">
                                                <li><span class="flaticon-bed"></span>{{ $property->rooms }}</li>
                                                <li><span class="flaticon-bathtub"></span>{{ $property->bathrooms }}</li>
                                                <li><span class="flaticon-floor-plan"></span>{{ $property->construction }}
                                                    Mt2</li>
                                            </ul>
                                            <h3><a href="#">{{ $property->name }}</a></h3>
                                            <span
                                                class="location">{{ $property->location ?? 'Ubicación no disponible' }}</span>
                                            <a href="{{ route('virtual-tour', $property->id) }}"
                                                class="d-flex align-items-center justify-content-center btn-custom">
                                                <span class="ion-ios-link"></span> Virtual Tour
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <center>
                                <h3 class="text-muted text-center mt-5">No hay propiedades para visualizar</h3>
                            </center>
                        @endif
                    </div>
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


    </body>

    </html>
@endsection
