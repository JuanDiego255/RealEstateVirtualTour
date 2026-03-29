<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $vehicle->brand }} {{ $vehicle->model }} - Tour Virtual</title>

    <style>
        :root {
            --accent: {{ $settings->accent_color ?? '#c2ac1f' }};
            --bg: #0b0f14;
            --card: rgba(255, 255, 255, 0.08);
            --border: rgba(255, 255, 255, 0.12);
            --text: #ffffff;
            --muted: rgba(255, 255, 255, 0.6);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text);
            text-decoration: none;
            font-size: 14px;
            padding: 10px 15px;
            background: var(--card);
            border-radius: 10px;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.15);
        }

        .share-btn {
            padding: 10px 15px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Main content */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
        }

        @media (max-width: 900px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Spin Viewer */
        .spin-section {
            background: var(--card);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .spin-viewer {
            aspect-ratio: 16/10;
            background: #000;
            cursor: grab;
            position: relative;
        }

        .spin-viewer:active { cursor: grabbing; }

        .spin-viewer canvas {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Hotspots en el spin */
        .spin-hotspot {
            position: absolute;
            width: 40px;
            height: 40px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transform: translate(-50%, -50%);
            animation: hotspotPulse 2s infinite;
            z-index: 10;
            transition: all 0.2s;
        }

        .spin-hotspot:hover {
            transform: translate(-50%, -50%) scale(1.2);
        }

        .spin-hotspot i {
            color: #000;
            font-size: 16px;
        }

        @keyframes hotspotPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(194, 172, 31, 0.5); }
            50% { box-shadow: 0 0 0 15px rgba(194, 172, 31, 0); }
        }

        /* Color selector */
        .color-selector {
            display: flex;
            gap: 10px;
            padding: 15px 20px;
            background: rgba(0,0,0,0.3);
            border-top: 1px solid var(--border);
        }

        .color-option {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.2s;
        }

        .color-option.active,
        .color-option:hover {
            border-color: #fff;
            transform: scale(1.1);
        }

        /* Vehicle Info Sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-card {
            background: var(--card);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--border);
        }

        .vehicle-header {
            margin-bottom: 15px;
        }

        .vehicle-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .vehicle-subtitle {
            color: var(--muted);
            font-size: 14px;
        }

        .vehicle-price {
            font-size: 32px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 20px;
        }

        .vehicle-condition {
            display: inline-block;
            padding: 5px 12px;
            background: {{ $vehicle->condition === 'Nuevo' ? '#22c55e' : '#f59e0b' }};
            color: #000;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }

        /* Specs grid */
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
        }

        .spec-item i {
            color: var(--accent);
            width: 20px;
            text-align: center;
        }

        .spec-item .label {
            font-size: 11px;
            color: var(--muted);
            display: block;
        }

        .spec-item .value {
            font-weight: 600;
            font-size: 14px;
        }

        /* Action buttons */
        .action-btn {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s;
            margin-bottom: 10px;
        }

        .action-btn-primary {
            background: var(--accent);
            color: #000;
        }

        .action-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(194, 172, 31, 0.3);
        }

        .action-btn-secondary {
            background: var(--card);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .action-btn-secondary:hover {
            background: rgba(255,255,255,0.15);
        }

        /* QR Section */
        .qr-section {
            text-align: center;
            padding: 20px;
        }

        .qr-section img {
            width: 150px;
            height: 150px;
            background: #fff;
            padding: 10px;
            border-radius: 12px;
        }

        .qr-section p {
            margin-top: 10px;
            font-size: 12px;
            color: var(--muted);
        }

        /* Test Drive Videos */
        .video-section h4 {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .video-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .video-thumb {
            aspect-ratio: 16/9;
            background: var(--card);
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            position: relative;
        }

        .video-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-thumb .play-icon {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.4);
        }

        .video-thumb .play-icon i {
            font-size: 30px;
            color: #fff;
        }

        /* Hotspot Modal */
        .hotspot-modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .hotspot-modal.active {
            display: flex;
        }

        .hotspot-content {
            background: #1a1a2e;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }

        .hotspot-image {
            width: 100%;
            aspect-ratio: 16/9;
            object-fit: cover;
        }

        .hotspot-info {
            padding: 25px;
        }

        .hotspot-info h3 {
            font-size: 22px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hotspot-info h3 i {
            color: var(--accent);
        }

        .hotspot-info p {
            color: var(--muted);
            line-height: 1.6;
        }

        .hotspot-close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--card);
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }

        /* Video Modal */
        .video-modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .video-modal.active {
            display: flex;
        }

        .video-modal video {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 12px;
        }

        .video-modal .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--card);
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <a href="{{ route('kiosk.index', ['event' => $eventName]) }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Ver todos los vehículos
            </a>
            <button class="share-btn" onclick="shareVehicle()">
                <i class="fas fa-share-alt"></i>
                Compartir
            </button>
        </header>

        <!-- Main Content -->
        <div class="main-grid">
            <!-- Spin Viewer -->
            <div class="spin-section">
                <div class="spin-viewer" id="spinViewer">
                    <canvas id="spinCanvas"></canvas>

                    <!-- Hotspots dinámicos -->
                    @foreach($hotspots as $hotspot)
                    <div class="spin-hotspot"
                         data-frame="{{ $hotspot->frame_number }}"
                         data-frame-start="{{ $hotspot->frame_range_start }}"
                         data-frame-end="{{ $hotspot->frame_range_end }}"
                         style="left: {{ $hotspot->position_x }}%; top: {{ $hotspot->position_y }}%; display: none;"
                         onclick="showHotspot({{ $hotspot->id }})">
                        <i class="fas fa-{{ $hotspot->icon === 'engine' ? 'car-battery' : ($hotspot->icon === 'sound' ? 'volume-up' : ($hotspot->icon === 'wheel' ? 'circle' : ($hotspot->icon === 'interior' ? 'couch' : 'info'))) }}"></i>
                    </div>
                    @endforeach
                </div>

                <!-- Color Selector (AR Lite) -->
                @if($colors->count() > 0)
                <div class="color-selector">
                    <span style="color: var(--muted); font-size: 13px; margin-right: 10px;">Color:</span>
                    @foreach($colors as $color)
                    <div class="color-option {{ $color->is_default ? 'active' : '' }}"
                         style="background-color: {{ $color->color_code }};"
                         title="{{ $color->color_name }} {{ $color->additional_price > 0 ? '(+₡'.number_format($color->additional_price).')' : '' }}"
                         onclick="selectColor({{ $color->id }}, '{{ $color->spin_frames_dir }}')"
                         data-color-id="{{ $color->id }}">
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Vehicle Info -->
                <div class="info-card">
                    <div class="vehicle-header">
                        <h1 class="vehicle-title">
                            {{ $vehicle->brand }} {{ $vehicle->model }}
                            @if($vehicle->condition)
                            <span class="vehicle-condition">{{ $vehicle->condition }}</span>
                            @endif
                        </h1>
                        <p class="vehicle-subtitle">{{ $vehicle->name }} - {{ $vehicle->year }}</p>
                    </div>

                    <div class="vehicle-price">
                        ₡{{ number_format($vehicle->price) }}
                    </div>

                    <div class="specs-grid">
                        @if($vehicle->mileage_km)
                        <div class="spec-item">
                            <i class="fas fa-tachometer-alt"></i>
                            <div>
                                <span class="label">Kilometraje</span>
                                <span class="value">{{ number_format($vehicle->mileage_km) }} km</span>
                            </div>
                        </div>
                        @endif

                        @if($vehicle->fuel_type)
                        <div class="spec-item">
                            <i class="fas fa-gas-pump"></i>
                            <div>
                                <span class="label">Combustible</span>
                                <span class="value">{{ $vehicle->fuel_type }}</span>
                            </div>
                        </div>
                        @endif

                        @if($vehicle->transmission)
                        <div class="spec-item">
                            <i class="fas fa-cogs"></i>
                            <div>
                                <span class="label">Transmisión</span>
                                <span class="value">{{ $vehicle->transmission }}</span>
                            </div>
                        </div>
                        @endif

                        @if($vehicle->engine_cc)
                        <div class="spec-item">
                            <i class="fas fa-bolt"></i>
                            <div>
                                <span class="label">Motor</span>
                                <span class="value">{{ $vehicle->engine_cc }} CC</span>
                            </div>
                        </div>
                        @endif

                        @if($vehicle->doors)
                        <div class="spec-item">
                            <i class="fas fa-door-open"></i>
                            <div>
                                <span class="label">Puertas</span>
                                <span class="value">{{ $vehicle->doors }}</span>
                            </div>
                        </div>
                        @endif

                        @if($vehicle->passengers)
                        <div class="spec-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <span class="label">Pasajeros</span>
                                <span class="value">{{ $vehicle->passengers }}</span>
                            </div>
                        </div>
                        @endif

                        @if($vehicle->drivetrain)
                        <div class="spec-item">
                            <i class="fas fa-car"></i>
                            <div>
                                <span class="label">Tracción</span>
                                <span class="value">{{ $vehicle->drivetrain }}</span>
                            </div>
                        </div>
                        @endif

                        @if($vehicle->color)
                        <div class="spec-item">
                            <i class="fas fa-palette"></i>
                            <div>
                                <span class="label">Color</span>
                                <span class="value">{{ $vehicle->color }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="info-card">
                    <button class="action-btn action-btn-primary" onclick="openLeadModal()">
                        <i class="fas fa-heart"></i>
                        Me Interesa Este Vehículo
                    </button>

                    <button class="action-btn action-btn-secondary" onclick="openQuoteModal()">
                        <i class="fas fa-calculator"></i>
                        Calcular Financiamiento
                    </button>

                    <button class="action-btn action-btn-secondary" onclick="addToWishlist()">
                        <i class="fas fa-bookmark"></i>
                        Guardar en Mi Lista
                    </button>

                    <a href="{{ route('kiosk.compare', ['vehicles' => [$vehicle->id], 'event' => $eventName]) }}"
                       class="action-btn action-btn-secondary" style="text-decoration: none;">
                        <i class="fas fa-balance-scale"></i>
                        Comparar con Otro
                    </a>
                </div>

                <!-- Test Drive Videos -->
                @if($testDriveVideos->count() > 0)
                <div class="info-card">
                    <h4 class="video-section">
                        <i class="fas fa-video" style="color: var(--accent);"></i>
                        Test Drive Virtual
                    </h4>
                    <div class="video-grid">
                        @foreach($testDriveVideos as $video)
                        <div class="video-thumb" onclick="playVideo('{{ route('file', $video->video_path) }}', '{{ $video->engine_audio ? route('file', $video->engine_audio) : '' }}')">
                            @if($video->thumbnail)
                            <img src="{{ route('file', $video->thumbnail) }}" alt="{{ $video->title }}">
                            @endif
                            <div class="play-icon">
                                <i class="fas fa-play-circle"></i>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- QR Code -->
                <div class="info-card qr-section">
                    <img src="{{ route('kiosk.qr', ['vehicleId' => $vehicle->id, 'event' => $eventName]) }}" alt="QR Code">
                    <p>Escanea para ver en tu celular<br>y compartir con alguien más</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hotspot Modal -->
    <div class="hotspot-modal" id="hotspotModal">
        <button class="hotspot-close" onclick="closeHotspotModal()">&times;</button>
        <div class="hotspot-content">
            <img class="hotspot-image" id="hotspotImage" src="" alt="">
            <div class="hotspot-info">
                <h3 id="hotspotTitle"><i class="fas fa-info-circle"></i> <span></span></h3>
                <p id="hotspotDescription"></p>
            </div>
        </div>
    </div>

    <!-- Video Modal -->
    <div class="video-modal" id="videoModal">
        <button class="close-btn" onclick="closeVideoModal()">&times;</button>
        <video id="videoPlayer" controls></video>
        <audio id="engineAudio" loop></audio>
    </div>

    <!-- Lead Modal (reutilizado) -->
    @include('kiosk.partials.lead-modal')

    <!-- Quote Modal (reutilizado) -->
    @include('kiosk.partials.quote-modal', ['vehicle' => $vehicle])

    <script>
        // ============================================
        // SPIN VIEWER
        // ============================================
        const spin = @json($spin);
        const hotspots = @json($hotspots);
        const eventName = @json($eventName);
        const vehicleId = {{ $vehicle->id }};
        const vehiclePrice = {{ $vehicle->price }};

        let currentFrame = 1;
        let totalFrames = spin ? (spin.frames_count || 72) : 72;
        let framesDir = spin ? spin.frames_dir : '';
        let baseUrl = `/storage/app/public/${framesDir}/`;

        const canvas = document.getElementById('spinCanvas');
        const ctx = canvas.getContext('2d', { alpha: false });
        const images = new Array(totalFrames + 1);
        let loaded = 0;
        let ready = false;
        let orbitIndex = 1;
        let dragging = false;
        let startX = 0;
        let accX = 0;
        let autoRotate = true;
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

        function wrapFrame(n) {
            return ((n - 1) % totalFrames + totalFrames) % totalFrames + 1;
        }

        function drawFrame(idx) {
            if (!ready) return;
            resizeCanvas();
            const wrapped = wrapFrame(Math.round(idx));
            currentFrame = wrapped;
            const img = images[wrapped] || images[1];
            if (!img) return;

            const scale = Math.min(canvas.width / img.naturalWidth, canvas.height / img.naturalHeight);
            const dw = img.naturalWidth * scale;
            const dh = img.naturalHeight * scale;
            const dx = (canvas.width - dw) / 2;
            const dy = (canvas.height - dh) / 2;

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, dx, dy, dw, dh);

            // Update hotspot visibility
            updateHotspotsVisibility(wrapped);
        }

        function tick(t) {
            const dt = Math.min(0.05, (t - lastT) / 1000);
            lastT = t;

            if (ready && autoRotate && !dragging) {
                const framesPerSec = totalFrames / 8;
                autoAcc += framesPerSec * dt;
                const step = Math.floor(autoAcc);
                if (step >= 1) {
                    autoAcc -= step;
                    orbitIndex += step;
                }
            }

            if (ready) drawFrame(orbitIndex);
            requestAnimationFrame(tick);
        }

        // Load frames
        if (spin && framesDir) {
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
        }

        // Drag handling
        const viewer = document.getElementById('spinViewer');

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
                setTimeout(() => { autoRotate = true; }, 2000);
            }
        });

        // Touch
        viewer.addEventListener('touchstart', e => {
            if (e.touches.length === 1) {
                dragging = true;
                startX = e.touches[0].clientX;
                accX = 0;
                autoRotate = false;
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
        }, { passive: true });

        viewer.addEventListener('touchend', () => {
            if (dragging) {
                dragging = false;
                setTimeout(() => { autoRotate = true; }, 2000);
            }
        }, { passive: true });

        requestAnimationFrame(tick);

        // ============================================
        // HOTSPOTS
        // ============================================
        function updateHotspotsVisibility(frame) {
            document.querySelectorAll('.spin-hotspot').forEach(el => {
                const hotspotFrame = parseInt(el.dataset.frame);
                const start = parseInt(el.dataset.frameStart) || hotspotFrame - 3;
                const end = parseInt(el.dataset.frameEnd) || hotspotFrame + 3;

                const visible = frame >= start && frame <= end;
                el.style.display = visible ? 'flex' : 'none';
            });
        }

        function showHotspot(id) {
            const hotspot = hotspots.find(h => h.id === id);
            if (!hotspot) return;

            document.getElementById('hotspotTitle').querySelector('span').textContent = hotspot.title;
            document.getElementById('hotspotDescription').textContent = hotspot.description || '';

            if (hotspot.image) {
                document.getElementById('hotspotImage').src = `/file/${hotspot.image}`;
                document.getElementById('hotspotImage').style.display = 'block';
            } else {
                document.getElementById('hotspotImage').style.display = 'none';
            }

            document.getElementById('hotspotModal').classList.add('active');
            autoRotate = false;
        }

        function closeHotspotModal() {
            document.getElementById('hotspotModal').classList.remove('active');
            autoRotate = true;
        }

        // ============================================
        // COLOR SELECTOR (AR LITE)
        // ============================================
        function selectColor(colorId, newFramesDir) {
            if (!newFramesDir) return;

            // Update active state
            document.querySelectorAll('.color-option').forEach(el => {
                el.classList.toggle('active', parseInt(el.dataset.colorId) === colorId);
            });

            // Reload frames with new color
            framesDir = newFramesDir;
            baseUrl = `/storage/app/public/${framesDir}/`;
            loaded = 0;
            ready = false;

            for (let i = 1; i <= totalFrames; i++) {
                const img = new Image();
                img.src = frameSrc(i);
                img.onload = () => {
                    images[i] = img;
                    loaded++;
                    if (!ready && loaded >= 12) {
                        ready = true;
                    }
                };
            }
        }

        // ============================================
        // TEST DRIVE VIDEO
        // ============================================
        function playVideo(videoUrl, audioUrl) {
            const modal = document.getElementById('videoModal');
            const video = document.getElementById('videoPlayer');
            const audio = document.getElementById('engineAudio');

            video.src = videoUrl;
            if (audioUrl) {
                audio.src = audioUrl;
                video.addEventListener('play', () => audio.play());
                video.addEventListener('pause', () => audio.pause());
            }

            modal.classList.add('active');
            video.play();
            autoRotate = false;
        }

        function closeVideoModal() {
            const modal = document.getElementById('videoModal');
            const video = document.getElementById('videoPlayer');
            const audio = document.getElementById('engineAudio');

            video.pause();
            audio.pause();
            modal.classList.remove('active');
            autoRotate = true;
        }

        // ============================================
        // SHARE
        // ============================================
        function shareVehicle() {
            const url = window.location.href;
            if (navigator.share) {
                navigator.share({
                    title: '{{ $vehicle->brand }} {{ $vehicle->model }}',
                    text: 'Mira este {{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->year }}',
                    url: url
                });
            } else {
                navigator.clipboard.writeText(url);
                alert('Link copiado al portapapeles!');
            }
        }

        // Close modals on outside click
        document.getElementById('hotspotModal').addEventListener('click', e => {
            if (e.target.id === 'hotspotModal') closeHotspotModal();
        });

        document.getElementById('videoModal').addEventListener('click', e => {
            if (e.target.id === 'videoModal') closeVideoModal();
        });

        // Keyboard
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeHotspotModal();
                closeVideoModal();
            }
        });
    </script>

    <script>
        // Lead & Quote functions (shared)
        function openLeadModal() {
            document.getElementById('leadModal').classList.add('active');
            autoRotate = false;
        }

        function openQuoteModal() {
            document.getElementById('quoteModal').classList.add('active');
            autoRotate = false;
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            autoRotate = true;
        }

        function addToWishlist() {
            const token = localStorage.getItem('wishlistToken');
            fetch('{{ route("kiosk.wishlist.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    token: token,
                    add_vehicle: vehicleId,
                    event_name: eventName
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    localStorage.setItem('wishlistToken', data.wishlist.share_token);
                    alert('Agregado a tu lista!\nAccede en: ' + data.share_url);
                }
            });
        }
    </script>
</body>
</html>
