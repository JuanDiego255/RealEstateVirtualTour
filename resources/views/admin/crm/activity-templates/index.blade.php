@extends('admin.main')
@section('title', 'Plantillas de Actividad')
@section('content')

@include('admin.crm.layouts._crm-styles')

@if (Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        <strong>{{ Session::get('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button>
    </div>
@endif

<style>
.toggle-switch { position: relative; display: inline-block; width: 40px; height: 22px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
    background: #ccc; border-radius: 22px; transition: .3s;
}
.toggle-slider::before {
    content: ''; position: absolute; height: 16px; width: 16px;
    left: 3px; bottom: 3px; background: #fff;
    border-radius: 50%; transition: .3s;
}
.toggle-switch input:checked + .toggle-slider { background: #22c55e; }
.toggle-switch input:checked + .toggle-slider::before { transform: translateX(18px); }

.type-badge-call        { background: #dbeafe; color: #1d4ed8; }
.type-badge-email       { background: #eff6ff; color: #2563eb; }
.type-badge-whatsapp    { background: #d1fae5; color: #065f46; }
.type-badge-visit       { background: #fef3c7; color: #92400e; }
.type-badge-meeting     { background: #ede9fe; color: #7c3aed; }
.type-badge-note        { background: #f1f5f9; color: #475569; }
.type-badge-other       { background: #f5f5f5; color: #666; }
</style>

<div class="crm-page">

    {{-- Header --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-file-text-o"></i> Plantillas de Actividad</h2>
            <div class="sub">Plantillas reutilizables para llamadas, emails, WhatsApp y más</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver a Leads
            </a>
            <a href="{{ route('admin.crm.activity-templates.create') }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nueva Plantilla
            </a>
        </div>
    </div>

    {{-- Stat Cards --}}
    @php
        $allTemplates = $byType->flatten();
        $totalCount     = $allTemplates->count();
        $waCount        = $byType->get('whatsapp', collect())->count();
        $emailCount     = $byType->get('email', collect())->count();
        $callCount      = $byType->get('call', collect())->count();
    @endphp
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); margin-bottom: 24px;">
        <div class="stat-card">
            <div class="sc-icon blue"><i class="fa fa-files-o"></i></div>
            <div class="sc-value">{{ $totalCount }}</div>
            <div class="sc-label">Total Plantillas</div>
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
        <div class="stat-card">
            <div class="sc-icon orange"><i class="fa fa-phone"></i></div>
            <div class="sc-value">{{ $callCount }}</div>
            <div class="sc-label">Llamadas</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="dashboard-card">
        <div class="dc-header" style="background:#1a1a2e;">
            <h5 style="color:#fff;"><i class="fa fa-list" style="color:#c2ac1f;"></i> Listado de Plantillas</h5>
            <span style="font-size:12px; color:#aaa;">{{ $templates->total() }} plantillas</span>
        </div>
        <div style="overflow-x:auto;">
            @if($templates->isEmpty())
                <div style="padding:40px; text-align:center; color:#aaa;">
                    <i class="fa fa-file-o" style="font-size:36px; margin-bottom:12px; display:block;"></i>
                    No hay plantillas creadas aún.
                    <br><a href="{{ route('admin.crm.activity-templates.create') }}" class="action-btn primary" style="margin-top:14px; display:inline-flex;">
                        <i class="fa fa-plus"></i> Crear primera plantilla
                    </a>
                </div>
            @else
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Etapa</th>
                        <th>Asunto</th>
                        <th style="text-align:center;">Estado</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $tpl)
                    <tr>
                        <td>
                            <div style="font-weight:600; color:#1a1a2e; font-size:13.5px;">{{ $tpl->name }}</div>
                            <div style="font-size:11.5px; color:#aaa; margin-top:2px;">
                                {{ Str::limit($tpl->body, 60) }}
                            </div>
                        </td>
                        <td>
                            @php
                                $typeLabels = [
                                    'call'     => ['Llamada',  'fa-phone',        'type-badge-call'],
                                    'email'    => ['Email',    'fa-envelope',     'type-badge-email'],
                                    'whatsapp' => ['WhatsApp', 'fa-whatsapp',     'type-badge-whatsapp'],
                                    'visit'    => ['Visita',   'fa-map-marker',   'type-badge-visit'],
                                    'meeting'  => ['Reunión',  'fa-users',        'type-badge-meeting'],
                                    'note'     => ['Nota',     'fa-sticky-note',  'type-badge-note'],
                                    'other'    => ['Otro',     'fa-ellipsis-h',   'type-badge-other'],
                                ];
                                [$typeLabel, $typeIcon, $typeCls] = $typeLabels[$tpl->type] ?? ['—','fa-question','type-badge-other'];
                            @endphp
                            <span class="crm-badge {{ $typeCls }}" style="border-radius:20px; padding:4px 10px;">
                                <i class="fa {{ $typeIcon }}"></i> {{ $typeLabel }}
                            </span>
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
                        <td style="max-width:200px;">
                            @if($tpl->subject)
                                <span style="font-size:12.5px; color:#555;">{{ Str::limit($tpl->subject, 45) }}</span>
                            @else
                                <span style="color:#ccc; font-size:12px;">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <label class="toggle-switch" title="{{ $tpl->is_active ? 'Activo' : 'Inactivo' }}">
                                <input type="checkbox" class="toggle-active"
                                       data-url="{{ route('admin.crm.activity-templates.toggle', $tpl) }}"
                                       {{ $tpl->is_active ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td style="text-align:right; white-space:nowrap;">
                            <a href="{{ route('admin.crm.activity-templates.edit', $tpl) }}" class="action-btn secondary" style="padding:5px 9px; font-size:12px;">
                                <i class="fa fa-pencil"></i> Editar
                            </a>
                            <form method="POST" action="{{ route('admin.crm.activity-templates.destroy', $tpl) }}"
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

@push('script')
<script>
document.querySelectorAll('.toggle-active').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
        var url  = this.dataset.url;
        var self = this;
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                self.checked = !self.checked;
            }
        })
        .catch(function() { self.checked = !self.checked; });
    });
});
</script>
@endpush
