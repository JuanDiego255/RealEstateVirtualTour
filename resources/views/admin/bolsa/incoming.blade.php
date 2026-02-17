@extends('admin.main')
@section('title', 'Solicitudes Recibidas - Bolsa')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show"><strong>{{ Session::get('error') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-inbox"></i> Solicitudes Recibidas</h4>
            <a href="{{ route('admin.bolsa.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Dashboard</a>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Propiedad</th>
                            <th>Solicitante</th>
                            <th>Comisión Propuesta</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr class="{{ $req->status === 'pending' ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ $req->property->name ?? 'N/A' }}</strong>
                                    <br><small class="text-muted">{{ $req->property->formatted_price ?? '' }}</small>
                                </td>
                                <td>
                                    {{ $req->requesterCompany->name ?? 'N/A' }}
                                    <br><small class="text-muted">{{ $req->requester->name ?? '' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $req->proposed_commission }}%</strong>
                                    @if($req->final_commission)
                                        <br><small class="text-success">Acordado: {{ $req->final_commission }}%</small>
                                    @endif
                                </td>
                                <td>
                                    @switch($req->status)
                                        @case('pending') <span class="badge badge-warning">Pendiente</span> @break
                                        @case('negotiating') <span class="badge badge-info">En Negociación</span> @break
                                        @case('accepted') <span class="badge badge-success">Aceptada</span> @break
                                        @case('rejected') <span class="badge badge-danger">Rechazada</span> @break
                                        @case('completed') <span class="badge badge-primary">Completada</span> @break
                                        @default <span class="badge badge-secondary">{{ ucfirst($req->status) }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $req->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.bolsa.negotiation', $req) }}" class="btn btn-outline-info" title="Ver/Negociar">
                                            <i class="fa fa-comments"></i>
                                        </a>
                                        @if($req->status === 'pending')
                                            <form action="{{ route('admin.bolsa.accept', $req) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-outline-success" title="Aceptar"><i class="fa fa-check"></i></button>
                                            </form>
                                            <form action="{{ route('admin.bolsa.reject', $req) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-outline-danger" onclick="return confirm('¿Rechazar solicitud?')" title="Rechazar">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No has recibido solicitudes de comisión</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
