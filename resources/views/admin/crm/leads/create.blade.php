@extends('admin.main')
@section('title', 'Nuevo Lead')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-user-plus"></i> Nuevo Lead</h4>
            <a href="{{ request('_back') ?: route('admin.crm.leads.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.crm.leads.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        {{-- Información básica --}}
                        <div class="col-md-6">
                            <h5 class="mb-3">Información del Cliente</h5>

                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>WhatsApp</label>
                                        <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Clasificación --}}
                        <div class="col-md-6">
                            <h5 class="mb-3">Clasificación</h5>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fuente <span class="text-danger">*</span></label>
                                        <select name="source" class="form-control" required>
                                            @foreach(\App\Lead::getSources() as $key => $label)
                                                <option value="{{ $key }}" {{ old('source') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Prioridad <span class="text-danger">*</span></label>
                                        <select name="priority" class="form-control" required>
                                            @foreach(\App\Lead::getPriorities() as $key => $label)
                                                <option value="{{ $key }}" {{ old('priority', 'medium') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Interés <span class="text-danger">*</span></label>
                                        <select name="interest_type" class="form-control" required>
                                            @foreach(\App\Lead::getInterestTypes() as $key => $label)
                                                <option value="{{ $key }}" {{ old('interest_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Agente Asignado</label>
                                        <select name="user_id" class="form-control">
                                            <option value="">-- Seleccionar --</option>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->id }}" {{ old('user_id', auth()->id()) == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        {{-- Interés específico --}}
                        <div class="col-md-6">
                            <h5 class="mb-3">Interés Específico</h5>

                            @if($properties->count() > 0)
                            <div class="form-group">
                                <label>Propiedad de Interés</label>
                                <select name="property_id" class="form-control">
                                    <option value="">-- Ninguna --</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>{{ $property->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            @if($vehicles->count() > 0)
                            <div class="form-group">
                                <label>Vehículo de Interés</label>
                                <select name="vehicle_id" class="form-control">
                                    <option value="">-- Ninguno --</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        {{-- Presupuesto --}}
                        <div class="col-md-6">
                            <h5 class="mb-3">Presupuesto</h5>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Moneda</label>
                                        <select name="budget_currency" class="form-control">
                                            <option value="CRC" {{ old('budget_currency') == 'CRC' ? 'selected' : '' }}>CRC (₡)</option>
                                            <option value="USD" {{ old('budget_currency') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Mínimo</label>
                                        <input type="number" name="budget_min" class="form-control" value="{{ old('budget_min') }}" min="0" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Máximo</label>
                                        <input type="number" name="budget_max" class="form-control" value="{{ old('budget_max') }}" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Requerimientos / Lo que busca</label>
                                <textarea name="requirements" class="form-control" rows="3">{{ old('requirements') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Notas</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Próximo Seguimiento</label>
                                <input type="date" name="next_follow_up" class="form-control" value="{{ old('next_follow_up') }}">
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Preferencias de búsqueda --}}
                    <div class="dashboard-card" style="margin-bottom:18px;">
                        <div class="dc-header">
                            <h5><i class="fa fa-sliders"></i> Preferencias de Búsqueda <span style="font-size:11px;font-weight:400;color:#aaa;">(para matching de propiedades)</span></h5>
                        </div>
                        <div class="dc-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Habitaciones mínimas</label>
                                        <input type="number" name="preferred_bedrooms_min" class="form-control"
                                               value="{{ old('preferred_bedrooms_min') }}" min="0" max="20" placeholder="Ej: 3">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Habitaciones máximas</label>
                                        <input type="number" name="preferred_bedrooms_max" class="form-control"
                                               value="{{ old('preferred_bedrooms_max') }}" min="0" max="20" placeholder="Ej: 5">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Zonas preferidas</label>
                                <input type="text" name="preferred_zones" class="form-control"
                                       value="{{ old('preferred_zones') }}"
                                       placeholder="Ej: Escazú, Santa Ana, Heredia (separar por comas)">
                                <small style="color:#aaa;font-size:11px;">Separar múltiples zonas con comas</small>
                            </div>
                            <div class="form-group">
                                <label style="font-weight:600;font-size:13px;color:#1a1a2e;display:block;margin-bottom:8px;">Tipos de propiedad preferidos</label>
                                <div style="display:flex;gap:16px;flex-wrap:wrap;">
                                    @foreach(['house' => 'Casa', 'apartment' => 'Apartamento', 'land' => 'Terreno', 'commercial' => 'Comercial'] as $val => $lbl)
                                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;font-weight:500;color:#555;">
                                        <input type="checkbox" name="preferred_property_types[]" value="{{ $val }}"
                                               {{ in_array($val, old('preferred_property_types', [])) ? 'checked' : '' }}
                                               style="accent-color:#c2ac1f;">
                                        {{ $lbl }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ request('_back') ?: route('admin.crm.leads.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar Lead</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
