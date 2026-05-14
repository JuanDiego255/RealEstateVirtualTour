@if($errors->any())
<div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:16px;">
    <ul style="margin:0;padding-left:18px;font-size:13px;color:#991b1b;">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
    <div style="grid-column:1/-1;">
        <label style="font-size:12px;font-weight:700;color:#888;text-transform:uppercase;">Nombre *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $rule->name ?? '') }}" required>
    </div>
    <div>
        <label style="font-size:12px;font-weight:700;color:#888;text-transform:uppercase;">Etapa que dispara *</label>
        <select name="trigger_stage" class="form-control" required>
            <option value="">— Seleccionar —</option>
            @foreach($statuses as $k => $v)
            <option value="{{ $k }}" {{ old('trigger_stage', $rule->trigger_stage ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-size:12px;font-weight:700;color:#888;text-transform:uppercase;">Días inactivo *</label>
        <input type="number" name="days_inactive" class="form-control" value="{{ old('days_inactive', $rule->days_inactive ?? 5) }}" min="1" max="365" required>
    </div>
    <div>
        <label style="font-size:12px;font-weight:700;color:#888;text-transform:uppercase;">Acción *</label>
        <select name="action" class="form-control" required>
            <option value="">— Seleccionar —</option>
            @foreach($actions as $k => $v)
            <option value="{{ $k }}" {{ old('action', $rule->action ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-size:12px;font-weight:700;color:#888;text-transform:uppercase;">Mensaje de acción</label>
        <input type="text" name="action_message" class="form-control" value="{{ old('action_message', $rule->action_message ?? '') }}" placeholder="Texto de la alerta/notificación">
    </div>
</div>
<div>
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }}>
        <span style="font-size:13px;font-weight:600;color:#555;">Regla activa</span>
    </label>
</div>
