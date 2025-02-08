@extends('frontend.front')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <body>
        <div class="hero-wrap ftco-degree-bg" style="background-image: url('virtualtour/images/bg_1.jpg')"
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
                                Detalla y observa cada espacio de nuestras casas a la venta
                            </p>
                            {{--  <form action="#" class="search-location mt-md-5">
                                <div class="row justify-content-center">
                                    <div class="col-lg-10 align-items-end">
                                        <div class="form-group">
                                            <div class="form-field">
                                                <input type="text" class="form-control" placeholder="Search location" />
                                                <button>
                                                    <span class="ion-ios-search"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form> --}}
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

        <section class="ftco-section goto-here">
            <div class="container">
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
                                        style="background-image: url('{{ asset('storage') . '/' . $property->image }}')"></a>
                                    <div class="text">
                                        <p class="price">
                                            <span class="old-price">₡{{ number_format($property->price) }}</span>
                                            <span
                                                class="orig-price">₡{{ number_format($property->maintenance) }}<small>/mo</small></span>
                                        </p>
                                        <ul class="property_list">
                                            <li><span class="flaticon-bed"></span>{{ $property->rooms }}</li>
                                            <li><span class="flaticon-bathtub"></span>{{ $property->bathrooms }}</li>
                                            <li><span class="flaticon-floor-plan"></span>{{ $property->construction }} Mt2
                                            </li>
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
                        <h3 class="text-muted text-center mt-5">No hay propiedades para visualizar</h3>
                    @endif
                </div>
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
