@extends('admin.main')
@section('title', 'WhatsApp — Facturación de ' . $company->name)
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h4>
            <a href="{{ route('admin.whatsapp.billing.index', ['period' => $period]) }}" class="text-muted mr-2"><i class="fa fa-arrow-left"></i></a>
            {{ $company->name }} — consumo
        </h4>
        <form method="GET" action="{{ route('admin.whatsapp.billing.show', $company) }}" class="form-inline">
            <label class="mr-2 mb-0">Periodo</label>
            <select name="period" class="form-control form-control-sm" onchange="this.form.submit()">
                @foreach($periods as $key => $label)
                    <option value="{{ $key }}" {{ $key === $period ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card text-center"><div class="card-body">
            <div class="text-muted small">Conversaciones</div>
            <h3 class="mb-0">{{ number_format($billing['used']) }} <small class="text-muted">/ {{ number_format($billing['included']) }}</small></h3>
        </div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body">
            <div class="text-muted small">Extras</div>
            <h3 class="mb-0">{{ number_format($billing['extras']) }} <small class="text-muted">(${{ number_format($billing['extrasCost'], 2) }})</small></h3>
        </div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body">
            <div class="text-muted small">Facturado</div>
            <h3 class="mb-0 text-primary">${{ number_format($billing['total'], 2) }}</h3>
        </div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body">
            <div class="text-muted small">Margen (costo ${{ number_format($billing['realCost'], 2) }})</div>
            <h3 class="mb-0 {{ $billing['profit'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($billing['profit'], 2) }}</h3>
        </div></div></div>
    </div>

    @if($billing['exceeded'])
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i>
            El bot está <strong>pausado</strong> este periodo por {{ $billing['capReached'] ? 'haber alcanzado el tope de gasto' : 'agotar el cupo incluido' }}.
        </div>
    @endif

    <div class="card">
        <div class="card-header"><strong>Conversaciones del periodo</strong></div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Teléfono</th>
                        <th>Inicio (ventana 24 h)</th>
                        <th class="text-right">Mensajes IA</th>
                        <th class="text-right">Tokens (in/out)</th>
                        <th class="text-right">Costo IA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $c)
                        <tr>
                            <td>{{ $c->phone }}</td>
                            <td>{{ optional($c->window_started_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-right">{{ number_format($c->messages_count) }}</td>
                            <td class="text-right">{{ number_format($c->tokens_in) }} / {{ number_format($c->tokens_out) }}</td>
                            <td class="text-right">${{ number_format($c->anthropic_cost, 4) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Sin conversaciones en este periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $conversations->links() }}</div>
</div>
@endsection
