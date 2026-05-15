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

        {{-- Quote type toggle --}}
        <div class="dashboard-card mb-4" style="margin-bottom:18px;">
            <div class="dc-header" style="background:#1a1a2e;">
                <h5 style="color:#fff;"><i class="fa fa-tag" style="color:#c2ac1f;"></i> Tipo de Cotización</h5>
            </div>
            <div class="dc-body" style="padding:20px;">
                <div style="display:flex; gap:12px;">
                    <label style="flex:1; cursor:pointer;">
                        <input type="radio" name="quote_type" value="property" id="qt-property" checked style="margin-right:6px;">
                        <span style="font-weight:600; font-size:14px;"><i class="fa fa-home"></i> Inmueble / Propiedad</span>
                    </label>
                    <label style="flex:1; cursor:pointer;">
                        <input type="radio" name="quote_type" value="vehicle" id="qt-vehicle" style="margin-right:6px;">
                        <span style="font-weight:600; font-size:14px;"><i class="fa fa-car"></i> Vehículo</span>
                    </label>
                </div>
            </div>
        </div>

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

        {{-- Vehicle quote fields (shown when type=vehicle) --}}
        <div id="vehicle-quote-section" class="dashboard-card" style="display:none; margin-bottom:18px;">
            <div class="dc-header" style="background:#1a1a2e;">
                <h5 style="color:#fff;"><i class="fa fa-car" style="color:#c2ac1f;"></i> Datos del Vehículo y Financiamiento</h5>
            </div>
            <div class="dc-body" style="padding:24px;">
                {{-- Vehicle selector --}}
                <div class="form-group mb-4" style="margin-bottom:16px;">
                    <label style="font-weight:600; font-size:13px; color:#444;">Vehículo <span class="text-danger" id="veh-req">*</span></label>
                    <select name="property_id" id="vehicle-select" class="form-control" style="border-radius:9px;">
                        <option value="">— Seleccionar vehículo —</option>
                        @foreach(\App\Properties::vehicles()->whereHas('category', fn($q) => $q->where('company_id', auth()->user()->company_id))->get() as $veh)
                            <option value="{{ $veh->id }}">
                                {{ $veh->brand ?? '' }} {{ $veh->model ?? '' }}{{ ($veh->year ?? '') ? ' ('.$veh->year.')' : '' }} — {{ $veh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-weight:600; font-size:13px; color:#444;">Precio del vehículo</label>
                            <input type="number" name="vq_vehicle_price" id="vq-price" class="form-control" step="0.01" min="0" placeholder="0.00" style="border-radius:9px;" oninput="calcQuote()">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-weight:600; font-size:13px; color:#444;">Prima (%)</label>
                            <input type="number" name="vq_down_payment_pct" id="vq-pct" class="form-control" step="0.1" min="0" max="100" value="50" style="border-radius:9px;" oninput="calcQuote()">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-weight:600; font-size:13px; color:#444;">Prima (monto)</label>
                            <input type="number" name="vq_down_payment" id="vq-down" class="form-control" step="0.01" readonly style="border-radius:9px; background:#f8f8f8;">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-weight:600; font-size:13px; color:#444;">Plazo (meses)</label>
                            <select name="vq_term_months" id="vq-term" class="form-control" style="border-radius:9px;" onchange="calcQuote()">
                                <option value="12">12 meses</option>
                                <option value="24">24 meses</option>
                                <option value="36" selected>36 meses</option>
                                <option value="48">48 meses</option>
                                <option value="60">60 meses</option>
                                <option value="72">72 meses</option>
                                <option value="84">84 meses</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-weight:600; font-size:13px; color:#444;">Tasa de interés (anual %)</label>
                            <input type="number" name="vq_interest_rate_pct" id="vq-rate-pct" class="form-control" step="0.01" min="0" max="100" placeholder="Ej: 12.5" style="border-radius:9px;" oninput="calcQuote()">
                            {{-- Store as decimal (12.5% → 0.125) --}}
                            <input type="hidden" name="vq_interest_rate" id="vq-rate">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-weight:600; font-size:13px; color:#444;">Frecuencia de pago</label>
                            <select name="vq_payment_frequency" id="vq-freq" class="form-control" style="border-radius:9px;" onchange="calcQuote()">
                                <option value="monthly">Mensual</option>
                                <option value="annual">Anual</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Calculation result --}}
                <div id="vq-result" style="display:none; background:linear-gradient(135deg,#1a1a2e,#2d2d4e); border-radius:12px; padding:20px; margin-top:10px;">
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; text-align:center;">
                        <div>
                            <div style="font-size:11px; color:#aaa; text-transform:uppercase; margin-bottom:4px;">Cuota estimada</div>
                            <div id="vq-payment-display" style="font-size:22px; font-weight:700; color:#c2ac1f;"></div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:#aaa; text-transform:uppercase; margin-bottom:4px;">Total intereses</div>
                            <div id="vq-interest-display" style="font-size:18px; font-weight:600; color:#fff;"></div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:#aaa; text-transform:uppercase; margin-bottom:4px;">Total a pagar</div>
                            <div id="vq-total-display" style="font-size:18px; font-weight:600; color:#fff;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div id="property-quote-section" class="dashboard-card" style="margin-bottom:18px;">
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
// Quote type toggle
document.querySelectorAll('input[name="quote_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        var isVehicle = this.value === 'vehicle';
        document.getElementById('vehicle-quote-section').style.display = isVehicle ? 'block' : 'none';
        var propSection = document.getElementById('property-quote-section');
        if (propSection) propSection.style.display = isVehicle ? 'none' : 'block';
        // Toggle required on items inputs
        document.querySelectorAll('#property-quote-section input[required], #property-quote-section select[required]').forEach(function(el) {
            el.required = !isVehicle;
        });
    });
});

// Vehicle quote calculator
function calcQuote() {
    var price    = parseFloat(document.getElementById('vq-price').value) || 0;
    var pct      = parseFloat(document.getElementById('vq-pct').value) || 50;
    var term     = parseInt(document.getElementById('vq-term').value) || 36;
    var ratePct  = parseFloat(document.getElementById('vq-rate-pct').value) || 0;
    var freq     = document.getElementById('vq-freq').value;

    var downPayment = price * (pct / 100);
    document.getElementById('vq-down').value = downPayment.toFixed(2);

    var rate = ratePct / 100;
    document.getElementById('vq-rate').value = rate;

    var principal = price - downPayment;
    var periodsPerYear = freq === 'annual' ? 1 : 12;
    var nPeriods = freq === 'annual' ? Math.ceil(term / 12) : term;
    var periodicRate = rate / periodsPerYear;

    var payment;
    if (rate > 0 && nPeriods > 0) {
        payment = principal * (periodicRate * Math.pow(1 + periodicRate, nPeriods)) / (Math.pow(1 + periodicRate, nPeriods) - 1);
    } else {
        payment = nPeriods > 0 ? principal / nPeriods : 0;
    }

    var totalPaid = payment * nPeriods;
    var totalInterest = Math.max(totalPaid - principal, 0);
    var grandTotal = downPayment + totalPaid;

    var fmt = function(n) { return n.toLocaleString('es-CR', {minimumFractionDigits:2, maximumFractionDigits:2}); };

    if (price > 0) {
        document.getElementById('vq-result').style.display = 'block';
        document.getElementById('vq-payment-display').textContent = '₡' + fmt(payment);
        document.getElementById('vq-interest-display').textContent = '₡' + fmt(totalInterest);
        document.getElementById('vq-total-display').textContent = '₡' + fmt(grandTotal);
    }
}

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
