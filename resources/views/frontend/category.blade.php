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

        {{-- SUBCATEGORÍAS DE LA SUCURSAL --}}
        <section class="ftco-section goto-here">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-12 heading-section text-center ftco-animate mb-5">
                        <span class="subheading">{{ $category->name }}</span>
                        <h2 class="mb-2">Subcategorías</h2>
                    </div>
                </div>

                @php
                    // Detectar si es sector automotriz
                    $isAutomotriz = isset($sector) && (
                        $sector->slug === 'sector-automotriz' ||
                        stripos($sector->name, 'automotriz') !== false ||
                        stripos($sector->name, 'vehículo') !== false
                    );
                @endphp

                @if ($category->subcategories->count() > 0)
                    <div class="row">
                        @foreach ($category->subcategories as $subcategory)
                            @php
                                if ($isAutomotriz) {
                                    // En sector automotriz, contar vehículos desde properties
                                    $subVehCount = $subcategory->properties()->where('property_type', 'vehicle')->count();
                                    $subPropCount = 0;
                                    $subTotal = $subVehCount;
                                } else {
                                    // En otros sectores, contar propiedades (excluyendo vehículos)
                                    $subPropCount = $subcategory->properties()->where('property_type', '!=', 'vehicle')->count();
                                    $subVehCount = 0;
                                    $subTotal = $subPropCount;
                                }
                            @endphp
                            <div class="col-md-{{ $category->subcategories->count() <= 2 ? '6' : '4' }} mb-4">
                                <div class="card ftco-animate h-100"
                                    style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s; border: none;">
                                    {{-- Imagen superior --}}
                                    <a href="{{ route('subcategory.show', [$category->slug, $subcategory->slug]) }}">
                                        @if($subcategory->image)
                                            <div style="background-image: url('{{ route('file', ['filename' => $subcategory->image]) }}');
                                                        height: 300px; background-size: cover; background-position: center;"></div>
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 220px;">
                                                <i class="fa fa-tags fa-3x text-muted"></i>
                                            </div>
                                        @endif
                                    </a>

                                    {{-- Contenido --}}
                                    <div class="card-body d-flex flex-column" style="min-height: 180px;">
                                        <a href="{{ route('subcategory.show', [$category->slug, $subcategory->slug]) }}" class="text-decoration-none text-dark">
                                            <h5 class="card-title mb-2" style="font-weight: 700;">{{ $subcategory->name }}</h5>
                                        </a>

                                        @if ($subcategory->description)
                                            <p class="text-muted small mb-2">{{ Str::limit($subcategory->description, 100) }}</p>
                                        @endif

                                        <div class="mt-auto">
                                            <div class="d-flex flex-wrap mb-2">
                                                @if ($isAutomotriz)
                                                    @if ($subVehCount > 0)
                                                        <span class="badge badge-info mr-2 mt-1" style="font-size: 0.8rem;">
                                                            <i class="fa fa-car mr-1"></i>{{ $subVehCount }}
                                                            {{ $subVehCount == 1 ? 'vehículo' : 'vehículos' }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary mt-1">Sin vehículos aún</span>
                                                    @endif
                                                @else
                                                    @if ($subPropCount > 0)
                                                        <span class="badge badge-primary mr-2 mt-1" style="font-size: 0.8rem;">
                                                            <i class="fa fa-building mr-1"></i>{{ $subPropCount }}
                                                            {{ $subPropCount == 1 ? 'propiedad' : 'propiedades' }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary mt-1">Sin inmuebles aún</span>
                                                    @endif
                                                @endif
                                            </div>
                                            <a href="{{ route('subcategory.show', [$category->slug, $subcategory->slug]) }}" class="btn btn-sm btn-block" style="background-color: #c2ac1f; color: #fff; border-radius: 8px;">
                                                {{ $isAutomotriz ? 'Ver vehículos' : 'Ver inmuebles' }} <i class="fa fa-arrow-right ml-1"></i>
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
                            <h3 class="text-muted mt-5">No hay subcategorías disponibles en esta sucursal</h3>
                        </div>
                    </div>
                @endif
            </div>
        </section>

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
