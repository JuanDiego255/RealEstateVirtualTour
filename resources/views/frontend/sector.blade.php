@extends('frontend.front')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <body>
        <div class="hero-wrap ftco-degree-bg"
            style="background-image: url('{{ isset($sector->image) ? route('file', $sector->image) : url('virtualtour/images/bg_1.jpg') }}')"
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
                            @php
                                $imagePath = null;
                                if (!empty($category->image)) {
                                    $imagePath = $category->image;
                                } elseif (!empty($category->cover_image)) {
                                    $imagePath = str_replace(url('/storage') . '/', '', $category->cover_image);
                                }
                                $hasLongDesc = $category->description && strlen($category->description) > 100;
                                $catId = 'cat-desc-' . $category->id;
                            @endphp

                            <div class="col-md-{{ count($categories) <= 2 ? '6' : '4' }} mb-4">
                                <div class="card ftco-animate h-100"
                                    style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s; border: none;">
                                    {{-- Imagen superior --}}
                                    <a href="{{ route('category.show', $category->slug) }}">
                                        <div style="background-image: url('{{ $imagePath ? route('file', ['filename' => $imagePath]) : url('images/producto-sin-imagen.PNG') }}');
                                                    height: 220px; background-size: cover; background-position: center;">
                                        </div>
                                    </a>

                                    {{-- Sección de contenido --}}
                                    <div class="card-body d-flex flex-column" style="min-height: 220px;">
                                        <a href="{{ route('category.show', $category->slug) }}" class="text-decoration-none text-dark">
                                            <h5 class="card-title mb-2" style="font-weight: 700;">{{ $category->name }}</h5>
                                        </a>

                                        @if ($category->location)
                                            <p class="text-muted small mb-2">
                                                <i class="fa fa-map-marker mr-1" style="color: #c2ac1f;"></i> {{ $category->location }}
                                            </p>
                                        @endif

                                        @if ($category->description)
                                            <div class="mb-2">
                                                @if ($hasLongDesc)
                                                    <p class="text-muted small mb-1">{{ Str::limit($category->description, 100) }}</p>
                                                    <div id="{{ $catId }}" style="display: none;">
                                                        <p class="text-muted small mb-1">{{ $category->description }}</p>
                                                    </div>
                                                    <a href="javascript:void(0)" class="small toggle-desc" data-target="{{ $catId }}" style="color: #c2ac1f; font-weight: 600;">
                                                        Ver m&aacute;s <i class="fa fa-chevron-down" style="font-size: 10px;"></i>
                                                    </a>
                                                @else
                                                    <p class="text-muted small mb-1">{{ $category->description }}</p>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="mt-auto">
                                            @php
                                                $catPropCount = $category->subcategories->sum('properties_count');
                                                $catVehCount = $category->subcategories->sum('vehicles_count');
                                            @endphp
                                            <div class="d-flex flex-wrap mb-2">
                                                @if ($catPropCount > 0)
                                                    <span class="badge badge-primary mr-2 mt-1" style="font-size: 0.8rem;">
                                                        <i class="fa fa-building mr-1"></i>
                                                        {{ $catPropCount }}
                                                        {{ $catPropCount == 1 ? 'propiedad' : 'propiedades' }}
                                                    </span>
                                                @endif
                                                @if ($catVehCount > 0)
                                                    <span class="badge badge-info mr-2 mt-1" style="font-size: 0.8rem;">
                                                        <i class="fa fa-car mr-1"></i>
                                                        {{ $catVehCount }}
                                                        {{ $catVehCount == 1 ? 'veh&iacute;culo' : 'veh&iacute;culos' }}
                                                    </span>
                                                @endif
                                                @if ($catPropCount == 0 && $catVehCount == 0)
                                                    <span class="badge badge-secondary mt-1">Sin items a&uacute;n</span>
                                                @endif
                                            </div>
                                            <a href="{{ route('category.show', $category->slug) }}" class="btn btn-sm btn-block" style="background-color: #c2ac1f; color: #fff; border-radius: 8px;">
                                                Explorar <i class="fa fa-arrow-right ml-1"></i>
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
        <script>
            $(document).ready(function() {
                $('.toggle-desc').on('click', function(e) {
                    e.preventDefault();
                    var target = $('#' + $(this).data('target'));
                    var shortText = $(this).prev('p').length ? $(this).siblings('p').first() : $(this).parent().find('p').first();
                    var icon = $(this).find('i');

                    if (target.is(':visible')) {
                        target.slideUp(200);
                        shortText.show();
                        $(this).html('Ver m&aacute;s <i class="fa fa-chevron-down" style="font-size: 10px;"></i>');
                    } else {
                        shortText.hide();
                        target.slideDown(200);
                        $(this).html('Ver menos <i class="fa fa-chevron-up" style="font-size: 10px;"></i>');
                    }
                });
            });
        </script>
    </body>

    </html>
@endsection
