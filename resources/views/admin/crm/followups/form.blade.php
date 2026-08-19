@extends('admin.main')
@section('title', $sequence->exists ? 'Editar secuencia' : 'Nueva secuencia')
@section('content')
@include('admin.crm._ui')
@php
    $oldSteps = old('steps', $sequence->exists ? $sequence->steps->map(fn($s) => [
        'delay_hours' => $s->delay_hours, 'channel' => $s->channel,
        'message_template_id' => $s->message_template_id, 'subject' => $s->subject, 'body' => $s->body,
    ])->toArray() : []);
@endphp

<div class="crm-page">
    <div class="crm-page-header">
        <div><h2><i class="fa fa-random"></i> {{ $sequence->exists ? 'Editar secuencia' : 'Nueva secuencia' }}</h2></div>
        <a href="{{ route('admin.crm.followups.index') }}" class="action-btn secondary"><i class="fa fa-arrow-left"></i> Volver</a>
    </div>

    @if($errors->any())
        <div class="crm-alert danger"><ul style="margin:0; padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ $sequence->exists ? route('admin.crm.followups.update', $sequence) : route('admin.crm.followups.store') }}">
        @csrf
        @if($sequence->exists) @method('PUT') @endif

        <div class="crm-section">
            <div class="crm-section-pad">
                <div class="crm-form-row">
                    <div class="crm-form-group">
                        <label class="crm-label">Nombre</label>
                        <input type="text" name="name" class="crm-input" value="{{ old('name', $sequence->name) }}" required placeholder="Ej: Bienvenida y seguimiento">
                    </div>
                    <div class="crm-form-group">
                        <label class="crm-label">Disparo</label>
                        <select name="trigger" class="crm-select">
                            <option value="lead_created" {{ old('trigger', $sequence->trigger) === 'lead_created' ? 'selected' : '' }}>Al crear el lead</option>
                            <option value="manual" {{ old('trigger', $sequence->trigger) === 'manual' ? 'selected' : '' }}>Manual</option>
                        </select>
                    </div>
                    <div class="crm-form-group">
                        <label class="crm-label">Opciones</label>
                        <div style="display:flex; flex-direction:column; gap:8px; padding-top:4px;">
                            <label class="crm-toggle">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $sequence->is_active) ? 'checked' : '' }}>
                                <span class="track"></span> Activa
                            </label>
                            <label class="crm-toggle">
                                <input type="hidden" name="stop_on_reply" value="0">
                                <input type="checkbox" name="stop_on_reply" value="1" {{ old('stop_on_reply', $sequence->stop_on_reply) ? 'checked' : '' }}>
                                <span class="track"></span> Detener si responde
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="crm-section">
            <div class="crm-section-header">
                <h5><i class="fa fa-list-ol"></i> Pasos</h5>
                <button type="button" class="action-btn secondary xs" onclick="addStep()"><i class="fa fa-plus"></i> Agregar paso</button>
            </div>
            <div class="crm-section-pad">
                <p class="crm-help" style="margin-bottom:14px;">La demora es en horas desde el paso anterior (0 = inmediato). Ej: 0, 48, 120.</p>
                <div id="steps"></div>
            </div>
        </div>

        <button class="action-btn primary"><i class="fa fa-save"></i> Guardar secuencia</button>
    </form>
</div>

<template id="step-template">
    <div class="step-row" style="border:1px solid #eef0f3; border-radius:12px; padding:16px; margin-bottom:14px; background:#fbfbfc;">
        <div class="crm-form-row">
            <div class="crm-form-group" style="max-width:150px;">
                <label class="crm-label">Demora (horas)</label>
                <input type="number" min="0" name="steps[__IDX__][delay_hours]" class="crm-input" value="0">
            </div>
            <div class="crm-form-group" style="max-width:170px;">
                <label class="crm-label">Canal</label>
                <select name="steps[__IDX__][channel]" class="crm-select">
                    <option value="whatsapp">WhatsApp</option>
                    <option value="email">Email</option>
                </select>
            </div>
            <div class="crm-form-group">
                <label class="crm-label">Plantilla (opcional)</label>
                <select name="steps[__IDX__][message_template_id]" class="crm-select">
                    <option value="">— Mensaje propio —</option>
                    @foreach($templates as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->channel }})</option>
                    @endforeach
                </select>
            </div>
            <div class="crm-form-group">
                <label class="crm-label">Asunto (email)</label>
                <input type="text" name="steps[__IDX__][subject]" class="crm-input" placeholder="Solo email">
            </div>
        </div>
        <div class="crm-form-group" style="margin-bottom:0;">
            <label class="crm-label">Mensaje (si no usás plantilla)</label>
            <textarea name="steps[__IDX__][body]" rows="2" class="crm-textarea" placeholder="Hola @{{nombre}}, ¿seguís interesado?"></textarea>
        </div>
        <div style="text-align:right; margin-top:8px;">
            <button type="button" class="action-btn danger xs" onclick="this.closest('.step-row').remove()"><i class="fa fa-trash"></i> Quitar paso</button>
        </div>
    </div>
</template>

<script>
    let stepIdx = 0;
    function addStep(data) {
        const tpl = document.getElementById('step-template').innerHTML.replace(/__IDX__/g, stepIdx);
        const wrap = document.createElement('div');
        wrap.innerHTML = tpl;
        const node = wrap.firstElementChild;
        if (data) {
            node.querySelector('[name$="[delay_hours]"]').value = data.delay_hours ?? 0;
            node.querySelector('[name$="[channel]"]').value = data.channel ?? 'whatsapp';
            node.querySelector('[name$="[message_template_id]"]').value = data.message_template_id ?? '';
            node.querySelector('[name$="[subject]"]').value = data.subject ?? '';
            node.querySelector('[name$="[body]"]').value = data.body ?? '';
        }
        document.getElementById('steps').appendChild(node);
        stepIdx++;
    }
    const existing = @json($oldSteps);
    if (existing.length) { existing.forEach(s => addStep(s)); } else { addStep(); }
</script>
@endsection
