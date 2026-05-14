@extends('admin.main')
@section('title', 'Citas — Lista')
@section('content')

@include('admin.crm.layouts._crm-styles')

@if (Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        <strong>{{ Session::get('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
    </div>
@endif

<div class="crm-page">

    {{-- Header --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-list-alt"></i> Lista de Citas</h2>
            <div class="sub">{{ $appointments->total() }} citas registradas</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.appointments.today') }}" class="action-btn warning">
                <i class="fa fa-sun-o"></i> Hoy
            </a>
            <a href="{{ route('admin.crm.appointments.index') }}" class="action-btn secondary">
                <i class="fa fa-calendar"></i> Calendario
            </a>
            <a href="{{ route('admin.crm.appointments.create') }}?_back={{ urlencode(url()->current()) }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nueva Cita
            </a>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="dashboard-card">
        <div class="dc-header">
            <h5><i class="fa fa-calendar-check-o"></i> Citas</h5>
        </div>
        <div style="overflow-x:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Título / Propiedad</th>
                        <th>Tipo</th>
                        <th>Fecha y Hora</th>
                        <th>Cliente</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Agente</th>
                        <th style="width:110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                    @php
                        $rowClass = ($appointment->isPast() && $appointment->status === 'scheduled') ? 'row-warning' : '';
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td>
                            <a href="{{ route('admin.crm.appointments.show', $appointment) }}"
                               style="font-weight:600; color:#1a1a2e; text-decoration:none; font-size:13.5px;">
                                {{ $appointment->title }}
                            </a>
                            @if($appointment->property)
                                <div style="font-size:11px; color:#888; margin-top:2px;">
                                    <i class="fa fa-home"></i> {{ Str::limit($appointment->property->title, 24) }}
                                </div>
                            @endif
                            @if($appointment->vehicle)
                                <div style="font-size:11px; color:#888; margin-top:2px;">
                                    <i class="fa fa-car"></i> {{ Str::limit($appointment->vehicle->name, 24) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:12.5px; color:#555;">{{ $appointment->type_label }}</span>
                        </td>
                        <td>
                            <div style="font-size:13px; font-weight:600; color:#1a1a2e;">{{ $appointment->starts_at->format('d/m/Y') }}</div>
                            <div style="font-size:11px; color:#aaa;">{{ $appointment->starts_at->format('H:i') }} — {{ $appointment->ends_at->format('H:i') }}</div>
                        </td>
                        <td>
                            <div style="font-size:13px; color:#333;">{{ $appointment->client_display_name }}</div>
                            @if($appointment->client_display_phone)
                                <div style="font-size:11px; color:#888;">{{ $appointment->client_display_phone }}</div>
                            @endif
                        </td>
                        <td>
                            @if($appointment->location)
                                <span style="font-size:12.5px; color:#555;">{{ Str::limit($appointment->location, 28) }}</span>
                            @else
                                <span style="color:#ddd;">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="crm-badge {{ $appointment->status }}">{{ $appointment->status_label }}</span>
                            @if($appointment->isPast() && $appointment->status === 'scheduled')
                                <div style="font-size:10px; color:#d97706; margin-top:3px;"><i class="fa fa-exclamation-circle"></i> Sin confirmar</div>
                            @endif
                        </td>
                        <td><span style="font-size:12.5px; color:#555;">{{ $appointment->user->name ?? '—' }}</span></td>
                        <td>
                            <div style="display:flex; gap:4px;">
                                <a href="{{ route('admin.crm.appointments.show', $appointment) }}" class="action-btn view" title="Ver">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.crm.appointments.edit', $appointment) }}?_back={{ urlencode(url()->current()) }}" class="action-btn" style="background:#e0e7ff;color:#4338ca;padding:5px 9px;font-size:12px;" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </a>
                                @if($appointment->canBeCancelled())
                                <form action="{{ route('admin.crm.appointments.cancel', $appointment) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Cancelar esta cita?');">
                                    @csrf
                                    <button class="action-btn danger" style="padding:5px 9px;font-size:12px;" title="Cancelar">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:48px 20px; color:#ccc;">
                            <i class="fa fa-calendar" style="font-size:32px; display:block; margin-bottom:10px;"></i>
                            <span style="font-size:14px; color:#aaa;">No hay citas registradas</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($appointments->hasPages())
        <div id="pagination-wrap" style="padding:14px 18px; border-top:1px solid #f5f5f5;">
            {{ $appointments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
