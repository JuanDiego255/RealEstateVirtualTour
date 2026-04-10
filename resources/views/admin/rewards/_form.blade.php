{{-- Partial compartido para crear/editar recompensas --}}
@php $types = \App\Models\AgentReward::types(); @endphp

<div class="form-group">
    <label>Nombre de la Recompensa *</label>
    <input type="text" name="name" value="{{ old('name', $reward->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror" required placeholder="Ej: Bono de Excelencia">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label>Descripción</label>
    <textarea name="description" class="form-control" rows="2"
              placeholder="Descripción breve de la recompensa...">{{ old('description', $reward->description ?? '') }}</textarea>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Conversiones Mínimas *</label>
        <input type="number" name="min_conversions"
               value="{{ old('min_conversions', $reward->min_conversions ?? 0) }}"
               class="form-control @error('min_conversions') is-invalid @enderror"
               min="0" required>
        @error('min_conversions')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-6">
        <label>Conversiones Máximas <small class="text-muted">(dejar vacío = sin límite)</small></label>
        <input type="number" name="max_conversions"
               value="{{ old('max_conversions', $reward->max_conversions ?? '') }}"
               class="form-control @error('max_conversions') is-invalid @enderror"
               min="0">
        @error('max_conversions')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="form-group">
    <label>Tipo de Recompensa *</label>
    <select name="reward_type" id="rewardType" class="form-control @error('reward_type') is-invalid @enderror" required>
        @foreach($types as $key => $type)
            <option value="{{ $key }}" {{ old('reward_type', $reward->reward_type ?? '') === $key ? 'selected' : '' }}>
                {{ $type['label'] }}
            </option>
        @endforeach
    </select>
    @error('reward_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-row" id="valueFields">
    <div class="form-group col-md-6">
        <label>Valor</label>
        <input type="number" name="reward_value" step="0.01"
               value="{{ old('reward_value', $reward->reward_value ?? 0) }}"
               class="form-control @error('reward_value') is-invalid @enderror"
               min="0">
        @error('reward_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-6">
        <label>Moneda</label>
        <select name="reward_currency" class="form-control">
            <option value="CRC" {{ old('reward_currency', $reward->reward_currency ?? 'CRC') === 'CRC' ? 'selected' : '' }}>₡ Colones (CRC)</option>
            <option value="USD" {{ old('reward_currency', $reward->reward_currency ?? '') === 'USD' ? 'selected' : '' }}>$ Dólares (USD)</option>
        </select>
    </div>
</div>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" class="custom-control-input" id="isActive" name="is_active" value="1"
               {{ old('is_active', $reward->is_active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="isActive">Recompensa Activa</label>
    </div>
</div>

<script>
document.getElementById('rewardType')?.addEventListener('change', function() {
    var type = this.value;
    var noValue = ['gift','recognition','vacation_day'];
    document.getElementById('valueFields').style.display = noValue.includes(type) ? 'none' : '';
});
// Init on load
(function() {
    var el = document.getElementById('rewardType');
    if (!el) return;
    var noValue = ['gift','recognition','vacation_day'];
    document.getElementById('valueFields').style.display = noValue.includes(el.value) ? 'none' : '';
})();
</script>
