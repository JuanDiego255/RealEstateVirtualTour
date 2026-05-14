@extends('admin.main')
@section('title', 'CRM — Pipeline')
@section('content')

@include('admin.crm.layouts._crm-styles')
<style>
/* Pipeline-specific */
.pipeline-wrap { padding: 20px; }
.pipeline-board {
    display: flex;
    gap: 14px;
    overflow-x: auto;
    padding-bottom: 16px;
    align-items: flex-start;
}
.pipeline-col {
    flex: 0 0 268px;
    min-width: 268px;
}
.pipeline-col-header {
    border-radius: 12px 12px 0 0;
    padding: 11px 14px;
    display: flex; justify-content: space-between; align-items: center;
}
.pipeline-col-header .col-title {
    font-size: 13px; font-weight: 700; letter-spacing: .3px;
}
.pipeline-col-header .col-count {
    font-size: 12px; font-weight: 700;
    background: rgba(255,255,255,.35);
    padding: 2px 9px; border-radius: 10px;
}
.pipeline-col-body {
    background: #f4f5f7;
    border-radius: 0 0 12px 12px;
    padding: 10px;
    min-height: 420px;
    max-height: 74vh;
    overflow-y: auto;
}
/* Column colors */
.col-new         .pipeline-col-header { background: #3b82f6; color: #fff; }
.col-contacted   .pipeline-col-header { background: #6366f1; color: #fff; }
.col-qualified   .pipeline-col-header { background: #8b5cf6; color: #fff; }
.col-proposal    .pipeline-col-header { background: #f59e0b; color: #fff; }
.col-negotiation .pipeline-col-header { background: #d97706; color: #fff; }
.col-won         .pipeline-col-header { background: #22c55e; color: #fff; }
.col-lost        .pipeline-col-header { background: #ef4444; color: #fff; }

/* Lead cards */
.lead-card {
    background: #fff;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 9px;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
    cursor: pointer;
    transition: transform .13s, box-shadow .13s;
    border-left: 3px solid transparent;
}
.lead-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
}
.col-new         .lead-card { border-left-color: #3b82f6; }
.col-contacted   .lead-card { border-left-color: #6366f1; }
.col-qualified   .lead-card { border-left-color: #8b5cf6; }
.col-proposal    .lead-card { border-left-color: #f59e0b; }
.col-negotiation .lead-card { border-left-color: #d97706; }
.col-won         .lead-card { border-left-color: #22c55e; }
.col-lost        .lead-card { border-left-color: #ef4444; }

.lead-card .lc-name {
    font-size: 13px; font-weight: 600;
    color: #1a1a2e;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 190px;
}
.lead-card .lc-contact {
    font-size: 11px; color: #888;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-top: 2px;
}
.lead-card .lc-meta {
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 9px;
}
.lead-card .lc-agent { font-size: 11px; color: #aaa; }
.lead-card .lc-followup { font-size: 11px; }
.lead-card .lc-property {
    font-size: 11px; color: #6366f1;
    margin-top: 4px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.empty-col {
    text-align: center; padding: 32px 16px;
    color: #ccc; font-size: 13px;
}
.empty-col i { font-size: 28px; display: block; margin-bottom: 8px; }
</style>

<div class="pipeline-wrap">

    {{-- Header --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-columns"></i> Pipeline de Leads</h2>
            <div class="sub">Vista Kanban · {{ $leadsByStatus->flatten()->count() }} leads en total</div>
        </div>
        <div class="actions">
            <div class="rt-indicator"><span class="rt-dot"></span> En vivo</div>
            <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary">
                <i class="fa fa-list"></i> Vista Lista
            </a>
            <a href="{{ route('admin.crm.leads.create') }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nuevo Lead
            </a>
        </div>
    </div>

    {{-- Resumen rápido --}}
    <div class="stats-grid" style="grid-template-columns: repeat(7, 1fr); margin-bottom: 20px;">
        @php
        $colDefs = [
            'new'         => ['label'=>'Nuevos',      'icon'=>'blue'],
            'contacted'   => ['label'=>'Contactados',  'icon'=>'indigo'],
            'qualified'   => ['label'=>'Calificados',  'icon'=>'purple'],
            'proposal'    => ['label'=>'Propuesta',    'icon'=>'yellow'],
            'negotiation' => ['label'=>'Negociación',  'icon'=>'orange'],
            'won'         => ['label'=>'Ganados',      'icon'=>'green'],
            'lost'        => ['label'=>'Perdidos',     'icon'=>'red'],
        ];
        $icons = [
            'new'=>'fa-star','contacted'=>'fa-phone','qualified'=>'fa-check-circle',
            'proposal'=>'fa-file-text','negotiation'=>'fa-handshake-o','won'=>'fa-trophy','lost'=>'fa-times-circle'
        ];
        @endphp
        @foreach($colDefs as $key => $def)
        <div class="stat-card" style="padding:14px;">
            <div class="sc-icon {{ $def['icon'] }}" style="width:36px;height:36px;font-size:15px;margin-bottom:8px;">
                <i class="fa {{ $icons[$key] }}"></i>
            </div>
            <div class="sc-value" style="font-size:22px;">{{ $leadsByStatus[$key]->count() }}</div>
            <div class="sc-label" style="font-size:11px;">{{ $def['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Board --}}
    <div class="pipeline-board">
        @foreach($statuses as $statusKey => $statusLabel)
        @php $colLeads = $leadsByStatus[$statusKey]; @endphp
        <div class="pipeline-col col-{{ $statusKey }}">
            <div class="pipeline-col-header">
                <span class="col-title">{{ $statusLabel }}</span>
                <span class="col-count">{{ $colLeads->count() }}</span>
            </div>
            <div class="pipeline-col-body pipeline-cards" data-status="{{ $statusKey }}">

                @forelse($colLeads as $lead)
                <div class="lead-card" onclick="window.location='{{ route('admin.crm.leads.show', $lead) }}'">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <span class="lc-name">{{ $lead->name }}</span>
                        <span class="crm-badge {{ $lead->priority }}" style="font-size:10px; flex-shrink:0; margin-left:6px;">{{ $lead->priority_label }}</span>
                    </div>

                    @if($lead->phone || $lead->email)
                        <div class="lc-contact"><i class="fa fa-{{ $lead->phone ? 'phone' : 'envelope' }}" style="color:#ddd;"></i> {{ $lead->phone ?? $lead->email }}</div>
                    @endif

                    @if($lead->property)
                        <div class="lc-property"><i class="fa fa-home"></i> {{ Str::limit($lead->property->title, 28) }}</div>
                    @endif
                    @if($lead->vehicle)
                        <div class="lc-property"><i class="fa fa-car"></i> {{ Str::limit($lead->vehicle->name, 28) }}</div>
                    @endif

                    <div class="lc-meta">
                        <span class="lc-agent"><i class="fa fa-user" style="color:#ddd;"></i> {{ Str::limit($lead->user->name ?? '—', 14) }}</span>
                        @if($lead->next_follow_up)
                            <span class="lc-followup {{ $lead->isOverdueForFollowUp() ? 'fu-overdue' : 'fu-ok' }}">
                                <i class="fa fa-clock-o"></i> {{ $lead->next_follow_up->format('d/m') }}
                            </span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="empty-col">
                    <i class="fa fa-inbox"></i>
                    Sin leads
                </div>
                @endforelse

            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
