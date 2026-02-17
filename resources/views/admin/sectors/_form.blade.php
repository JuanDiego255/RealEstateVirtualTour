@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="form-group col-md-6">
        <label for="name">Nombre del sector <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="name" id="name"
               value="{{ old('name', $sector->name ?? '') }}" required placeholder="Ej: Bienes Raíces">
    </div>

    <div class="form-group col-md-3">
        <label for="icon">Ícono (Font Awesome)</label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa {{ old('icon', $sector->icon ?? 'fa-folder') }}"></i></span>
            </div>
            <input type="text" class="form-control" name="icon" id="icon"
                   value="{{ old('icon', $sector->icon ?? '') }}" placeholder="fa-home">
        </div>
        <small class="text-muted">Ej: fa-home, fa-car, fa-building</small>
    </div>

    <div class="form-group col-md-3">
        <label for="color">Color</label>
        <input type="color" class="form-control" name="color" id="color"
               value="{{ old('color', $sector->color ?? '#2563eb') }}" style="height: 38px;">
    </div>

    <div class="form-group col-md-12">
        <label for="description">Descripción</label>
        <textarea class="form-control" name="description" id="description" rows="3"
                  placeholder="Descripción del sector">{{ old('description', $sector->description ?? '') }}</textarea>
    </div>

    <div class="form-group col-md-6">
        <label for="image">Imagen</label>
        @if(isset($sector) && $sector->image)
            <div class="mb-2">
                <img src="{{ $sector->image_url }}" class="img-thumbnail" style="max-height: 100px;">
            </div>
        @endif
        <input type="file" class="form-control-file" name="image" accept="image/*">
    </div>

    <div class="form-group col-md-3">
        <label for="sort_order">Orden</label>
        <input type="number" class="form-control" name="sort_order" id="sort_order"
               value="{{ old('sort_order', $sector->sort_order ?? 0) }}" min="0">
    </div>

    <div class="form-group col-md-3">
        <label class="d-block">&nbsp;</label>
        <div class="custom-control custom-switch mt-2">
            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                   {{ old('is_active', $sector->is_active ?? true) ? 'checked' : '' }}>
            <label class="custom-control-label" for="is_active">Activo</label>
        </div>
    </div>
</div>
