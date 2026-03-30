<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $vehicle->brand }} {{ $vehicle->model }} - Tour Virtual</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--kiosk-bg);
            color: var(--kiosk-text);
        }

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

        .back-btn {
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

        .back-btn:hover {
            background: var(--kiosk-accent);
            color: #000;
        }

        .main-container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .spin-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px 40px 200px;
            position: relative;
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

        .spin-viewer .static-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-spin-badge {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.7);
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 13px;
        }

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

        /* QR flotante */
        .qr-floating {
            position: fixed;
            top: 90px;
            right: 30px;
            background: white;
            padding: 15px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 90;
        }

        .qr-floating img {
            width: 120px;
            height: 120px;
        }

        .qr-floating p {
            margin-top: 10px;
            font-size: 11px;
            color: #333;
            line-height: 1.3;
        }

        /* Colors panel */
        .colors-panel {
            position: fixed;
            top: 90px;
            left: 30px;
            background: var(--kiosk-card);
            padding: 15px;
            border-radius: 16px;
            border: 1px solid var(--kiosk-border);
            z-index: 90;
        }

        .colors-panel h4 {
            font-size: 12px;
            margin-bottom: 10px;
            color: rgba(255,255,255,0.7);
        }

        .color-options {
            display: flex;
            gap: 8px;
        }

        .color-swatch {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .color-swatch:hover,
        .color-swatch.active {
            border-color: var(--kiosk-accent);
            transform: scale(1.1);
        }

        /* Modal */
        .modal-kiosk {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.85);
            z-index: 200;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-kiosk.active {
            display: flex;
        }

        .modal-content-kiosk {
            background: #1a1e24;
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header-kiosk {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header-kiosk h3 {
            font-size: 20px;
        }

        .modal-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 28px;
            cursor: pointer;
        }

        .form-group-kiosk {
            margin-bottom: 15px;
        }

        .form-group-kiosk label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            color: rgba(255,255,255,0.7);
        }

        .form-control-kiosk {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid var(--kiosk-border);
            background: var(--kiosk-card);
            color: #fff;
            font-size: 15px;
        }

        .form-control-kiosk:focus {
            outline: none;
            border-color: var(--kiosk-accent);
        }

        /* Test drive button */
        .test-drive-btn {
            position: fixed;
            bottom: 180px;
            right: 30px;
            z-index: 80;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="kiosk-header">
        <a href="{{ route('kiosk.index', ['event' => $eventName]) }}" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        @if($settings->logo)
            <img src="{{ route('file', $settings->logo) }}" alt="Logo" class="kiosk-logo">
        @endif
        <div></div>
    </header>

    <!-- Main content -->
    <div class="main-container">
        <div class="spin-container">
            <div class="spin-viewer" id="spinViewer">
                @if($spin)
                    <canvas id="spinCanvas"></canvas>
                @else
                    <img class="static-image"
                         src="{{ $vehicle->image ? route('file', $vehicle->image) : url('images/producto-sin-imagen.PNG') }}"
                         alt="{{ $vehicle->brand }} {{ $vehicle->model }}">
                    <div class="no-spin-badge">
                        <i class="fas fa-image"></i> Vista del vehiculo
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- QR flotante -->
    @if($settings->enable_qr ?? true)
    <div class="qr-floating">
        <img src="{{ route('kiosk.qr', ['vehicleId' => $vehicle->id, 'event' => $eventName]) }}" alt="QR">
        <p>Escanea y lleva<br>la info contigo</p>
    </div>
    @endif

    <!-- Panel de colores -->
    @if($colors->count() > 0)
    <div class="colors-panel">
        <h4>Colores disponibles</h4>
        <div class="color-options">
            @foreach($colors as $color)
            <div class="color-swatch {{ $loop->first ? 'active' : '' }}"
                 style="background: {{ $color->hex_code }}"
                 title="{{ $color->name }}"
                 onclick="selectColor('{{ $color->id }}', '{{ $color->spin_folder ?? '' }}')">
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Info del vehiculo -->
    <div class="vehicle-info-panel">
        <div class="vehicle-main-info">
            <div>
                <h1 class="vehicle-title">{{ $vehicle->brand }} {{ $vehicle->model }}</h1>
                <p class="vehicle-subtitle">{{ $vehicle->year }} {{ $vehicle->version ?? '' }}</p>
            </div>
            <div class="vehicle-price">{{ $vehicle->price_formatted ?? ('CRC ' . number_format($vehicle->price)) }}</div>
        </div>

        <div class="vehicle-specs">
            @if($vehicle->mileage)
            <div class="spec-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>{{ number_format($vehicle->mileage) }} km</span>
            </div>
            @endif
            @if($vehicle->engine)
            <div class="spec-item">
                <i class="fas fa-cog"></i>
                <span>{{ $vehicle->engine }}</span>
            </div>
            @endif
            @if($vehicle->transmission)
            <div class="spec-item">
                <i class="fas fa-cogs"></i>
                <span>{{ $vehicle->transmission }}</span>
            </div>
            @endif
            @if($vehicle->fuel_type)
            <div class="spec-item">
                <i class="fas fa-gas-pump"></i>
                <span>{{ $vehicle->fuel_type }}</span>
            </div>
            @endif
            @if($vehicle->color)
            <div class="spec-item">
                <i class="fas fa-palette"></i>
                <span>{{ $vehicle->color }}</span>
            </div>
            @endif
        </div>

        <div class="action-buttons">
            <button class="btn-kiosk btn-kiosk-primary" onclick="openModal('leadModal')">
                <i class="fas fa-heart"></i> Me Interesa
            </button>
            <button class="btn-kiosk btn-kiosk-secondary" onclick="openModal('quoteModal')">
                <i class="fas fa-calculator"></i> Cotizar
            </button>
            @if($testDriveVideos->count() > 0)
            <button class="btn-kiosk btn-kiosk-secondary" onclick="openTestDrive()">
                <i class="fas fa-car"></i> Test Drive Virtual
            </button>
            @endif
        </div>
    </div>

    <!-- Modal de Lead -->
    <div class="modal-kiosk" id="leadModal">
        <div class="modal-content-kiosk">
            <div class="modal-header-kiosk">
                <h3><i class="fas fa-user-plus" style="color: var(--kiosk-accent);"></i> Me Interesa</h3>
                <button class="modal-close" onclick="closeModal('leadModal')">&times;</button>
            </div>
            <form id="leadForm">
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
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
                    <input type="email" name="email" class="form-control-kiosk" placeholder="correo@ejemplo.com">
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
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                <input type="hidden" name="vehicle_price" value="{{ $vehicle->price }}">

                <div class="form-group-kiosk">
                    <label>Prima ({{ $settings->min_down_payment ?? 10 }}% - 50%)</label>
                    <input type="range" name="down_payment_percent" id="downPaymentRange"
                           min="{{ $settings->min_down_payment ?? 10 }}" max="50" value="20"
                           class="form-control-kiosk" style="padding: 5px;"
                           onchange="calculateQuote()">
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-top: 5px;">
                        <span id="downPaymentPercent">20%</span>
                        <span id="downPaymentAmount">CRC {{ number_format($vehicle->price * 0.2) }}</span>
                    </div>
                </div>

                <div class="form-group-kiosk">
                    <label>Plazo (meses)</label>
                    <select name="term_months" class="form-control-kiosk" onchange="calculateQuote()">
                        <option value="24">24 meses</option>
                        <option value="36">36 meses</option>
                        <option value="48" selected>48 meses</option>
                        <option value="60">60 meses</option>
                        <option value="72">72 meses</option>
                        <option value="84">84 meses</option>
                    </select>
                </div>

                <div style="background: var(--kiosk-card); padding: 20px; border-radius: 12px; margin: 20px 0; text-align: center;">
                    <p style="font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 5px;">Cuota mensual estimada</p>
                    <p id="monthlyPayment" style="font-size: 32px; font-weight: 700; color: var(--kiosk-accent);">
                        Calculando...
                    </p>
                </div>

                <div class="form-group-kiosk">
                    <label>Tu nombre *</label>
                    <input type="text" name="customer_name" class="form-control-kiosk" required>
                </div>
                <div class="form-group-kiosk">
                    <label>Tu telefono *</label>
                    <input type="tel" name="customer_phone" class="form-control-kiosk" required>
                </div>

                <button type="submit" class="btn-kiosk btn-kiosk-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-file-pdf"></i> Guardar Cotizacion
                </button>
            </form>
        </div>
    </div>

    <script>
        const vehicleId = {{ $vehicle->id }};
        const eventName = '{{ $eventName ?? "" }}';
        const vehiclePrice = {{ $vehicle->price }};
        const interestRate = {{ $settings->default_interest_rate ?? 12 }};

        // Modal functions
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Quote calculator
        function calculateQuote() {
            const form = document.getElementById('quoteForm');
            const downPaymentPercent = parseInt(form.down_payment_percent.value);
            const termMonths = parseInt(form.term_months.value);

            const downPayment = vehiclePrice * (downPaymentPercent / 100);
            const loanAmount = vehiclePrice - downPayment;
            const monthlyRate = (interestRate / 100) / 12;
            const monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, termMonths)) / (Math.pow(1 + monthlyRate, termMonths) - 1);

            document.getElementById('downPaymentPercent').textContent = downPaymentPercent + '%';
            document.getElementById('downPaymentAmount').textContent = 'CRC ' + Math.round(downPayment).toLocaleString();
            document.getElementById('monthlyPayment').textContent = 'CRC ' + Math.round(monthlyPayment).toLocaleString();
        }

        // Lead form
        document.getElementById('leadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('event_name', eventName);
            formData.append('source', 'kiosk');

            try {
                const response = await fetch('{{ route("kiosk.lead.capture") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                if (response.ok) {
                    alert('Gracias por tu interes. Te contactaremos pronto.');
                    closeModal('leadModal');
                    e.target.reset();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });

        // Quote form
        document.getElementById('quoteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const downPaymentPercent = parseInt(formData.get('down_payment_percent'));
            formData.set('down_payment', vehiclePrice * (downPaymentPercent / 100));
            formData.append('interest_rate', interestRate);
            formData.append('event_name', eventName);

            try {
                const response = await fetch('{{ route("kiosk.quote.save") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                if (response.ok) {
                    const data = await response.json();
                    window.open('{{ url("kiosk/quote") }}/' + data.quote_id + '/pdf', '_blank');
                    closeModal('quoteModal');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });

        // Initialize
        calculateQuote();

        @if($spin)
        // Spin 360 initialization
        const spinConfig = {
            folder: '{{ $spin->folder_path }}',
            frames: {{ $spin->frame_count }},
            startFrame: {{ $spin->initial_frame ?? 1 }}
        };

        let currentFrame = spinConfig.startFrame;
        let images = [];
        let isLoaded = false;
        const canvas = document.getElementById('spinCanvas');
        const ctx = canvas?.getContext('2d');

        function loadSpinImages() {
            if (!canvas) return;

            const container = document.getElementById('spinViewer');
            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;

            for (let i = 1; i <= spinConfig.frames; i++) {
                const img = new Image();
                img.src = `/storage/${spinConfig.folder}/${String(i).padStart(3, '0')}.jpg`;
                images.push(img);
            }

            images[0].onload = () => {
                isLoaded = true;
                renderFrame();
            };
        }

        function renderFrame() {
            if (!ctx || !images[currentFrame - 1]) return;
            const img = images[currentFrame - 1];
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            const scale = Math.min(canvas.width / img.width, canvas.height / img.height);
            const x = (canvas.width - img.width * scale) / 2;
            const y = (canvas.height - img.height * scale) / 2;

            ctx.drawImage(img, x, y, img.width * scale, img.height * scale);
        }

        // Drag interaction
        let isDragging = false;
        let lastX = 0;

        canvas?.addEventListener('mousedown', (e) => {
            isDragging = true;
            lastX = e.clientX;
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging || !isLoaded) return;
            const deltaX = e.clientX - lastX;
            if (Math.abs(deltaX) > 5) {
                currentFrame += deltaX > 0 ? 1 : -1;
                if (currentFrame > spinConfig.frames) currentFrame = 1;
                if (currentFrame < 1) currentFrame = spinConfig.frames;
                renderFrame();
                lastX = e.clientX;
            }
        });

        document.addEventListener('mouseup', () => isDragging = false);

        // Touch support
        canvas?.addEventListener('touchstart', (e) => {
            isDragging = true;
            lastX = e.touches[0].clientX;
        });

        document.addEventListener('touchmove', (e) => {
            if (!isDragging || !isLoaded) return;
            const deltaX = e.touches[0].clientX - lastX;
            if (Math.abs(deltaX) > 5) {
                currentFrame += deltaX > 0 ? 1 : -1;
                if (currentFrame > spinConfig.frames) currentFrame = 1;
                if (currentFrame < 1) currentFrame = spinConfig.frames;
                renderFrame();
                lastX = e.touches[0].clientX;
            }
        });

        document.addEventListener('touchend', () => isDragging = false);

        loadSpinImages();
        @endif

        // Color selection
        function selectColor(colorId, spinFolder) {
            document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
            event.target.classList.add('active');

            if (spinFolder) {
                // Reload spin with new color folder
                spinConfig.folder = spinFolder;
                images = [];
                loadSpinImages();
            }
        }

        // Test drive
        function openTestDrive() {
            alert('Funcion de Test Drive Virtual disponible proximamente');
        }

        // Track view duration
        let viewStartTime = Date.now();
        window.addEventListener('beforeunload', () => {
            const duration = Math.round((Date.now() - viewStartTime) / 1000);
            navigator.sendBeacon('{{ route("kiosk.track.view") }}', JSON.stringify({
                vehicle_id: vehicleId,
                duration: duration,
                _token: '{{ csrf_token() }}'
            }));
        });
    </script>
</body>
</html>
