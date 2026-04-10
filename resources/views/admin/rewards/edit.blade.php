@extends('admin.main')
@section('title', 'Editar Recompensa')
@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('admin.rewards.index') }}" class="btn btn-sm btn-secondary mr-2">
            <i class="fa fa-arrow-left"></i> Volver
        </a>
        <h5 class="mb-0 font-weight-bold">Editar: {{ $reward->name }}</h5>
    </div>
    <div class="card shadow-sm" style="max-width:680px;">
        <div class="card-header bg-warning text-dark">
            <i class="fa fa-edit"></i> Editar Recompensa
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.rewards.update', $reward) }}">
                @csrf @method('PUT')
                @include('admin.rewards._form')
                <hr>
                <div class="text-right">
                    <a href="{{ route('admin.rewards.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
