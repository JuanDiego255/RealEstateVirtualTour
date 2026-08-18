@extends('admin.main')
@section('title', 'Configuración de correo (SMTP)')
@section('content')
@include('admin.crm._ui')

<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-envelope"></i> Configuración de correo (SMTP)</h2>
            <p class="sub">La cuenta con la que salen los correos de tu empresa</p>
        </div>
    </div>

    @if(session('success'))<div class="crm-alert success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="crm-alert danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="crm-alert danger"><ul style="margin:0; padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    @if(!empty($lastError))
        <div class="crm-alert danger">
            <strong><i class="fa fa-exclamation-triangle"></i> Último error de envío</strong>
            ({{ $lastError->created_at->format('d/m/Y H:i') }}@if($lastError->to_email) · a {{ $lastError->to_email }}@endif):
            <div style="margin-top:4px; font-size:12px;">{{ \Illuminate\Support\Str::limit($lastError->error, 300) }}</div>
        </div>
    @endif

    <div class="crm-two-col">
        <div>
            <form method="POST" action="{{ route('admin.mail.settings.update') }}">
                @csrf @method('PUT')
                <div class="crm-section">
                    <div class="crm-section-header">
                        <h5><i class="fa fa-paper-plane-o"></i> Cuenta que envía</h5>
                        <label class="crm-toggle">
                            <input type="hidden" name="enabled" value="0">
                            <input type="checkbox" name="enabled" value="1" {{ old('enabled', $setting->enabled) ? 'checked' : '' }}>
                            <span class="track"></span> Habilitado
                        </label>
                    </div>
                    <div class="crm-section-pad">
                        <div class="crm-form-row">
                            <div class="crm-form-group">
                                <label class="crm-label">Nombre del remitente</label>
                                <input type="text" name="from_name" class="crm-input" value="{{ old('from_name', $setting->from_name) }}" placeholder="Ej: Autos del Valle">
                            </div>
                            <div class="crm-form-group">
                                <label class="crm-label">Correo del remitente</label>
                                <input type="email" name="from_address" class="crm-input" value="{{ old('from_address', $setting->from_address) }}" placeholder="ventas@tudominio.com">
                            </div>
                        </div>

                        <div class="crm-form-row">
                            <div class="crm-form-group">
                                <label class="crm-label">Servidor SMTP (host)</label>
                                <input type="text" name="host" id="smtp_host" class="crm-input" value="{{ old('host', $setting->host) }}" placeholder="smtp.gmail.com">
                            </div>
                            <div class="crm-form-group" style="max-width:120px;">
                                <label class="crm-label">Puerto</label>
                                <input type="number" name="port" id="smtp_port" class="crm-input" value="{{ old('port', $setting->port) }}" placeholder="587">
                            </div>
                            <div class="crm-form-group" style="max-width:140px;">
                                <label class="crm-label">Seguridad</label>
                                @php $enc = old('encryption', $setting->encryption); @endphp
                                <select name="encryption" id="smtp_enc" class="crm-select">
                                    <option value="tls" {{ $enc === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ $enc === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="none" {{ $enc === null || $enc === 'none' ? 'selected' : '' }}>Ninguna</option>
                                </select>
                            </div>
                        </div>

                        <div class="crm-form-row">
                            <div class="crm-form-group">
                                <label class="crm-label">Usuario (correo completo)</label>
                                <input type="text" name="username" class="crm-input" value="{{ old('username', $setting->username) }}" placeholder="tucuenta@gmail.com" autocomplete="off">
                                <div class="crm-help">La dirección completa (tucuenta@gmail.com), no tu nombre.</div>
                            </div>
                            <div class="crm-form-group">
                                <label class="crm-label">Clave de aplicación</label>
                                <input type="password" name="password" class="crm-input" value="" placeholder="{{ $setting->password ? '•••••••• (sin cambios)' : 'clave de aplicaciones de terceros' }}" autocomplete="new-password">
                                <div class="crm-help">Dejala vacía para conservar la actual.</div>
                            </div>
                        </div>

                        <button class="action-btn primary"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                </div>
            </form>
        </div>

        <div>
            <div class="crm-section">
                <div class="crm-section-header"><h5><i class="fa fa-question-circle-o"></i> Ayuda rápida</h5></div>
                <div class="crm-section-pad">
                    <p style="font-size:13px; margin-bottom:12px;">Usá una <strong>clave de aplicaciones</strong> (app password), no la contraseña normal.</p>
                    <div style="display:flex; gap:8px; margin-bottom:12px;">
                        <button type="button" class="action-btn secondary xs" onclick="fillPreset('gmail')">Preset Gmail</button>
                        <button type="button" class="action-btn secondary xs" onclick="fillPreset('outlook')">Preset Outlook</button>
                    </div>
                    <ul style="font-size:12px; color:#64748b; padding-left:18px; margin:0;">
                        <li>Gmail: <code>smtp.gmail.com</code> · 587 · TLS (requiere verificación en 2 pasos + clave de aplicación).</li>
                        <li>Outlook: <code>smtp.office365.com</code> · 587 · TLS.</li>
                    </ul>
                </div>
            </div>

            <div class="crm-section">
                <div class="crm-section-header"><h5><i class="fa fa-flask"></i> Probar envío</h5></div>
                <div class="crm-section-pad">
                    @if($setting->last_test_at)
                        <p style="font-size:12px; margin-bottom:10px;">
                            Última prueba: {{ $setting->last_test_at->format('d/m/Y H:i') }}
                            @if($setting->last_test_ok)<span class="crm-badge green">OK</span>@else<span class="crm-badge red">Falló</span>@endif
                        </p>
                        @if(!$setting->last_test_ok && $setting->last_test_error)
                            <p style="font-size:11px; color:#dc2626;">{{ \Illuminate\Support\Str::limit($setting->last_test_error, 160) }}</p>
                        @endif
                    @endif
                    <form method="POST" action="{{ route('admin.mail.settings.test') }}">
                        @csrf
                        <div class="crm-form-group">
                            <label class="crm-label">Enviar prueba a</label>
                            <input type="email" name="test_to" class="crm-input" placeholder="{{ $setting->from_address ?: 'tu@correo.com' }}">
                        </div>
                        <button class="action-btn success" style="width:100%; justify-content:center;"><i class="fa fa-paper-plane"></i> Enviar correo de prueba</button>
                        <div class="crm-help" style="margin-top:8px;">Guardá los cambios antes de probar.</div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Correos enviados por esta configuración --}}
    <div class="crm-section">
        <div class="crm-section-header">
            <h5><i class="fa fa-paper-plane-o"></i> Correos enviados</h5>
            <span class="hint">últimos 50 salientes de esta cuenta</span>
        </div>
        <div class="crm-section-body">
            <div class="crm-table-wrap">
                <table class="crm-table">
                    <thead><tr>
                        <th>Fecha</th><th>Para</th><th>Asunto</th><th>Origen</th><th>Estado</th>
                    </tr></thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="muted">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $log->to_email ?: '—' }}@if($log->to_name)<div class="muted">{{ $log->to_name }}</div>@endif</td>
                                <td>{{ $log->subject ?: '—' }}</td>
                                <td class="muted">{{ $log->context ?: '—' }}</td>
                                <td>
                                    @if($log->status === 'sent')
                                        <span class="crm-badge green">Enviado</span>
                                    @else
                                        <span class="crm-badge red" title="{{ $log->error }}">Falló</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state"><i class="fa fa-envelope-o"></i>Todavía no se registraron correos.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function fillPreset(kind) {
    var host = document.getElementById('smtp_host'), port = document.getElementById('smtp_port'), enc = document.getElementById('smtp_enc');
    if (kind === 'gmail')   { host.value = 'smtp.gmail.com';     port.value = 587; enc.value = 'tls'; }
    if (kind === 'outlook') { host.value = 'smtp.office365.com'; port.value = 587; enc.value = 'tls'; }
}
</script>
@endsection
