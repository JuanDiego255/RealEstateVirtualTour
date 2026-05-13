<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Recordatorios Vencidos</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Helvetica','Arial',sans-serif; font-size:9px; color:#333; background:#fff; line-height:1.4; }
.page { padding:24px 28px; }
.badge { display:inline-block; padding:2px 6px; border-radius:3px; font-size:8px; font-weight:bold; }
.badge-danger   { background:#dc3545; color:#fff; }
.badge-warning  { background:#ffc107; color:#333; }
.badge-secondary{ background:#6c757d; color:#fff; }
.badge-primary  { background:#007bff; color:#fff; }
table { width:100%; border-collapse:collapse; margin-bottom:10px; }
th { background:#6c757d; color:#fff; padding:5px 8px; font-size:8px; text-align:left; }
td { padding:5px 8px; border-bottom:1px solid #f0f0f0; font-size:8px; vertical-align:top; }
tr:nth-child(even) td { background:#fafafa; }
.alert-box { background:#f8d7da; border:1px solid #f5c6cb; border-radius:4px; padding:8px 12px; margin-bottom:14px; font-size:9px; color:#721c24; }
</style>
</head>
<body>
<div class="page">

    @php $reportTitle = 'Recordatorios Vencidos'; @endphp
    @include('admin.crm.reports.pdf._header')

    <div class="alert-box">
        <strong>Atención:</strong> Los siguientes {{ $reminders->count() }} recordatorios han vencido y no han sido atendidos.
        Revisar y resolver cuanto antes para mantener la productividad del equipo.
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:20%;">Título</th>
                <th style="width:10%;">Prioridad</th>
                <th style="width:12%;">Fecha programada</th>
                <th style="width:10%;">Días vencido</th>
                <th style="width:18%;">Relacionado a</th>
                <th style="width:12%;">Agente</th>
                <th style="width:18%;">Descripción</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reminders as $rem)
            @php $daysOverdue = $rem->remind_at->diffInDays(now()); @endphp
            <tr>
                <td><strong>{{ $rem->title }}</strong></td>
                <td>
                    <span class="badge badge-{{ $rem->priority === 'urgent' ? 'danger' : ($rem->priority === 'high' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($rem->priority) }}
                    </span>
                </td>
                <td style="color:#dc3545;">{{ $rem->remind_at->format('d/m/Y H:i') }}</td>
                <td style="{{ $daysOverdue >= 3 ? 'color:#dc3545;font-weight:bold;' : 'color:#e67e22;' }}">
                    {{ $daysOverdue }} día{{ $daysOverdue !== 1 ? 's' : '' }}
                </td>
                <td>
                    @if($rem->remindable)
                        @if($rem->remindable instanceof \App\Lead)
                            Lead: {{ $rem->remindable->name }}
                        @elseif($rem->remindable instanceof \App\Appointment)
                            Cita: {{ \Illuminate\Support\Str::limit($rem->remindable->title, 22) }}
                        @else
                            {{ $rem->getRelatedLabelAttribute() ?? '—' }}
                        @endif
                    @else —
                    @endif
                </td>
                <td>{{ $rem->user?->name ?? '—' }}</td>
                <td>{{ $rem->description ? \Illuminate\Support\Str::limit($rem->description, 40) : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center; color:#aaa; padding:14px;">No hay recordatorios vencidos. ¡Todo al día!</td></tr>
            @endforelse
        </tbody>
    </table>

    <table width="100%" style="border-top:1px solid #dee2e6; margin-top:8px; padding-top:6px;">
        <tr>
            <td style="font-size:7.5px; color:#aaa; border:none;">{{ $company->name }} · Recordatorios Vencidos · {{ $printDate }}</td>
            <td style="font-size:7.5px; color:#aaa; text-align:right; border:none;">Total: {{ $reminders->count() }} · Confidencial</td>
        </tr>
    </table>

</div>
</body>
</html>
