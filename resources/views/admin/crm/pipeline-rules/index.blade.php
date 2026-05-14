@extends('admin.main')
@section('title', 'Reglas de Pipeline')
@section('content')
@include('admin.crm.layouts._crm-styles')
<div class="crm-page">
    <div class="crm-page-header">
        <div><h2><i class="fa fa-cogs"></i> Reglas de Pipeline</h2></div>
        <div class="actions">
            <a href="{{ route('admin.crm.pipeline-rules.create') }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nueva Regla
            </a>
        </div>
    </div>
    @if(Session::has('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:12px;padding:12px 18px;margin-bottom:16px;color:#065f46;font-size:13.5px;">
        <i class="fa fa-check-circle" style="color:#22c55e;"></i> {{ Session::get('success') }}
    </div>
    @endif
    <div class="dashboard-card">
        <div class="dc-header"><h5><i class="fa fa-cogs" style="color:#c2ac1f;"></i> Reglas Configuradas ({{ $rules->count() }})</h5></div>
        @if($rules->isEmpty())
        <div class="dc-body" style="text-align:center;padding:48px;">
            <i class="fa fa-cogs" style="font-size:36px;opacity:.3;display:block;margin-bottom:10px;"></i>
            <p style="color:#aaa;">No hay reglas configuradas. <a href="{{ route('admin.crm.pipeline-rules.create') }}">Crear primera regla</a>.</p>
        </div>
        @else
        <div style="overflow-x:auto;">
            <table class="crm-table">
                <thead>
                    <tr><th>Nombre</th><th>Etapa</th><th>Días inactivo</th><th>Acción</th><th style="text-align:center;">Estado</th><th></th></tr>
                </thead>
                <tbody>
                @foreach($rules as $rule)
                <tr>
                    <td style="font-weight:600;color:#1a1a2e;">{{ $rule->name }}</td>
                    <td><span class="crm-badge {{ $rule->trigger_stage }}">{{ $statuses[$rule->trigger_stage] ?? $rule->trigger_stage }}</span></td>
                    <td style="text-align:center;"><strong>{{ $rule->days_inactive }}</strong> días</td>
                    <td style="font-size:13px;color:#555;">{{ $actions[$rule->action] ?? $rule->action }}</td>
                    <td style="text-align:center;">
                        <button class="btn-toggle-rule action-btn {{ $rule->is_active ? 'status' : 'secondary' }}"
                                data-id="{{ $rule->id }}"
                                data-url="{{ route('admin.crm.pipeline-rules.toggle', $rule) }}"
                                style="font-size:11px;padding:3px 10px;">
                            {{ $rule->is_active ? 'Activa' : 'Inactiva' }}
                        </button>
                    </td>
                    <td>
                        <a href="{{ route('admin.crm.pipeline-rules.edit', $rule) }}" class="action-btn view"><i class="fa fa-edit"></i></a>
                        <form method="POST" action="{{ route('admin.crm.pipeline-rules.destroy', $rule) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar esta regla?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn danger"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@push('script')
<script>
document.querySelectorAll('.btn-toggle-rule').forEach(btn => {
    btn.addEventListener('click', function() {
        fetch(this.dataset.url, {method:'POST', headers:{'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}})
            .then(r => r.json()).then(d => { if(d.success) location.reload(); });
    });
});
</script>
@endpush
@endsection
