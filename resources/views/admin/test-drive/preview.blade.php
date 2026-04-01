<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Preview Test Drive - {{ $vehicle->brand }} {{ $vehicle->model }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --accent: #ffc107;
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

        .preview-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 15px 20px;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .preview-header h1 {
            font-size: 18px;
            font-weight: 600;
        }

        .preview-header .badge {
            background: var(--accent);
            color: #000;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .preview-header a {
            color: var(--accent);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .preview-content {
            padding: 80px 20px 20px;
            max-width: 600px;
            margin: 0 auto;
        }

        .info-card {
            background: var(--card);
            border-radius: 16px;
            border: 1px solid var(--border);
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="preview-header">
        <h1><i class="fas fa-eye mr-2"></i> Vista Previa - Test Drive</h1>
        <span class="badge">{{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->year }}</span>
        @if(request()->get('from') === 'kiosk')
            <button onclick="window.close()" style="background:none;border:none;color:var(--accent);cursor:pointer;display:flex;align-items:center;gap:6px;font-size:14px;">
                <i class="fas fa-times"></i> Cerrar
            </button>
        @else
            <a href="{{ route('admin.test-drive.index', $vehicle->id) }}">
                <i class="fas fa-arrow-left"></i> Volver al Admin
            </a>
        @endif
    </div>

    <div class="preview-content">
        @php
            // Variable necesaria para el partial (debe estar ANTES del include)
            $testDriveVideos = $videos;
        @endphp
        @include('kiosk.partials.test-drive-immersive')
    </div>
</body>
</html>
