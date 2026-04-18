@extends('admin.main')
@section('title', 'Nuevo Premio')
@section('content')

<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.raffle-prizes.index') }}" class="btn btn-link p-0 mr-3 text-secondary">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h4 class="mb-0"><i class="fas fa-dice" style="color:#e74c3c;"></i> Nuevo Premio</h4>
    </div>

    <div class="card shadow-sm" style="max-width:700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.raffle-prizes.store') }}">
                @csrf
                @include('admin.raffle-prizes._form')
                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Premio
                </button>
                <a href="{{ route('admin.raffle-prizes.index') }}" class="btn btn-link text-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

@endsection
