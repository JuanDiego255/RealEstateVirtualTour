@extends('admin.main')
@section('title', 'Nuevo Paquete')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-cube"></i> Nuevo Paquete</h4>
            <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>
        <div class="card"><div class="card-body">
            <form action="{{ route('admin.packages.store') }}" method="POST">
                @csrf
                @include('admin.packages._form')
                <button type="submit" class="btn btn-primary mt-3"><i class="fa fa-save"></i> Crear Paquete</button>
            </form>
        </div></div>
    </div>
@endsection
