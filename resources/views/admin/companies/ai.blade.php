@extends('admin.main')
@section('title', 'IA — ' . $company->name)
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fa fa-magic" style="color:#6d28d9"></i> IA — {{ $company->name }}</h4>
        <div>
            <a href="{{ route('admin.companies.whatsapp.edit', $company) }}" class="btn btn-outline-success btn-sm"><i class="fa fa-whatsapp"></i> WhatsApp</a>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form action="{{ route('admin.companies.ai.update', $company) }}" method="POST">
        @csrf @method('PUT')

        {{-- Credenciales y modelo --}}
        <div class="card mb-3">
            <div class="card-header"><i class="fa fa-key"></i> Credenciales y modelo</div>
            <div class="card-body">
                <div class="form-group">
                    <input type="hidden" name="enabled" value="0">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="ai_enabled" name="enabled" value="1" {{ old('enabled', $ai->enabled) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="ai_enabled"><strong>IA habilitada para esta empresa</strong></label>
                    </div>
                </div>
                <div class="form-group">
                    <label>API Key de Anthropic (exclusiva de esta empresa)</label>
                    <input type="password" name="api_key" class="form-control" autocomplete="new-password"
                           placeholder="{{ $ai->api_key ? '(guardada — escribí solo para reemplazarla)' : 'sk-ant-...' }}">
                </div>
                <div class="form-group">
                    <label>Modelo</label>
                    <select name="model" class="form-control">
                        @foreach($models as $key => $label)
                            <option value="{{ $key }}" {{ old('model', $ai->model ?: \App\Models\CompanyAiSetting::DEFAULT_MODEL) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Plan y cuota --}}
        <div class="card mb-3">
            <div class="card-header"><i class="fa fa-credit-card"></i> Plan y cuota mensual</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Plan</label>
                        <input type="text" name="plan" class="form-control" value="{{ old('plan', $ai->plan) }}" placeholder="Ej: Básico — $12/mes">
                    </div>
                    <div class="form-group col-md-6">
                        <div class="custom-control custom-switch mt-4">
                            <input type="hidden" name="allow_overage" value="0">
                            <input type="checkbox" class="custom-control-input" id="ai_allow_overage" name="allow_overage" value="1" {{ old('allow_overage', $ai->allow_overage) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="ai_allow_overage">Permitir consumo adicional (se cobra aparte)</label>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Generaciones incluidas</label>
                        <input type="number" name="included_generations" class="form-control" value="{{ old('included_generations', $ai->included_generations) }}" placeholder="(del plan)">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Precio mensual USD</label>
                        <input type="number" step="0.01" name="plan_price_usd" class="form-control" value="{{ old('plan_price_usd', $ai->plan_price_usd) }}" placeholder="(del plan)">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Precio por extra USD</label>
                        <input type="number" step="0.0001" name="extra_generation_price_usd" class="form-control" value="{{ old('extra_generation_price_usd', $ai->extra_generation_price_usd) }}" placeholder="(del plan)">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Tope consumo adicional USD</label>
                        <input type="number" step="0.01" name="overage_cap_usd" class="form-control" value="{{ old('overage_cap_usd', $ai->overage_cap_usd) }}" placeholder="0 = sin tope">
                    </div>
                </div>
            </div>
        </div>

        {{-- Personalización --}}
        <div class="card mb-3">
            <div class="card-header"><i class="fa fa-pencil"></i> Personalización del contenido</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Voz de marca</label>
                        <input type="text" name="brand_voice" class="form-control" value="{{ old('brand_voice', $ai->brand_voice) }}" placeholder="Ej: Cercana, confiable">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Público objetivo</label>
                        <input type="text" name="audience" class="form-control" value="{{ old('audience', $ai->audience) }}" placeholder="Ej: Familias, Costa Rica">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Idioma</label>
                        <input type="text" name="language" class="form-control" value="{{ old('language', $ai->language) }}" placeholder="Español">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Máximo de hashtags</label>
                        <input type="number" name="max_hashtags" class="form-control" value="{{ old('max_hashtags', $ai->max_hashtags) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Avanzado --}}
        <div class="card mb-3">
            <div class="card-header"><i class="fa fa-cogs"></i> System Prompt personalizado (avanzado)</div>
            <div class="card-body">
                <textarea name="system_prompt" class="form-control" rows="4" placeholder="Dejar vacío para usar el prompt del sistema">{{ old('system_prompt', $ai->system_prompt) }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar</button>
    </form>
</div>
@endsection
