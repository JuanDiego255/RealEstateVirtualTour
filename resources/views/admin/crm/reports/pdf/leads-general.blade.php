<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Leads General</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Helvetica','Arial',sans-serif; font-size:9px; color:#333; background:#fff; line-height:1.4; }
.page { padding:24px 28px; }
.badge { display:inline-block; padding:1px 6px; border-radius:3px; font-size:7.5px; font-weight:bold; }
.badge-info      { background:#17a2b8; color:#fff; }
.badge-success   { background:#28a745; color:#fff; }
.badge-warning   { background:#ffc107; color:#333; }
.badge-danger    { background:#dc3545; color:#fff; }
.badge-primary   { background:#007bff; color:#fff; }
.badge-secondary { background:#6c757d; color:#fff; }
.badge-dark      { background:#343a40; color:#fff; }
/* Tabla lead info */
table.lead-info { width:100%; border-collapse:collapse; margin-bottom:0; }
table.lead-info th { background:#2c3e50; color:#fff; padding:4px 7px; font-size:8px; text-align:left; }
table.lead-info td { padding:4px 7px; border-bottom:1px solid #e9ecef; font-size:8.5px; vertical-align:top; }
/* Tabla actividades */
table.activities { width:100%; border-collapse:collapse; margin:0; }
table.activities th { background:#495057; color:#fff; padding:3px 7px; font-size:7.5px; text-align:left; }
table.activities td { padding:3px 7px; border-bottom:1px solid #f0f0f0; font-size:7.5px; vertical-align:top; }
table.activities tr:nth-child(even) td { background:#f9f9f9; }
/* Bloque por lead */
.lead-block { border:1px solid #dee2e6; border-radius:3px; margin-bottom:18px; page-break-inside:avoid; }
.lead-header { background:#e9ecef; padding:5px 10px; }
.lead-header strong { font-size:10px; }
.no-acts { padding:6px 10px; font-size:8px; color:#aaa; font-style:italic; }
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
        $totalActs = $leads->sum(fn($l) => $l->activities->count());
    @endphp
    <table style="margin-bottom:14px; border:1px solid #e9ecef; border-collapse:collapse;">
        <tr>
            <td style="background:#f8f9fa; padding:6px 10px; border:none;">
                <strong>Resumen: </strong>
                <span style="margin-left:6px;">Total leads: <strong>{{ $leads->count() }}</strong></span>
                <span style="margin-left:10px;">Total actividades: <strong>{{ $totalActs }}</strong></span>
                @foreach($statuses as $key => $label)
                    @php $cnt = $byStatus->get($key, collect())->count(); @endphp
                    @if($cnt > 0)
                        <span style="margin-left:10px;">{{ $label }}: <strong>{{ $cnt }}</strong></span>
                    @endif
                @endforeach
            </td>
        </tr>
    </table>

    {{-- Un bloque por lead --}}
    @forelse($leads as $lead)
    <div class="lead-block">

        {{-- Encabezado del lead --}}
        <div class="lead-header">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="border:none; padding:0; width:60%;">
                        <strong>{{ $lead->name }}</strong>
                        <span class="badge badge-{{ $lead->status_color }}" style="margin-left:5px;">{{ $lead->status_label }}</span>
                        <span class="badge badge-{{ $lead->priority_color }}" style="margin-left:3px;">{{ $lead->priority_label }}</span>
                    </td>
                    <td style="border:none; padding:0; text-align:right; font-size:8px; color:#555;">
                        Agente: <strong>{{ $lead->user?->name ?? '—' }}</strong>
                        &nbsp;·&nbsp; Fuente: {{ $lead->source_label }}
                        &nbsp;·&nbsp; Creado: {{ $lead->created_at->format('d/m/Y') }}
                    </td>
                </tr>
                <tr>
                    <td style="border:none; padding:2px 0 0; font-size:8px; color:#555;">
                        @if($lead->phone) Tel: {{ $lead->phone }} @endif
                        @if($lead->phone && $lead->email) &nbsp;·&nbsp; @endif
                        @if($lead->email) Email: {{ $lead->email }} @endif
                        @if($lead->next_follow_up)
                            &nbsp;·&nbsp;
                            <span style="{{ $lead->isOverdueForFollowUp() ? 'color:#dc3545;font-weight:bold;' : '' }}">
                                Próx. seguim.: {{ $lead->next_follow_up->format('d/m/Y') }}
                            </span>
                        @endif
                    </td>
                    <td style="border:none; padding:2px 0 0; text-align:right; font-size:8px; color:#555;">
                        Presupuesto: {{ $lead->budget_range }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- Tabla de actividades --}}
        @if($lead->activities->count() > 0)
        <table class="activities">
            <thead>
                <tr>
                    <th style="width:13%;">Fecha</th>
                    <th style="width:12%;">Tipo</th>
                    <th style="width:22%;">Asunto</th>
                    <th style="width:38%;">Descripción / Detalle</th>
                    <th style="width:15%;">Registrado por</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lead->activities as $act)
                <tr>
                    <td>{{ $act->activity_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($act->type === 'status_change')
                            <span class="badge badge-dark">Cambio estado</span>
                        @else
                            {{ $act->type_label }}
                        @endif
                    </td>
                    <td>{{ $act->subject ?: '—' }}</td>
                    <td>
                        @if($act->type === 'status_change')
                            {{ \App\Lead::getStatuses()[$act->old_status] ?? $act->old_status }}
                            &rarr;
                            {{ \App\Lead::getStatuses()[$act->new_status] ?? $act->new_status }}
                            @if($act->description)
                                <br><span style="color:#666;">{{ $act->description }}</span>
                            @endif
                        @else
                            {{ $act->description ?: '—' }}
                        @endif
                        @if($act->call_result)
                            <br><span style="color:#888;">Llamada: {{ $act->call_result_label }}
                            @if($act->call_duration_formatted) ({{ $act->call_duration_formatted }}) @endif
                            </span>
                        @endif
                    </td>
                    <td>{{ $act->user?->name ?? 'Sistema' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="no-acts">Sin actividades registradas para este lead.</p>
        @endif

    </div>
    @empty
    <p style="text-align:center; color:#aaa; padding:20px;">Sin leads con los filtros aplicados.</p>
    @endforelse

    <table width="100%" style="border-top:1px solid #dee2e6; margin-top:8px; padding-top:6px;">
        <tr>
            <td style="font-size:7.5px; color:#aaa; border:none;">{{ $company->name }} · Reporte General de Leads · {{ $printDate }}</td>
            <td style="font-size:7.5px; color:#aaa; text-align:right; border:none;">{{ $leads->count() }} leads · {{ $totalActs }} actividades · Confidencial</td>
        </tr>
    </table>

</div>
</body>
</html>
