@if($errors->any())
<div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:16px;">
    <ul style="margin:0;padding-left:18px;font-size:13px;color:#991b1b;">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
    <div>
        <label style="font-size:12px;font-weight:700;color:#888;text-transform:uppercase;">Nombre *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $template->name ?? '') }}" required>
    </div>
    <div>
        <label style="font-size:12px;font-weight:700;color:#888;text-transform:uppercase;">Canal *</label>
        <select name="channel" class="form-control" required>
            <option value="">— Canal —</option>
            <option value="whatsapp" {{ old('channel', $template->channel ?? '') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
            <option value="email" {{ old('channel', $template->channel ?? '') === 'email' ? 'selected' : '' }}>Email</option>
        </select>
    </div>
    <div>
        <label style="font-size:12px;font-weight:700;color:#888;text-transform:uppercase;">Etapa (opcional)</label>
        <select name="stage" class="form-control">
            <option value="">— Todas las etapas —</option>
            @foreach(\App\Lead::getStatuses() as $k => $v)
            <option value="{{ $k }}" {{ old('stage', $template->stage ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-size:12px;font-weight:700;color:#888;text-transform:uppercase;">Orden</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $template->sort_order ?? 0) }}" min="0">
    </div>
</div>
<div style="margin-bottom:16px;">
    <label style="font-size:12px;font-weight:700;color:#888;text-transform:uppercase;">Asunto (email)</label>
    <input type="text" name="subject" class="form-control" value="{{ old('subject', $template->subject ?? '') }}">
</div>
<div style="margin-bottom:16px;">
    <label style="font-size:12px;font-weight:700;color:#888;text-transform:uppercase;">Cuerpo del mensaje *</label>
    <div style="font-size:11px;color:#aaa;margin-bottom:6px;">
        Variables: <code>@{{nombre}}</code>, <code>@{{agente}}</code>, <code>@{{propiedad}}</code>, <code>@{{fecha}}</code>, <code>@{{enlace}}</code>, <code>@{{empresa}}</code>
    </div>
    <textarea name="body" rows="8" class="form-control" required style="font-family:monospace;font-size:13px;">{{ old('body', $template->body ?? '') }}</textarea>
</div>
<div>
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
        <span style="font-size:13px;font-weight:600;color:#555;">Plantilla activa</span>
    </label>
</div>
