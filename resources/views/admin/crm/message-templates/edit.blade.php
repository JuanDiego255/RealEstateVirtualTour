@extends('admin.main')
@section('title', 'Editar Plantilla de Mensaje')
@section('content')
@include('admin.crm.layouts._crm-styles')
<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-edit"></i> Editar Plantilla</h2>
            <div class="sub">{{ $template->name }}</div>
        </div>
        <div class="actions">
            <form method="POST" action="{{ route('admin.crm.message-templates.destroy', $template) }}" onsubmit="return confirm('¿Eliminar esta plantilla?')">
                @csrf @method('DELETE')
                <button type="submit" class="action-btn danger"><i class="fa fa-trash"></i></button>
            </form>
            <a href="{{ route('admin.crm.message-templates.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    <div class="dashboard-card">
        <div class="dc-header"><h5><i class="fa fa-edit" style="color:#c2ac1f;"></i> Editar Plantilla</h5></div>
        <div class="dc-body">
            <form method="POST" action="{{ route('admin.crm.message-templates.update', $template) }}">
                @csrf @method('PUT')
                @include('admin.crm.message-templates._form', ['template' => $template])
                <div style="margin-top:20px;display:flex;gap:10px;">
                    <button type="submit" class="action-btn primary"><i class="fa fa-save"></i> Actualizar</button>
                    <a href="{{ route('admin.crm.message-templates.index') }}" class="action-btn secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
