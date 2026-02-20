<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Spin Preview</title>
    <style>
        body {
            font-family: Arial;
            background: #111;
            color: white;
            text-align: center;
        }

        .viewer {
            width: 800px;
            margin: 40px auto;
            cursor: grab;
        }

        .viewer img {
            width: 100%;
            user-select: none;
            pointer-events: none;
        }
    </style>
</head>

<body>

    <h2>Spin Preview ({{ $spin->frames_count }} frames)</h2>

    <div class="viewer">
        <img id="spinImage" src="{{ asset('storage/app/public/' . $spin->frames_dir . '/frame-001.jpg') }}">
    </div>

    <script>
        let frame = 1;
        let totalFrames = {{ $spin->frames_count }};
        let dragging = false;
        let startX = 0;

        const img = document.getElementById('spinImage');

        function updateFrame() {
            let num = String(frame).padStart(3, '0');
            img.src = "/storage/app/public/{{ $spin->frames_dir }}/frame-" + num + ".jpg";
        }

        document.querySelector('.viewer').addEventListener('mousedown', e => {
            dragging = true;
            startX = e.pageX;
        });

        document.addEventListener('mouseup', () => dragging = false);

        document.addEventListener('mousemove', e => {
            if (!dragging) return;

            let diff = e.pageX - startX;

            if (Math.abs(diff) > 5) {
                if (diff > 0) {
                    frame++;
                } else {
                    frame--;
                }

                if (frame < 1) frame = totalFrames;
                if (frame > totalFrames) frame = 1;

                updateFrame();
                startX = e.pageX;
            }
        });
    </script>

</body>

</html>
