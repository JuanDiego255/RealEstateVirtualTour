@extends('admin.main')
@section('title', 'Secuencias de seguimiento')
@section('content')
@include('admin.crm._ui')

<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-random"></i> Secuencias de seguimiento</h2>
            <p class="sub">Nurturing automático de leads</p>
        </div>
        <a href="{{ route('admin.crm.followups.create') }}" class="action-btn primary"><i class="fa fa-plus"></i> Nueva secuencia</a>
    </div>

    @if(session('success'))<div class="crm-alert success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="crm-alert danger">{{ session('error') }}</div>@endif

    <div class="crm-alert info">
        Las secuencias inscriben a cada lead nuevo y le envían mensajes con demoras.
        En WhatsApp, fuera de la ventana de 24&nbsp;h no se envía texto libre: se deja una tarea al asesor.
    </div>

    <div class="crm-section">
        <div class="crm-section-body">
            <div class="crm-table-wrap">
                <table class="crm-table">
                    <thead><tr>
                        <th>Nombre</th><th>Disparo</th><th class="num">Pasos</th><th class="num">Inscritos</th><th>Estado</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse($sequences as $seq)
                            <tr>
                                <td style="font-weight:600;">{{ $seq->name }}</td>
                                <td class="muted">{{ $seq->trigger === 'lead_created' ? 'Al crear el lead' : 'Manual' }}</td>
                                <td class="num">{{ $seq->steps_count }}</td>
                                <td class="num">{{ $seq->enrollments_count }}</td>
                                <td><span class="crm-badge {{ $seq->is_active ? 'green' : 'slate' }}">{{ $seq->is_active ? 'Activa' : 'Inactiva' }}</span></td>
                                <td class="num">
                                    <div style="display:inline-flex; gap:6px;">
                                        <a href="{{ route('admin.crm.followups.edit', $seq) }}" class="action-btn secondary xs">Editar</a>
                                        <form method="POST" action="{{ route('admin.crm.followups.toggle', $seq) }}">@csrf
                                            <button class="action-btn secondary xs">{{ $seq->is_active ? 'Desactivar' : 'Activar' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.crm.followups.destroy', $seq) }}" onsubmit="return confirm('¿Eliminar esta secuencia?')">@csrf @method('DELETE')
                                            <button class="action-btn danger xs">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="empty-state"><i class="fa fa-random"></i>Todavía no hay secuencias. Creá la primera.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
