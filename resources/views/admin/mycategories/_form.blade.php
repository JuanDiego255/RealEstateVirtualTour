<div class="row">
    <div class="form-group col-md-8">
        <label>Nombre <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required
               value="{{ old('name', $category->name ?? '') }}" placeholder="Ej: Condominio Corteza">
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Sector <span class="text-danger">*</span></label>
        <select name="sector_id" class="form-control" required>
            <option value="">Seleccionar sector...</option>
            @foreach($sectors as $s)
                <option value="{{ $s->id }}" {{ old('sector_id', $category->sector_id ?? '') == $s->id ? 'selected' : '' }}>
                    @if($s->icon)<i class="{{ $s->icon }}"></i> @endif{{ $s->name }}
                </option>
            @endforeach
        </select>
        @error('sector_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
</div>

<div class="form-group">
    <label>Descripción</label>
    <textarea name="description" class="form-control" rows="3" placeholder="Descripción del negocio o categoría">{{ old('description', $category->description ?? '') }}</textarea>
</div>

<h6 class="mt-4 mb-3 text-primary"><i class="fa fa-image"></i> Imágenes</h6>
<div class="row">
    <div class="form-group col-md-6">
        <label>Logo</label>
        @if(isset($category) && $category->logo)
            <div class="mb-2"><img src="{{ $category->logo_url }}" class="img-thumbnail" style="max-height:80px"></div>
        @endif
        <input type="file" name="logo" class="form-control-file" accept="image/*">
        <small class="form-text text-muted">Máx. 2MB</small>
    </div>
    <div class="form-group col-md-6">
        <label>Imagen de Portada</label>
        @if(isset($category) && $category->cover_image)
            <div class="mb-2"><img src="{{ $category->cover_url }}" class="img-thumbnail" style="max-height:80px"></div>
        @endif
        <input type="file" name="cover_image" class="form-control-file" accept="image/*">
        <small class="form-text text-muted">Máx. 5MB</small>
    </div>
</div>

<h6 class="mt-4 mb-3 text-primary"><i class="fa fa-phone"></i> Contacto</h6>
<div class="row">
    <div class="form-group col-md-6">
        <label>Nombre de contacto</label>
        <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', $category->contact_name ?? '') }}">
    </div>
    <div class="form-group col-md-6">
        <label>Email de contacto</label>
        <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $category->contact_email ?? '') }}">
    </div>
    <div class="form-group col-md-6">
        <label>Teléfono</label>
        <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $category->contact_phone ?? '') }}">
    </div>
    <div class="form-group col-md-6">
        <label>WhatsApp</label>
        <input type="text" name="contact_whatsapp" class="form-control" value="{{ old('contact_whatsapp', $category->contact_whatsapp ?? '') }}" placeholder="+506 8888-8888">
    </div>
</div>

<h6 class="mt-4 mb-3 text-primary"><i class="fa fa-map-marker"></i> Ubicación</h6>
<div class="form-group">
    <label>Dirección</label>
    <textarea name="address" class="form-control" rows="2">{{ old('address', $category->address ?? '') }}</textarea>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <label>Latitud</label>
        <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $category->latitude ?? '') }}" placeholder="Ej: 9.93420">
    </div>
    <div class="form-group col-md-6">
        <label>Longitud</label>
        <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $category->longitude ?? '') }}" placeholder="Ej: -84.08780">
    </div>
</div>

<h6 class="mt-4 mb-3 text-primary"><i class="fa fa-globe"></i> Web y Redes Sociales</h6>
<div class="row">
    <div class="form-group col-md-4">
        <label>Sitio Web</label>
        <input type="url" name="website" class="form-control" value="{{ old('website', $category->website ?? '') }}" placeholder="https://...">
    </div>
    <div class="form-group col-md-4">
        <label>Facebook</label>
        <input type="text" name="social_facebook" class="form-control" value="{{ old('social_facebook', $category->social_facebook ?? '') }}" placeholder="URL o usuario">
    </div>
    <div class="form-group col-md-4">
        <label>Instagram</label>
        <input type="text" name="social_instagram" class="form-control" value="{{ old('social_instagram', $category->social_instagram ?? '') }}" placeholder="@usuario">
    </div>
</div>

<div class="form-group mt-3">
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
               {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="is_active">Categoría activa</label>
    </div>
</div>
