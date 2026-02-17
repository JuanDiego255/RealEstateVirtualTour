@extends('admin.main')

@section('title', 'Paquetes')

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
            <h4><i class="fa fa-cube"></i> Paquetes de Suscripción</h4>
            <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Nuevo Paquete
            </a>
        </div>

        <div class="row">
            @forelse ($packages as $package)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 {{ $package->is_featured ? 'border-primary' : '' }} {{ !$package->is_active ? 'border-secondary' : '' }}">
                        @if($package->is_featured)
                            <div class="card-header bg-primary text-white text-center py-1">
                                <small><i class="fa fa-star"></i> RECOMENDADO</small>
                            </div>
                        @endif
                        <div class="card-body text-center">
                            <h4 class="card-title">{{ $package->name }}</h4>
                            @if(!$package->is_active)
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                            <div class="my-3">
                                <h2 class="text-primary mb-0">{{ $package->formatted_price }}</h2>
                                <small class="text-muted">/ {{ $package->billing_period_name }}</small>
                            </div>
                            <p class="text-muted small">{{ $package->description }}</p>
                            <hr>
                            <ul class="list-unstyled text-left">
                                <li class="mb-2"><i class="fa fa-users text-primary"></i> <strong>{{ $package->max_users }}</strong> usuarios</li>
                                <li class="mb-2"><i class="fa fa-folder text-primary"></i> <strong>{{ $package->max_categories }}</strong> categorías</li>
                                <li class="mb-2"><i class="fa fa-list text-primary"></i> <strong>{{ $package->max_posts_per_category }}</strong> inmuebles/categoría</li>
                                <li class="mb-2">
                                    <i class="fa fa-{{ $package->allows_tours ? 'check text-success' : 'times text-danger' }}"></i>
                                    Tours virtuales
                                    @if($package->allows_tours && $package->tour_price > 0)
                                        <small class="text-muted">({{ $package->formatted_tour_price }}/tour)</small>
                                    @elseif($package->allows_tours)
                                        <small class="text-success">(Incluido)</small>
                                    @endif
                                </li>
                                <li class="mb-2">
                                    <i class="fa fa-{{ $package->allows_commission ? 'check text-success' : 'times text-danger' }}"></i>
                                    Bolsa inmobiliaria
                                </li>
                                @if($package->trial_days > 0)
                                <li class="mb-2"><i class="fa fa-clock-o text-info"></i> <strong>{{ $package->trial_days }}</strong> días de prueba</li>
                                @endif
                            </ul>
                            <hr>
                            <small class="text-muted">{{ $package->subscriptions_count }} suscripciones activas</small>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group btn-group-sm w-100">
                                <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-outline-primary">
                                    <i class="fa fa-pencil"></i> Editar
                                </a>
                                <form action="{{ route('admin.packages.toggle-featured', $package) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-{{ $package->is_featured ? 'warning' : 'info' }}" title="Destacar">
                                        <i class="fa fa-star{{ $package->is_featured ? '' : '-o' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.packages.toggle-status', $package) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-{{ $package->is_active ? 'warning' : 'success' }}">
                                        <i class="fa fa-{{ $package->is_active ? 'eye-slash' : 'eye' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fa fa-cube fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay paquetes creados</h5>
                    <a href="{{ route('admin.packages.create') }}" class="btn btn-primary mt-2">Crear primer paquete</a>
                </div>
            @endforelse
        </div>
    </div>
@endsection
