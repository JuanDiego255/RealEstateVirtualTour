@extends('admin.main')

@section('title', 'Calificaciones de ' . $user->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-star"></i> Calificaciones de {{ $user->name }}</h5>
            </div>
            <div class="card-body">
                {{-- Info del usuario --}}
                <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                         class="rounded-circle mr-3" style="width: 80px; height: 80px; object-fit: cover;">
                    <div>
                        <h4 class="mb-1">{{ $user->name }}</h4>
                        <p class="text-muted mb-2">{{ $user->email }}</p>
                        @if($user->average_rating)
                            <div>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa fa-star{{ $i <= round($user->average_rating) ? '' : '-o' }} text-warning" style="font-size: 1.3rem;"></i>
                                @endfor
                                <span class="ml-2 font-weight-bold" style="font-size: 1.2rem;">{{ number_format($user->average_rating, 2) }}</span>
                                <span class="text-muted ml-1">({{ $user->reviews_count }} {{ $user->reviews_count == 1 ? 'calificación' : 'calificaciones' }})</span>
                            </div>
                        @else
                            <p class="text-muted">Sin calificaciones aún</p>
                        @endif
                    </div>
                </div>

                {{-- Estadísticas de distribución --}}
                @if($stats['total_reviews'] > 0)
                    <div class="row mb-4">
                        <div class="col-md-8 offset-md-2">
                            <h6 class="mb-3">Distribución de Calificaciones</h6>
                            @for($i = 5; $i >= 1; $i--)
                                <div class="d-flex align-items-center mb-2">
                                    <span style="width: 60px;">
                                        {{ $i }} <i class="fa fa-star text-warning"></i>
                                    </span>
                                    <div class="progress flex-grow-1 mx-3" style="height: 25px;">
                                        @php
                                            $count = $stats['rating_distribution'][$i] ?? 0;
                                            $percentage = $stats['total_reviews'] > 0 ? ($count / $stats['total_reviews']) * 100 : 0;
                                        @endphp
                                        <div class="progress-bar bg-warning" role="progressbar"
                                             style="width: {{ $percentage }}%"
                                             aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="font-weight-bold" style="width: 40px;">{{ $count }}</span>
                                </div>
                            @endfor
                        </div>
                    </div>
                @endif

                {{-- Lista de calificaciones --}}
                <hr>
                <h5 class="mb-3">Comentarios de Clientes</h5>

                @forelse($reviews as $review)
                    <div class="card mb-3 border">
                        <div class="card-body">
                            <div class="d-flex">
                                <img src="{{ $review->reviewer->avatar_url }}" alt="{{ $review->reviewer->name }}"
                                     class="rounded-circle mr-3" style="width: 50px; height: 50px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $review->reviewer->name }}</h6>
                                            <div class="mb-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fa fa-star{{ $i <= $review->rating ? '' : '-o' }} text-warning"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if($review->sale)
                                        <p class="text-muted small mb-2">
                                            <i class="fa fa-check-circle text-success"></i> Venta verificada: <strong>{{ $review->sale->property->name ?? 'N/A' }}</strong>
                                        </p>
                                    @endif
                                    @if($review->comment)
                                        <p class="mb-0 mt-2">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info text-center">
                        <i class="fa fa-info-circle"></i> Este usuario no tiene calificaciones con comentarios aún
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

        {{-- Botón para calificar --}}
        @auth
            @if(Auth::id() !== $user->id)
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <a href="{{ route('reviews.create', $user->id) }}" class="btn btn-primary btn-lg">
                            <i class="fa fa-star"></i> Calificar a {{ $user->name }}
                        </a>
                    </div>
                </div>
            @endif
        @endauth
    </div>
</div>
@endsection
