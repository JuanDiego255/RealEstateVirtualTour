@extends('admin.main')
@section('title', 'Bot de WhatsApp — ' . $company->name)
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fa fa-whatsapp" style="color:#25d366"></i> Bot de WhatsApp — {{ $company->name }}</h4>
        <div>
            <a href="{{ route('admin.companies.ai.edit', $company) }}" class="btn btn-outline-primary btn-sm"><i class="fa fa-magic"></i> IA</a>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    {{-- Guía: datos para pegar en Meta --}}
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="text-muted"><i class="fa fa-info-circle"></i> Datos para configurar en Meta</h6>
            <div class="small">
                <strong>Webhook URL:</strong> <code>{{ $webhookUrl }}</code><br>
                <strong>Verify Token (global):</strong> <code>{{ $verifyToken ?: '(definí WHATSAPP_VERIFY_TOKEN en el .env)' }}</code>
                &nbsp;·&nbsp; <strong>Graph:</strong> <code>{{ $graph }}</code><br>
                <span class="text-muted">Suscribí el webhook al campo <code>messages</code>. Lo que distingue a cada empresa es el <strong>Phone Number ID</strong>.</span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.companies.whatsapp.update', $company) }}" method="POST">
        @csrf @method('PUT')

        {{-- Conexión --}}
        <div class="card mb-3">
            <div class="card-header"><i class="fa fa-plug"></i> Conexión con WhatsApp Cloud API</div>
            <div class="card-body">
                <div class="form-group">
                    <input type="hidden" name="enabled" value="0">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="enabled" name="enabled" value="1" {{ old('enabled', $bot->enabled) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="enabled"><strong>Bot habilitado</strong> — recibe y responde mensajes</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tipo de negocio</label>
                    <input type="text" name="business_type" class="form-control" value="{{ old('business_type', $bot->business_type) }}"
                           placeholder="Ej: Concesionario — vehículos usados y nuevos, cierre en sala">
                    <small class="form-text text-muted">Contexto que usa el bot en su prompt.</small>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Phone Number ID</label>
                        <input type="text" name="phone_number_id" class="form-control" value="{{ old('phone_number_id', $bot->phone_number_id) }}">
                        <small class="form-text text-muted">Único por empresa. Es la llave del webhook.</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label>WhatsApp Business Account ID</label>
                        <input type="text" name="waba_id" class="form-control" value="{{ old('waba_id', $bot->waba_id) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Número visible</label>
                        <input type="text" name="display_phone" class="form-control" value="{{ old('display_phone', $bot->display_phone) }}" placeholder="+506 ...">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Access Token</label>
                        <input type="password" name="access_token" class="form-control" autocomplete="new-password"
                               placeholder="{{ $bot->access_token ? '(guardado — escribí solo para reemplazarlo)' : 'Token permanente de Meta' }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>App Secret</label>
                        <input type="password" name="app_secret" class="form-control" autocomplete="new-password"
                               placeholder="{{ $bot->app_secret ? '(guardado)' : 'Valida la firma del webhook' }}">
                        <small class="form-text text-muted">Sin esto, los mensajes se rechazan.</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Verify Token (opcional)</label>
                        <input type="password" name="verify_token" class="form-control" autocomplete="new-password"
                               placeholder="{{ $bot->verify_token ? '(guardado)' : 'Se usa el global si lo dejás vacío' }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Versión Graph API (opcional)</label>
                        <input type="text" name="graph_version" class="form-control" value="{{ old('graph_version', $bot->graph_version) }}" placeholder="{{ $graph }} (del sistema)">
                    </div>
                </div>
            </div>
        </div>

        {{-- Plan y cuotas --}}
        <div class="card mb-3">
            <div class="card-header"><i class="fa fa-credit-card"></i> Plan y cuotas</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Plan</label>
                        <input type="text" name="plan" class="form-control" value="{{ old('plan', $bot->plan) }}" placeholder="Ej: Chat Esencial — $25/mes · 120 conversaciones">
                    </div>
                    <div class="form-group col-md-6">
                        <div class="custom-control custom-switch mt-4">
                            <input type="hidden" name="allow_overage" value="0">
                            <input type="checkbox" class="custom-control-input" id="allow_overage" name="allow_overage" value="1" {{ old('allow_overage', $bot->allow_overage) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="allow_overage">Permitir conversaciones adicionales (se cobran aparte)</label>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Conversaciones incluidas</label>
                        <input type="number" name="included_conversations" class="form-control" value="{{ old('included_conversations', $bot->included_conversations) }}" placeholder="(del plan)">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Precio mensual USD</label>
                        <input type="number" step="0.01" name="plan_price_usd" class="form-control" value="{{ old('plan_price_usd', $bot->plan_price_usd) }}" placeholder="(del plan)">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Precio por extra USD</label>
                        <input type="number" step="0.0001" name="extra_conversation_price_usd" class="form-control" value="{{ old('extra_conversation_price_usd', $bot->extra_conversation_price_usd) }}" placeholder="(del plan)">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Tope consumo adicional USD</label>
                        <input type="number" step="0.01" name="overage_cap_usd" class="form-control" value="{{ old('overage_cap_usd', $bot->overage_cap_usd) }}" placeholder="0 = sin tope">
                    </div>
                </div>
            </div>
        </div>

        {{-- Cuándo responde --}}
        <div class="card mb-3">
            <div class="card-header"><i class="fa fa-clock-o"></i> Cuándo responde el bot</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Modo de activación</label>
                        <select name="activation_mode" class="form-control">
                            @foreach(\App\Models\CompanyWhatsappBot::ACTIVATION_MODES as $k => $label)
                                <option value="{{ $k }}" {{ old('activation_mode', $bot->activation_mode) === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">En hosting compartido, "esperar minutos" requiere un cron; por defecto responde de inmediato.</small>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Minutos de espera</label>
                        <input type="number" name="delay_minutes" class="form-control" value="{{ old('delay_minutes', $bot->delay_minutes) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Horario laboral — inicio</label>
                        <input type="time" name="business_hours_start" class="form-control" value="{{ old('business_hours_start', $bot->business_hours_start) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Horario laboral — fin</label>
                        <input type="time" name="business_hours_end" class="form-control" value="{{ old('business_hours_end', $bot->business_hours_end) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="instant_outside_hours" value="0">
                            <input type="checkbox" class="custom-control-input" id="instant_outside_hours" name="instant_outside_hours" value="1" {{ old('instant_outside_hours', $bot->instant_outside_hours) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="instant_outside_hours">Fuera del horario laboral, responder de inmediato</label>
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Vehículos por respuesta</label>
                        <input type="number" name="max_vehicles_per_reply" class="form-control" value="{{ old('max_vehicles_per_reply', $bot->max_vehicles_per_reply) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Notas --}}
        <div class="card mb-3">
            <div class="card-body">
                <label>Notas</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $bot->notes) }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar</button>
    </form>
</div>
@endsection
