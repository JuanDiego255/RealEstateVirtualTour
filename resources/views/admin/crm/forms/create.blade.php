@extends('admin.main')
@section('title', 'Nuevo Formulario Web')
@section('content')
@include('admin.crm.layouts._crm-styles')

<style>
.form-builder-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 20px;
    align-items: start;
}
@media(max-width:1000px) { .form-builder-grid { grid-template-columns: 1fr; } }

.field-row {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 10px 12px;
    margin-bottom: 8px;
    cursor: grab;
    transition: box-shadow .15s;
}
.field-row:active { cursor: grabbing; box-shadow: 0 4px 12px rgba(0,0,0,.15); }
.field-row .drag-handle { color: #aaa; font-size: 16px; margin-right: 4px; }
.field-row input, .field-row select {
    border: 1px solid #dee2e6;
    border-radius: 7px;
    padding: 5px 9px;
    font-size: 13px;
    background: #fff;
}
.field-row input { flex: 1; min-width: 80px; }
.field-row select { width: 120px; }
.required-toggle {
    display: flex; align-items: center; gap: 5px;
    font-size: 12px; color: #666; white-space: nowrap;
}
.required-toggle input[type=checkbox] { width: 14px; height: 14px; margin: 0; }
.preview-field { margin-bottom: 14px; }
.preview-field label {
    display: block; font-size: 13px; font-weight: 600;
    color: #1a1a2e; margin-bottom: 5px;
}
.preview-field label span.req { color: #ef4444; }
.preview-field input,
.preview-field textarea,
.preview-field select {
    width: 100%; padding: 9px 12px;
    border: 1px solid #d1d5db; border-radius: 8px;
    font-size: 14px; background: #fff;
    box-sizing: border-box;
}
.preview-field textarea { height: 80px; resize: vertical; }
.preview-submit {
    width: 100%; padding: 12px;
    background: #1a1a2e; color: #fff;
    border: none; border-radius: 10px;
    font-size: 15px; font-weight: 600;
    cursor: pointer; margin-top: 8px;
}
.builder-label {
    font-size: 11px; text-transform: uppercase;
    letter-spacing: .5px; color: #888; font-weight: 600;
    margin-bottom: 8px;
}
</style>

<div class="crm-page">

    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-wpforms"></i> Nuevo Formulario Web</h2>
            <p class="sub">Diseña tu formulario y embébelo en cualquier sitio</p>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.forms.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.crm.forms.store') }}" id="form-builder-form">
        @csrf
        <input type="hidden" name="fields_json" id="fields_json">

        {{-- Ajustes generales --}}
        <div class="dashboard-card" style="margin-bottom:20px;">
            <div class="dc-header" style="background:#1a1a2e;">
                <h5 style="color:#fff;"><i class="fa fa-cog" style="color:#c2ac1f;"></i> Configuración General</h5>
            </div>
            <div class="dc-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="builder-label">Nombre interno *</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               placeholder="Ej: Formulario contacto homepage"
                               class="form-control" required>
                        @error('name')<span style="color:#ef4444;font-size:12px;">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="builder-label">Fuente por defecto *</label>
                        <select name="default_source" class="form-control" required>
                            @foreach($sources as $key => $label)
                                <option value="{{ $key }}" {{ old('default_source')==$key?'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="builder-label">Título visible del formulario *</label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               placeholder="Ej: Contáctanos" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="builder-label">Mensaje de éxito *</label>
                        <input type="text" name="success_message"
                               value="{{ old('success_message', '¡Gracias! Nos pondremos en contacto pronto.') }}"
                               class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="builder-label">Descripción</label>
                        <textarea name="description" class="form-control" rows="2"
                                  placeholder="Texto introductorio del formulario">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="builder-label">URL de redirección (opcional)</label>
                        <input type="url" name="redirect_url" value="{{ old('redirect_url') }}"
                               placeholder="https://..." class="form-control">
                    </div>
                </div>
            </div>
        </div>

        {{-- Builder + Preview --}}
        <div class="form-builder-grid">

            {{-- Left: Field builder --}}
            <div class="dashboard-card">
                <div class="dc-header" style="background:#1a1a2e;">
                    <h5 style="color:#fff;"><i class="fa fa-list-ul" style="color:#c2ac1f;"></i> Campos del Formulario</h5>
                    <button type="button" id="btn-add-field" class="action-btn gold" style="padding:5px 12px;font-size:12px;">
                        <i class="fa fa-plus"></i> Agregar Campo
                    </button>
                </div>
                <div class="dc-body">
                    <div style="display:flex;gap:8px;margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid #f0f0f0;">
                        <span style="flex:1;font-size:11px;color:#aaa;text-transform:uppercase;font-weight:600;">Nombre / Etiqueta</span>
                        <span style="width:120px;font-size:11px;color:#aaa;text-transform:uppercase;font-weight:600;">Tipo</span>
                        <span style="width:80px;font-size:11px;color:#aaa;text-transform:uppercase;font-weight:600;text-align:center;">Req.</span>
                        <span style="width:30px;"></span>
                    </div>
                    <div id="fields-list">
                        @foreach($defaultFields as $field)
                        <div class="field-row" draggable="true">
                            <span class="drag-handle"><i class="fa fa-bars"></i></span>
                            <input type="text" class="field-name" placeholder="name_campo"
                                   value="{{ $field['name'] }}" style="width:110px;flex:none;">
                            <input type="text" class="field-label" placeholder="Etiqueta visible"
                                   value="{{ $field['label'] }}" style="flex:1;">
                            <select class="field-type">
                                @foreach(['text','email','tel','number','textarea','select','checkbox'] as $t)
                                    <option value="{{ $t }}" {{ $field['type']==$t?'selected':'' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                            <label class="required-toggle">
                                <input type="checkbox" class="field-required" {{ $field['required']?'checked':'' }}> Req
                            </label>
                            <button type="button" class="btn-remove-field" style="background:none;border:none;color:#ef4444;font-size:16px;cursor:pointer;padding:2px;">
                                <i class="fa fa-times-circle"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    <p style="font-size:12px;color:#aaa;margin-top:8px;"><i class="fa fa-info-circle"></i> Arrastra para reordenar campos</p>
                </div>
            </div>

            {{-- Right: Live Preview --}}
            <div class="dashboard-card" style="position:sticky;top:20px;">
                <div class="dc-header" style="background:#1a1a2e;">
                    <h5 style="color:#fff;"><i class="fa fa-eye" style="color:#c2ac1f;"></i> Vista Previa</h5>
                </div>
                <div class="dc-body">
                    <div style="background:#f8f9fa;border-radius:12px;padding:20px;" id="form-preview">
                        <div style="font-size:18px;font-weight:700;color:#1a1a2e;margin-bottom:6px;" id="preview-title">Título del formulario</div>
                        <div style="font-size:13px;color:#888;margin-bottom:18px;" id="preview-desc"></div>
                        <div id="preview-fields"></div>
                        <button class="preview-submit" type="button">Enviar</button>
                    </div>
                </div>
            </div>

        </div>

        <div style="margin-top:20px;text-align:right;">
            <a href="{{ route('admin.crm.forms.index') }}" class="action-btn secondary" style="margin-right:8px;">Cancelar</a>
            <button type="submit" class="action-btn primary" style="padding:10px 28px;font-size:14px;">
                <i class="fa fa-save"></i> Guardar Formulario
            </button>
        </div>
    </form>

</div>

<script>
(function() {
    var fieldsList    = document.getElementById('fields-list');
    var previewFields = document.getElementById('preview-fields');
    var previewTitle  = document.getElementById('preview-title');
    var previewDesc   = document.getElementById('preview-desc');
    var fieldsJsonInput = document.getElementById('fields_json');

    // Sync settings to preview
    document.querySelector('[name="title"]').addEventListener('input', function() {
        previewTitle.textContent = this.value || 'Título del formulario';
    });
    document.querySelector('[name="description"]').addEventListener('input', function() {
        previewDesc.textContent = this.value;
    });

    function renderPreview() {
        previewFields.innerHTML = '';
        var rows = fieldsList.querySelectorAll('.field-row');
        rows.forEach(function(row) {
            var label    = row.querySelector('.field-label').value || 'Campo';
            var type     = row.querySelector('.field-type').value;
            var required = row.querySelector('.field-required').checked;
            var div      = document.createElement('div');
            div.className = 'preview-field';
            var lbl = '<label>' + label + (required ? ' <span class="req">*</span>' : '') + '</label>';
            var inp = '';
            if (type === 'textarea') {
                inp = '<textarea placeholder="' + label + '" disabled></textarea>';
            } else if (type === 'select') {
                inp = '<select disabled><option>Selecciona...</option></select>';
            } else if (type === 'checkbox') {
                inp = '<label style="font-weight:normal;display:flex;gap:6px;align-items:center;"><input type="checkbox" disabled> ' + label + '</label>';
                div.innerHTML = inp;
                previewFields.appendChild(div);
                return;
            } else {
                inp = '<input type="' + type + '" placeholder="' + label + '" disabled>';
            }
            div.innerHTML = lbl + inp;
            previewFields.appendChild(div);
        });
    }

    function serializeFields() {
        var rows = fieldsList.querySelectorAll('.field-row');
        var fields = [];
        rows.forEach(function(row) {
            fields.push({
                name:     row.querySelector('.field-name').value.trim().replace(/\s+/g,'_'),
                label:    row.querySelector('.field-label').value.trim(),
                type:     row.querySelector('.field-type').value,
                required: row.querySelector('.field-required').checked,
            });
        });
        return fields;
    }

    // Delegate change/input on fields list
    fieldsList.addEventListener('input', renderPreview);
    fieldsList.addEventListener('change', renderPreview);

    // Remove field
    fieldsList.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-remove-field');
        if (btn) {
            btn.closest('.field-row').remove();
            renderPreview();
        }
    });

    // Add field
    document.getElementById('btn-add-field').addEventListener('click', function() {
        var row = document.createElement('div');
        row.className = 'field-row';
        row.draggable = true;
        row.innerHTML = '<span class="drag-handle"><i class="fa fa-bars"></i></span>' +
            '<input type="text" class="field-name" placeholder="campo" style="width:110px;flex:none;">' +
            '<input type="text" class="field-label" placeholder="Etiqueta" style="flex:1;">' +
            '<select class="field-type">' +
                ['text','email','tel','number','textarea','select','checkbox'].map(function(t){
                    return '<option value="'+t+'">'+t+'</option>';
                }).join('') +
            '</select>' +
            '<label class="required-toggle"><input type="checkbox" class="field-required"> Req</label>' +
            '<button type="button" class="btn-remove-field" style="background:none;border:none;color:#ef4444;font-size:16px;cursor:pointer;padding:2px;"><i class="fa fa-times-circle"></i></button>';
        fieldsList.appendChild(row);
        setupDrag(row);
        renderPreview();
    });

    // Drag-to-reorder
    var dragSrc = null;
    function setupDrag(el) {
        el.addEventListener('dragstart', function(e) { dragSrc = el; el.style.opacity = '0.4'; });
        el.addEventListener('dragend',   function()  { el.style.opacity = ''; renderPreview(); });
        el.addEventListener('dragover',  function(e) { e.preventDefault(); });
        el.addEventListener('drop', function(e) {
            e.preventDefault();
            if (dragSrc !== el) {
                var allRows = [...fieldsList.querySelectorAll('.field-row')];
                var srcIdx  = allRows.indexOf(dragSrc);
                var tgtIdx  = allRows.indexOf(el);
                if (srcIdx < tgtIdx) {
                    fieldsList.insertBefore(dragSrc, el.nextSibling);
                } else {
                    fieldsList.insertBefore(dragSrc, el);
                }
            }
        });
    }
    fieldsList.querySelectorAll('.field-row').forEach(setupDrag);

    // On submit: serialize fields to hidden input and inject real field inputs
    document.getElementById('form-builder-form').addEventListener('submit', function(e) {
        var fields = serializeFields();
        if (fields.length === 0) {
            e.preventDefault();
            alert('Agrega al menos un campo.');
            return;
        }
        fieldsJsonInput.value = JSON.stringify(fields);

        // Remove any old injected fields
        this.querySelectorAll('.injected-field-input').forEach(function(el){ el.remove(); });

        // Inject fields as proper named inputs for server validation
        var form = this;
        fields.forEach(function(f, i) {
            ['name','label','type'].forEach(function(key) {
                var inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = 'fields[' + i + '][' + key + ']';
                inp.value = f[key];
                inp.className = 'injected-field-input';
                form.appendChild(inp);
            });
            if (f.required) {
                var reqInp = document.createElement('input');
                reqInp.type  = 'hidden';
                reqInp.name  = 'fields[' + i + '][required]';
                reqInp.value = '1';
                reqInp.className = 'injected-field-input';
                form.appendChild(reqInp);
            }
        });
    });

    renderPreview();
})();
</script>
@endsection
