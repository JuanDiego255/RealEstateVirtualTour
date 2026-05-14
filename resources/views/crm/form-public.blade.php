<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $form->title }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .form-card { max-width: 560px; margin: 48px auto; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(0,0,0,.10); overflow: hidden; }
        .form-header { background: #1a1a2e; padding: 28px 32px; color: #fff; }
        .form-header h1 { font-size: 22px; font-weight: 700; margin: 0 0 6px; }
        .form-header p  { font-size: 14px; color: rgba(255,255,255,.7); margin: 0; }
        .form-body { padding: 28px 32px; }
        .form-group label { font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; }
        .form-control { border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 14px; padding: 10px 14px; }
        .form-control:focus { border-color: #c2ac1f; box-shadow: none; }
        .btn-submit { background: #c2ac1f; color: #fff; border: none; border-radius: 10px; padding: 12px 28px; font-size: 15px; font-weight: 700; width: 100%; cursor: pointer; }
        .btn-submit:hover { background: #a89316; }
        .success-msg { background: #d1fae5; border: 1px solid #6ee7b7; border-radius: 12px; padding: 20px; text-align: center; color: #065f46; font-size: 15px; }
    </style>
</head>
<body>
    <div class="form-card">
        <div class="form-header">
            <h1>{{ $form->title }}</h1>
            @if($form->description)
            <p>{{ $form->description }}</p>
            @endif
        </div>
        <div class="form-body">
            @if(session('success'))
            <div class="success-msg">
                <i style="font-size:32px;display:block;margin-bottom:10px;">&#x2705;</i>
                {{ session('success') }}
            </div>
            @else
            @if($errors->any())
            <div style="background:#fee2e2;border-radius:10px;padding:14px 18px;margin-bottom:18px;color:#991b1b;font-size:13px;">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
            @endif
            <form method="POST" action="{{ route('crm.form.submit', $form->slug) }}">
                @csrf
                @foreach($form->fields as $field)
                <div class="form-group">
                    <label>{{ $field['label'] }}@if(!empty($field['required'])) <span style="color:#ef4444;">*</span>@endif</label>
                    @if($field['type'] === 'textarea')
                        <textarea name="{{ $field['name'] }}" class="form-control" rows="4" {{ !empty($field['required']) ? 'required' : '' }}>{{ old($field['name']) }}</textarea>
                    @elseif($field['type'] === 'select' && !empty($field['options']))
                        <select name="{{ $field['name'] }}" class="form-control" {{ !empty($field['required']) ? 'required' : '' }}>
                            <option value="">— Seleccionar —</option>
                            @foreach($field['options'] as $opt)
                            <option value="{{ $opt }}" {{ old($field['name']) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    @elseif($field['type'] === 'checkbox')
                        <label style="display:flex;align-items:center;gap:8px;text-transform:none;font-size:14px;font-weight:400;color:#444;">
                            <input type="checkbox" name="{{ $field['name'] }}" value="1" {{ old($field['name']) ? 'checked' : '' }}>
                            {{ $field['label'] }}
                        </label>
                    @else
                        <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" class="form-control"
                               value="{{ old($field['name']) }}"
                               {{ !empty($field['required']) ? 'required' : '' }}>
                    @endif
                </div>
                @endforeach
                <button type="submit" class="btn-submit">Enviar</button>
            </form>
            @endif
        </div>
    </div>
</body>
</html>
