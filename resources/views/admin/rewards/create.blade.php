@extends('admin.main')
@section('title', 'Nueva Recompensa')
@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('admin.rewards.index') }}" class="btn btn-sm btn-secondary mr-2">
            <i class="fa fa-arrow-left"></i> Volver
        </a>
        <h5 class="mb-0 font-weight-bold">Nueva Regla de Recompensa</h5>
    </div>
    <div class="card shadow-sm" style="max-width:680px;">
        <div class="card-header bg-warning text-dark">
            <i class="fa fa-trophy"></i> Configurar Recompensa
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.rewards.store') }}">
                @csrf
                @include('admin.rewards._form')
                <hr>
                <div class="text-right">
                    <a href="{{ route('admin.rewards.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
