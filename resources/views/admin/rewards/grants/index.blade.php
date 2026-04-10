@extends('admin.main')
@section('title', 'Gestión de Recompensas')
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
            <a href="{{ route('admin.rewards.index') }}" class="btn btn-sm btn-outline-warning ml-2">
                <i class="fa fa-trophy"></i> Reglas
            </a>
        </div>
        <form method="GET" class="form-inline">
            @if(Auth::user()->isSuperAdmin())
                <input type="number" name="company_id" value="{{ $companyId }}" class="form-control form-control-sm mr-2" placeholder="Company ID">
            @endif
            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm mr-2">
            <button class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
        </form>
    </div>

    <div class="row">

        {{-- ===== OTORGAR RECOMPENSA ===== --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="fa fa-gift"></i> Otorgar Recompensa
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.rewards.grants.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Agente *</label>
                            <select name="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">Seleccionar agente...</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ old('user_id') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label>Recompensa *</label>
                            <select name="reward_id" class="form-control @error('reward_id') is-invalid @enderror" required>
                                <option value="">Seleccionar recompensa...</option>
                                @foreach($rewards as $reward)
                                    @php $typeInfo = \App\Models\AgentReward::types()[$reward->reward_type] ?? null; @endphp
                                    <option value="{{ $reward->id }}" {{ old('reward_id') == $reward->id ? 'selected' : '' }}>
                                        {{ $reward->name }} — {{ $reward->formatted_value }}
                                        ({{ $reward->min_conversions }}{{ $reward->max_conversions ? '-'.$reward->max_conversions : '+' }} conv.)
                                    </option>
                                @endforeach
                            </select>
                            @error('reward_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label>Mes *</label>
                            <input type="month" name="month" value="{{ old('month', $month) }}"
                                   class="form-control @error('month') is-invalid @enderror" required>
                            @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label>Notas</label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="Motivo o comentario...">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fa fa-trophy"></i> Otorgar Recompensa
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===== HISTORIAL DE GRANTS ===== --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark d-flex justify-content-between">
                    <span><i class="fa fa-list"></i> Recompensas Otorgadas</span>
                    <small>{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Agente</th>
                                    <th>Recompensa</th>
                                    <th class="text-center">Conv.</th>
                                    <th>Otorgado por</th>
                                    <th>Fecha</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($grants as $grant)
                                <tr>
                                    <td><strong>{{ $grant->agent?->name ?? '—' }}</strong></td>
                                    <td>
                                        <span class="badge badge-warning">{{ $grant->reward?->name ?? '—' }}</span>
                                        @if($grant->notes)
                                            <br><small class="text-muted">{{ $grant->notes }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success">{{ $grant->conversions_count }}</span>
                                    </td>
                                    <td><small>{{ $grant->grantedBy?->name ?? 'Sistema' }}</small></td>
                                    <td><small class="text-muted">{{ $grant->granted_at?->format('d/m/Y') }}</small></td>
                                    <td>
                                        <form method="POST"
                                              action="{{ route('admin.rewards.grants.destroy', $grant) }}"
                                              onsubmit="return confirm('¿Revocar esta recompensa?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No se han otorgado recompensas este mes.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
