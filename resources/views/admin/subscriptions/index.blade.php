@extends('admin.main')
@section('title', 'Suscripciones')
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
            <h4><i class="fa fa-id-card"></i> Suscripciones</h4>
            <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Nueva Suscripción
            </a>
        </div>

        {{-- Filtros --}}
        <div class="card mb-4">
            <div class="card-body py-2">
                <form method="GET" class="row align-items-end">
                    <div class="form-group col-md-3 mb-2">
                        <label class="small">Estado</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="trial" {{ request('status') == 'trial' ? 'selected' : '' }}>Prueba</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activa</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirada</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <label class="small">Empresa</label>
                        <select name="company_id" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <label class="small">Paquete</label>
                        <select name="package_id" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($packages as $p)
                                <option value="{{ $p->id }}" {{ request('package_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search"></i> Filtrar</button>
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Paquete</th>
                            <th>Estado</th>
                            <th>Inicio</th>
                            <th>Vence</th>
                            <th>Método pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subscriptions as $sub)
                            <tr>
                                <td>
                                    <strong>{{ $sub->company->name ?? 'N/A' }}</strong>
                                    <br><small class="text-muted">Creada por: {{ $sub->createdBy->name ?? 'Sistema' }}</small>
                                </td>
                                <td>{{ $sub->package->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-{{ $sub->status_color }}">{{ $sub->status_name }}</span>
                                    @if($sub->isExpiringSoon())
                                        <br><small class="text-warning"><i class="fa fa-exclamation-triangle"></i> Vence pronto</small>
                                    @endif
                                </td>
                                <td>{{ $sub->starts_at->format('d/m/Y') }}</td>
                                <td>
                                    {{ $sub->ends_at->format('d/m/Y') }}
                                    <br><small class="text-muted">{{ $sub->days_remaining }} días</small>
                                </td>
                                <td>{{ $sub->payment_method_name }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.subscriptions.show', $sub) }}" class="btn btn-outline-info" title="Ver">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @if($sub->status === 'pending')
                                            <form action="{{ route('admin.subscriptions.approve', $sub) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-outline-success" title="Aprobar"><i class="fa fa-check"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No hay suscripciones</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $subscriptions->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
