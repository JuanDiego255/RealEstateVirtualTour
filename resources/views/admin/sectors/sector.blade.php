@extends('admin.main')

@section('title', 'Sectores')

@section('content')
    @if ($message = Session::has('success'))
        <div class="alert-dismiss">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>{{ Session::get('success') }}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span class="fa fa-times"></span>
                </button>
            </div>
        </div>
    @endif
    @if ($message = Session::has('error'))
        <div class="alert-dismiss">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>{{ Session::get('error') }}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span class="fa fa-times"></span>
                </button>
            </div>
        </div>
    @endif
    @include('admin.sectors.add')

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-dark"><i class="fa fa-th-large mr-2"></i>Gestión de Sectores</h4>
            <button type="button" class="btn btn-rounded btn-outline-info" data-toggle="modal" data-target="#addSector">
                <i class="fa fa-plus mr-1"></i> Nuevo Sector
            </button>
        </div>

        <div class="row mt-3">
            @if(count($sectors) > 0)
                @foreach ($sectors as $sector)
                    @include('admin.sectors.edit')
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="position-relative">
                                <img class="card-img-top" style="height: 180px; object-fit: cover;"
                                    src="{{ isset($sector->image) ? route('file', $sector->image) : url('images/producto-sin-imagen.PNG') }}">
                                <span class="badge badge-{{ $sector->status ? 'success' : 'secondary' }} position-absolute"
                                    style="top: 10px; right: 10px;">
                                    {{ $sector->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    @if($sector->icon)
                                        <i class="fa {{ $sector->icon }} mr-2"></i>
                                    @endif
                                    {{ $sector->name }}
                                </h5>
                                <p class="card-text text-muted small">
                                    {{ $sector->description ?? 'Sin descripción' }}
                                </p>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge badge-info mr-2">
                                        <i class="fa fa-folder-open mr-1"></i>{{ $sector->categories_count }} Categorías
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between">
                                <button class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                    data-target="#editSector{{ $sector->id }}">
                                    <i class="fa fa-edit mr-1"></i>Editar
                                </button>
                                <a onclick="if (confirm('¿Deseas borrar este sector y todas sus categorías?')) {
                                    document.getElementById('deleteSector{{ $sector->id }}').submit();
                                }" href="#" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash mr-1"></i>Eliminar
                                </a>
                                <form id="deleteSector{{ $sector->id }}" method="post"
                                    action="{{ url('/delete/sector/' . $sector->id) }}">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12 text-center mt-5">
                    <i class="fa fa-th-large fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay sectores registrados</h5>
                    <p class="text-muted">Crea un nuevo sector para comenzar a organizar tus tours virtuales.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
