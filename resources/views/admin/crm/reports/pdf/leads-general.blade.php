<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Leads General</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Helvetica','Arial',sans-serif; font-size:8.5px; color:#333; background:#fff; line-height:1.4; }
.page { padding:24px 28px; }
.badge { display:inline-block; padding:1px 6px; border-radius:3px; font-size:7.5px; font-weight:bold; }
.badge-info     { background:#17a2b8; color:#fff; }
.badge-success  { background:#28a745; color:#fff; }
.badge-warning  { background:#ffc107; color:#333; }
.badge-danger   { background:#dc3545; color:#fff; }
.badge-primary  { background:#007bff; color:#fff; }
.badge-secondary{ background:#6c757d; color:#fff; }
.badge-dark     { background:#343a40; color:#fff; }
table { width:100%; border-collapse:collapse; margin-bottom:10px; }
th { background:#2c3e50; color:#fff; padding:5px 7px; font-size:8px; text-align:left; }
td { padding:5px 7px; border-bottom:1px solid #f0f0f0; font-size:8px; vertical-align:top; }
tr:nth-child(even) td { background:#f9f9f9; }
.summary-box { display:inline-block; text-align:center; padding:8px 14px; border-radius:4px; margin-right:8px; margin-bottom:12px; }
</style>
</head>
<body>
<div class="page">

    @php $reportTitle = 'Reporte General de Leads'; @endphp
    @include('admin.crm.reports.pdf._header')

    {{-- Resumen estadístico --}}
    @php
        $byStatus = $leads->groupBy('status');
        $statuses = \App\Lead::getStatuses();
    @endphp
    <table style="margin-bottom:14px; border:1px solid #e9ecef;">
        <tr>
            <td style="background:#f8f9fa; padding:6px 10px; border:none;" colspan="{{ count($statuses) + 1 }}">
                <strong style="font-size:9px;">Resumen:</strong>
                <span style="font-size:9px; margin-left:8px;">
                    Total: <strong>{{ $leads->count() }}</strong>
                </span>
                @foreach($statuses as $key => $label)
                @php $cnt = $byStatus->get($key, collect())->count(); @endphp
                @if($cnt > 0)
                    <span style="font-size:9px; margin-left:8px;">
                        {{ $label }}: <strong>{{ $cnt }}</strong>
                    </span>
                @endif
                @endforeach
            </td>
        </tr>
    </table>

    {{-- Tabla principal --}}
    <table>
        <thead>
            <tr>
                <th style="width:16%;">Lead</th>
                <th style="width:13%;">Contacto</th>
                <th style="width:9%;">Estado</th>
                <th style="width:8%;">Prioridad</th>
                <th style="width:9%;">Fuente</th>
                <th style="width:10%;">Agente</th>
                <th style="width:9%;">Seg. próximo</th>
                <th style="width:10%;">Última actividad</th>
                <th style="width:10%;">Presupuesto</th>
                <th style="width:6%;">Creado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leads as $lead)
            @php $lastAct = $lead->activities->first(); @endphp
            <tr>
                <td>
                    <strong>{{ $lead->name }}</strong>
                    @if($lead->property)
                        <br><span style="color:#888; font-size:7.5px;">{{ \Illuminate\Support\Str::limit($lead->property->title, 20) }}</span>
                    @elseif($lead->vehicle)
                        <br><span style="color:#888; font-size:7.5px;">{{ \Illuminate\Support\Str::limit($lead->vehicle->name ?? '', 20) }}</span>
                    @endif
                </td>
                <td>
                    {{ $lead->phone ?: '' }}
                    @if($lead->phone && $lead->email)<br>@endif
                    {{ $lead->email ? \Illuminate\Support\Str::limit($lead->email, 18) : '' }}
                </td>
                <td><span class="badge badge-{{ $lead->status_color }}">{{ $lead->status_label }}</span></td>
                <td><span class="badge badge-{{ $lead->priority_color }}">{{ $lead->priority_label }}</span></td>
                <td>{{ $lead->source_label }}</td>
                <td>{{ $lead->user->name ?? '—' }}</td>
                <td style="{{ $lead->isOverdueForFollowUp() ? 'color:#dc3545;font-weight:bold;' : '' }}">
                    {{ $lead->next_follow_up?->format('d/m/Y') ?? '—' }}
                </td>
                <td>
                    @if($lastAct)
                        {{ $lastAct->type_label }}<br>
                        <span style="color:#888;">{{ $lastAct->activity_at->format('d/m/Y') }}</span>
                    @else
                        <span style="color:#aaa;">Sin actividad</span>
                    @endif
                </td>
                <td>{{ $lead->budget_range }}</td>
                <td>{{ $lead->created_at->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align:center; color:#aaa; padding:14px;">Sin leads con los filtros aplicados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table width="100%" style="border-top:1px solid #dee2e6; margin-top:8px; padding-top:6px;">
        <tr>
            <td style="font-size:7.5px; color:#aaa; border:none;">{{ $company->name }} · Reporte General de Leads · {{ $printDate }}</td>
            <td style="font-size:7.5px; color:#aaa; text-align:right; border:none;">Total: {{ $leads->count() }} leads · Confidencial</td>
        </tr>
    </table>

</div>
</body>
</html>
