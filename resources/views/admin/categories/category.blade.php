@extends('admin.main')

@section('title', 'Categorías')

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
    @include('admin.categories.add')

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-dark"><i class="fa fa-folder-open mr-2"></i>Gestión de Categorías</h4>
            <button type="button" class="btn btn-rounded btn-outline-info" data-toggle="modal" data-target="#addCategory">
                <i class="fa fa-plus mr-1"></i> Nueva Categoría
            </button>
        </div>

        {{-- Filtro por sector --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <form method="GET" action="{{ route('categories') }}">
                    <div class="input-group">
                        <select name="sector_id" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Todos los Sectores --</option>
                            @foreach ($sectors as $sector)
                                <option value="{{ $sector->id }}"
                                    {{ request('sector_id') == $sector->id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mt-3">
            @if (count($categories) > 0)
                @foreach ($categories as $category)
                    @include('admin.categories.edit')
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="position-relative">
                                @php
                                    $imagePath = null;

                                    if (!empty($category->image)) {
                                        $imagePath = $category->image;
                                    } elseif (!empty($category->cover_image)) {
                                        // Por si viene como URL completa
                                        $imagePath = str_replace(url('/storage') . '/', '', $category->cover_image);
                                    }
                                @endphp

                                <img class="card-img-top" style="height: 180px; object-fit: cover;"
                                    src="{{ $imagePath ? route('file', ['filename' => $imagePath]) : url('images/producto-sin-imagen.PNG') }}">

                                {{ $category->sector->name ?? 'Sin sector' }}
                                </span>
                                <span
                                    class="badge badge-{{ $category->status ? 'success' : 'secondary' }} position-absolute"
                                    style="top: 10px; right: 10px;">
                                    {{ $category->status ? 'Activa' : 'Inactiva' }}
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $category->name }}</h5>
                                @if ($category->location)
                                    <p class="card-text small mb-1">
                                        <i class="fa fa-map-marker-alt text-danger mr-1"></i>{{ $category->location }}
                                    </p>
                                @endif
                                @if ($category->description)
                                    <p class="card-text text-muted small">
                                        {{ Str::limit($category->description, 80) }}
                                    </p>
                                @endif
                                @php
                                    $propCount = 0;
                                    $vehCount = 0;
                                    foreach ($category->subcategories as $sub) {
                                        $propCount += $sub->properties()->count();
                                        $vehCount += $sub->vehicles()->count();
                                    }
                                @endphp
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-secondary mr-2">
                                        <i class="fa fa-tags mr-1"></i>{{ $category->subcategories->count() }} Subcategorías
                                    </span>
                                    <span class="badge badge-info mr-2">
                                        <i class="fa fa-building mr-1"></i>{{ $propCount }}
                                        Propiedades
                                    </span>
                                    <span class="badge badge-warning">
                                        <i class="fa fa-car mr-1"></i>{{ $vehCount }} Vehículos
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between">
                                <button class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                    data-target="#editCategory{{ $category->id }}">
                                    <i class="fa fa-edit mr-1"></i>Editar
                                </button>
                                <a onclick="if (confirm('¿Deseas borrar esta categoría?')) {
                                    document.getElementById('deleteCategory{{ $category->id }}').submit();
                                }"
                                    href="#" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash mr-1"></i>Eliminar
                                </a>
                                <form id="deleteCategory{{ $category->id }}" method="post"
                                    action="{{ url('/delete/category/' . $category->id) }}">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12 text-center mt-5">
                    <i class="fa fa-folder-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay categorías registradas</h5>
                    <p class="text-muted">Crea un nuevo sector primero, luego agrega categorías.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
