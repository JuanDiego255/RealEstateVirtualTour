@extends('admin.main')

@section('title', 'Nuevo Sector')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-th-large"></i> Nuevo Sector</h4>
            <a href="{{ route('admin.sectors.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.sectors.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('admin.sectors._form')
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Crear Sector</button>
                </form>
            </div>
        </div>
    </div>
@endsection
