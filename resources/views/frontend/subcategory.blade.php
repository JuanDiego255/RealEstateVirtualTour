@extends('frontend.front')
@php
    $imagePath = null;
    if (!empty($subcategory->image)) {
        $imagePath = $subcategory->image;
    } elseif (!empty($category->image)) {
        $imagePath = $category->image;
    } elseif (!empty($category->cover_image)) {
        $imagePath = str_replace(url('/storage') . '/', '', $category->cover_image);
    }
@endphp

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <body>
        <div class="hero-wrap ftco-degree-bg"
            style="background-image: url('{{ $imagePath ? route('file', ['filename' => $imagePath]) : url('images/producto-sin-imagen.PNG') }}')"
            data-stellar-background-ratio="0.5">
            <div class="overlay"></div>
            <div class="container">
                <div class="row no-gutters slider-text justify-content-center align-items-center">
                    <div class="col-lg-8 col-md-6 ftco-animate d-flex align-items-end">
                        <div class="text text-center">
                            <p style="font-size: 16px; opacity: 0.8;">{{ $category->name }}</p>
                            <h1 class="mb-4">{{ $subcategory->name }}</h1>
                            @if ($subcategory->description)
                                <p style="font-size: 18px">{{ $subcategory->description }}</p>
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

        {{-- PUBLICACIONES (propiedades + vehículos unificados) --}}
        @if ($publications->count() > 0)
            @include('frontend._spin-card-styles')

            <section class="ftco-section goto-here">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-12 heading-section text-center ftco-animate mb-5">
                            <span class="subheading">{{ $subcategory->name }}</span>
                            <h2 class="mb-2">Publicaciones</h2>
                        </div>
                    </div>
                    <div class="row">
                        @foreach ($publications as $property)
                            @php
                                $spin = $property->active_spin;
                                $hasSpin = !empty($spin);
                                $isVehicleModel = $property instanceof \App\Vehicle;
                                $tourUrl = $isVehicleModel
                                    ? route('virtual-tour', ['id' => $property->id, 'type' => 'vehicle'])
                                    : route('virtual-tour', $property->id);
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
                                        <a href="{{ $tourUrl }}">
                                            <div class="spin-img-wrap"
                                                style="background-image: url('{{ isset($property->image) ? route('file', $property->image) : url('images/producto-sin-imagen.PNG') }}')">
                                            </div>
                                        </a>
                                    @endif

                                    {{-- INFO --}}
                                    <div class="card-info">
                                        <div class="price-row">
                                            <span class="price-main">{{ $property->formatted_price ?? (($property->currency ?? 'CRC') === 'USD' ? '$' : '₡') . number_format($property->price) }}</span>
                                            @if(($property->property_type ?? '') !== 'vehicle' && $property->maintenance)
                                                <span class="price-sub">₡{{ number_format($property->maintenance) }}/mo</span>
                                            @endif
                                        </div>

                                        @if(($property->property_type ?? '') === 'vehicle')
                                            <ul class="prop-features">
                                                @if($property->engine_cc)<li><i class="fa fa-cog"></i>{{ $property->engine_cc }} CC</li>@endif
                                                @if($property->transmission)<li><i class="fa fa-exchange"></i>{{ $property->transmission }}</li>@endif
                                                @if($property->fuel_type)<li><i class="fa fa-tint"></i>{{ $property->fuel_type }}</li>@endif
                                            </ul>
                                            <h5><a href="{{ $tourUrl }}">{{ $property->brand }} {{ $property->model }}</a></h5>
                                            <span class="location-text">
                                                @if($property->mileage_km){{ number_format($property->mileage_km) }} km @endif
                                                @if($property->doors)| {{ $property->doors }} puertas @endif
                                                @if($property->passengers)| {{ $property->passengers }} pasajeros @endif
                                            </span>
                                        @else
                                            <ul class="prop-features">
                                                <li><span class="flaticon-bed"></span>{{ $property->rooms }}</li>
                                                <li><span class="flaticon-bathtub"></span>{{ $property->bathrooms }}</li>
                                                <li><span class="flaticon-floor-plan"></span>{{ $property->construction }} Mt2</li>
                                            </ul>
                                            <h5><a href="{{ $tourUrl }}">{{ $property->name }}</a></h5>
                                            <span class="location-text">{{ $property->location ?? 'Ubicación no disponible' }}</span>
                                        @endif

                                        <a href="{{ $tourUrl }}" class="btn-tour">
                                            <i class="fa fa-play-circle mr-1"></i> Virtual Tour
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            @include('frontend._spin-viewer-script')
        @endif

        {{-- SIN ITEMS --}}
        @if ($publications->count() == 0)
            <section class="ftco-section goto-here">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-8 text-center">
                            <h3 class="text-muted mt-5">No hay inmuebles disponibles en esta subcategoría</h3>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- BOTÓN VOLVER --}}
        <section style="padding: 0 0 40px;">
            <div class="container text-center">
                <a href="{{ route('category.show', $category->slug) }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left mr-2"></i> Volver a {{ $category->name }}
                </a>
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
        <script src="{{ asset('virtualtour/js/main.js') }}"></script>
    </body>

    </html>
@endsection
