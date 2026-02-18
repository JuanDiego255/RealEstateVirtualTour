@extends('admin.main')
@section('title', 'Nueva Sucursal')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-plus"></i> Nueva Sucursal</h4>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('admin.mycategories._form')
                    <hr>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Crear Sucursal
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
