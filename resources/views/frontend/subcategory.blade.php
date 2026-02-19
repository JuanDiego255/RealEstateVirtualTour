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

        {{-- PROPIEDADES --}}
        @if ($subcategory->properties->count() > 0)
            <section class="ftco-section goto-here">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-12 heading-section text-center ftco-animate mb-5">
                            <span class="subheading">{{ $subcategory->name }}</span>
                            <h2 class="mb-2">Propiedades</h2>
                        </div>
                    </div>
                    <div class="row">
                        @foreach ($subcategory->properties as $property)
                            <div class="col-md-4">
                                <div class="property-wrap ftco-animate">
                                    <a href="#" class="img"
                                        style="background-image: url('{{ isset($property->image) ? route('file', $property->image) : url('images/producto-sin-imagen.PNG') }}')"></a>
                                    <div class="text">
                                        <p class="price">
                                            <span class="old-price">{{ $property->formatted_price ?? '₡' . number_format($property->price) }}</span>
                                            @if($property->maintenance)
                                                <span class="orig-price">₡{{ number_format($property->maintenance) }}<small>/mo</small></span>
                                            @endif
                                        </p>
                                        <ul class="property_list">
                                            <li><span class="flaticon-bed"></span>{{ $property->rooms }}</li>
                                            <li><span class="flaticon-bathtub"></span>{{ $property->bathrooms }}</li>
                                            <li><span class="flaticon-floor-plan"></span>{{ $property->construction }} Mt2</li>
                                        </ul>
                                        <h3><a href="{{ route('virtual-tour', $property->id) }}">{{ $property->name }}</a></h3>
                                        <span class="location">{{ $property->location ?? 'Ubicación no disponible' }}</span>
                                        <a href="{{ route('virtual-tour', $property->id) }}"
                                            class="d-flex align-items-center justify-content-center btn-custom">
                                            <span class="ion-ios-link mr-2"></span> Virtual Tour
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- VEHÍCULOS --}}
        @if ($subcategory->vehicles->count() > 0)
            <section class="ftco-section {{ $subcategory->properties->count() > 0 ? 'bg-light' : 'goto-here' }}">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-12 heading-section text-center ftco-animate mb-5">
                            <span class="subheading">{{ $subcategory->name }}</span>
                            <h2 class="mb-2">Vehículos</h2>
                        </div>
                    </div>
                    <div class="row">
                        @foreach ($subcategory->vehicles as $vehicle)
                            <div class="col-md-4">
                                <div class="property-wrap ftco-animate" style="border-radius: 12px; overflow: hidden;">
                                    <a href="#" class="img"
                                        style="background-image: url('{{ isset($vehicle->image) ? route('file', $vehicle->image) : url('images/producto-sin-imagen.PNG') }}')"></a>
                                    <div class="text">
                                        <p class="price">
                                            <span class="orig-price">₡{{ number_format($vehicle->price) }}</span>
                                        </p>
                                        <ul class="property_list">
                                            <li><i class="fa fa-cog mr-1"></i>{{ $vehicle->engine_cc }} CC</li>
                                            <li><i class="fa fa-exchange mr-1"></i>{{ $vehicle->transmission }}</li>
                                            <li><i class="fa fa-tint mr-1"></i>{{ $vehicle->fuel_type }}</li>
                                        </ul>
                                        <h3>
                                            <a href="{{ route('virtual-tour', ['id' => $vehicle->id, 'type' => 'vehicle']) }}">
                                                {{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->year }}
                                            </a>
                                        </h3>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fa fa-road mr-1"></i>{{ number_format($vehicle->mileage_km) }} km
                                                &nbsp;|&nbsp;
                                                <i class="fa fa-car mr-1"></i>{{ $vehicle->doors }} puertas
                                                &nbsp;|&nbsp;
                                                <i class="fa fa-users mr-1"></i>{{ $vehicle->passengers }} pasajeros
                                            </small>
                                        </div>
                                        @if ($vehicle->condition)
                                            <span class="badge {{ $vehicle->condition == 'Nuevo' ? 'badge-success' : 'badge-secondary' }} mb-2">
                                                {{ $vehicle->condition }}
                                            </span>
                                        @endif
                                        <a href="{{ route('virtual-tour', ['id' => $vehicle->id, 'type' => 'vehicle']) }}"
                                            class="d-flex align-items-center justify-content-center btn-custom">
                                            <span class="ion-ios-link mr-2"></span> Virtual Tour
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- SIN ITEMS --}}
        @if ($subcategory->properties->count() == 0 && $subcategory->vehicles->count() == 0)
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
