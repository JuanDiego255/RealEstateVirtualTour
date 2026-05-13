<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Reporte de Citas</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Helvetica','Arial',sans-serif; font-size:9px; color:#333; background:#fff; line-height:1.4; }
.page { padding:24px 28px; }
.badge { display:inline-block; padding:2px 6px; border-radius:3px; font-size:8px; font-weight:bold; }
.badge-success  { background:#28a745; color:#fff; }
.badge-warning  { background:#ffc107; color:#333; }
.badge-danger   { background:#dc3545; color:#fff; }
.badge-primary  { background:#007bff; color:#fff; }
.badge-secondary{ background:#6c757d; color:#fff; }
.badge-info     { background:#17a2b8; color:#fff; }
.badge-dark     { background:#343a40; color:#fff; }
table { width:100%; border-collapse:collapse; margin-bottom:10px; }
th { background:#28a745; color:#fff; padding:5px 8px; font-size:8px; text-align:left; }
td { padding:5px 8px; border-bottom:1px solid #f0f0f0; font-size:8px; vertical-align:top; }
tr:nth-child(even) td { background:#f9f9f9; }
</style>
</head>
<body>
<div class="page">

    @php $reportTitle = 'Reporte de Citas'; @endphp
    @include('admin.crm.reports.pdf._header')

    {{-- Resumen --}}
    @php
        $byStatus = $appointments->groupBy('status');
        $statuses = \App\Appointment::getStatuses();
    @endphp
    <table style="margin-bottom:14px; border:1px solid #e9ecef;">
        <tr>
            <td style="background:#f8f9fa; padding:6px 10px; border:none;">
                <strong style="font-size:9px;">Resumen:</strong>
                <span style="font-size:9px; margin-left:8px;">Total: <strong>{{ $appointments->count() }}</strong></span>
                @foreach($statuses as $key => $label)
                @php $cnt = $byStatus->get($key, collect())->count(); @endphp
                @if($cnt > 0)
                    <span style="font-size:9px; margin-left:8px;">{{ $label }}: <strong>{{ $cnt }}</strong></span>
                @endif
                @endforeach
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width:18%;">Título</th>
                <th style="width:10%;">Tipo</th>
                <th style="width:10%;">Estado</th>
                <th style="width:12%;">Lead</th>
                <th style="width:10%;">Agente</th>
                <th style="width:11%;">Inicio</th>
                <th style="width:11%;">Fin</th>
                <th style="width:10%;">Ubicación</th>
                <th style="width:8%;">Resultado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments as $apt)
            <tr>
                <td><strong>{{ \Illuminate\Support\Str::limit($apt->title, 28) }}</strong></td>
                <td>{{ $apt->type_label }}</td>
                <td><span class="badge badge-{{ $apt->status_color }}">{{ $apt->status_label }}</span></td>
                <td>{{ $apt->lead?->name ?? ($apt->client_name ?: '—') }}</td>
                <td>{{ $apt->user?->name ?? '—' }}</td>
                <td>{{ $apt->starts_at->format('d/m/Y H:i') }}</td>
                <td>{{ $apt->ends_at?->format('d/m/Y H:i') ?? '—' }}</td>
                <td>{{ $apt->location ? \Illuminate\Support\Str::limit($apt->location, 20) : '—' }}</td>
                <td>
                    @if($apt->outcome)
                        <span class="badge badge-{{ $apt->outcome === 'successful' ? 'success' : ($apt->outcome === 'not_interested' ? 'danger' : 'secondary') }}">
                            {{ \App\Appointment::getOutcomes()[$apt->outcome] ?? $apt->outcome }}
                        </span>
                    @else —
                    @endif
                </td>
            </tr>
            @if($apt->outcome_notes)
            <tr>
                <td colspan="9" style="color:#666; background:#fffde7; font-style:italic; padding:3px 8px;">
                    Notas: {{ $apt->outcome_notes }}
                </td>
            </tr>
            @endif
            @empty
            <tr><td colspan="9" style="text-align:center; color:#aaa; padding:14px;">Sin citas con los filtros aplicados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table width="100%" style="border-top:1px solid #dee2e6; margin-top:8px; padding-top:6px;">
        <tr>
            <td style="font-size:7.5px; color:#aaa; border:none;">{{ $company->name }} · Reporte de Citas · {{ $printDate }}</td>
            <td style="font-size:7.5px; color:#aaa; text-align:right; border:none;">Total: {{ $appointments->count() }} citas · Confidencial</td>
        </tr>
    </table>

</div>
</body>
</html>
