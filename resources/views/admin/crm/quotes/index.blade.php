@extends('admin.main')
@section('title', 'Cotizaciones')
@section('content')
@include('admin.crm.layouts._crm-styles')

<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-file-text-o"></i> Cotizaciones</h2>
            <div class="sub">Gestión de cotizaciones para leads</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.quotes.create') }}" class="action-btn primary">
                <i class="fa fa-plus"></i> Nueva Cotización
            </a>
        </div>
    </div>

    @if(Session::has('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:12px;padding:12px 18px;margin-bottom:16px;color:#065f46;font-size:13.5px;">
        <i class="fa fa-check-circle" style="color:#22c55e;"></i> {{ Session::get('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="stats-grid" style="margin-bottom:22px;">
        <div class="stat-card"><div class="sc-icon blue"><i class="fa fa-file-text-o"></i></div><div class="sc-value">{{ $stats['total'] }}</div><div class="sc-label">Total</div></div>
        <div class="stat-card"><div class="sc-icon yellow"><i class="fa fa-pencil"></i></div><div class="sc-value">{{ $stats['draft'] }}</div><div class="sc-label">Borradores</div></div>
        <div class="stat-card"><div class="sc-icon blue"><i class="fa fa-paper-plane"></i></div><div class="sc-value">{{ $stats['sent'] }}</div><div class="sc-label">Enviadas</div></div>
        <div class="stat-card"><div class="sc-icon green"><i class="fa fa-check"></i></div><div class="sc-value">{{ $stats['accepted'] }}</div><div class="sc-label">Aceptadas</div></div>
    </div>

    {{-- Filters --}}
    <form method="GET" style="margin-bottom:18px;">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;background:#fff;border-radius:14px;padding:12px 18px;box-shadow:0 2px 8px rgba(0,0,0,.06);">
            <select name="status" class="form-control" style="width:160px;">
                <option value="">Todos los estados</option>
                @foreach($statuses as $k => $v)
                <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
            <input type="text" name="search" class="form-control" style="width:220px;" placeholder="Buscar por título o #" value="{{ request('search') }}">
            <button type="submit" class="action-btn primary"><i class="fa fa-search"></i> Filtrar</button>
            <a href="{{ route('admin.crm.quotes.index') }}" class="action-btn secondary"><i class="fa fa-times"></i> Limpiar</a>
        </div>
    </form>

    <div class="dashboard-card">
        <div class="dc-header" style="background:#1a1a2e;">
            <h5 style="color:#fff;"><i class="fa fa-file-text-o" style="color:#c2ac1f;"></i> Cotizaciones ({{ $quotes->total() }})</h5>
        </div>
        @if($quotes->isEmpty())
        <div class="dc-body" style="text-align:center;padding:48px;">
            <i class="fa fa-file-text-o" style="font-size:36px;opacity:.3;display:block;margin-bottom:10px;"></i>
            <p style="color:#aaa;">No hay cotizaciones. <a href="{{ route('admin.crm.quotes.create') }}">Crear primera cotización</a></p>
        </div>
        @else
        <div style="overflow-x:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lead</th>
                        <th>Título</th>
                        <th style="text-align:right;">Total</th>
                        <th style="text-align:center;">Estado</th>
                        <th>Creada</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($quotes as $q)
                <tr>
                    <td style="font-family:monospace;font-size:12px;color:#888;">{{ $q->quote_number }}</td>
                    <td>
                        @if($q->lead)
                        <a href="{{ route('admin.crm.leads.show', $q->lead) }}" style="font-weight:600;color:#1a1a2e;text-decoration:none;">{{ $q->lead->name }}</a>
                        @else
                        <span style="color:#ccc;">—</span>
                        @endif
                    </td>
                    <td style="font-size:13px;">{{ Str::limit($q->title, 40) }}</td>
                    <td style="text-align:right;font-weight:700;font-size:14px;color:#1a1a2e;">{{ $q->formatted_total }}</td>
                    <td style="text-align:center;"><span class="crm-badge {{ $q->status_color }}">{{ $q->status_label }}</span></td>
                    <td style="font-size:12px;color:#888;">{{ $q->created_at->format('d/m/Y') }}</td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('admin.crm.quotes.show', $q) }}" class="action-btn view"><i class="fa fa-eye"></i></a>
                        <a href="{{ route('admin.crm.quotes.pdf', $q) }}" class="action-btn secondary" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
                        <form method="POST" action="{{ route('admin.crm.quotes.destroy', $q) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar cotización?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn danger"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($quotes->hasPages())
        <div style="padding:14px 18px;">{{ $quotes->links() }}</div>
        @endif
        @endif
    </div>
</div>
@endsection
