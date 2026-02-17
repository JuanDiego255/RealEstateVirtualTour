@extends('admin.main')
@section('title', 'Pagos Pendientes de Revisión')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show"><strong>{{ Session::get('error') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-money"></i> Pagos Pendientes de Revisión</h4>
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
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Referencia</th>
                            <th>Comprobante</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td><strong>{{ $payment->subscription->company->name ?? 'N/A' }}</strong></td>
                                <td>{{ $payment->subscription->package->name ?? 'N/A' }}</td>
                                <td>
                                    @php $symbol = $payment->currency === 'USD' ? '$' : '₡'; @endphp
                                    {{ $symbol }}{{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td>
                                    @switch($payment->payment_method)
                                        @case('sinpe') SINPE Móvil @break
                                        @case('transfer') Transferencia @break
                                        @default {{ ucfirst($payment->payment_method) }}
                                    @endswitch
                                </td>
                                <td>{{ $payment->reference_number ?? '—' }}</td>
                                <td>
                                    @if($payment->proof_image)
                                        <a href="{{ route('file', $payment->proof_image) }}" target="_blank" class="btn btn-outline-info btn-sm">
                                            <i class="fa fa-image"></i> Ver
                                        </a>
                                    @else
                                        <span class="text-muted">Sin comprobante</span>
                                    @endif
                                </td>
                                <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('admin.subscriptions.approve-payment', $payment) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-success" onclick="return confirm('¿Aprobar este pago?')" title="Aprobar">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.subscriptions.reject-payment', $payment) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-danger" onclick="return confirm('¿Rechazar este pago?')" title="Rechazar">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No hay pagos pendientes de revisión</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
