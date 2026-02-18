@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row">
    <div class="form-group col-md-6">
        <label>Nombre del paquete <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="name" value="{{ old('name', $package->name ?? '') }}" required placeholder="Ej: Profesional">
    </div>

    <div class="form-group col-md-3">
        <label>Precio <span class="text-danger">*</span></label>
        <input type="number" class="form-control" name="price" value="{{ old('price', $package->price ?? 0) }}" required min="0" step="0.01">
    </div>

    <div class="form-group col-md-3">
        <label>Moneda <span class="text-danger">*</span></label>
        <select class="form-control" name="currency">
            <option value="CRC" {{ old('currency', $package->currency ?? 'CRC') == 'CRC' ? 'selected' : '' }}>Colones (CRC)</option>
            <option value="USD" {{ old('currency', $package->currency ?? '') == 'USD' ? 'selected' : '' }}>Dólares (USD)</option>
        </select>
    </div>

    <div class="form-group col-md-4">
        <label>Período de facturación <span class="text-danger">*</span></label>
        <select class="form-control" name="billing_period">
            <option value="monthly" {{ old('billing_period', $package->billing_period ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Mensual</option>
            <option value="quarterly" {{ old('billing_period', $package->billing_period ?? '') == 'quarterly' ? 'selected' : '' }}>Trimestral</option>
            <option value="yearly" {{ old('billing_period', $package->billing_period ?? '') == 'yearly' ? 'selected' : '' }}>Anual</option>
        </select>
    </div>

    <div class="form-group col-md-4">
        <label>Días de prueba gratis</label>
        <input type="number" class="form-control" name="trial_days" value="{{ old('trial_days', $package->trial_days ?? 0) }}" min="0">
    </div>

    <div class="form-group col-md-4">
        <label>Orden de visualización</label>
        <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', $package->sort_order ?? 0) }}" min="0">
    </div>

    <div class="form-group col-md-12">
        <label>Descripción</label>
        <textarea class="form-control" name="description" rows="2" placeholder="Descripción del paquete">{{ old('description', $package->description ?? '') }}</textarea>
    </div>
</div>

<h5 class="mt-3 mb-3"><i class="fa fa-sliders"></i> Límites del paquete</h5>
<div class="row">
    <div class="form-group col-md-4">
        <label>Máximo de usuarios <span class="text-danger">*</span></label>
        <input type="number" class="form-control" name="max_users" value="{{ old('max_users', $package->max_users ?? 1) }}" required min="1">
        <small class="text-muted">Usuarios activos por empresa</small>
    </div>

    <div class="form-group col-md-4">
        <label>Máximo de categorías <span class="text-danger">*</span></label>
        <input type="number" class="form-control" name="max_categories" value="{{ old('max_categories', $package->max_categories ?? 1) }}" required min="1">
        <small class="text-muted">Negocios/categorías por empresa</small>
    </div>

    <div class="form-group col-md-3">
        <label>Inmuebles por sucursal <span class="text-danger">*</span></label>
        <input type="number" class="form-control" name="max_posts_per_category" value="{{ old('max_posts_per_category', $package->max_posts_per_category ?? 10) }}" required min="1">
    </div>

    <div class="form-group col-md-3">
        <label>Categorías por sucursal <span class="text-danger">*</span></label>
        <input type="number" class="form-control" name="max_subcategories_per_category" value="{{ old('max_subcategories_per_category', $package->max_subcategories_per_category ?? 5) }}" required min="1">
        <small class="text-muted">Ej: SUV, Sedán, Apartamentos</small>
    </div>
</div>

<h5 class="mt-3 mb-3"><i class="fa fa-toggle-on"></i> Funcionalidades</h5>
<div class="row">
    <div class="form-group col-md-3">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="allows_tours" name="allows_tours"
                   {{ old('allows_tours', $package->allows_tours ?? false) ? 'checked' : '' }}>
            <label class="custom-control-label" for="allows_tours">Permite tours virtuales</label>
        </div>
    </div>

    <div class="form-group col-md-3" id="included-tours-group">
        <label>Tours incluidos</label>
        <input type="number" class="form-control" name="included_tours" value="{{ old('included_tours', $package->included_tours ?? 0) }}" min="0" placeholder="0 = ilimitados">
        <small class="text-muted">0 = ilimitados</small>
    </div>

    <div class="form-group col-md-3" id="tour-price-group">
        <label>Precio tour adicional</label>
        <input type="number" class="form-control" name="tour_price" value="{{ old('tour_price', $package->tour_price ?? '') }}" min="0" step="0.01" placeholder="0 = incluido">
        <small class="text-muted">Después del límite</small>
    </div>

    <div class="form-group col-md-3">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="allows_commission" name="allows_commission"
                   {{ old('allows_commission', $package->allows_commission ?? false) ? 'checked' : '' }}>
            <label class="custom-control-label" for="allows_commission">Bolsa inmobiliaria</label>
        </div>
    </div>

    <div class="form-group col-md-3">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured"
                   {{ old('is_featured', $package->is_featured ?? false) ? 'checked' : '' }}>
            <label class="custom-control-label" for="is_featured">Paquete destacado</label>
        </div>
    </div>

    <div class="form-group col-md-3">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                   {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
            <label class="custom-control-label" for="is_active">Activo</label>
        </div>
    </div>
</div>
