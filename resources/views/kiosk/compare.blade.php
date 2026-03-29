<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Comparador de Vehículos - Side by Side</title>

    <style>
        :root {
            --accent: #c2ac1f;
            --bg: #0b0f14;
            --card: rgba(255, 255, 255, 0.08);
            --border: rgba(255, 255, 255, 0.12);
            --better: #22c55e;
            --worse: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: #fff;
            min-height: 100vh;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            background: rgba(0,0,0,0.5);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header h1 {
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header h1 i {
            color: var(--accent);
        }

        .back-link {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: var(--card);
            border-radius: 10px;
        }

        .compare-container {
            display: flex;
            gap: 20px;
            padding: 20px;
            overflow-x: auto;
        }

        .vehicle-column {
            flex: 1;
            min-width: 400px;
            background: var(--card);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        /* Spin Viewer */
        .spin-wrapper {
            aspect-ratio: 16/10;
            background: #000;
            position: relative;
            cursor: grab;
        }

        .spin-wrapper:active { cursor: grabbing; }

        .spin-wrapper canvas {
            width: 100%;
            height: 100%;
            display: block;
        }

        .sync-indicator {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 6px 12px;
            background: rgba(0,0,0,0.7);
            border-radius: 20px;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .sync-dot {
            width: 8px;
            height: 8px;
            background: var(--accent);
            border-radius: 50%;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Vehicle Info */
        .vehicle-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }

        .vehicle-name {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .vehicle-subtitle {
            color: rgba(255,255,255,0.6);
            font-size: 14px;
        }

        .vehicle-price {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
            margin-top: 10px;
        }

        /* Specs Comparison */
        .specs-section {
            padding: 20px;
        }

        .specs-section h4 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 15px;
        }

        .spec-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 15px;
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
            margin-bottom: 8px;
        }

        .spec-row .label {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .spec-row .label i {
            color: var(--accent);
            width: 18px;
        }

        .spec-row .value {
            font-weight: 600;
            font-size: 14px;
        }

        .spec-row .value.better {
            color: var(--better);
        }

        .spec-row .value.worse {
            color: var(--worse);
        }

        /* Actions */
        .actions {
            padding: 20px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
        }

        .action-btn {
            flex: 1;
            padding: 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--accent);
            color: #000;
        }

        .btn-secondary {
            background: var(--card);
            color: #fff;
            border: 1px solid var(--border);
        }

        /* Add vehicle button */
        .add-vehicle-btn {
            min-width: 300px;
            flex: 0 0 auto;
            background: var(--card);
            border: 2px dashed var(--border);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            padding: 40px;
        }

        .add-vehicle-btn:hover {
            border-color: var(--accent);
            background: rgba(194, 172, 31, 0.1);
        }

        .add-vehicle-btn i {
            font-size: 48px;
            color: var(--accent);
            margin-bottom: 15px;
        }

        .add-vehicle-btn span {
            font-size: 16px;
            color: rgba(255,255,255,0.7);
        }

        /* Control bar */
        .control-bar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.9);
            padding: 15px 25px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 20px;
            z-index: 100;
            border: 1px solid var(--border);
        }

        .control-bar label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        .control-bar input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--accent);
        }

        /* Vehicle selector modal */
        .selector-modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .selector-modal.active {
            display: flex;
        }

        .selector-content {
            background: #1a1a2e;
            border-radius: 20px;
            max-width: 800px;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            padding: 30px;
        }

        .selector-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .selector-item {
            background: var(--card);
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .selector-item:hover {
            border-color: var(--accent);
        }

        .selector-item img {
            width: 100%;
            aspect-ratio: 16/9;
            object-fit: cover;
        }

        .selector-item .info {
            padding: 12px;
        }

        .selector-item .name {
            font-weight: 600;
            font-size: 14px;
        }

        .selector-item .price {
            color: var(--accent);
            font-size: 13px;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <h1><i class="fas fa-balance-scale"></i> Comparador Side-by-Side</h1>
        <a href="{{ route('kiosk.index', ['event' => $eventName]) }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </header>

    <div class="compare-container" id="compareContainer">
        @foreach($vehicles as $index => $vehicle)
        <div class="vehicle-column" data-vehicle-id="{{ $vehicle->id }}" data-index="{{ $index }}">
            <!-- Spin Viewer -->
            <div class="spin-wrapper" id="spinWrapper{{ $index }}">
                <canvas id="spinCanvas{{ $index }}"></canvas>
                <div class="sync-indicator">
                    <span class="sync-dot"></span>
                    <span>Sincronizado</span>
                </div>
            </div>

            <!-- Vehicle Header -->
            <div class="vehicle-header">
                <h2 class="vehicle-name">{{ $vehicle->brand }} {{ $vehicle->model }}</h2>
                <p class="vehicle-subtitle">{{ $vehicle->year }} - {{ $vehicle->condition }}</p>
                <div class="vehicle-price">₡{{ number_format($vehicle->price) }}</div>
            </div>

            <!-- Specs -->
            <div class="specs-section">
                <h4>Especificaciones</h4>

                @if($vehicle->mileage_km)
                <div class="spec-row" data-spec="mileage" data-value="{{ $vehicle->mileage_km }}">
                    <span class="label"><i class="fas fa-tachometer-alt"></i> Kilometraje</span>
                    <span class="value">{{ number_format($vehicle->mileage_km) }} km</span>
                </div>
                @endif

                @if($vehicle->engine_cc)
                <div class="spec-row" data-spec="engine" data-value="{{ $vehicle->engine_cc }}">
                    <span class="label"><i class="fas fa-bolt"></i> Motor</span>
                    <span class="value">{{ $vehicle->engine_cc }} CC</span>
                </div>
                @endif

                @if($vehicle->fuel_type)
                <div class="spec-row">
                    <span class="label"><i class="fas fa-gas-pump"></i> Combustible</span>
                    <span class="value">{{ $vehicle->fuel_type }}</span>
                </div>
                @endif

                @if($vehicle->transmission)
                <div class="spec-row">
                    <span class="label"><i class="fas fa-cogs"></i> Transmisión</span>
                    <span class="value">{{ $vehicle->transmission }}</span>
                </div>
                @endif

                @if($vehicle->doors)
                <div class="spec-row" data-spec="doors" data-value="{{ $vehicle->doors }}">
                    <span class="label"><i class="fas fa-door-open"></i> Puertas</span>
                    <span class="value">{{ $vehicle->doors }}</span>
                </div>
                @endif

                @if($vehicle->passengers)
                <div class="spec-row" data-spec="passengers" data-value="{{ $vehicle->passengers }}">
                    <span class="label"><i class="fas fa-users"></i> Pasajeros</span>
                    <span class="value">{{ $vehicle->passengers }}</span>
                </div>
                @endif

                @if($vehicle->drivetrain)
                <div class="spec-row">
                    <span class="label"><i class="fas fa-car"></i> Tracción</span>
                    <span class="value">{{ $vehicle->drivetrain }}</span>
                </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="actions">
                <a href="{{ route('kiosk.vehicle', ['id' => $vehicle->id, 'event' => $eventName]) }}" class="action-btn btn-secondary">
                    <i class="fas fa-eye"></i> Ver detalle
                </a>
                <button class="action-btn btn-primary" onclick="selectVehicle({{ $vehicle->id }})">
                    <i class="fas fa-heart"></i> Me interesa
                </button>
            </div>
        </div>
        @endforeach

        <!-- Add more vehicles -->
        @if($vehicles->count() < 3)
        <div class="add-vehicle-btn" onclick="openVehicleSelector()">
            <i class="fas fa-plus-circle"></i>
            <span>Agregar otro vehículo</span>
        </div>
        @endif
    </div>

    <!-- Control Bar -->
    <div class="control-bar">
        <label>
            <input type="checkbox" id="syncSpins" checked onchange="toggleSync()">
            Sincronizar rotación
        </label>
        <label>
            <input type="checkbox" id="highlightDiff" checked onchange="highlightDifferences()">
            Resaltar diferencias
        </label>
    </div>

    <!-- Vehicle Selector Modal -->
    <div class="selector-modal" id="selectorModal">
        <div class="selector-content">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Selecciona un vehículo para comparar</h3>
                <button onclick="closeSelectorModal()" style="background: none; border: none; color: #fff; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
            <div class="selector-grid" id="vehicleGrid">
                <!-- Se llena dinámicamente -->
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // CONFIGURATION
        // ============================================
        const vehicles = @json($vehicles);
        const eventName = @json($eventName);
        let syncEnabled = true;
        let globalOrbitIndex = 1;
        let spinInstances = [];
        let draggingIndex = -1;

        // ============================================
        // SPIN VIEWER WITH SYNC
        // ============================================
        function initSpins() {
            vehicles.forEach((vehicle, index) => {
                const scene = vehicle.scenes?.find(s => s.spin_id && s.spin);
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

                // Load frames
                for (let i = 1; i <= totalFrames; i++) {
                    const img = new Image();
                    img.src = frameSrc(i);
                    img.onload = () => {
                        images[i] = img;
                        loaded++;
                        if (!ready && loaded >= 12) ready = true;
                    };
                }

                spinInstances[index] = {
                    draw: drawFrame,
                    totalFrames,
                    ready: () => ready
                };

                // Drag handling
                const wrapper = document.getElementById(`spinWrapper${index}`);
                let startX = 0;
                let accX = 0;

                wrapper.addEventListener('mousedown', e => {
                    draggingIndex = index;
                    startX = e.clientX;
                    accX = 0;
                });

                wrapper.addEventListener('touchstart', e => {
                    if (e.touches.length === 1) {
                        draggingIndex = index;
                        startX = e.touches[0].clientX;
                        accX = 0;
                    }
                }, { passive: true });
            });

            // Global mouse/touch move
            document.addEventListener('mousemove', e => {
                if (draggingIndex < 0) return;
                handleDrag(e.clientX);
            });

            document.addEventListener('touchmove', e => {
                if (draggingIndex < 0 || !e.touches.length) return;
                handleDrag(e.touches[0].clientX);
            }, { passive: true });

            document.addEventListener('mouseup', () => { draggingIndex = -1; });
            document.addEventListener('touchend', () => { draggingIndex = -1; });
        }

        let lastDragX = 0;
        function handleDrag(clientX) {
            const dx = clientX - lastDragX;
            lastDragX = clientX;

            const step = Math.trunc(dx / 12);
            if (step !== 0) {
                globalOrbitIndex += step;

                if (syncEnabled) {
                    // Update all spins
                    spinInstances.forEach((instance, i) => {
                        if (instance && instance.ready()) {
                            instance.draw(globalOrbitIndex);
                        }
                    });
                } else {
                    // Only update dragged spin
                    const instance = spinInstances[draggingIndex];
                    if (instance && instance.ready()) {
                        instance.draw(globalOrbitIndex);
                    }
                }
            }
        }

        // Animation loop for auto-rotate
        let autoRotate = true;
        let lastT = performance.now();

        function tick(t) {
            const dt = Math.min(0.05, (t - lastT) / 1000);
            lastT = t;

            if (autoRotate && draggingIndex < 0) {
                const framesPerSec = 72 / 8;
                globalOrbitIndex += framesPerSec * dt;

                spinInstances.forEach((instance, i) => {
                    if (instance && instance.ready()) {
                        instance.draw(globalOrbitIndex);
                    }
                });
            }

            requestAnimationFrame(tick);
        }

        // ============================================
        // SYNC TOGGLE
        // ============================================
        function toggleSync() {
            syncEnabled = document.getElementById('syncSpins').checked;
        }

        // ============================================
        // HIGHLIGHT DIFFERENCES
        // ============================================
        function highlightDifferences() {
            const highlight = document.getElementById('highlightDiff').checked;

            if (!highlight || vehicles.length < 2) {
                // Remove all highlights
                document.querySelectorAll('.spec-row .value').forEach(el => {
                    el.classList.remove('better', 'worse');
                });
                return;
            }

            // Compare numeric specs
            const specsToCompare = ['mileage', 'engine', 'doors', 'passengers'];

            specsToCompare.forEach(spec => {
                const rows = document.querySelectorAll(`[data-spec="${spec}"]`);
                if (rows.length < 2) return;

                const values = Array.from(rows).map(row => ({
                    element: row.querySelector('.value'),
                    value: parseFloat(row.dataset.value) || 0
                }));

                // For mileage, lower is better
                // For others, higher is better
                const isLowerBetter = spec === 'mileage';

                const maxVal = Math.max(...values.map(v => v.value));
                const minVal = Math.min(...values.map(v => v.value));

                if (maxVal === minVal) return; // No difference

                values.forEach(v => {
                    v.element.classList.remove('better', 'worse');
                    if (isLowerBetter) {
                        if (v.value === minVal) v.element.classList.add('better');
                        if (v.value === maxVal) v.element.classList.add('worse');
                    } else {
                        if (v.value === maxVal) v.element.classList.add('better');
                        if (v.value === minVal) v.element.classList.add('worse');
                    }
                });
            });
        }

        // ============================================
        // VEHICLE SELECTOR
        // ============================================
        async function openVehicleSelector() {
            const currentIds = vehicles.map(v => v.id);

            // Fetch all vehicles (simplified - in production would be an API call)
            // For now, redirect to kiosk with compare mode
            const modal = document.getElementById('selectorModal');
            const grid = document.getElementById('vehicleGrid');

            // Show loading
            grid.innerHTML = '<p style="text-align: center; padding: 40px;">Cargando vehículos...</p>';
            modal.classList.add('active');

            // In production, fetch from API
            // For demo, we'll just show a message
            grid.innerHTML = `
                <p style="text-align: center; padding: 40px; grid-column: 1/-1;">
                    <a href="{{ route('kiosk.index') }}" style="color: var(--accent);">
                        Ir a la galería para seleccionar más vehículos
                    </a>
                </p>
            `;
        }

        function closeSelectorModal() {
            document.getElementById('selectorModal').classList.remove('active');
        }

        function addVehicleToCompare(vehicleId) {
            const currentIds = vehicles.map(v => v.id);
            currentIds.push(vehicleId);
            window.location.href = `{{ route('kiosk.compare') }}?vehicles[]=${currentIds.join('&vehicles[]=')}`;
        }

        function selectVehicle(vehicleId) {
            // Open lead modal or similar
            alert('Función "Me interesa" - En producción abriría el modal de leads');
        }

        // ============================================
        // INIT
        // ============================================
        document.addEventListener('DOMContentLoaded', () => {
            initSpins();
            highlightDifferences();
            requestAnimationFrame(tick);
        });

        // Pause auto-rotate on interaction
        document.addEventListener('mousedown', () => { autoRotate = false; });
        document.addEventListener('touchstart', () => { autoRotate = false; });
        document.addEventListener('mouseup', () => {
            setTimeout(() => { autoRotate = true; }, 2000);
        });
        document.addEventListener('touchend', () => {
            setTimeout(() => { autoRotate = true; }, 2000);
        });
    </script>
</body>
</html>
