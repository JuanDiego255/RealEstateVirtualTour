@extends('admin.main')
@section('title', 'Premios Ruleta')
@section('content')

    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>{{ Session::get('success') }}</strong>
            <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
        </div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>{{ Session::get('error') }}</strong>
            <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
        </div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0"><i class="fas fa-dice" style="color:#e74c3c;"></i> Premios de la Ruleta</h4>
                <small class="text-muted">Los premios agotados desaparecen automáticamente del kiosko</small>
            </div>
            <a href="{{ route('admin.raffle-prizes.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Agregar Premio
            </a>
        </div>

        @if ($prizes->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                No hay premios configurados todavía.
                <a href="{{ route('admin.raffle-prizes.create') }}">Agrega el primero aquí</a>.
            </div>
        @else
            {{-- Resumen rápido --}}
            @php
                $totalActive = $prizes->where('active', true)->count();
                $totalClaimed = $prizes->sum('claimed');
            @endphp
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center py-3">
                        <div style="font-size:28px;font-weight:700;color:#2ecc71;">{{ $totalActive }}</div>
                        <small class="text-muted">Premios activos</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center py-3">
                        <div style="font-size:28px;font-weight:700;color:#e74c3c;">{{ $totalClaimed }}</div>
                        <small class="text-muted">Total entregados</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center py-3">
                        <div style="font-size:28px;font-weight:700;color:#3498db;">{{ $prizes->count() }}</div>
                        <small class="text-muted">Tipos de premio</small>
                    </div>
                </div>
            </div>

            <div class="row">
                @foreach ($prizes as $prize)
                    @php $exhausted = $prize->quantity !== null && $prize->claimed >= $prize->quantity; @endphp
                    <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
                        <div class="card h-100 {{ !$prize->active || $exhausted ? 'border-secondary' : 'border-0 shadow-sm' }}"
                            style="{{ !$prize->active || $exhausted ? 'opacity:.65;' : '' }}">
                            <div class="card-body">
                                {{-- Color + Emoji + Nombre --}}
                                <div class="d-flex align-items-center mb-3">
                                    <div
                                        style="width:48px;height:48px;border-radius:50%;background:{{ $prize->color }};
                                        flex-shrink:0;display:flex;align-items:center;justify-content:center;
                                        font-size:22px;box-shadow:0 2px 8px {{ $prize->color }}66;">
                                        {{ $prize->emoji ?: '🎁' }}
                                    </div>
                                    <div class="ml-3" style="min-width:0;">
                                        <h6 class="mb-0 font-weight-bold text-truncate">{{ $prize->name }}</h6>
                                        <div class="mt-1">
                                            @if ($prize->active && !$exhausted)
                                                <span class="badge badge-success">Activo</span>
                                            @elseif($exhausted)
                                                <span class="badge badge-danger">Agotado</span>
                                            @else
                                                <span class="badge badge-secondary">Inactivo</span>
                                            @endif
                                            <span class="badge badge-light ml-1">Peso: {{ $prize->weight }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Cantidad / progreso --}}
                                @if ($prize->quantity === null)
                                    <p class="mb-0 text-success small">
                                        <i class="fas fa-infinity"></i> Sin límite
                                        <span class="text-muted">· {{ $prize->claimed }} entregados</span>
                                    </p>
                                @else
                                    @php $pct = $prize->quantity > 0 ? ($prize->claimed / $prize->quantity * 100) : 0; @endphp
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">{{ $prize->claimed }} / {{ $prize->quantity }}
                                            entregados</small>
                                        <small class="font-weight-bold">{{ round($pct) }}%</small>
                                    </div>
                                    <div class="progress mb-1" style="height:6px;">
                                        <div class="progress-bar {{ $pct >= 100 ? 'bg-danger' : ($pct >= 70 ? 'bg-warning' : 'bg-success') }}"
                                            style="width:{{ min($pct, 100) }}%"></div>
                                    </div>
                                    @if ($exhausted)
                                        <small class="text-danger"><i class="fas fa-times-circle"></i> Sin stock</small>
                                    @else
                                        <small class="text-success"><i class="fas fa-check-circle"></i>
                                            {{ $prize->remaining }} disponibles</small>
                                    @endif
                                @endif
                            </div>

                            <div class="card-footer bg-transparent d-flex" style="gap:.4rem;">
                                <a href="{{ route('admin.raffle-prizes.edit', $prize) }}"
                                    class="btn btn-sm btn-outline-secondary flex-fill">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form method="POST" action="{{ route('admin.raffle-prizes.reset', $prize) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-warning"
                                        title="Reiniciar contador">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.raffle-prizes.destroy', $prize) }}"
                                    onsubmit="return confirm('¿Eliminar este premio?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

@endsection
