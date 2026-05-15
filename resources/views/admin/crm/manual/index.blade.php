@extends('admin.main')
@section('title', 'Manual de Usuario CRM')
@section('content')

@include('admin.crm.layouts._crm-styles')
<style>
.manual-wrap { max-width: 960px; margin: 0 auto; padding: 24px 16px; }
.manual-title { color: #1a1a2e; font-size: 28px; font-weight: 700; border-bottom: 4px solid #c2ac1f; padding-bottom: 12px; margin-bottom: 8px; }
.manual-subtitle { color: #666; font-size: 14px; margin-bottom: 32px; }
.manual-toc { background: #f8f8f2; border-left: 4px solid #c2ac1f; border-radius: 8px; padding: 16px 20px; margin-bottom: 32px; }
.manual-toc h4 { color: #1a1a2e; font-size: 15px; font-weight: 700; margin-bottom: 10px; }
.manual-toc ol { margin: 0; padding-left: 20px; column-count: 2; }
.manual-toc li { font-size: 13px; margin-bottom: 4px; }
.manual-toc a { color: #1a1a2e; text-decoration: none; }
.manual-toc a:hover { color: #c2ac1f; }
.manual-section { margin-bottom: 40px; }
.manual-section h2 { color: #1a1a2e; font-size: 19px; font-weight: 700; border-left: 5px solid #c2ac1f; padding-left: 14px; margin-bottom: 14px; margin-top: 0; }
.manual-section h3 { color: #2d2d4e; font-size: 15px; font-weight: 600; margin-top: 18px; margin-bottom: 8px; }
.manual-section p, .manual-section li { font-size: 13.5px; color: #333; line-height: 1.7; }
.manual-section ul, .manual-section ol { padding-left: 22px; }
.manual-tip { background: #fffbeb; border-left: 4px solid #c2ac1f; border-radius: 6px; padding: 10px 14px; margin: 14px 0; font-size: 13px; color: #444; }
.manual-tip strong { color: #c2ac1f; }
.manual-note { background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 6px; padding: 10px 14px; margin: 14px 0; font-size: 13px; color: #1e3a5f; }
.manual-warn { background: #fff1f2; border-left: 4px solid #ef4444; border-radius: 6px; padding: 10px 14px; margin: 14px 0; font-size: 13px; color: #7f1d1d; }
.manual-table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 13px; }
.manual-table th { background: #1a1a2e; color: #fff; padding: 8px 10px; text-align: left; font-weight: 600; }
.manual-table td { border: 1px solid #e5e7eb; padding: 7px 10px; color: #333; }
.manual-table tr:nth-child(even) { background: #f9f9f9; }
.badge-nuevo { background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.badge-contactado { background:#fef9c3;color:#854d0e;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.badge-calificado { background:#dcfce7;color:#166534;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.badge-propuesta { background:#f3e8ff;color:#6b21a8;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.badge-negociacion { background:#ffedd5;color:#9a3412;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.badge-ganado { background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.badge-perdido { background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.pdf-btn { display:inline-flex;align-items:center;gap:8px;background:#ef4444;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer; }
.pdf-btn:hover { background:#dc2626;color:#fff; }
.back-btn { display:inline-flex;align-items:center;gap:8px;background:#1a1a2e;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer; }
.back-btn:hover { background:#2d2d4e;color:#fff; }
.step-list { list-style: decimal; padding-left: 22px; }
.step-list li { margin-bottom: 6px; font-size: 13.5px; color: #333; }
.divider { border: none; border-top: 1px solid #e5e7eb; margin: 32px 0; }
</style>

<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-book"></i> Manual de Usuario — CRM</h2>
            <div class="sub">Guía completa del sistema de gestión de clientes</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.leads.index') }}" class="back-btn"><i class="fa fa-arrow-left"></i> Volver al CRM</a>
            <a href="{{ route('admin.crm.manual.pdf') }}" target="_blank" class="pdf-btn"><i class="fa fa-file-pdf-o"></i> Descargar PDF</a>
        </div>
    </div>
    <div class="manual-wrap">
        @include('admin.crm.manual._sections', ['isPdf' => false])
    </div>
</div>

@endsection
