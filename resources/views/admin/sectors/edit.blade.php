@extends('admin.main')

@section('title', 'Editar Sector')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-th-large"></i> Editar: {{ $sector->name }}</h4>
            <a href="{{ route('admin.sectors.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.sectors.update', $sector) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    @include('admin.sectors._form', ['sector' => $sector])
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
@endsection
