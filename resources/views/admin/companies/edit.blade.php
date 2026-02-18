@extends('admin.main')
@section('title', 'Editar Empresa')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-building"></i> Editar: {{ $company->name }}</h4>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.companies.update', $company) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    @include('admin.companies._form')
                    <hr>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
@endsection
