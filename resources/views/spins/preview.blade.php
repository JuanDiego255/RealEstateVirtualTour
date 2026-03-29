<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spin Preview</title>

    <style>
        :root {
            --bg: #0b0f14;
            --card: rgba(255, 255, 255, 0.06);
            --text: rgba(255, 255, 255, 0.92);
            --muted: rgba(255, 255, 255, 0.65);
            --ring: rgba(255, 255, 255, 0.25);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background:
                radial-gradient(1200px 600px at 50% -10%, rgba(120, 160, 255, 0.18), transparent 55%),
                radial-gradient(900px 500px at 10% 10%, rgba(255, 140, 80, 0.10), transparent 55%),
                radial-gradient(800px 450px at 90% 20%, rgba(80, 220, 180, 0.08), transparent 55%),
                var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            padding: 28px 16px 40px;
        }

        .wrap {
            width: min(980px, 100%);
        }

        .header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        h2 {
            margin: 0;
            font-size: 18px;
            letter-spacing: 0.2px;
        }

        .sub {
            margin-top: 6px;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.35;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 999px;
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, 0.10);
            color: var(--muted);
            font-size: 12px;
            user-select: none;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: rgba(255, 220, 120, 0.9);
            box-shadow: 0 0 14px rgba(255, 220, 120, 0.45);
        }

        .panel {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.04));
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 18px;
            padding: 14px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
        }

        .viewer {
            position: relative;
            width: 100%;
            aspect-ratio: 16/9;
            border-radius: 14px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.35);
            cursor: grab;
            border: 1px solid rgba(255, 255, 255, 0.10);
            outline: none;
            touch-action: pan-y;
        }

        .viewer:focus {
            box-shadow: 0 0 0 3px var(--ring);
        }

        .viewer.dragging {
            cursor: grabbing;
        }

        canvas {
            width: 100%;
            height: 100%;
            display: block;
        }

        .hud {
            position: absolute;
            left: 10px;
            right: 10px;
            top: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            pointer-events: none;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(0, 0, 0, 0.40);
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: rgba(255, 255, 255, 0.82);
            font-size: 12px;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .kbd {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Courier New", monospace;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.78);
        }

        .controls {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            margin-top: 12px;
        }

        .leftControls,
        .rightControls {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .btn {
            appearance: none;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.88);
            padding: 9px 12px;
            border-radius: 12px;
            font-size: 13px;
            cursor: pointer;
            transition: transform 120ms ease, background 120ms ease, border-color 120ms ease;
            user-select: none;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.10);
            border-color: rgba(255, 255, 255, 0.22);
            transform: translateY(-1px);
        }

        .btn.primary {
            background: rgba(120, 160, 255, 0.20);
            border-color: rgba(120, 160, 255, 0.35);
        }

        .toggle,
        .rangeWrap {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: rgba(255, 255, 255, 0.88);
            user-select: none;
        }

        .toggle input {
            width: 16px;
            height: 16px;
            accent-color: #84a6ff;
        }

        .rangeWrap label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.80);
            user-select: none;
        }

        input[type="range"] {
            width: 170px;
        }

        .progressWrap {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--muted);
            font-size: 12px;
        }

        .bar {
            flex: 1;
            height: 8px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.10);
        }

        .bar>div {
            height: 100%;
            width: 0%;
            background: rgba(120, 160, 255, 0.55);
            transition: width 120ms ease;
        }

        .hint {
            margin-top: 10px;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.45;
            text-align: left;
        }

        @media (max-width: 720px) {
            .viewer {
                aspect-ratio: 4/3;
            }

            input[type="range"] {
                width: 120px;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">

        <div class="header">
            <div>
                <h2>Spin Preview ({{ $spin->frames_count }} frames)</h2>
                <div class="sub">
                    Auto-rotate inicia al cargar. Arrastra para girar; al soltar reanuda (si está activo).
                    Precarga rápida para iniciar en 5–10s aprox y luego completa en background.
                </div>
            </div>
            <div class="pill">
                <span class="dot" id="statusDot"></span>
                <span id="statusText">Cargando…</span>
            </div>
        </div>

        <div class="panel">
            <div class="viewer" id="viewer" tabindex="0" aria-label="Spin viewer">
                <div class="hud">
                    <div class="badge">
                        Frame <span class="kbd" id="frameText">1</span> /
                        <span class="kbd" id="totalText">{{ $spin->frames_count }}</span>
                    </div>
                    <div class="badge">
                        <span class="kbd">Drag</span>
                        <span class="kbd">Espacio</span> pause
                        <span class="kbd">Click</span> invierte sentido
                    </div>
                </div>

                <canvas id="spinCanvas"></canvas>
            </div>

            <div class="progressWrap">
                <span id="progressLabel">Precargando frames…</span>
                <div class="bar">
                    <div id="progressBar"></div>
                </div>
                <span class="kbd" id="progressPct">0%</span>
            </div>

            <div class="controls">
                <div class="leftControls">
                    <button class="btn primary" id="btnReset" type="button">Reset</button>
                    <button class="btn" id="btnPlayPause" type="button">Pause</button>

                    <label class="toggle" title="Reanuda auto-rotate después de soltar">
                        <input type="checkbox" id="resumeToggle" checked>
                        <span>Reanudar</span>
                    </label>

                    <label class="toggle" title="Suaviza SOLO la unión último→primero">
                        <input type="checkbox" id="seamBlendToggle" checked>
                        <span>Seam-blend</span>
                    </label>
                </div>

                <div class="rightControls">
                    <div class="rangeWrap" title="Segundos por vuelta (mientras más alto, más lento)">
                        <label for="revRange">Seg/vuelta</label>
                        <input type="range" id="revRange" min="2" max="20" step="0.5" value="6">
                        <span class="kbd" id="revText">6.0</span>
                    </div>

                    <div class="rangeWrap" title="Sensibilidad drag (px por frame)">
                        <label for="sensRange">Sens</label>
                        <input type="range" id="sensRange" min="6" max="26" step="1" value="12">
                        <span class="kbd" id="sensText">12px</span>
                    </div>

                    <div class="rangeWrap" title="Cuántos frames se mezclan en la unión último→primero">
                        <label for="seamRange">Seam</label>
                        <input type="range" id="seamRange" min="2" max="14" step="1" value="8">
                        <span class="kbd" id="seamText">8</span>
                    </div>
                </div>
            </div>

            <div class="hint">
                72 frames a 640px WebP q70. Precarga 12 frames para arranque inmediato, el resto carga en background con 16 workers paralelos.
            </div>
        </div>

    </div>

    <script>
        // ==========================
        // Config
        // ==========================
        const totalFrames = Number({{ $spin->frames_count }}) || 1;
        const framesDir = "{{ $spin->frames_dir }}";
        const baseUrl = "/storage/" + framesDir + "/";

        // Si cambias a .webp, solo cambia aquí:
        const ext = "webp"; // "webp" o "jpg"

        function frameSrc(n) {
            const num = String(n).padStart(3, '0');
            return baseUrl + "frame-" + num + "." + ext;
        }

        // ==========================
        // DOM
        // ==========================
        const viewer = document.getElementById('viewer');
        const canvas = document.getElementById('spinCanvas');
        const ctx = canvas.getContext('2d', { alpha: false });

        const statusText = document.getElementById('statusText');
        const statusDot = document.getElementById('statusDot');

        const frameText = document.getElementById('frameText');
        document.getElementById('totalText').textContent = totalFrames;

        const btnReset = document.getElementById('btnReset');
        const btnPlayPause = document.getElementById('btnPlayPause');

        const resumeToggle = document.getElementById('resumeToggle');
        const seamBlendToggle = document.getElementById('seamBlendToggle');

        const revRange = document.getElementById('revRange');
        const revText = document.getElementById('revText');

        const sensRange = document.getElementById('sensRange');
        const sensText = document.getElementById('sensText');

        const seamRange = document.getElementById('seamRange');
        const seamText = document.getElementById('seamText');

        const progressLabel = document.getElementById('progressLabel');
        const progressBar = document.getElementById('progressBar');
        const progressPct = document.getElementById('progressPct');

        // ==========================
        // Performance preload strategy (5–10s perceived)
        // ==========================
        const MIN_READY = Math.min(12, totalFrames); // arranque rápido con menos frames
        const CONCURRENCY = 16;                      // más workers para carga paralela
        const PRIORITY_RADIUS = 14;                  // preload alrededor del frame actual
        let backgroundStarted = false;

        // ==========================
        // Estado
        // ==========================
        let dragging = false;
        let startX = 0;
        let accX = 0;
        let velocity = 0;
        let inertiaRaf = null;

        let autoRotate = true;
        let autoDir = 1;
        let secondsPerRev = parseFloat(revRange.value);
        let pxPerFrame = parseInt(sensRange.value, 10);

        let seamBlendFrames = parseInt(seamRange.value, 10);

        // Índice orbital continuo
        let orbitIndex = 1; // float
        let lastT = performance.now();
        let autoAcc = 0;

        // Cache de imágenes
        const images = new Array(totalFrames + 1);
        let loaded = 0;
        let ready = false;

        // Reanudar
        let resumeTimeout = null;
        const resumeDelayMs = 1400;

        // ==========================
        // Helpers
        // ==========================
        function setStatus(mode) {
            if (mode === 'loading') {
                statusText.textContent = "Cargando…";
                statusDot.style.background = "rgba(255, 220, 120, 0.9)";
                statusDot.style.boxShadow = "0 0 14px rgba(255, 220, 120, 0.45)";
                return;
            }
            if (mode === 'auto') {
                statusText.textContent = "Auto-rotate";
                statusDot.style.background = "rgba(120, 255, 180, 0.9)";
                statusDot.style.boxShadow = "0 0 14px rgba(120, 255, 180, 0.55)";
                return;
            }
            if (mode === 'paused') {
                statusText.textContent = "Pausado";
                statusDot.style.background = "rgba(255, 220, 120, 0.9)";
                statusDot.style.boxShadow = "0 0 14px rgba(255, 220, 120, 0.45)";
                return;
            }
            statusText.textContent = "Interactuando";
            statusDot.style.background = "rgba(255, 140, 120, 0.9)";
            statusDot.style.boxShadow = "0 0 14px rgba(255, 140, 120, 0.45)";
        }

        function wrapFloat(x) {
            const m = ((x - 1) % totalFrames + totalFrames) % totalFrames;
            return m + 1;
        }

        function wrapInt(n) {
            n = n % totalFrames;
            if (n <= 0) n += totalFrames;
            return n;
        }

        function stopInertia() {
            if (inertiaRaf) {
                cancelAnimationFrame(inertiaRaf);
                inertiaRaf = null;
            }
        }

        function scheduleResumeIfEnabled() {
            if (!resumeToggle.checked) return;
            if (resumeTimeout) clearTimeout(resumeTimeout);
            resumeTimeout = setTimeout(() => {
                if (!dragging) {
                    autoRotate = true;
                    setStatus('auto');
                    btnPlayPause.textContent = "Pause";
                }
            }, resumeDelayMs);
        }

        // ==========================
        // Canvas sizing
        // ==========================
        function resizeCanvasToDisplaySize() {
            const rect = canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            const w = Math.max(1, Math.round(rect.width * dpr));
            const h = Math.max(1, Math.round(rect.height * dpr));
            if (canvas.width !== w || canvas.height !== h) {
                canvas.width = w;
                canvas.height = h;
            }
        }

        window.addEventListener('resize', resizeCanvasToDisplaySize);

        function drawContain(image, alpha) {
            if (!image || !image.naturalWidth) return;

            const iw = image.naturalWidth;
            const ih = image.naturalHeight;

            const cw = canvas.width;
            const ch = canvas.height;

            const scale = Math.min(cw / iw, ch / ih);
            const dw = iw * scale;
            const dh = ih * scale;
            const dx = (cw - dw) / 2;
            const dy = (ch - dh) / 2;

            ctx.globalAlpha = alpha;
            ctx.drawImage(image, dx, dy, dw, dh);
            ctx.globalAlpha = 1;
        }

        // ==========================
        // Render (nítido + seam-blend)
        // ==========================
        function drawIndex(idxFloat) {
            if (!ready) return;

            resizeCanvasToDisplaySize();

            const w = wrapFloat(idxFloat);
            const base = Math.floor(w);
            const frac = w - base;
            const next = (base >= totalFrames) ? 1 : base + 1;

            const imgA = images[base] || images[1];
            const imgB = images[next] || images[1];

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            const isSeam = (base === totalFrames && next === 1);
            const doSeamBlend = seamBlendToggle.checked && isSeam && frac > 0;

            if (doSeamBlend && seamBlendFrames > 1) {
                const alpha = Math.min(1, Math.max(0, frac * seamBlendFrames));
                drawContain(imgA, 1);
                drawContain(imgB, alpha);
            } else {
                drawContain(imgA, 1);
            }

            const shown = wrapInt(Math.round(w));
            frameText.textContent = String(shown);
        }

        // ==========================
        // Animation loop (crisp auto)
        // ==========================
        function tick(t) {
            const dt = Math.min(0.05, (t - lastT) / 1000);
            lastT = t;

            if (ready && autoRotate && !dragging) {
                const framesPerSec = totalFrames / Math.max(0.5, secondsPerRev);
                autoAcc += framesPerSec * dt;

                const step = Math.floor(autoAcc);
                if (step >= 1) {
                    autoAcc -= step;
                    orbitIndex += autoDir * step;
                }
            }

            if (ready) drawIndex(orbitIndex);

            requestAnimationFrame(tick);
        }

        // ==========================
        // Drag
        // ==========================
        function applyDelta(dx) {
            accX += dx;
            const step = Math.trunc(accX / pxPerFrame);
            if (step !== 0) {
                accX -= step * pxPerFrame;
                orbitIndex += step;
            }
        }

        function pointerDown(clientX) {
            viewer.focus();
            dragging = true;
            viewer.classList.add('dragging');
            startX = clientX;
            accX = 0;
            velocity = 0;

            stopInertia();
            autoRotate = false;
            setStatus('drag');
            btnPlayPause.textContent = "Play";

            if (resumeTimeout) { clearTimeout(resumeTimeout); resumeTimeout = null; }
        }

        function pointerMove(clientX) {
            if (!dragging) return;
            const dx = clientX - startX;
            startX = clientX;
            velocity = dx;
            applyDelta(dx);
        }

        function pointerUp() {
            if (!dragging) return;
            dragging = false;
            viewer.classList.remove('dragging');

            let v = velocity;
            const friction = 0.92;

            if (Math.abs(v) > 0.6) {
                const stepInertia = () => {
                    v *= friction;
                    if (Math.abs(v) < 0.25) {
                        inertiaRaf = null;
                        scheduleResumeIfEnabled();
                        return;
                    }
                    applyDelta(v);
                    inertiaRaf = requestAnimationFrame(stepInertia);
                };
                inertiaRaf = requestAnimationFrame(stepInertia);
            } else {
                scheduleResumeIfEnabled();
            }
        }

        // Mouse
        viewer.addEventListener('mousedown', (e) => pointerDown(e.clientX));
        document.addEventListener('mousemove', (e) => pointerMove(e.clientX));
        document.addEventListener('mouseup', () => pointerUp());

        // Touch
        viewer.addEventListener('touchstart', (e) => {
            if (!e.touches || !e.touches[0]) return;
            pointerDown(e.touches[0].clientX);
        }, { passive: true });

        viewer.addEventListener('touchmove', (e) => {
            if (!e.touches || !e.touches[0]) return;
            pointerMove(e.touches[0].clientX);
        }, { passive: true });

        viewer.addEventListener('touchend', () => pointerUp(), { passive: true });

        // Click: invierte dirección
        viewer.addEventListener('click', () => { autoDir *= -1; });

        // Keyboard
        viewer.addEventListener('keydown', (e) => {
            if (e.code === 'Space') {
                e.preventDefault();
                autoRotate = !autoRotate;
                setStatus(autoRotate ? 'auto' : 'paused');
                btnPlayPause.textContent = autoRotate ? "Pause" : "Play";
            }
            if (e.code === 'ArrowRight' || e.code === 'ArrowLeft') {
                e.preventDefault();
                autoRotate = false;
                setStatus('drag');
                btnPlayPause.textContent = "Play";
                orbitIndex += (e.code === 'ArrowRight' ? 1 : -1);
                scheduleResumeIfEnabled();
            }
        });

        // Controls
        btnReset.addEventListener('click', () => { orbitIndex = 1; autoAcc = 0; });

        btnPlayPause.addEventListener('click', () => {
            autoRotate = !autoRotate;
            setStatus(autoRotate ? 'auto' : 'paused');
            btnPlayPause.textContent = autoRotate ? "Pause" : "Play";
        });

        revRange.addEventListener('input', () => {
            secondsPerRev = parseFloat(revRange.value);
            revText.textContent = secondsPerRev.toFixed(1);
        });

        sensRange.addEventListener('input', () => {
            pxPerFrame = parseInt(sensRange.value, 10);
            sensText.textContent = pxPerFrame + "px";
        });

        seamRange.addEventListener('input', () => {
            seamBlendFrames = parseInt(seamRange.value, 10);
            seamText.textContent = String(seamBlendFrames);
        });

        // ==========================
        // SMART PRELOAD: quick start + background
        // ==========================
        function updateProgressUI() {
            const pct = Math.round((loaded / totalFrames) * 100);
            progressBar.style.width = pct + "%";
            progressPct.textContent = pct + "%";

            if (!ready) {
                progressLabel.textContent = `Cargando rápido… (${loaded}/${MIN_READY})`;
            } else {
                progressLabel.textContent = `Cargando en background… (${loaded}/${totalFrames})`;
            }
        }

        function loadImage(n) {
            return new Promise((resolve) => {
                if (images[n]) return resolve(true);

                const im = new Image();
                im.decoding = "async";
                im.loading = "eager";
                im.src = frameSrc(n);

                im.onload = async () => {
                    images[n] = im;
                    loaded++;
                    updateProgressUI();

                    if (im.decode) { try { await im.decode(); } catch (e) {} }

                    if (!ready && loaded >= MIN_READY) {
                        ready = true;
                        setStatus('auto');
                        progressLabel.textContent = "Listo ✅ (arranque rápido)";
                    }
                    resolve(true);
                };

                im.onerror = () => resolve(false);
            });
        }

        function buildQueue() {
            const q = [];
            const seen = new Set();

            const center = wrapInt(Math.round(wrapFloat(orbitIndex)));
            for (let k = 0; k <= PRIORITY_RADIUS; k++) {
                const a = wrapInt(center + k);
                const b = wrapInt(center - k);
                if (!seen.has(a)) { q.push(a); seen.add(a); }
                if (!seen.has(b)) { q.push(b); seen.add(b); }
            }

            for (let i = 1; i <= totalFrames; i++) {
                if (!seen.has(i)) q.push(i);
            }
            return q;
        }

        async function runQueue(queue) {
            let idx = 0;

            async function worker() {
                while (idx < queue.length) {
                    const n = queue[idx++];
                    if (!images[n]) await loadImage(n);
                }
            }

            const workers = [];
            for (let i = 0; i < CONCURRENCY; i++) workers.push(worker());
            await Promise.all(workers);
        }

        function backgroundLoop() {
            if (backgroundStarted) return;
            backgroundStarted = true;

            const runner = async () => {
                if (loaded >= totalFrames) {
                    progressLabel.textContent = "Listo ✅ (precarga completa)";
                    return;
                }

                const queue = buildQueue();
                await runQueue(queue);

                setTimeout(runner, 200);
            };

            if ('requestIdleCallback' in window) {
                requestIdleCallback(() => runner(), { timeout: 1500 });
            } else {
                setTimeout(runner, 300);
            }
        }

        async function preloadSmart() {
            setStatus('loading');
            seamText.textContent = String(seamBlendFrames);
            updateProgressUI();

            // 1) Arranque rápido: primeros MIN_READY frames
            const quick = [];
            for (let i = 1; i <= MIN_READY; i++) quick.push(i);
            await runQueue(quick);

            // 2) Background: priorizado + el resto
            backgroundLoop();
        }

        // ==========================
        // Init
        // ==========================
        requestAnimationFrame((t) => {
            lastT = t;
            resizeCanvasToDisplaySize();
            requestAnimationFrame(tick);
        });

        preloadSmart();
    </script>

</body>

</html>