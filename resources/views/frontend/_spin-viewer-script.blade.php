<script>
(function() {
    var viewers = document.querySelectorAll('.spin-viewer-wrap[data-frames-dir]');
    if (!viewers.length) return;

    viewers.forEach(function(el) {
        var framesDir = el.dataset.framesDir;
        var totalFrames = parseInt(el.dataset.framesCount) || 1;
        var autoRotate = el.dataset.autoRotate === '1';
        var canvas = el.querySelector('canvas');
        var ctx = canvas.getContext('2d', { alpha: false });

        var baseUrl = '/storage/' + framesDir + '/';
        var images = new Array(totalFrames + 1);
        var loaded = 0;
        var ready = false;
        var orbitIndex = 1;
        var dragging = false;
        var startX = 0;
        var accX = 0;
        var pxPerFrame = 10;
        var autoDir = 1;
        var lastT = 0;
        var autoAcc = 0;
        var secondsPerRev = 8;
        var MIN_READY = Math.min(8, totalFrames);
        var CONCURRENCY = 6;

        function frameSrc(n) {
            return baseUrl + 'frame-' + String(n).padStart(3, '0') + '.webp';
        }

        function resizeCanvas() {
            var rect = canvas.getBoundingClientRect();
            var dpr = window.devicePixelRatio || 1;
            var w = Math.max(1, Math.round(rect.width * dpr));
            var h = Math.max(1, Math.round(rect.height * dpr));
            if (canvas.width !== w || canvas.height !== h) {
                canvas.width = w;
                canvas.height = h;
            }
        }

        function drawFrame(idx) {
            if (!ready) return;
            resizeCanvas();
            var n = ((idx - 1) % totalFrames + totalFrames) % totalFrames + 1;
            var img = images[n] || images[1];
            if (!img) return;
            var cw = canvas.width, ch = canvas.height;
            var scale = Math.min(cw / img.naturalWidth, ch / img.naturalHeight);
            var dw = img.naturalWidth * scale, dh = img.naturalHeight * scale;
            ctx.clearRect(0, 0, cw, ch);
            ctx.drawImage(img, (cw - dw) / 2, (ch - dh) / 2, dw, dh);
        }

        function tick(t) {
            if (!lastT) lastT = t;
            var dt = Math.min(0.05, (t - lastT) / 1000);
            lastT = t;

            if (ready && autoRotate && !dragging) {
                var fps = totalFrames / Math.max(0.5, secondsPerRev);
                autoAcc += fps * dt;
                var step = Math.floor(autoAcc);
                if (step >= 1) {
                    autoAcc -= step;
                    orbitIndex += autoDir * step;
                }
            }
            if (ready) drawFrame(Math.round(orbitIndex));
            requestAnimationFrame(tick);
        }

        // DRAG
        el.addEventListener('mousedown', function(e) {
            dragging = true; startX = e.clientX; accX = 0;
            e.preventDefault();
        });
        document.addEventListener('mousemove', function(e) {
            if (!dragging) return;
            var dx = e.clientX - startX; startX = e.clientX;
            accX += dx;
            var step = Math.trunc(accX / pxPerFrame);
            if (step !== 0) { accX -= step * pxPerFrame; orbitIndex += step; }
        });
        document.addEventListener('mouseup', function() { dragging = false; });

        // TOUCH
        el.addEventListener('touchstart', function(e) {
            if (!e.touches[0]) return;
            dragging = true; startX = e.touches[0].clientX; accX = 0;
        }, { passive: true });
        el.addEventListener('touchmove', function(e) {
            if (!dragging || !e.touches[0]) return;
            var dx = e.touches[0].clientX - startX; startX = e.touches[0].clientX;
            accX += dx;
            var step = Math.trunc(accX / pxPerFrame);
            if (step !== 0) { accX -= step * pxPerFrame; orbitIndex += step; }
        }, { passive: true });
        el.addEventListener('touchend', function() { dragging = false; }, { passive: true });

        // PRELOAD
        function loadImage(n) {
            return new Promise(function(resolve) {
                if (images[n]) return resolve();
                var im = new Image();
                im.src = frameSrc(n);
                im.onload = function() {
                    images[n] = im;
                    loaded++;
                    if (!ready && loaded >= MIN_READY) { ready = true; }
                    resolve();
                };
                im.onerror = function() { resolve(); };
            });
        }

        async function preload() {
            var quick = [];
            for (var i = 1; i <= MIN_READY; i++) quick.push(i);
            await runQueue(quick);

            var rest = [];
            for (var i = 1; i <= totalFrames; i++) {
                if (!images[i]) rest.push(i);
            }
            if (rest.length) await runQueue(rest);
        }

        async function runQueue(queue) {
            var idx = 0;
            async function worker() {
                while (idx < queue.length) {
                    var n = queue[idx++];
                    if (!images[n]) await loadImage(n);
                }
            }
            var workers = [];
            for (var i = 0; i < CONCURRENCY; i++) workers.push(worker());
            await Promise.all(workers);
        }

        // Init
        requestAnimationFrame(tick);
        preload();
    });
})();
</script>
