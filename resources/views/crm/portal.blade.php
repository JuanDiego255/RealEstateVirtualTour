<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Cliente</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .portal-card { max-width: 640px; margin: 48px auto; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(0,0,0,.10); overflow: hidden; }
        .portal-header { background: #1a1a2e; padding: 28px 32px; color: #fff; }
        .portal-header h1 { font-size: 22px; font-weight: 700; margin: 0 0 4px; }
        .portal-header p  { font-size: 14px; color: rgba(255,255,255,.65); margin: 0; }
        .portal-body { padding: 28px 32px; }
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #888; font-weight: 600; }
        .badge-status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; background: #1a1a2e; color: #c2ac1f; }
        .quotes-section { margin-top: 24px; }
        .quotes-section h4 { font-size: 15px; font-weight: 700; color: #1a1a2e; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .quote-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0; gap: 12px; flex-wrap: wrap; }
        .quote-item:last-child { border-bottom: none; }
        .quote-meta { flex: 1; }
        .quote-number { font-weight: 700; font-size: 13px; color: #1a1a2e; }
        .quote-date { font-size: 11px; color: #aaa; margin-top: 2px; }
        .quote-total { font-weight: 700; font-size: 14px; color: #1a1a2e; white-space: nowrap; }
        .quote-pdf { display: inline-flex; align-items: center; gap: 5px; background: #ef4444; color: #fff; border-radius: 8px; padding: 5px 12px; font-size: 12px; font-weight: 600; text-decoration: none; white-space: nowrap; }
        .quote-pdf:hover { background: #dc2626; color: #fff; text-decoration: none; }
        .badge-quote { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; background: #f1f5f9; color: #475569; margin-left: 6px; }
        .badge-quote.viewed { background: #d1fae5; color: #065f46; }
        .badge-quote.accepted { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
<div class="portal-card">
    <div class="portal-header">
        <h1>Hola, {{ $lead->name }}</h1>
        <p>Aquí puedes ver el estado de tu proceso con nosotros.</p>
    </div>
    <div class="portal-body">
        <div class="info-row">
            <span class="info-label">Estado actual</span>
            <span class="badge-status">{{ $lead->status_label }}</span>
        </div>
        @if($lead->property)
        <div class="info-row">
            <span class="info-label">Propiedad de interés</span>
            <span>{{ $lead->property->title }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Presupuesto</span>
            <span>{{ $lead->budget_range }}</span>
        </div>
        @if($lead->next_follow_up)
        <div class="info-row">
            <span class="info-label">Próxima cita / seguimiento</span>
            <span>{{ $lead->next_follow_up->format('d/m/Y') }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Tu asesor</span>
            <span>{{ $lead->user?->name ?? 'Por asignar' }}</span>
        </div>
        @if($lead->user?->phone)
        <div class="info-row">
            <span class="info-label">Contacto del asesor</span>
            <span>{{ $lead->user->phone }}</span>
        </div>
        @endif
    </div>

    @if($lead->relationLoaded('quotes') && $lead->quotes->isNotEmpty())
    <div class="portal-body" style="border-top: 2px solid #f0f0f0; padding-top: 0;">
        <div class="quotes-section">
            <h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#c2ac1f" viewBox="0 0 16 16"><path d="M4 0h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H4z"/><path d="M4.5 4a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7zm0 3a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7zm0 3a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1h-4z"/></svg>
                Mis Cotizaciones
            </h4>
            @foreach($lead->quotes as $quote)
            <div class="quote-item">
                <div class="quote-meta">
                    <div class="quote-number">
                        {{ $quote->quote_number ?? ('COT-' . str_pad($quote->id, 4, '0', STR_PAD_LEFT)) }}
                        — {{ $quote->title }}
                        <span class="badge-quote {{ in_array($quote->status, ['viewed','accepted']) ? $quote->status : '' }}">
                            {{ $quote->status_label ?? ucfirst($quote->status) }}
                        </span>
                    </div>
                    <div class="quote-date">{{ $quote->created_at->format('d/m/Y') }}</div>
                </div>
                <div class="quote-total">
                    @if($quote->quote_type === 'vehicle' && $quote->vq_monthly_payment)
                        ₡{{ number_format($quote->vq_monthly_payment, 2, ',', '.') }}/mes
                    @else
                        ₡{{ number_format($quote->total ?? 0, 0, ',', '.') }}
                    @endif
                </div>
                @if(Route::has('admin.crm.quotes.pdf'))
                <a href="{{ route('admin.crm.quotes.pdf', $quote) }}" target="_blank" class="quote-pdf">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>
                    Ver PDF
                </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
</body>
</html>
