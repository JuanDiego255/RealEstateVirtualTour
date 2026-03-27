<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparación de Vehículos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #c2ac1f;
        }
        .header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 11px;
        }
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .comparison-table th,
        .comparison-table td {
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #ddd;
            vertical-align: middle;
        }
        .comparison-table th {
            background: #343a40;
            color: #fff;
            font-weight: 600;
            font-size: 11px;
        }
        .comparison-table th:first-child {
            text-align: left;
            background: #2d3339;
            width: 150px;
        }
        .comparison-table td:first-child {
            text-align: left;
            font-weight: 600;
            background: #f8f9fa;
        }
        .vehicle-header {
            background: #f8f9fa !important;
        }
        .vehicle-name {
            font-weight: 700;
            font-size: 13px;
            color: #333;
        }
        .vehicle-price {
            color: #c2ac1f;
            font-weight: 700;
            font-size: 14px;
        }
        .best-value {
            background: rgba(40, 167, 69, 0.15) !important;
            color: #28a745;
            font-weight: 700;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        @media print {
            body {
                padding: 10px;
            }
            .comparison-table th,
            .comparison-table td {
                padding: 6px 4px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Comparación de Vehículos</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="comparison-table">
        <thead>
            <tr class="vehicle-header">
                <th>Especificación</th>
                @foreach($vehicles as $vehicle)
                    <th>
                        <div class="vehicle-name">{{ $vehicle->brand }} {{ $vehicle->model }}</div>
                        <div>{{ $vehicle->year }}</div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Precio</td>
                @php
                    $prices = $vehicles->pluck('price')->toArray();
                    $minPrice = min($prices);
                @endphp
                @foreach($vehicles as $vehicle)
                    <td class="{{ $vehicle->price == $minPrice ? 'best-value' : '' }}">
                        <span class="vehicle-price">₡{{ number_format($vehicle->price, 0, ',', '.') }}</span>
                    </td>
                @endforeach
            </tr>
            <tr>
                <td>Condición</td>
                @foreach($vehicles as $vehicle)
                    <td>{{ ucfirst($vehicle->condition ?? 'N/A') }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Color</td>
                @foreach($vehicles as $vehicle)
                    <td>{{ $vehicle->color ?? 'N/A' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Kilometraje</td>
                @php
                    $mileages = $vehicles->pluck('mileage_km')->filter()->toArray();
                    $minMileage = count($mileages) ? min($mileages) : null;
                @endphp
                @foreach($vehicles as $vehicle)
                    <td class="{{ $minMileage && $vehicle->mileage_km == $minMileage ? 'best-value' : '' }}">
                        {{ $vehicle->mileage_km ? number_format($vehicle->mileage_km) . ' km' : 'N/A' }}
                    </td>
                @endforeach
            </tr>
            <tr>
                <td>Motor</td>
                @foreach($vehicles as $vehicle)
                    <td>{{ $vehicle->engine_cc ? $vehicle->engine_cc . ' cc' : 'N/A' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Combustible</td>
                @foreach($vehicles as $vehicle)
                    <td>{{ $vehicle->fuel_type ?? 'N/A' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Tanque</td>
                @foreach($vehicles as $vehicle)
                    <td>{{ $vehicle->fuel_tank_capacity ? $vehicle->fuel_tank_capacity . ' L' : 'N/A' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Transmisión</td>
                @foreach($vehicles as $vehicle)
                    <td>{{ $vehicle->transmission ?? 'N/A' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Tracción</td>
                @foreach($vehicles as $vehicle)
                    <td>{{ $vehicle->drivetrain ?? 'N/A' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Puertas</td>
                @foreach($vehicles as $vehicle)
                    <td>{{ $vehicle->doors ?? 'N/A' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Pasajeros</td>
                @php
                    $passengers = $vehicles->pluck('passengers')->filter()->toArray();
                    $maxPassengers = count($passengers) ? max($passengers) : null;
                @endphp
                @foreach($vehicles as $vehicle)
                    <td class="{{ $maxPassengers && $vehicle->passengers == $maxPassengers ? 'best-value' : '' }}">
                        {{ $vehicle->passengers ?? 'N/A' }}
                    </td>
                @endforeach
            </tr>
            <tr>
                <td>Llantas</td>
                @foreach($vehicles as $vehicle)
                    <td>{{ $vehicle->tires ?? 'N/A' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Placa</td>
                @foreach($vehicles as $vehicle)
                    <td>{{ $vehicle->plate ?? 'N/A' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Sucursal</td>
                @foreach($vehicles as $vehicle)
                    <td>{{ $vehicle->category->name ?? 'N/A' }}</td>
                @endforeach
            </tr>
            <tr>
                <td>Categoría</td>
                @foreach($vehicles as $vehicle)
                    <td>{{ $vehicle->subcategory->name ?? 'N/A' }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Este documento es solo para referencia. Los precios y especificaciones pueden variar.</p>
    </div>
</body>
</html>
