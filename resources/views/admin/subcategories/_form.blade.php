@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row">
    <div class="form-group col-md-8">
        <label>Nombre <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required
               value="{{ old('name', $subcategory->name ?? '') }}" placeholder="Ej: SUV, Sedán, Apartamentos">
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Sucursal</label>
        <input type="text" class="form-control" value="{{ $category->name }}" disabled>
    </div>
</div>

<div class="form-group">
    <label>Descripción</label>
    <textarea name="description" class="form-control" rows="3" placeholder="Descripción de la categoría">{{ old('description', $subcategory->description ?? '') }}</textarea>
</div>

<div class="form-group">
    <label>Imagen</label>
    @if(isset($subcategory) && $subcategory->image)
        <div class="mb-2"><img src="{{ route('file', $subcategory->image) }}" class="img-thumbnail" style="max-height:120px"></div>
    @endif
    <input type="file" name="image" class="form-control-file" accept="image/*">
    <small class="form-text text-muted">Máx. 10MB. Imagen representativa de la categoría.</small>
</div>

<div class="form-group mt-3">
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
               {{ old('is_active', $subcategory->is_active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="is_active">Categoría activa</label>
    </div>
</div>
