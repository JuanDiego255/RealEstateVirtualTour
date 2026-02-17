@extends('admin.main')
@section('title', 'Suscripciones Pendientes')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show"><strong>{{ Session::get('error') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-clock-o"></i> Suscripciones Pendientes</h4>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        @if($subscriptions->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>No hay suscripciones pendientes de aprobación</h5>
                </div>
            </div>
        @else
            <div class="row">
                @foreach($subscriptions as $sub)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark d-flex justify-content-between">
                                <strong>{{ $sub->company->name ?? 'Sin empresa' }}</strong>
                                <span class="badge badge-dark">{{ $sub->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="card-body">
                                <p><strong>Paquete:</strong> {{ $sub->package->name ?? 'N/A' }}</p>
                                <p><strong>Creada por:</strong> {{ $sub->createdBy->name ?? 'Sistema' }}</p>
                                <p><strong>Método pago:</strong> {{ $sub->payment_method_name }}</p>
                                <p><strong>Período:</strong> {{ $sub->starts_at->format('d/m/Y') }} - {{ $sub->ends_at->format('d/m/Y') }}</p>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <a href="{{ route('admin.subscriptions.show', $sub) }}" class="btn btn-info btn-sm">
                                    <i class="fa fa-eye"></i> Ver detalle
                                </a>
                                <form action="{{ route('admin.subscriptions.approve', $sub) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-success btn-sm" onclick="return confirm('¿Aprobar esta suscripción?')">
                                        <i class="fa fa-check"></i> Aprobar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
