@extends('admin.main')
@section('title', $sequence->exists ? 'Editar secuencia' : 'Nueva secuencia')
@section('content')
@php
    $oldSteps = old('steps', $sequence->exists ? $sequence->steps->map(fn($s) => [
        'delay_hours' => $s->delay_hours, 'channel' => $s->channel,
        'message_template_id' => $s->message_template_id, 'subject' => $s->subject, 'body' => $s->body,
    ])->toArray() : []);
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fa fa-random"></i> {{ $sequence->exists ? 'Editar secuencia' : 'Nueva secuencia' }}</h4>
        <a href="{{ route('admin.crm.followups.index') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ $sequence->exists ? route('admin.crm.followups.update', $sequence) : route('admin.crm.followups.store') }}">
        @csrf
        @if($sequence->exists) @method('PUT') @endif

        <div class="card mb-4">
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Nombre</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $sequence->name) }}" required placeholder="Ej: Bienvenida y seguimiento">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Disparo</label>
                        <select name="trigger" class="form-control">
                            <option value="lead_created" {{ old('trigger', $sequence->trigger) === 'lead_created' ? 'selected' : '' }}>Al crear el lead</option>
                            <option value="manual" {{ old('trigger', $sequence->trigger) === 'manual' ? 'selected' : '' }}>Manual</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3 d-flex align-items-end">
                        <div>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $sequence->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Activa</label>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="stop_on_reply" value="0">
                                <input type="checkbox" class="custom-control-input" id="stop_on_reply" name="stop_on_reply" value="1" {{ old('stop_on_reply', $sequence->stop_on_reply) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="stop_on_reply">Detener si responde</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Pasos</strong>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addStep()"><i class="fa fa-plus"></i> Agregar paso</button>
            </div>
            <div class="card-body">
                <p class="text-muted small">La demora es en horas desde el paso anterior (0 = inmediato). Ej: 0, 48, 120.</p>
                <div id="steps"></div>
            </div>
        </div>

        <button class="btn btn-primary"><i class="fa fa-save"></i> Guardar secuencia</button>
    </form>
</div>

<template id="step-template">
    <div class="border rounded p-3 mb-3 step-row">
        <div class="form-row">
            <div class="form-group col-md-2">
                <label class="small mb-1">Demora (horas)</label>
                <input type="number" min="0" name="steps[__IDX__][delay_hours]" class="form-control form-control-sm" value="0">
            </div>
            <div class="form-group col-md-2">
                <label class="small mb-1">Canal</label>
                <select name="steps[__IDX__][channel]" class="form-control form-control-sm">
                    <option value="whatsapp">WhatsApp</option>
                    <option value="email">Email</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="small mb-1">Plantilla (opcional)</label>
                <select name="steps[__IDX__][message_template_id]" class="form-control form-control-sm">
                    <option value="">— Mensaje propio —</option>
                    @foreach($templates as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->channel }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="small mb-1">Asunto (email)</label>
                <input type="text" name="steps[__IDX__][subject]" class="form-control form-control-sm" placeholder="Solo email">
            </div>
            <div class="form-group col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-block" onclick="this.closest('.step-row').remove()">×</button>
            </div>
        </div>
        <div class="form-group mb-0">
            <label class="small mb-1">Mensaje (si no usás plantilla)</label>
            <textarea name="steps[__IDX__][body]" rows="2" class="form-control form-control-sm" placeholder="Hola {{ '{{nombre}}' }}, ¿seguís interesado?"></textarea>
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
    if (existing.length) {
        existing.forEach(s => addStep(s));
    } else {
        addStep();
    }
</script>
@endsection
