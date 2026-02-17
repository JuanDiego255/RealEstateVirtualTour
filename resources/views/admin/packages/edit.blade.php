@extends('admin.main')
@section('title', 'Editar Paquete')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-cube"></i> Editar: {{ $package->name }}</h4>
            <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>
        <div class="card"><div class="card-body">
            <form action="{{ route('admin.packages.update', $package) }}" method="POST">
                @csrf @method('PUT')
                @include('admin.packages._form', ['package' => $package])
                <button type="submit" class="btn btn-primary mt-3"><i class="fa fa-save"></i> Guardar Cambios</button>
            </form>
        </div></div>
    </div>
@endsection
