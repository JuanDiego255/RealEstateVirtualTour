@extends('admin.main')
@section('title', 'Nueva Cotización')
@section('content')
@include('admin.crm.layouts._crm-styles')

<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-plus-circle"></i> Nueva Cotización</h2>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.quotes.index') }}" class="action-btn secondary"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>
    </div>

    @if($errors->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:16px;">
        <ul style="margin:0;padding-left:18px;font-size:13px;color:#991b1b;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.crm.quotes.store') }}" id="quote-form">
        @csrf

        <div class="dashboard-card" style="margin-bottom:18px;">
            <div class="dc-header"><h5><i class="fa fa-info-circle" style="color:#c2ac1f;"></i> Información General</h5></div>
            <div class="dc-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Lead *</label>
                            <select name="lead_id" class="form-control" required>
                                <option value="">— Seleccionar lead —</option>
                                @foreach($leads as $l)
                                <option value="{{ $l->id }}" {{ old('lead_id', $lead?->id) == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Propiedad (opcional)</label>
                            <select name="property_id" class="form-control">
                                <option value="">— Sin propiedad —</option>
                                @foreach($properties as $p)
                                <option value="{{ $p->id }}" {{ old('property_id') == $p->id ? 'selected' : '' }}>{{ $p->title ?? $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Título de la cotización *</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Ej: Propuesta casa en Escazú">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Moneda</label>
                            <select name="currency" class="form-control">
                                <option value="CRC" {{ old('currency','CRC') === 'CRC' ? 'selected' : '' }}>₡ CRC</option>
                                <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>$ USD</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label style="font-weight:600;font-size:13px;color:#1a1a2e;">Vigencia (días)</label>
                            <input type="number" name="validity_days" class="form-control" value="{{ old('validity_days', 30) }}" min="1" max="365">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="dashboard-card" style="margin-bottom:18px;">
            <div class="dc-header">
                <h5><i class="fa fa-list" style="color:#c2ac1f;"></i> Ítems de la Cotización</h5>
                <button type="button" id="add-item" class="action-btn primary" style="font-size:12px;padding:5px 12px;">
                    <i class="fa fa-plus"></i> Agregar ítem
                </button>
            </div>
            <div class="dc-body">
                <table class="crm-table" id="items-table">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th style="width:90px;text-align:center;">Cant.</th>
                            <th style="width:150px;text-align:right;">Precio unit.</th>
                            <th style="width:150px;text-align:right;">Subtotal</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <tr class="item-row">
                            <td><input type="text" name="items[0][description]" class="form-control form-control-sm" placeholder="Descripción" required></td>
                            <td><input type="number" name="items[0][qty]" class="form-control form-control-sm item-qty" value="1" min="0" step="any" required style="text-align:center;"></td>
                            <td><input type="number" name="items[0][price]" class="form-control form-control-sm item-price" value="0" min="0" step="any" required style="text-align:right;"></td>
                            <td style="text-align:right;font-weight:600;" class="item-subtotal">0</td>
                            <td><button type="button" class="action-btn danger remove-item" style="padding:4px 8px;font-size:11px;"><i class="fa fa-times"></i></button></td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top:18px;display:flex;justify-content:flex-end;">
                    <div style="width:280px;">
                        <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;">
                            <span style="color:#888;">Subtotal</span>
                            <span id="display-subtotal" style="font-weight:600;">0</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:13px;">
                            <span style="color:#888;">Descuento (%)</span>
                            <input type="number" name="discount_pct" id="discount-input" class="form-control form-control-sm" value="{{ old('discount_pct', 0) }}" min="0" max="100" step="0.01" style="width:80px;text-align:right;">
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:10px 0;font-size:16px;font-weight:700;border-top:2px solid #1a1a2e;margin-top:6px;">
                            <span style="color:#1a1a2e;">TOTAL</span>
                            <span id="display-total" style="color:#c2ac1f;">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="dashboard-card" style="margin-bottom:18px;">
            <div class="dc-header"><h5><i class="fa fa-sticky-note-o" style="color:#c2ac1f;"></i> Notas y Condiciones</h5></div>
            <div class="dc-body">
                <textarea name="notes" rows="4" class="form-control" placeholder="Condiciones, términos, observaciones...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <a href="{{ route('admin.crm.quotes.index') }}" class="action-btn secondary">Cancelar</a>
            <button type="submit" class="action-btn primary"><i class="fa fa-save"></i> Guardar Cotización</button>
        </div>
    </form>
</div>

@push('script')
<script>
(function() {
    let rowIndex = 1;

    function formatNum(n) {
        return new Intl.NumberFormat('es-CR').format(Math.round(n));
    }

    function recalculate() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty   = parseFloat(row.querySelector('.item-qty').value)   || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const sub   = qty * price;
            row.querySelector('.item-subtotal').textContent = formatNum(sub);
            subtotal += sub;
        });
        const disc  = parseFloat(document.getElementById('discount-input').value) || 0;
        const total = subtotal * (1 - disc / 100);
        document.getElementById('display-subtotal').textContent = formatNum(subtotal);
        document.getElementById('display-total').textContent    = formatNum(total);
    }

    document.getElementById('add-item').addEventListener('click', function() {
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td><input type="text" name="items[${rowIndex}][description]" class="form-control form-control-sm" placeholder="Descripción" required></td>
            <td><input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm item-qty" value="1" min="0" step="any" required style="text-align:center;"></td>
            <td><input type="number" name="items[${rowIndex}][price]" class="form-control form-control-sm item-price" value="0" min="0" step="any" required style="text-align:right;"></td>
            <td style="text-align:right;font-weight:600;" class="item-subtotal">0</td>
            <td><button type="button" class="action-btn danger remove-item" style="padding:4px 8px;font-size:11px;"><i class="fa fa-times"></i></button></td>`;
        document.getElementById('items-body').appendChild(row);
        rowIndex++;
        row.querySelectorAll('input').forEach(i => i.addEventListener('input', recalculate));
        row.querySelector('.remove-item').addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) { row.remove(); recalculate(); }
        });
    });

    document.querySelectorAll('.item-row input').forEach(i => i.addEventListener('input', recalculate));
    document.getElementById('discount-input').addEventListener('input', recalculate);
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) { btn.closest('tr').remove(); recalculate(); }
        });
    });
    recalculate();
})();
</script>
@endpush
@endsection
