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
</div>
</body>
</html>
