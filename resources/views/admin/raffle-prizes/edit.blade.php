@extends('admin.main')
@section('title', 'Editar Premio')
@section('content')

<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.raffle-prizes.index') }}" class="btn btn-link p-0 mr-3 text-secondary">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h4 class="mb-0"><i class="fas fa-dice" style="color:#e74c3c;"></i> Editar Premio</h4>
    </div>

    @if($rafflePrize->quantity !== null)
    <div class="alert alert-info alert-sm mb-3" style="max-width:700px;">
        <i class="fas fa-chart-bar"></i>
        <strong>{{ $rafflePrize->claimed }}</strong> de <strong>{{ $rafflePrize->quantity }}</strong> entregados.
        Disponibles: <strong>{{ $rafflePrize->remaining }}</strong>
    </div>
    @endif

    <div class="card shadow-sm" style="max-width:700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.raffle-prizes.update', $rafflePrize) }}">
                @csrf @method('PUT')
                @include('admin.raffle-prizes._form')
                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar
                </button>
                <a href="{{ route('admin.raffle-prizes.index') }}" class="btn btn-link text-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

@endsection
