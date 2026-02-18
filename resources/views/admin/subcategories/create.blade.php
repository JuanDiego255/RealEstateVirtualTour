@extends('admin.main')
@section('title', 'Nueva Categoría - ' . $category->name)
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-plus"></i> Nueva Categoría en <strong>{{ $category->name }}</strong></h4>
            <a href="{{ route('admin.subcategories.index', $category) }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.subcategories.store', $category) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('admin.subcategories._form')
                    <button type="submit" class="btn btn-success mt-3"><i class="fa fa-save"></i> Crear Categoría</button>
                </form>
            </div>
        </div>
    </div>
@endsection
