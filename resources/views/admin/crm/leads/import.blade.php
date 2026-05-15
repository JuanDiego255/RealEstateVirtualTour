@extends('admin.main')
@section('title', 'Importar Leads CSV')
@section('content')

@include('admin.crm.layouts._crm-styles')

<div class="crm-page">

    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-upload"></i> Importar Leads desde CSV</h2>
            <div class="sub">Sube un archivo CSV para importar leads masivamente</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="dashboard-card" style="max-width:600px;">
        <div class="dc-header" style="background:#1a1a2e;">
            <h5 style="color:#fff;"><i class="fa fa-file-text-o" style="color:#c2ac1f;"></i> Archivo CSV</h5>
        </div>
        <div class="dc-body" style="padding:24px;">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div style="background:#fffdf0;border:1px solid #f0e8a0;border-radius:9px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#7a6012;">
                <i class="fa fa-info-circle"></i>
                <strong>Columnas esperadas:</strong> name, email, phone, whatsapp, source, priority, interest_type, budget_min, budget_max, budget_currency, notes
            </div>

            <form method="POST" action="{{ route('admin.crm.leads.import.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label style="font-size:13px;font-weight:600;color:#444;margin-bottom:6px;display:block;">
                        Archivo CSV <span class="text-danger">*</span>
                    </label>
                    <input type="file" name="csv_file" accept=".csv,.txt"
                           class="form-control @error('csv_file') is-invalid @enderror"
                           style="border-radius:9px;border:1px solid #e5e7eb;font-size:14px;">
                    @error('csv_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
                    <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary">Cancelar</a>
                    <button type="submit" class="action-btn"
                            style="background:linear-gradient(135deg,#c2ac1f,#a89617);color:#000;font-weight:700;padding:9px 22px;border-radius:9px;">
                        <i class="fa fa-upload"></i> Importar
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
