<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Productividad por Agente</title>
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
th { background:#6f42c1; color:#fff; padding:5px 8px; font-size:8px; text-align:left; }
td { padding:5px 8px; border-bottom:1px solid #f0f0f0; font-size:8px; vertical-align:top; }
tr:nth-child(even) td { background:#fafafa; }
.agent-header { background:#343a40; color:#fff; padding:6px 10px; font-size:10px; font-weight:bold; border-radius:3px 3px 0 0; }
.agent-block { border:1px solid #dee2e6; border-radius:4px; margin-bottom:18px; page-break-inside:avoid; }
.agent-stats { display:table; width:100%; background:#f8f9fa; padding:6px 10px; }
.stat-item { display:table-cell; text-align:center; }
.stat-num { font-size:14px; font-weight:bold; color:#2c3e50; }
.stat-lbl { font-size:7.5px; color:#888; display:block; }
</style>
</head>
<body>
<div class="page">

    @php $reportTitle = 'Productividad por Agente'; @endphp
    @include('admin.crm.reports.pdf._header')

    {{-- Resumen global --}}
    <table style="margin-bottom:16px; border:1px solid #dee2e6;">
        <thead>
            <tr>
                <th style="background:#6f42c1;">Agente</th>
                <th style="background:#6f42c1; text-align:center;">Total Leads</th>
                <th style="background:#6f42c1; text-align:center;">Activos</th>
                <th style="background:#6f42c1; text-align:center;">Ganados</th>
                <th style="background:#6f42c1; text-align:center;">Perdidos</th>
                <th style="background:#6f42c1; text-align:center;">Conversión %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agentData as $data)
            <tr>
                <td><strong>{{ $data['agent']->name }}</strong></td>
                <td style="text-align:center;">{{ $data['total'] }}</td>
                <td style="text-align:center;">{{ $data['active'] }}</td>
                <td style="text-align:center; color:#28a745; font-weight:bold;">{{ $data['won'] }}</td>
                <td style="text-align:center; color:#dc3545;">{{ $data['lost'] }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $data['conv_rate'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Detalle por agente --}}
    @foreach($agentData as $data)
    <div class="agent-block">
        <div class="agent-header">
            {{ $data['agent']->name }}
            <span style="float:right; font-size:9px; font-weight:normal;">
                {{ $data['total'] }} leads · {{ $data['won'] }} ganados · {{ $data['conv_rate'] }}% conversión
            </span>
        </div>
        @if($data['leads']->count() > 0)
        <table>
            <thead>
                <tr style="background:#555;">
                    <th>Lead</th>
                    <th>Estado</th>
                    <th>Fuente</th>
                    <th>Prioridad</th>
                    <th>Creado</th>
                    <th>Presupuesto</th>
                    <th>Próx. seguim.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['leads']->take(30) as $lead)
                <tr>
                    <td>
                        <strong>{{ $lead->name }}</strong>
                        @if($lead->phone)<br><span style="color:#888;">{{ $lead->phone }}</span>@endif
                    </td>
                    <td><span class="badge badge-{{ $lead->status_color }}">{{ $lead->status_label }}</span></td>
                    <td>{{ $lead->source_label }}</td>
                    <td><span class="badge badge-{{ $lead->priority_color }}">{{ $lead->priority_label }}</span></td>
                    <td>{{ $lead->created_at->format('d/m/Y') }}</td>
                    <td>{{ $lead->budget_range }}</td>
                    <td style="{{ $lead->isOverdueForFollowUp() ? 'color:#dc3545;font-weight:bold;' : '' }}">
                        {{ $lead->next_follow_up?->format('d/m/Y') ?? '—' }}
                    </td>
                </tr>
                @endforeach
                @if($data['leads']->count() > 30)
                <tr>
                    <td colspan="7" style="text-align:center; color:#888; font-style:italic;">
                        + {{ $data['leads']->count() - 30 }} leads adicionales no mostrados
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
        @else
        <p style="color:#aaa; text-align:center; padding:10px;">Sin leads asignados.</p>
        @endif
    </div>
    @endforeach

    <table width="100%" style="border-top:1px solid #dee2e6; margin-top:8px; padding-top:6px;">
        <tr>
            <td style="font-size:7.5px; color:#aaa; border:none;">{{ $company->name }} · Productividad por Agente · {{ $printDate }}</td>
            <td style="font-size:7.5px; color:#aaa; text-align:right; border:none;">{{ $agentData->count() }} agentes · Confidencial</td>
        </tr>
    </table>

</div>
</body>
</html>
