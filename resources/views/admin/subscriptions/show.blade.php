@extends('admin.main')
@section('title', 'Detalle Suscripción')
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
            <h4><i class="fa fa-id-card"></i> Suscripción #{{ $subscription->id }}</h4>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>

        <div class="row">
            {{-- Info principal --}}
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header"><strong>Información de la suscripción</strong></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Empresa:</strong> {{ $subscription->company->name }}</p>
                                <p><strong>Paquete:</strong> {{ $subscription->package->name }} ({{ $subscription->package->formatted_price }}/{{ $subscription->package->billing_period_name }})</p>
                                <p><strong>Método de pago:</strong> {{ $subscription->payment_method_name }}</p>
                                <p><strong>Creada por:</strong> {{ $subscription->createdBy->name ?? 'Sistema' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Inicio:</strong> {{ $subscription->starts_at->format('d/m/Y') }}</p>
                                <p><strong>Vence:</strong> {{ $subscription->ends_at->format('d/m/Y') }}
                                    <span class="badge badge-{{ $subscription->days_remaining > 7 ? 'success' : ($subscription->days_remaining > 0 ? 'warning' : 'danger') }}">
                                        {{ $subscription->days_remaining }} días restantes
                                    </span>
                                </p>
                                @if($subscription->trial_ends_at)
                                    <p><strong>Prueba hasta:</strong> {{ $subscription->trial_ends_at->format('d/m/Y') }}</p>
                                @endif
                                @if($subscription->approvedBy)
                                    <p><strong>Aprobada por:</strong> {{ $subscription->approvedBy->name }} el {{ $subscription->approved_at->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        </div>
                        @if($subscription->notes)
                            <hr>
                            <p><strong>Notas:</strong> {{ $subscription->notes }}</p>
                        @endif
                    </div>
                </div>

                {{-- Pagos --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <strong>Historial de pagos</strong>
                        <a href="{{ route('admin.subscriptions.create-payment', $subscription) }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus"></i> Registrar pago
                        </a>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Fecha</th><th>Monto</th><th>Método</th><th>Referencia</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody>
                                @forelse ($subscription->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                        <td>{{ $payment->formatted_amount }}</td>
                                        <td>{{ $payment->payment_method_name }}</td>
                                        <td>{{ $payment->payment_reference ?? '-' }}</td>
                                        <td><span class="badge badge-{{ $payment->status_color }}">{{ $payment->status_name }}</span></td>
                                        <td>
                                            @if($payment->proof_url)
                                                <a href="{{ $payment->proof_url }}" target="_blank" class="btn btn-sm btn-outline-info"><i class="fa fa-image"></i></a>
                                            @endif
                                            @if($payment->isPending() && Auth::user()->isSuperAdmin())
                                                <form action="{{ route('admin.subscriptions.approve-payment', $payment) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-success"><i class="fa fa-check"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No hay pagos registrados</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Panel lateral --}}
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header text-center">
                        <span class="badge badge-{{ $subscription->status_color }}" style="font-size: 16px;">
                            {{ $subscription->status_name }}
                        </span>
                    </div>
                    <div class="card-body">
                        @if(Auth::user()->isSuperAdmin())
                            {{-- Acciones admin --}}
                            @if($subscription->status === 'pending')
                                <form action="{{ route('admin.subscriptions.approve', $subscription) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button class="btn btn-success btn-block"><i class="fa fa-check"></i> Aprobar</button>
                                </form>
                            @endif

                            @if(in_array($subscription->status, ['active', 'trial']))
                                <form action="{{ route('admin.subscriptions.extend', $subscription) }}" method="POST" class="mb-2">
                                    @csrf
                                    <div class="input-group">
                                        <input type="number" name="periods" value="1" min="1" max="12" class="form-control">
                                        <div class="input-group-append">
                                            <button class="btn btn-info"><i class="fa fa-calendar-plus-o"></i> Extender</button>
                                        </div>
                                    </div>
                                </form>

                                <form action="{{ route('admin.subscriptions.suspend', $subscription) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button class="btn btn-warning btn-block" onclick="return confirm('¿Suspender esta suscripción?');">
                                        <i class="fa fa-pause"></i> Suspender
                                    </button>
                                </form>
                            @endif

                            @if(!in_array($subscription->status, ['cancelled']))
                                <form action="{{ route('admin.subscriptions.cancel', $subscription) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-danger btn-block" onclick="return confirm('¿Cancelar esta suscripción?');">
                                        <i class="fa fa-times"></i> Cancelar
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Usuarios de la empresa --}}
                <div class="card">
                    <div class="card-header"><strong>Usuarios de la empresa</strong></div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($subscription->company->users as $user)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        <br><small class="text-muted">{{ $user->email ?? $user->username }}</small>
                                    </div>
                                    <span class="badge badge-{{ $user->role === 'company_admin' ? 'primary' : 'secondary' }}">
                                        {{ $user->role === 'company_admin' ? 'Admin' : 'Agente' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
