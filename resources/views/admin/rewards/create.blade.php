@extends('admin.main')
@section('title', 'Nueva Recompensa')
@section('content')

@include('admin.metrics.layouts._metrics-styles')

<div class="crm-page">

    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-trophy"></i> Nueva Regla de Recompensa</h2>
            <div class="sub">Configura los criterios y valor de la recompensa</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.rewards.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div style="max-width:680px;">
        <div class="dashboard-card">
            <div class="dc-header" style="background:linear-gradient(135deg,#c2ac1f,#a89617);">
                <h5 style="color:#fff;"><i class="fa fa-trophy" style="color:#fff;"></i> Configurar Recompensa</h5>
            </div>
            <div class="dc-body">
                <form method="POST" action="{{ route('admin.rewards.store') }}">
                    @csrf
                    @include('admin.rewards._form')
                    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid #f0f0f0;">
                        <a href="{{ route('admin.rewards.index') }}" class="action-btn secondary">Cancelar</a>
                        <button type="submit" class="action-btn success"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
