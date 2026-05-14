@forelse($leads as $lead)
    <tr class="{{ $lead->isOverdueForFollowUp() ? 'overdue-row' : '' }}"
        data-lead-id="{{ $lead->id }}"
        data-lead-name="{{ $lead->name }}"
        data-lead-status="{{ $lead->status }}"
        data-lead-status-label="{{ $lead->status_label }}"
        data-lead-status-color="{{ $lead->status_color }}"
        data-lead-email="{{ $lead->email }}"
        data-lead-phone="{{ $lead->phone }}"
        data-lead-whatsapp="{{ $lead->whatsapp }}"
        data-lead-agent="{{ $lead->user->name ?? '' }}"
        data-lead-priority="{{ $lead->priority_label }}"
        data-lead-source="{{ $lead->source_label }}"
        data-quick-status-url="{{ route('admin.crm.leads.quick-status', $lead) }}"
        data-quick-activity-url="{{ route('admin.crm.leads.quick-activity', $lead) }}"
        data-quick-reminder-url="{{ route('admin.crm.leads.quick-reminder', $lead) }}"
        data-show-url="{{ route('admin.crm.leads.show', $lead) }}"
        data-edit-url="{{ route('admin.crm.leads.edit', $lead) }}"
        data-appointment-url="{{ route('admin.crm.appointments.create', ['lead_id' => $lead->id]) }}">

        {{-- Lead --}}
        <td>
            <a href="{{ route('admin.crm.leads.show', $lead) }}" style="font-weight:600; color:#1a1a2e; text-decoration:none;">
                {{ $lead->name }}
            </a>
            @if($lead->property)
                <br><span style="font-size:11px; color:#999;"><i class="fa fa-home"></i> {{ Str::limit($lead->property->title, 24) }}</span>
            @endif
            @if($lead->vehicle)
                <br><span style="font-size:11px; color:#999;"><i class="fa fa-car"></i> {{ Str::limit($lead->vehicle->name, 24) }}</span>
            @endif
        </td>

        {{-- Contacto --}}
        <td>
            @if($lead->phone)
                <span style="font-size:12px; color:#555;"><i class="fa fa-phone" style="color:#aaa;"></i> {{ $lead->phone }}</span><br>
            @endif
            @if($lead->email)
                <span style="font-size:12px; color:#555;"><i class="fa fa-envelope" style="color:#aaa;"></i> {{ Str::limit($lead->email, 22) }}</span>
            @endif
            @if($lead->whatsapp)
                <br><span style="font-size:12px; color:#25d366;"><i class="fa fa-whatsapp"></i> {{ $lead->whatsapp }}</span>
            @endif
        </td>

        {{-- Estado --}}
        <td>
            <span class="crm-badge {{ $lead->status }} lead-status-badge" id="status-badge-{{ $lead->id }}">
                {{ $lead->status_label }}
            </span>
        </td>

        {{-- Fuente --}}
        <td>
            @if(in_array($lead->source, ['event', 'kiosk', 'qr', 'quote', 'walk_in']))
                <span class="crm-badge proposal"><i class="fa fa-calendar"></i> {{ $lead->source_label }}</span>
                @if($lead->event_name)
                    <br><span style="font-size:11px; color:#999;">{{ Str::limit($lead->event_name, 18) }}</span>
                @endif
            @else
                <span class="crm-badge contacted">{{ $lead->source_label }}</span>
            @endif
        </td>

        {{-- Prioridad --}}
        <td><span class="crm-badge {{ $lead->priority }}">{{ $lead->priority_label }}</span></td>

        {{-- Agente --}}
        <td><span style="font-size:12.5px; color:#555;">{{ $lead->user->name ?? '-' }}</span></td>

        {{-- Seguimiento --}}
        <td>
            @if($lead->next_follow_up)
                @if($lead->isOverdueForFollowUp())
                    <span class="followup-date overdue"><i class="fa fa-exclamation-circle"></i> {{ $lead->next_follow_up->format('d/m/Y') }}</span>
                @elseif($lead->needsFollowUpToday())
                    <span class="followup-date today"><i class="fa fa-clock-o"></i> Hoy</span>
                @else
                    <span class="followup-date ok">{{ $lead->next_follow_up->format('d/m/Y') }}</span>
                @endif
            @else
                <span style="color:#ccc; font-size:13px;">—</span>
            @endif
        </td>

        {{-- Acciones rápidas --}}
        <td>
            <div style="display:flex; align-items:center; gap:4px; flex-wrap:nowrap;">
                <a href="{{ route('admin.crm.leads.show', $lead) }}" class="action-btn view" title="Ver detalle">
                    <i class="fa fa-eye"></i>
                </a>
                <button class="action-btn status btn-quick-status" data-lead-id="{{ $lead->id }}" title="Cambiar estado">
                    <i class="fa fa-exchange"></i>
                </button>
                <button class="action-btn activity btn-quick-activity" data-lead-id="{{ $lead->id }}" title="Actividad">
                    <i class="fa fa-plus"></i>
                </button>
                <button class="action-btn reminder btn-quick-reminder" data-lead-id="{{ $lead->id }}" title="Recordatorio">
                    <i class="fa fa-bell"></i>
                </button>
                <div class="dropdown">
                    <button class="action-btn more dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow-sm" style="min-width:180px; border-radius:10px; border:none; box-shadow:0 4px 20px rgba(0,0,0,.12)!important; font-size:13px;">
                        <h6 class="dropdown-header" style="font-size:11px; color:#aaa; text-transform:uppercase; letter-spacing:.4px;">{{ Str::limit($lead->name, 22) }}</h6>
                        <button class="dropdown-item btn-quick-view" data-lead-id="{{ $lead->id }}" style="display:flex;align-items:center;gap:8px;">
                            <i class="fa fa-info-circle" style="color:#94a3b8; width:14px;"></i> Vista rápida
                        </button>
                        <a class="dropdown-item" href="{{ route('admin.crm.leads.edit', $lead) }}" style="display:flex;align-items:center;gap:8px;">
                            <i class="fa fa-edit" style="color:#94a3b8; width:14px;"></i> Editar lead
                        </a>
                        <a class="dropdown-item" href="{{ route('admin.crm.appointments.create', ['lead_id' => $lead->id]) }}" style="display:flex;align-items:center;gap:8px;">
                            <i class="fa fa-calendar-plus-o" style="color:#94a3b8; width:14px;"></i> Agendar cita
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('admin.crm.reports.lead-detail', $lead) }}" target="_blank" style="display:flex;align-items:center;gap:8px;">
                            <i class="fa fa-file-pdf-o" style="color:#ef4444; width:14px;"></i> Ver PDF
                        </a>
                    </div>
                </div>
            </div>
        </td>
    </tr>
@empty
    <tr id="empty-row">
        <td colspan="8" style="text-align:center; color:#ccc; padding:48px 20px;">
            <i class="fa fa-search" style="font-size:32px; display:block; margin-bottom:10px;"></i>
            <span style="font-size:14px; color:#aaa;">No hay leads que coincidan con los filtros aplicados</span>
        </td>
    </tr>
@endforelse
