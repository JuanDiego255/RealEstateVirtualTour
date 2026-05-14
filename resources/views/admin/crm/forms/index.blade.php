@extends('admin.main')
@section('title', 'Formularios Web')
@section('content')
@include('admin.crm.layouts._crm-styles')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3">
    <strong>{{ session('success') }}</strong>
    <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
</div>
@endif

<div class="crm-page">

    {{-- Header --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-wpforms"></i> Formularios Web</h2>
            <p class="sub">Captura leads desde cualquier sitio web mediante formularios embebibles</p>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.forms.create') }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nuevo Formulario
            </a>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);max-width:600px;margin-bottom:24px;">
        <div class="stat-card">
            <div class="sc-icon blue"><i class="fa fa-wpforms"></i></div>
            <div class="sc-value">{{ $stats['total'] }}</div>
            <div class="sc-label">Total formularios</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon green"><i class="fa fa-check-circle"></i></div>
            <div class="sc-value">{{ $stats['active'] }}</div>
            <div class="sc-label">Activos</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon gold"><i class="fa fa-inbox"></i></div>
            <div class="sc-value">{{ $stats['submissions_total'] }}</div>
            <div class="sc-label">Submissions este mes</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="dashboard-card">
        <div class="dc-header" style="background:#1a1a2e;">
            <h5 style="color:#fff;"><i class="fa fa-list" style="color:#c2ac1f;"></i> Mis Formularios</h5>
        </div>
        <div class="dc-body" style="padding:0;">
            @if($forms->isEmpty())
                <div style="text-align:center;padding:48px 20px;color:#aaa;">
                    <i class="fa fa-wpforms" style="font-size:48px;display:block;margin-bottom:12px;"></i>
                    <p style="margin:0;">No hay formularios aún. <a href="{{ route('admin.crm.forms.create') }}">Crear el primero</a></p>
                </div>
            @else
            <div style="overflow-x:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Submissions</th>
                        <th>Embed / Slug</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($forms as $form)
                <tr>
                    <td>
                        <div style="font-weight:600;">{{ $form->name }}</div>
                        <div style="font-size:12px;color:#888;">{{ $form->title }}</div>
                    </td>
                    <td>
                        <button class="crm-badge {{ $form->is_active ? 'won' : 'lost' }} toggle-status-btn"
                                data-id="{{ $form->id }}"
                                data-url="{{ route('admin.crm.forms.toggle', $form) }}"
                                style="border:none;cursor:pointer;">
                            {{ $form->is_active ? 'Activo' : 'Inactivo' }}
                        </button>
                    </td>
                    <td>
                        <span style="font-weight:600;">{{ $form->submissions_count }}</span>
                    </td>
                    <td>
                        <code style="font-size:11px;background:#f5f5f5;padding:3px 7px;border-radius:5px;">{{ $form->slug }}</code>
                    </td>
                    <td style="font-size:12px;color:#888;">
                        {{ $form->created_at->format('d/m/Y') }}
                    </td>
                    <td>
                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                            <a href="{{ route('admin.crm.forms.show', $form) }}" class="action-btn view" title="Ver detalle">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.crm.forms.edit', $form) }}" class="action-btn warning" title="Editar">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.crm.forms.destroy', $form) }}"
                                  onsubmit="return confirm('¿Eliminar este formulario y todos sus datos?')">
                                @csrf @method('DELETE')
                                <button class="action-btn danger" title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            <div style="padding:16px 18px;">
                {{ $forms->links() }}
            </div>
            @endif
        </div>
    </div>

</div>

<script>
document.querySelectorAll('.toggle-status-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var url = this.dataset.url;
        var el  = this;
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(function(data) {
            if (data.success) {
                if (data.is_active) {
                    el.textContent = 'Activo';
                    el.className = 'crm-badge won toggle-status-btn';
                } else {
                    el.textContent = 'Inactivo';
                    el.className = 'crm-badge lost toggle-status-btn';
                }
                el.dataset.url = url;
            }
        });
    });
});
</script>
@endsection
