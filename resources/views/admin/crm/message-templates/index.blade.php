@extends('admin.main')
@section('title', 'Plantillas de Mensajes')
@section('content')

@include('admin.crm.layouts._crm-styles')

@if (Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        <strong>{{ Session::get('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
    </div>
@endif

<style>
.channel-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
.channel-tab {
    padding: 7px 20px; border-radius: 20px; font-size: 13px; font-weight: 600;
    text-decoration: none; transition: all .15s; border: 2px solid transparent;
}
.channel-tab.active-all      { background: #1a1a2e; color: #fff; border-color: #1a1a2e; }
.channel-tab.active-whatsapp { background: #d1fae5; color: #065f46; border-color: #22c55e; }
.channel-tab.active-email    { background: #dbeafe; color: #1d4ed8; border-color: #3b82f6; }
.channel-tab.inactive        { background: #f5f5f5; color: #666; }
.channel-tab.inactive:hover  { background: #ebebeb; color: #333; text-decoration: none; }

.var-info-box {
    background: #fffdf0; border: 1px solid #f0e8a0;
    border-radius: 12px; padding: 14px 18px; margin-bottom: 22px;
    font-size: 13px; color: #7a6012;
}
.var-info-box code {
    background: #f0e8a0; border-radius: 4px;
    padding: 2px 7px; margin: 0 3px; font-size: 12px;
}
</style>

<div class="crm-page">

    {{-- Header --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-comments"></i> Plantillas de Mensajes</h2>
            <div class="sub">Plantillas de WhatsApp y Email para comunicación con leads</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver a Leads
            </a>
            <a href="{{ route('admin.crm.message-templates.create') }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nueva Plantilla
            </a>
        </div>
    </div>

    {{-- Variables info --}}
    <div class="var-info-box">
        <strong><i class="fa fa-info-circle"></i> Variables de interpolación disponibles:</strong>
        <span style="margin-left:6px;">
            <code>{{nombre}}</code> nombre del lead &nbsp;·&nbsp;
            <code>{{agente}}</code> nombre del agente &nbsp;·&nbsp;
            <code>{{propiedad}}</code> propiedad de interés &nbsp;·&nbsp;
            <code>{{fecha}}</code> fecha actual &nbsp;·&nbsp;
            <code>{{enlace}}</code> enlace al portal
        </span>
    </div>

    {{-- Stats --}}
    @php
        $waCount    = $byChannel->get('whatsapp', collect())->count();
        $emailCount = $byChannel->get('email', collect())->count();
        $total      = $waCount + $emailCount;
    @endphp
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); margin-bottom: 22px;">
        <div class="stat-card">
            <div class="sc-icon blue"><i class="fa fa-files-o"></i></div>
            <div class="sc-value">{{ $total }}</div>
            <div class="sc-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon green"><i class="fa fa-whatsapp"></i></div>
            <div class="sc-value">{{ $waCount }}</div>
            <div class="sc-label">WhatsApp</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon blue"><i class="fa fa-envelope"></i></div>
            <div class="sc-value">{{ $emailCount }}</div>
            <div class="sc-label">Email</div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    @php $activeChannel = request('channel', ''); @endphp
    <div class="channel-tabs">
        <a href="{{ route('admin.crm.message-templates.index') }}"
           class="channel-tab {{ $activeChannel==='' ? 'active-all' : 'inactive' }}">
            <i class="fa fa-th-list"></i> Todos
        </a>
        <a href="{{ route('admin.crm.message-templates.index', ['channel'=>'whatsapp']) }}"
           class="channel-tab {{ $activeChannel==='whatsapp' ? 'active-whatsapp' : 'inactive' }}">
            <i class="fa fa-whatsapp"></i> WhatsApp
        </a>
        <a href="{{ route('admin.crm.message-templates.index', ['channel'=>'email']) }}"
           class="channel-tab {{ $activeChannel==='email' ? 'active-email' : 'inactive' }}">
            <i class="fa fa-envelope"></i> Email
        </a>
    </div>

    {{-- Table --}}
    <div class="dashboard-card">
        <div class="dc-header" style="background:#1a1a2e;">
            <h5 style="color:#fff;"><i class="fa fa-list" style="color:#c2ac1f;"></i> Plantillas de Mensajes</h5>
            <span style="font-size:12px; color:#aaa;">{{ $templates->total() }} plantillas</span>
        </div>
        <div style="overflow-x:auto;">
            @if($templates->isEmpty())
                <div style="padding:40px; text-align:center; color:#aaa;">
                    <i class="fa fa-comments-o" style="font-size:36px; margin-bottom:12px; display:block;"></i>
                    No hay plantillas de mensajes aún.
                    <br><a href="{{ route('admin.crm.message-templates.create') }}" class="action-btn primary" style="margin-top:14px; display:inline-flex;">
                        <i class="fa fa-plus"></i> Crear primera plantilla
                    </a>
                </div>
            @else
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Canal</th>
                        <th>Etapa</th>
                        <th>Vista previa</th>
                        <th style="text-align:center;">Estado</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $tpl)
                    <tr>
                        <td>
                            <div style="font-weight:600; color:#1a1a2e; font-size:13.5px;">{{ $tpl->name }}</div>
                            @if($tpl->subject)
                                <div style="font-size:11.5px; color:#888; margin-top:2px;">
                                    <i class="fa fa-tag"></i> {{ Str::limit($tpl->subject, 50) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($tpl->channel === 'whatsapp')
                                <span class="crm-badge" style="background:#d1fae5; color:#065f46;">
                                    <i class="fa fa-whatsapp"></i> WhatsApp
                                </span>
                            @else
                                <span class="crm-badge" style="background:#dbeafe; color:#1d4ed8;">
                                    <i class="fa fa-envelope"></i> Email
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($tpl->stage)
                                @php
                                    $stageLabels = [
                                        'new'         => 'Nuevo',
                                        'contacted'   => 'Contactado',
                                        'qualified'   => 'Calificado',
                                        'proposal'    => 'Propuesta',
                                        'negotiation' => 'Negociación',
                                        'won'         => 'Ganado',
                                        'lost'        => 'Perdido',
                                    ];
                                @endphp
                                <span class="crm-badge {{ $tpl->stage }}">{{ $stageLabels[$tpl->stage] ?? $tpl->stage }}</span>
                            @else
                                <span style="color:#bbb; font-size:12px;">Todas</span>
                            @endif
                        </td>
                        <td style="max-width:240px;">
                            <span style="font-size:12.5px; color:#666; white-space:pre-line;">{{ Str::limit($tpl->body, 80) }}</span>
                        </td>
                        <td style="text-align:center;">
                            @if($tpl->is_active)
                                <span class="crm-badge won"><i class="fa fa-check"></i> Activa</span>
                            @else
                                <span class="crm-badge lost"><i class="fa fa-times"></i> Inactiva</span>
                            @endif
                        </td>
                        <td style="text-align:right; white-space:nowrap;">
                            <a href="{{ route('admin.crm.message-templates.edit', $tpl) }}" class="action-btn secondary" style="padding:5px 9px; font-size:12px;">
                                <i class="fa fa-pencil"></i> Editar
                            </a>
                            <form method="POST" action="{{ route('admin.crm.message-templates.destroy', $tpl) }}"
                                  style="display:inline;"
                                  onsubmit="return confirm('¿Eliminar esta plantilla?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn danger" style="padding:5px 9px; font-size:12px;">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        @if($templates->hasPages())
        <div id="pagination-wrap" style="padding:12px 18px; border-top:1px solid #f5f5f5;">
            {{ $templates->links() }}
        </div>
        @endif
    </div>

</div>

@endsection
