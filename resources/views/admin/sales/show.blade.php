@extends('admin.main')
@section('title', 'Detalle de Venta #' . $sale->id)
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-money"></i> Venta #{{ $sale->id }}</h4>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>

        <div class="row">
            <div class="col-md-8">
                {{-- Info de la venta --}}
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <strong>Información de la Venta</strong>
                        @switch($sale->status)
                            @case('pending') <span class="badge badge-warning badge-pill px-3 py-2">Pendiente</span> @break
                            @case('confirmed') <span class="badge badge-success badge-pill px-3 py-2">Confirmada</span> @break
                            @case('cancelled') <span class="badge badge-danger badge-pill px-3 py-2">Cancelada</span> @break
                        @endswitch
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Propiedad:</strong> {{ $sale->property->name ?? 'N/A' }}</p>
                                <p><strong>Comprador:</strong> {{ $sale->buyer_name ?? 'No especificado' }}</p>
                                <p><strong>Fecha registro:</strong> {{ $sale->created_at->format('d/m/Y H:i') }}</p>
                                @if($sale->confirmed_at)
                                    <p><strong>Fecha confirmación:</strong> {{ $sale->confirmed_at->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @php $sym = ($sale->currency ?? 'CRC') === 'USD' ? '$' : '₡'; @endphp
                                <p><strong>Precio de venta:</strong>
                                    <span class="h5 text-primary">{{ $sym }}{{ number_format($sale->sale_price, 0, ',', '.') }}</span>
                                </p>
                                <p><strong>Moneda:</strong> {{ $sale->currency ?? 'CRC' }}</p>
                            </div>
                        </div>

                        @if($sale->notes)
                            <hr>
                            <p><strong>Notas:</strong></p>
                            <p class="text-muted">{{ $sale->notes }}</p>
                        @endif
                    </div>
                </div>

                {{-- Desglose de comisiones --}}
                <div class="card">
                    <div class="card-header"><strong><i class="fa fa-pie-chart"></i> Desglose de Comisiones</strong></div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="card bg-light p-3">
                                    <h6 class="text-muted">Comisión Total</h6>
                                    <h4>{{ $sale->commission_percentage }}%</h4>
                                    <p class="text-primary mb-0">{{ $sym }}{{ number_format($sale->total_commission_amount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light p-3">
                                    <h6 class="text-muted">Propietario</h6>
                                    <h4>{{ $sale->owner_commission_percentage ?? 0 }}%</h4>
                                    <p class="text-success mb-0">{{ $sym }}{{ number_format($sale->owner_commission_amount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light p-3">
                                    <h6 class="text-muted">Agente Externo</h6>
                                    <h4>{{ $sale->agent_commission_percentage ?? 0 }}%</h4>
                                    <p class="text-info mb-0">{{ $sym }}{{ number_format($sale->agent_commission_amount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        @if($sale->commissionRequest)
                            <hr>
                            <p class="small text-muted">
                                <strong>Solicitud de comisión:</strong> #{{ $sale->commissionRequest->id }} —
                                {{ $sale->commissionRequest->requesterCompany->name ?? 'N/A' }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- Propiedad --}}
                <div class="card mb-3">
                    <div class="card-header"><strong>Propiedad</strong></div>
                    @if($sale->property && $sale->property->image)
                        <img src="{{ $sale->property->image_url }}" class="card-img-top" style="height: 150px; object-fit: cover;">
                    @endif
                    <div class="card-body small">
                        <p><strong>{{ $sale->property->name ?? 'N/A' }}</strong></p>
                        <p>Tipo: {{ $sale->property->property_type_name ?? 'N/A' }}</p>
                        @if($sale->property->location)<p>Ubicación: {{ $sale->property->location }}</p>@endif
                    </div>
                </div>

                {{-- Acciones --}}
                @if($sale->status === 'pending')
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.sales.confirm', $sale) }}" method="POST" class="mb-2">
                                @csrf
                                <button class="btn btn-success btn-block" onclick="return confirm('¿Confirmar esta venta?')">
                                    <i class="fa fa-check"></i> Confirmar Venta
                                </button>
                            </form>
                            <form action="{{ route('admin.sales.cancel', $sale) }}" method="POST">
                                @csrf
                                <button class="btn btn-outline-danger btn-block" onclick="return confirm('¿Cancelar esta venta?')">
                                    <i class="fa fa-times"></i> Cancelar Venta
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Calificar al vendedor --}}
                @if($sale->status === 'confirmed' && $sale->seller && Auth::id() !== $sale->seller_id)
                    <div class="card mt-3">
                        <div class="card-header bg-warning text-white">
                            <strong><i class="fa fa-star"></i> Calificación del Vendedor</strong>
                        </div>
                        <div class="card-body text-center">
                            @php
                                $hasReviewed = \App\Review::where('reviewer_id', Auth::id())
                                    ->where('reviewee_id', $sale->seller_id)
                                    ->where('sale_id', $sale->id)
                                    ->exists();
                            @endphp

                            @if($hasReviewed)
                                <p class="text-success mb-2">
                                    <i class="fa fa-check-circle"></i> Ya has calificado a este vendedor
                                </p>
                                <a href="{{ route('reviews.user', $sale->seller_id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-star"></i> Ver calificaciones
                                </a>
                            @else
                                <p class="mb-3">¿Cómo fue tu experiencia con este vendedor?</p>
                                <a href="{{ route('reviews.create', ['user' => $sale->seller_id, 'sale_id' => $sale->id]) }}"
                                   class="btn btn-warning btn-block">
                                    <i class="fa fa-star"></i> Calificar Vendedor
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Info del vendedor --}}
                @if($sale->seller)
                    <div class="card mt-3">
                        <div class="card-header"><strong>Vendedor</strong></div>
                        <div class="card-body text-center">
                            <img src="{{ $sale->seller->avatar_url }}" alt="{{ $sale->seller->name }}"
                                 class="rounded-circle mb-2" style="width: 60px; height: 60px; object-fit: cover;">
                            <h6 class="mb-1">{{ $sale->seller->name }}</h6>
                            @if($sale->seller->average_rating)
                                <div class="mb-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa fa-star{{ $i <= round($sale->seller->average_rating) ? '' : '-o' }} text-warning"></i>
                                    @endfor
                                    <br>
                                    <small class="text-muted">{{ number_format($sale->seller->average_rating, 2) }} ({{ $sale->seller->reviews_count }})</small>
                                </div>
                            @endif
                            <a href="{{ route('reviews.user', $sale->seller_id) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i> Ver perfil
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
