<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Lead: {{ $lead->name }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Helvetica','Arial',sans-serif; font-size:10px; color:#333; background:#fff; line-height:1.4; }
.page { padding:28px 32px; }
h3 { font-size:13px; margin-bottom:8px; color:#2c3e50; }
h4 { font-size:11px; margin-bottom:6px; color:#2c3e50; border-bottom:1px solid #dee2e6; padding-bottom:4px; }
.badge { display:inline-block; padding:2px 7px; border-radius:3px; font-size:9px; font-weight:bold; }
.badge-info     { background:#17a2b8; color:#fff; }
.badge-success  { background:#28a745; color:#fff; }
.badge-warning  { background:#ffc107; color:#333; }
.badge-danger   { background:#dc3545; color:#fff; }
.badge-primary  { background:#007bff; color:#fff; }
.badge-secondary{ background:#6c757d; color:#fff; }
.badge-dark     { background:#343a40; color:#fff; }
table { width:100%; border-collapse:collapse; margin-bottom:14px; }
th { background:#2c3e50; color:#fff; padding:5px 8px; font-size:9px; text-align:left; }
td { padding:5px 8px; border-bottom:1px solid #f0f0f0; font-size:9px; vertical-align:top; }
tr:nth-child(even) td { background:#f8f9fa; }
.section { margin-bottom:18px; }
.two-col { display:table; width:100%; }
.col-left  { display:table-cell; width:48%; padding-right:10px; vertical-align:top; }
.col-right { display:table-cell; width:48%; padding-left:10px; vertical-align:top; }
.timeline-item { border-left:2px solid #dee2e6; padding:0 0 10px 10px; margin-bottom:2px; }
.tl-dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:4px; }
.box { border:1px solid #e9ecef; border-radius:4px; padding:8px 10px; margin-bottom:8px; background:#fafafa; }
</style>
</head>
<body>
<div class="page">

    @php $reportTitle = 'Reporte de Lead'; @endphp
    @include('admin.crm.reports.pdf._header')

    {{-- Nombre y estado del lead --}}
    <table style="margin-bottom:16px;">
        <tr>
            <td style="width:70%; border:none;">
                <h3 style="margin-bottom:4px;">{{ $lead->name }}</h3>
                <span class="badge badge-{{ $lead->status_color }}">{{ $lead->status_label }}</span>
                <span class="badge badge-{{ $lead->priority_color }}" style="margin-left:4px;">{{ $lead->priority_label }}</span>
            </td>
            <td style="width:30%; text-align:right; border:none; vertical-align:top;">
                <span style="font-size:9px; color:#888;">
                    Creado: {{ $lead->created_at->format('d/m/Y H:i') }}<br>
                    Agente: {{ $lead->user->name ?? 'Sin asignar' }}
                </span>
            </td>
        </tr>
    </table>

    {{-- Datos generales + Detalles --}}
    <div class="two-col">
        <div class="col-left">
            <h4>Datos de Contacto</h4>
            <table>
                <tr><td style="color:#666; width:40%;">Teléfono</td><td>{{ $lead->phone ?: '—' }}</td></tr>
                <tr><td style="color:#666;">Email</td><td>{{ $lead->email ?: '—' }}</td></tr>
                <tr><td style="color:#666;">WhatsApp</td><td>{{ $lead->whatsapp ?: '—' }}</td></tr>
            </table>

            <h4>Información General</h4>
            <table>
                <tr><td style="color:#666; width:40%;">Fuente</td><td>{{ $lead->source_label }}</td></tr>
                <tr><td style="color:#666;">Interés</td><td>{{ $lead->interest_type_label }}</td></tr>
                <tr><td style="color:#666;">Presupuesto</td><td>{{ $lead->budget_range }}</td></tr>
                @if($lead->property)
                <tr><td style="color:#666;">Propiedad</td><td>{{ $lead->property->title }}</td></tr>
                @endif
                @if($lead->vehicle)
                <tr><td style="color:#666;">Vehículo</td><td>{{ $lead->vehicle->name ?? '—' }}</td></tr>
                @endif
                <tr><td style="color:#666;">Primer contacto</td><td>{{ $lead->first_contact_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                <tr><td style="color:#666;">Último contacto</td><td>{{ $lead->last_contact_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                @if($lead->converted_at)
                <tr><td style="color:#666;">Convertido</td><td>{{ $lead->converted_at->format('d/m/Y') }}</td></tr>
                @endif
                @if($lead->next_follow_up)
                <tr><td style="color:#666;">Próx. seguim.</td><td style="{{ $lead->isOverdueForFollowUp() ? 'color:#dc3545;font-weight:bold;' : '' }}">{{ $lead->next_follow_up->format('d/m/Y') }}</td></tr>
                @endif
                @if($lead->event_name)
                <tr><td style="color:#666;">Evento</td><td>{{ $lead->event_name }}</td></tr>
                @endif
            </table>
        </div>
        <div class="col-right">
            @if($lead->requirements)
            <h4>Lo que busca</h4>
            <div class="box"><span style="font-size:9px;">{{ $lead->requirements }}</span></div>
            @endif
            @if($lead->notes)
            <h4>Notas</h4>
            <div class="box"><span style="font-size:9px;">{{ $lead->notes }}</span></div>
            @endif

            {{-- Citas --}}
            @if($lead->appointments->count() > 0)
            <h4>Citas ({{ $lead->appointments->count() }})</h4>
            @foreach($lead->appointments as $apt)
            <div class="box" style="margin-bottom:5px;">
                <strong>{{ $apt->title }}</strong>
                <span class="badge badge-{{ $apt->status_color }}" style="float:right;">{{ $apt->status_label }}</span>
                <br><span style="color:#666;">{{ $apt->starts_at->format('d/m/Y H:i') }}</span>
                @if($apt->location)<span style="color:#888;"> · {{ $apt->location }}</span>@endif
                <br><span style="color:#888;font-size:8px;">{{ $apt->type_label }}</span>
            </div>
            @endforeach
            @endif

            {{-- Recordatorios --}}
            @if($lead->reminders->count() > 0)
            <h4>Recordatorios ({{ $lead->reminders->count() }})</h4>
            @foreach($lead->reminders as $rem)
            <div class="box" style="margin-bottom:5px;">
                <strong>{{ $rem->title }}</strong>
                <span class="badge badge-{{ $rem->priority === 'urgent' ? 'danger' : ($rem->priority === 'high' ? 'warning' : 'secondary') }}" style="float:right;">{{ ucfirst($rem->priority) }}</span>
                <br><span style="color:#666;">{{ $rem->remind_at->format('d/m/Y H:i') }}</span>
                @if($rem->is_completed)<span style="color:#28a745;"> ✓ Completado</span>@endif
            </div>
            @endforeach
            @endif
        </div>
    </div>

    {{-- Historial de actividades --}}
    @if($lead->activities->count() > 0)
    <div class="section">
        <h4>Historial de Actividades ({{ $lead->activities->count() }})</h4>
        <table>
            <thead>
                <tr>
                    <th style="width:13%;">Fecha</th>
                    <th style="width:12%;">Tipo</th>
                    <th style="width:25%;">Asunto</th>
                    <th>Descripción</th>
                    <th style="width:15%;">Agente</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lead->activities as $act)
                <tr>
                    <td>{{ $act->activity_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($act->type === 'status_change')
                            <span class="badge badge-secondary">Cambio estado</span>
                        @else
                            {{ $act->type_label }}
                        @endif
                    </td>
                    <td>{{ $act->subject ?: '—' }}</td>
                    <td>
                        {{ $act->description ?: '' }}
                        @if($act->type === 'status_change')
                            {{ \App\Lead::getStatuses()[$act->old_status] ?? $act->old_status }} → {{ \App\Lead::getStatuses()[$act->new_status] ?? $act->new_status }}
                        @endif
                        @if($act->call_result)
                            <span style="color:#888;">({{ $act->call_result_label }})</span>
                        @endif
                    </td>
                    <td>{{ $act->user->name ?? 'Sistema' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p style="color:#888; font-size:9px; text-align:center; padding:10px;">Sin actividades registradas.</p>
    @endif

    {{-- Pie de página --}}
    <table width="100%" style="border-top:1px solid #dee2e6; margin-top:10px; padding-top:6px;">
        <tr>
            <td style="font-size:8px; color:#aaa; border:none;">{{ $company->name }} · CRM Report · {{ $printDate }}</td>
            <td style="font-size:8px; color:#aaa; text-align:right; border:none;">Confidencial</td>
        </tr>
    </table>

</div>
</body>
</html>
