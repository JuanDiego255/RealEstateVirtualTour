<style>
    .quote-result {
        background: rgba(0,0,0,0.3);
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 20px;
        text-align: center;
    }
    .quote-result .monthly {
        font-size: 42px;
        font-weight: 700;
        color: var(--accent, #c2ac1f);
        margin: 10px 0;
    }
    .quote-result .label {
        font-size: 13px;
        color: rgba(255,255,255,0.6);
    }
    .quote-details {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(255,255,255,0.1);
        font-size: 13px;
    }
    .quote-details span {
        color: rgba(255,255,255,0.7);
    }
    .quote-details strong {
        color: #fff;
    }
    .range-container {
        margin-bottom: 20px;
    }
    .range-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 13px;
        color: rgba(255,255,255,0.7);
    }
    .range-header .value {
        color: var(--accent, #c2ac1f);
        font-weight: 600;
    }
    input[type="range"] {
        width: 100%;
        height: 8px;
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
        outline: none;
        -webkit-appearance: none;
    }
    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 24px;
        height: 24px;
        background: var(--accent, #c2ac1f);
        border-radius: 50%;
        cursor: pointer;
    }
    .term-buttons {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        margin-bottom: 20px;
    }
    .term-btn {
        padding: 12px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 10px;
        color: #fff;
        cursor: pointer;
        text-align: center;
        font-size: 13px;
        transition: all 0.2s;
    }
    .term-btn.active {
        background: var(--accent, #c2ac1f);
        color: #000;
        border-color: var(--accent, #c2ac1f);
    }
    .frequency-selector {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .frequency-btn {
        flex: 1;
        padding: 12px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 10px;
        color: #fff;
        cursor: pointer;
        text-align: center;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
    }
    .frequency-btn.active {
        background: var(--accent, #c2ac1f);
        color: #000;
        border-color: var(--accent, #c2ac1f);
    }
    .frequency-btn:hover:not(.active) {
        background: rgba(255,255,255,0.12);
    }
    .quote-actions {
        display: flex;
        gap: 10px;
    }
    .quote-actions button {
        flex: 1;
        padding: 14px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .btn-quote-primary {
        background: var(--accent, #c2ac1f);
        color: #000;
        border: none;
    }
    .btn-quote-secondary {
        background: rgba(255,255,255,0.08);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.12);
    }
</style>

<div class="modal-kiosk" id="quoteModal">
    <div class="modal-content-kiosk" style="max-width: 480px;">
        <div class="modal-header-kiosk">
            <h3>
                <i class="fas fa-calculator" style="color: var(--accent, #c2ac1f);"></i>
                Cotizador
            </h3>
            <button class="modal-close" onclick="closeModal('quoteModal')">&times;</button>
        </div>

        <input type="hidden" id="quoteVehicleId" value="{{ $vehicle->id ?? '' }}">
        <input type="hidden" id="quoteVehiclePrice" value="{{ $vehicle->price ?? 0 }}">

        <!-- Vehicle Price Display -->
        <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; margin-bottom: 20px;">
            <div style="font-size: 13px; color: rgba(255,255,255,0.6);">Precio del vehículo</div>
            <div style="font-size: 24px; font-weight: 700;" id="quotePriceDisplay">
                ₡{{ number_format($vehicle->price ?? 0) }}
            </div>
        </div>

        <!-- Down Payment Slider -->
        <div class="range-container">
            <div class="range-header">
                <span>Prima (enganche)</span>
                <span class="value"><span id="downPaymentPercent">50</span>%</span>
            </div>
            <input type="range" id="downPaymentSlider" min="10" max="60" value="50" step="5">
            <div style="text-align: center; margin-top: 8px; font-size: 18px; font-weight: 600;" id="downPaymentDisplay">
                ₡0
            </div>
        </div>

        <!-- Payment Frequency Selection -->
        <div style="margin-bottom: 15px;">
            <div style="font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 10px;">Frecuencia de pago</div>
            <div class="frequency-selector">
                <button type="button" class="frequency-btn active" data-frequency="monthly" onclick="selectFrequency(this)">
                    <i class="fas fa-calendar-alt"></i> Mensual
                </button>
                <button type="button" class="frequency-btn" data-frequency="annual" onclick="selectFrequency(this)">
                    <i class="fas fa-calendar"></i> Anual
                </button>
            </div>
            <input type="hidden" id="paymentFrequency" value="monthly">
        </div>

        <!-- Term Selection -->
        <div style="margin-bottom: 15px;">
            <div style="font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 10px;" id="termLabel">Plazo</div>
            <div class="term-buttons" id="termButtonsContainer">
                <button type="button" class="term-btn" data-months="12" onclick="selectTerm(this)">12 meses</button>
                <button type="button" class="term-btn" data-months="24" onclick="selectTerm(this)">24 meses</button>
                <button type="button" class="term-btn active" data-months="36" onclick="selectTerm(this)">36 meses</button>
                <button type="button" class="term-btn" data-months="48" onclick="selectTerm(this)">48 meses</button>
                <button type="button" class="term-btn" data-months="60" onclick="selectTerm(this)">60 meses</button>
                <button type="button" class="term-btn" data-months="72" onclick="selectTerm(this)">72 meses</button>
                <button type="button" class="term-btn" data-months="84" onclick="selectTerm(this)">84 meses</button>
            </div>
            <input type="hidden" id="termMonths" value="36">
        </div>

        <!-- Interest Rate -->
        <div class="range-container">
            <div class="range-header">
                <span>Tasa de interés anual</span>
                <span class="value"><span id="interestRateDisplay">12</span>%</span>
            </div>
            <input type="range" id="interestRateSlider" min="0" max="25" value="12" step="0.5">
        </div>

        <!-- Result -->
        <div class="quote-result">
            <div class="label" id="paymentLabel">Cuota mensual estimada</div>
            <div class="monthly" id="monthlyPaymentDisplay">₡0</div>
            <div class="quote-details">
                <div class="d-none">
                    <span>Total intereses:</span><br>
                    <strong id="totalInterestDisplay">₡0</strong>
                </div>
                <div>
                    <span>Monto a financiar:</span><br>
                    <strong id="financedAmountDisplay">₡0</strong>
                </div>
                <div>
                    <span>Monto total:</span><br>
                    <strong id="totalAmountDisplay">₡0</strong>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div style="margin-bottom: 20px;">
            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 6px;">Tu nombre *</label>
                <input type="text" id="quoteCustomerName" placeholder="Tu nombre completo" required
                    style="width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; color: #fff; font-size: 14px; outline: none;">
            </div>
            <div>
                <label style="display: block; font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 6px;">Tu teléfono *</label>
                <input type="tel" id="quoteCustomerPhone" placeholder="8888-8888" required
                    style="width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; color: #fff; font-size: 14px; outline: none;">
            </div>
        </div>

        <!-- Actions -->
        <div class="quote-actions">
            <button type="button" class="btn-quote-secondary" onclick="closeModal('quoteModal')">
                Cerrar
            </button>
            <button type="button" class="btn-quote-primary" onclick="saveAndDownloadQuote()">
                <i class="fas fa-download"></i>
                Guardar Cotizacion
            </button>
        </div>
    </div>
</div>

<script>
let quoteTermMonths = 36;
let quotePaymentFrequency = 'monthly';

function selectFrequency(btn) {
    document.querySelectorAll('.frequency-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    quotePaymentFrequency = btn.dataset.frequency;
    document.getElementById('paymentFrequency').value = quotePaymentFrequency;

    // Update labels based on frequency
    if (quotePaymentFrequency === 'annual') {
        document.getElementById('paymentLabel').textContent = 'Cuota anual estimada';
        document.getElementById('termLabel').textContent = 'Plazo (años)';
    } else {
        document.getElementById('paymentLabel').textContent = 'Cuota mensual estimada';
        document.getElementById('termLabel').textContent = 'Plazo';
    }

    updateQuoteCalculation();
}

function selectTerm(btn) {
    document.querySelectorAll('.term-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    quoteTermMonths = parseInt(btn.dataset.months);
    document.getElementById('termMonths').value = quoteTermMonths;
    updateQuoteCalculation();
}

function updateQuoteCalculation() {
    const price = parseFloat(document.getElementById('quoteVehiclePrice').value) || 0;
    const downPercent = parseFloat(document.getElementById('downPaymentSlider').value) || 20;
    const downPayment = price * (downPercent / 100);
    const termMonths = parseInt(document.getElementById('termMonths').value) || 36;
    const interestRate = parseFloat(document.getElementById('interestRateSlider').value) || 12;
    const frequency = document.getElementById('paymentFrequency').value || 'monthly';

    const principal = price - downPayment;

    let payment, totalAmount, totalInterest, numPayments;

    if (frequency === 'annual') {
        // Calculo anual - metodo frances con tasa anual y periodos anuales
        const annualRate = interestRate / 100;
        const years = Math.ceil(termMonths / 12); // Convertir meses a años
        numPayments = years;

        if (interestRate === 0) {
            payment = principal / years;
        } else {
            // Formula francesa: C = P * (i * (1 + i)^n) / ((1 + i)^n - 1)
            payment = principal * (annualRate * Math.pow(1 + annualRate, years))
                / (Math.pow(1 + annualRate, years) - 1);
        }

        totalAmount = payment * years;
        totalInterest = totalAmount - principal;
    } else {
        // Calculo mensual - metodo frances con tasa mensual
        const monthlyRate = (interestRate / 100) / 12;
        numPayments = termMonths;

        if (interestRate === 0) {
            payment = principal / termMonths;
        } else {
            // Formula francesa: C = P * (i * (1 + i)^n) / ((1 + i)^n - 1)
            payment = principal * (monthlyRate * Math.pow(1 + monthlyRate, termMonths))
                / (Math.pow(1 + monthlyRate, termMonths) - 1);
        }

        totalAmount = payment * termMonths;
        totalInterest = totalAmount - principal;
    }

    // Update displays
    document.getElementById('downPaymentPercent').textContent = downPercent;
    document.getElementById('downPaymentDisplay').textContent = '₡' + Math.round(downPayment).toLocaleString('es-CR');
    document.getElementById('interestRateDisplay').textContent = interestRate;
    document.getElementById('monthlyPaymentDisplay').textContent = '₡' + Math.round(payment).toLocaleString('es-CR');
    document.getElementById('totalInterestDisplay').textContent = '₡' + Math.round(totalInterest).toLocaleString('es-CR');
    document.getElementById('financedAmountDisplay').textContent = '₡' + Math.round(principal).toLocaleString('es-CR');
    document.getElementById('totalAmountDisplay').textContent = '₡' + Math.round(totalAmount).toLocaleString('es-CR');
}

document.getElementById('downPaymentSlider').addEventListener('input', updateQuoteCalculation);
document.getElementById('interestRateSlider').addEventListener('input', updateQuoteCalculation);

// Initialize calculation
document.addEventListener('DOMContentLoaded', updateQuoteCalculation);

async function saveAndDownloadQuote() {
    const vehicleId = document.getElementById('quoteVehicleId').value;
    const price = document.getElementById('quoteVehiclePrice').value;
    const downPercent = document.getElementById('downPaymentSlider').value;
    const downPayment = price * (downPercent / 100);
    const termMonths = document.getElementById('termMonths').value;
    const interestRate = document.getElementById('interestRateSlider').value;
    const paymentFrequency = document.getElementById('paymentFrequency').value || 'monthly';

    // Get customer info from form fields
    const name = document.getElementById('quoteCustomerName').value.trim();
    const phone = document.getElementById('quoteCustomerPhone').value.trim();

    // Validate required fields
    if (!name) {
        showToast('Por favor ingresa tu nombre', 'error');
        document.getElementById('quoteCustomerName').focus();
        return;
    }
    if (!phone) {
        showToast('Por favor ingresa tu teléfono', 'error');
        document.getElementById('quoteCustomerPhone').focus();
        return;
    }

    try {
        const response = await fetch('{{ route("kiosk.quote.save") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                vehicle_id: vehicleId,
                vehicle_price: price,
                down_payment: downPayment,
                term_months: termMonths,
                interest_rate: interestRate,
                payment_frequency: paymentFrequency,
                customer_name: name,
                customer_phone: phone,
                customer_email: null,
                event_name: typeof eventName !== 'undefined' ? eventName : null
            })
        });

        const data = await response.json();
        if (data.success) {
            // Open PDF in new tab
            window.open(`/kiosk/quote/${data.quote_id}/pdf`, '_blank');
            showToast('Cotización guardada! Se descargará el PDF.', 'success');
            // Reset form fields
            document.getElementById('quoteCustomerName').value = '';
            document.getElementById('quoteCustomerPhone').value = '';
            closeModal('quoteModal');
        } else {
            showToast('Error al guardar la cotización.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al procesar. Intenta de nuevo.', 'error');
    }
}
</script>
