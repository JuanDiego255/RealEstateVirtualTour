@extends('frontend.front')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <body>
        <div class="hero-wrap ftco-degree-bg" style="background-image: url('{{ url('virtualtour/images/bg_1.png') }}')"
            data-stellar-background-ratio="0.5">
            <div class="overlay"></div>
            <div class="container">
                <div class="row no-gutters slider-text justify-content-center align-items-center">
                    <div class="col-lg-8 col-md-6 ftco-animate d-flex align-items-end">
                        <div class="text text-center">
                            <h1 class="mb-4">
                                Planes y Precios
                            </h1>
                            <p style="font-size: 18px">
                                Elige el plan ideal para tu negocio inmobiliario
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

        {{-- SECCIÓN DE PAQUETES --}}
        <section class="ftco-section goto-here">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-12 heading-section text-center ftco-animate mb-5">
                        <span class="subheading">Nuestros Planes</span>
                        <h2 class="mb-2">Selecciona el plan que mejor se adapte a tu empresa</h2>
                    </div>
                </div>

                @if (count($packages) > 0)
                    <div class="row justify-content-center">
                        @foreach ($packages as $package)
                            <div class="col-md-4 mb-4">
                                <div class="property-wrap ftco-animate"
                                    style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s; position: relative;
                                           {{ $package->is_featured ? 'border: 2px solid #c2ac1f;' : '' }}">

                                    {{-- Header del paquete --}}
                                    <div style="background: {{ $package->is_featured ? 'linear-gradient(135deg, #c2ac1f, #a89318)' : 'linear-gradient(135deg, #343a40, #495057)' }};
                                                padding: 30px 20px; text-align: center; position: relative;">

                                        @if ($package->is_featured)
                                            <span class="badge badge-light"
                                                style="position: absolute; top: 10px; right: 10px; font-size: 0.75rem;">
                                                <i class="fa fa-star mr-1"></i> Popular
                                            </span>
                                        @endif

                                        <h3 class="text-white mb-2" style="font-size: 1.5rem; font-weight: 700;">
                                            {{ $package->name }}
                                        </h3>

                                        <div class="text-white mb-2">
                                            <span style="font-size: 2.5rem; font-weight: 700;">
                                                {{ $package->formatted_price }}
                                            </span>
                                            <span style="font-size: 0.9rem; opacity: 0.8;">
                                                / {{ $package->billing_period_name }}
                                            </span>
                                        </div>

                                        @if ($package->description)
                                            <p class="text-white-50 mb-0 small">{{ $package->description }}</p>
                                        @endif
                                    </div>

                                    {{-- Features del paquete --}}
                                    <div style="padding: 25px 20px; background: #fff;">
                                        <ul class="list-unstyled mb-4">
                                            <li class="d-flex align-items-center mb-3">
                                                <i class="fa fa-check-circle mr-2" style="color: #c2ac1f;"></i>
                                                <span><strong>{{ $package->max_users }}</strong> {{ $package->max_users == 1 ? 'usuario' : 'usuarios' }}</span>
                                            </li>
                                            <li class="d-flex align-items-center mb-3">
                                                <i class="fa fa-check-circle mr-2" style="color: #c2ac1f;"></i>
                                                <span><strong>{{ $package->max_categories }}</strong> {{ $package->max_categories == 1 ? 'categoría' : 'categorías' }}</span>
                                            </li>
                                            <li class="d-flex align-items-center mb-3">
                                                <i class="fa fa-check-circle mr-2" style="color: #c2ac1f;"></i>
                                                <span><strong>{{ $package->max_posts_per_category }}</strong> propiedades por categoría</span>
                                            </li>
                                            <li class="d-flex align-items-center mb-3">
                                                @if ($package->allows_tours)
                                                    <i class="fa fa-check-circle mr-2" style="color: #c2ac1f;"></i>
                                                    <span>Tours virtuales 360°
                                                        @if ($package->tour_price > 0)
                                                            ({{ $package->formatted_tour_price }} c/u)
                                                        @else
                                                            incluidos
                                                        @endif
                                                    </span>
                                                @else
                                                    <i class="fa fa-times-circle mr-2" style="color: #ccc;"></i>
                                                    <span class="text-muted">Tours virtuales 360°</span>
                                                @endif
                                            </li>
                                            <li class="d-flex align-items-center mb-3">
                                                @if ($package->allows_commission)
                                                    <i class="fa fa-check-circle mr-2" style="color: #c2ac1f;"></i>
                                                    <span>Bolsa Inmobiliaria</span>
                                                @else
                                                    <i class="fa fa-times-circle mr-2" style="color: #ccc;"></i>
                                                    <span class="text-muted">Bolsa Inmobiliaria</span>
                                                @endif
                                            </li>

                                            @if ($package->features && is_array($package->features))
                                                @foreach ($package->features as $featureKey => $featureValue)
                                                    @if (is_bool($featureValue) || $featureValue === 'true' || $featureValue === 'false')
                                                        <li class="d-flex align-items-center mb-3">
                                                            @if ($featureValue && $featureValue !== 'false')
                                                                <i class="fa fa-check-circle mr-2" style="color: #c2ac1f;"></i>
                                                                <span>{{ ucfirst(str_replace('_', ' ', $featureKey)) }}</span>
                                                            @else
                                                                <i class="fa fa-times-circle mr-2" style="color: #ccc;"></i>
                                                                <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $featureKey)) }}</span>
                                                            @endif
                                                        </li>
                                                    @else
                                                        <li class="d-flex align-items-center mb-3">
                                                            <i class="fa fa-check-circle mr-2" style="color: #c2ac1f;"></i>
                                                            <span>{{ ucfirst(str_replace('_', ' ', $featureKey)) }}: <strong>{{ $featureValue }}</strong></span>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </ul>

                                        @if ($package->trial_days > 0)
                                            <p class="text-center mb-3" style="color: #c2ac1f; font-weight: 600; font-size: 0.9rem;">
                                                <i class="fa fa-gift mr-1"></i> {{ $package->trial_days }} días de prueba gratis
                                            </p>
                                        @endif

                                        <div class="text-center">
                                            <a href="{{ route('register.company', ['package' => $package->id]) }}"
                                                class="d-inline-block btn-custom px-4 py-2"
                                                style="border-radius: 30px; font-weight: 600; text-decoration: none;
                                                       {{ $package->is_featured ? 'background: #c2ac1f; color: #fff;' : '' }}">
                                                <i class="fa fa-rocket mr-1"></i> Comenzar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="row justify-content-center">
                        <div class="col-md-8 text-center">
                            <h3 class="text-muted mt-5">No hay planes disponibles en este momento</h3>
                            <a href="{{ url('/') }}" class="btn btn-primary mt-3">
                                <i class="fa fa-arrow-left mr-2"></i> Volver al inicio
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Sección de FAQ rápido --}}
                <div class="row justify-content-center mt-5">
                    <div class="col-md-8 text-center ftco-animate">
                        <h4 class="mb-4">¿Tiene preguntas?</h4>
                        <p class="text-muted mb-4">
                            Todos los planes incluyen soporte técnico y actualizaciones.
                            Puede cambiar de plan en cualquier momento.
                        </p>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left mr-2"></i> Volver al inicio
                        </a>
                    </div>
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
