@extends('admin.main')
@section('title', 'Nuevo Usuario')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-user-plus"></i> Nuevo Usuario</h4>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    @include('admin.users._form')
                    <hr>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Crear Usuario
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
