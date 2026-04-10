@extends('admin.main')
@section('title', 'Reglas de Recompensas')
@section('content')

@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <strong>{{ Session::get('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
    </div>
@endif

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('admin.metrics.index') }}" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left"></i> Métricas
            </a>
            <a href="{{ route('admin.rewards.grants.index') }}" class="btn btn-sm btn-warning ml-2">
                <i class="fa fa-gift"></i> Otorgar Recompensas
            </a>
        </div>
        @if(Auth::user()->isSuperAdmin())
        <a href="{{ route('admin.rewards.create') }}" class="btn btn-success">
            <i class="fa fa-plus"></i> Nueva Regla
        </a>
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">
            <i class="fa fa-trophy"></i> Reglas de Recompensas
            <small class="ml-2 text-muted">({{ $rewards->count() }} reglas configuradas)</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th class="text-center">Rango Conversiones</th>
                            <th class="text-center">Valor</th>
                            <th class="text-center">Estado</th>
                            @if(Auth::user()->isSuperAdmin())
                            <th class="text-center">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($rewards as $reward)
                        @php $typeInfo = \App\Models\AgentReward::types()[$reward->reward_type] ?? null; @endphp
                        <tr class="{{ !$reward->is_active ? 'table-secondary text-muted' : '' }}">
                            <td>
                                <strong>{{ $reward->name }}</strong>
                                @if($reward->description)
                                    <br><small class="text-muted">{{ Str::limit($reward->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($typeInfo)
                                    <i class="fa {{ $typeInfo['icon'] }}"></i> {{ $typeInfo['label'] }}
                                @else
                                    {{ $reward->reward_type }}
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary">
                                    {{ $reward->min_conversions }}
                                    @if($reward->max_conversions)
                                        — {{ $reward->max_conversions }}
                                    @else
                                        +
                                    @endif
                                    conversiones
                                </span>
                            </td>
                            <td class="text-center">
                                <strong>{{ $reward->formatted_value }}</strong>
                            </td>
                            <td class="text-center">
                                @if($reward->is_active)
                                    <span class="badge badge-success"><i class="fa fa-check"></i> Activa</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fa fa-times"></i> Inactiva</span>
                                @endif
                            </td>
                            @if(Auth::user()->isSuperAdmin())
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.rewards.toggle', $reward) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs {{ $reward->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                                            title="{{ $reward->is_active ? 'Desactivar' : 'Activar' }}">
                                        <i class="fa {{ $reward->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.rewards.edit', $reward) }}" class="btn btn-xs btn-outline-primary">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.rewards.destroy', $reward) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta regla de recompensa?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->isSuperAdmin() ? 6 : 5 }}" class="text-center text-muted py-5">
                                <i class="fa fa-trophy fa-2x mb-2 d-block text-warning"></i>
                                No hay reglas de recompensas configuradas aún.
                                @if(Auth::user()->isSuperAdmin())
                                    <br><a href="{{ route('admin.rewards.create') }}" class="btn btn-sm btn-success mt-2">Crear Primera Regla</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
