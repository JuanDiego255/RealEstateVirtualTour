<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tour Virtual - Modo Kiosko</title>

    <style>
        :root {
            --kiosk-bg: {{ $settings->background_color ?? '#0b0f14' }};
            --kiosk-accent: {{ $settings->accent_color ?? '#c2ac1f' }};
            --kiosk-text: #ffffff;
            --kiosk-card: rgba(255, 255, 255, 0.08);
            --kiosk-border: rgba(255, 255, 255, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--kiosk-bg);
            color: var(--kiosk-text);
            touch-action: pan-x;
        }

        /* Header fijo con logo */
        .kiosk-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: linear-gradient(180deg, rgba(0,0,0,0.8) 0%, transparent 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            z-index: 100;
        }

        .kiosk-logo {
            height: 45px;
            object-fit: contain;
        }

        .kiosk-logo-text {
            font-size: 24px;
            font-weight: 700;
            color: var(--kiosk-accent);
        }

        .kiosk-event-name {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Contenedor del slider */
        .kiosk-slider {
            width: 100%;
            height: 100%;
            display: flex;
            transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .kiosk-slide {
            min-width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Viewer del spin */
        .spin-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 80px 40px 200px;
        }

        .spin-viewer {
            width: 100%;
            max-width: 900px;
            aspect-ratio: 16/9;
            background: rgba(0,0,0,0.3);
            border-radius: 20px;
            overflow: hidden;
            cursor: grab;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }

        .spin-viewer:active {
            cursor: grabbing;
        }

        .spin-viewer canvas {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Info del vehículo */
        .vehicle-info-panel {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(0deg, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.8) 70%, transparent 100%);
            padding: 30px 40px 40px;
            z-index: 50;
        }

        .vehicle-main-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 20px;
        }

        .vehicle-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .vehicle-subtitle {
            font-size: 16px;
            color: rgba(255,255,255,0.6);
        }

        .vehicle-price {
            font-size: 36px;
            font-weight: 700;
            color: var(--kiosk-accent);
        }

        .vehicle-specs {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: rgba(255,255,255,0.8);
        }

        .spec-item i {
            color: var(--kiosk-accent);
            width: 20px;
            text-align: center;
        }

        /* Botones de acción */
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-kiosk {
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s ease;
        }

        .btn-kiosk-primary {
            background: var(--kiosk-accent);
            color: #000;
        }

        .btn-kiosk-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(194, 172, 31, 0.4);
        }

        .btn-kiosk-secondary {
            background: var(--kiosk-card);
            color: var(--kiosk-text);
            border: 1px solid var(--kiosk-border);
        }

        .btn-kiosk-secondary:hover {
            background: rgba(255,255,255,0.15);
        }

        /* Indicador de slides */
        .slide-indicators {
            position: fixed;
            bottom: 200px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 60;
        }

        .slide-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .slide-indicator.active {
            background: var(--kiosk-accent);
            transform: scale(1.2);
        }

        /* Flechas de navegación */
        .nav-arrow {
            position: fixed;
            top: 50%;
            transform: translateY(-50%);
            width: 60px;
            height: 60px;
            background: var(--kiosk-card);
            border: 1px solid var(--kiosk-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 60;
            transition: all 0.2s ease;
            font-size: 24px;
            color: var(--kiosk-text);
        }

        .nav-arrow:hover {
            background: var(--kiosk-accent);
            color: #000;
        }

        .nav-arrow.prev { left: 30px; }
        .nav-arrow.next { right: 30px; }

        /* Instrucciones de swipe */
        .swipe-hint {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.9);
            padding: 30px 50px;
            border-radius: 20px;
            text-align: center;
            z-index: 200;
            animation: fadeOut 3s forwards;
            animation-delay: 2s;
        }

        .swipe-hint i {
            font-size: 48px;
            color: var(--kiosk-accent);
            margin-bottom: 15px;
            display: block;
            animation: swipeAnim 1.5s infinite;
        }

        @keyframes swipeAnim {
            0%, 100% { transform: translateX(-10px); }
            50% { transform: translateX(10px); }
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; pointer-events: none; }
        }

        /* QR flotante */
        .qr-floating {
            position: fixed;
            top: 100px;
            right: 30px;
            background: #fff;
            padding: 15px;
            border-radius: 15px;
            text-align: center;
            z-index: 70;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .qr-floating img {
            width: 120px;
            height: 120px;
        }

        .qr-floating p {
            color: #333;
            font-size: 11px;
            margin-top: 8px;
            font-weight: 600;
        }

        /* Modal */
        .modal-kiosk {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 500;
            padding: 20px;
        }

        .modal-kiosk.active {
            display: flex;
        }

        .modal-content-kiosk {
            background: #1a1a2e;
            border-radius: 24px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
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
        }

        .modal-close {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--kiosk-card);
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
        }

        /* Form inputs */
        .form-group-kiosk {
            margin-bottom: 20px;
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
            background: var(--kiosk-card);
            border: 1px solid var(--kiosk-border);
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
        }

        .form-control-kiosk:focus {
            outline: none;
            border-color: var(--kiosk-accent);
        }

        /* Auto-rotate indicator */
        .auto-rotate-indicator {
            position: fixed;
            top: 80px;
            left: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            background: var(--kiosk-card);
            border-radius: 30px;
            font-size: 13px;
            z-index: 70;
        }

        .auto-rotate-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4ade80;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .auto-rotate-indicator.paused .auto-rotate-dot {
            background: #f59e0b;
            animation: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .vehicle-title { font-size: 24px; }
            .vehicle-price { font-size: 28px; }
            .nav-arrow { display: none; }
            .qr-floating { display: none; }
            .spin-container { padding: 70px 20px 180px; }
            .vehicle-info-panel { padding: 20px; }
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="kiosk-header">
        <div>
            @if($settings->logo)
                <img src="{{ route('file', $settings->logo) }}" alt="Logo" class="kiosk-logo">
            @else
                <span class="kiosk-logo-text">Tour Virtual 360</span>
            @endif
        </div>
        @if($eventName)
            <span class="kiosk-event-name">{{ $eventName }}</span>
        @endif
    </header>

    <!-- Indicador de auto-rotate -->
    <div class="auto-rotate-indicator" id="autoRotateIndicator">
        <span class="auto-rotate-dot"></span>
        <span id="autoRotateText">Auto-rotando</span>
    </div>

    <!-- Flechas de navegación -->
    <button class="nav-arrow prev" onclick="prevSlide()">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="nav-arrow next" onclick="nextSlide()">
        <i class="fas fa-chevron-right"></i>
    </button>

    <!-- Slider de vehículos -->
    <div class="kiosk-slider" id="kioskSlider">
        @foreach($vehicles as $index => $vehicle)
        <div class="kiosk-slide" data-vehicle-id="{{ $vehicle->id }}" data-index="{{ $index }}">
            <div class="spin-container">
                <div class="spin-viewer" id="spinViewer{{ $index }}">
                    <canvas id="spinCanvas{{ $index }}"></canvas>
                </div>
            </div>

            <!-- QR flotante -->
            @if($settings->enable_qr ?? true)
            <div class="qr-floating">
                <img src="{{ route('kiosk.qr', ['vehicleId' => $vehicle->id, 'event' => $eventName]) }}" alt="QR">
                <p>Escanea y lleva<br>la info contigo</p>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Indicadores de slide -->
    <div class="slide-indicators">
        @foreach($vehicles as $index => $vehicle)
            <div class="slide-indicator {{ $index === 0 ? 'active' : '' }}"
                 onclick="goToSlide({{ $index }})"
                 data-index="{{ $index }}"></div>
        @endforeach
    </div>

    <!-- Panel de info del vehículo -->
    <div class="vehicle-info-panel" id="vehicleInfoPanel">
        <!-- Se llena dinámicamente -->
    </div>

    <!-- Hint de swipe (solo primera vez) -->
    <div class="swipe-hint" id="swipeHint">
        <i class="fas fa-hand-point-left"></i>
        <p>Desliza para ver más vehículos</p>
    </div>

    <!-- Modal de Lead Capture -->
    <div class="modal-kiosk" id="leadModal">
        <div class="modal-content-kiosk">
            <div class="modal-header-kiosk">
                <h3><i class="fas fa-user-plus" style="color: var(--kiosk-accent);"></i> Me Interesa</h3>
                <button class="modal-close" onclick="closeModal('leadModal')">&times;</button>
            </div>
            <form id="leadForm">
                <input type="hidden" name="vehicle_id" id="leadVehicleId">
                <div class="form-group-kiosk">
                    <label>Nombre completo *</label>
                    <input type="text" name="name" class="form-control-kiosk" required placeholder="Tu nombre">
                </div>
                <div class="form-group-kiosk">
                    <label>Telefono *</label>
                    <input type="tel" name="phone" class="form-control-kiosk" required placeholder="8888-8888">
                </div>
                <div class="form-group-kiosk">
                    <label>Email (opcional)</label>
                    <input type="email" name="email" class="form-control-kiosk" placeholder="tu@email.com">
                </div>
                <button type="submit" class="btn-kiosk btn-kiosk-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-paper-plane"></i> Enviar
                </button>
            </form>
        </div>
    </div>

    <!-- Modal de Cotizador -->
    <div class="modal-kiosk" id="quoteModal">
        <div class="modal-content-kiosk">
            <div class="modal-header-kiosk">
                <h3><i class="fas fa-calculator" style="color: var(--kiosk-accent);"></i> Cotizador</h3>
                <button class="modal-close" onclick="closeModal('quoteModal')">&times;</button>
            </div>
            <form id="quoteForm">
                <input type="hidden" name="vehicle_id" id="quoteVehicleId">
                <input type="hidden" name="vehicle_price" id="quoteVehiclePrice">

                <div class="form-group-kiosk">
                    <label>Precio del vehículo</label>
                    <input type="text" class="form-control-kiosk" id="quotePriceDisplay" readonly>
                </div>

                <div class="form-group-kiosk">
                    <label>Prima (enganche) - <span id="downPaymentPercent">20</span>%</label>
                    <input type="range" id="downPaymentSlider" min="10" max="50" value="20" style="width: 100%;">
                    <input type="text" class="form-control-kiosk" id="downPaymentDisplay" readonly style="margin-top: 10px;">
                </div>

                <div class="form-group-kiosk">
                    <label>Plazo (meses)</label>
                    <select name="term_months" class="form-control-kiosk" id="termMonths">
                        <option value="12">12 meses</option>
                        <option value="24">24 meses</option>
                        <option value="36" selected>36 meses</option>
                        <option value="48">48 meses</option>
                        <option value="60">60 meses</option>
                        <option value="72">72 meses</option>
                        <option value="84">84 meses</option>
                    </select>
                </div>

                <div class="form-group-kiosk">
                    <label>Tasa de interés anual (%)</label>
                    <input type="number" name="interest_rate" class="form-control-kiosk" id="interestRate" value="12" min="0" max="30" step="0.5">
                </div>

                <div style="background: var(--kiosk-card); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <div style="text-align: center;">
                        <p style="color: rgba(255,255,255,0.6); font-size: 13px;">Cuota mensual estimada</p>
                        <p style="font-size: 36px; font-weight: 700; color: var(--kiosk-accent);" id="monthlyPaymentDisplay">₡0</p>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 15px; font-size: 13px; color: rgba(255,255,255,0.7);">
                        <span>Total intereses: <strong id="totalInterestDisplay">₡0</strong></span>
                        <span>Monto total: <strong id="totalAmountDisplay">₡0</strong></span>
                    </div>
                </div>

                <button type="button" class="btn-kiosk btn-kiosk-primary" style="width: 100%; justify-content: center; margin-bottom: 10px;" onclick="saveQuote()">
                    <i class="fas fa-download"></i> Guardar Cotización
                </button>
            </form>
        </div>
    </div>

    <script>
        // ============================================
        // CONFIGURACIÓN
        // ============================================
        const vehicles = @json($vehicles);
        const eventName = @json($eventName);
        const autoRotateSeconds = {{ $settings->auto_rotate_seconds ?? 15 }};
        const idleTimeoutSeconds = {{ $settings->idle_timeout_seconds ?? 60 }};
        const showPrice = {{ ($settings->show_price ?? true) ? 'true' : 'false' }};
        const enableQuote = {{ ($settings->enable_quote ?? true) ? 'true' : 'false' }};
        const enableLeadCapture = {{ ($settings->enable_lead_capture ?? true) ? 'true' : 'false' }};

        let currentSlide = 0;
        let touchStartX = 0;
        let touchEndX = 0;
        let autoRotateInterval = null;
        let idleTimeout = null;
        let isPaused = false;
        let spinInstances = [];
        let viewStartTime = Date.now();

        // ============================================
        // SLIDER NAVIGATION
        // ============================================
        function goToSlide(index) {
            if (index < 0) index = vehicles.length - 1;
            if (index >= vehicles.length) index = 0;

            currentSlide = index;
            const slider = document.getElementById('kioskSlider');
            slider.style.transform = `translateX(-${index * 100}%)`;

            // Actualizar indicadores
            document.querySelectorAll('.slide-indicator').forEach((el, i) => {
                el.classList.toggle('active', i === index);
            });

            // Actualizar info del vehículo
            updateVehicleInfo(vehicles[index]);

            // Resetear tracking de vista
            trackViewDuration();
            viewStartTime = Date.now();

            // Iniciar spin si no está iniciado
            initSpinForSlide(index);

            resetIdleTimeout();
        }

        function nextSlide() {
            goToSlide(currentSlide + 1);
        }

        function prevSlide() {
            goToSlide(currentSlide - 1);
        }

        // ============================================
        // TOUCH/SWIPE HANDLING
        // ============================================
        document.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
            pauseAutoRotate();
        }, { passive: true });

        document.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            resumeAutoRotate();
        }, { passive: true });

        function handleSwipe() {
            const diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) nextSlide();
                else prevSlide();
            }
        }

        // ============================================
        // AUTO-ROTATE
        // ============================================
        function startAutoRotate() {
            stopAutoRotate();
            autoRotateInterval = setInterval(() => {
                if (!isPaused) nextSlide();
            }, autoRotateSeconds * 1000);
            updateAutoRotateIndicator(false);
        }

        function stopAutoRotate() {
            if (autoRotateInterval) {
                clearInterval(autoRotateInterval);
                autoRotateInterval = null;
            }
        }

        function pauseAutoRotate() {
            isPaused = true;
            updateAutoRotateIndicator(true);
        }

        function resumeAutoRotate() {
            isPaused = false;
            updateAutoRotateIndicator(false);
            resetIdleTimeout();
        }

        function updateAutoRotateIndicator(paused) {
            const indicator = document.getElementById('autoRotateIndicator');
            const text = document.getElementById('autoRotateText');
            indicator.classList.toggle('paused', paused);
            text.textContent = paused ? 'Pausado' : 'Auto-rotando';
        }

        // ============================================
        // IDLE TIMEOUT
        // ============================================
        function resetIdleTimeout() {
            if (idleTimeout) clearTimeout(idleTimeout);
            idleTimeout = setTimeout(() => {
                // Volver al inicio después de inactividad
                goToSlide(0);
                isPaused = false;
                updateAutoRotateIndicator(false);
            }, idleTimeoutSeconds * 1000);
        }

        document.addEventListener('click', resetIdleTimeout);
        document.addEventListener('touchstart', resetIdleTimeout);

        // ============================================
        // VEHICLE INFO PANEL
        // ============================================
        function updateVehicleInfo(vehicle) {
            const panel = document.getElementById('vehicleInfoPanel');
            const priceFormatted = '₡' + Number(vehicle.price).toLocaleString('es-CR');

            let specsHtml = '';
            if (vehicle.year) specsHtml += `<div class="spec-item"><i class="fas fa-calendar"></i> ${vehicle.year}</div>`;
            if (vehicle.mileage_km) specsHtml += `<div class="spec-item"><i class="fas fa-tachometer-alt"></i> ${Number(vehicle.mileage_km).toLocaleString()} km</div>`;
            if (vehicle.fuel_type) specsHtml += `<div class="spec-item"><i class="fas fa-gas-pump"></i> ${vehicle.fuel_type}</div>`;
            if (vehicle.transmission) specsHtml += `<div class="spec-item"><i class="fas fa-cogs"></i> ${vehicle.transmission}</div>`;
            if (vehicle.engine_cc) specsHtml += `<div class="spec-item"><i class="fas fa-bolt"></i> ${vehicle.engine_cc} CC</div>`;
            if (vehicle.doors) specsHtml += `<div class="spec-item"><i class="fas fa-door-open"></i> ${vehicle.doors} puertas</div>`;

            let buttonsHtml = '';
            if (enableLeadCapture) {
                buttonsHtml += `<button class="btn-kiosk btn-kiosk-primary" onclick="openLeadModal(${vehicle.id})">
                    <i class="fas fa-heart"></i> Me Interesa
                </button>`;
            }
            if (enableQuote) {
                buttonsHtml += `<button class="btn-kiosk btn-kiosk-secondary" onclick="openQuoteModal(${vehicle.id}, ${vehicle.price})">
                    <i class="fas fa-calculator"></i> Cotizar
                </button>`;
            }
            buttonsHtml += `<button class="btn-kiosk btn-kiosk-secondary" onclick="addToWishlist(${vehicle.id})">
                <i class="fas fa-bookmark"></i> Guardar
            </button>`;
            buttonsHtml += `<a href="{{ url('/kiosk/compare') }}?vehicles[]=${vehicle.id}" class="btn-kiosk btn-kiosk-secondary">
                <i class="fas fa-balance-scale"></i> Comparar
            </a>`;

            panel.innerHTML = `
                <div class="vehicle-main-info">
                    <div>
                        <h2 class="vehicle-title">${vehicle.brand} ${vehicle.model}</h2>
                        <p class="vehicle-subtitle">${vehicle.name || ''}</p>
                    </div>
                    ${showPrice ? `<div class="vehicle-price">${priceFormatted}</div>` : ''}
                </div>
                <div class="vehicle-specs">${specsHtml}</div>
                <div class="action-buttons">${buttonsHtml}</div>
            `;
        }

        // ============================================
        // SPIN VIEWER
        // ============================================
        function initSpinForSlide(index) {
            const vehicle = vehicles[index];
            if (!vehicle.scenes || spinInstances[index]) return;

            const scene = vehicle.scenes.find(s => s.spin_id && s.spin);
            if (!scene || !scene.spin) return;

            const spin = scene.spin;
            const canvas = document.getElementById(`spinCanvas${index}`);
            if (!canvas) return;

            const ctx = canvas.getContext('2d', { alpha: false });
            const totalFrames = spin.frames_count || 72;
            const framesDir = spin.frames_dir;
            const baseUrl = `/storage/app/public/${framesDir}/`;

            const images = new Array(totalFrames + 1);
            let loaded = 0;
            let ready = false;
            let orbitIndex = 1;
            let dragging = false;
            let startX = 0;
            let accX = 0;
            let autoRotate = true;
            let autoDir = 1;
            let lastT = performance.now();
            let autoAcc = 0;

            function frameSrc(n) {
                return baseUrl + 'frame-' + String(n).padStart(3, '0') + '.webp';
            }

            function resizeCanvas() {
                const rect = canvas.getBoundingClientRect();
                const dpr = window.devicePixelRatio || 1;
                canvas.width = Math.round(rect.width * dpr);
                canvas.height = Math.round(rect.height * dpr);
            }

            function drawFrame(idx) {
                if (!ready) return;
                resizeCanvas();
                const wrapped = ((idx - 1) % totalFrames + totalFrames) % totalFrames + 1;
                const img = images[wrapped] || images[1];
                if (!img) return;

                const scale = Math.min(canvas.width / img.naturalWidth, canvas.height / img.naturalHeight);
                const dw = img.naturalWidth * scale;
                const dh = img.naturalHeight * scale;
                const dx = (canvas.width - dw) / 2;
                const dy = (canvas.height - dh) / 2;

                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, dx, dy, dw, dh);
            }

            function tick(t) {
                const dt = Math.min(0.05, (t - lastT) / 1000);
                lastT = t;

                if (ready && autoRotate && !dragging) {
                    const framesPerSec = totalFrames / 6;
                    autoAcc += framesPerSec * dt;
                    const step = Math.floor(autoAcc);
                    if (step >= 1) {
                        autoAcc -= step;
                        orbitIndex += autoDir * step;
                    }
                }

                if (ready) drawFrame(orbitIndex);
                requestAnimationFrame(tick);
            }

            // Load frames
            const MIN_READY = Math.min(12, totalFrames);
            for (let i = 1; i <= totalFrames; i++) {
                const img = new Image();
                img.src = frameSrc(i);
                img.onload = () => {
                    images[i] = img;
                    loaded++;
                    if (!ready && loaded >= MIN_READY) {
                        ready = true;
                    }
                };
            }

            // Drag handling
            const viewer = document.getElementById(`spinViewer${index}`);
            viewer.addEventListener('mousedown', e => {
                dragging = true;
                startX = e.clientX;
                accX = 0;
                autoRotate = false;
            });

            document.addEventListener('mousemove', e => {
                if (!dragging) return;
                const dx = e.clientX - startX;
                startX = e.clientX;
                accX += dx;
                const step = Math.trunc(accX / 12);
                if (step !== 0) {
                    accX -= step * 12;
                    orbitIndex += step;
                }
            });

            document.addEventListener('mouseup', () => {
                if (dragging) {
                    dragging = false;
                    setTimeout(() => { autoRotate = true; }, 1500);
                }
            });

            // Touch
            viewer.addEventListener('touchstart', e => {
                if (e.touches.length === 1) {
                    dragging = true;
                    startX = e.touches[0].clientX;
                    accX = 0;
                    autoRotate = false;
                    e.stopPropagation();
                }
            }, { passive: true });

            viewer.addEventListener('touchmove', e => {
                if (!dragging || e.touches.length !== 1) return;
                const dx = e.touches[0].clientX - startX;
                startX = e.touches[0].clientX;
                accX += dx;
                const step = Math.trunc(accX / 12);
                if (step !== 0) {
                    accX -= step * 12;
                    orbitIndex += step;
                }
                e.stopPropagation();
            }, { passive: true });

            viewer.addEventListener('touchend', () => {
                if (dragging) {
                    dragging = false;
                    setTimeout(() => { autoRotate = true; }, 1500);
                }
            }, { passive: true });

            requestAnimationFrame(tick);
            spinInstances[index] = true;
        }

        // ============================================
        // MODALS
        // ============================================
        function openLeadModal(vehicleId) {
            document.getElementById('leadVehicleId').value = vehicleId;
            document.getElementById('leadModal').classList.add('active');
            pauseAutoRotate();
        }

        function openQuoteModal(vehicleId, price) {
            document.getElementById('quoteVehicleId').value = vehicleId;
            document.getElementById('quoteVehiclePrice').value = price;
            document.getElementById('quotePriceDisplay').value = '₡' + Number(price).toLocaleString('es-CR');
            updateQuoteCalculation();
            document.getElementById('quoteModal').classList.add('active');
            pauseAutoRotate();
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            resumeAutoRotate();
        }

        // Close modal on outside click
        document.querySelectorAll('.modal-kiosk').forEach(modal => {
            modal.addEventListener('click', e => {
                if (e.target === modal) closeModal(modal.id);
            });
        });

        // ============================================
        // QUOTE CALCULATOR
        // ============================================
        function updateQuoteCalculation() {
            const price = parseFloat(document.getElementById('quoteVehiclePrice').value) || 0;
            const downPercent = parseFloat(document.getElementById('downPaymentSlider').value) || 20;
            const downPayment = price * (downPercent / 100);
            const termMonths = parseInt(document.getElementById('termMonths').value) || 36;
            const interestRate = parseFloat(document.getElementById('interestRate').value) || 12;

            const principal = price - downPayment;
            const monthlyRate = (interestRate / 100) / 12;

            let monthlyPayment;
            if (interestRate === 0) {
                monthlyPayment = principal / termMonths;
            } else {
                monthlyPayment = principal * (monthlyRate * Math.pow(1 + monthlyRate, termMonths))
                    / (Math.pow(1 + monthlyRate, termMonths) - 1);
            }

            const totalAmount = monthlyPayment * termMonths;
            const totalInterest = totalAmount - principal;

            document.getElementById('downPaymentPercent').textContent = downPercent;
            document.getElementById('downPaymentDisplay').value = '₡' + Math.round(downPayment).toLocaleString('es-CR');
            document.getElementById('monthlyPaymentDisplay').textContent = '₡' + Math.round(monthlyPayment).toLocaleString('es-CR');
            document.getElementById('totalInterestDisplay').textContent = '₡' + Math.round(totalInterest).toLocaleString('es-CR');
            document.getElementById('totalAmountDisplay').textContent = '₡' + Math.round(totalAmount).toLocaleString('es-CR');
        }

        document.getElementById('downPaymentSlider').addEventListener('input', updateQuoteCalculation);
        document.getElementById('termMonths').addEventListener('change', updateQuoteCalculation);
        document.getElementById('interestRate').addEventListener('input', updateQuoteCalculation);

        async function saveQuote() {
            const vehicleId = document.getElementById('quoteVehicleId').value;
            const price = document.getElementById('quoteVehiclePrice').value;
            const downPercent = document.getElementById('downPaymentSlider').value;
            const downPayment = price * (downPercent / 100);
            const termMonths = document.getElementById('termMonths').value;
            const interestRate = document.getElementById('interestRate').value;

            // Pedir datos del cliente
            const name = prompt('Tu nombre:');
            const phone = prompt('Tu teléfono:');
            if (!name || !phone) return;

            try {
                const response = await fetch('{{ route("kiosk.quote.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        vehicle_id: vehicleId,
                        vehicle_price: price,
                        down_payment: downPayment,
                        term_months: termMonths,
                        interest_rate: interestRate,
                        customer_name: name,
                        customer_phone: phone,
                        event_name: eventName
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Cotización guardada! Puedes descargar el PDF.');
                    window.open(`/kiosk/quote/${data.quote_id}/pdf`, '_blank');
                    closeModal('quoteModal');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al guardar la cotización');
            }
        }

        // ============================================
        // LEAD FORM
        // ============================================
        document.getElementById('leadForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            data.event_name = eventName;
            data.source = 'kiosk';
            data.vehicles_viewed = [vehicles[currentSlide].id];

            try {
                const response = await fetch('{{ route("kiosk.lead.capture") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    alert('Gracias por tu interés! Un asesor te contactará pronto.');
                    e.target.reset();
                    closeModal('leadModal');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al enviar. Intenta de nuevo.');
            }
        });

        // ============================================
        // WISHLIST
        // ============================================
        let wishlistToken = localStorage.getItem('wishlistToken') || null;

        async function addToWishlist(vehicleId) {
            try {
                const response = await fetch('{{ route("kiosk.wishlist.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        token: wishlistToken,
                        add_vehicle: vehicleId,
                        event_name: eventName
                    })
                });

                const data = await response.json();
                if (data.success) {
                    wishlistToken = data.wishlist.share_token;
                    localStorage.setItem('wishlistToken', wishlistToken);
                    alert('Agregado a tu lista! Accede en:\n' + data.share_url);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // ============================================
        // TRACKING
        // ============================================
        async function trackViewDuration() {
            const duration = Math.round((Date.now() - viewStartTime) / 1000);
            if (duration < 2) return;

            try {
                await fetch('{{ route("kiosk.track.view") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        vehicle_id: vehicles[currentSlide].id,
                        duration: duration,
                        spin_interacted: true
                    })
                });
            } catch (error) {
                console.error('Tracking error:', error);
            }
        }

        // Track before leaving
        window.addEventListener('beforeunload', trackViewDuration);

        // ============================================
        // INIT
        // ============================================
        document.addEventListener('DOMContentLoaded', () => {
            if (vehicles.length > 0) {
                updateVehicleInfo(vehicles[0]);
                initSpinForSlide(0);
                startAutoRotate();
                resetIdleTimeout();
            }

            // Hide swipe hint after 5 seconds
            setTimeout(() => {
                const hint = document.getElementById('swipeHint');
                if (hint) hint.style.display = 'none';
            }, 5000);
        });

        // Keyboard navigation
        document.addEventListener('keydown', e => {
            if (e.key === 'ArrowRight') nextSlide();
            if (e.key === 'ArrowLeft') prevSlide();
            if (e.key === 'Escape') {
                closeModal('leadModal');
                closeModal('quoteModal');
            }
        });
    </script>
</body>
</html>
