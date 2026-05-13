<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Pipeline CRM</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Helvetica','Arial',sans-serif; font-size:9px; color:#333; background:#fff; line-height:1.4; }
.page { padding:24px 28px; }
h3 { font-size:13px; margin-bottom:6px; color:#2c3e50; }
h4 { font-size:10px; font-weight:bold; color:#fff; padding:5px 10px; margin-bottom:0; }
.badge { display:inline-block; padding:2px 7px; border-radius:3px; font-size:8px; font-weight:bold; }
.badge-info     { background:#17a2b8; color:#fff; }
.badge-success  { background:#28a745; color:#fff; }
.badge-warning  { background:#ffc107; color:#333; }
.badge-danger   { background:#dc3545; color:#fff; }
.badge-primary  { background:#007bff; color:#fff; }
.badge-secondary{ background:#6c757d; color:#fff; }
.badge-dark     { background:#343a40; color:#fff; }
table { width:100%; border-collapse:collapse; }
th { background:#555; color:#fff; padding:4px 7px; font-size:8px; text-align:left; }
td { padding:4px 7px; border-bottom:1px solid #f0f0f0; font-size:8px; vertical-align:top; }
tr:nth-child(even) td { background:#fafafa; }
.status-header { border-radius:3px 3px 0 0; margin-bottom:0; }
.status-block  { border:1px solid #dee2e6; border-radius:4px; margin-bottom:16px; page-break-inside:avoid; }
.status-summary { padding:4px 10px; background:#f8f9fa; font-size:8px; color:#666; border-top:1px solid #e9ecef; }
.summary-row { background:#2c3e50; color:#fff; }
.summary-row td { color:#fff; font-weight:bold; border:none; }
</style>
</head>
<body>
<div class="page">

    @php $reportTitle = 'Pipeline CRM'; @endphp
    @include('admin.crm.reports.pdf._header')

    {{-- Resumen global --}}
    @php
        $statusColors = [
            'new'         => '#17a2b8',
            'contacted'   => '#007bff',
            'qualified'   => '#6f42c1',
            'proposal'    => '#fd7e14',
            'negotiation' => '#ffc107',
            'won'         => '#28a745',
            'lost'        => '#dc3545',
        ];
    @endphp

    <table style="margin-bottom:16px; border:1px solid #dee2e6;">
        <thead>
            <tr class="summary-row">
                <td colspan="4" style="padding:6px 10px; font-size:10px;">Resumen del Pipeline</td>
            </tr>
            <tr>
                <th>Etapa</th>
                <th style="text-align:center;">Leads</th>
                <th style="text-align:right;">Presupuesto mín.</th>
                <th style="text-align:right;">Presupuesto máx.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statuses as $key => $label)
            @php $group = $byStatus->get($key, collect()); @endphp
            <tr>
                <td>
                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $statusColors[$key] ?? '#999' }};margin-right:4px;"></span>
                    {{ $label }}
                </td>
                <td style="text-align:center;">{{ $group->count() }}</td>
                <td style="text-align:right;">
                    @if($group->sum('budget_min') > 0)
                        {{ number_format($group->sum('budget_min'), 0, ',', '.') }}
                    @else —
                    @endif
                </td>
                <td style="text-align:right;">
                    @if($group->sum('budget_max') > 0)
                        {{ number_format($group->sum('budget_max'), 0, ',', '.') }}
                    @else —
                    @endif
                </td>
            </tr>
            @endforeach
            <tr style="font-weight:bold; background:#f1f3f4;">
                <td>Total</td>
                <td style="text-align:center;">{{ $leads->count() }}</td>
                <td style="text-align:right;">{{ $leads->sum('budget_min') > 0 ? number_format($leads->sum('budget_min'), 0, ',', '.') : '—' }}</td>
                <td style="text-align:right;">{{ $leads->sum('budget_max') > 0 ? number_format($leads->sum('budget_max'), 0, ',', '.') : '—' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Detalle por etapa --}}
    @foreach($statuses as $key => $label)
    @php $group = $byStatus->get($key, collect()); @endphp
    @if($group->count() > 0)
    <div class="status-block">
        <h4 style="background:{{ $statusColors[$key] ?? '#555' }};">
            {{ $label }}
            <span style="float:right; font-size:10px;">{{ $group->count() }} lead{{ $group->count() !== 1 ? 's' : '' }}</span>
        </h4>
        <table>
            <thead>
                <tr>
                    <th style="width:22%;">Lead</th>
                    <th style="width:14%;">Contacto</th>
                    <th style="width:10%;">Fuente</th>
                    <th style="width:8%;">Prioridad</th>
                    <th style="width:12%;">Agente</th>
                    <th style="width:10%;">Seg. próximo</th>
                    <th style="width:12%;">Presupuesto</th>
                    <th style="width:12%;">Última actividad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group as $lead)
                <tr>
                    <td><strong>{{ $lead->name }}</strong></td>
                    <td>{{ $lead->phone ?: $lead->email ?: '—' }}</td>
                    <td>{{ $lead->source_label }}</td>
                    <td><span class="badge badge-{{ $lead->priority_color }}">{{ $lead->priority_label }}</span></td>
                    <td>{{ $lead->user->name ?? '—' }}</td>
                    <td style="{{ $lead->isOverdueForFollowUp() ? 'color:#dc3545;font-weight:bold;' : '' }}">
                        {{ $lead->next_follow_up?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td>{{ $lead->budget_range }}</td>
                    <td>{{ $lead->updated_at->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @endforeach

    <table width="100%" style="border-top:1px solid #dee2e6; margin-top:10px; padding-top:6px;">
        <tr>
            <td style="font-size:8px; color:#aaa; border:none;">{{ $company->name }} · Pipeline CRM · {{ $printDate }}</td>
            <td style="font-size:8px; color:#aaa; text-align:right; border:none;">Confidencial</td>
        </tr>
    </table>

</div>
</body>
</html>
