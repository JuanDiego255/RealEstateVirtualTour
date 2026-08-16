@extends('admin.main')
@section('title', 'WhatsApp — Facturación')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h4><i class="fa fa-whatsapp" style="color:#25d366"></i> Facturación del bot</h4>
        <form method="GET" action="{{ route('admin.whatsapp.billing.index') }}" class="form-inline">
            <label class="mr-2 mb-0">Periodo</label>
            <select name="period" class="form-control form-control-sm" onchange="this.form.submit()">
                @foreach($periods as $key => $label)
                    <option value="{{ $key }}" {{ $key === $period ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <div class="text-muted small">Conversaciones</div>
                <h3 class="mb-0">{{ number_format($totals['used']) }}</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <div class="text-muted small">Facturado</div>
                <h3 class="mb-0 text-primary">${{ number_format($totals['total'], 2) }}</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <div class="text-muted small">Costo real</div>
                <h3 class="mb-0">${{ number_format($totals['realCost'], 2) }}</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <div class="text-muted small">Margen</div>
                <h3 class="mb-0 {{ $totals['profit'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($totals['profit'], 2) }}</h3>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Plan</th>
                        <th class="text-right">Usadas</th>
                        <th class="text-right">Incluidas</th>
                        <th class="text-right">Extras</th>
                        <th class="text-right">Facturado</th>
                        <th class="text-right">Costo real</th>
                        <th class="text-right">Margen</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr class="{{ $r['exceeded'] ? 'table-warning' : '' }}">
                            <td>{{ $r['company'] }}</td>
                            <td>{{ $r['plan'] ?: '—' }}</td>
                            <td class="text-right">{{ number_format($r['used']) }}</td>
                            <td class="text-right">{{ number_format($r['included']) }}</td>
                            <td class="text-right">
                                {{ number_format($r['extras']) }}
                                @if($r['extras'] > 0)<span class="text-muted small">(${{ number_format($r['extrasCost'], 2) }})</span>@endif
                            </td>
                            <td class="text-right">${{ number_format($r['total'], 2) }}</td>
                            <td class="text-right">${{ number_format($r['realCost'], 2) }}</td>
                            <td class="text-right {{ $r['profit'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($r['profit'], 2) }}</td>
                            <td>
                                @if(!$r['enabled'])
                                    <span class="badge badge-secondary">Apagado</span>
                                @elseif($r['exceeded'])
                                    <span class="badge badge-danger">{{ $r['capReached'] ? 'Tope alcanzado' : 'Cupo agotado' }}</span>
                                @else
                                    <span class="badge badge-success">Activo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.whatsapp.billing.show', ['company' => $r['company_id'], 'period' => $period]) }}" class="btn btn-xs btn-outline-primary">Detalle</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">No hay empresas con bot configurado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-muted small mt-2">La unidad de cobro es la conversación (ventana de 24 h de WhatsApp), no el mensaje.</p>
</div>
@endsection
