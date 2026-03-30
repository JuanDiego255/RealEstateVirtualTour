{{-- Test Drive Inmersivo - Experiencia de Primera Persona --}}

<style>
/* ============================================
   TEST DRIVE INMERSIVO - ESTILOS
   ============================================ */

.test-drive-launcher {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border-radius: 16px;
    padding: 20px;
    border: 1px solid rgba(255,255,255,0.1);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.test-drive-launcher:hover {
    transform: translateY(-2px);
    border-color: var(--accent);
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

.test-drive-launcher::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    transition: left 0.5s;
}

.test-drive-launcher:hover::before {
    left: 100%;
}

.test-drive-launcher-content {
    display: flex;
    align-items: center;
    gap: 15px;
}

.test-drive-icon {
    width: 60px;
    height: 60px;
    background: var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #000;
    animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
    0%, 100% { box-shadow: 0 0 20px rgba(194, 172, 31, 0.4); }
    50% { box-shadow: 0 0 40px rgba(194, 172, 31, 0.8); }
}

.test-drive-text h4 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 4px;
}

.test-drive-text p {
    font-size: 13px;
    color: var(--muted);
    margin: 0;
}

/* ============================================
   MODAL INMERSIVO FULLSCREEN
   ============================================ */

.immersive-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #000;
    z-index: 10000;
    display: none;
    flex-direction: column;
}

.immersive-modal.active {
    display: flex;
}

/* Video de fondo */
.immersive-video-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.immersive-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.8);
}

/* Overlay con controles */
.immersive-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 30px;
    background: linear-gradient(180deg, rgba(0,0,0,0.3) 0%, transparent 30%, transparent 70%, rgba(0,0,0,0.5) 100%);
}

/* Header del modal */
.immersive-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.immersive-close {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 50%;
    color: #fff;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s;
}

.immersive-close:hover {
    background: rgba(255,255,255,0.2);
    transform: scale(1.1);
}

.immersive-title {
    text-align: right;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
}

.immersive-title h2 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 5px;
}

.immersive-title p {
    font-size: 14px;
    color: rgba(255,255,255,0.7);
}

/* Dashboard de controles */
.immersive-dashboard {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 30px;
}

/* Tacometro */
.tachometer {
    width: 200px;
    height: 200px;
    position: relative;
}

.tachometer-bg {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(20px);
    border: 3px solid rgba(255,255,255,0.2);
    position: relative;
    overflow: hidden;
}

.tachometer-inner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.rpm-value {
    font-size: 48px;
    font-weight: 800;
    font-family: 'Courier New', monospace;
    color: #fff;
    line-height: 1;
}

.rpm-label {
    font-size: 14px;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 2px;
}

.tachometer-needle {
    position: absolute;
    bottom: 50%;
    left: 50%;
    width: 4px;
    height: 70px;
    background: linear-gradient(to top, #ff3333, #ff6666);
    transform-origin: bottom center;
    transform: translateX(-50%) rotate(-120deg);
    transition: transform 0.1s ease-out;
    border-radius: 2px;
    box-shadow: 0 0 10px rgba(255,50,50,0.5);
}

.tachometer-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    background: #333;
    border-radius: 50%;
    border: 2px solid #666;
}

.tachometer-marks {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.tach-mark {
    position: absolute;
    top: 10px;
    left: 50%;
    width: 2px;
    height: 15px;
    background: rgba(255,255,255,0.5);
    transform-origin: bottom center;
}

.tach-mark.major {
    height: 20px;
    width: 3px;
    background: #fff;
}

.tach-mark.redline {
    background: #ff3333;
}

/* Controles centrales */
.controls-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

/* Boton de encendido */
.ignition-btn {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
    border: 4px solid #333;
    cursor: pointer;
    position: relative;
    transition: all 0.3s;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5), inset 0 -5px 20px rgba(0,0,0,0.5);
}

.ignition-btn::before {
    content: '';
    position: absolute;
    top: 10px;
    left: 10px;
    right: 10px;
    bottom: 10px;
    border-radius: 50%;
    background: linear-gradient(145deg, #333, #222);
    display: flex;
    align-items: center;
    justify-content: center;
}

.ignition-btn-inner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 1;
}

.ignition-btn i {
    font-size: 32px;
    color: #666;
    transition: all 0.3s;
}

.ignition-btn span {
    display: block;
    font-size: 10px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 5px;
}

.ignition-btn:hover {
    border-color: #444;
}

.ignition-btn.ready {
    border-color: #ff6600;
    animation: ignition-pulse 1.5s infinite;
}

.ignition-btn.ready i,
.ignition-btn.ready span {
    color: #ff6600;
}

@keyframes ignition-pulse {
    0%, 100% { box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 20px rgba(255,102,0,0.3); }
    50% { box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 40px rgba(255,102,0,0.6); }
}

.ignition-btn.running {
    border-color: #00ff88;
}

.ignition-btn.running i,
.ignition-btn.running span {
    color: #00ff88;
}

/* Indicadores de estado */
.status-indicators {
    display: flex;
    gap: 15px;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    font-size: 12px;
    color: rgba(255,255,255,0.7);
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #666;
}

.status-dot.active {
    background: #00ff88;
    box-shadow: 0 0 10px #00ff88;
}

/* Pedal de aceleracion */
.accelerator-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.accelerator-container {
    width: 80px;
    height: 200px;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(20px);
    border-radius: 40px;
    border: 2px solid rgba(255,255,255,0.2);
    position: relative;
    overflow: hidden;
}

.accelerator-track {
    position: absolute;
    bottom: 10px;
    left: 10px;
    right: 10px;
    top: 10px;
    background: linear-gradient(to top, #1a1a1a, #2a2a2a);
    border-radius: 30px;
    overflow: hidden;
}

.accelerator-fill {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 0%;
    background: linear-gradient(to top, #00ff88, #00cc66);
    transition: height 0.05s linear;
    border-radius: 30px;
}

.accelerator-fill.high {
    background: linear-gradient(to top, #ff6600, #ff3300);
}

.accelerator-handle {
    position: absolute;
    bottom: 5%;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 30px;
    background: linear-gradient(145deg, #555, #333);
    border-radius: 15px;
    border: 2px solid #666;
    cursor: grab;
    transition: bottom 0.05s linear;
}

.accelerator-handle:active {
    cursor: grabbing;
}

.accelerator-label {
    font-size: 12px;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Velocimetro digital */
.speedometer {
    text-align: center;
    padding: 15px 30px;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(20px);
    border-radius: 15px;
    border: 1px solid rgba(255,255,255,0.1);
}

.speed-value {
    font-size: 64px;
    font-weight: 800;
    font-family: 'Courier New', monospace;
    color: #fff;
    line-height: 1;
}

.speed-unit {
    font-size: 18px;
    color: rgba(255,255,255,0.5);
}

/* Instrucciones */
.immersive-instructions {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    opacity: 1;
    transition: opacity 0.5s;
    pointer-events: none;
}

.immersive-instructions.hidden {
    opacity: 0;
}

.instruction-text {
    font-size: 24px;
    color: #fff;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
    margin-bottom: 10px;
}

.instruction-sub {
    font-size: 14px;
    color: rgba(255,255,255,0.6);
}

/* Efectos de vibracion */
@keyframes vibrate {
    0%, 100% { transform: translate(0, 0); }
    25% { transform: translate(-1px, 1px); }
    50% { transform: translate(1px, -1px); }
    75% { transform: translate(-1px, -1px); }
}

.vibrating {
    animation: vibrate 0.1s infinite;
}

/* Responsive */
@media (max-width: 768px) {
    .immersive-overlay {
        padding: 15px;
    }

    .tachometer {
        width: 140px;
        height: 140px;
    }

    .rpm-value {
        font-size: 32px;
    }

    .ignition-btn {
        width: 90px;
        height: 90px;
    }

    .ignition-btn i {
        font-size: 24px;
    }

    .accelerator-container {
        width: 60px;
        height: 150px;
    }

    .speed-value {
        font-size: 48px;
    }

    .immersive-title h2 {
        font-size: 20px;
    }
}

/* Sin video - modo estatico */
.immersive-static-bg {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.6);
}

.no-video-message {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 5;
}

.no-video-message i {
    font-size: 60px;
    color: var(--accent);
    margin-bottom: 15px;
    display: block;
}

.no-video-message p {
    font-size: 18px;
    color: rgba(255,255,255,0.8);
}
</style>

{{-- Boton lanzador del Test Drive --}}
<div class="info-card">
    <div class="test-drive-launcher" onclick="launchTestDrive()">
        <div class="test-drive-launcher-content">
            <div class="test-drive-icon">
                <i class="fas fa-steering-wheel"></i>
            </div>
            <div class="test-drive-text">
                <h4>Test Drive Virtual</h4>
                <p>Experiencia inmersiva - Enciende el motor y siente la potencia</p>
            </div>
        </div>
    </div>
</div>

{{-- Modal Inmersivo --}}
<div class="immersive-modal" id="immersiveTestDrive">
    <div class="immersive-video-container">
        @php
            // Prioridad: 1) Video POV del vehiculo, 2) Primer video de test drive, 3) Imagen
            $povVideo = $vehicle->interior_pov_video;
            $mainVideo = isset($testDriveVideos) && $testDriveVideos->count() > 0 ? $testDriveVideos->first() : null;
            $hasVideo = $povVideo || $mainVideo;
        @endphp

        @if($povVideo)
            <video class="immersive-video" id="immersiveVideo" loop muted playsinline>
                <source src="{{ route('file', $povVideo) }}" type="video/mp4">
            </video>
        @elseif($mainVideo)
            <video class="immersive-video" id="immersiveVideo" loop muted playsinline>
                <source src="{{ route('file', $mainVideo->video_path) }}" type="video/mp4">
            </video>
        @else
            {{-- Imagen de fondo si no hay video --}}
            @if($vehicle->featured_image)
                <img src="{{ route('file', $vehicle->featured_image) }}" class="immersive-static-bg" alt="Interior">
            @elseif($vehicle->image)
                <img src="{{ route('file', $vehicle->image) }}" class="immersive-static-bg" alt="Interior">
            @else
                <div class="immersive-static-bg" style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f0f23 100%);display:flex;align-items:center;justify-content:center;">
                    <div style="text-align:center;opacity:0.3;">
                        <i class="fas fa-car" style="font-size:120px;color:#fff;"></i>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <div class="immersive-overlay">
        {{-- Header --}}
        <div class="immersive-header">
            <button class="immersive-close" onclick="closeTestDrive()">
                <i class="fas fa-times"></i>
            </button>
            <div class="immersive-title">
                <h2>{{ $vehicle->brand }} {{ $vehicle->model }}</h2>
                <p>{{ $vehicle->year }} &bull; Test Drive Virtual</p>
            </div>
        </div>

        {{-- Instrucciones iniciales --}}
        <div class="immersive-instructions" id="testDriveInstructions">
            <div class="instruction-text">
                <i class="fas fa-hand-pointer"></i> Presiona el boton para encender
            </div>
            <div class="instruction-sub">Despues arrastra el acelerador para sentir el motor</div>
        </div>

        {{-- Dashboard --}}
        <div class="immersive-dashboard">
            {{-- Tacometro --}}
            <div class="tachometer">
                <div class="tachometer-bg">
                    <div class="tachometer-marks" id="tachMarks"></div>
                    <div class="tachometer-needle" id="tachNeedle"></div>
                    <div class="tachometer-center"></div>
                    <div class="tachometer-inner">
                        <div class="rpm-value" id="rpmDisplay">0</div>
                        <div class="rpm-label">RPM x1000</div>
                    </div>
                </div>
            </div>

            {{-- Controles centrales --}}
            <div class="controls-center">
                <div class="speedometer">
                    <div class="speed-value" id="speedDisplay">0</div>
                    <div class="speed-unit">KM/H</div>
                </div>

                {{-- Boton de encendido --}}
                <div class="ignition-btn" id="ignitionBtn" onclick="toggleIgnition()">
                    <div class="ignition-btn-inner">
                        <i class="fas fa-power-off"></i>
                        <span>Engine</span>
                    </div>
                </div>

                {{-- Indicadores --}}
                <div class="status-indicators">
                    <div class="status-indicator">
                        <div class="status-dot" id="engineLight"></div>
                        <span>Motor</span>
                    </div>
                    <div class="status-indicator">
                        <div class="status-dot" id="fuelLight"></div>
                        <span>Combustible</span>
                    </div>
                </div>
            </div>

            {{-- Acelerador --}}
            <div class="accelerator-section">
                <div class="accelerator-container" id="acceleratorContainer">
                    <div class="accelerator-track">
                        <div class="accelerator-fill" id="acceleratorFill"></div>
                    </div>
                    <div class="accelerator-handle" id="acceleratorHandle"></div>
                </div>
                <div class="accelerator-label">Acelerador</div>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// TEST DRIVE INMERSIVO - MOTOR DE AUDIO
// ============================================

class ImmersiveTestDrive {
    constructor() {
        this.isRunning = false;
        this.rpm = 0;
        this.targetRpm = 0;
        this.speed = 0;
        this.throttle = 0;
        this.audioContext = null;
        this.engineSound = null;
        this.gainNode = null;
        this.isAudioInitialized = false;

        // Configuracion del motor
        this.config = {
            idleRpm: 800,
            maxRpm: 7000,
            redlineRpm: 6500,
            rpmAcceleration: 3000, // RPM por segundo
            rpmDeceleration: 2000,
            speedPerRpm: 0.03, // km/h por RPM
        };

        // URLs de audio (se pueden personalizar por vehiculo)
        this.audioUrls = {
            startup: '{{ $vehicle->engine_startup_audio ? route("file", $vehicle->engine_startup_audio) : "" }}',
            idle: '{{ $vehicle->engine_idle_audio ? route("file", $vehicle->engine_idle_audio) : "" }}',
            rev: '{{ $vehicle->engine_rev_audio ? route("file", $vehicle->engine_rev_audio) : "" }}',
        };

        // Audio buffers
        this.audioBuffers = {};
        this.currentSource = null;

        this.init();
    }

    init() {
        this.createTachMarks();
        this.setupAccelerator();
        this.startRenderLoop();
    }

    createTachMarks() {
        const container = document.getElementById('tachMarks');
        if (!container) return;

        // Crear marcas del tacometro (0-8 en escala de 1000)
        for (let i = 0; i <= 8; i++) {
            const mark = document.createElement('div');
            mark.className = 'tach-mark' + (i % 2 === 0 ? ' major' : '') + (i >= 6 ? ' redline' : '');
            // Angulo: -120 a 120 grados (240 grados total)
            const angle = -120 + (i / 8) * 240;
            mark.style.transform = `rotate(${angle}deg) translateX(-50%)`;
            container.appendChild(mark);
        }
    }

    async initAudio() {
        if (this.isAudioInitialized) return;

        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.gainNode = this.audioContext.createGain();
            this.gainNode.connect(this.audioContext.destination);
            this.gainNode.gain.value = 0.7;

            // Pre-cargar sonidos si estan disponibles
            if (this.audioUrls.idle) {
                await this.loadAudio('idle', this.audioUrls.idle);
            }
            if (this.audioUrls.startup) {
                await this.loadAudio('startup', this.audioUrls.startup);
            }

            this.isAudioInitialized = true;
        } catch (e) {
            console.warn('Audio no disponible:', e);
        }
    }

    async loadAudio(name, url) {
        try {
            const response = await fetch(url);
            const arrayBuffer = await response.arrayBuffer();
            this.audioBuffers[name] = await this.audioContext.decodeAudioData(arrayBuffer);
        } catch (e) {
            console.warn(`No se pudo cargar audio ${name}:`, e);
        }
    }

    playEngineSound() {
        if (!this.audioContext || !this.audioBuffers.idle) {
            // Fallback: usar oscillator para simular motor
            this.playOscillatorEngine();
            return;
        }

        // Reproducir audio real del motor
        if (this.currentSource) {
            this.currentSource.stop();
        }

        this.currentSource = this.audioContext.createBufferSource();
        this.currentSource.buffer = this.audioBuffers.idle;
        this.currentSource.loop = true;
        this.currentSource.connect(this.gainNode);
        this.currentSource.start();
    }

    playOscillatorEngine() {
        // Simular sonido de motor con osciladores
        if (!this.audioContext) {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.gainNode = this.audioContext.createGain();
            this.gainNode.connect(this.audioContext.destination);
        }

        // Crear multiples osciladores para un sonido mas rico
        this.oscillators = [];

        const frequencies = [80, 160, 240]; // Harmonicos de un motor
        frequencies.forEach((freq, i) => {
            const osc = this.audioContext.createOscillator();
            const oscGain = this.audioContext.createGain();

            osc.type = 'sawtooth';
            osc.frequency.value = freq;
            oscGain.gain.value = 0.15 / (i + 1); // Harmonicos mas suaves

            osc.connect(oscGain);
            oscGain.connect(this.gainNode);
            osc.start();

            this.oscillators.push({ osc, gain: oscGain, baseFreq: freq });
        });
    }

    updateEngineSound() {
        if (!this.isRunning) return;

        // Calcular factor de pitch basado en RPM
        const rpmFactor = this.rpm / this.config.idleRpm;

        // Actualizar oscilladores
        if (this.oscillators) {
            this.oscillators.forEach(({ osc, gain, baseFreq }) => {
                osc.frequency.value = baseFreq * rpmFactor;
                // Aumentar volumen con RPM
                gain.gain.value = (0.1 + (this.rpm / this.config.maxRpm) * 0.2) / this.oscillators.length;
            });
        }

        // Actualizar playbackRate si usamos buffer
        if (this.currentSource && this.currentSource.playbackRate) {
            this.currentSource.playbackRate.value = 0.5 + (this.rpm / this.config.maxRpm) * 1.5;
        }
    }

    stopEngineSound() {
        if (this.oscillators) {
            this.oscillators.forEach(({ osc }) => {
                try { osc.stop(); } catch(e) {}
            });
            this.oscillators = null;
        }

        if (this.currentSource) {
            try { this.currentSource.stop(); } catch(e) {}
            this.currentSource = null;
        }
    }

    setupAccelerator() {
        const container = document.getElementById('acceleratorContainer');
        const handle = document.getElementById('acceleratorHandle');
        if (!container || !handle) return;

        let isDragging = false;
        let startY = 0;
        let startThrottle = 0;

        const updateThrottle = (clientY) => {
            const rect = container.getBoundingClientRect();
            const relativeY = clientY - rect.top;
            const percentage = 1 - Math.max(0, Math.min(1, relativeY / rect.height));
            this.setThrottle(percentage);
        };

        // Mouse events
        handle.addEventListener('mousedown', (e) => {
            isDragging = true;
            startY = e.clientY;
            startThrottle = this.throttle;
            e.preventDefault();
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            updateThrottle(e.clientY);
        });

        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                // Soltar acelerador gradualmente
                this.releaseThrottle();
            }
        });

        // Touch events
        handle.addEventListener('touchstart', (e) => {
            isDragging = true;
            startY = e.touches[0].clientY;
            startThrottle = this.throttle;
            e.preventDefault();
        }, { passive: false });

        container.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            updateThrottle(e.touches[0].clientY);
            e.preventDefault();
        }, { passive: false });

        container.addEventListener('touchend', () => {
            if (isDragging) {
                isDragging = false;
                this.releaseThrottle();
            }
        });

        // Click directo en el track
        container.addEventListener('click', (e) => {
            if (e.target === handle) return;
            updateThrottle(e.clientY);
            setTimeout(() => this.releaseThrottle(), 500);
        });
    }

    setThrottle(value) {
        if (!this.isRunning) return;

        this.throttle = Math.max(0, Math.min(1, value));
        this.targetRpm = this.config.idleRpm + (this.config.maxRpm - this.config.idleRpm) * this.throttle;

        // Actualizar visual del acelerador
        const fill = document.getElementById('acceleratorFill');
        const handle = document.getElementById('acceleratorHandle');

        if (fill) {
            fill.style.height = (this.throttle * 100) + '%';
            fill.classList.toggle('high', this.throttle > 0.7);
        }

        if (handle) {
            handle.style.bottom = (5 + this.throttle * 85) + '%';
        }

        // Vibracion haptica en moviles
        if (this.throttle > 0.5 && navigator.vibrate) {
            navigator.vibrate(10);
        }
    }

    releaseThrottle() {
        // Animacion de soltar acelerador
        const release = () => {
            if (this.throttle > 0) {
                this.setThrottle(this.throttle - 0.05);
                requestAnimationFrame(release);
            } else {
                this.throttle = 0;
                this.targetRpm = this.config.idleRpm;
            }
        };
        release();
    }

    async toggleIgnition() {
        const btn = document.getElementById('ignitionBtn');
        const instructions = document.getElementById('testDriveInstructions');
        const engineLight = document.getElementById('engineLight');
        const fuelLight = document.getElementById('fuelLight');
        const video = document.getElementById('immersiveVideo');

        if (!this.isRunning) {
            // Encender motor
            await this.initAudio();

            btn.classList.remove('ready');
            btn.classList.add('running');

            // Ocultar instrucciones
            if (instructions) instructions.classList.add('hidden');

            // Activar luces
            if (engineLight) engineLight.classList.add('active');
            if (fuelLight) fuelLight.classList.add('active');

            // Reproducir video
            if (video) {
                video.muted = true;
                video.play().catch(() => {});
            }

            // Sonido de arranque
            this.isRunning = true;
            this.targetRpm = this.config.idleRpm;

            // Simular arranque
            this.rpm = 200;
            setTimeout(() => {
                this.playEngineSound();
            }, 300);

            // Vibracion de arranque
            if (navigator.vibrate) {
                navigator.vibrate([100, 50, 100, 50, 200]);
            }

        } else {
            // Apagar motor
            this.isRunning = false;
            this.targetRpm = 0;
            this.throttle = 0;

            btn.classList.remove('running');
            btn.classList.add('ready');

            if (engineLight) engineLight.classList.remove('active');

            // Parar video
            if (video) {
                video.pause();
            }

            // Parar sonido
            setTimeout(() => {
                this.stopEngineSound();
            }, 500);
        }
    }

    startRenderLoop() {
        let lastTime = performance.now();

        const render = (time) => {
            const dt = (time - lastTime) / 1000;
            lastTime = time;

            // Actualizar RPM
            if (this.isRunning) {
                const rpmDiff = this.targetRpm - this.rpm;
                const acceleration = rpmDiff > 0 ? this.config.rpmAcceleration : this.config.rpmDeceleration;
                const maxChange = acceleration * dt;

                if (Math.abs(rpmDiff) < maxChange) {
                    this.rpm = this.targetRpm;
                } else {
                    this.rpm += Math.sign(rpmDiff) * maxChange;
                }

                // Calcular velocidad simulada
                this.speed = Math.max(0, (this.rpm - this.config.idleRpm) * this.config.speedPerRpm);

                // Actualizar sonido
                this.updateEngineSound();
            } else {
                // Motor apagado - RPM baja gradualmente
                this.rpm = Math.max(0, this.rpm - this.config.rpmDeceleration * dt);
                this.speed = Math.max(0, this.speed - 50 * dt);
            }

            // Actualizar UI
            this.updateDisplay();

            requestAnimationFrame(render);
        };

        requestAnimationFrame(render);
    }

    updateDisplay() {
        // RPM
        const rpmDisplay = document.getElementById('rpmDisplay');
        if (rpmDisplay) {
            rpmDisplay.textContent = Math.round(this.rpm / 100) / 10;
        }

        // Velocidad
        const speedDisplay = document.getElementById('speedDisplay');
        if (speedDisplay) {
            speedDisplay.textContent = Math.round(this.speed);
        }

        // Aguja del tacometro
        const needle = document.getElementById('tachNeedle');
        if (needle) {
            // -120 a 120 grados para 0-8000 RPM
            const angle = -120 + (this.rpm / 8000) * 240;
            needle.style.transform = `translateX(-50%) rotate(${angle}deg)`;

            // Color rojo en redline
            if (this.rpm >= this.config.redlineRpm) {
                needle.style.background = 'linear-gradient(to top, #ff0000, #ff3333)';
                needle.style.boxShadow = '0 0 15px rgba(255,0,0,0.8)';
            } else {
                needle.style.background = 'linear-gradient(to top, #ff3333, #ff6666)';
                needle.style.boxShadow = '0 0 10px rgba(255,50,50,0.5)';
            }
        }

        // Vibracion al estar en marcha
        const modal = document.getElementById('immersiveTestDrive');
        if (modal && this.isRunning && this.rpm > 3000) {
            modal.classList.add('vibrating');
        } else if (modal) {
            modal.classList.remove('vibrating');
        }
    }
}

// Instancia global
let testDriveInstance = null;

function launchTestDrive() {
    const modal = document.getElementById('immersiveTestDrive');
    if (modal) {
        modal.classList.add('active');

        // Inicializar si no existe
        if (!testDriveInstance) {
            testDriveInstance = new ImmersiveTestDrive();
        }

        // Marcar boton como listo
        const btn = document.getElementById('ignitionBtn');
        if (btn) btn.classList.add('ready');

        // Fullscreen en moviles
        if (document.documentElement.requestFullscreen && window.innerWidth < 768) {
            document.documentElement.requestFullscreen().catch(() => {});
        }
    }
}

function closeTestDrive() {
    const modal = document.getElementById('immersiveTestDrive');
    const video = document.getElementById('immersiveVideo');

    if (modal) {
        modal.classList.remove('active');
    }

    // Apagar motor si esta encendido
    if (testDriveInstance && testDriveInstance.isRunning) {
        testDriveInstance.toggleIgnition();
    }

    // Salir de fullscreen
    if (document.exitFullscreen && document.fullscreenElement) {
        document.exitFullscreen().catch(() => {});
    }
}

function toggleIgnition() {
    if (testDriveInstance) {
        testDriveInstance.toggleIgnition();
    }
}

// Cerrar con ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeTestDrive();
    }
});
</script>
