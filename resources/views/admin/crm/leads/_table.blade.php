@forelse($leads as $lead)
    <tr class="{{ $lead->isOverdueForFollowUp() ? 'table-warning' : '' }}"
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
            <a href="{{ route('admin.crm.leads.show', $lead) }}" class="text-dark font-weight-bold text-decoration-none">
                {{ $lead->name }}
            </a>
            @if($lead->property)
                <br><small class="text-muted"><i class="fa fa-home"></i> {{ Str::limit($lead->property->title, 22) }}</small>
            @endif
            @if($lead->vehicle)
                <br><small class="text-muted"><i class="fa fa-car"></i> {{ Str::limit($lead->vehicle->name, 22) }}</small>
            @endif
        </td>
        {{-- Contacto --}}
        <td>
            @if($lead->phone)
                <small><i class="fa fa-phone text-muted"></i> {{ $lead->phone }}</small><br>
            @endif
            @if($lead->email)
                <small><i class="fa fa-envelope text-muted"></i> {{ Str::limit($lead->email, 22) }}</small>
            @endif
            @if($lead->whatsapp)
                <br><small class="text-success"><i class="fa fa-whatsapp"></i> {{ $lead->whatsapp }}</small>
            @endif
        </td>
        {{-- Estado --}}
        <td>
            <span class="badge badge-{{ $lead->status_color }} lead-status-badge" id="status-badge-{{ $lead->id }}">
                {{ $lead->status_label }}
            </span>
        </td>
        {{-- Fuente --}}
        <td>
            @if(in_array($lead->source, ['event', 'kiosk', 'qr', 'quote', 'walk_in']))
                <span class="badge badge-warning"><i class="fa fa-calendar"></i> {{ $lead->source_label }}</span>
                @if($lead->event_name)
                    <br><small class="text-muted">{{ Str::limit($lead->event_name, 18) }}</small>
                @endif
            @else
                <span class="badge badge-info">{{ $lead->source_label }}</span>
            @endif
        </td>
        {{-- Prioridad --}}
        <td><span class="badge badge-{{ $lead->priority_color }}">{{ $lead->priority_label }}</span></td>
        {{-- Agente --}}
        <td><small>{{ $lead->user->name ?? '-' }}</small></td>
        {{-- Seguimiento --}}
        <td>
            @if($lead->next_follow_up)
                @if($lead->isOverdueForFollowUp())
                    <span class="text-danger small"><i class="fa fa-exclamation-circle"></i> {{ $lead->next_follow_up->format('d/m/Y') }}</span>
                @elseif($lead->needsFollowUpToday())
                    <span class="text-warning small"><i class="fa fa-clock-o"></i> Hoy</span>
                @else
                    <small class="text-muted">{{ $lead->next_follow_up->format('d/m/Y') }}</small>
                @endif
            @else
                <small class="text-muted">—</small>
            @endif
        </td>
        {{-- Acciones rápidas --}}
        <td>
            <div class="d-flex align-items-center" style="gap:4px;">
                <a href="{{ route('admin.crm.leads.show', $lead) }}" class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                    <i class="fa fa-eye"></i>
                </a>
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bolt"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow-sm" style="min-width:200px;">
                        <h6 class="dropdown-header small text-uppercase text-muted">{{ $lead->name }}</h6>
                        <button class="dropdown-item btn-quick-status" data-lead-id="{{ $lead->id }}">
                            <i class="fa fa-exchange text-info"></i> Cambiar estado
                        </button>
                        <button class="dropdown-item btn-quick-activity" data-lead-id="{{ $lead->id }}">
                            <i class="fa fa-plus-circle text-success"></i> Registrar actividad
                        </button>
                        <button class="dropdown-item btn-quick-reminder" data-lead-id="{{ $lead->id }}">
                            <i class="fa fa-bell text-warning"></i> Agregar recordatorio
                        </button>
                        <button class="dropdown-item btn-quick-view" data-lead-id="{{ $lead->id }}">
                            <i class="fa fa-info-circle text-secondary"></i> Vista rápida
                        </button>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('admin.crm.leads.edit', $lead) }}">
                            <i class="fa fa-edit text-muted"></i> Editar lead
                        </a>
                        <a class="dropdown-item" href="{{ route('admin.crm.appointments.create', ['lead_id' => $lead->id]) }}">
                            <i class="fa fa-calendar-plus-o text-muted"></i> Agendar cita
                        </a>
                    </div>
                </div>
            </div>
        </td>
    </tr>
@empty
    <tr id="empty-row">
        <td colspan="8" class="text-center text-muted py-5">
            <i class="fa fa-search fa-2x mb-2 d-block"></i>
            No hay leads que coincidan con los filtros aplicados
        </td>
    </tr>
@endforelse
