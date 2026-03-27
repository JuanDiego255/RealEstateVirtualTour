@extends('admin.main')

@section('title', 'Mis Favoritos')

@section('content')
<div class="main-content-inner">
    <div class="row">
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="header-title mb-0">
                                <i class="fa fa-heart text-danger"></i> Mis Propiedades Favoritas
                            </h4>
                            <p class="text-muted small mb-0">Propiedades que has guardado para revisar más tarde</p>
                        </div>
                        <div>
                            <span class="badge badge-primary badge-pill" style="font-size: 1rem;">
                                {{ $favorites->total() }} {{ $favorites->total() == 1 ? 'favorito' : 'favoritos' }}
                            </span>
                        </div>
                    </div>

                    @if($favorites->count() > 0)
                        <div class="row properties-grid">
                            @foreach($favorites as $property)
                                @include('partials.property-card', ['property' => $property, 'isFavorite' => true])
                            @endforeach
                        </div>

                        {{-- Paginación --}}
                        <div class="d-flex justify-content-center mt-4">
                            {{ $favorites->links() }}
                        </div>
                    @else
                        {{-- Estado vacío --}}
                        <div class="text-center py-5">
                            <i class="fa fa-heart-o fa-4x text-muted mb-3"></i>
                            <h4>No tienes favoritos aún</h4>
                            <p class="text-muted">Explora propiedades disponibles y guarda tus favoritas aquí para acceder fácilmente</p>
                            <a href="/" class="btn btn-primary mt-3">
                                <i class="fa fa-search"></i> Explorar Propiedades
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/spin-viewer.css') }}">
<style>
    .properties-grid {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -15px;
    }

    .property-card-col {
        padding: 0 15px;
        margin-bottom: 30px;
    }

    @media (max-width: 768px) {
        .property-card-col {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }

    @media (min-width: 769px) and (max-width: 1024px) {
        .property-card-col {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @media (min-width: 1025px) {
        .property-card-col {
            flex: 0 0 33.333%;
            max-width: 33.333%;
        }
    }
</style>
@endpush

@push('script')
<script src="{{ asset('js/favorites.js') }}"></script>
<script src="{{ asset('js/spin-viewer.js') }}"></script>
<script>
    $(document).ready(function() {
        // Inicializar spin viewers si existen
        $('.spin-viewer-wrap').each(function() {
            const $wrap = $(this);
            const framesDir = $wrap.data('frames-dir');
            const framesCount = parseInt($wrap.data('frames-count'));
            const autoRotate = $wrap.data('auto-rotate') === '1';

            if (framesDir && framesCount > 0 && typeof SpinViewer !== 'undefined') {
                new SpinViewer({
                    element: this,
                    framesDir: framesDir,
                    framesCount: framesCount,
                    autoRotate: autoRotate
                });
            }
        });
    });
</script>
@endpush
