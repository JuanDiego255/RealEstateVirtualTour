@extends('admin.main')
@section('title', 'Ventas')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show"><strong>{{ Session::get('error') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-money"></i> Ventas</h4>
            <a href="{{ route('admin.sales.report') }}" class="btn btn-info btn-sm"><i class="fa fa-bar-chart"></i> Ver Reporte</a>
        </div>

        {{-- Filtros --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row align-items-end">
                    <div class="form-group col-md-3 mb-2">
                        <label class="small">Estado</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmada</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <label class="small">Desde</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <label class="small">Hasta</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search"></i> Filtrar</button>
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Propiedad</th>
                            <th>Precio Venta</th>
                            <th>Comisión Total</th>
                            <th>Comprador</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td>
                                    <strong>{{ $sale->property->name ?? 'N/A' }}</strong>
                                    @if($sale->property->category)
                                        <br><small class="text-muted">{{ $sale->property->category->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php $sym = ($sale->currency ?? 'CRC') === 'USD' ? '$' : '₡'; @endphp
                                    <strong>{{ $sym }}{{ number_format($sale->sale_price, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    {{ $sym }}{{ number_format($sale->total_commission_amount, 0, ',', '.') }}
                                    <br><small class="text-muted">{{ $sale->commission_percentage }}%</small>
                                </td>
                                <td>{{ $sale->buyer_name ?? '—' }}</td>
                                <td>
                                    @switch($sale->status)
                                        @case('pending') <span class="badge badge-warning">Pendiente</span> @break
                                        @case('confirmed') <span class="badge badge-success">Confirmada</span> @break
                                        @case('cancelled') <span class="badge badge-danger">Cancelada</span> @break
                                    @endswitch
                                </td>
                                <td>{{ $sale->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-outline-info"><i class="fa fa-eye"></i></a>
                                        @if($sale->status === 'pending')
                                            <form action="{{ route('admin.sales.confirm', $sale) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-outline-success" onclick="return confirm('¿Confirmar venta?')" title="Confirmar">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.sales.cancel', $sale) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-outline-danger" onclick="return confirm('¿Cancelar venta?')" title="Cancelar">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No hay ventas registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if(method_exists($sales, 'links'))
                    {{ $sales->withQueryString()->links() }}
                @endif
            </div>
        </div>
    </div>
@endsection
