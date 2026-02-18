@extends('admin.main')
@section('title', 'Mis Categorías')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button
                type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show"><strong>{{ Session::get('error') }}</strong><button
                type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-folder-open"></i> Mis Categorías</h4>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Nueva Categoría
            </a>
        </div>

        <div class="row">
            @forelse($categories as $cat)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 {{ !$cat->is_active ? 'border-secondary' : '' }}">
                        @if ($cat->cover_image)
                            <img src="{{ route('file', ['filename' => $cat->cover_image]) }}" class="card-img-top"
                                style="height: 150px; object-fit: cover;">
                        @elseif($cat->image)
                            <img src="{{ route('file', ['filename' => $cat->image]) }}" class="card-img-top"
                                style="height: 150px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                <i class="fa fa-image fa-3x text-muted"></i>
                            </div>
                        @endif

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">{{ $cat->name }}</h5>
                                @if (!$cat->is_active)
                                    <span class="badge badge-secondary">Inactiva</span>
                                @endif
                            </div>

                            @if ($cat->sector)
                                <span class="badge badge-info mb-2">
                                    @if ($cat->sector->icon)
                                        <i class="{{ $cat->sector->icon }}"></i>
                                    @endif
                                    {{ $cat->sector->name }}
                                </span>
                            @endif

                            @if ($cat->company)
                                <br><small class="text-muted">{{ $cat->company->name }}</small>
                            @endif

                            <p class="card-text text-muted small mt-2">
                                {{ Str::limit($cat->description, 100) }}
                            </p>

                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="fa fa-list"></i> {{ $cat->properties_count }} propiedades</span>
                                <span><i class="fa fa-eye"></i> {{ $cat->views_count ?? 0 }} vistas</span>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.categories.show', $cat) }}" class="btn btn-outline-info"
                                    title="Ver"><i class="fa fa-eye"></i></a>
                                <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-outline-primary"
                                    title="Editar"><i class="fa fa-edit"></i></a>
                                <form action="{{ route('admin.categories.toggle-status', $cat) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button class="btn btn-outline-{{ $cat->is_active ? 'warning' : 'success' }}"
                                        title="{{ $cat->is_active ? 'Desactivar' : 'Activar' }}">
                                        <i class="fa fa-{{ $cat->is_active ? 'eye-slash' : 'eye' }}"></i>
                                    </button>
                                </form>
                            </div>
                            @if ($cat->share_token)
                                <button class="btn btn-sm btn-outline-success"
                                    onclick="copyShareLink('{{ $cat->share_url }}')" title="Copiar enlace">
                                    <i class="fa fa-share-alt"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fa fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5>No tienes categorías aún</h5>
                            <p class="text-muted">Crea tu primera categoría para empezar a publicar</p>
                            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Crear Categoría
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        {{ $categories->links() }}
    </div>
@endsection

@push('script')
    <script>
        function copyShareLink(url) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function() {
                    alert('Enlace copiado al portapapeles');
                });
            } else {
                var temp = document.createElement('input');
                temp.value = url;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                alert('Enlace copiado al portapapeles');
            }
        }
    </script>
@endpush
