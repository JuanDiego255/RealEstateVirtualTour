@extends('admin.main')
@section('title', 'Secuencias de seguimiento')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fa fa-random"></i> Secuencias de seguimiento</h4>
        <a href="{{ route('admin.crm.followups.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nueva secuencia</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="alert alert-info small">
        Las secuencias automáticas inscriben a cada lead nuevo y le envían mensajes con demoras.
        En WhatsApp, fuera de la ventana de 24&nbsp;h no se envía texto libre: se deja una tarea al asesor.
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th>Nombre</th><th>Disparo</th><th class="text-right">Pasos</th><th class="text-right">Inscritos</th><th>Estado</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse($sequences as $seq)
                        <tr>
                            <td><strong>{{ $seq->name }}</strong></td>
                            <td>{{ $seq->trigger === 'lead_created' ? 'Al crear el lead' : 'Manual' }}</td>
                            <td class="text-right">{{ $seq->steps_count }}</td>
                            <td class="text-right">{{ $seq->enrollments_count }}</td>
                            <td>
                                <span class="badge badge-{{ $seq->is_active ? 'success' : 'secondary' }}">{{ $seq->is_active ? 'Activa' : 'Inactiva' }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.crm.followups.edit', $seq) }}" class="btn btn-xs btn-outline-primary">Editar</a>
                                <form method="POST" action="{{ route('admin.crm.followups.toggle', $seq) }}" class="d-inline">@csrf
                                    <button class="btn btn-xs btn-outline-secondary">{{ $seq->is_active ? 'Desactivar' : 'Activar' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.crm.followups.destroy', $seq) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta secuencia?')">@csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Todavía no hay secuencias. Creá la primera.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
