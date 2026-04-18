<div class="row">
    <div class="form-group col-md-6">
        <label>Nombre comercial <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required
               value="{{ old('name', $company->name ?? '') }}">
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-6">
        <label>Razón social</label>
        <input type="text" name="legal_name" class="form-control"
               value="{{ old('legal_name', $company->legal_name ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="form-group col-md-4">
        <label>Cédula jurídica / NIT</label>
        <input type="text" name="tax_id" class="form-control"
               value="{{ old('tax_id', $company->tax_id ?? '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>Email</label>
        <input type="email" name="email" class="form-control"
               value="{{ old('email', $company->email ?? '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>Teléfono</label>
        <input type="text" name="phone" class="form-control"
               value="{{ old('phone', $company->phone ?? '') }}">
    </div>
</div>

<div class="form-group">
    <label>Dirección</label>
    <textarea name="address" class="form-control" rows="2">{{ old('address', $company->address ?? '') }}</textarea>
</div>

<div class="row">
    <div class="form-group col-md-6">
        <label>Logo</label>
        @if(isset($company) && $company->logo)
            <div class="mb-2"><img src="{{ $company->logo_url }}" class="img-thumbnail" style="max-height: 80px;"></div>
        @endif
        <input type="file" name="logo" class="form-control-file" accept="image/*">
        <small class="form-text text-muted">Máx. 2MB</small>
    </div>
    <div class="form-group col-md-6">
        <label>Propietario / Admin principal</label>
        <select name="owner_id" class="form-control">
            <option value="">Sin asignar</option>
            @foreach($owners ?? [] as $o)
                <option value="{{ $o->id }}" {{ old('owner_id', $company->owner_id ?? '') == $o->id ? 'selected' : '' }}>
                    {{ $o->name }} ({{ $o->email }})
                </option>
            @endforeach
        </select>
        <small class="form-text text-muted">Este usuario será asignado automáticamente a la empresa como Admin</small>
    </div>
</div>

@if(isset($company))
    <div class="form-group">
        <label>Estado</label>
        <select name="status" class="form-control">
            <option value="active" {{ old('status', $company->status) == 'active' ? 'selected' : '' }}>Activa</option>
            <option value="inactive" {{ old('status', $company->status) == 'inactive' ? 'selected' : '' }}>Inactiva</option>
            <option value="suspended" {{ old('status', $company->status) == 'suspended' ? 'selected' : '' }}>Suspendida</option>
        </select>
    </div>
    <div class="form-group">
        <label>Modo Kiosko</label>
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="otherKioskSwitch"
                   name="other_kiosk" value="1"
                   {{ old('other_kiosk', $company->other_kiosk ?? false) ? 'checked' : '' }}>
            <label class="custom-control-label" for="otherKioskSwitch">
                Kiosko "Other" — sin catálogo de productos (ideal para repuestos, servicios B2B)
            </label>
        </div>
        <small class="form-text text-muted">Activar para empresas que usan el kiosko solo para captura de leads y cotizaciones libres, sin necesidad de un catálogo de vehículos o propiedades en BD.</small>
    </div>
    <div class="form-group" id="otherKioskCategories" style="{{ old('other_kiosk', $company->other_kiosk ?? false) ? '' : 'display:none' }}">
        <label>Categorías del Kiosko Other</label>
        <input type="text" name="other_kiosk_categories" class="form-control"
               placeholder="Ej: Filtros, Rodamientos, Hidráulicos, Eléctrico, Otro"
               value="{{ old('other_kiosk_categories', ($company->settings['other_kiosk_categories'] ?? '')) }}">
        <small class="form-text text-muted">Categorías separadas por coma. Se muestran como botones en el formulario del kiosko para que el cliente seleccione qué busca.</small>
    </div>
    <div class="form-group" id="agentPinField" style="{{ old('other_kiosk', $company->other_kiosk ?? false) ? '' : 'display:none' }}">
        <label>PIN Modo Asesor</label>
        <input type="text" name="agent_kiosk_pin" class="form-control" maxlength="6"
               placeholder="Ej: 1234"
               value="{{ old('agent_kiosk_pin', ($company->settings['agent_kiosk_pin'] ?? '')) }}">
        <small class="form-text text-muted">PIN numérico que el asesor ingresa en el kiosko para activar el modo compacto (sin pantalla idle, formulario directo). Dejar vacío para desactivar.</small>
    </div>
@endif

<script>
(function() {
    var sw   = document.getElementById('otherKioskSwitch');
    var cats = document.getElementById('otherKioskCategories');
    var pin  = document.getElementById('agentPinField');
    if (sw && cats) {
        sw.addEventListener('change', function() {
            cats.style.display = this.checked ? '' : 'none';
            if (pin) pin.style.display = this.checked ? '' : 'none';
        });
    }
})();
</script>
