@extends('admin.main')
@section('title', 'Suscripciones por Vencer')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-exclamation-triangle text-warning"></i> Suscripciones por Vencer</h4>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Paquete</th>
                            <th>Vence</th>
                            <th>Días restantes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subscriptions as $sub)
                            <tr>
                                <td><strong>{{ $sub->company->name ?? 'N/A' }}</strong></td>
                                <td>{{ $sub->package->name ?? 'N/A' }}</td>
                                <td>{{ $sub->ends_at->format('d/m/Y') }}</td>
                                <td>
                                    @php $days = $sub->days_remaining; @endphp
                                    <span class="badge badge-{{ $days <= 3 ? 'danger' : ($days <= 7 ? 'warning' : 'info') }}">
                                        {{ $days }} días
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.subscriptions.show', $sub) }}" class="btn btn-outline-info"><i class="fa fa-eye"></i></a>
                                        <form action="{{ route('admin.subscriptions.extend', $sub) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-outline-success" onclick="return confirm('¿Extender por un período adicional?')" title="Extender">
                                                <i class="fa fa-calendar-plus-o"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No hay suscripciones próximas a vencer</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
