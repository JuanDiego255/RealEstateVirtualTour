@extends('admin.main')
@section('title', 'Bolsa Inmobiliaria')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <h4 class="mb-4"><i class="fa fa-exchange"></i> Bolsa Inmobiliaria — Dashboard</h4>

        {{-- Tarjetas resumen --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-primary bg-light">
                    <div class="card-body text-center">
                        <h3 class="text-primary">{{ $stats['available'] ?? 0 }}</h3>
                        <small class="text-muted">Propiedades Disponibles</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-warning bg-light">
                    <div class="card-body text-center">
                        <h3 class="text-warning">{{ $stats['my_pending'] ?? 0 }}</h3>
                        <small class="text-muted">Mis Solicitudes Pendientes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-info bg-light">
                    <div class="card-body text-center">
                        <h3 class="text-info">{{ $stats['incoming_pending'] ?? 0 }}</h3>
                        <small class="text-muted">Solicitudes Recibidas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-success bg-light">
                    <div class="card-body text-center">
                        <h3 class="text-success">{{ $stats['completed'] ?? 0 }}</h3>
                        <small class="text-muted">Ventas Completadas</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Accesos rápidos --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><strong><i class="fa fa-search"></i> Explorar Propiedades</strong></div>
                    <div class="card-body">
                        <p class="text-muted">Busca propiedades disponibles para comisión de otras empresas.</p>
                        <a href="{{ route('admin.bolsa.available') }}" class="btn btn-primary">
                            <i class="fa fa-building"></i> Ver Disponibles
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><strong><i class="fa fa-bell"></i> Solicitudes</strong></div>
                    <div class="card-body">
                        <p class="text-muted">Gestiona solicitudes de comisión enviadas y recibidas.</p>
                        <a href="{{ route('admin.bolsa.my-requests') }}" class="btn btn-primary mr-2">
                            <i class="fa fa-paper-plane"></i> Enviadas
                        </a>
                        <a href="{{ route('admin.bolsa.incoming') }}" class="btn btn-info">
                            <i class="fa fa-inbox"></i> Recibidas
                            @if(($stats['incoming_pending'] ?? 0) > 0)
                                <span class="badge badge-danger">{{ $stats['incoming_pending'] }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
