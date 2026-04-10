<style>
    .modal-kiosk {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.9);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 20px;
    }
    .modal-kiosk.active { display: flex; }
    .modal-content-kiosk {
        background: #1a1a2e;
        border-radius: 24px;
        max-width: 450px;
        width: 100%;
        padding: 30px;
    }
    .modal-header-kiosk {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    .modal-header-kiosk h3 {
        font-size: 22px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .modal-close {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
        border: none;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
    }
    .form-group-kiosk {
        margin-bottom: 18px;
    }
    .form-group-kiosk label {
        display: block;
        font-size: 13px;
        color: rgba(255,255,255,0.7);
        margin-bottom: 8px;
    }
    .form-control-kiosk {
        width: 100%;
        padding: 14px 18px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 12px;
        color: #fff;
        font-size: 16px;
    }
    .form-control-kiosk:focus {
        outline: none;
        border-color: var(--accent, #c2ac1f);
    }
    .btn-submit-kiosk {
        width: 100%;
        padding: 16px;
        background: var(--accent, #c2ac1f);
        color: #000;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.2s;
    }
    .btn-submit-kiosk:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(194, 172, 31, 0.3);
    }
    .interest-levels {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .interest-btn {
        flex: 1;
        padding: 12px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 10px;
        color: #fff;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s;
    }
    .interest-btn.active {
        background: var(--accent, #c2ac1f);
        color: #000;
        border-color: var(--accent, #c2ac1f);
    }
    .interest-btn i {
        display: block;
        font-size: 20px;
        margin-bottom: 5px;
    }
    .category-btns {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 8px;
        margin-bottom: 20px;
    }
    .category-btn {
        padding: 10px 6px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 10px;
        color: #fff;
        cursor: pointer;
        text-align: center;
        font-size: 12px;
        transition: all 0.2s;
        line-height: 1.3;
    }
    .category-btn.active {
        background: var(--accent, #c2ac1f);
        color: #000;
        border-color: var(--accent, #c2ac1f);
        font-weight: 600;
    }
    .category-btn i {
        display: block;
        font-size: 16px;
        margin-bottom: 4px;
    }
</style>

<div class="modal-kiosk" id="leadModal">
    <div class="modal-content-kiosk">
        <div class="modal-header-kiosk">
            <h3>
                <i class="fas fa-heart" style="color: var(--accent, #c2ac1f);"></i>
                Me Interesa
            </h3>
            <button class="modal-close" onclick="closeModal('leadModal')">&times;</button>
        </div>

        <form id="leadForm" onsubmit="submitLeadForm(event)">
            <input type="hidden" name="vehicle_id" id="leadVehicleId" value="{{ $vehicle->id ?? '' }}">

            <div class="form-group-kiosk">
                <label>Nombre completo *</label>
                <input type="text" name="name" class="form-control-kiosk" required placeholder="Tu nombre completo">
            </div>

            <div class="form-group-kiosk">
                <label>Telefono *</label>
                <input type="tel" name="phone" class="form-control-kiosk" required placeholder="8888-8888">
            </div>

            <div class="form-group-kiosk">
                <label>Email (opcional)</label>
                <input type="email" name="email" class="form-control-kiosk" placeholder="tu@email.com">
            </div>

            <label style="font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 10px; display: block;">
                Nivel de interés
            </label>
            <div class="interest-levels">
                <button type="button" class="interest-btn" data-level="low" onclick="selectInterest(this)">
                    <i class="fas fa-thermometer-empty"></i>
                    Bajo
                </button>
                <button type="button" class="interest-btn active" data-level="medium" onclick="selectInterest(this)">
                    <i class="fas fa-thermometer-half"></i>
                    Medio
                </button>
                <button type="button" class="interest-btn" data-level="high" onclick="selectInterest(this)">
                    <i class="fas fa-thermometer-three-quarters"></i>
                    Alto
                </button>
                <button type="button" class="interest-btn" data-level="hot" onclick="selectInterest(this)">
                    <i class="fas fa-fire"></i>
                    Compra
                </button>
            </div>
            <input type="hidden" name="interest_level" id="interestLevel" value="medium">

            <label style="font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 10px; display: block;">
                ¿Cómo nos conociste?
            </label>
            <div class="category-btns">
                <button type="button" class="category-btn active" data-cat="prospect" onclick="selectCategory(this)">
                    <i class="fas fa-user-plus"></i>
                    Prospecto
                </button>
                <button type="button" class="category-btn" data-cat="exploring" onclick="selectCategory(this)">
                    <i class="fas fa-search"></i>
                    Explorando
                </button>
                <button type="button" class="category-btn" data-cat="comparing" onclick="selectCategory(this)">
                    <i class="fas fa-balance-scale"></i>
                    Comparando
                </button>
                <button type="button" class="category-btn" data-cat="future_interest" onclick="selectCategory(this)">
                    <i class="fas fa-clock"></i>
                    A Futuro
                </button>
                <button type="button" class="category-btn" data-cat="referral" onclick="selectCategory(this)">
                    <i class="fas fa-user-friends"></i>
                    Referido
                </button>
                <button type="button" class="category-btn" data-cat="returning" onclick="selectCategory(this)">
                    <i class="fas fa-redo"></i>
                    Frecuente
                </button>
            </div>
            <input type="hidden" name="lead_category" id="leadCategory" value="prospect">

            <div class="form-group-kiosk">
                <label>Notas adicionales</label>
                <textarea name="notes" class="form-control-kiosk" rows="2" placeholder="Algo que debamos saber..."></textarea>
            </div>

            <button type="submit" class="btn-submit-kiosk">
                <i class="fas fa-paper-plane"></i>
                Enviar Mi Interés
            </button>
        </form>
    </div>
</div>

<script>
function selectInterest(btn) {
    document.querySelectorAll('.interest-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('interestLevel').value = btn.dataset.level;
}

function selectCategory(btn) {
    document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('leadCategory').value = btn.dataset.cat;
}

async function submitLeadForm(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    data.event_name = typeof eventName !== 'undefined' ? eventName : null;
    data.source = 'kiosk';
    data.vehicles_viewed = [parseInt(data.vehicle_id)];

    try {
        const response = await fetch('{{ route("kiosk.lead.capture") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.success) {
            showToast('Gracias por tu interés! Un asesor te contactará pronto.', 'success');
            form.reset();
            closeModal('leadModal');
        } else {
            showToast('Error al enviar. Intenta de nuevo.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al enviar. Intenta de nuevo.', 'error');
    }
}
</script>
