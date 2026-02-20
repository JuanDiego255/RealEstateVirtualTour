<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Spin Upload</title>
</head>

<body style="font-family: Arial; padding: 20px;">
    <h2>Prueba: Subir video para generar Spin (CloudConvert)</h2>

    @if ($errors->any())
        <div style="background:#ffecec; padding:10px; margin-bottom:10px;">
            <b>Errores:</b>
            <ul>
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div style="background:#eaffea; padding:10px; margin-bottom:10px;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('spins.test.store') }}" enctype="multipart/form-data">
        @csrf

        <div style="margin-bottom:10px;">
            <label>vehicle_id (opcional)</label><br>
            <input type="number" name="vehicle_id" value="{{ old('vehicle_id') }}">
        </div>

        <div style="margin-bottom:10px;">
            <label>Video (mp4/mov/webm)</label><br>
            <input type="file" name="video" accept="video/mp4,video/quicktime,video/webm" required>
        </div>

        <button type="submit">Subir y procesar</button>
    </form>

    <hr>
    <p>Luego revisá el estado del último spin en DB o en el endpoint:</p>
    <code>/spins/test/{id}</code>
</body>

</html>
