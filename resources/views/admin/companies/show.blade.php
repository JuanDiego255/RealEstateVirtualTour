@extends('admin.main')
@section('title', $company->name)
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>
                @if($company->logo)<img src="{{ route('file', $company->logo) }}" class="mr-2 rounded" style="height: 30px;">@endif
                {{ $company->name }}
            </h4>
            <div>
                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Editar</a>
                <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                {{-- Info general --}}
                <div class="card mb-3">
                    <div class="card-header"><strong>Información General</strong></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre comercial:</strong> {{ $company->name }}</p>
                                <p><strong>Razón social:</strong> {{ $company->legal_name ?? '—' }}</p>
                                <p><strong>Cédula/NIT:</strong> {{ $company->tax_id ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> {{ $company->email ?? '—' }}</p>
                                <p><strong>Teléfono:</strong> {{ $company->phone ?? '—' }}</p>
                                <p><strong>Estado:</strong>
                                    @switch($company->status)
                                        @case('active') <span class="badge badge-success">Activa</span> @break
                                        @case('inactive') <span class="badge badge-secondary">Inactiva</span> @break
                                        @case('suspended') <span class="badge badge-warning">Suspendida</span> @break
                                    @endswitch
                                </p>
                            </div>
                        </div>
                        @if($company->address)
                            <hr>
                            <p><strong>Dirección:</strong> {{ $company->address }}</p>
                        @endif
                    </div>
                </div>

                {{-- Usuarios --}}
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <strong>Usuarios ({{ $company->users->count() }})</strong>
                        <a href="{{ route('admin.users.create') }}?company_id={{ $company->id }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-plus"></i> Agregar
                        </a>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Usuario</th><th>Email</th><th>Rol</th><th>Estado</th></tr></thead>
                            <tbody>
                                @forelse($company->users as $u)
                                    <tr>
                                        <td>{{ $u->name }}</td>
                                        <td>{{ $u->email }}</td>
                                        <td>
                                            @switch($u->role)
                                                @case('company_admin') <span class="badge badge-primary">Admin</span> @break
                                                @case('agent') <span class="badge badge-info">Agente</span> @break
                                                @default <span class="badge badge-secondary">{{ $u->role }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $u->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($u->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted text-center">Sin usuarios</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Categorías --}}
                <div class="card">
                    <div class="card-header"><strong>Categorías ({{ $company->categories->count() }})</strong></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Nombre</th><th>Sector</th><th>Estado</th></tr></thead>
                            <tbody>
                                @forelse($company->categories as $cat)
                                    <tr>
                                        <td>{{ $cat->name }}</td>
                                        <td>{{ $cat->sector->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $cat->is_active ? 'success' : 'secondary' }}">{{ $cat->is_active ? 'Activa' : 'Inactiva' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-muted text-center">Sin categorías</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- Propietario --}}
                <div class="card mb-3">
                    <div class="card-header"><strong>Propietario</strong></div>
                    <div class="card-body">
                        @if($company->owner)
                            <p><strong>{{ $company->owner->name }}</strong></p>
                            <p class="text-muted small">{{ $company->owner->email }}</p>
                        @else
                            <p class="text-muted">Sin propietario asignado</p>
                        @endif
                    </div>
                </div>

                {{-- Suscripción activa --}}
                <div class="card mb-3">
                    <div class="card-header"><strong>Suscripción</strong></div>
                    <div class="card-body">
                        @php $sub = $company->subscriptions->where('status', 'active')->first() ?? $company->subscriptions->where('status', 'trial')->first(); @endphp
                        @if($sub)
                            <p><strong>Paquete:</strong> {{ $sub->package->name ?? 'N/A' }}</p>
                            <p><strong>Estado:</strong>
                                <span class="badge badge-{{ $sub->status === 'active' ? 'success' : 'info' }}">{{ ucfirst($sub->status) }}</span>
                            </p>
                            <p><strong>Vence:</strong> {{ $sub->ends_at->format('d/m/Y') }}</p>
                        @else
                            <p class="text-muted">Sin suscripción activa</p>
                            <a href="{{ route('admin.subscriptions.create') }}?company_id={{ $company->id }}" class="btn btn-sm btn-outline-success">
                                <i class="fa fa-plus"></i> Crear suscripción
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Stats --}}
                <div class="card">
                    <div class="card-header"><strong>Estadísticas</strong></div>
                    <div class="card-body">
                        <p><i class="fa fa-users"></i> {{ $company->users->count() }} usuarios</p>
                        <p><i class="fa fa-folder"></i> {{ $company->categories->count() }} categorías</p>
                        <p><i class="fa fa-calendar"></i> Creada: {{ $company->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
