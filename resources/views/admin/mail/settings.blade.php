@extends('admin.main')
@section('title', 'Configuración de correo (SMTP)')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fa fa-envelope"></i> Configuración de correo (SMTP)</h4>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.mail.settings.update') }}">
                @csrf @method('PUT')

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Cuenta que envía los correos</strong>
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="enabled" value="0">
                            <input type="checkbox" class="custom-control-input" id="enabled" name="enabled" value="1" {{ old('enabled', $setting->enabled) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="enabled">Habilitado</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Nombre del remitente</label>
                                <input type="text" name="from_name" class="form-control" value="{{ old('from_name', $setting->from_name) }}" placeholder="Ej: Autos del Valle">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Correo del remitente</label>
                                <input type="email" name="from_address" class="form-control" value="{{ old('from_address', $setting->from_address) }}" placeholder="ventas@tudominio.com">
                            </div>
                        </div>

                        <hr>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Servidor SMTP (host)</label>
                                <input type="text" name="host" id="smtp_host" class="form-control" value="{{ old('host', $setting->host) }}" placeholder="smtp.gmail.com">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Puerto</label>
                                <input type="number" name="port" id="smtp_port" class="form-control" value="{{ old('port', $setting->port) }}" placeholder="587">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Seguridad</label>
                                <select name="encryption" id="smtp_enc" class="form-control">
                                    @php $enc = old('encryption', $setting->encryption); @endphp
                                    <option value="tls" {{ $enc === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ $enc === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="none" {{ $enc === null || $enc === 'none' ? 'selected' : '' }}>Ninguna</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Usuario</label>
                                <input type="text" name="username" class="form-control" value="{{ old('username', $setting->username) }}" placeholder="tucuenta@gmail.com" autocomplete="off">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Clave de aplicación</label>
                                <input type="password" name="password" class="form-control" value="" placeholder="{{ $setting->password ? '•••••••• (sin cambios)' : 'clave de aplicaciones de terceros' }}" autocomplete="new-password">
                                <small class="form-text text-muted">Dejala vacía para conservar la actual.</small>
                            </div>
                        </div>

                        <button class="btn btn-primary"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><strong>Ayuda rápida</strong></div>
                <div class="card-body">
                    <p class="small mb-2">Usá una <strong>clave de aplicaciones</strong> (app password), no la contraseña normal de tu cuenta.</p>
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillPreset('gmail')">Preset Gmail</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillPreset('outlook')">Preset Outlook</button>
                    </div>
                    <ul class="small text-muted pl-3 mb-0">
                        <li>Gmail: <code>smtp.gmail.com</code> · 587 · TLS. Requiere verificación en 2 pasos + clave de aplicación.</li>
                        <li>Outlook: <code>smtp.office365.com</code> · 587 · TLS.</li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><strong>Probar envío</strong></div>
                <div class="card-body">
                    @if($setting->last_test_at)
                        <p class="small mb-2">
                            Última prueba: {{ $setting->last_test_at->format('d/m/Y H:i') }}
                            @if($setting->last_test_ok)
                                <span class="badge badge-success">OK</span>
                            @else
                                <span class="badge badge-danger">Falló</span>
                            @endif
                        </p>
                        @if(!$setting->last_test_ok && $setting->last_test_error)
                            <p class="small text-danger">{{ \Illuminate\Support\Str::limit($setting->last_test_error, 160) }}</p>
                        @endif
                    @endif
                    <form method="POST" action="{{ route('admin.mail.settings.test') }}">
                        @csrf
                        <div class="form-group">
                            <label class="small mb-1">Enviar prueba a</label>
                            <input type="email" name="test_to" class="form-control form-control-sm" placeholder="{{ $setting->from_address ?: 'tu@correo.com' }}">
                        </div>
                        <button class="btn btn-sm btn-success btn-block"><i class="fa fa-paper-plane"></i> Enviar correo de prueba</button>
                    </form>
                    <small class="text-muted">Guardá los cambios antes de probar.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillPreset(kind) {
    var host = document.getElementById('smtp_host');
    var port = document.getElementById('smtp_port');
    var enc  = document.getElementById('smtp_enc');
    if (kind === 'gmail')   { host.value = 'smtp.gmail.com';       port.value = 587; enc.value = 'tls'; }
    if (kind === 'outlook') { host.value = 'smtp.office365.com';   port.value = 587; enc.value = 'tls'; }
}
</script>
@endsection
