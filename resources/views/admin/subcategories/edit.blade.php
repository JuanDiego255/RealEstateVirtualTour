@extends('admin.main')
@section('title', 'Editar Categoría - ' . $subcategory->name)
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-edit"></i> Editar Categoría: <strong>{{ $subcategory->name }}</strong></h4>
            <a href="{{ route('admin.subcategories.index', $category) }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.subcategories.update', [$category, $subcategory]) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    @include('admin.subcategories._form')
                    <button type="submit" class="btn btn-primary mt-3"><i class="fa fa-save"></i> Actualizar Categoría</button>
                </form>
            </div>
        </div>
    </div>
@endsection
