@extends('admin.main')
@section('title', 'Metas de Agentes')
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
            <h2><i class="fa fa-bullseye"></i> Metas Mensuales</h2>
            <div class="sub">
                {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                — configura las metas de conversión por agente
            </div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.metrics.index', ['month' => $month]) }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Dashboard
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

    {{-- Goals table card --}}
    <div class="dashboard-card">
        <div class="dc-header" style="background:#1a1a2e;">
            <h5 style="color:#fff;">
                <i class="fa fa-bullseye" style="color:#c2ac1f;"></i>
                Metas para {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
            </h5>
            @if($agents->count() > 0)
            <button type="submit" form="goals-form" class="action-btn gold" style="font-size:12px;">
                <i class="fa fa-save"></i> Guardar Metas
            </button>
            @endif
        </div>

        <form id="goals-form" method="POST" action="{{ route('admin.metrics.goals.bulk-store') }}">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">

            <div style="overflow-x:auto;">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th>Agente</th>
                            <th style="text-align:center; min-width:160px;">
                                <i class="fa fa-users" style="color:#3b82f6;"></i> Leads
                                <br><span style="font-size:10px;color:#bbb;font-weight:400;">Meta / Real</span>
                            </th>
                            <th style="text-align:center; min-width:160px;">
                                <i class="fa fa-calculator" style="color:#6366f1;"></i> Cotizaciones
                                <br><span style="font-size:10px;color:#bbb;font-weight:400;">Meta / Real</span>
                            </th>
                            <th style="text-align:center; min-width:160px;">
                                <i class="fa fa-check-circle" style="color:#22c55e;"></i> Conversiones
                                <br><span style="font-size:10px;color:#bbb;font-weight:400;">Meta / Real</span>
                            </th>
                            <th style="text-align:center;">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($agents as $agent)
                        @php
                            $goal    = $goals->get($agent->id);
                            $metrics = $goal ? $goal->metrics() : ['leads'=>0,'quotes'=>0,'conversions'=>0];
                        @endphp
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:36px;height:36px;border-radius:10px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:14px;color:#888;font-weight:700;flex-shrink:0;">
                                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600;color:#1a1a2e;font-size:13.5px;">{{ $agent->name }}</div>
                                        <div style="font-size:11px;color:#aaa;">{{ $agent->email }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Leads goal --}}
                            <td style="text-align:center;">
                                @php
                                    $val = $goal?->leads_goal ?? 0;
                                    $real = $metrics['leads'];
                                    $pct = ($val > 0) ? min(100, round(($real/$val)*100)) : null;
                                    $barCol = $pct === null ? '#d1d5db' : ($pct >= 100 ? '#22c55e' : ($pct >= 70 ? '#f59e0b' : '#ef4444'));
                                @endphp
                                <input type="number" name="goals[{{ $agent->id }}][leads_goal]"
                                       value="{{ $val }}"
                                       class="goal-input" min="0" max="9999" style="width:80px;text-align:center;border:1px solid #e5e7eb;border-radius:8px;padding:5px 8px;font-size:13px;">
                                <div style="font-size:11px;color:#888;margin-top:3px;">Real: <strong style="color:#1a1a2e;">{{ $real }}</strong></div>
                                @if($pct !== null)
                                <div style="height:4px;background:#f0f0f0;border-radius:4px;margin-top:5px;overflow:hidden;">
                                    <div style="height:100%;width:{{ $pct }}%;background:{{ $barCol }};border-radius:4px;transition:width .4s;"></div>
                                </div>
                                <div style="font-size:10px;color:{{ $barCol }};font-weight:600;margin-top:2px;">{{ $pct }}%</div>
                                @endif
                            </td>

                            {{-- Quotes goal --}}
                            <td style="text-align:center;">
                                @php
                                    $val = $goal?->quotes_goal ?? 0;
                                    $real = $metrics['quotes'];
                                    $pct = ($val > 0) ? min(100, round(($real/$val)*100)) : null;
                                    $barCol = $pct === null ? '#d1d5db' : ($pct >= 100 ? '#22c55e' : ($pct >= 70 ? '#f59e0b' : '#ef4444'));
                                @endphp
                                <input type="number" name="goals[{{ $agent->id }}][quotes_goal]"
                                       value="{{ $val }}"
                                       class="goal-input" min="0" max="9999" style="width:80px;text-align:center;border:1px solid #e5e7eb;border-radius:8px;padding:5px 8px;font-size:13px;">
                                <div style="font-size:11px;color:#888;margin-top:3px;">Real: <strong style="color:#1a1a2e;">{{ $real }}</strong></div>
                                @if($pct !== null)
                                <div style="height:4px;background:#f0f0f0;border-radius:4px;margin-top:5px;overflow:hidden;">
                                    <div style="height:100%;width:{{ $pct }}%;background:{{ $barCol }};border-radius:4px;transition:width .4s;"></div>
                                </div>
                                <div style="font-size:10px;color:{{ $barCol }};font-weight:600;margin-top:2px;">{{ $pct }}%</div>
                                @endif
                            </td>

                            {{-- Conversions goal --}}
                            <td style="text-align:center;">
                                @php
                                    $val = $goal?->conversions_goal ?? 0;
                                    $real = $metrics['conversions'];
                                    $pct = ($val > 0) ? min(100, round(($real/$val)*100)) : null;
                                    $barCol = $pct === null ? '#d1d5db' : ($pct >= 100 ? '#22c55e' : ($pct >= 70 ? '#f59e0b' : '#ef4444'));
                                @endphp
                                <input type="number" name="goals[{{ $agent->id }}][conversions_goal]"
                                       value="{{ $val }}"
                                       class="goal-input" min="0" max="9999" style="width:80px;text-align:center;border:1px solid #e5e7eb;border-radius:8px;padding:5px 8px;font-size:13px;">
                                <div style="font-size:11px;color:#888;margin-top:3px;">Real: <strong style="color:#1a1a2e;">{{ $real }}</strong></div>
                                @if($pct !== null)
                                <div style="height:4px;background:#f0f0f0;border-radius:4px;margin-top:5px;overflow:hidden;">
                                    <div style="height:100%;width:{{ $pct }}%;background:{{ $barCol }};border-radius:4px;transition:width .4s;"></div>
                                </div>
                                <div style="font-size:10px;color:{{ $barCol }};font-weight:600;margin-top:2px;">{{ $pct }}%</div>
                                @endif
                            </td>

                            <td style="text-align:center;">
                                <a href="{{ route('admin.metrics.agent', [$agent->id, 'month' => $month]) }}"
                                   class="action-btn view" title="Ver detalle del agente">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:60px 20px;">
                                <i class="fa fa-users" style="font-size:40px;color:#ddd;display:block;margin-bottom:14px;"></i>
                                <div style="font-size:15px;font-weight:600;color:#aaa;">No hay agentes registrados</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($agents->count() > 0)
            <div style="padding:14px 18px;border-top:1px solid #f0f0f0;display:flex;justify-content:flex-end;">
                <button type="submit" class="action-btn success">
                    <i class="fa fa-save"></i> Guardar Todas las Metas
                </button>
            </div>
            @endif
        </form>
    </div>

</div>
@endsection
