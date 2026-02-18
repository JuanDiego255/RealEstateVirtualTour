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
@endif
