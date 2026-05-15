@extends('admin.main')
@section('title', 'Auditoría — Leads Eliminados')
@section('content')

@include('admin.crm.layouts._crm-styles')

<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-shield"></i> Auditoría de Eliminación de Leads</h2>
            <div class="sub">Registro de leads eliminados por agentes de la empresa</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.leads.index') }}" class="crm-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver al CRM
            </a>
        </div>
    </div>

    <div style="padding: 0 20px 30px;">
        @if($audits->isEmpty())
            <div class="crm-empty-state">
                <i class="fa fa-shield fa-3x" style="color:#c2ac1f;"></i>
                <p style="margin-top:12px;color:#888;">No hay registros de eliminación aún.</p>
            </div>
        @else
        <div class="dashboard-card" style="overflow:auto;">
            <table class="crm-table" style="min-width:700px;">
                <thead>
                    <tr>
                        <th>Fecha Eliminación</th>
                        <th>Lead</th>
                        <th>Email / Teléfono</th>
                        <th>Estado</th>
                        <th>Score</th>
                        <th>Eliminado por</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($audits as $audit)
                    <tr>
                        <td style="white-space:nowrap;font-size:12px;color:#888;">
                            {{ \Carbon\Carbon::parse($audit->deleted_at)->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <strong style="font-size:13px;">{{ $audit->lead_name }}</strong>
                        </td>
                        <td style="font-size:12px;color:#666;">
                            {{ $audit->lead_email }}<br>
                            {{ $audit->lead_phone }}
                        </td>
                        <td>
                            <span class="crm-badge {{ $audit->lead_status }}">{{ $audit->lead_status }}</span>
                        </td>
                        <td style="text-align:center;font-weight:600;">{{ $audit->lead_score }}</td>
                        <td style="font-size:13px;">{{ $audit->deletedBy->name ?? '—' }}</td>
                        <td style="font-size:12px;color:#555;max-width:250px;">
                            {{ $audit->reason ?: '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:16px;">
            {{ $audits->links() }}
        </div>
        @endif
    </div>
</div>

@endsection
