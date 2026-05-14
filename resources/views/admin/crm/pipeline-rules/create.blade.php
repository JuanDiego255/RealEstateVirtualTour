@extends('admin.main')
@section('title', 'Nueva Regla de Pipeline')
@section('content')
@include('admin.crm.layouts._crm-styles')
<div class="crm-page">
    <div class="crm-page-header">
        <div><h2><i class="fa fa-plus"></i> Nueva Regla de Pipeline</h2></div>
        <div class="actions">
            <a href="{{ route('admin.crm.pipeline-rules.index') }}" class="action-btn secondary"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>
    </div>
    <div class="dashboard-card">
        <div class="dc-header"><h5><i class="fa fa-cogs" style="color:#c2ac1f;"></i> Configurar Regla</h5></div>
        <div class="dc-body">
            <form method="POST" action="{{ route('admin.crm.pipeline-rules.store') }}">
                @csrf
                @include('admin.crm.pipeline-rules._form', ['rule' => null])
                <div style="margin-top:20px;display:flex;gap:10px;">
                    <button type="submit" class="action-btn primary"><i class="fa fa-save"></i> Crear Regla</button>
                    <a href="{{ route('admin.crm.pipeline-rules.index') }}" class="action-btn secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
