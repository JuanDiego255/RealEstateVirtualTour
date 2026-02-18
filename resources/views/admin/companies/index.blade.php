@extends('admin.main')
@section('title', 'Empresas')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show"><strong>{{ Session::get('error') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-building"></i> Empresas</h4>
            <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Nueva Empresa
            </a>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Contacto</th>
                            <th>Usuarios</th>
                            <th>Categorías</th>
                            <th>Estado</th>
                            <th>Creada</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $c)
                            <tr>
                                <td>
                                    @if($c->logo)
                                        <img src="{{ $c->logo_url }}" class="mr-2 rounded" style="width: 32px; height: 32px; object-fit: cover;">
                                    @endif
                                    <strong>{{ $c->name }}</strong>
                                    @if($c->legal_name && $c->legal_name !== $c->name)
                                        <br><small class="text-muted">{{ $c->legal_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($c->email)<small>{{ $c->email }}</small><br>@endif
                                    @if($c->phone)<small class="text-muted">{{ $c->phone }}</small>@endif
                                </td>
                                <td><span class="badge badge-info">{{ $c->users_count }}</span></td>
                                <td><span class="badge badge-secondary">{{ $c->categories_count }}</span></td>
                                <td>
                                    @switch($c->status)
                                        @case('active') <span class="badge badge-success">Activa</span> @break
                                        @case('inactive') <span class="badge badge-secondary">Inactiva</span> @break
                                        @case('suspended') <span class="badge badge-warning">Suspendida</span> @break
                                    @endswitch
                                </td>
                                <td>{{ $c->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.companies.show', $c) }}" class="btn btn-outline-info" title="Ver"><i class="fa fa-eye"></i></a>
                                        <a href="{{ route('admin.companies.edit', $c) }}" class="btn btn-outline-primary" title="Editar"><i class="fa fa-edit"></i></a>
                                        <form action="{{ route('admin.companies.toggle-status', $c) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-outline-{{ $c->status === 'active' ? 'warning' : 'success' }}" title="{{ $c->status === 'active' ? 'Desactivar' : 'Activar' }}">
                                                <i class="fa fa-{{ $c->status === 'active' ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No hay empresas registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $companies->links() }}
            </div>
        </div>
    </div>
@endsection
