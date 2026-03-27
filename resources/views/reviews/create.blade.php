@extends('admin.main')

@section('title', 'Calificar Usuario')

@section('content')
<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-star"></i> Calificar Usuario</h5>
            </div>
            <div class="card-body">
                {{-- Info del usuario a calificar --}}
                <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                    <img src="{{ $reviewee->avatar_url }}" alt="{{ $reviewee->name }}"
                         class="rounded-circle mr-3" style="width: 60px; height: 60px; object-fit: cover;">
                    <div>
                        <h5 class="mb-1">{{ $reviewee->name }}</h5>
                        <p class="text-muted mb-0">{{ $reviewee->email }}</p>
                        @if($reviewee->average_rating)
                            <div class="mt-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa fa-star{{ $i <= round($reviewee->average_rating) ? '' : '-o' }} text-warning"></i>
                                @endfor
                                <small class="text-muted ml-1">({{ $reviewee->reviews_count }} {{ $reviewee->reviews_count == 1 ? 'calificación' : 'calificaciones' }})</small>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Info de la venta (si existe) --}}
                @if(isset($sale))
                    <div class="alert alert-info">
                        <strong><i class="fa fa-info-circle"></i> Venta relacionada:</strong>
                        <br>
                        Propiedad: <strong>{{ $sale->property->name ?? 'N/A' }}</strong>
                        <br>
                        Precio: <strong>{{ $sale->currency === 'USD' ? '$' : '₡' }}{{ number_format($sale->sale_price) }}</strong>
                    </div>
                @endif

                {{-- Formulario --}}
                <form action="{{ route('reviews.store', $reviewee->id) }}" method="POST">
                    @csrf

                    @if(isset($sale))
                        <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                    @endif

                    {{-- Rating con estrellas --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Calificación <span class="text-danger">*</span></label>
                        <div class="star-rating">
                            <input type="hidden" name="rating" id="rating" value="5" required>
                            <div class="stars" style="font-size: 2rem; cursor: pointer;">
                                <i class="fa fa-star star-icon" data-rating="1"></i>
                                <i class="fa fa-star star-icon" data-rating="2"></i>
                                <i class="fa fa-star star-icon" data-rating="3"></i>
                                <i class="fa fa-star star-icon" data-rating="4"></i>
                                <i class="fa fa-star star-icon" data-rating="5"></i>
                            </div>
                            <small class="form-text text-muted">Haz clic en las estrellas para calificar (1-5)</small>
                        </div>
                        @error('rating')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Comentario --}}
                    <div class="form-group">
                        <label for="comment" class="font-weight-bold">Comentario (opcional)</label>
                        <textarea name="comment" id="comment" rows="4" class="form-control"
                                  placeholder="Comparte tu experiencia trabajando con este usuario...">{{ old('comment') }}</textarea>
                        @error('comment')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Máximo 1000 caracteres</small>
                    </div>

                    {{-- Botones --}}
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane"></i> Enviar Calificación
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .star-rating .stars {
        display: inline-block;
    }

    .star-icon {
        color: #ddd;
        transition: color 0.2s ease;
    }

    .star-icon.active {
        color: #ffc107;
    }

    .star-icon:hover {
        color: #ffc107;
    }
</style>
@endpush

@push('script')
<script>
    $(document).ready(function() {
        const $stars = $('.star-icon');
        const $ratingInput = $('#rating');

        // Inicializar con 5 estrellas por defecto
        updateStars(5);

        $stars.on('click', function() {
            const rating = $(this).data('rating');
            $ratingInput.val(rating);
            updateStars(rating);
        });

        $stars.on('mouseenter', function() {
            const rating = $(this).data('rating');
            updateStars(rating);
        });

        $('.stars').on('mouseleave', function() {
            const currentRating = $ratingInput.val();
            updateStars(currentRating);
        });

        function updateStars(rating) {
            $stars.each(function() {
                const starRating = $(this).data('rating');
                if (starRating <= rating) {
                    $(this).removeClass('fa-star-o').addClass('fa-star active');
                } else {
                    $(this).removeClass('fa-star active').addClass('fa-star-o');
                }
            });
        }
    });
</script>
@endpush
