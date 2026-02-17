@extends('admin.main')

@section('title', 'Sectores')

@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>{{ Session::get('success') }}</strong>
            <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
        </div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>{{ Session::get('error') }}</strong>
            <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
        </div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-th-large"></i> Sectores</h4>
            <a href="{{ route('admin.sectors.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Nuevo Sector
            </a>
        </div>

        <div class="row">
            @forelse ($sectors as $sector)
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 {{ !$sector->is_active ? 'border-secondary' : '' }}">
                        @if($sector->image)
                            <img src="{{ $sector->image_url }}" class="card-img-top" style="height: 150px; object-fit: cover;">
                        @else
                            <div class="card-img-top d-flex align-items-center justify-content-center"
                                 style="height: 150px; background: {{ $sector->color ?? '#6c757d' }};">
                                <i class="fa {{ $sector->icon ?? 'fa-folder' }}" style="font-size: 50px; color: rgba(255,255,255,0.8);"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $sector->name }}
                                @if(!$sector->is_active)
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </h5>
                            <p class="card-text text-muted small">{{ Str::limit($sector->description, 80) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge badge-info">{{ $sector->categories_count }} categorías</span>
                                <span class="badge badge-light">Orden: {{ $sector->sort_order }}</span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group btn-group-sm w-100">
                                <a href="{{ route('admin.sectors.edit', $sector) }}" class="btn btn-outline-primary" title="Editar">
                                    <i class="fa fa-pencil"></i> Editar
                                </a>
                                <form action="{{ route('admin.sectors.toggle-status', $sector) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-{{ $sector->is_active ? 'warning' : 'success' }}" title="{{ $sector->is_active ? 'Desactivar' : 'Activar' }}">
                                        <i class="fa fa-{{ $sector->is_active ? 'eye-slash' : 'eye' }}"></i>
                                    </button>
                                </form>
                                @if($sector->categories_count == 0)
                                <form action="{{ route('admin.sectors.destroy', $sector) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Está seguro de eliminar este sector?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fa fa-th-large fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay sectores creados</h5>
                    <a href="{{ route('admin.sectors.create') }}" class="btn btn-primary mt-2">Crear primer sector</a>
                </div>
            @endforelse
        </div>
    </div>
@endsection
