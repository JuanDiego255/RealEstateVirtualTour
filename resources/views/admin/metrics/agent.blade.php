@extends('admin.main')
@section('title', 'Métricas — ' . $user->name)
@section('content')

@include('admin.metrics.layouts._metrics-styles')

<style>
/* ── Page-level overrides / additions ── */

/* Month filter inside header */
.agent-filter-form {
    display: flex;
    align-items: center;
    gap: 8px;
}
.agent-filter-form input[type="month"] {
    border: 1.5px solid #e5e7eb; border-radius: 9px;
    padding: 6px 12px; font-size: 13px; color: #1a1a2e;
    outline: none; background: #fafafa;
    transition: border-color .15s;
}
.agent-filter-form input[type="month"]:focus { border-color: #c2ac1f; background: #fff; }

/* Agent avatar icon box */
.agent-avatar {
    width: 48px; height: 48px; border-radius: 13px;
    background: linear-gradient(135deg, #1a1a2e, #2d2d4e);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.agent-avatar i { color: #c2ac1f; font-size: 22px; }

/* Agent badge */
.agent-name-badge {
    display: inline-block;
    background: #e0e7ff; color: #4338ca;
    font-size: 11px; font-weight: 700;
    padding: 2px 9px; border-radius: 20px;
    text-transform: uppercase; letter-spacing: .3px;
    margin-left: 8px; vertical-align: middle;
}

/* Two-column main layout */
.agent-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    align-items: start;
}
@media (max-width: 900px) {
    .agent-layout { grid-template-columns: 1fr; }
}

/* Right column stack */
.agent-sidebar { display: flex; flex-direction: column; gap: 0; }

/* Mini progress bars in stat cards */
.mini-progress {
    height: 6px; border-radius: 3px;
    background: #f0f0f0; overflow: hidden; margin-top: 6px;
}
.mini-progress-bar { height: 100%; border-radius: 3px; transition: width .4s; }

/* Scrollable leads table */
.leads-table-wrap {
    overflow-x: auto;
    max-height: 520px;
    overflow-y: auto;
}
.leads-table-wrap thead th {
    position: sticky; top: 0; z-index: 1;
    background: #fafafa;
}

/* Interest level pills not in shared styles */
.badge-hot    { background: #fee2e2; color: #991b1b; }
.badge-high   { background: #fef3c7; color: #92400e; }
.badge-medium { background: #dbeafe; color: #1e40af; }
.badge-low    { background: #f1f5f9; color: #64748b; }

/* Category row in sidebar */
.cat-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: 13px;
}
.cat-row:last-child { border-bottom: none; }
.cat-row .cat-count {
    background: #f1f5f9; color: #475569;
    font-size: 11px; font-weight: 700;
    padding: 2px 8px; border-radius: 20px;
}

/* Dark card header variant */
.dc-header-dark    { background: linear-gradient(135deg, #1a1a2e, #2d2d4e); border-bottom: none; }
.dc-header-dark h5 { color: #fff; }
.dc-header-dark h5 i { color: #c2ac1f; }

/* Modal styling overrides */
.fu-modal-header {
    background: linear-gradient(135deg, #1a1a2e, #2d2d4e);
    padding: 16px 20px;
    display: flex; justify-content: space-between; align-items: center;
}
.fu-modal-header h5 {
    color: #fff; font-size: 15px; font-weight: 600;
    margin: 0; display: flex; align-items: center; gap: 8px;
}
.fu-modal-header h5 i { color: #c2ac1f; }
.fu-modal-close {
    background: none; border: none; color: rgba(255,255,255,.7);
    font-size: 20px; cursor: pointer; line-height: 1;
    padding: 0 4px; transition: color .15s;
}
.fu-modal-close:hover { color: #fff; }
.fu-form-group { margin-bottom: 16px; }
.fu-form-group label {
    display: block; font-size: 12px; font-weight: 700;
    color: #888; text-transform: uppercase; letter-spacing: .4px;
    margin-bottom: 6px;
}
.fu-form-group select,
.fu-form-group textarea,
.fu-form-group input[type="datetime-local"] {
    width: 100%;
    border: 1.5px solid #e5e7eb; border-radius: 9px;
    padding: 8px 12px; font-size: 13px; color: #1a1a2e;
    outline: none; transition: border-color .15s;
    background: #fafafa;
}
.fu-form-group select:focus,
.fu-form-group textarea:focus,
.fu-form-group input[type="datetime-local"]:focus {
    border-color: #c2ac1f; background: #fff;
}
</style>

@if(Session::has('success'))
<div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:12px;
            padding:12px 18px;margin-bottom:16px;display:flex;align-items:center;
            gap:10px;color:#065f46;font-size:13.5px;">
    <i class="fa fa-check-circle" style="color:#22c55e;"></i>
    <strong>{{ Session::get('success') }}</strong>
</div>
@endif

<div class="crm-page">

    {{-- ══════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════ --}}
    <div class="crm-page-header">
        <div style="display:flex;align-items:center;gap:14px;">
            <div class="agent-avatar">
                <i class="fa fa-user"></i>
            </div>
            <div>
                <h2 style="margin-bottom:2px;">
                    {{ $user->name }}
                    <span class="agent-name-badge">Agente</span>
                </h2>
                <div class="sub">
                    <i class="fa fa-calendar"></i>
                    {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                </div>
            </div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.metrics.index', ['month' => $month]) }}"
               class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
            <form method="GET" class="agent-filter-form">
                <input type="hidden" name="user" value="{{ $user->id }}">
                <input type="month" name="month" value="{{ $month }}">
                <button type="submit" class="action-btn primary">
                    <i class="fa fa-filter"></i> Filtrar
                </button>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         STAT CARDS  (3 cards)
    ══════════════════════════════════════════ --}}
    @php
        $leadsCount       = $leads->count();
        $quotesCount      = $quotes->count();
        $conversionsCount = $leads->where('sale_status', 'converted')->count();

        $statCards = [
            [
                'label'  => 'Leads Capturados',
                'count'  => $leadsCount,
                'goal'   => $goal?->leads_goal,
                'icon'   => 'fa-users',
                'color'  => 'blue',
            ],
            [
                'label'  => 'Cotizaciones',
                'count'  => $quotesCount,
                'goal'   => $goal?->quotes_goal,
                'icon'   => 'fa-calculator',
                'color'  => 'indigo',
            ],
            [
                'label'  => 'Conversiones',
                'count'  => $conversionsCount,
                'goal'   => $goal?->conversions_goal,
                'icon'   => 'fa-check-circle',
                'color'  => 'green',
            ],
        ];
    @endphp

    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px;">
        @foreach($statCards as $sc)
            @php
                $scPct = ($sc['goal'] > 0)
                    ? min(100, round(($sc['count'] / $sc['goal']) * 100))
                    : null;
                $barColor = $scPct === null ? '#94a3b8'
                    : ($scPct >= 100 ? '#22c55e' : ($scPct >= 70 ? '#eab308' : '#ef4444'));
            @endphp
            <div class="stat-card">
                <div class="sc-icon {{ $sc['color'] }}">
                    <i class="fa {{ $sc['icon'] }}"></i>
                </div>
                <div class="sc-value">{{ $sc['count'] }}</div>
                <div class="sc-label">{{ $sc['label'] }}</div>
                @if($sc['goal'])
                    <div class="sc-sub">Meta: {{ $sc['goal'] }}</div>
                    <div class="mini-progress">
                        <div class="mini-progress-bar"
                             style="width:{{ $scPct }}%;background:{{ $barColor }};"></div>
                    </div>
                    <div style="font-size:11px;color:{{ $barColor }};font-weight:600;margin-top:3px;">
                        {{ $scPct }}%
                    </div>
                @else
                    <div class="sc-sub" style="color:#bbb;">Sin meta asignada</div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════
         MAIN TWO-COLUMN LAYOUT
    ══════════════════════════════════════════ --}}
    <div class="agent-layout">

        {{-- ── LEFT: Leads table ── --}}
        <div class="dashboard-card" style="margin-bottom:0;">
            <div class="dc-header dc-header-dark">
                <h5>
                    <i class="fa fa-users"></i>
                    Leads Capturados
                </h5>
                <span style="background:rgba(194,172,31,.2);color:#c2ac1f;
                             padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                    {{ $leadsCount }}
                </span>
            </div>

            <div class="leads-table-wrap">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th>Nombre / Teléfono</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Interés</th>
                            <th>Fecha</th>
                            <th style="text-align:center;">Seguims.</th>
                            <th style="width:90px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($leads as $lead)
                        @php
                            $catDefs    = \App\Models\EventLead::leadCategories();
                            $statDefs   = \App\Models\EventLead::saleStatuses();
                            $catLabel   = $catDefs[$lead->lead_category]['label']  ?? ($lead->lead_category ?? '—');
                            $statusInfo = $statDefs[$lead->sale_status] ?? null;

                            // Map bootstrap badge colors to crm-badge classes
                            $statusBadgeMap = [
                                'secondary' => 'low',
                                'info'      => 'contacted',
                                'success'   => 'won',
                                'danger'    => 'lost',
                                'warning'   => 'proposal',
                            ];
                            $statusBadge = $statusInfo
                                ? ($statusBadgeMap[$statusInfo['color']] ?? 'low')
                                : 'low';

                            $interestClass = match($lead->interest_level) {
                                'hot'    => 'badge-hot',
                                'high'   => 'badge-high',
                                'medium' => 'badge-medium',
                                default  => 'badge-low',
                            };
                            $interestLabel = match($lead->interest_level) {
                                'hot'    => 'Hot',
                                'high'   => 'Alto',
                                'medium' => 'Medio',
                                default  => ucfirst($lead->interest_level ?? 'Bajo'),
                            };
                        @endphp
                        <tr>
                            <td>
                                <strong style="color:#1a1a2e;">{{ $lead->name }}</strong><br>
                                <span style="font-size:12px;color:#999;">{{ $lead->phone }}</span>
                            </td>
                            <td>
                                @if($lead->lead_category)
                                    <span class="crm-badge low">{{ $catLabel }}</span>
                                @else
                                    <span style="color:#ccc;">—</span>
                                @endif
                            </td>
                            <td>
                                @if($statusInfo)
                                    <span class="crm-badge {{ $statusBadge }}">
                                        <i class="fa {{ $statusInfo['icon'] }}" style="font-size:10px;"></i>
                                        {{ $statusInfo['label'] }}
                                    </span>
                                @else
                                    <span class="crm-badge low">{{ $lead->sale_status }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="crm-badge {{ $interestClass }}">
                                    {{ $interestLabel }}
                                </span>
                            </td>
                            <td style="font-size:12px;color:#888;white-space:nowrap;">
                                {{ $lead->created_at->format('d/m/Y') }}
                            </td>
                            <td style="text-align:center;">
                                <span class="crm-badge contacted">
                                    {{ $lead->followups->count() }}
                                </span>
                            </td>
                            <td>
                                <button type="button"
                                        class="action-btn more"
                                        data-toggle="modal"
                                        data-target="#followupModal"
                                        data-lead-id="{{ $lead->id }}"
                                        data-lead-name="{{ $lead->name }}"
                                        data-lead-status="{{ $lead->sale_status }}">
                                    <i class="fa fa-plus"></i> Seguimiento
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;color:#aaa;padding:36px;">
                                <i class="fa fa-inbox"
                                   style="font-size:22px;display:block;margin-bottom:8px;"></i>
                                No hay leads para este mes.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>{{-- /.leads card --}}

        {{-- ── RIGHT: Sidebar ── --}}
        <div class="agent-sidebar">

            {{-- Por Categoría ── --}}
            <div class="dashboard-card">
                <div class="dc-header">
                    <h5><i class="fa fa-pie-chart"></i> Por Categoría</h5>
                </div>
                <div class="dc-body">
                    @forelse($byCategory as $cat => $cnt)
                        @php
                            $cats2  = \App\Models\EventLead::leadCategories();
                            $lbl2   = $cats2[$cat]['label'] ?? ucfirst($cat);
                        @endphp
                        <div class="cat-row">
                            <span>{{ $lbl2 }}</span>
                            <span class="cat-count">{{ $cnt }}</span>
                        </div>
                    @empty
                        <p style="font-size:13px;color:#aaa;text-align:center;margin:12px 0;">
                            Sin datos de categoría
                        </p>
                    @endforelse
                    <canvas id="categoryChart" height="160" style="margin-top:16px;"></canvas>
                </div>
            </div>

            {{-- Recompensas ── --}}
            @if($grants->count() > 0)
            <div class="dashboard-card">
                <div class="dc-header" style="background:linear-gradient(135deg,#1a1a2e,#2d2d4e);border-bottom:none;">
                    <h5 style="color:#fff;">
                        <i class="fa fa-trophy" style="color:#c2ac1f;"></i>
                        Recompensas
                    </h5>
                    <span style="background:rgba(194,172,31,.2);color:#c2ac1f;
                                 padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">
                        {{ $grants->count() }}
                    </span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Recompensa</th>
                                <th style="text-align:right;">Mes</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($grants as $grant)
                            <tr>
                                <td>
                                    <span class="crm-badge proposal"
                                          style="display:inline-flex;align-items:center;gap:4px;">
                                        <i class="fa fa-trophy"></i>
                                        {{ $grant->reward?->name ?? '—' }}
                                    </span>
                                </td>
                                <td style="text-align:right;font-size:12px;color:#999;">
                                    {{ \Carbon\Carbon::parse($grant->month)->translatedFormat('M Y') }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Historial 12 meses ── --}}
            @if($history->count() > 0)
            <div class="dashboard-card">
                <div class="dc-header">
                    <h5><i class="fa fa-history"></i> Historial (12 meses)</h5>
                </div>
                <div style="overflow-x:auto;">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th style="text-align:center;">Leads</th>
                                <th style="text-align:center;">Conv.</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($history as $h)
                            <tr>
                                <td style="font-size:12px;white-space:nowrap;">
                                    {{ \Carbon\Carbon::parse($h['goal']->month)->translatedFormat('M Y') }}
                                </td>
                                <td style="text-align:center;font-size:13px;font-weight:600;color:#1a1a2e;">
                                    {{ $h['leads'] }}
                                </td>
                                <td style="text-align:center;">
                                    <span class="crm-badge {{ $h['conversions'] > 0 ? 'won' : 'low' }}">
                                        {{ $h['conversions'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>{{-- /.agent-sidebar --}}

    </div>{{-- /.agent-layout --}}

</div>{{-- /.crm-page --}}

{{-- ══════════════════════════════════════════
     FOLLOWUP MODAL
══════════════════════════════════════════ --}}
<div class="modal fade" id="followupModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border:none;border-radius:16px;overflow:hidden;">

            <div class="fu-modal-header">
                <h5><i class="fa fa-phone"></i> Registrar Seguimiento</h5>
                <button type="button" class="fu-modal-close" data-dismiss="modal">
                    &times;
                </button>
            </div>

            <form id="followupForm">
                @csrf
                <input type="hidden" id="followupLeadId" name="lead_id">

                <div style="padding:20px;">

                    <div style="background:#f8f9fb;border-radius:10px;padding:10px 14px;
                                margin-bottom:18px;font-size:13.5px;color:#1a1a2e;">
                        <i class="fa fa-user" style="color:#c2ac1f;margin-right:6px;"></i>
                        <strong id="followupLeadName"></strong>
                    </div>

                    <div class="fu-form-group">
                        <label>Tipo de contacto *</label>
                        <select name="action" required>
                            @foreach(\App\Models\LeadFollowup::actions() as $key => $action)
                                <option value="{{ $key }}">{{ $action['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fu-form-group">
                        <label>Resultado *</label>
                        <select name="outcome" required>
                            @foreach(\App\Models\LeadFollowup::outcomes() as $key => $outcome)
                                <option value="{{ $key }}">{{ $outcome['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fu-form-group">
                        <label>Notas</label>
                        <textarea name="notes" rows="3"
                                  placeholder="Detalles del contacto..."></textarea>
                    </div>

                    <div class="fu-form-group" style="margin-bottom:0;">
                        <label>Próximo seguimiento</label>
                        <input type="datetime-local" name="next_followup_at">
                    </div>

                </div>

                <div style="padding:12px 20px 16px;border-top:1px solid #f0f0f0;
                            display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" class="action-btn secondary" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="action-btn primary">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('script')
<script>
// ── Followup Modal ──────────────────────────────────────────
$('#followupModal').on('show.bs.modal', function (e) {
    var btn = $(e.relatedTarget);
    $('#followupLeadId').val(btn.data('lead-id'));
    $('#followupLeadName').text(btn.data('lead-name'));
});

$('#followupForm').on('submit', function (e) {
    e.preventDefault();
    var leadId = $('#followupLeadId').val();
    var url    = '/admin/event-leads/' + leadId + '/followups';
    var data   = $(this).serialize();

    $.post(url, data, function (res) {
        if (res.success) {
            $('#followupModal').modal('hide');
            location.reload();
        }
    }).fail(function (xhr) {
        alert('Error al guardar: ' + (xhr.responseJSON?.message || 'Error desconocido'));
    });
});

// ── Category Doughnut Chart ─────────────────────────────────
@php
    $catKeys    = $byCategory->keys()->toArray();
    $catVals    = $byCategory->values()->toArray();
    $catDefs    = \App\Models\EventLead::leadCategories();
    $catLabels  = array_map(fn($k) => $catDefs[$k]['label'] ?? ucfirst($k), $catKeys);
@endphp
(function () {
    const ctx = document.getElementById('categoryChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($catLabels),
            datasets: [{
                data: @json($catVals),
                backgroundColor: [
                    '#3b82f6', '#22c55e', '#6366f1',
                    '#eab308', '#ef4444', '#94a3b8'
                ],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11 }, padding: 14 }
                }
            },
            cutout: '62%',
        }
    });
})();
</script>
@endpush

@endsection
