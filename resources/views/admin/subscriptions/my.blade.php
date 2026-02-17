@extends('admin.main')
@section('title', 'Mi Suscripción')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show"><strong>{{ Session::get('error') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <h4 class="mb-4"><i class="fa fa-credit-card"></i> Mi Suscripción</h4>

        @if(!$subscription)
            {{-- Sin suscripción activa --}}
            <div class="card border-info">
                <div class="card-body text-center py-5">
                    <i class="fa fa-info-circle fa-3x text-info mb-3"></i>
                    <h5>No tienes una suscripción activa</h5>
                    <p class="text-muted">Selecciona un paquete para comenzar a usar la plataforma</p>
                </div>
            </div>

            @if(isset($packages) && $packages->count())
                <h5 class="mt-4 mb-3">Paquetes disponibles</h5>
                <div class="row">
                    @foreach($packages as $pkg)
                        <div class="col-md-4 mb-4">
                            <div class="card {{ $pkg->is_featured ? 'border-primary' : '' }}">
                                @if($pkg->is_featured)
                                    <div class="card-header bg-primary text-white text-center">
                                        <i class="fa fa-star"></i> Recomendado
                                    </div>
                                @endif
                                <div class="card-body text-center">
                                    <h5>{{ $pkg->name }}</h5>
                                    <h3 class="text-primary my-3">{{ $pkg->formatted_price }}</h3>
                                    <p class="text-muted">/ {{ $pkg->billing_period_name }}</p>
                                    <hr>
                                    <ul class="list-unstyled text-left">
                                        <li><i class="fa fa-check text-success"></i> {{ $pkg->max_users }} usuarios</li>
                                        <li><i class="fa fa-check text-success"></i> {{ $pkg->max_categories }} categorías</li>
                                        <li><i class="fa fa-check text-success"></i> {{ $pkg->max_posts_per_category }} publicaciones/categoría</li>
                                        @if($pkg->allows_virtual_tours)
                                            <li><i class="fa fa-check text-success"></i> Tours virtuales</li>
                                        @endif
                                        @if($pkg->allows_commission_access)
                                            <li><i class="fa fa-check text-success"></i> Bolsa Inmobiliaria</li>
                                        @endif
                                        @if($pkg->trial_days > 0)
                                            <li><i class="fa fa-gift text-info"></i> {{ $pkg->trial_days }} días de prueba gratis</li>
                                        @endif
                                    </ul>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="{{ route('admin.subscriptions.create-payment', ['subscription' => 'new', 'package_id' => $pkg->id]) }}"
                                       class="btn btn-{{ $pkg->is_featured ? 'primary' : 'outline-primary' }} btn-block">
                                        Seleccionar
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            {{-- Suscripción activa --}}
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Detalle de Suscripción</h6>
                            <span class="badge badge-{{ $subscription->status_color }} badge-pill px-3 py-2">
                                {{ $subscription->status_name }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Paquete:</strong> {{ $subscription->package->name ?? 'N/A' }}</p>
                                    <p><strong>Inicio:</strong> {{ $subscription->starts_at->format('d/m/Y') }}</p>
                                    <p><strong>Vence:</strong> {{ $subscription->ends_at->format('d/m/Y') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Días restantes:</strong>
                                        <span class="badge badge-{{ $subscription->days_remaining <= 7 ? 'warning' : 'success' }}">
                                            {{ $subscription->days_remaining }} días
                                        </span>
                                    </p>
                                    <p><strong>Método de pago:</strong> {{ $subscription->payment_method_name }}</p>
                                    <p><strong>Empresa:</strong> {{ $subscription->company->name ?? 'N/A' }}</p>
                                </div>
                            </div>

                            @if($subscription->isExpiringSoon())
                                <div class="alert alert-warning mt-3">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    Tu suscripción vence pronto. Realiza el pago de renovación para no perder acceso.
                                </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.subscriptions.payments', $subscription) }}" class="btn btn-info btn-sm">
                                <i class="fa fa-history"></i> Historial de Pagos
                            </a>
                            <a href="{{ route('admin.subscriptions.create-payment', $subscription) }}" class="btn btn-success btn-sm">
                                <i class="fa fa-money"></i> Realizar Pago
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Tu Plan: <strong>{{ $subscription->package->name ?? '' }}</strong></h6>
                            <hr>
                            <p><i class="fa fa-users"></i> {{ $subscription->package->max_users ?? 0 }} usuarios</p>
                            <p><i class="fa fa-folder-open"></i> {{ $subscription->package->max_categories ?? 0 }} categorías</p>
                            <p><i class="fa fa-list"></i> {{ $subscription->package->max_posts_per_category ?? 0 }} publicaciones/cat.</p>
                            @if($subscription->package->allows_virtual_tours ?? false)
                                <p class="text-success"><i class="fa fa-check"></i> Tours virtuales</p>
                            @endif
                            @if($subscription->package->allows_commission_access ?? false)
                                <p class="text-success"><i class="fa fa-check"></i> Bolsa Inmobiliaria</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
