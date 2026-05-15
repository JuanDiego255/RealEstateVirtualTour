@extends('admin.main')
@section('title', 'Importar Leads CSV')
@section('content')

@include('admin.crm.layouts._crm-styles')

<div class="crm-page">

    {{-- PAGE HEADER --}}
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-upload"></i> Importar Leads desde CSV</h2>
            <div class="sub">Sube un archivo CSV para importar leads masivamente</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary">
                <i class="fa fa-arrow-left"></i> Volver a Leads
            </a>
        </div>
    </div>

    {{-- ERROR ALERT --}}
    @if($errors->any())
        <div class="alert alert-danger" style="border-radius:9px;margin-bottom:20px;">
            <strong><i class="fa fa-exclamation-triangle"></i> Errores de validación:</strong>
            <ul style="margin:8px 0 0 18px;padding:0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ============================= --}}
    {{-- SECTION A: UPLOAD FORM (always shown) --}}
    {{-- ============================= --}}
    <div class="dashboard-card" style="margin-bottom:28px;">
        <div class="dc-header" style="background:#1a1a2e;">
            <h5 style="color:#fff;margin:0;">
                <i class="fa fa-table" style="color:#c2ac1f;"></i>
                Columnas esperadas en el CSV
            </h5>
        </div>
        <div class="dc-body" style="padding:0;">
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f4f4f8;">
                            <th style="padding:10px 16px;text-align:left;border-bottom:2px solid #e5e7eb;color:#1a1a2e;font-weight:700;">Columna</th>
                            <th style="padding:10px 16px;text-align:left;border-bottom:2px solid #e5e7eb;color:#1a1a2e;font-weight:700;">Descripción</th>
                            <th style="padding:10px 16px;text-align:center;border-bottom:2px solid #e5e7eb;color:#1a1a2e;font-weight:700;">Requerido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #f0f0f5;">
                            <td style="padding:9px 16px;"><code style="background:#f4f4f8;padding:2px 6px;border-radius:4px;">nombre / name</code></td>
                            <td style="padding:9px 16px;color:#555;">Nombre completo del lead</td>
                            <td style="padding:9px 16px;text-align:center;"><span style="color:#16a34a;font-weight:700;">Sí</span></td>
                        </tr>
                        <tr style="border-bottom:1px solid #f0f0f5;background:#fafafa;">
                            <td style="padding:9px 16px;"><code style="background:#f4f4f8;padding:2px 6px;border-radius:4px;">correo / email</code></td>
                            <td style="padding:9px 16px;color:#555;">Correo electrónico</td>
                            <td style="padding:9px 16px;text-align:center;color:#888;">No</td>
                        </tr>
                        <tr style="border-bottom:1px solid #f0f0f5;">
                            <td style="padding:9px 16px;"><code style="background:#f4f4f8;padding:2px 6px;border-radius:4px;">telefono / phone</code></td>
                            <td style="padding:9px 16px;color:#555;">Teléfono</td>
                            <td style="padding:9px 16px;text-align:center;color:#888;">No</td>
                        </tr>
                        <tr style="border-bottom:1px solid #f0f0f5;background:#fafafa;">
                            <td style="padding:9px 16px;"><code style="background:#f4f4f8;padding:2px 6px;border-radius:4px;">whatsapp</code></td>
                            <td style="padding:9px 16px;color:#555;">Número WhatsApp</td>
                            <td style="padding:9px 16px;text-align:center;color:#888;">No</td>
                        </tr>
                        <tr style="border-bottom:1px solid #f0f0f5;">
                            <td style="padding:9px 16px;"><code style="background:#f4f4f8;padding:2px 6px;border-radius:4px;">fuente / source</code></td>
                            <td style="padding:9px 16px;color:#555;">Fuente (web, referral, social_media, etc.)</td>
                            <td style="padding:9px 16px;text-align:center;color:#888;">No</td>
                        </tr>
                        <tr style="border-bottom:1px solid #f0f0f5;background:#fafafa;">
                            <td style="padding:9px 16px;"><code style="background:#f4f4f8;padding:2px 6px;border-radius:4px;">estado / status</code></td>
                            <td style="padding:9px 16px;color:#555;">Estado inicial (new, contacted…)</td>
                            <td style="padding:9px 16px;text-align:center;color:#888;">No</td>
                        </tr>
                        <tr style="border-bottom:1px solid #f0f0f5;">
                            <td style="padding:9px 16px;"><code style="background:#f4f4f8;padding:2px 6px;border-radius:4px;">prioridad / priority</code></td>
                            <td style="padding:9px 16px;color:#555;">Prioridad (low, medium, high)</td>
                            <td style="padding:9px 16px;text-align:center;color:#888;">No</td>
                        </tr>
                        <tr style="border-bottom:1px solid #f0f0f5;background:#fafafa;">
                            <td style="padding:9px 16px;"><code style="background:#f4f4f8;padding:2px 6px;border-radius:4px;">notas / notes</code></td>
                            <td style="padding:9px 16px;color:#555;">Notas iniciales</td>
                            <td style="padding:9px 16px;text-align:center;color:#888;">No</td>
                        </tr>
                        <tr style="border-bottom:1px solid #f0f0f5;">
                            <td style="padding:9px 16px;"><code style="background:#f4f4f8;padding:2px 6px;border-radius:4px;">budget_min</code></td>
                            <td style="padding:9px 16px;color:#555;">Presupuesto mínimo</td>
                            <td style="padding:9px 16px;text-align:center;color:#888;">No</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 16px;"><code style="background:#f4f4f8;padding:2px 6px;border-radius:4px;">budget_max</code></td>
                            <td style="padding:9px 16px;color:#555;">Presupuesto máximo</td>
                            <td style="padding:9px 16px;text-align:center;color:#888;">No</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dashboard-card" style="max-width:600px;margin-bottom:28px;">
        <div class="dc-header" style="background:#1a1a2e;">
            <h5 style="color:#fff;margin:0;">
                <i class="fa fa-file-text-o" style="color:#c2ac1f;"></i>
                Seleccionar archivo CSV
            </h5>
        </div>
        <div class="dc-body" style="padding:24px;">
            <form method="POST" action="{{ route('admin.crm.leads.import-preview') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label style="font-size:13px;font-weight:600;color:#444;margin-bottom:6px;display:block;">
                        Archivo CSV <span class="text-danger">*</span>
                    </label>
                    <input type="file" name="file" accept=".csv,.txt"
                           class="form-control @error('file') is-invalid @enderror"
                           style="border-radius:9px;border:1px solid #e5e7eb;font-size:14px;">
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small style="color:#888;font-size:12px;margin-top:4px;display:block;">Máximo 2 MB. Formatos: .csv, .txt</small>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
                    <a href="{{ route('admin.crm.leads.index') }}" class="action-btn secondary">Cancelar</a>
                    <button type="submit" class="action-btn"
                            style="background:linear-gradient(135deg,#c2ac1f,#a89617);color:#000;font-weight:700;padding:9px 22px;border-radius:9px;border:none;cursor:pointer;">
                        <i class="fa fa-search"></i> Vista Previa
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- SECTION B: PREVIEW (only if $preview is set) --}}
    {{-- ============================= --}}
    @isset($preview)
    <div class="dashboard-card" style="margin-bottom:28px;">
        <div class="dc-header" style="background:#1a1a2e;">
            <h5 style="color:#fff;margin:0;">
                <i class="fa fa-eye" style="color:#c2ac1f;"></i>
                Vista previa de la importación
            </h5>
        </div>
        <div class="dc-body" style="padding:20px;">

            {{-- Summary --}}
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;padding:12px 18px;margin-bottom:20px;font-size:14px;color:#15803d;">
                <i class="fa fa-info-circle"></i>
                <strong>Se importarán {{ $totalRows }} leads</strong>
                (mostrando primeros {{ count($preview) }})
            </div>

            {{-- Detected columns --}}
            <div style="margin-bottom:20px;">
                <h6 style="font-size:13px;font-weight:700;color:#1a1a2e;margin-bottom:10px;">
                    <i class="fa fa-columns"></i> Columnas detectadas:
                </h6>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    @foreach($mappedColumns as $raw => $field)
                        @if($field)
                            <span style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                                <i class="fa fa-check-circle"></i> {{ $raw }} → {{ $field }}
                            </span>
                        @else
                            <span style="background:#fef9f0;border:1px solid #fde68a;color:#92400e;padding:4px 10px;border-radius:20px;font-size:12px;">
                                <i class="fa fa-question-circle"></i> {{ $raw }} (ignorada)
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Preview table --}}
            @if(count($preview) > 0)
            <div style="overflow-x:auto;margin-bottom:20px;">
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr style="background:#1a1a2e;">
                            @foreach(array_keys($preview[0]) as $col)
                                <th style="padding:8px 12px;text-align:left;color:#c2ac1f;font-weight:600;white-space:nowrap;">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($preview as $i => $row)
                        <tr style="border-bottom:1px solid #f0f0f5;{{ $i % 2 === 1 ? 'background:#fafafa;' : '' }}">
                            @foreach($row as $val)
                                <td style="padding:7px 12px;color:#333;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $val ?: '—' }}
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div style="padding:20px;text-align:center;color:#888;font-size:14px;">
                    <i class="fa fa-exclamation-circle"></i> No se encontraron filas de datos en el archivo.
                </div>
            @endif

            {{-- Confirm form --}}
            @if($totalRows > 0)
            <form method="POST" action="{{ route('admin.crm.leads.import-execute') }}">
                @csrf
                <input type="hidden" name="confirm" value="1">
                <div style="display:flex;align-items:center;gap:16px;justify-content:flex-end;flex-wrap:wrap;">
                    <a href="{{ route('admin.crm.leads.import') }}" class="action-btn secondary">
                        <i class="fa fa-refresh"></i> Cambiar archivo
                    </a>
                    <button type="submit" class="action-btn"
                            style="background:linear-gradient(135deg,#1a1a2e,#2e2e4e);color:#c2ac1f;font-weight:700;padding:10px 28px;border-radius:9px;border:2px solid #c2ac1f;cursor:pointer;font-size:14px;">
                        <i class="fa fa-check-circle"></i> Confirmar e Importar {{ $totalRows }} leads
                    </button>
                </div>
            </form>
            @endif

        </div>
    </div>
    @endisset

</div>

@endsection
