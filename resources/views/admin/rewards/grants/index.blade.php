@extends('admin.main')
@section('title', 'Gestión de Recompensas')
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
            <h2><i class="fa fa-gift"></i> Otorgar Recompensas</h2>
            <div class="sub">{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.metrics.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Métricas
            </a>
            <a href="{{ route('admin.rewards.index') }}" class="action-btn warning">
                <i class="fa fa-trophy"></i> Reglas
            </a>
            <form method="GET" class="d-inline-flex align-items-center" style="gap:8px;">
                @if(Auth::user()->isSuperAdmin())
                    <input type="number" name="company_id" value="{{ $companyId }}"
                           style="border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px;font-size:13px;width:130px;"
                           placeholder="Company ID">
                @endif
                <input type="month" name="month" value="{{ $month }}"
                       style="border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px;font-size:13px;">
                <button class="action-btn primary" type="submit"><i class="fa fa-filter"></i> Filtrar</button>
            </form>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:340px 1fr;gap:22px;align-items:start;">

        {{-- Grant form --}}
        <div class="dashboard-card">
            <div class="dc-header" style="background:linear-gradient(135deg,#c2ac1f,#a89617);">
                <h5 style="color:#fff;"><i class="fa fa-gift" style="color:#fff;"></i> Otorgar Recompensa</h5>
            </div>
            <div class="dc-body">
                <form method="POST" action="{{ route('admin.rewards.grants.store') }}">
                    @csrf

                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:5px;">Agente <span style="color:#ef4444;">*</span></label>
                        <select name="user_id" required
                                style="width:100%;border:1px solid {{ $errors->has('user_id') ? '#ef4444' : '#e5e7eb' }};border-radius:8px;padding:8px 10px;font-size:13px;">
                            <option value="">Seleccionar agente...</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ old('user_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')<div style="color:#ef4444;font-size:11px;margin-top:3px;">{{ $message }}</div>@enderror
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:5px;">Recompensa <span style="color:#ef4444;">*</span></label>
                        <select name="reward_id" required
                                style="width:100%;border:1px solid {{ $errors->has('reward_id') ? '#ef4444' : '#e5e7eb' }};border-radius:8px;padding:8px 10px;font-size:13px;">
                            <option value="">Seleccionar recompensa...</option>
                            @foreach($rewards as $reward)
                                <option value="{{ $reward->id }}" {{ old('reward_id') == $reward->id ? 'selected' : '' }}>
                                    {{ $reward->name }} — {{ $reward->formatted_value }}
                                    ({{ $reward->min_conversions }}{{ $reward->max_conversions ? '-'.$reward->max_conversions : '+' }} conv.)
                                </option>
                            @endforeach
                        </select>
                        @error('reward_id')<div style="color:#ef4444;font-size:11px;margin-top:3px;">{{ $message }}</div>@enderror
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:5px;">Mes <span style="color:#ef4444;">*</span></label>
                        <input type="month" name="month" value="{{ old('month', $month) }}" required
                               style="width:100%;border:1px solid {{ $errors->has('month') ? '#ef4444' : '#e5e7eb' }};border-radius:8px;padding:8px 10px;font-size:13px;">
                        @error('month')<div style="color:#ef4444;font-size:11px;margin-top:3px;">{{ $message }}</div>@enderror
                    </div>

                    <div style="margin-bottom:18px;">
                        <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:5px;">Notas</label>
                        <textarea name="notes" rows="2" placeholder="Motivo o comentario..."
                                  style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:13px;resize:none;">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="action-btn gold" style="width:100%;justify-content:center;padding:10px;">
                        <i class="fa fa-trophy"></i> Otorgar Recompensa
                    </button>
                </form>
            </div>
        </div>

        {{-- Grants history --}}
        <div class="dashboard-card">
            <div class="dc-header" style="background:#1a1a2e;">
                <h5 style="color:#fff;"><i class="fa fa-list" style="color:#c2ac1f;"></i> Recompensas Otorgadas</h5>
                <span style="font-size:12px;color:#aaa;">{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</span>
            </div>

            <div style="overflow-x:auto;">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th>Agente</th>
                            <th>Recompensa</th>
                            <th style="text-align:center;">Conv.</th>
                            <th>Otorgado por</th>
                            <th>Fecha</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($grants as $grant)
                        <tr>
                            <td>
                                <div style="font-weight:600;color:#1a1a2e;">{{ $grant->agent?->name ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="crm-badge proposal"><i class="fa fa-trophy"></i> {{ $grant->reward?->name ?? '—' }}</span>
                                @if($grant->notes)
                                    <div style="font-size:11px;color:#aaa;margin-top:2px;">{{ $grant->notes }}</div>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <span class="crm-badge won">{{ $grant->conversions_count }}</span>
                            </td>
                            <td><span style="font-size:12px;color:#555;">{{ $grant->grantedBy?->name ?? 'Sistema' }}</span></td>
                            <td><span style="font-size:12px;color:#aaa;">{{ $grant->granted_at?->format('d/m/Y') }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.rewards.grants.destroy', $grant) }}"
                                      onsubmit="return confirm('¿Revocar esta recompensa?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn danger" style="padding:5px 9px;font-size:12px;" title="Revocar">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:50px 20px;">
                                <i class="fa fa-gift" style="font-size:38px;color:#ddd;display:block;margin-bottom:12px;"></i>
                                <div style="font-size:14px;color:#aaa;">No se han otorgado recompensas este mes.</div>
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
