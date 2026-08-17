@extends('admin.main')
@section('title', 'WhatsApp — Facturación de ' . $company->name)
@section('content')
@include('admin.crm._ui')

<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2>
                <a href="{{ route('admin.whatsapp.billing.index', ['period' => $period]) }}" style="color:#94a3b8; text-decoration:none;"><i class="fa fa-arrow-left"></i></a>
                {{ $company->name }} — consumo
            </h2>
        </div>
        <form method="GET" action="{{ route('admin.whatsapp.billing.show', $company) }}">
            <select name="period" class="crm-select" style="width:auto;" onchange="this.form.submit()">
                @foreach($periods as $key => $label)
                    <option value="{{ $key }}" {{ $key === $period ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="crm-stat-row">
        <div class="crm-stat-card bd-blue">
            <div class="crm-stat-icon ic-blue"><i class="fa fa-comments"></i></div>
            <div><div class="crm-stat-number">{{ number_format($billing['used']) }}<span style="font-size:15px; color:#94a3b8;"> / {{ number_format($billing['included']) }}</span></div><div class="crm-stat-label">Conversaciones</div></div>
        </div>
        <div class="crm-stat-card bd-amber">
            <div class="crm-stat-icon ic-amber"><i class="fa fa-plus-circle"></i></div>
            <div><div class="crm-stat-number">{{ number_format($billing['extras']) }}</div><div class="crm-stat-label">Extras (${{ number_format($billing['extrasCost'], 2) }})</div></div>
        </div>
        <div class="crm-stat-card bd-violet">
            <div class="crm-stat-icon ic-violet"><i class="fa fa-dollar"></i></div>
            <div><div class="crm-stat-number">${{ number_format($billing['total'], 2) }}</div><div class="crm-stat-label">Facturado</div></div>
        </div>
        <div class="crm-stat-card bd-green">
            <div class="crm-stat-icon ic-green"><i class="fa fa-line-chart"></i></div>
            <div><div class="crm-stat-number">${{ number_format($billing['profit'], 2) }}</div><div class="crm-stat-label">Margen (costo ${{ number_format($billing['realCost'], 2) }})</div></div>
        </div>
    </div>

    @if($billing['exceeded'])
        <div class="crm-alert warning">
            <i class="fa fa-exclamation-triangle"></i>
            El bot está <strong>pausado</strong> este periodo por {{ $billing['capReached'] ? 'haber alcanzado el tope de gasto' : 'agotar el cupo incluido' }}.
        </div>
    @endif

    <div class="crm-section">
        <div class="crm-section-header"><h5><i class="fa fa-list"></i> Conversaciones del periodo</h5></div>
        <div class="crm-section-body">
            <div class="crm-table-wrap">
                <table class="crm-table">
                    <thead><tr>
                        <th>Teléfono</th><th>Inicio (ventana 24&nbsp;h)</th><th class="num">Mensajes IA</th><th class="num">Tokens (in/out)</th><th class="num">Costo IA</th>
                    </tr></thead>
                    <tbody>
                        @forelse($conversations as $c)
                            <tr>
                                <td style="font-weight:600;">{{ $c->phone }}</td>
                                <td class="muted">{{ optional($c->window_started_at)->format('d/m/Y H:i') }}</td>
                                <td class="num">{{ number_format($c->messages_count) }}</td>
                                <td class="num">{{ number_format($c->tokens_in) }} / {{ number_format($c->tokens_out) }}</td>
                                <td class="num">${{ number_format($c->anthropic_cost, 4) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state"><i class="fa fa-inbox"></i>Sin conversaciones en este periodo.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>{{ $conversations->links() }}</div>
</div>
@endsection
