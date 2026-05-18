<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Cliente — {{ $lead->name }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding: 32px 16px 60px;
        }
        .portal-brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .portal-brand h1 {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #c2ac1f;
            margin: 0;
        }
        .portal-wrap { max-width: 700px; margin: 0 auto; }
        .portal-card, .quotes-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
            margin-bottom: 20px;
        }
        .portal-hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #2d2d4e 100%);
            padding: 32px 32px 28px;
            position: relative;
            overflow: hidden;
        }
        .portal-hero::before {
            content: ''; position: absolute; top: -40px; right: -40px;
            width: 180px; height: 180px; border-radius: 50%;
            background: rgba(194,172,31,.08);
        }
        .portal-hero h2 { font-size: 24px; font-weight: 800; color: #fff; margin: 0 0 4px; }
        .portal-hero p  { font-size: 13px; color: rgba(255,255,255,.6); margin: 0; }
        .portal-hero .status-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(194,172,31,.15); border: 1px solid rgba(194,172,31,.4);
            color: #c2ac1f; border-radius: 20px; padding: 4px 14px;
            font-size: 12px; font-weight: 700; margin-top: 12px;
        }
        .portal-info {
            padding: 24px 32px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }
        @media(max-width:540px) { .portal-info { grid-template-columns: 1fr; } }
        .info-cell {
            padding: 14px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .info-cell:nth-child(odd)  { padding-right: 20px; border-right: 1px solid #f5f5f5; }
        .info-cell:nth-child(even) { padding-left: 20px; }
        .info-label {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: #aaa; margin-bottom: 4px;
        }
        .info-value { font-size: 14px; font-weight: 600; color: #1a1a2e; }
        .info-value a { color: #1a1a2e; text-decoration: none; }
        .info-value a:hover { color: #c2ac1f; }
        .advisor-card {
            margin: 0 32px 24px;
            background: #f8f9fa; border-radius: 14px; padding: 16px 20px;
            display: flex; align-items: center; gap: 16px;
        }
        .advisor-avatar {
            width: 46px; height: 46px; border-radius: 50%;
            background: linear-gradient(135deg, #1a1a2e, #2d2d4e);
            display: flex; align-items: center; justify-content: center;
            color: #c2ac1f; font-size: 18px; flex-shrink: 0;
        }
        .advisor-name { font-weight: 700; font-size: 14px; color: #1a1a2e; margin-bottom: 2px; }
        .advisor-role { font-size: 11px; color: #888; }
        .advisor-contact { margin-top: 6px; font-size: 12px; }
        .advisor-contact a { color: #1a1a2e; text-decoration: none; margin-right: 12px; }
        .advisor-contact a:hover { color: #c2ac1f; }
        .quotes-header {
            background: #1a1a2e; padding: 18px 28px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .quotes-header h3 {
            font-size: 15px; font-weight: 700; color: #fff; margin: 0;
            display: flex; align-items: center; gap: 8px;
        }
        .quotes-header h3 i { color: #c2ac1f; }
        .quotes-count {
            background: rgba(194,172,31,.2); color: #c2ac1f;
            border-radius: 20px; padding: 3px 10px; font-size: 12px; font-weight: 700;
        }
        .quote-row { padding: 18px 28px; border-bottom: 1px solid #f5f5f5; }
        .quote-row:last-child { border-bottom: none; }
        .quote-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 8px; }
        .quote-num  { font-weight: 800; font-size: 13px; color: #1a1a2e; }
        .quote-title{ font-size: 14px; color: #444; margin-top: 2px; }
        .quote-date { font-size: 11px; color: #aaa; margin-top: 2px; }
        .q-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;
        }
        .q-badge.draft    { background:#f1f5f9; color:#64748b; }
        .q-badge.sent     { background:#dbeafe; color:#1d4ed8; }
        .q-badge.viewed   { background:#e0f2fe; color:#0369a1; }
        .q-badge.accepted { background:#d1fae5; color:#065f46; }
        .q-badge.rejected { background:#fee2e2; color:#991b1b; }
        .vq-summary {
            background: linear-gradient(135deg, #1a1a2e 0%, #2d2d4e 100%);
            border-radius: 12px; padding: 14px 18px; margin-top: 10px;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
        }
        .vq-item { text-align: center; }
        .vq-label { font-size: 10px; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: 1px; }
        .vq-value       { font-size: 16px; font-weight: 800; color: #c2ac1f; margin-top: 2px; }
        .vq-value.white { color: #fff; font-size: 14px; }
        .pdf-btn {
            display: inline-flex; align-items: center; gap: 6px;
            background: #ef4444; color: #fff; border-radius: 8px;
            padding: 7px 14px; font-size: 12px; font-weight: 700;
            text-decoration: none; margin-top: 8px; transition: background .15s;
        }
        .pdf-btn:hover { background: #dc2626; color: #fff; text-decoration: none; }
        .portal-footer {
            text-align: center; font-size: 11px;
            color: rgba(255,255,255,.3); margin-top: 8px;
        }
    </style>
</head>
<body>
<div class="portal-wrap">

    <div class="portal-brand">
        <h1>Portal del Cliente</h1>
    </div>

    {{-- ── Main info card ── --}}
    <div class="portal-card">
        <div class="portal-hero">
            <h2>Hola, {{ $lead->name }}</h2>
            <p>Aquí puedes ver el estado de tu proceso con nosotros.</p>
            <div class="status-pill">
                <i class="fa fa-circle" style="font-size:8px;"></i>
                {{ $lead->status_label }}
            </div>
        </div>

        <div class="portal-info">
            @if($lead->budget_range)
            <div class="info-cell">
                <div class="info-label"><i class="fa fa-money"></i> Presupuesto</div>
                <div class="info-value">{{ $lead->budget_range }}</div>
            </div>
            @endif
            @if($lead->property)
            <div class="info-cell">
                <div class="info-label"><i class="fa fa-home"></i> Propiedad de interés</div>
                <div class="info-value">{{ $lead->property->title }}</div>
            </div>
            @endif
            @if($lead->vehicle)
            <div class="info-cell">
                <div class="info-label"><i class="fa fa-car"></i> Vehículo de interés</div>
                <div class="info-value">{{ $lead->vehicle->name }}</div>
            </div>
            @endif
            @if($lead->next_follow_up)
            <div class="info-cell">
                <div class="info-label"><i class="fa fa-calendar"></i> Próxima cita / seguimiento</div>
                <div class="info-value">{{ $lead->next_follow_up->format('d/m/Y') }}</div>
            </div>
            @endif
            @if($lead->email)
            <div class="info-cell">
                <div class="info-label"><i class="fa fa-envelope"></i> Email de contacto</div>
                <div class="info-value"><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></div>
            </div>
            @endif
            @if($lead->phone)
            <div class="info-cell">
                <div class="info-label"><i class="fa fa-phone"></i> Teléfono</div>
                <div class="info-value"><a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></div>
            </div>
            @endif
        </div>

        {{-- Advisor --}}
        <div class="advisor-card">
            <div class="advisor-avatar"><i class="fa fa-user"></i></div>
            <div>
                <div class="advisor-name">{{ $lead->user?->name ?? 'Por asignar' }}</div>
                <div class="advisor-role">Tu asesor asignado</div>
                @if($lead->user)
                <div class="advisor-contact">
                    @if($lead->user->email)
                        <a href="mailto:{{ $lead->user->email }}">
                            <i class="fa fa-envelope"></i> {{ $lead->user->email }}
                        </a>
                    @endif
                    @if($lead->user->phone ?? null)
                        <a href="tel:{{ $lead->user->phone }}">
                            <i class="fa fa-phone"></i> {{ $lead->user->phone }}
                        </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Cotizaciones ── --}}
    @if($lead->relationLoaded('quotes') && $lead->quotes->isNotEmpty())
    <div class="quotes-card">
        <div class="quotes-header">
            <h3><i class="fa fa-file-text-o"></i> Mis Cotizaciones</h3>
            <span class="quotes-count">{{ $lead->quotes->count() }}</span>
        </div>

        @foreach($lead->quotes as $quote)
        <div class="quote-row">
            <div class="quote-top">
                <div>
                    <div class="quote-num">
                        {{ $quote->quote_number ?? ('COT-' . str_pad($quote->id, 4, '0', STR_PAD_LEFT)) }}
                        <span class="q-badge {{ $quote->status }}" style="margin-left:6px;">
                            {{ $quote->status_label ?? ucfirst($quote->status) }}
                        </span>
                    </div>
                    <div class="quote-title">{{ $quote->title }}</div>
                    <div class="quote-date"><i class="fa fa-calendar-o"></i> {{ $quote->created_at->format('d/m/Y') }}</div>
                </div>
                @if(Route::has('admin.crm.quotes.pdf'))
                <a href="{{ route('admin.crm.quotes.pdf', $quote) }}" target="_blank" class="pdf-btn">
                    <i class="fa fa-file-pdf-o"></i> Ver PDF
                </a>
                @endif
            </div>

            @if($quote->quote_type === 'vehicle' && $quote->vq_monthly_payment)
            <div class="vq-summary">
                <div class="vq-item">
                    <div class="vq-label">Precio vehículo</div>
                    <div class="vq-value white">₡{{ number_format($quote->vq_vehicle_price, 0, ',', '.') }}</div>
                </div>
                <div class="vq-item">
                    <div class="vq-label">Prima ({{ number_format($quote->vq_down_payment_pct, 0) }}%)</div>
                    <div class="vq-value white">₡{{ number_format($quote->vq_down_payment, 0, ',', '.') }}</div>
                </div>
                <div class="vq-item">
                    <div class="vq-label">Plazo</div>
                    <div class="vq-value white">{{ $quote->vq_term_months }} meses</div>
                </div>
                <div class="vq-item">
                    <div class="vq-label">Cuota {{ $quote->vq_payment_frequency === 'annual' ? 'anual' : 'mensual' }}</div>
                    <div class="vq-value">₡{{ number_format($quote->vq_monthly_payment, 2, ',', '.') }}</div>
                </div>
            </div>
            @else
            <div style="font-size:14px;font-weight:700;color:#1a1a2e;margin-top:6px;">
                Total: ₡{{ number_format($quote->total ?? 0, 0, ',', '.') }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <div class="portal-footer">
        Documento confidencial &nbsp;·&nbsp; Generado el {{ now()->format('d/m/Y H:i') }}
    </div>

</div>
</body>
</html>
