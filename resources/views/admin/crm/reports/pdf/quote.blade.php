<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: helvetica, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; line-height: 1.4; }
.page { padding: 28px 32px; }
.section { margin-bottom: 18px; }
.section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #888; letter-spacing: 1px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-bottom: 10px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
th { background: #1a1a2e; color: #fff; padding: 7px 10px; font-size: 10px; text-align: left; }
td { padding: 6px 10px; border-bottom: 1px solid #f0f0f0; font-size: 11px; vertical-align: top; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.total-row td { font-weight: bold; font-size: 13px; border-top: 2px solid #1a1a2e; }
.total-row .amount { color: #c2ac1f; font-size: 15px; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
.badge-draft    { background: #f1f5f9; color: #64748b; }
.badge-sent     { background: #dbeafe; color: #1d4ed8; }
.badge-viewed   { background: #e0f2fe; color: #0369a1; }
.badge-accepted { background: #d1fae5; color: #065f46; }
.badge-rejected { background: #fee2e2; color: #991b1b; }
.info-grid { width: 100%; }
.info-grid td { padding: 4px 8px; border: none; vertical-align: top; }
.info-label { color: #888; font-size: 10px; font-weight: bold; text-transform: uppercase; }
.info-value { font-size: 12px; font-weight: 600; }
.footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; color: #aaa; border-top: 1px solid #e5e7eb; padding: 6px 0; text-align: center; }
</style>
</head>
<body>
<div class="page">

@php $reportTitle = 'Cotización'; @endphp
@include('admin.crm.reports.pdf._header')

<div class="section">
    <div class="section-title">Información de la Cotización</div>
    <table class="info-grid">
        <tr>
            <td width="25%"><div class="info-label">Número</div><div class="info-value">{{ $quote->quote_number }}</div></td>
            <td width="25%"><div class="info-label">Estado</div>
                <span class="badge badge-{{ $quote->status }}">{{ $quote->status_label }}</span>
            </td>
            <td width="25%"><div class="info-label">Fecha de emisión</div><div class="info-value">{{ $quote->created_at->format('d/m/Y') }}</div></td>
            <td width="25%"><div class="info-label">Vigencia hasta</div><div class="info-value">{{ $quote->valid_until }}</div></td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Datos del Cliente</div>
    <table class="info-grid">
        <tr>
            <td width="33%"><div class="info-label">Nombre</div><div class="info-value">{{ $quote->lead->name }}</div></td>
            <td width="33%"><div class="info-label">Email</div><div class="info-value">{{ $quote->lead->email ?? '—' }}</div></td>
            <td width="33%"><div class="info-label">Teléfono</div><div class="info-value">{{ $quote->lead->phone ?? '—' }}</div></td>
        </tr>
    </table>
</div>

@php $isVehicleQuote = $quote->quote_type === 'vehicle' && $quote->vq_vehicle_price; @endphp

@if($isVehicleQuote)
@php
    $freq    = $quote->vq_payment_frequency === 'annual' ? 'anual' : 'mensual';
    $termYrs = $quote->vq_term_months ? round($quote->vq_term_months / 12, 1) : null;
    $ratePct = $quote->vq_interest_rate ? number_format($quote->vq_interest_rate * 100, 2) : '0.00';
@endphp
@if($quote->property)
<div class="section">
    <div class="section-title">Vehículo</div>
    <table class="info-grid">
        <tr>
            <td width="60%"><div class="info-label">Vehículo</div><div class="info-value">{{ trim(($quote->property->brand ?? '').' '.($quote->property->model ?? '').' ('.($quote->property->year ?? '').')') }} — {{ $quote->property->name }}</div></td>
            <td width="40%"><div class="info-label">Precio</div><div class="info-value">₡{{ number_format($quote->vq_vehicle_price, 0, ',', '.') }}</div></td>
        </tr>
    </table>
</div>
@endif
<div class="section">
    <div class="section-title">Plan de Financiamiento — {{ $quote->title }}</div>
    <table class="info-grid" style="margin-bottom:12px;">
        <tr>
            <td width="25%"><div class="info-label">Prima ({{ number_format($quote->vq_down_payment_pct, 0) }}%)</div><div class="info-value">₡{{ number_format($quote->vq_down_payment, 0, ',', '.') }}</div></td>
            <td width="25%"><div class="info-label">Plazo</div><div class="info-value">{{ $quote->vq_term_months }} meses{{ $termYrs ? ' ('.$termYrs.' años)' : '' }}</div></td>
            <td width="25%"><div class="info-label">Tasa anual</div><div class="info-value">{{ $ratePct }}%</div></td>
            <td width="25%"><div class="info-label">Frecuencia pago</div><div class="info-value">{{ ucfirst($freq) }}</div></td>
        </tr>
    </table>
    <table style="background:#1a1a2e;border-radius:6px;overflow:hidden;">
        <tr>
            <td style="padding:14px 20px;">
                <div style="color:#aaa;font-size:9px;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Cuota estimada ({{ $freq }})</div>
                <div style="color:#c2ac1f;font-size:20px;font-weight:bold;">₡{{ number_format($quote->vq_monthly_payment, 2, ',', '.') }}</div>
            </td>
            <td style="padding:14px 20px;text-align:right;">
                <div style="color:#aaa;font-size:9px;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Total a pagar</div>
                <div style="color:#fff;font-size:15px;font-weight:bold;">₡{{ number_format($quote->vq_total_amount, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>
    <p style="font-size:9px;color:#aaa;margin-top:6px;">* Cuota calculada bajo el método francés de amortización. Condiciones finales sujetas a aprobación crediticia.</p>
</div>
@endif

@if(!$isVehicleQuote)
@if($quote->property)
<div class="section">
    <div class="section-title">Propiedad de Referencia</div>
    <table class="info-grid">
        <tr>
            <td width="50%"><div class="info-label">Propiedad</div><div class="info-value">{{ $quote->property->title ?? $quote->property->name }}</div></td>
            <td width="25%"><div class="info-label">Precio de lista</div><div class="info-value">{{ ($quote->property->currency === 'USD' ? '$' : '₡') . ' ' . number_format($quote->property->price, 0, ',', '.') }}</div></td>
            <td width="25%"><div class="info-label">Habitaciones</div><div class="info-value">{{ $quote->property->rooms ?? '—' }}</div></td>
        </tr>
    </table>
</div>
@endif
<div class="section">
    <div class="section-title">{{ $quote->title }}</div>
    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th class="text-center" style="width:70px;">Cant.</th>
                <th class="text-right" style="width:120px;">Precio unit.</th>
                <th class="text-right" style="width:120px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
        @foreach($quote->items ?? [] as $item)
        <tr>
            <td>{{ $item['description'] }}</td>
            <td class="text-center">{{ $item['qty'] }}</td>
            <td class="text-right">{{ number_format($item['price'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($item['qty'] * $item['price'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="3" class="text-right" style="color:#888;font-size:10px;padding:5px 10px;">Subtotal</td>
                <td class="text-right" style="font-weight:600;">{{ $quote->formatted_subtotal }}</td></tr>
            @if($quote->discount_pct > 0)
            <tr><td colspan="3" class="text-right" style="color:#888;font-size:10px;padding:5px 10px;">Descuento ({{ $quote->discount_pct }}%)</td>
                <td class="text-right" style="color:#ef4444;font-weight:600;">-{{ number_format($quote->subtotal * $quote->discount_pct / 100, 0, ',', '.') }}</td></tr>
            @endif
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL</td>
                <td class="text-right amount">{{ $quote->formatted_total }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

@if($quote->notes)
<div class="section">
    <div class="section-title">Notas y Condiciones</div>
    <p style="font-size:11px;color:#555;line-height:1.6;">{{ $quote->notes }}</p>
</div>
@endif

<div class="section" style="background:#f8fafc;border-radius:8px;padding:10px 14px;">
    <p style="font-size:10px;color:#888;margin:0;">
        Esta cotización tiene una vigencia de <strong>{{ $quote->validity_days }} días</strong> a partir de su fecha de emisión (válida hasta {{ $quote->valid_until }}).
        El precio final puede estar sujeto a condiciones adicionales. Documento generado el {{ $printDate }}.
    </p>
</div>

<div class="footer">
    {{ $company->name ?? '' }} &nbsp;·&nbsp; Cotización {{ $quote->quote_number }} &nbsp;·&nbsp; Confidencial
</div>

</div>
</body>
</html>
