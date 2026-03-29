<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Lista de Favoritos</title>

    <style>
        :root {
            --accent: #c2ac1f;
            --bg: #f5f7fa;
            --card: #ffffff;
            --text: #1a1a2e;
            --muted: #6b7280;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding-bottom: 40px;
        }

        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #2d2d4a 100%);
            color: #fff;
            padding: 40px 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .header h1 i {
            color: var(--accent);
        }

        .header p {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .info-banner {
            background: linear-gradient(135deg, rgba(194, 172, 31, 0.15), rgba(194, 172, 31, 0.05));
            border: 1px solid rgba(194, 172, 31, 0.3);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .info-banner i {
            font-size: 24px;
            color: var(--accent);
        }

        .info-banner .text h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .info-banner .text p {
            font-size: 13px;
            color: var(--muted);
        }

        .vehicles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .vehicle-card {
            background: var(--card);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .vehicle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }

        .vehicle-image {
            aspect-ratio: 16/10;
            background: #f0f0f0;
            position: relative;
        }

        .vehicle-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .vehicle-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-new {
            background: #22c55e;
            color: #fff;
        }

        .badge-used {
            background: #f59e0b;
            color: #000;
        }

        .remove-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(0,0,0,0.6);
            border: none;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .remove-btn:hover {
            background: #ef4444;
        }

        .vehicle-info {
            padding: 20px;
        }

        .vehicle-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .vehicle-subtitle {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 15px;
        }

        .vehicle-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 15px;
        }

        .vehicle-specs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .spec-tag {
            padding: 6px 12px;
            background: #f5f5f5;
            border-radius: 8px;
            font-size: 12px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .spec-tag i {
            color: var(--accent);
        }

        .vehicle-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--accent);
            color: #000;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: var(--text);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(194, 172, 31, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--muted);
            margin-bottom: 20px;
        }

        .share-section {
            background: var(--card);
            border-radius: 16px;
            padding: 25px;
            margin-top: 30px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .share-section h4 {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .share-url {
            display: flex;
            gap: 10px;
            max-width: 500px;
            margin: 0 auto;
        }

        .share-url input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
        }

        .share-url button {
            padding: 12px 20px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        .footer-note {
            text-align: center;
            margin-top: 30px;
            font-size: 13px;
            color: var(--muted);
        }

        @media (max-width: 640px) {
            .vehicles-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <h1><i class="fas fa-bookmark"></i> Mi Lista de Favoritos</h1>
        @if($wishlist->client_name)
        <p>Lista de {{ $wishlist->client_name }}</p>
        @endif
    </header>

    <div class="container">
        @if($vehicles->count() > 0)
        <div class="info-banner">
            <i class="fas fa-lightbulb"></i>
            <div class="text">
                <h4>Tu lista guardada</h4>
                <p>Estos son los vehículos que te interesaron. Puedes compartir este enlace o contactar directamente al concesionario.</p>
            </div>
        </div>

        <div class="vehicles-grid">
            @foreach($vehicles as $vehicle)
            <div class="vehicle-card">
                <div class="vehicle-image">
                    @if($vehicle->image)
                    <img src="{{ route('file', $vehicle->image) }}" alt="{{ $vehicle->brand }} {{ $vehicle->model }}">
                    @else
                    <img src="{{ url('images/producto-sin-imagen.PNG') }}" alt="Sin imagen">
                    @endif

                    @if($vehicle->condition)
                    <span class="vehicle-badge {{ $vehicle->condition === 'Nuevo' ? 'badge-new' : 'badge-used' }}">
                        {{ $vehicle->condition }}
                    </span>
                    @endif

                    <button class="remove-btn" onclick="removeFromList({{ $vehicle->id }})" title="Quitar de la lista">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="vehicle-info">
                    <h3 class="vehicle-title">{{ $vehicle->brand }} {{ $vehicle->model }}</h3>
                    <p class="vehicle-subtitle">{{ $vehicle->name }} - {{ $vehicle->year }}</p>
                    <div class="vehicle-price">₡{{ number_format($vehicle->price) }}</div>

                    <div class="vehicle-specs">
                        @if($vehicle->mileage_km)
                        <span class="spec-tag"><i class="fas fa-tachometer-alt"></i> {{ number_format($vehicle->mileage_km) }} km</span>
                        @endif
                        @if($vehicle->fuel_type)
                        <span class="spec-tag"><i class="fas fa-gas-pump"></i> {{ $vehicle->fuel_type }}</span>
                        @endif
                        @if($vehicle->transmission)
                        <span class="spec-tag"><i class="fas fa-cogs"></i> {{ $vehicle->transmission }}</span>
                        @endif
                    </div>

                    <div class="vehicle-actions">
                        <a href="{{ route('kiosk.vehicle', $vehicle->id) }}" class="action-btn btn-primary">
                            <i class="fas fa-eye"></i> Ver Tour 360
                        </a>
                        <button class="action-btn btn-secondary" onclick="contactAbout({{ $vehicle->id }})">
                            <i class="fas fa-phone"></i> Contactar
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="share-section">
            <h4><i class="fas fa-share-alt" style="color: var(--accent); margin-right: 8px;"></i> Comparte tu lista</h4>
            <div class="share-url">
                <input type="text" id="shareUrl" value="{{ $wishlist->share_url }}" readonly>
                <button onclick="copyShareUrl()"><i class="fas fa-copy"></i> Copiar</button>
            </div>
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-bookmark"></i>
            <h3>Tu lista está vacía</h3>
            <p>Aún no has agregado vehículos a tu lista de favoritos.</p>
            <a href="{{ route('kiosk.index') }}" class="action-btn btn-primary" style="display: inline-flex;">
                <i class="fas fa-car"></i> Ver vehículos disponibles
            </a>
        </div>
        @endif

        <p class="footer-note">
            Esta lista expira el {{ $wishlist->expires_at ? $wishlist->expires_at->format('d/m/Y') : 'nunca' }}.
            Accesos: {{ $wishlist->access_count }}
        </p>
    </div>

    <script>
        const wishlistToken = '{{ $wishlist->share_token }}';

        async function removeFromList(vehicleId) {
            if (!confirm('¿Quitar este vehículo de tu lista?')) return;

            try {
                const response = await fetch('{{ route("kiosk.wishlist.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        token: wishlistToken,
                        remove_vehicle: vehicleId
                    })
                });

                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function copyShareUrl() {
            const input = document.getElementById('shareUrl');
            input.select();
            document.execCommand('copy');
            alert('Enlace copiado al portapapeles!');
        }

        function contactAbout(vehicleId) {
            // In production, this would open a contact form or WhatsApp
            alert('Contactar sobre vehículo ID: ' + vehicleId + '\n\nEn producción, esto abriría WhatsApp o un formulario de contacto.');
        }
    </script>
</body>
</html>
