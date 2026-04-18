<div class="row">
    <div class="form-group col-md-6">
        <label>Nombre del premio <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required maxlength="80"
               placeholder="Ej: Descuento 10%, Llavero corporativo, Consultoría gratis"
               value="{{ old('name', $rafflePrize->name ?? '') }}">
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-3">
        <label>Emoji</label>
        <input type="text" name="emoji" class="form-control" maxlength="10"
               placeholder="🎁"
               value="{{ old('emoji', $rafflePrize->emoji ?? '') }}"
               style="font-size:22px;">
        <small class="text-muted">Se muestra en la ruleta y en el resultado</small>
    </div>
    <div class="form-group col-md-3">
        <label>Color en la ruleta</label>
        <input type="color" name="color" class="form-control" style="height:42px;padding:4px;"
               value="{{ old('color', $rafflePrize->color ?? '#e74c3c') }}">
    </div>
</div>

<div class="row">
    <div class="form-group col-md-4">
        <label>Cantidad disponible</label>
        <input type="number" name="quantity" class="form-control" min="1" max="9999"
               placeholder="Dejar vacío = sin límite"
               value="{{ old('quantity', $rafflePrize->quantity ?? '') }}">
        <small class="text-muted">Al agotarse se elimina automáticamente de la ruleta</small>
    </div>
    <div class="form-group col-md-4">
        <label>Peso de probabilidad (1–100) <span class="text-danger">*</span></label>
        <input type="number" name="weight" class="form-control" min="1" max="100" required
               value="{{ old('weight', $rafflePrize->weight ?? 10) }}">
        <small class="text-muted">
            Mayor = más probable &nbsp;|&nbsp;
            Descuentos: <strong>3–5</strong> &nbsp;|&nbsp;
            Normal: <strong>10</strong> &nbsp;|&nbsp;
            Grandes premios: <strong>15+</strong>
        </small>
    </div>
    <div class="form-group col-md-4">
        <label>Estado</label>
        <div class="custom-control custom-switch mt-2">
            <input type="checkbox" class="custom-control-input" id="activeSwitch"
                   name="active" value="1"
                   {{ old('active', ($rafflePrize->active ?? true)) ? 'checked' : '' }}>
            <label class="custom-control-label" for="activeSwitch">Activo en la ruleta</label>
        </div>
        <small class="text-muted">Si está inactivo, no aparece en la ruleta del kiosko</small>
    </div>
</div>
