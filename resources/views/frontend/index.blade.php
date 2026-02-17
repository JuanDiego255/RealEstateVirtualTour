@extends('frontend.front')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <body>
        <div class="hero-wrap ftco-degree-bg" style="background-image: url('virtualtour/images/bg_1.png')"
            data-stellar-background-ratio="0.5">
            <div class="overlay"></div>
            <div class="container">
                <div class="row no-gutters slider-text justify-content-center align-items-center">
                    <div class="col-lg-8 col-md-6 ftco-animate d-flex align-items-end">
                        <div class="text text-center">
                            <h1 class="mb-4">
                                Explora nuestra nueva implementación <br />Virtual Tour
                            </h1>
                            <p style="font-size: 18px">
                                Recorre cada espacio con tours virtuales 360 interactivos
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mouse">
                <a href="#" class="mouse-icon">
                    <div class="mouse-wheel">
                        <span class="ion-ios-arrow-round-down"></span>
                    </div>
                </a>
            </div>
        </div>

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
                                <a href="{{ route('sector.show', $sector->slug) }}" class="text-decoration-none">
                                    <div class="property-wrap ftco-animate" style="border-radius: 12px; overflow: hidden;">
                                        <div class="img"
                                            style="background-image: url('{{ isset($sector->image) ? route('file', $sector->image) : url('virtualtour/images/bg_1.jpg') }}');
                                                   height: 280px; background-size: cover; background-position: center;
                                                   position: relative;">
                                            <div
                                                style="position: absolute; bottom: 0; left: 0; right: 0;
                                                        background: linear-gradient(transparent, rgba(0,0,0,0.8));
                                                        padding: 30px 20px 20px;">
                                                <h3 class="text-white mb-1" style="font-size: 1.5rem;">
                                                    @if ($sector->icon)
                                                        <i class="fa {{ $sector->icon }} mr-2"></i>
                                                    @endif
                                                    {{ $sector->name }}
                                                </h3>
                                                @if ($sector->description)
                                                    <p class="text-white-50 mb-0 small">{{ $sector->description }}</p>
                                                @endif
                                                <span class="badge badge-light mt-2">
                                                    {{ $sector->categories->count() }}
                                                    {{ $sector->categories->count() == 1 ? 'categoría' : 'categorías' }}
                                                    disponibles
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
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
                                                <span class="orig-price">₡{{ number_format($property->maintenance) }}<small>/mo</small></span>
                                            </p>
                                            <ul class="property_list">
                                                <li><span class="flaticon-bed"></span>{{ $property->rooms }}</li>
                                                <li><span class="flaticon-bathtub"></span>{{ $property->bathrooms }}</li>
                                                <li><span class="flaticon-floor-plan"></span>{{ $property->construction }} Mt2</li>
                                            </ul>
                                            <h3><a href="#">{{ $property->name }}</a></h3>
                                            <span class="location">{{ $property->location ?? 'Ubicación no disponible' }}</span>
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
                <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
                <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00" />
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
