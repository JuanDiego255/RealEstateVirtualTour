@extends('admin.main')
@section('title', 'Reporte de Ventas')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-bar-chart"></i> Reporte de Ventas</h4>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>

        {{-- Filtro de período --}}
        <div class="card mb-4">
            <div class="card-body py-2">
                <form method="GET" class="row align-items-end">
                    <div class="form-group col-md-3 mb-2">
                        <label class="small">Desde</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <label class="small">Hasta</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search"></i> Generar Reporte</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Resumen --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h2>{{ $stats['total_sales'] ?? 0 }}</h2>
                        <p class="mb-0">Ventas Totales</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h2>{{ $stats['confirmed_sales'] ?? 0 }}</h2>
                        <p class="mb-0">Confirmadas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        @php $sym = '₡'; @endphp
                        <h4>{{ $sym }}{{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</h4>
                        <p class="mb-0">Volumen Total</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h4>{{ $sym }}{{ number_format($stats['total_commissions'] ?? 0, 0, ',', '.') }}</h4>
                        <p class="mb-0">Comisiones Totales</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla detallada --}}
        <div class="card">
            <div class="card-header"><strong>Detalle de Ventas</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Propiedad</th>
                            <th>Precio Venta</th>
                            <th>Comisión %</th>
                            <th>Comisión Monto</th>
                            <th>Tu Comisión</th>
                            <th>Agente Ext.</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales ?? [] as $sale)
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td>{{ $sale->created_at->format('d/m/Y') }}</td>
                                <td>{{ $sale->property->name ?? 'N/A' }}</td>
                                <td>
                                    @php $s = ($sale->currency ?? 'CRC') === 'USD' ? '$' : '₡'; @endphp
                                    {{ $s }}{{ number_format($sale->sale_price, 0, ',', '.') }}
                                </td>
                                <td>{{ $sale->commission_percentage }}%</td>
                                <td>{{ $s }}{{ number_format($sale->total_commission_amount, 0, ',', '.') }}</td>
                                <td class="text-success">{{ $s }}{{ number_format($sale->owner_commission_amount, 0, ',', '.') }}</td>
                                <td class="text-info">
                                    @if($sale->agent_commission_amount > 0)
                                        {{ $s }}{{ number_format($sale->agent_commission_amount, 0, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @switch($sale->status)
                                        @case('confirmed') <span class="badge badge-success">OK</span> @break
                                        @case('pending') <span class="badge badge-warning">Pend.</span> @break
                                        @case('cancelled') <span class="badge badge-danger">Canc.</span> @break
                                    @endswitch
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No hay ventas en este período</td></tr>
                        @endforelse
                    </tbody>
                    @if(isset($sales) && $sales->count())
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="3">TOTALES</td>
                                <td>{{ $sym }}{{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</td>
                                <td></td>
                                <td>{{ $sym }}{{ number_format($stats['total_commissions'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-success">{{ $sym }}{{ number_format($stats['owner_commissions'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-info">{{ $sym }}{{ number_format($stats['agent_commissions'] ?? 0, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
