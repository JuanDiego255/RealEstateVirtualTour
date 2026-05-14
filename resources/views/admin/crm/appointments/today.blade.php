@extends('admin.main')
@section('title', 'Citas de Hoy')
@section('content')

@include('admin.crm.layouts._crm-styles')
<style>
.today-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,.07);
    overflow: hidden;
    transition: transform .15s, box-shadow .15s;
    margin-bottom: 18px;
}
.today-card:hover { transform: translateY(-2px); box-shadow: 0 6px 22px rgba(0,0,0,.11); }
.today-card .tc-accent {
    height: 5px;
}
.today-card .tc-body { padding: 18px; }
.today-card .tc-title { font-size: 15px; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
.today-card .tc-time  { font-size: 13px; color: #888; margin-bottom: 12px; }
.today-card hr { border-color: #f0f0f0; margin: 12px 0; }
.today-card .tc-label { font-size: 11px; color: #aaa; text-transform: uppercase; letter-spacing:.4px; }
.today-card .tc-value { font-size: 13px; color: #333; }
.today-card .tc-footer {
    padding: 12px 16px;
    background: #fafafa;
    border-top: 1px solid #f0f0f0;
    display: flex; gap: 8px; flex-wrap: wrap;
}
</style>

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
            <h2><i class="fa fa-sun-o"></i> Citas de Hoy</h2>
            <div class="sub">{{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }} · {{ $appointments->count() }} cita{{ $appointments->count() != 1 ? 's' : '' }}</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.appointments.index') }}" class="action-btn secondary">
                <i class="fa fa-calendar"></i> Calendario
            </a>
            <a href="{{ route('admin.crm.appointments.create', ['date' => now()->format('Y-m-d')]) }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nueva Cita
            </a>
        </div>
    </div>

    @if($appointments->count() > 0)

    {{-- Resumen del día --}}
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 22px;">
        @php
            $scheduled  = $appointments->where('status', 'scheduled')->count();
            $confirmed  = $appointments->where('status', 'confirmed')->count();
            $completed  = $appointments->where('status', 'completed')->count();
            $cancelled  = $appointments->where('status', 'cancelled')->count();
        @endphp
        <div class="stat-card">
            <div class="sc-icon blue"><i class="fa fa-calendar-o"></i></div>
            <div class="sc-value">{{ $scheduled }}</div>
            <div class="sc-label">Programadas</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon green"><i class="fa fa-check-circle"></i></div>
            <div class="sc-value">{{ $confirmed }}</div>
            <div class="sc-label">Confirmadas</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon purple"><i class="fa fa-flag-checkered"></i></div>
            <div class="sc-value">{{ $completed }}</div>
            <div class="sc-label">Completadas</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon red"><i class="fa fa-times-circle"></i></div>
            <div class="sc-value">{{ $cancelled }}</div>
            <div class="sc-label">Canceladas</div>
        </div>
    </div>

    {{-- Grid de citas --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:18px;">
        @foreach($appointments as $appointment)
        <div class="today-card">
            <div class="tc-accent" style="background:{{ $appointment->calendar_color }};"></div>
            <div class="tc-body">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px;">
                    <div class="tc-title">{{ $appointment->title }}</div>
                    <span class="crm-badge {{ $appointment->status }}" style="flex-shrink:0; margin-left:8px;">{{ $appointment->status_label }}</span>
                </div>
                <div class="tc-time">
                    <i class="fa fa-clock-o" style="color:#aaa;"></i>
                    {{ $appointment->starts_at->format('H:i') }} — {{ $appointment->ends_at->format('H:i') }}
                    &nbsp;·&nbsp; {{ $appointment->type_label }}
                </div>

                <hr>

                <div style="margin-bottom:8px;">
                    <div class="tc-label">Cliente</div>
                    <div class="tc-value" style="font-weight:600;">{{ $appointment->client_display_name }}</div>
                    @if($appointment->client_display_phone)
                        <a href="tel:{{ $appointment->client_display_phone }}" style="font-size:12.5px; color:#555; text-decoration:none;">
                            <i class="fa fa-phone" style="color:#aaa;"></i> {{ $appointment->client_display_phone }}
                        </a>
                    @endif
                </div>

                @if($appointment->location)
                <div style="margin-bottom:8px;">
                    <div class="tc-label">Ubicación</div>
                    <div class="tc-value"><i class="fa fa-map-marker" style="color:#aaa;"></i> {{ $appointment->location }}</div>
                </div>
                @endif

                @if($appointment->property)
                <div style="margin-bottom:4px; font-size:12px; color:#6366f1;">
                    <i class="fa fa-home"></i> {{ Str::limit($appointment->property->title, 32) }}
                </div>
                @endif
                @if($appointment->vehicle)
                <div style="margin-bottom:4px; font-size:12px; color:#6366f1;">
                    <i class="fa fa-car"></i> {{ Str::limit($appointment->vehicle->name, 32) }}
                </div>
                @endif
            </div>
            <div class="tc-footer">
                <a href="{{ route('admin.crm.appointments.show', $appointment) }}" class="action-btn view">
                    <i class="fa fa-eye"></i> Ver
                </a>
                @if($appointment->status === 'scheduled')
                <form action="{{ route('admin.crm.appointments.update', $appointment) }}" method="POST" class="d-inline">
                    @csrf @method('PUT')
                    <input type="hidden" name="title" value="{{ $appointment->title }}">
                    <input type="hidden" name="type" value="{{ $appointment->type }}">
                    <input type="hidden" name="starts_at_date" value="{{ $appointment->starts_at->format('Y-m-d') }}">
                    <input type="hidden" name="starts_at_time" value="{{ $appointment->starts_at->format('H:i') }}">
                    <input type="hidden" name="duration" value="{{ $appointment->duration_in_minutes }}">
                    <input type="hidden" name="status" value="confirmed">
                    <button type="submit" class="action-btn success"><i class="fa fa-check"></i> Confirmar</button>
                </form>
                @endif
                @if($appointment->client_display_phone)
                <a href="tel:{{ $appointment->client_display_phone }}" class="action-btn secondary">
                    <i class="fa fa-phone"></i> Llamar
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @else
    {{-- Empty state --}}
    <div class="dashboard-card">
        <div class="dc-body" style="text-align:center; padding:72px 20px;">
            <div style="width:72px; height:72px; background:rgba(34,197,94,.12); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px; font-size:28px; color:#22c55e;">
                <i class="fa fa-calendar-check-o"></i>
            </div>
            <h5 style="font-size:17px; font-weight:700; color:#1a1a2e; margin-bottom:8px;">Sin citas para hoy</h5>
            <p style="font-size:13px; color:#aaa; margin-bottom:20px;">No tienes citas programadas para este día.</p>
            <a href="{{ route('admin.crm.appointments.create', ['date' => now()->format('Y-m-d')]) }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Programar Cita
            </a>
        </div>
    </div>
    @endif

</div>
@endsection
