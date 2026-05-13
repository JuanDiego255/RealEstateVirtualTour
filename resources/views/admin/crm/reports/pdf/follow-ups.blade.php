<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Reporte de Seguimientos</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Helvetica','Arial',sans-serif; font-size:9px; color:#333; background:#fff; line-height:1.4; }
.page { padding:24px 28px; }
.badge { display:inline-block; padding:2px 6px; border-radius:3px; font-size:7.5px; font-weight:bold; }
.badge-info     { background:#17a2b8; color:#fff; }
.badge-success  { background:#28a745; color:#fff; }
.badge-warning  { background:#ffc107; color:#333; }
.badge-danger   { background:#dc3545; color:#fff; }
.badge-primary  { background:#007bff; color:#fff; }
.badge-secondary{ background:#6c757d; color:#fff; }
table { width:100%; border-collapse:collapse; margin-bottom:14px; }
th { background:#e67e22; color:#fff; padding:5px 8px; font-size:8px; text-align:left; }
td { padding:5px 8px; border-bottom:1px solid #f0f0f0; font-size:8px; vertical-align:top; }
tr:nth-child(even) td { background:#fafafa; }
.overdue { color:#dc3545; font-weight:bold; }
.activities-cell { font-size:7.5px; color:#555; }
</style>
</head>
<body>
<div class="page">

    @php $reportTitle = 'Reporte de Seguimientos'; @endphp
    @include('admin.crm.reports.pdf._header')

    {{-- Resumen --}}
    @php
        $overdue  = $leads->filter(fn($l) => $l->isOverdueForFollowUp())->count();
        $today    = $leads->filter(fn($l) => $l->needsFollowUpToday() && !$l->isOverdueForFollowUp())->count();
        $upcoming = $leads->count() - $overdue - $today;
    @endphp
    <table style="margin-bottom:14px; border:1px solid #e9ecef;">
        <tr>
            <td style="background:#f8f9fa; padding:6px 10px; border:none;">
                <strong style="font-size:9px;">Resumen:</strong>
                <span style="font-size:9px; margin-left:10px;">Total: <strong>{{ $leads->count() }}</strong></span>
                <span style="font-size:9px; margin-left:10px; color:#dc3545;">Vencidos: <strong>{{ $overdue }}</strong></span>
                <span style="font-size:9px; margin-left:10px; color:#e67e22;">Hoy: <strong>{{ $today }}</strong></span>
                <span style="font-size:9px; margin-left:10px; color:#28a745;">Próximos: <strong>{{ $upcoming }}</strong></span>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width:17%;">Lead</th>
                <th style="width:9%;">Estado</th>
                <th style="width:9%;">Prioridad</th>
                <th style="width:10%;">Agente</th>
                <th style="width:11%;">Próx. seguim.</th>
                <th style="width:10%;">Último contac.</th>
                <th style="width:34%;">Últimas actividades (hasta 3)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leads as $lead)
            <tr>
                <td>
                    <strong>{{ $lead->name }}</strong>
                    @if($lead->phone)<br><span style="color:#888;">{{ $lead->phone }}</span>@endif
                </td>
                <td><span class="badge badge-{{ $lead->status_color }}">{{ $lead->status_label }}</span></td>
                <td><span class="badge badge-{{ $lead->priority_color }}">{{ $lead->priority_label }}</span></td>
                <td>{{ $lead->user?->name ?? '—' }}</td>
                <td class="{{ $lead->isOverdueForFollowUp() ? 'overdue' : '' }}">
                    {{ $lead->next_follow_up->format('d/m/Y') }}
                    @if($lead->isOverdueForFollowUp())
                        <br><span style="font-size:7.5px;">({{ $lead->next_follow_up->diffForHumans() }})</span>
                    @endif
                </td>
                <td>{{ $lead->last_contact_at?->format('d/m/Y H:i') ?? '—' }}</td>
                <td class="activities-cell">
                    @if($lead->activities->isEmpty())
                        <span style="color:#aaa;">Sin actividades</span>
                    @else
                        @foreach($lead->activities->take(3) as $act)
                            <div style="margin-bottom:3px; padding-bottom:3px; {{ !$loop->last ? 'border-bottom:1px solid #eee;' : '' }}">
                                <strong>{{ $act->type_label }}</strong>
                                · {{ $act->activity_at->format('d/m/Y') }}
                                · {{ $act->user?->name ?? 'Sistema' }}
                                @if($act->subject)
                                    <br><span style="color:#888;">{{ \Illuminate\Support\Str::limit($act->subject, 40) }}</span>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center; color:#aaa; padding:14px;">Sin seguimientos con los filtros aplicados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table width="100%" style="border-top:1px solid #dee2e6; margin-top:8px; padding-top:6px;">
        <tr>
            <td style="font-size:7.5px; color:#aaa; border:none;">{{ $company->name }} · Reporte de Seguimientos · {{ $printDate }}</td>
            <td style="font-size:7.5px; color:#aaa; text-align:right; border:none;">Total: {{ $leads->count() }} leads · Confidencial</td>
        </tr>
    </table>

</div>
</body>
</html>
