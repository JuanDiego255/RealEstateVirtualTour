@extends('admin.main')
@section('title', 'Reglas de Recompensas')
@section('content')

@include('admin.metrics.layouts._metrics-styles')

@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        <strong>{{ Session::get('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
    </div>
@endif

<div class="crm-page">

    {{-- Header --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-trophy"></i> Reglas de Recompensas</h2>
            <div class="sub">{{ $rewards->count() }} reglas configuradas</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.metrics.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Métricas
            </a>
            <a href="{{ route('admin.rewards.grants.index') }}" class="action-btn warning">
                <i class="fa fa-gift"></i> Otorgar Recompensas
            </a>
            @if(Auth::user()->isSuperAdmin())
            <a href="{{ route('admin.rewards.create') }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nueva Regla
            </a>
            @endif
        </div>
    </div>

    {{-- Table card --}}
    <div class="dashboard-card">
        <div class="dc-header" style="background:#1a1a2e;">
            <h5 style="color:#fff;"><i class="fa fa-trophy" style="color:#c2ac1f;"></i> Reglas configuradas</h5>
        </div>

        <div style="overflow-x:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th style="text-align:center;">Rango Conversiones</th>
                        <th style="text-align:center;">Valor</th>
                        <th style="text-align:center;">Estado</th>
                        @if(Auth::user()->isSuperAdmin())
                        <th style="text-align:center;">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @forelse($rewards as $reward)
                    @php $typeInfo = \App\Models\AgentReward::types()[$reward->reward_type] ?? null; @endphp
                    <tr style="{{ !$reward->is_active ? 'opacity:.55;' : '' }}">
                        <td>
                            <div style="font-weight:600;color:#1a1a2e;">{{ $reward->name }}</div>
                            @if($reward->description)
                                <div style="font-size:12px;color:#aaa;margin-top:2px;">{{ Str::limit($reward->description, 70) }}</div>
                            @endif
                        </td>
                        <td>
                            @if($typeInfo)
                                <span class="crm-badge contacted"><i class="fa {{ $typeInfo['icon'] }}"></i> {{ $typeInfo['label'] }}</span>
                            @else
                                <span style="color:#888;">{{ $reward->reward_type }}</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <span class="crm-badge medium">
                                {{ $reward->min_conversions }}
                                @if($reward->max_conversions) — {{ $reward->max_conversions }}
                                @else +
                                @endif
                                conv.
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <strong style="font-size:15px;color:#1a1a2e;">{{ $reward->formatted_value }}</strong>
                        </td>
                        <td style="text-align:center;">
                            @if($reward->is_active)
                                <span class="crm-badge won"><i class="fa fa-check"></i> Activa</span>
                            @else
                                <span class="crm-badge lost"><i class="fa fa-times"></i> Inactiva</span>
                            @endif
                        </td>
                        @if(Auth::user()->isSuperAdmin())
                        <td style="text-align:center;">
                            <div style="display:flex;justify-content:center;gap:6px;flex-wrap:nowrap;">
                                <form method="POST" action="{{ route('admin.rewards.toggle', $reward) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="action-btn {{ $reward->is_active ? 'secondary' : 'success' }}"
                                            title="{{ $reward->is_active ? 'Desactivar' : 'Activar' }}" style="padding:5px 9px;font-size:12px;">
                                        <i class="fa {{ $reward->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.rewards.edit', $reward) }}" class="action-btn view" style="padding:5px 9px;font-size:12px;">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.rewards.destroy', $reward) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta regla de recompensa?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn danger" style="padding:5px 9px;font-size:12px;">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ Auth::user()->isSuperAdmin() ? 6 : 5 }}" style="text-align:center;padding:60px 20px;">
                            <i class="fa fa-trophy" style="font-size:42px;color:#ddd;display:block;margin-bottom:14px;"></i>
                            <div style="font-size:15px;font-weight:600;color:#aaa;margin-bottom:6px;">No hay reglas configuradas</div>
                            @if(Auth::user()->isSuperAdmin())
                                <a href="{{ route('admin.rewards.create') }}" class="action-btn primary" style="margin-top:10px;">
                                    <i class="fa fa-plus"></i> Crear Primera Regla
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
