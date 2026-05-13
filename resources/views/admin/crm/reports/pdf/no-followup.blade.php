<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Leads Sin Seguimiento</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Helvetica','Arial',sans-serif; font-size:9px; color:#333; background:#fff; line-height:1.4; }
.page { padding:24px 28px; }
.badge { display:inline-block; padding:2px 6px; border-radius:3px; font-size:8px; font-weight:bold; }
.badge-info     { background:#17a2b8; color:#fff; }
.badge-success  { background:#28a745; color:#fff; }
.badge-warning  { background:#ffc107; color:#333; }
.badge-danger   { background:#dc3545; color:#fff; }
.badge-primary  { background:#007bff; color:#fff; }
.badge-secondary{ background:#6c757d; color:#fff; }
table { width:100%; border-collapse:collapse; margin-bottom:10px; }
th { background:#dc3545; color:#fff; padding:5px 8px; font-size:8px; text-align:left; }
td { padding:5px 8px; border-bottom:1px solid #f0f0f0; font-size:8px; vertical-align:top; }
tr:nth-child(even) td { background:#fff5f5; }
.alert-box { background:#fff3cd; border:1px solid #ffc107; border-radius:4px; padding:8px 12px; margin-bottom:14px; font-size:9px; }
</style>
</head>
<body>
<div class="page">

    @php $reportTitle = 'Leads Sin Seguimiento'; @endphp
    @include('admin.crm.reports.pdf._header')

    <div class="alert-box">
        <strong>Atención:</strong> Los siguientes {{ $leads->count() }} leads están activos pero no tienen una fecha de seguimiento asignada.
        Estos leads pueden estar siendo descuidados. Se recomienda asignarles una fecha de seguimiento inmediatamente.
    </div>

    @php $byAgent = $leads->groupBy('user_id'); @endphp

    <table>
        <thead>
            <tr>
                <th style="width:18%;">Lead</th>
                <th style="width:12%;">Contacto</th>
                <th style="width:9%;">Estado</th>
                <th style="width:9%;">Prioridad</th>
                <th style="width:9%;">Fuente</th>
                <th style="width:11%;">Agente</th>
                <th style="width:11%;">Último contacto</th>
                <th style="width:8%;">Creado</th>
                <th style="width:13%;">Días sin seguim.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leads as $lead)
            @php $daysSince = $lead->last_contact_at ? $lead->last_contact_at->diffInDays(now()) : $lead->created_at->diffInDays(now()); @endphp
            <tr>
                <td><strong>{{ $lead->name }}</strong></td>
                <td>{{ $lead->phone ?: $lead->email ?: '—' }}</td>
                <td><span class="badge badge-{{ $lead->status_color }}">{{ $lead->status_label }}</span></td>
                <td><span class="badge badge-{{ $lead->priority_color }}">{{ $lead->priority_label }}</span></td>
                <td>{{ $lead->source_label }}</td>
                <td>{{ $lead->user?->name ?? 'Sin asignar' }}</td>
                <td>{{ $lead->last_contact_at?->format('d/m/Y') ?? '—' }}</td>
                <td>{{ $lead->created_at->format('d/m/Y') }}</td>
                <td style="{{ $daysSince >= 7 ? 'color:#dc3545;font-weight:bold;' : ($daysSince >= 3 ? 'color:#e67e22;' : '') }}">
                    {{ $daysSince }} día{{ $daysSince !== 1 ? 's' : '' }}
                    @if($daysSince >= 7)<br><span style="font-size:7.5px;">¡Urgente!</span>@endif
                </td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center; color:#aaa; padding:14px;">No hay leads activos sin seguimiento. ¡Bien hecho!</td></tr>
            @endforelse
        </tbody>
    </table>

    <table width="100%" style="border-top:1px solid #dee2e6; margin-top:8px; padding-top:6px;">
        <tr>
            <td style="font-size:7.5px; color:#aaa; border:none;">{{ $company->name }} · Leads Sin Seguimiento · {{ $printDate }}</td>
            <td style="font-size:7.5px; color:#aaa; text-align:right; border:none;">Total: {{ $leads->count() }} leads · Confidencial</td>
        </tr>
    </table>

</div>
</body>
</html>
