@extends('admin.main')
@section('title', 'Editar Plantilla de Actividad')
@section('content')

@include('admin.crm.layouts._crm-styles')

<div class="crm-page">

    {{-- Header --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-pencil-square-o"></i> Editar Plantilla</h2>
            <div class="sub">{{ $template->name }}</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.activity-templates.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.crm.activity-templates.update', $template) }}">
        @csrf @method('PUT')

        <div class="dashboard-card" style="max-width:760px;">
            <div class="dc-header" style="background:#1a1a2e;">
                <h5 style="color:#fff;"><i class="fa fa-file-text-o" style="color:#c2ac1f;"></i> Datos de la Plantilla</h5>
            </div>
            <div class="dc-body" style="padding:24px;">

                {{-- Nombre --}}
                <div class="form-group mb-4">
                    <label style="font-size:13px; font-weight:600; color:#444; margin-bottom:6px; display:block;">
                        Nombre <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $template->name) }}"
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="Ej: Llamada de seguimiento inicial"
                           style="border-radius:9px; border:1px solid #e5e7eb; font-size:14px;">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tipo + Etapa --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label style="font-size:13px; font-weight:600; color:#444; margin-bottom:6px; display:block;">
                            Tipo de Actividad <span class="text-danger">*</span>
                        </label>
                        <select name="type" class="form-control @error('type') is-invalid @enderror"
                                style="border-radius:9px; border:1px solid #e5e7eb; font-size:14px;">
                            <option value="">— Seleccionar tipo —</option>
                            <option value="call"     {{ old('type', $template->type)=='call'?'selected':'' }}>📞 Llamada</option>
                            <option value="email"    {{ old('type', $template->type)=='email'?'selected':'' }}>✉️ Email</option>
                            <option value="whatsapp" {{ old('type', $template->type)=='whatsapp'?'selected':'' }}>💬 WhatsApp</option>
                            <option value="visit"    {{ old('type', $template->type)=='visit'?'selected':'' }}>🏠 Visita</option>
                            <option value="meeting"  {{ old('type', $template->type)=='meeting'?'selected':'' }}>👥 Reunión</option>
                            <option value="note"     {{ old('type', $template->type)=='note'?'selected':'' }}>📝 Nota</option>
                            <option value="other"    {{ old('type', $template->type)=='other'?'selected':'' }}>🔘 Otro</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label style="font-size:13px; font-weight:600; color:#444; margin-bottom:6px; display:block;">
                            Etapa del Lead <span style="color:#aaa; font-weight:400;">(opcional)</span>
                        </label>
                        <select name="stage" class="form-control @error('stage') is-invalid @enderror"
                                style="border-radius:9px; border:1px solid #e5e7eb; font-size:14px;">
                            <option value="">— Aplica a todas —</option>
                            <option value="new"         {{ old('stage', $template->stage)=='new'?'selected':'' }}>Nuevo</option>
                            <option value="contacted"   {{ old('stage', $template->stage)=='contacted'?'selected':'' }}>Contactado</option>
                            <option value="qualified"   {{ old('stage', $template->stage)=='qualified'?'selected':'' }}>Calificado</option>
                            <option value="proposal"    {{ old('stage', $template->stage)=='proposal'?'selected':'' }}>Propuesta</option>
                            <option value="negotiation" {{ old('stage', $template->stage)=='negotiation'?'selected':'' }}>Negociación</option>
                            <option value="won"         {{ old('stage', $template->stage)=='won'?'selected':'' }}>Ganado</option>
                            <option value="lost"        {{ old('stage', $template->stage)=='lost'?'selected':'' }}>Perdido</option>
                        </select>
                        @error('stage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Asunto --}}
                <div class="form-group mb-4">
                    <label style="font-size:13px; font-weight:600; color:#444; margin-bottom:6px; display:block;">
                        Asunto <span style="color:#aaa; font-weight:400;">(opcional, para email)</span>
                    </label>
                    <input type="text" name="subject" value="{{ old('subject', $template->subject) }}"
                           class="form-control @error('subject') is-invalid @enderror"
                           placeholder="Ej: Seguimiento de tu consulta sobre @{{propiedad}}"
                           style="border-radius:9px; border:1px solid #e5e7eb; font-size:14px;">
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Cuerpo --}}
                <div class="form-group mb-3">
                    <label style="font-size:13px; font-weight:600; color:#444; margin-bottom:6px; display:block;">
                        Cuerpo del mensaje <span class="text-danger">*</span>
                    </label>
                    <div style="background:#fffdf0; border:1px solid #f0e8a0; border-radius:9px; padding:10px 14px; margin-bottom:10px; font-size:12.5px; color:#7a6012;">
                        <i class="fa fa-info-circle"></i>
                        Variables disponibles:
                        <code style="background:#f0e8a0; border-radius:4px; padding:1px 5px; margin:0 2px;">@{{nombre}}</code>
                        <code style="background:#f0e8a0; border-radius:4px; padding:1px 5px; margin:0 2px;">@{{agente}}</code>
                        <code style="background:#f0e8a0; border-radius:4px; padding:1px 5px; margin:0 2px;">@{{propiedad}}</code>
                        <code style="background:#f0e8a0; border-radius:4px; padding:1px 5px; margin:0 2px;">@{{fecha}}</code>
                    </div>
                    <textarea name="body" rows="7"
                              class="form-control @error('body') is-invalid @enderror"
                              placeholder="Hola @{{nombre}}, te contacto en nombre de @{{agente}}..."
                              style="border-radius:9px; border:1px solid #e5e7eb; font-size:14px; resize:vertical;">{{ old('body', $template->body) }}</textarea>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div style="text-align:right; font-size:12px; color:#aaa; margin-top:4px;">
                        <span id="body-count">0</span> / 2000 caracteres
                    </div>
                </div>

                {{-- Orden + Activo --}}
                <div class="row align-items-center mb-2">
                    <div class="col-md-4">
                        <label style="font-size:13px; font-weight:600; color:#444; margin-bottom:6px; display:block;">
                            Orden
                        </label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $template->sort_order) }}"
                               class="form-control"
                               min="0" style="border-radius:9px; border:1px solid #e5e7eb; font-size:14px;">
                    </div>
                    <div class="col-md-8 d-flex align-items-center" style="padding-top:24px;">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:14px; font-weight:500; color:#444;">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                                   style="width:18px; height:18px; accent-color:#c2ac1f;">
                            Plantilla activa (disponible para usar)
                        </label>
                    </div>
                </div>

            </div>
            <div style="padding:16px 24px; border-top:1px solid #f0f0f0; display:flex; gap:10px; justify-content:space-between; align-items:center;">
                {{-- Delete --}}
                <form method="POST" action="{{ route('admin.crm.activity-templates.destroy', $template) }}"
                      onsubmit="return confirm('¿Seguro que deseas eliminar esta plantilla? Esta acción no se puede deshacer.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn danger">
                        <i class="fa fa-trash"></i> Eliminar plantilla
                    </button>
                </form>
                <div style="display:flex; gap:10px;">
                    <a href="{{ route('admin.crm.activity-templates.index') }}" class="action-btn secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="action-btn"
                            style="background:linear-gradient(135deg,#c2ac1f,#a89617); color:#000; font-weight:700; padding:9px 22px; border-radius:9px;">
                        <i class="fa fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>

    </form>

</div>

@endsection

@push('script')
<script>
(function() {
    var bodyEl = document.querySelector('textarea[name="body"]');
    var counter = document.getElementById('body-count');
    function updateCount() {
        if (bodyEl && counter) counter.textContent = bodyEl.value.length;
    }
    if (bodyEl) {
        bodyEl.addEventListener('input', updateCount);
        updateCount();
    }
})();
</script>
@endpush
