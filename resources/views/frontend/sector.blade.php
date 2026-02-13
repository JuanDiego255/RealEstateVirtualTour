@extends('frontend.front')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <body>
        <div class="hero-wrap ftco-degree-bg" style="background-image: url('{{ isset($sector->image) ? route('file', $sector->image) : url('virtualtour/images/bg_1.jpg') }}')"
            data-stellar-background-ratio="0.5">
            <div class="overlay"></div>
            <div class="container">
                <div class="row no-gutters slider-text justify-content-center align-items-center">
                    <div class="col-lg-8 col-md-6 ftco-animate d-flex align-items-end">
                        <div class="text text-center">
                            <h1 class="mb-4">
                                @if ($sector->icon)
                                    <i class="fa {{ $sector->icon }} mr-2"></i>
                                @endif
                                {{ $sector->name }}
                            </h1>
                            @if ($sector->description)
                                <p style="font-size: 18px">{{ $sector->description }}</p>
                            @endif
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

        {{-- SECCIÓN DE CATEGORÍAS --}}
        <section class="ftco-section goto-here">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-12 heading-section text-center ftco-animate mb-5">
                        <span class="subheading">{{ $sector->name }}</span>
                        <h2 class="mb-2">Categorías disponibles</h2>
                    </div>
                </div>

                @if (count($categories) > 0)
                    <div class="row">
                        @foreach ($categories as $category)
                            <div class="col-md-{{ count($categories) <= 2 ? '6' : '4' }} mb-4">
                                <a href="{{ route('category.show', $category->slug) }}" class="text-decoration-none">
                                    <div class="property-wrap ftco-animate" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s;">
                                        <div class="img"
                                            style="background-image: url('{{ isset($category->image) ? route('file', $category->image) : url('virtualtour/images/bg_1.jpg') }}');
                                                   height: 300px; background-size: cover; background-position: center;
                                                   position: relative;">
                                            <div
                                                style="position: absolute; bottom: 0; left: 0; right: 0;
                                                        background: linear-gradient(transparent, rgba(0,0,0,0.85));
                                                        padding: 40px 20px 20px;">
                                                <h3 class="text-white mb-1" style="font-size: 1.4rem;">
                                                    {{ $category->name }}
                                                </h3>
                                                @if ($category->location)
                                                    <p class="text-white-50 mb-2 small">
                                                        <i class="fa fa-map-marker mr-1"></i> {{ $category->location }}
                                                    </p>
                                                @endif
                                                @if ($category->description)
                                                    <p class="text-white-50 mb-2 small">{{ Str::limit($category->description, 80) }}</p>
                                                @endif
                                                <div class="d-flex flex-wrap">
                                                    @if ($category->properties_count > 0)
                                                        <span class="badge badge-primary mr-2 mt-1">
                                                            <i class="fa fa-building mr-1"></i>
                                                            {{ $category->properties_count }}
                                                            {{ $category->properties_count == 1 ? 'propiedad' : 'propiedades' }}
                                                        </span>
                                                    @endif
                                                    @if ($category->vehicles_count > 0)
                                                        <span class="badge badge-info mr-2 mt-1">
                                                            <i class="fa fa-car mr-1"></i>
                                                            {{ $category->vehicles_count }}
                                                            {{ $category->vehicles_count == 1 ? 'vehículo' : 'vehículos' }}
                                                        </span>
                                                    @endif
                                                    @if ($category->properties_count == 0 && $category->vehicles_count == 0)
                                                        <span class="badge badge-secondary mt-1">Sin items aún</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="row justify-content-center">
                        <div class="col-md-8 text-center">
                            <h3 class="text-muted mt-5">No hay categorías disponibles en este sector</h3>
                            <a href="{{ url('/') }}" class="btn btn-primary mt-3">
                                <i class="fa fa-arrow-left mr-2"></i> Volver al inicio
                            </a>
                        </div>
                    </div>
                @endif

                <div class="row justify-content-center mt-4">
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left mr-2"></i> Volver a Sectores
                    </a>
                </div>
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
        <script src="{{ asset('virtualtour/js/main.js') }}"></script>
    </body>

    </html>
@endsection
