@extends('frontend.front')
@php
    $imagePath = null;

    if (!empty($category->image)) {
        $imagePath = $category->image;
    } elseif (!empty($category->cover_image)) {
        // Por si viene como URL completa
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
                            <h1 class="mb-4">{{ $category->name }}</h1>
                            @if ($category->location)
                                <p style="font-size: 18px">
                                    <i class="fa fa-map-marker mr-2"></i>{{ $category->location }}
                                </p>
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

        {{-- INFORMACIÓN DE LA CATEGORÍA --}}
        @if ($category->description || $category->facilities || $category->features || $category->notes)
            <section class="ftco-section bg-light" style="padding: 40px 0;">
                <div class="container">
                    <div class="row justify-content-center">
                        @if ($category->description)
                            <div class="col-md-10 text-center ftco-animate mb-3">
                                <p class="text-muted" style="font-size: 16px;">{{ $category->description }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="row justify-content-center">
                        @if ($category->facilities)
                            <div class="col-md-4 ftco-animate mb-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fa fa-building mr-2 text-primary"></i>Facilidades
                                        </h5>
                                        <p class="card-text text-muted">{{ $category->facilities }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($category->features)
                            <div class="col-md-4 ftco-animate mb-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fa fa-star mr-2 text-warning"></i>Características
                                        </h5>
                                        <p class="card-text text-muted">{{ $category->features }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($category->notes)
                            <div class="col-md-4 ftco-animate mb-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fa fa-info-circle mr-2 text-info"></i>Notas</h5>
                                        <p class="card-text text-muted">{{ $category->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- INMUEBLES POR SUBCATEGORÍA --}}
        @php
            $hasItems = false;
            $sectionIndex = 0;
        @endphp
        @foreach ($category->subcategories as $subcategory)
            @if ($subcategory->properties->count() > 0 || $subcategory->vehicles->count() > 0)
                @php $hasItems = true; @endphp

                {{-- Propiedades de esta subcategoría --}}
                @if ($subcategory->properties->count() > 0)
                    <section class="ftco-section {{ $sectionIndex === 0 ? 'goto-here' : ($sectionIndex % 2 === 1 ? 'bg-light' : '') }}">
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
                    @php $sectionIndex++; @endphp
                @endif

                {{-- Vehículos de esta subcategoría --}}
                @if ($subcategory->vehicles->count() > 0)
                    <section class="ftco-section {{ $sectionIndex === 0 ? 'goto-here' : ($sectionIndex % 2 === 1 ? 'bg-light' : '') }}">
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
                    @php $sectionIndex++; @endphp
                @endif
            @endif
        @endforeach

        {{-- SIN ITEMS --}}
        @if (!$hasItems)
            <section class="ftco-section goto-here">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-8 text-center">
                            <h3 class="text-muted mt-5">No hay items disponibles en esta sucursal</h3>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- BOTÓN VOLVER --}}
        <section style="padding: 0 0 40px;">
            <div class="container text-center">
                <a href="{{ route('sector.show', $sector->slug) }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left mr-2"></i> Volver a {{ $sector->name }}
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
