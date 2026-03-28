@extends('admin.main')
@section('title', 'CRM - Seguimientos Pendientes')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-clock-o"></i> Seguimientos Pendientes</h4>
            <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Volver a Leads
            </a>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th>Prioridad</th>
                            <th>Fecha Seguimiento</th>
                            <th>Último Contacto</th>
                            <th>Agente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr class="{{ $lead->isOverdueForFollowUp() ? 'table-danger' : ($lead->needsFollowUpToday() ? 'table-warning' : '') }}">
                                <td>
                                    <strong>{{ $lead->name }}</strong>
                                    @if($lead->property)
                                        <br><small class="text-muted"><i class="fa fa-home"></i> {{ Str::limit($lead->property->title, 20) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($lead->phone)
                                        <a href="tel:{{ $lead->phone }}"><i class="fa fa-phone"></i> {{ $lead->phone }}</a><br>
                                    @endif
                                    @if($lead->whatsapp)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp) }}" target="_blank" class="text-success">
                                            <i class="fa fa-whatsapp"></i> WhatsApp
                                        </a>
                                    @endif
                                </td>
                                <td><span class="badge badge-{{ $lead->status_color }}">{{ $lead->status_label }}</span></td>
                                <td><span class="badge badge-{{ $lead->priority_color }}">{{ $lead->priority_label }}</span></td>
                                <td>
                                    @if($lead->isOverdueForFollowUp())
                                        <span class="text-danger font-weight-bold">
                                            <i class="fa fa-exclamation-triangle"></i>
                                            {{ $lead->next_follow_up->format('d/m/Y') }}
                                            <br><small>{{ $lead->next_follow_up->diffForHumans() }}</small>
                                        </span>
                                    @elseif($lead->needsFollowUpToday())
                                        <span class="text-warning font-weight-bold">
                                            <i class="fa fa-clock-o"></i> Hoy
                                        </span>
                                    @else
                                        {{ $lead->next_follow_up->format('d/m/Y') }}
                                    @endif
                                </td>
                                <td>
                                    @if($lead->last_contact_at)
                                        {{ $lead->last_contact_at->format('d/m/Y') }}
                                        <br><small class="text-muted">{{ $lead->last_contact_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $lead->user->name ?? '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.crm.leads.show', $lead) }}" class="btn btn-outline-info" title="Ver">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.crm.appointments.create', ['lead_id' => $lead->id]) }}" class="btn btn-outline-success" title="Programar Cita">
                                            <i class="fa fa-calendar-plus-o"></i>
                                        </a>
                                        @if($lead->phone)
                                        <a href="tel:{{ $lead->phone }}" class="btn btn-outline-primary" title="Llamar">
                                            <i class="fa fa-phone"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                                    <p>No hay seguimientos pendientes</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $leads->links() }}
            </div>
        </div>
    </div>
@endsection
