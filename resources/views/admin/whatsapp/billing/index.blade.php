@extends('admin.main')
@section('title', 'WhatsApp — Facturación')
@section('content')
@include('admin.crm._ui')

<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-whatsapp" style="color:#25d366;"></i> Facturación del bot</h2>
            <p class="sub">La unidad de cobro es la conversación (ventana de 24&nbsp;h), no el mensaje.</p>
        </div>
        <form method="GET" action="{{ route('admin.whatsapp.billing.index') }}">
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
            <div><div class="crm-stat-number">{{ number_format($totals['used']) }}</div><div class="crm-stat-label">Conversaciones</div></div>
        </div>
        <div class="crm-stat-card bd-violet">
            <div class="crm-stat-icon ic-violet"><i class="fa fa-dollar"></i></div>
            <div><div class="crm-stat-number">${{ number_format($totals['total'], 2) }}</div><div class="crm-stat-label">Facturado</div></div>
        </div>
        <div class="crm-stat-card bd-slate">
            <div class="crm-stat-icon ic-slate"><i class="fa fa-microchip"></i></div>
            <div><div class="crm-stat-number">${{ number_format($totals['realCost'], 2) }}</div><div class="crm-stat-label">Costo real</div></div>
        </div>
        <div class="crm-stat-card bd-green">
            <div class="crm-stat-icon ic-green"><i class="fa fa-line-chart"></i></div>
            <div><div class="crm-stat-number">${{ number_format($totals['profit'], 2) }}</div><div class="crm-stat-label">Margen</div></div>
        </div>
    </div>

    <div class="crm-section">
        <div class="crm-section-body">
            <div class="crm-table-wrap">
                <table class="crm-table">
                    <thead><tr>
                        <th>Empresa</th><th>Plan</th><th class="num">Usadas</th><th class="num">Incluidas</th><th class="num">Extras</th>
                        <th class="num">Facturado</th><th class="num">Costo real</th><th class="num">Margen</th><th>Estado</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse($rows as $r)
                            <tr>
                                <td style="font-weight:600;">{{ $r['company'] }}</td>
                                <td class="muted">{{ $r['plan'] ?: '—' }}</td>
                                <td class="num">{{ number_format($r['used']) }}</td>
                                <td class="num">{{ number_format($r['included']) }}</td>
                                <td class="num">{{ number_format($r['extras']) }}@if($r['extras'] > 0)<span class="muted"> (${{ number_format($r['extrasCost'], 2) }})</span>@endif</td>
                                <td class="num">${{ number_format($r['total'], 2) }}</td>
                                <td class="num">${{ number_format($r['realCost'], 2) }}</td>
                                <td class="num" style="color:{{ $r['profit'] >= 0 ? '#059669' : '#dc2626' }}; font-weight:600;">${{ number_format($r['profit'], 2) }}</td>
                                <td>
                                    @if(!$r['enabled'])<span class="crm-badge slate">Apagado</span>
                                    @elseif($r['exceeded'])<span class="crm-badge red">{{ $r['capReached'] ? 'Tope' : 'Cupo agotado' }}</span>
                                    @else<span class="crm-badge green">Activo</span>@endif
                                </td>
                                <td class="num"><a href="{{ route('admin.whatsapp.billing.show', ['company' => $r['company_id'], 'period' => $period]) }}" class="action-btn secondary xs">Detalle</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="10"><div class="empty-state"><i class="fa fa-whatsapp"></i>No hay empresas con bot configurado.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
