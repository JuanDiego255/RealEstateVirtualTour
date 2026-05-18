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
                                        @if(Auth::user()->role === 'company_admin' || Auth::user()->isSuperAdmin())
                                            <select name="user_id" class="form-control">
                                                <option value="">-- Seleccionar --</option>
                                                @foreach($agents as $agent)
                                                    <option value="{{ $agent->id }}" {{ old('user_id', auth()->id()) == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                        @endif
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
                                <select name="vehicle_id" id="vehicle_id_select" class="form-control">
                                    <option value="">-- Ninguno --</option>
                                    @foreach($vehicles as $vehicle)
                                        @php $imgUrl = $vehicle->images->first()?->url ?? ($vehicle->image ? route('file', $vehicle->image) : ''); @endphp
                                        <option value="{{ $vehicle->id }}"
                                            data-img="{{ $imgUrl }}"
                                            {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                            @if($vehicle->brand || $vehicle->year)
                                                {{ trim($vehicle->brand . ' ' . $vehicle->model) }} ({{ $vehicle->year }}) — {{ $vehicle->name }}
                                            @else
                                                {{ $vehicle->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div id="vehicle-preview" style="margin-top:8px;display:none;">
                                    <img id="vehicle-preview-img" src="" alt="Vista previa" style="width:50px;height:50px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                </div>
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
                                        <input type="text" name="budget_min_display" data-money="budget_min" class="form-control" value="{{ old('budget_min') }}" placeholder="0">
                                        <input type="hidden" name="budget_min" value="{{ old('budget_min') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Máximo</label>
                                        <input type="text" name="budget_max_display" data-money="budget_max" class="form-control" value="{{ old('budget_max') }}" placeholder="0">
                                        <input type="hidden" name="budget_max" value="{{ old('budget_max') }}">
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
                            <h5><i class="fa fa-sliders"></i> Preferencias de Búsqueda <span style="font-size:11px;font-weight:400;color:#aaa;">(para matching de propiedades / vehículos)</span></h5>
                        </div>
                        <div class="dc-body">

                            {{-- Asset type toggle --}}
                            <div class="form-group mb-3">
                                <label style="font-weight:600; font-size:13px; color:#444;">Tipo de búsqueda</label>
                                <div style="display:flex; gap:16px; margin-top:6px;">
                                    <label style="cursor:pointer; display:flex; align-items:center; gap:8px; font-size:14px;">
                                        <input type="radio" name="preferred_asset_type" value="property"
                                            {{ old('preferred_asset_type', 'property') === 'property' ? 'checked' : '' }}
                                            onchange="toggleAssetPrefs(this.value)"
                                            style="accent-color:#c2ac1f;">
                                        <i class="fa fa-home"></i> Propiedad / Inmueble
                                    </label>
                                    <label style="cursor:pointer; display:flex; align-items:center; gap:8px; font-size:14px;">
                                        <input type="radio" name="preferred_asset_type" value="vehicle"
                                            {{ old('preferred_asset_type') === 'vehicle' ? 'checked' : '' }}
                                            onchange="toggleAssetPrefs(this.value)"
                                            style="accent-color:#c2ac1f;">
                                        <i class="fa fa-car"></i> Vehículo
                                    </label>
                                </div>
                            </div>

                            {{-- Property preferences --}}
                            <div id="pref-property-section">
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

                            {{-- Vehicle preferences --}}
                            <div id="pref-vehicle-section" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label style="font-weight:600; font-size:13px; color:#444;">Marca preferida</label>
                                            <input type="text" name="preferred_vehicle_brand" class="form-control"
                                                value="{{ old('preferred_vehicle_brand', '') }}"
                                                placeholder="Ej: Toyota, Honda, Kia..." style="border-radius:9px;">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label style="font-weight:600; font-size:13px; color:#444;">Precio máximo</label>
                                            <input type="number" name="preferred_vehicle_max_price" class="form-control"
                                                value="{{ old('preferred_vehicle_max_price', '') }}"
                                                placeholder="0" min="0" style="border-radius:9px;">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label style="font-weight:600; font-size:13px; color:#444;">Año mínimo</label>
                                            <input type="number" name="preferred_vehicle_min_year" class="form-control"
                                                value="{{ old('preferred_vehicle_min_year', '') }}"
                                                placeholder="2015" min="1990" max="2030" style="border-radius:9px;">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label style="font-weight:600; font-size:13px; color:#444;">Año máximo</label>
                                            <input type="number" name="preferred_vehicle_max_year" class="form-control"
                                                value="{{ old('preferred_vehicle_max_year', '') }}"
                                                placeholder="{{ date('Y') }}" min="1990" max="2030" style="border-radius:9px;">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label style="font-weight:600; font-size:13px; color:#444;">Combustible</label>
                                            <select name="preferred_vehicle_fuel_type" class="form-control" style="border-radius:9px;">
                                                <option value="">— Cualquiera —</option>
                                                <option value="gasoline" {{ old('preferred_vehicle_fuel_type') === 'gasoline' ? 'selected' : '' }}>Gasolina</option>
                                                <option value="diesel" {{ old('preferred_vehicle_fuel_type') === 'diesel' ? 'selected' : '' }}>Diesel</option>
                                                <option value="hybrid" {{ old('preferred_vehicle_fuel_type') === 'hybrid' ? 'selected' : '' }}>Híbrido</option>
                                                <option value="electric" {{ old('preferred_vehicle_fuel_type') === 'electric' ? 'selected' : '' }}>Eléctrico</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label style="font-weight:600; font-size:13px; color:#444;">Transmisión</label>
                                            <select name="preferred_vehicle_transmission" class="form-control" style="border-radius:9px;">
                                                <option value="">— Cualquiera —</option>
                                                <option value="automatic" {{ old('preferred_vehicle_transmission') === 'automatic' ? 'selected' : '' }}>Automática</option>
                                                <option value="manual" {{ old('preferred_vehicle_transmission') === 'manual' ? 'selected' : '' }}>Manual</option>
                                            </select>
                                        </div>
                                    </div>
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
@push('script')
<script>
function toggleAssetPrefs(val) {
    document.getElementById('pref-property-section').style.display = val === 'property' ? 'block' : 'none';
    document.getElementById('pref-vehicle-section').style.display = val === 'vehicle' ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    // Asset prefs toggle
    var checked = document.querySelector('input[name="preferred_asset_type"]:checked');
    if (checked) toggleAssetPrefs(checked.value);

    // Vehicle image preview
    var vSel = document.getElementById('vehicle_id_select');
    if (vSel) {
        function updateVehiclePreview() {
            var opt = vSel.options[vSel.selectedIndex];
            var img = opt ? opt.getAttribute('data-img') : '';
            var previewDiv = document.getElementById('vehicle-preview');
            var previewImg = document.getElementById('vehicle-preview-img');
            if (img) { previewImg.src = img; previewDiv.style.display = 'block'; }
            else { previewDiv.style.display = 'none'; }
        }
        vSel.addEventListener('change', updateVehiclePreview);
        updateVehiclePreview();
    }

    // Budget thousand separator
    document.querySelectorAll('[data-money]').forEach(function(el) {
        var hiddenName = el.getAttribute('data-money');
        var hidden = document.querySelector('input[type="hidden"][name="' + hiddenName + '"]');
        function fmt(v) {
            var raw = v.replace(/[^0-9]/g, '');
            return raw ? parseInt(raw, 10).toLocaleString('es-CR') : '';
        }
        el.addEventListener('input', function() {
            var raw = this.value.replace(/[^0-9]/g, '');
            if (hidden) hidden.value = raw;
            this.value = fmt(this.value);
        });
        if (el.value) {
            var raw = el.value.replace(/[^0-9]/g, '');
            if (hidden) hidden.value = raw;
            el.value = raw ? parseInt(raw, 10).toLocaleString('es-CR') : '';
        }
    });
});
</script>
@endpush
@endsection
