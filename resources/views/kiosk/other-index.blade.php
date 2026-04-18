<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kiosko - {{ $company->name ?? 'Bienvenido' }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        :root {
            --bg: {{ $settings->background_color ?? '#0b0f14' }};
            --accent: {{ $settings->accent_color ?? '#c2ac1f' }};
            --card: rgba(255,255,255,0.07);
            --border: rgba(255,255,255,0.12);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        html, body {
            width: 100%; height: 100%; overflow: hidden;
            background: var(--bg);
            color: #fff;
            font-family: 'Segoe UI', Arial, sans-serif;
            user-select: none;
        }

        /* ── WELCOME SCREEN ── */
        #welcomeScreen {
            position: fixed; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: var(--bg);
            z-index: 10;
            transition: opacity 0.4s;
        }

        .welcome-logo {
            width: 140px; height: 140px;
            border-radius: 24px;
            object-fit: contain;
            margin-bottom: 28px;
            background: var(--card);
            padding: 16px;
        }

        .welcome-company {
            font-size: 32px; font-weight: 700;
            color: var(--accent);
            margin-bottom: 12px;
            text-align: center;
            max-width: 80%;
        }

        .welcome-msg {
            font-size: 16px; color: rgba(255,255,255,0.6);
            text-align: center; max-width: 480px;
            line-height: 1.6; margin-bottom: 48px;
        }

        .welcome-cta {
            display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;
        }

        .btn-main {
            padding: 20px 48px;
            border-radius: 16px;
            font-size: 18px; font-weight: 700;
            border: none; cursor: pointer;
            display: flex; align-items: center; gap: 10px;
            transition: transform 0.15s, box-shadow 0.15s;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-main:active { transform: scale(0.96); }

        .btn-lead {
            background: var(--accent); color: #000;
        }

        .btn-quote {
            background: var(--card);
            border: 2px solid var(--accent);
            color: #fff;
        }

        .btn-main i { font-size: 22px; }

        .idle-pulse {
            position: absolute; bottom: 40px;
            font-size: 13px; color: rgba(255,255,255,0.3);
            animation: pulse 2.5s ease-in-out infinite;
        }

        @keyframes pulse { 0%,100%{opacity:.3} 50%{opacity:.7} }

        /* ── MODALS ── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.75);
            z-index: 100;
            display: none;
            align-items: center; justify-content: center;
            padding: 20px;
        }

        .modal-overlay.open { display: flex; }

        .modal-box {
            background: #1a1f28;
            border: 1px solid var(--border);
            border-radius: 20px;
            width: 100%; max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 32px;
        }

        .modal-title {
            font-size: 20px; font-weight: 700;
            color: var(--accent);
            margin-bottom: 24px;
            display: flex; align-items: center; gap: 10px;
        }

        .field { margin-bottom: 18px; }

        .field label {
            display: block; font-size: 13px;
            color: rgba(255,255,255,0.6);
            margin-bottom: 6px;
        }

        .field input, .field textarea, .field select {
            width: 100%; padding: 13px 16px;
            background: rgba(255,255,255,0.07);
            border: 1px solid var(--border);
            border-radius: 10px; color: #fff;
            font-size: 15px; outline: none;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .field input:focus, .field textarea:focus, .field select:focus {
            border-color: var(--accent);
        }

        .field textarea { resize: none; height: 90px; }

        .field select option { background: #1a1f28; }

        .interest-grid {
            display: grid; grid-template-columns: repeat(4,1fr);
            gap: 8px;
        }

        .interest-btn {
            padding: 10px 4px;
            border-radius: 8px; border: 1px solid var(--border);
            background: var(--card); color: #fff;
            font-size: 12px; font-weight: 600;
            cursor: pointer; text-align: center;
            transition: all 0.2s;
        }

        .interest-btn.active { background: var(--accent); color: #000; border-color: var(--accent); }

        .modal-actions {
            display: flex; gap: 12px; margin-top: 24px;
        }

        .btn-cancel {
            flex: 1; padding: 14px;
            background: var(--card); border: 1px solid var(--border);
            border-radius: 12px; color: #fff;
            font-size: 15px; font-weight: 600;
            cursor: pointer;
        }

        .btn-save {
            flex: 2; padding: 14px;
            background: var(--accent); border: none;
            border-radius: 12px; color: #000;
            font-size: 15px; font-weight: 700;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        /* ── SUCCESS OVERLAY ── */
        #successScreen {
            position: fixed; inset: 0;
            background: var(--bg);
            z-index: 200;
            display: none;
            flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center;
        }

        #successScreen.open { display: flex; }

        .success-icon {
            font-size: 72px; color: var(--accent);
            margin-bottom: 24px;
        }

        .success-title {
            font-size: 28px; font-weight: 700;
            color: var(--accent); margin-bottom: 12px;
        }

        .success-msg {
            font-size: 16px; color: rgba(255,255,255,0.6);
            max-width: 400px; line-height: 1.6;
        }

        /* ── TOAST ── */
        #toast {
            position: fixed; bottom: 40px; left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: #222; color: #fff;
            padding: 14px 28px; border-radius: 12px;
            font-size: 14px; opacity: 0;
            transition: all 0.3s; pointer-events: none;
            border-left: 4px solid var(--accent);
            z-index: 999;
        }

        #toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        #toast.error { border-color: #e74c3c; }
    </style>
</head>
<body>

<!-- ══ WELCOME SCREEN ══ -->
<div id="welcomeScreen">
    @if($settings->logo ?? null)
        <img src="{{ route('file', $settings->logo) }}" class="welcome-logo" alt="{{ $company->name }}">
    @elseif($company->logo ?? null)
        <img src="{{ route('file', $company->logo) }}" class="welcome-logo" alt="{{ $company->name }}">
    @else
        <div class="welcome-logo" style="display:flex;align-items:center;justify-content:center;font-size:48px;color:var(--accent);">
            <i class="fas fa-store"></i>
        </div>
    @endif

    <div class="welcome-company">{{ $company->name }}</div>
    <div class="welcome-msg">
        {{ $settings->welcome_message ?? 'Bienvenido. Regístranos tu interés o solicita una cotización.' }}
    </div>

    <div class="welcome-cta">
        <button class="btn-main btn-lead" onclick="openModal('leadModal')">
            <i class="fas fa-heart"></i> Me Interesa
        </button>
        @if($settings->enable_quote ?? true)
        <button class="btn-main btn-quote" onclick="openModal('quoteModal')">
            <i class="fas fa-calculator"></i> Cotizar
        </button>
        @endif
    </div>

    <div class="idle-pulse">Toca para comenzar</div>
</div>

<!-- ══ LEAD MODAL ══ -->
<div class="modal-overlay" id="leadModal">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-heart"></i> Registrar Interés</div>

        <div class="field">
            <label>Nombre completo *</label>
            <input type="text" id="leadName" placeholder="Tu nombre" autocomplete="off">
        </div>
        <div class="field">
            <label>Teléfono *</label>
            <input type="tel" id="leadPhone" placeholder="8888-8888" autocomplete="off">
        </div>
        <div class="field">
            <label>Correo electrónico</label>
            <input type="email" id="leadEmail" placeholder="correo@empresa.com" autocomplete="off">
        </div>
        <div class="field">
            <label>Empresa / Negocio</label>
            <input type="text" id="leadCompany" placeholder="Nombre de su empresa" autocomplete="off">
        </div>
        <div class="field">
            <label>¿Qué producto o servicio busca?</label>
            <input type="text" id="leadProduct" placeholder="Ej. Repuesto para excavadora CAT 320" autocomplete="off">
        </div>
        <div class="field">
            <label>Notas adicionales</label>
            <textarea id="leadNotes" placeholder="Información adicional que desee agregar..."></textarea>
        </div>
        <div class="field">
            <label>Nivel de interés</label>
            <div class="interest-grid">
                <button class="interest-btn" data-level="low" onclick="selectInterest(this)">Bajo</button>
                <button class="interest-btn active" data-level="medium" onclick="selectInterest(this)">Medio</button>
                <button class="interest-btn" data-level="high" onclick="selectInterest(this)">Alto</button>
                <button class="interest-btn" data-level="hot" onclick="selectInterest(this)">🔥 Hot</button>
            </div>
            <input type="hidden" id="leadInterestLevel" value="medium">
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('leadModal')">Cancelar</button>
            <button class="btn-save" onclick="submitLead()">
                <i class="fas fa-check"></i> Guardar
            </button>
        </div>
    </div>
</div>

<!-- ══ QUOTE MODAL ══ -->
@if($settings->enable_quote ?? true)
<div class="modal-overlay" id="quoteModal">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-calculator"></i> Cotización</div>

        <div class="field">
            <label>Nombre completo *</label>
            <input type="text" id="quoteName" placeholder="Tu nombre" autocomplete="off">
        </div>
        <div class="field">
            <label>Teléfono *</label>
            <input type="tel" id="quotePhone" placeholder="8888-8888" autocomplete="off">
        </div>
        <div class="field">
            <label>Correo electrónico</label>
            <input type="email" id="quoteEmail" placeholder="correo@empresa.com" autocomplete="off">
        </div>
        <div class="field">
            <label>Empresa / Negocio</label>
            <input type="text" id="quoteCompanyName" placeholder="Nombre de su empresa" autocomplete="off">
        </div>
        <div class="field">
            <label>Descripción de lo cotizado *</label>
            <textarea id="quoteDescription" placeholder="Describa el producto, repuesto o servicio a cotizar..."></textarea>
        </div>
        <div class="field">
            <label>Monto estimado (opcional)</label>
            <input type="number" id="quoteAmount" placeholder="0" min="0" step="1000" autocomplete="off">
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('quoteModal')">Cancelar</button>
            <button class="btn-save" onclick="submitQuote()">
                <i class="fas fa-file-alt"></i> Guardar y PDF
            </button>
        </div>
    </div>
</div>
@endif

<!-- ══ SUCCESS SCREEN ══ -->
<div id="successScreen">
    <div class="success-icon"><i class="fas fa-check-circle"></i></div>
    <div class="success-title" id="successTitle">¡Registro guardado!</div>
    <div class="success-msg" id="successMsg">Sus datos han sido registrados. Un asesor se pondrá en contacto pronto.</div>
</div>

<div id="toast"></div>

<script>
const eventName = @json($eventName);

function openModal(id) {
    document.getElementById(id).classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function selectInterest(btn) {
    document.querySelectorAll('.interest-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('leadInterestLevel').value = btn.dataset.level;
}

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'show' + (type === 'error' ? ' error' : '');
    setTimeout(() => t.className = '', 3000);
}

function showSuccess(title, msg) {
    const s = document.getElementById('successScreen');
    document.getElementById('successTitle').textContent = title;
    document.getElementById('successMsg').textContent = msg;
    s.classList.add('open');
    setTimeout(() => {
        s.classList.remove('open');
    }, 4000);
}

async function submitLead() {
    const name  = document.getElementById('leadName').value.trim();
    const phone = document.getElementById('leadPhone').value.trim();
    if (!name)  { showToast('Ingresa tu nombre', 'error'); return; }
    if (!phone) { showToast('Ingresa tu teléfono', 'error'); return; }

    const product = document.getElementById('leadProduct').value.trim();
    const company = document.getElementById('leadCompany').value.trim();
    const notes   = document.getElementById('leadNotes').value.trim();
    const level   = document.getElementById('leadInterestLevel').value;
    const desc    = [company ? 'Empresa: ' + company : '', product].filter(Boolean).join(' | ');

    try {
        const res = await fetch('{{ route("kiosk.lead.capture") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({
                name, phone,
                email: document.getElementById('leadEmail').value.trim() || null,
                source: 'kiosk',
                event_name: eventName,
                interest_level: level,
                lead_category: 'prospect',
                notes: notes || null,
                description: desc || null,
            })
        });
        const data = await res.json();
        if (data.success) {
            closeModal('leadModal');
            clearLeadForm();
            showSuccess('¡Registro guardado!', 'Sus datos han sido registrados. Un asesor se pondrá en contacto pronto.');
        } else {
            showToast('Error al guardar. Intente de nuevo.', 'error');
        }
    } catch (e) {
        showToast('Error de conexión.', 'error');
    }
}

async function submitQuote() {
    const name  = document.getElementById('quoteName').value.trim();
    const phone = document.getElementById('quotePhone').value.trim();
    const desc  = document.getElementById('quoteDescription').value.trim();
    if (!name)  { showToast('Ingresa tu nombre', 'error'); return; }
    if (!phone) { showToast('Ingresa tu teléfono', 'error'); return; }
    if (!desc)  { showToast('Ingresa la descripción de lo cotizado', 'error'); return; }

    const amount  = parseFloat(document.getElementById('quoteAmount').value) || 0;
    const company = document.getElementById('quoteCompanyName').value.trim();
    const fullDesc = company ? 'Empresa: ' + company + '\n' + desc : desc;

    try {
        const res = await fetch('{{ route("kiosk.quote.save") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({
                customer_name: name,
                customer_phone: phone,
                customer_email: document.getElementById('quoteEmail').value.trim() || null,
                vehicle_price: amount || null,
                down_payment: 0,
                monthly_payment: amount || null,
                total_amount: amount || null,
                total_interest: 0,
                description: fullDesc,
                event_name: eventName,
            })
        });
        const data = await res.json();
        if (data.success) {
            closeModal('quoteModal');
            clearQuoteForm();
            window.open('/kiosk/quote/' + data.quote_id + '/pdf', '_blank');
            showSuccess('¡Cotización guardada!', 'Se descargará el PDF con los datos de su cotización.');
        } else {
            showToast('Error al guardar. Intente de nuevo.', 'error');
        }
    } catch (e) {
        showToast('Error de conexión.', 'error');
    }
}

function clearLeadForm() {
    ['leadName','leadPhone','leadEmail','leadCompany','leadProduct','leadNotes'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.querySelectorAll('.interest-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('[data-level="medium"]').classList.add('active');
    document.getElementById('leadInterestLevel').value = 'medium';
}

function clearQuoteForm() {
    ['quoteName','quotePhone','quoteEmail','quoteCompanyName','quoteDescription','quoteAmount'].forEach(id => {
        document.getElementById(id).value = '';
    });
}
</script>
</body>
</html>
