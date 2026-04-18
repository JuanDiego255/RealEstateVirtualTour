<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kiosko - {{ $company->name ?? 'Bienvenido' }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

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
            background: var(--bg); color: #fff;
            font-family: 'Segoe UI', Arial, sans-serif;
            user-select: none;
        }

        /* ── WELCOME SCREEN ── */
        #welcomeScreen {
            position: fixed; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: var(--bg); z-index: 10;
        }

        .welcome-logo {
            width: 140px; height: 140px; border-radius: 24px;
            object-fit: contain; margin-bottom: 28px;
            background: var(--card); padding: 16px;
        }

        .welcome-company {
            font-size: 32px; font-weight: 700; color: var(--accent);
            margin-bottom: 12px; text-align: center; max-width: 80%;
        }

        .welcome-msg {
            font-size: 16px; color: rgba(255,255,255,0.6);
            text-align: center; max-width: 480px;
            line-height: 1.6; margin-bottom: 48px;
        }

        .welcome-cta { display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; }

        .btn-main {
            padding: 20px 48px; border-radius: 16px;
            font-size: 18px; font-weight: 700; border: none; cursor: pointer;
            display: flex; align-items: center; gap: 10px;
            transition: transform 0.15s;
        }
        .btn-main:active { transform: scale(0.96); }
        .btn-lead { background: var(--accent); color: #000; }
        .btn-quote { background: var(--card); border: 2px solid var(--accent); color: #fff; }
        .btn-main i { font-size: 22px; }

        .idle-hint {
            position: absolute; bottom: 40px;
            font-size: 13px; color: rgba(255,255,255,0.3);
            animation: softpulse 2.5s ease-in-out infinite;
        }
        @keyframes softpulse { 0%,100%{opacity:.3} 50%{opacity:.7} }

        /* ── IDLE OVERLAY (Feature 3) ── */
        #idleOverlay {
            position: fixed; inset: 0; z-index: 50;
            background: linear-gradient(135deg, #0b0f14 0%, #111827 50%, #0b0f14 100%);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.6s;
        }
        #idleOverlay.active { opacity: 1; pointer-events: all; }

        .idle-logo-wrap {
            position: relative; margin-bottom: 40px;
        }
        .idle-logo-wrap::before {
            content: ''; position: absolute; inset: -20px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
            animation: idleHalo 2s ease-in-out infinite alternate;
        }
        @keyframes idleHalo { from{opacity:.15;transform:scale(.9)} to{opacity:.4;transform:scale(1.1)} }

        .idle-logo {
            position: relative; width: 120px; height: 120px;
            border-radius: 24px; object-fit: contain;
            background: rgba(255,255,255,0.05);
            padding: 14px;
            animation: idlePop 2s ease-in-out infinite alternate;
        }
        @keyframes idlePop { from{transform:scale(.95)} to{transform:scale(1.05)} }

        .idle-logo-icon {
            position: relative; width: 120px; height: 120px;
            border-radius: 24px; background: rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: center;
            font-size: 56px; color: var(--accent);
            animation: idlePop 2s ease-in-out infinite alternate;
        }

        .idle-cta-text {
            font-size: 28px; font-weight: 800; letter-spacing: 2px;
            color: var(--accent);
            animation: idleGlow 1.5s ease-in-out infinite alternate;
        }
        @keyframes idleGlow {
            from { text-shadow: 0 0 10px var(--accent); opacity:.7; }
            to   { text-shadow: 0 0 30px var(--accent), 0 0 60px var(--accent); opacity:1; }
        }

        .idle-sub {
            margin-top: 12px; font-size: 15px;
            color: rgba(255,255,255,0.4);
        }

        /* Partículas flotantes */
        .particle {
            position: absolute; width: 4px; height: 4px;
            border-radius: 50%; background: var(--accent);
            opacity: 0; animation: floatUp linear infinite;
        }
        @keyframes floatUp {
            0%   { transform: translateY(0) scale(0); opacity:0; }
            20%  { opacity:.6; }
            80%  { opacity:.3; }
            100% { transform: translateY(-100vh) scale(1.5); opacity:0; }
        }

        /* ── MODALS ── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.75); z-index: 100;
            display: none; align-items: center; justify-content: center; padding: 20px;
        }
        .modal-overlay.open { display: flex; }

        .modal-box {
            background: #1a1f28; border: 1px solid var(--border);
            border-radius: 20px; width: 100%; max-width: 520px;
            max-height: 90vh; overflow-y: auto; padding: 32px;
        }

        .modal-title {
            font-size: 20px; font-weight: 700; color: var(--accent);
            margin-bottom: 24px; display: flex; align-items: center; gap: 10px;
        }

        .field { margin-bottom: 18px; }
        .field label { display: block; font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 6px; }

        .field input, .field textarea, .field select {
            width: 100%; padding: 13px 16px;
            background: rgba(255,255,255,0.07);
            border: 1px solid var(--border);
            border-radius: 10px; color: #fff; font-size: 15px;
            outline: none; font-family: inherit; transition: border-color 0.2s;
        }
        .field input:focus, .field textarea:focus, .field select:focus { border-color: var(--accent); }
        .field textarea { resize: none; height: 80px; }
        .field select option { background: #1a1f28; }

        /* Selector grid (interest + urgency + categories) */
        .sel-grid { display: flex; gap: 8px; flex-wrap: wrap; }

        .sel-btn {
            padding: 10px 14px; border-radius: 8px;
            border: 1px solid var(--border); background: var(--card);
            color: #fff; font-size: 13px; font-weight: 600;
            cursor: pointer; transition: all 0.2s;
        }
        .sel-btn.active { background: var(--accent); color: #000; border-color: var(--accent); }
        .sel-btn.sm { padding: 8px 10px; font-size: 11px; }

        .modal-actions { display: flex; gap: 12px; margin-top: 24px; }

        .btn-cancel {
            flex: 1; padding: 14px; background: var(--card);
            border: 1px solid var(--border); border-radius: 12px;
            color: #fff; font-size: 15px; font-weight: 600; cursor: pointer;
        }

        .btn-save {
            flex: 2; padding: 14px; background: var(--accent); border: none;
            border-radius: 12px; color: #000; font-size: 15px; font-weight: 700;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        /* ── SUCCESS SCREEN (Feature 1 + 5) ── */
        #successScreen {
            position: fixed; inset: 0; background: var(--bg); z-index: 200;
            display: none; flex-direction: column;
            align-items: center; justify-content: center; text-align: center;
            padding: 40px;
        }
        #successScreen.open { display: flex; }

        .success-icon { font-size: 64px; color: var(--accent); margin-bottom: 16px; }
        .success-title { font-size: 26px; font-weight: 700; color: var(--accent); margin-bottom: 8px; }
        .success-msg { font-size: 15px; color: rgba(255,255,255,0.6); max-width: 380px; line-height: 1.6; margin-bottom: 28px; }

        .success-actions { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; margin-bottom: 28px; }

        .btn-wa {
            padding: 14px 24px; border-radius: 12px;
            background: #25D366; color: #fff; border: none;
            font-size: 15px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; gap: 8px;
        }

        #qrContainer {
            display: none; flex-direction: column; align-items: center; margin-top: 8px;
        }
        #qrContainer.show { display: flex; }
        .qr-label { font-size: 12px; color: rgba(255,255,255,0.5); margin-bottom: 10px; }
        #qrCanvas { background: #fff; padding: 12px; border-radius: 12px; }

        .success-countdown { font-size: 12px; color: rgba(255,255,255,0.3); margin-top: 16px; }

        /* ── TOAST ── */
        #toast {
            position: fixed; bottom: 40px; left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: #222; color: #fff; padding: 14px 28px;
            border-radius: 12px; font-size: 14px; opacity: 0;
            transition: all 0.3s; pointer-events: none;
            border-left: 4px solid var(--accent); z-index: 999;
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
    </div>

    <div class="idle-hint">Toca para comenzar</div>
</div>

<!-- ══ IDLE OVERLAY (Feature 3) ══ -->
<div id="idleOverlay">
    <!-- Partículas flotantes -->
    @for($i = 0; $i < 8; $i++)
    <div class="particle" style="left:{{ rand(5,95) }}%;animation-duration:{{ rand(6,14) }}s;animation-delay:{{ rand(0,10) }}s;opacity:0;"></div>
    @endfor

    <div class="idle-logo-wrap">
        @if($settings->logo ?? null)
            <img src="{{ route('file', $settings->logo) }}" class="idle-logo" alt="{{ $company->name }}">
        @elseif($company->logo ?? null)
            <img src="{{ route('file', $company->logo) }}" class="idle-logo" alt="{{ $company->name }}">
        @else
            <div class="idle-logo-icon"><i class="fas fa-store"></i></div>
        @endif
    </div>

    <div class="idle-cta-text">TOCA PARA COMENZAR</div>
    <div class="idle-sub">{{ $company->name }}</div>
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

        @if(count($categories) > 0)
        <div class="field">
            <label>¿Qué tipo de producto busca?</label>
            <div class="sel-grid" id="catGrid">
                @foreach($categories as $cat)
                <button class="sel-btn sm" data-cat="{{ $cat }}" onclick="selectCat(this)">{{ $cat }}</button>
                @endforeach
            </div>
            <input type="hidden" id="leadCategory" value="">
        </div>
        @endif

        <div class="field">
            <label>¿Qué producto o servicio busca?</label>
            <input type="text" id="leadProduct" placeholder="Ej. Repuesto para excavadora CAT 320" autocomplete="off">
        </div>
        <div class="field">
            <label>Notas adicionales</label>
            <textarea id="leadNotes" placeholder="Información adicional..."></textarea>
        </div>

        {{-- Feature 2: Urgencia --}}
        <div class="field">
            <label>¿Cuándo lo necesita?</label>
            <div class="sel-grid">
                <button class="sel-btn" data-urgency="urgente" onclick="selectUrgency(this,'lead')">⚡ Urgente (&lt;1 sem)</button>
                <button class="sel-btn active" data-urgency="normal" onclick="selectUrgency(this,'lead')">Normal (1 mes)</button>
                <button class="sel-btn" data-urgency="planificacion" onclick="selectUrgency(this,'lead')">📋 Planificación (3+ m)</button>
            </div>
            <input type="hidden" id="leadUrgency" value="normal">
        </div>

        <div class="field">
            <label>Nivel de interés</label>
            <div class="sel-grid">
                <button class="sel-btn" data-level="low" onclick="selectInterest(this)">Bajo</button>
                <button class="sel-btn active" data-level="medium" onclick="selectInterest(this)">Medio</button>
                <button class="sel-btn" data-level="high" onclick="selectInterest(this)">Alto</button>
                <button class="sel-btn" data-level="hot" onclick="selectInterest(this)">🔥 Hot</button>
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

{{-- Formulario de cotización deshabilitado: el cliente solo usa "Me Interesa" --}}

<!-- ══ SUCCESS SCREEN (Feature 1 + 5) ══ -->
<div id="successScreen">
    <div class="success-icon"><i class="fas fa-check-circle"></i></div>
    <div class="success-title" id="successTitle">¡Registro guardado!</div>
    <div class="success-msg" id="successMsg">Sus datos han sido registrados. Un asesor se pondrá en contacto pronto.</div>

    <div class="success-actions">
        {{-- Feature 5: WhatsApp post-cotización --}}
        <button class="btn-wa" id="btnWaAdvisor" style="display:none" onclick="openWaAdvisor()">
            <i class="fab fa-whatsapp"></i> Enviar al asesor
        </button>
        {{-- Feature 1: Abrir WhatsApp directamente (alternativa al QR) --}}
        <button class="btn-wa" id="btnWaDirect" style="display:none;background:#128C7E;" onclick="openWaDirect()">
            <i class="fab fa-whatsapp"></i> Abrir WhatsApp
        </button>
    </div>

    {{-- Feature 1: QR de WhatsApp --}}
    <div id="qrContainer">
        <div class="qr-label">Escanea para contactar al asesor vía WhatsApp</div>
        <div id="qrCanvas"></div>
    </div>

    <div class="success-countdown" id="successCountdown"></div>
</div>

<div id="toast"></div>

<script>
const eventName      = @json($eventName);
const waNumber       = @json($whatsappNumber ?? '');
const idleTimeoutSec = {{ $settings->idle_timeout_seconds ?? 60 }};

// ── Estado ──
let currentWaUrl   = '';
let currentWaAdvisoryMsg = '';
let dismissTimer   = null;
let idleTimer      = null;
let qrInstance     = null;

// ── Idle overlay (Feature 3) ──
function resetIdle() {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(showIdle, idleTimeoutSec * 1000);
}

function showIdle() {
    document.getElementById('idleOverlay').classList.add('active');
}

function hideIdle() {
    document.getElementById('idleOverlay').classList.remove('active');
    resetIdle();
}

document.getElementById('idleOverlay').addEventListener('click', hideIdle);
document.getElementById('idleOverlay').addEventListener('touchstart', hideIdle);
['click','touchstart','mousemove','keydown'].forEach(ev =>
    document.addEventListener(ev, resetIdle, { passive: true })
);
resetIdle();

// ── Modales ──
function openModal(id) {
    document.getElementById(id).classList.add('open');
    resetIdle();
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    resetIdle();
}

// ── Selectores ──
function selectInterest(btn) {
    document.querySelectorAll('#leadModal .sel-btn[data-level]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('leadInterestLevel').value = btn.dataset.level;
}

function selectUrgency(btn, form) {
    const container = btn.closest('.sel-grid');
    container.querySelectorAll('.sel-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(form + 'Urgency').value = btn.dataset.urgency;
}

function selectCat(btn) {
    document.querySelectorAll('#catGrid .sel-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('leadCategory').value = btn.dataset.cat;
}

function selectQuoteCat(btn) {
    document.querySelectorAll('#quoteCatGrid .sel-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const el = document.getElementById('quoteCategory');
    if (el) el.value = btn.dataset.cat;
}

// ── Toast ──
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'show' + (type === 'error' ? ' error' : '');
    setTimeout(() => t.className = '', 3000);
}

// ── Success screen ──
function showSuccess(title, msg, opts = {}) {
    clearTimeout(dismissTimer);

    document.getElementById('successTitle').textContent = title;
    document.getElementById('successMsg').textContent   = msg;

    // QR WhatsApp (Feature 1)
    const qrCont = document.getElementById('qrContainer');
    const btnDirect = document.getElementById('btnWaDirect');
    if (opts.waUrl && waNumber) {
        currentWaUrl = opts.waUrl;
        qrCont.classList.add('show');
        btnDirect.style.display = '';

        // Regenerar QR
        document.getElementById('qrCanvas').innerHTML = '';
        qrInstance = new QRCode(document.getElementById('qrCanvas'), {
            text: opts.waUrl, width: 180, height: 180,
            colorDark: '#000000', colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    } else {
        qrCont.classList.remove('show');
        btnDirect.style.display = 'none';
    }

    // Botón WhatsApp al asesor (Feature 5 - cotización)
    const btnAdvisor = document.getElementById('btnWaAdvisor');
    if (opts.waAdvisorUrl) {
        currentWaAdvisoryMsg = opts.waAdvisorUrl;
        btnAdvisor.style.display = '';
    } else {
        btnAdvisor.style.display = 'none';
    }

    const s = document.getElementById('successScreen');
    s.classList.add('open');

    // Countdown y auto-dismiss
    let secs = 10;
    const cd = document.getElementById('successCountdown');
    cd.textContent = 'Cerrando en ' + secs + 's...';
    const interval = setInterval(() => {
        secs--;
        if (secs <= 0) { clearInterval(interval); cd.textContent = ''; }
        else cd.textContent = 'Cerrando en ' + secs + 's...';
    }, 1000);

    dismissTimer = setTimeout(() => {
        clearInterval(interval);
        s.classList.remove('open');
        resetIdle();
    }, 10000);

    s.onclick = () => {
        clearTimeout(dismissTimer);
        clearInterval(interval);
        s.classList.remove('open');
        resetIdle();
    };
}

function openWaDirect()    { if (currentWaUrl) window.open(currentWaUrl, '_blank'); }
function openWaAdvisor()   { if (currentWaAdvisoryMsg) window.open(currentWaAdvisoryMsg, '_blank'); }

// ── Formatear número WA ──
function cleanWaNumber(raw) {
    // Quitar todo excepto dígitos; si son 8 dígitos (CR) agregar 506
    let num = (raw || '').replace(/\D/g, '');
    if (num.length === 8) num = '506' + num;
    return num;
}

// ── SUBMIT LEAD ──
async function submitLead() {
    const name  = document.getElementById('leadName').value.trim();
    const phone = document.getElementById('leadPhone').value.trim();
    if (!name)  { showToast('Ingresa tu nombre', 'error'); return; }
    if (!phone) { showToast('Ingresa tu teléfono', 'error'); return; }

    const product  = document.getElementById('leadProduct').value.trim();
    const company  = document.getElementById('leadCompany').value.trim();
    const notes    = document.getElementById('leadNotes').value.trim();
    const level    = document.getElementById('leadInterestLevel').value;
    const urgency  = document.getElementById('leadUrgency').value;
    const cat      = document.getElementById('leadCategory')?.value || '';

    // Construir descripción
    const parts = [];
    if (company)  parts.push('Empresa: ' + company);
    if (cat)      parts.push('Cat: ' + cat);
    if (product)  parts.push(product);
    const desc = parts.join(' | ');

    const notesWithUrgency = '[Urgencia: ' + urgency + ']' + (notes ? ' ' + notes : '');

    try {
        const res = await fetch('{{ route("kiosk.lead.capture") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({
                name, phone,
                email:          document.getElementById('leadEmail').value.trim() || null,
                source:         'kiosk',
                event_name:     eventName,
                interest_level: level,
                lead_category:  'prospect',
                notes:          notesWithUrgency,
                description:    desc || null,
            })
        });
        const data = await res.json();
        if (data.success) {
            closeModal('leadModal');
            clearLeadForm();

            // Feature 1: construir URL de WhatsApp para el asesor
            let waUrl = null;
            const waNum = cleanWaNumber(waNumber);
            if (waNum) {
                const msg = encodeURIComponent(
                    '¡Hola! Me interesa información.\n' +
                    'Nombre: ' + name + '\n' +
                    'Teléfono: ' + phone + '\n' +
                    (cat ? 'Categoría: ' + cat + '\n' : '') +
                    (product ? 'Busco: ' + product + '\n' : '') +
                    'Urgencia: ' + urgency
                );
                waUrl = 'https://wa.me/' + waNum + '?text=' + msg;
            }

            showSuccess(
                '¡Registro guardado!',
                'Un asesor se pondrá en contacto pronto.' + (waUrl ? ' Escanea el QR para escribirnos directamente.' : ''),
                { waUrl }
            );
        } else {
            showToast('Error al guardar. Intente de nuevo.', 'error');
        }
    } catch (e) {
        showToast('Error de conexión.', 'error');
    }
}

// ── SUBMIT QUOTE ──
async function submitQuote() {
    const name  = document.getElementById('quoteName').value.trim();
    const phone = document.getElementById('quotePhone').value.trim();
    const desc  = document.getElementById('quoteDescription').value.trim();
    if (!name)  { showToast('Ingresa tu nombre', 'error'); return; }
    if (!phone) { showToast('Ingresa tu teléfono', 'error'); return; }
    if (!desc)  { showToast('Ingresa la descripción de lo cotizado', 'error'); return; }

    const amount   = parseFloat(document.getElementById('quoteAmount').value) || 0;
    const company  = document.getElementById('quoteCompanyName').value.trim();
    const urgency  = document.getElementById('quoteUrgency').value;
    const cat      = document.getElementById('quoteCategory')?.value || '';
    const fullDesc = (company ? 'Empresa: ' + company + '\n' : '') +
                     (cat ? 'Cat: ' + cat + '\n' : '') +
                     '[Urgencia: ' + urgency + ']\n' + desc;

    try {
        const res = await fetch('{{ route("kiosk.quote.save") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({
                customer_name:  name,
                customer_phone: phone,
                customer_email: document.getElementById('quoteEmail').value.trim() || null,
                vehicle_price:  amount || null,
                down_payment:   0,
                monthly_payment: amount || null,
                total_amount:   amount || null,
                total_interest: 0,
                description:    fullDesc,
                event_name:     eventName,
            })
        });
        const data = await res.json();
        if (data.success) {
            closeModal('quoteModal');
            clearQuoteForm();

            // Descarga PDF automáticamente
            window.open('/kiosk/quote/' + data.quote_id + '/pdf', '_blank');

            // Feature 5: URL de WhatsApp al asesor con resumen de la cotización
            let waAdvisorUrl = null;
            const waNum = cleanWaNumber(waNumber);
            if (waNum) {
                const msg = encodeURIComponent(
                    '¡Hola! Acabo de enviar una cotización en el evento.\n' +
                    'Nombre: ' + name + '\n' +
                    'Teléfono: ' + phone + '\n' +
                    'Descripción: ' + desc + '\n' +
                    (amount ? 'Monto estimado: ' + amount.toLocaleString() + '\n' : '') +
                    'Urgencia: ' + urgency
                );
                waAdvisorUrl = 'https://wa.me/' + waNum + '?text=' + msg;
            }

            showSuccess(
                '¡Cotización guardada!',
                'Se está descargando el PDF.' + (waAdvisorUrl ? ' También puede enviar el resumen por WhatsApp.' : ''),
                { waAdvisorUrl }
            );
        } else {
            showToast('Error al guardar. Intente de nuevo.', 'error');
        }
    } catch (e) {
        showToast('Error de conexión.', 'error');
    }
}

// ── Limpiar formularios ──
function clearLeadForm() {
    ['leadName','leadPhone','leadEmail','leadCompany','leadProduct','leadNotes'].forEach(id => {
        const el = document.getElementById(id); if (el) el.value = '';
    });
    document.querySelectorAll('#leadModal .sel-btn').forEach(b => b.classList.remove('active'));
    const mediumBtn = document.querySelector('#leadModal [data-level="medium"]');
    if (mediumBtn) mediumBtn.classList.add('active');
    const normalBtn = document.querySelector('#leadModal [data-urgency="normal"]');
    if (normalBtn) normalBtn.classList.add('active');
    const catInput = document.getElementById('leadCategory');
    if (catInput) catInput.value = '';
    document.getElementById('leadInterestLevel').value = 'medium';
    document.getElementById('leadUrgency').value = 'normal';
}

function clearQuoteForm() {
    ['quoteName','quotePhone','quoteEmail','quoteCompanyName','quoteDescription','quoteAmount'].forEach(id => {
        const el = document.getElementById(id); if (el) el.value = '';
    });
    document.querySelectorAll('#quoteModal .sel-btn').forEach(b => b.classList.remove('active'));
    const normalBtn = document.querySelector('#quoteModal [data-urgency="normal"]');
    if (normalBtn) normalBtn.classList.add('active');
    document.getElementById('quoteUrgency').value = 'normal';
    const catInput = document.getElementById('quoteCategory');
    if (catInput) catInput.value = '';
}
</script>
</body>
</html>
