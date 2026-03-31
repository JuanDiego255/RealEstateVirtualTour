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

        .back-home-btn {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: var(--kiosk-card);
            border: 1px solid var(--kiosk-border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--kiosk-text);
            text-decoration: none;
            font-size: 18px;
            transition: all 0.2s ease;
        }

        .back-home-btn:hover {
            background: var(--kiosk-accent);
            color: #000;
            border-color: var(--kiosk-accent);
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

        /* Imagen estática para vehículos sin spin */
        .spin-viewer .static-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .spin-viewer .no-spin-overlay {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.7);
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 13px;
            color: rgba(255,255,255,0.8);
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

        /* QR flotante - ahora posicionado absoluto dentro de cada slide */
        .qr-floating {
            position: absolute;
            top: 100px;
            right: 30px;
            background: #fff;
            padding: 15px;
            border-radius: 15px;
            text-align: center;
            z-index: 70;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .kiosk-slide.active .qr-floating {
            opacity: 1;
            pointer-events: auto;
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

        /* Comparador flotante */
        .compare-floating {
            position: fixed;
            bottom: 220px;
            right: 30px;
            background: var(--kiosk-card);
            border: 1px solid var(--kiosk-border);
            border-radius: 15px;
            padding: 15px;
            z-index: 80;
            min-width: 200px;
            display: none;
        }

        .compare-floating.active {
            display: block;
        }

        .compare-floating h4 {
            font-size: 13px;
            margin-bottom: 10px;
            color: var(--kiosk-accent);
        }

        .compare-list {
            max-height: 150px;
            overflow-y: auto;
        }

        .compare-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            background: rgba(0,0,0,0.2);
            border-radius: 8px;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .compare-item .remove-btn {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            padding: 2px 6px;
        }

        .compare-actions {
            margin-top: 10px;
            display: flex;
            gap: 8px;
        }

        .compare-actions button,
        .compare-actions a {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .compare-actions .btn-compare-go {
            background: var(--kiosk-accent);
            color: #000;
        }

        .compare-actions .btn-compare-clear {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }

        .btn-kiosk .compare-badge {
            background: var(--kiosk-accent);
            color: #000;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 5px;
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

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 80px;
            right: 30px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            padding: 16px 24px;
            border-radius: 12px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            animation: toastSlideIn 0.3s ease, toastFadeOut 0.3s ease forwards;
            animation-delay: 0s, 3s;
            max-width: 350px;
        }

        .toast-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .toast-info {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .toast i {
            font-size: 20px;
        }

        @keyframes toastSlideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes toastFadeOut {
            from { opacity: 1; }
            to { opacity: 0; visibility: hidden; }
        }

        /* Spin rotation indicator */
        .spin-rotate-hint {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.75);
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 13px;
            color: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            gap: 10px;
            pointer-events: none;
            z-index: 10;
        }

        .spin-rotate-hint i {
            color: var(--kiosk-accent);
            animation: rotateHint 2s infinite;
        }

        @keyframes rotateHint {
            0%, 100% { transform: rotate(-20deg); }
            50% { transform: rotate(20deg); }
        }

        /* Interest level buttons */
        .interest-levels {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .interest-btn {
            flex: 1;
            padding: 12px 8px;
            background: var(--kiosk-card);
            border: 1px solid var(--kiosk-border);
            border-radius: 10px;
            color: #fff;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            font-size: 12px;
        }

        .interest-btn:hover {
            background: rgba(255,255,255,0.15);
        }

        .interest-btn.active {
            background: var(--kiosk-accent);
            color: #000;
            border-color: var(--kiosk-accent);
        }

        .interest-btn i {
            display: block;
            font-size: 18px;
            margin-bottom: 5px;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="kiosk-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="{{ url('/') }}" class="back-home-btn" title="Volver al inicio">
                <i class="fas fa-arrow-left"></i>
            </a>
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

    <!-- Indicador de control manual (antes era auto-rotate) -->
    <div class="auto-rotate-indicator" id="autoRotateIndicator" style="display: none;">
        <span class="auto-rotate-dot"></span>
        <span id="autoRotateText">Control manual</span>
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
        @php
            $hasSpin = $vehicle->scenes->contains(fn($s) => $s->spin_id && $s->spin);
        @endphp
        <div class="kiosk-slide {{ $index === 0 ? 'active' : '' }}" data-vehicle-id="{{ $vehicle->id }}" data-index="{{ $index }}" data-has-spin="{{ $hasSpin ? '1' : '0' }}">
            <div class="spin-container">
                <div class="spin-viewer" id="spinViewer{{ $index }}">
                    @if($hasSpin)
                        <canvas id="spinCanvas{{ $index }}"></canvas>
                        <div class="spin-rotate-hint">
                            <i class="fas fa-sync-alt"></i>
                            <span>Arrastra para rotar 360</span>
                        </div>
                    @else
                        <img class="static-image"
                             src="{{ $vehicle->image ? route('file', $vehicle->image) : url('images/producto-sin-imagen.PNG') }}"
                             alt="{{ $vehicle->brand }} {{ $vehicle->model }}">
                        <div class="no-spin-overlay">
                            <i class="fas fa-image"></i> Vista del vehículo
                        </div>
                    @endif
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

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>


    <!-- Widget flotante de comparador -->
    <div class="compare-floating" id="compareFloating">
        <h4><i class="fas fa-balance-scale"></i> Comparador</h4>
        <div class="compare-list" id="compareList">
            <!-- Se llena dinámicamente -->
        </div>
        <div class="compare-actions">
            <button class="btn-compare-clear" onclick="clearCompareList()">Limpiar</button>
            <a href="#" class="btn-compare-go" id="compareGoBtn" onclick="goToCompare(event)">Comparar</a>
        </div>
    </div>

    <!-- Modal de selección de vehículos para comparar -->
    <div class="modal-kiosk" id="compareModal">
        <div class="modal-content-kiosk" style="max-width: 700px;">
            <div class="modal-header-kiosk">
                <h3><i class="fas fa-balance-scale" style="color: var(--kiosk-accent);"></i> Seleccionar vehículos para comparar</h3>
                <button class="modal-close" onclick="closeModal('compareModal')">&times;</button>
            </div>
            <p style="color: rgba(255,255,255,0.7); margin-bottom: 20px;">Selecciona 2 o más vehículos para compararlos lado a lado.</p>
            <div id="compareVehicleGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; max-height: 400px; overflow-y: auto;">
                @foreach($vehicles as $vehicle)
                <div class="compare-vehicle-option" data-vehicle-id="{{ $vehicle->id }}" onclick="toggleCompareSelection({{ $vehicle->id }}, '{{ $vehicle->brand }} {{ $vehicle->model }}')" style="background: var(--kiosk-card); border-radius: 12px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all 0.2s;">
                    <img src="{{ $vehicle->image ? route('file', $vehicle->image) : url('images/producto-sin-imagen.PNG') }}" alt="{{ $vehicle->brand }}" style="width: 100%; aspect-ratio: 16/10; object-fit: cover;">
                    <div style="padding: 12px;">
                        <p style="font-weight: 600; font-size: 13px;">{{ $vehicle->brand }} {{ $vehicle->model }}</p>
                        <p style="color: var(--kiosk-accent); font-size: 12px;">₡{{ number_format($vehicle->price) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                <button class="btn-kiosk btn-kiosk-secondary" onclick="closeModal('compareModal')">Cancelar</button>
                <button class="btn-kiosk btn-kiosk-primary" id="compareModalBtn" onclick="goToCompare(event)" disabled>
                    <i class="fas fa-balance-scale"></i> Comparar (<span id="compareCount">0</span>)
                </button>
            </div>
        </div>
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
                <input type="hidden" name="interest_level" id="leadInterestLevel" value="medium">
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
                <div class="form-group-kiosk">
                    <label>Nivel de interés</label>
                    <div class="interest-levels">
                        <button type="button" class="interest-btn" data-level="low" onclick="selectInterestLevel(this)">
                            <i class="fas fa-thermometer-empty"></i>
                            Bajo
                        </button>
                        <button type="button" class="interest-btn active" data-level="medium" onclick="selectInterestLevel(this)">
                            <i class="fas fa-thermometer-half"></i>
                            Medio
                        </button>
                        <button type="button" class="interest-btn" data-level="high" onclick="selectInterestLevel(this)">
                            <i class="fas fa-thermometer-three-quarters"></i>
                            Alto
                        </button>
                        <button type="button" class="interest-btn" data-level="hot" onclick="selectInterestLevel(this)">
                            <i class="fas fa-fire"></i>
                            Compra
                        </button>
                    </div>
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

                <div class="form-group-kiosk">
                    <label>Tu nombre *</label>
                    <input type="text" name="customer_name" id="quoteCustomerName" class="form-control-kiosk" required placeholder="Tu nombre completo">
                </div>
                <div class="form-group-kiosk">
                    <label>Tu teléfono *</label>
                    <input type="tel" name="customer_phone" id="quoteCustomerPhone" class="form-control-kiosk" required placeholder="8888-8888">
                </div>

                <button type="button" class="btn-kiosk btn-kiosk-primary" style="width: 100%; justify-content: center; margin-bottom: 10px;" onclick="submitQuoteDirectly()">
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
        // TOAST NOTIFICATIONS
        // ============================================
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;

            let icon = 'fa-check-circle';
            if (type === 'error') icon = 'fa-exclamation-circle';
            if (type === 'info') icon = 'fa-info-circle';

            toast.innerHTML = `<i class="fas ${icon}"></i><span>${message}</span>`;
            container.appendChild(toast);

            // Remove after animation
            setTimeout(() => {
                toast.remove();
            }, 3500);
        }

        // ============================================
        // INTEREST LEVEL SELECTION
        // ============================================
        function selectInterestLevel(btn) {
            document.querySelectorAll('#leadModal .interest-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('leadInterestLevel').value = btn.dataset.level;
        }

        // ============================================
        // SLIDER NAVIGATION
        // ============================================
        function goToSlide(index) {
            if (vehicles.length === 0) return;

            if (index < 0) index = vehicles.length - 1;
            if (index >= vehicles.length) index = 0;

            currentSlide = index;
            const slider = document.getElementById('kioskSlider');
            slider.style.transform = `translateX(-${index * 100}%)`;

            // Actualizar indicadores
            document.querySelectorAll('.slide-indicator').forEach((el, i) => {
                el.classList.toggle('active', i === index);
            });

            // Actualizar clase active en slides (para el QR)
            document.querySelectorAll('.kiosk-slide').forEach((el, i) => {
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
            if (!vehicle) return;

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
            const isInCompare = compareList.includes(vehicle.id);
            buttonsHtml += `<button class="btn-kiosk btn-kiosk-secondary" onclick="toggleCompareVehicle(${vehicle.id}, '${vehicle.brand} ${vehicle.model}')" id="compareBtn${vehicle.id}">
                <i class="fas fa-balance-scale"></i> ${isInCompare ? 'Quitar' : 'Comparar'}
                ${compareList.length > 0 ? '<span class="compare-badge">' + compareList.length + '</span>' : ''}
            </button>`;
            buttonsHtml += `<a href="/admin/test-drive/${vehicle.id}/preview" class="btn-kiosk btn-kiosk-secondary" style="text-decoration: none;">
                <i class="fas fa-car"></i> Test Drive
            </a>`;
            buttonsHtml += `<a href="/virtual-tour/${vehicle.id}" class="btn-kiosk btn-kiosk-secondary" style="text-decoration: none;">
                <i class="fas fa-vr-cardboard"></i> Virtual Tour
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
            const slide = document.querySelector(`.kiosk-slide[data-index="${index}"]`);

            // Si ya está inicializado o no tiene spin, no hacer nada
            if (spinInstances[index]) return;
            if (!slide || slide.dataset.hasSpin !== '1') return;
            if (!vehicle.scenes) return;

            const scene = vehicle.scenes.find(s => s.spin_id && s.spin);
            if (!scene || !scene.spin) return;

            const spin = scene.spin;
            const canvas = document.getElementById(`spinCanvas${index}`);
            if (!canvas) return;

            const ctx = canvas.getContext('2d', { alpha: false });
            const totalFrames = spin.frames_count || 72;
            const framesDir = spin.frames_dir;
            const baseUrl = `/storage/${framesDir}/`;

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

        async function submitQuoteDirectly() {
            const vehicleId = document.getElementById('quoteVehicleId').value;
            const price = document.getElementById('quoteVehiclePrice').value;
            const downPercent = document.getElementById('downPaymentSlider').value;
            const downPayment = price * (downPercent / 100);
            const termMonths = document.getElementById('termMonths').value;
            const interestRate = document.getElementById('interestRate').value;
            const name = document.getElementById('quoteCustomerName').value.trim();
            const phone = document.getElementById('quoteCustomerPhone').value.trim();

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
                    showToast('Cotización guardada! Descargando PDF...', 'success');
                    window.open(`/kiosk/quote/${data.quote_id}/pdf`, '_blank');
                    document.getElementById('quoteCustomerName').value = '';
                    document.getElementById('quoteCustomerPhone').value = '';
                    closeModal('quoteModal');
                } else {
                    showToast('Error al guardar la cotización', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al guardar la cotización', 'error');
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
                    showToast('Gracias por tu interés! Un asesor te contactará pronto.', 'success');
                    e.target.reset();
                    // Reset interest level buttons
                    document.querySelectorAll('#leadModal .interest-btn').forEach(b => b.classList.remove('active'));
                    document.querySelector('#leadModal .interest-btn[data-level="medium"]').classList.add('active');
                    document.getElementById('leadInterestLevel').value = 'medium';
                    closeModal('leadModal');
                } else {
                    showToast('Error al enviar. Intenta de nuevo.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al enviar. Intenta de nuevo.', 'error');
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
                    showToast('Vehículo agregado a tu lista de favoritos!', 'success');
                } else {
                    showToast('Error al guardar. Intenta de nuevo.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al guardar. Intenta de nuevo.', 'error');
            }
        }

        // ============================================
        // COMPARADOR
        // ============================================
        let compareList = JSON.parse(localStorage.getItem('compareList') || '[]');
        let compareNames = JSON.parse(localStorage.getItem('compareNames') || '{}');

        function updateCompareUI() {
            const floating = document.getElementById('compareFloating');
            const list = document.getElementById('compareList');
            const countEl = document.getElementById('compareCount');
            const goBtn = document.getElementById('compareGoBtn');
            const modalBtn = document.getElementById('compareModalBtn');

            // Actualizar contador
            if (countEl) countEl.textContent = compareList.length;

            // Mostrar/ocultar widget flotante
            if (compareList.length > 0) {
                floating.classList.add('active');
                list.innerHTML = compareList.map(id => `
                    <div class="compare-item">
                        <span>${compareNames[id] || 'Vehículo ' + id}</span>
                        <button class="remove-btn" onclick="removeFromCompare(${id})"><i class="fas fa-times"></i></button>
                    </div>
                `).join('');
            } else {
                floating.classList.remove('active');
            }

            // Habilitar/deshabilitar botón de comparar
            const canCompare = compareList.length >= 2;
            if (goBtn) {
                goBtn.style.opacity = canCompare ? '1' : '0.5';
                goBtn.style.pointerEvents = canCompare ? 'auto' : 'none';
            }
            if (modalBtn) {
                modalBtn.disabled = !canCompare;
            }

            // Actualizar selección visual en el modal
            document.querySelectorAll('.compare-vehicle-option').forEach(el => {
                const vid = parseInt(el.dataset.vehicleId);
                el.style.borderColor = compareList.includes(vid) ? 'var(--kiosk-accent)' : 'transparent';
            });

            // Guardar en localStorage
            localStorage.setItem('compareList', JSON.stringify(compareList));
            localStorage.setItem('compareNames', JSON.stringify(compareNames));
        }

        function toggleCompareVehicle(vehicleId, vehicleName) {
            const index = compareList.indexOf(vehicleId);
            if (index > -1) {
                compareList.splice(index, 1);
                delete compareNames[vehicleId];
                showToast('Vehículo removido de comparación', 'info');
            } else {
                if (compareList.length >= 4) {
                    showToast('Máximo 4 vehículos para comparar', 'error');
                    return;
                }
                compareList.push(vehicleId);
                compareNames[vehicleId] = vehicleName;
                showToast('Vehículo agregado a comparación', 'success');
            }
            updateCompareUI();
            // Actualizar el panel de info para reflejar el cambio
            updateVehicleInfo(vehicles[currentSlide]);
        }

        function toggleCompareSelection(vehicleId, vehicleName) {
            toggleCompareVehicle(vehicleId, vehicleName);
        }

        function removeFromCompare(vehicleId) {
            const index = compareList.indexOf(vehicleId);
            if (index > -1) {
                compareList.splice(index, 1);
                delete compareNames[vehicleId];
            }
            updateCompareUI();
            updateVehicleInfo(vehicles[currentSlide]);
        }

        function clearCompareList() {
            compareList = [];
            compareNames = {};
            updateCompareUI();
            updateVehicleInfo(vehicles[currentSlide]);
        }

        function goToCompare(event) {
            event.preventDefault();
            if (compareList.length < 2) {
                showToast('Selecciona al menos 2 vehículos para comparar', 'info');
                return;
            }
            const params = compareList.map(id => `vehicles[]=${id}`).join('&');
            window.location.href = `{{ url('/kiosk/compare') }}?${params}&event=${encodeURIComponent(eventName || '')}`;
        }

        function openCompareModal() {
            document.getElementById('compareModal').classList.add('active');
            updateCompareUI();
            pauseAutoRotate();
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
            // Inicializar UI del comparador
            updateCompareUI();

            if (vehicles.length > 0) {
                updateVehicleInfo(vehicles[0]);
                initSpinForSlide(0);
                // Auto-rotate deshabilitado - control manual por el agente
                // startAutoRotate();
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
                closeModal('compareModal');
            }
        });
    </script>
</body>
</html>
