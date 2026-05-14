@extends('admin.main')
@section('title', 'Nueva Plantilla de Mensaje')
@section('content')
@include('admin.crm.layouts._crm-styles')
<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-comment"></i> Nueva Plantilla de Mensaje</h2>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.message-templates.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    <div class="dashboard-card">
        <div class="dc-header"><h5><i class="fa fa-comment" style="color:#c2ac1f;"></i> Información de la Plantilla</h5></div>
        <div class="dc-body">
            <form method="POST" action="{{ route('admin.crm.message-templates.store') }}">
                @csrf
                @include('admin.crm.message-templates._form', ['template' => null])
                <div style="margin-top:20px;display:flex;gap:10px;">
                    <button type="submit" class="action-btn primary"><i class="fa fa-save"></i> Guardar Plantilla</button>
                    <a href="{{ route('admin.crm.message-templates.index') }}" class="action-btn secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
