@extends('admin.main')
@section('title', 'Cotización ' . $quote->quote_number)
@section('content')
@include('admin.crm.layouts._crm-styles')

<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-file-text-o"></i> {{ $quote->quote_number }}</h2>
            <div class="sub">{{ $quote->title }}</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.quotes.pdf', $quote) }}" target="_blank" class="action-btn secondary">
                <i class="fa fa-file-pdf-o"></i> Ver PDF
            </a>
            <a href="{{ route('admin.crm.quotes.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    @if(Session::has('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:12px;padding:12px 18px;margin-bottom:16px;color:#065f46;font-size:13.5px;">
        <i class="fa fa-check-circle" style="color:#22c55e;"></i> {{ Session::get('success') }}
    </div>
    @endif

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
        {{-- Main info --}}
        <div>
            <div class="dashboard-card" style="margin-bottom:18px;">
                <div class="dc-header"><h5><i class="fa fa-info-circle" style="color:#c2ac1f;"></i> Detalle</h5></div>
                <div class="dc-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                        <div><div style="font-size:11px;color:#aaa;text-transform:uppercase;font-weight:700;">Lead</div>
                            @if($quote->lead)<a href="{{ route('admin.crm.leads.show', $quote->lead) }}" style="font-weight:600;color:#1a1a2e;">{{ $quote->lead->name }}</a>@endif</div>
                        <div><div style="font-size:11px;color:#aaa;text-transform:uppercase;font-weight:700;">Estado</div>
                            <span class="crm-badge {{ $quote->status_color }}">{{ $quote->status_label }}</span></div>
                        <div><div style="font-size:11px;color:#aaa;text-transform:uppercase;font-weight:700;">Vigencia</div>
                            <span style="font-size:13px;">{{ $quote->validity_days }} días (hasta {{ $quote->valid_until }})</span></div>
                        <div><div style="font-size:11px;color:#aaa;text-transform:uppercase;font-weight:700;">Creado por</div>
                            <span style="font-size:13px;">{{ $quote->creator?->name ?? '—' }}</span></div>
                    </div>

                    {{-- Items table --}}
                    <table class="crm-table">
                        <thead><tr><th>Descripción</th><th style="text-align:center;">Cant.</th><th style="text-align:right;">Precio</th><th style="text-align:right;">Subtotal</th></tr></thead>
                        <tbody>
                        @foreach($quote->items ?? [] as $item)
                        <tr>
                            <td>{{ $item['description'] }}</td>
                            <td style="text-align:center;">{{ $item['qty'] }}</td>
                            <td style="text-align:right;">{{ number_format($item['price'], 0, ',', '.') }}</td>
                            <td style="text-align:right;font-weight:600;">{{ number_format($item['qty'] * $item['price'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td colspan="3" style="text-align:right;color:#888;font-size:13px;">Subtotal</td><td style="text-align:right;font-weight:600;">{{ $quote->formatted_subtotal }}</td></tr>
                            @if($quote->discount_pct > 0)
                            <tr><td colspan="3" style="text-align:right;color:#888;font-size:13px;">Descuento ({{ $quote->discount_pct }}%)</td><td style="text-align:right;color:#ef4444;font-weight:600;">-{{ number_format($quote->subtotal * $quote->discount_pct / 100, 0, ',', '.') }}</td></tr>
                            @endif
                            <tr><td colspan="3" style="text-align:right;font-size:15px;font-weight:700;color:#1a1a2e;">TOTAL</td><td style="text-align:right;font-size:16px;font-weight:700;color:#c2ac1f;">{{ $quote->formatted_total }}</td></tr>
                        </tfoot>
                    </table>

                    @if($quote->notes)
                    <div style="margin-top:16px;background:#f8fafc;border-radius:10px;padding:14px;font-size:13px;color:#555;">
                        <strong>Notas:</strong><br>{{ $quote->notes }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Status panel --}}
        <div>
            <div class="dashboard-card">
                <div class="dc-header"><h5><i class="fa fa-exchange" style="color:#c2ac1f;"></i> Cambiar Estado</h5></div>
                <div class="dc-body">
                    @foreach(\App\Models\LeadQuote::getStatuses() as $k => $v)
                    <form method="POST" action="{{ route('admin.crm.quotes.update-status', $quote) }}" style="margin-bottom:8px;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $k }}">
                        <button type="submit" class="action-btn {{ $quote->status === $k ? 'primary' : 'secondary' }}" style="width:100%;justify-content:flex-start;gap:8px;">
                            <i class="fa fa-circle" style="color:{{ $quote->status === $k ? '#c2ac1f' : '#ccc' }};font-size:9px;"></i>
                            {{ $v }}
                        </button>
                    </form>
                    @endforeach

                    <hr style="margin:16px 0;">
                    <form method="POST" action="{{ route('admin.crm.quotes.destroy', $quote) }}" onsubmit="return confirm('¿Eliminar esta cotización?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn danger" style="width:100%;"><i class="fa fa-trash"></i> Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
