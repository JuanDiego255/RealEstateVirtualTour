@extends('admin.main')

@section('title', 'Mis Calificaciones')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-star"></i> Mis Calificaciones Recibidas</h5>
            </div>
            <div class="card-body">
                {{-- Estadísticas --}}
                <div class="row text-center mb-4">
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <h2 class="mb-1 font-weight-bold text-primary">
                                @if($stats['average_rating'])
                                    {{ number_format($stats['average_rating'], 2) }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </h2>
                            <p class="text-muted mb-0">Promedio General</p>
                            @if($stats['average_rating'])
                                <div class="mt-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa fa-star{{ $i <= round($stats['average_rating']) ? '' : '-o' }} text-warning"></i>
                                    @endfor
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <h2 class="mb-1 font-weight-bold text-info">{{ $stats['total_reviews'] }}</h2>
                            <p class="text-muted mb-0">Total de Calificaciones</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <p class="mb-2 font-weight-bold">Distribución</p>
                            @for($i = 5; $i >= 1; $i--)
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span style="width: 80px;">
                                        {{ $i }} <i class="fa fa-star text-warning"></i>
                                    </span>
                                    <div class="progress flex-grow-1 mx-2" style="height: 20px;">
                                        @php
                                            $count = $stats['rating_distribution'][$i] ?? 0;
                                            $percentage = $stats['total_reviews'] > 0 ? ($count / $stats['total_reviews']) * 100 : 0;
                                        @endphp
                                        <div class="progress-bar bg-warning" role="progressbar"
                                             style="width: {{ $percentage }}%"
                                             aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="text-muted" style="width: 30px;">{{ $count }}</span>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                {{-- Lista de calificaciones --}}
                <hr>
                <h5 class="mb-3">Calificaciones Detalladas</h5>

                @forelse($reviews as $review)
                    <div class="card mb-3 border">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex">
                                    <img src="{{ $review->reviewer->avatar_url }}" alt="{{ $review->reviewer->name }}"
                                         class="rounded-circle mr-3" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1">{{ $review->reviewer->name }}</h6>
                                        <div class="mb-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fa fa-star{{ $i <= $review->rating ? '' : '-o' }} text-warning"></i>
                                            @endfor
                                            <span class="ml-2 text-muted small">{{ $review->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        @if($review->sale)
                                            <p class="text-muted small mb-2">
                                                <i class="fa fa-home"></i> Relacionado a: <strong>{{ $review->sale->property->name ?? 'Venta' }}</strong>
                                            </p>
                                        @endif
                                        @if($review->comment)
                                            <p class="mb-0">{{ $review->comment }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info text-center">
                        <i class="fa fa-info-circle"></i> Aún no has recibido calificaciones
                    </div>
                @endforelse

                {{-- Paginación --}}
                @if($reviews->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $reviews->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
