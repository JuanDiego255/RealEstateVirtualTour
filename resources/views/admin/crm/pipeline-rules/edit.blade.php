@extends('admin.main')
@section('title', 'Editar Regla')
@section('content')
@include('admin.crm.layouts._crm-styles')
<div class="crm-page">
    <div class="crm-page-header">
        <div><h2><i class="fa fa-edit"></i> Editar Regla</h2><div class="sub">{{ $rule->name }}</div></div>
        <div class="actions">
            <form method="POST" action="{{ route('admin.crm.pipeline-rules.destroy', $rule) }}" onsubmit="return confirm('¿Eliminar?')">
                @csrf @method('DELETE')
                <button type="submit" class="action-btn danger"><i class="fa fa-trash"></i></button>
            </form>
            <a href="{{ route('admin.crm.pipeline-rules.index') }}" class="action-btn secondary"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>
    </div>
    <div class="dashboard-card">
        <div class="dc-header"><h5><i class="fa fa-cogs" style="color:#c2ac1f;"></i> Editar Regla</h5></div>
        <div class="dc-body">
            <form method="POST" action="{{ route('admin.crm.pipeline-rules.update', $rule) }}">
                @csrf @method('PUT')
                @include('admin.crm.pipeline-rules._form', ['rule' => $rule])
                <div style="margin-top:20px;display:flex;gap:10px;">
                    <button type="submit" class="action-btn primary"><i class="fa fa-save"></i> Actualizar</button>
                    <a href="{{ route('admin.crm.pipeline-rules.index') }}" class="action-btn secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
