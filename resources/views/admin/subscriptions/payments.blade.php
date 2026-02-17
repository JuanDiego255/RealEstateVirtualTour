@extends('admin.main')
@section('title', 'Historial de Pagos')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-history"></i> Historial de Pagos</h4>
            <div>
                <a href="{{ route('admin.subscriptions.create-payment', $subscription) }}" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Nuevo Pago
                </a>
                <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body py-2">
                <strong>Suscripción:</strong> {{ $subscription->company->name ?? 'N/A' }} —
                {{ $subscription->package->name ?? 'N/A' }}
                <span class="badge badge-{{ $subscription->status_color }} ml-2">{{ $subscription->status_name }}</span>
            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Referencia</th>
                            <th>Estado</th>
                            <th>Comprobante</th>
                            <th>Fecha</th>
                            <th>Revisado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->id }}</td>
                                <td>
                                    @php
                                        $symbol = $payment->currency === 'USD' ? '$' : '₡';
                                    @endphp
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
                                    @switch($payment->status)
                                        @case('pending')
                                            <span class="badge badge-warning">Pendiente</span> @break
                                        @case('approved')
                                            <span class="badge badge-success">Aprobado</span> @break
                                        @case('rejected')
                                            <span class="badge badge-danger">Rechazado</span> @break
                                    @endswitch
                                </td>
                                <td>
                                    @if($payment->proof_image)
                                        <a href="{{ route('file', $payment->proof_image) }}" target="_blank" class="btn btn-outline-info btn-sm">
                                            <i class="fa fa-image"></i> Ver
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $payment->reviewer->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No hay pagos registrados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
