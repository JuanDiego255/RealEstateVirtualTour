@extends('admin.main')
@section('title', 'Formulario: ' . $form->name)
@section('content')
@include('admin.crm.layouts._crm-styles')
<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-wpforms"></i> {{ $form->name }}</h2>
            <div class="sub">{{ $form->submissions_count ?? 0 }} envíos totales</div>
        </div>
        <div class="actions">
            <a href="{{ route('crm.form.show', $form->slug) }}" target="_blank" class="action-btn gold">
                <i class="fa fa-external-link"></i> Ver formulario público
            </a>
            <a href="{{ route('admin.crm.forms.edit', $form) }}" class="action-btn primary">
                <i class="fa fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.crm.forms.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Info card --}}
    <div class="dashboard-card" style="margin-bottom:18px;">
        <div class="dc-header"><h5><i class="fa fa-info-circle" style="color:#c2ac1f;"></i> Información</h5></div>
        <div class="dc-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase;font-weight:700;">URL pública</div>
                <code style="font-size:12px;word-break:break-all;">{{ url('/f/' . $form->slug) }}</code>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase;font-weight:700;">Fuente por defecto</div>
                <span class="crm-badge contacted">{{ $form->default_source }}</span>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase;font-weight:700;">Estado</div>
                @if($form->is_active)
                    <span class="crm-badge won">Activo</span>
                @else
                    <span class="crm-badge lost">Inactivo</span>
                @endif
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase;font-weight:700;">Campos</div>
                <span style="font-size:14px;font-weight:700;">{{ count($form->fields ?? []) }}</span>
            </div>
        </div>
    </div>

    {{-- Submissions --}}
    <div class="dashboard-card">
        <div class="dc-header"><h5><i class="fa fa-inbox" style="color:#c2ac1f;"></i> Envíos Recientes</h5></div>
        <div style="overflow-x:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Lead creado</th>
                        <th>Fecha</th>
                        <th>IP</th>
                        <th>Datos</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($submissions as $sub)
                    <tr>
                        <td>
                            @if($sub->lead)
                                <a href="{{ route('admin.crm.leads.show', $sub->lead) }}" style="font-weight:600;color:#1a1a2e;">
                                    {{ $sub->lead->name }}
                                </a>
                            @else
                                <span style="color:#ccc;">—</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:#888;">{{ $sub->created_at->format('d/m/Y H:i') }}</td>
                        <td style="font-size:12px;color:#aaa;">{{ $sub->ip_address }}</td>
                        <td style="font-size:12px;">{{ count($sub->data ?? []) }} campos</td>
                        <td>
                            @if($sub->lead)
                                <a href="{{ route('admin.crm.leads.show', $sub->lead) }}" class="action-btn view">
                                    <i class="fa fa-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:#aaa;padding:30px;">Sin envíos aún.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($submissions->hasPages())
        <div style="padding:16px 20px;">{{ $submissions->links() }}</div>
        @endif
    </div>
</div>
@endsection
