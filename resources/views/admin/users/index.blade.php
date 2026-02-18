@extends('admin.main')
@section('title', 'Usuarios')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show"><strong>{{ Session::get('error') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-users"></i> Usuarios</h4>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Nuevo Usuario
            </a>
        </div>

        @if(Auth::user()->isSuperAdmin() && isset($companies) && $companies->count())
            <div class="card mb-3">
                <div class="card-body py-2">
                    <form method="GET" class="row align-items-end">
                        <div class="form-group col-md-4 mb-2">
                            <label class="small">Empresa</label>
                            <select name="company_id" class="form-control form-control-sm">
                                <option value="">Todas las empresas</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3 mb-2">
                            <label class="small">Rol</label>
                            <select name="role" class="form-control form-control-sm">
                                <option value="">Todos los roles</option>
                                <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="company_admin" {{ request('role') == 'company_admin' ? 'selected' : '' }}>Admin Empresa</option>
                                <option value="agent" {{ request('role') == 'agent' ? 'selected' : '' }}>Agente</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3 mb-2">
                            <label class="small">Estado</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2 mb-2">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            @if(Auth::user()->isSuperAdmin())
                                <th>Empresa</th>
                            @endif
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr class="{{ $u->status !== 'active' ? 'table-secondary' : '' }}">
                                <td>
                                    <img src="{{ $u->avatar_url }}" class="rounded-circle mr-2" style="width: 32px; height: 32px;">
                                    <strong>{{ $u->name }}</strong>
                                    <br><small class="text-muted">@{{ $u->username }}</small>
                                </td>
                                <td>{{ $u->email }}</td>
                                @if(Auth::user()->isSuperAdmin())
                                    <td>{{ $u->company->name ?? '—' }}</td>
                                @endif
                                <td>
                                    @switch($u->role)
                                        @case('super_admin')
                                            <span class="badge badge-danger">Super Admin</span> @break
                                        @case('company_admin')
                                            <span class="badge badge-primary">Admin Empresa</span> @break
                                        @case('agent')
                                            <span class="badge badge-info">Agente</span> @break
                                        @default
                                            <span class="badge badge-secondary">{{ $u->role }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @switch($u->status)
                                        @case('active')
                                            <span class="badge badge-success">Activo</span> @break
                                        @case('inactive')
                                            <span class="badge badge-secondary">Inactivo</span> @break
                                        @case('suspended')
                                            <span class="badge badge-warning">Suspendido</span> @break
                                    @endswitch
                                </td>
                                <td>{{ $u->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-outline-primary" title="Editar">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if($u->id !== Auth::id())
                                            <form action="{{ route('admin.users.toggle-status', $u) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-outline-{{ $u->status === 'active' ? 'warning' : 'success' }}"
                                                        title="{{ $u->status === 'active' ? 'Desactivar' : 'Activar' }}">
                                                    <i class="fa fa-{{ $u->status === 'active' ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ Auth::user()->isSuperAdmin() ? 7 : 6 }}" class="text-center text-muted py-4">No hay usuarios</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $users->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
