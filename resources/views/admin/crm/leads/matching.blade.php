@extends('admin.main')
@section('title', 'Propiedades Compatibles')
@section('content')
@include('admin.crm.layouts._crm-styles')
<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-home"></i> Propiedades Compatibles</h2>
            <div class="sub">Lead: <strong>{{ $lead->name }}</strong></div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.leads.show', $lead) }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver al lead
            </a>
        </div>
    </div>

    {{-- Lead preferences summary --}}
    <div class="dashboard-card" style="margin-bottom:18px;">
        <div class="dc-header"><h5><i class="fa fa-sliders" style="color:#c2ac1f;"></i> Preferencias del Lead</h5></div>
        <div class="dc-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase;font-weight:700;">Presupuesto</div>
                <div style="font-weight:600;color:#1a1a2e;">{{ $lead->budget_range }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase;font-weight:700;">Habitaciones</div>
                <div style="font-weight:600;color:#1a1a2e;">
                    @if($lead->preferred_bedrooms_min || $lead->preferred_bedrooms_max)
                        {{ $lead->preferred_bedrooms_min ?? '—' }} – {{ $lead->preferred_bedrooms_max ?? '—' }}
                    @else
                        Sin preferencia
                    @endif
                </div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase;font-weight:700;">Zonas</div>
                <div style="font-weight:600;color:#1a1a2e;">
                    {{ !empty($lead->preferred_zones) ? implode(', ', $lead->preferred_zones) : 'Sin preferencia' }}
                </div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase;font-weight:700;">Interés</div>
                <div style="font-weight:600;color:#1a1a2e;">{{ $lead->interest_type_label }}</div>
            </div>
        </div>
    </div>

    {{-- Matched properties --}}
    <div class="dashboard-card">
        <div class="dc-header">
            <h5><i class="fa fa-search" style="color:#c2ac1f;"></i> Resultados ({{ $matched->count() }})</h5>
        </div>
        @if($matched->isEmpty())
        <div class="dc-body" style="text-align:center;padding:48px;">
            <i class="fa fa-home" style="font-size:36px;opacity:.3;display:block;margin-bottom:10px;"></i>
            <p style="color:#aaa;font-size:14px;">No se encontraron propiedades compatibles con las preferencias del lead.</p>
        </div>
        @else
        <div style="overflow-x:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Propiedad</th>
                        <th style="text-align:center;">Compatibilidad</th>
                        <th>Precio</th>
                        <th>Sector</th>
                        <th>Habs.</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($matched as $item)
                @php
                    $property = $item['property'];
                    $score    = $item['score'];
                    $barColor = $score >= 70 ? '#22c55e' : ($score >= 40 ? '#f59e0b' : '#ef4444');
                @endphp
                <tr>
                    <td>
                        <a href="#" style="font-weight:600;color:#1a1a2e;text-decoration:none;">
                            {{ Str::limit($property->title ?? $property->name ?? 'Propiedad #' . $property->id, 40) }}
                        </a>
                    </td>
                    <td style="text-align:center;min-width:120px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:6px;background:#f0f0f0;border-radius:3px;overflow:hidden;">
                                <div style="height:100%;width:{{ $score }}%;background:{{ $barColor }};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:12px;font-weight:700;color:{{ $barColor }};min-width:30px;">{{ $score }}%</span>
                        </div>
                    </td>
                    <td style="font-size:13px;font-weight:600;">
                        {{ ($property->currency ?? 'CRC') === 'USD' ? '$' : '₡' }}{{ number_format($property->price ?? $property->sale_price ?? 0) }}
                    </td>
                    <td style="font-size:13px;color:#666;">{{ $property->sector?->name ?? '—' }}</td>
                    <td style="text-align:center;font-size:13px;">{{ $property->bedrooms ?? '—' }}</td>
                    <td>
                        <a href="{{ route('admin.crm.leads.show', $lead) }}" class="action-btn view">
                            <i class="fa fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
