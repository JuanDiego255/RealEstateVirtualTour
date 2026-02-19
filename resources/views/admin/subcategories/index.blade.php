@extends('admin.main')
@section('title', 'Categorías - ' . $category->name)
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show"><strong>{{ Session::get('error') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4><i class="fa fa-tags"></i> Categorías de <strong>{{ $category->name }}</strong></h4>
                <small class="text-muted">
                    @php
                        $package = $category->company ? $category->company->getCurrentPackage() : null;
                        $maxSubs = $package ? $package->max_subcategories_per_category : '∞';
                    @endphp
                    {{ $subcategories->total() }} de {{ $maxSubs }} categorías
                </small>
            </div>
            <div>
                <a href="{{ route('admin.subcategories.create', $category) }}" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Nueva Categoría</a>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Sucursales</a>
            </div>
        </div>

        @if($subcategories->count())
            <div class="row">
                @foreach($subcategories as $sub)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 {{ !$sub->is_active ? 'border-secondary' : '' }}">
                            @if($sub->image)
                                <img src="{{ route('file', ['filename' => $sub->image]) }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="{{ $sub->name }}">
                            @else
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                    <i class="fa fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">
                                    {{ $sub->name }}
                                    @if(!$sub->is_active) <span class="badge badge-secondary">Inactiva</span> @endif
                                </h5>
                                @if($sub->description)
                                    <p class="card-text text-muted small">{{ Str::limit($sub->description, 100) }}</p>
                                @endif
                                @php
                                    $pubCount = $sub->properties()->count();
                                @endphp
                                <div class="d-flex flex-wrap mt-2">
                                    @if($pubCount > 0)
                                        <span class="badge badge-info mr-1 mb-1"><i class="fa fa-th-list mr-1"></i>{{ $pubCount }} Publicaciones</span>
                                    @else
                                        <span class="badge badge-light text-muted mb-1">Sin publicaciones</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="{{ route('admin.subcategories.inmuebles', [$category, $sub]) }}" class="btn btn-sm btn-outline-info btn-block mb-2">
                                    <i class="fa fa-list mr-1"></i> Ver publicaciones ({{ $pubCount }})
                                </a>
                                <div class="btn-group btn-group-sm w-100">
                                    <a href="{{ route('admin.subcategories.edit', [$category, $sub]) }}" class="btn btn-outline-primary" title="Editar"><i class="fa fa-edit"></i></a>
                                    <form action="{{ route('admin.subcategories.toggle-status', [$category, $sub]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-outline-{{ $sub->is_active ? 'warning' : 'success' }}" title="{{ $sub->is_active ? 'Desactivar' : 'Activar' }}">
                                            <i class="fa fa-{{ $sub->is_active ? 'eye-slash' : 'eye' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.subcategories.destroy', [$category, $sub]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger" title="Eliminar"><i class="fa fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {{ $subcategories->links() }}
        @else
            <div class="text-center py-5">
                <i class="fa fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay categorías en esta sucursal</h5>
                <p class="text-muted">Cree categorías para organizar sus publicaciones (Ej: SUV, Sedán, Apartamentos)</p>
                <a href="{{ route('admin.subcategories.create', $category) }}" class="btn btn-success"><i class="fa fa-plus"></i> Crear Categoría</a>
            </div>
        @endif
    </div>
@endsection
