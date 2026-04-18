<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cotizacion - {{ $quote->vehicle->brand ?? '' }} {{ $quote->vehicle->model ?? '' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #fff;
        }

        .container {
            padding: 30px 40px;
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 3px solid #c2ac1f;
            padding-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            width: 40%;
            text-align: right;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #0b0f14;
            margin-bottom: 5px;
        }

        .quote-title {
            font-size: 18px;
            color: #666;
        }

        .quote-number {
            font-size: 14px;
            color: #c2ac1f;
            font-weight: bold;
        }

        .quote-date {
            font-size: 11px;
            color: #888;
            margin-top: 5px;
        }

        /* Vehicle Info */
        .vehicle-section {
            margin-bottom: 25px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0b0f14;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #c2ac1f;
        }

        .vehicle-name {
            font-size: 20px;
            font-weight: bold;
            color: #0b0f14;
            margin-bottom: 5px;
        }

        .vehicle-details {
            font-size: 13px;
            color: #666;
        }

        .vehicle-price {
            font-size: 22px;
            font-weight: bold;
            color: #c2ac1f;
            margin-top: 10px;
        }

        /* Customer Info */
        .customer-section {
            margin-bottom: 25px;
        }

        .customer-info {
            display: table;
            width: 100%;
        }

        .customer-item {
            display: table-row;
        }

        .customer-label {
            display: table-cell;
            font-weight: bold;
            color: #666;
            padding: 5px 15px 5px 0;
            width: 100px;
        }

        .customer-value {
            display: table-cell;
            color: #333;
            padding: 5px 0;
        }

        /* Quote Details */
        .quote-details {
            margin-bottom: 25px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table th,
        .details-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .details-table th {
            background: #0b0f14;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        .details-table td {
            font-size: 12px;
        }

        .details-table .label {
            color: #666;
            width: 50%;
        }

        .details-table .value {
            text-align: right;
            font-weight: bold;
            color: #333;
        }

        .details-table .highlight {
            background: #f8f9fa;
        }

        .details-table .total-row {
            background: #c2ac1f;
            color: #fff;
        }

        .details-table .total-row td {
            font-size: 14px;
            font-weight: bold;
            border-bottom: none;
        }

        /* Monthly Payment Highlight */
        .monthly-payment-box {
            background: linear-gradient(135deg, #0b0f14 0%, #1a1f26 100%);
            color: #fff;
            padding: 25px;
            text-align: center;
            margin: 25px 0;
            border-radius: 8px;
        }

        .monthly-label {
            font-size: 14px;
            color: #c2ac1f;
            margin-bottom: 10px;
        }

        .monthly-amount {
            font-size: 36px;
            font-weight: bold;
            color: #fff;
        }

        .monthly-term {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }

        /* Disclaimer */
        .disclaimer {
            margin-top: 30px;
            padding: 15px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            font-size: 10px;
            color: #856404;
        }

        .disclaimer-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 10px;
            color: #888;
        }

        .footer-contact {
            margin-bottom: 10px;
        }

        .validity {
            margin-top: 15px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ $quote->vehicle->category->company->name ?? 'Concesionario' }}</div>
                <div class="quote-title">Cotizacion de Financiamiento</div>
            </div>
            <div class="header-right">
                <div class="quote-number">Cotizacion #{{ str_pad($quote->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div class="quote-date">Fecha: {{ $quote->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        @if($quote->property)
        <!-- Vehicle Section -->
        <div class="vehicle-section">
            <div class="section-title">Vehiculo</div>
            <div class="vehicle-name">
                {{ $quote->vehicle->brand ?? '' }} {{ $quote->vehicle->model ?? '' }}
            </div>
            <div class="vehicle-details">
                @if($quote->vehicle->year)
                    Ano: {{ $quote->vehicle->year }} |
                @endif
                @if($quote->vehicle->engine)
                    Motor: {{ $quote->vehicle->engine }} |
                @endif
                @if($quote->vehicle->transmission)
                    Transmision: {{ $quote->vehicle->transmission }}
                @endif
            </div>
            <div class="vehicle-price">
                {{ $quote->currency === 'USD' ? '$' : 'CRC ' }}{{ number_format($quote->vehicle_price, 0, ',', '.') }}
            </div>
        </div>
        @elseif($quote->description)
        <!-- Description Section (other_kiosk mode) -->
        <div class="vehicle-section">
            <div class="section-title">Descripcion de la Cotizacion</div>
            <div class="vehicle-name" style="font-size: 14px; font-weight: normal;">{{ $quote->description }}</div>
            @if($quote->vehicle_price)
            <div class="vehicle-price">
                Monto estimado: {{ $quote->currency === 'USD' ? '$' : 'CRC ' }}{{ number_format($quote->vehicle_price, 0, ',', '.') }}
            </div>
            @endif
        </div>
        @endif

        <!-- Customer Section -->
        @if($quote->customer_name || $quote->customer_phone || $quote->customer_email)
        <div class="customer-section">
            <div class="section-title">Datos del Cliente</div>
            <div class="customer-info">
                @if($quote->customer_name)
                <div class="customer-item">
                    <div class="customer-label">Nombre:</div>
                    <div class="customer-value">{{ $quote->customer_name }}</div>
                </div>
                @endif
                @if($quote->customer_phone)
                <div class="customer-item">
                    <div class="customer-label">Telefono:</div>
                    <div class="customer-value">{{ $quote->customer_phone }}</div>
                </div>
                @endif
                @if($quote->customer_email)
                <div class="customer-item">
                    <div class="customer-label">Email:</div>
                    <div class="customer-value">{{ $quote->customer_email }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Quote Details -->
        <div class="quote-details">
            @if($quote->property)
            {{-- Kiosko normal: detalle de financiamiento del vehículo --}}
            <div class="section-title">Detalle del Financiamiento</div>
            <table class="details-table">
                <tr>
                    <td class="label">Precio del vehiculo</td>
                    <td class="value">{{ $quote->currency === 'USD' ? '$' : 'CRC ' }}{{ number_format($quote->vehicle_price, 0, ',', '.') }}</td>
                </tr>
                <tr class="highlight">
                    <td class="label">Prima ({{ number_format($quote->down_payment_percent, 1) }}%)</td>
                    <td class="value">{{ $quote->currency === 'USD' ? '$' : 'CRC ' }}{{ number_format($quote->down_payment, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Monto a financiar</td>
                    <td class="value">{{ $quote->currency === 'USD' ? '$' : 'CRC ' }}{{ number_format($quote->vehicle_price - $quote->down_payment, 0, ',', '.') }}</td>
                </tr>
                <tr class="highlight">
                    <td class="label">Plazo</td>
                    <td class="value">
                        @if(($quote->payment_frequency ?? 'monthly') === 'annual')
                            {{ ceil($quote->term_months / 12) }} {{ ceil($quote->term_months / 12) == 1 ? 'ano' : 'anos' }}
                        @else
                            {{ $quote->term_months }} meses
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Frecuencia de pago</td>
                    <td class="value">{{ ($quote->payment_frequency ?? 'monthly') === 'annual' ? 'Anual' : 'Mensual' }}</td>
                </tr>
                <tr class="total-row">
                    <td>Cuota Mensual</td>
                    <td class="value">{{ $quote->currency === 'USD' ? '$' : 'CRC ' }}{{ number_format($quote->monthly_payment, 0, ',', '.') }}</td>
                </tr>
            </table>
            @else
            {{-- Kiosko "other": cotización libre sin vehículo --}}
            <div class="section-title">Resumen de Cotizacion</div>
            <table class="details-table">
                @if($quote->vehicle_price)
                <tr class="total-row">
                    <td>Monto estimado</td>
                    <td class="value">{{ $quote->currency === 'USD' ? '$' : 'CRC ' }}{{ number_format($quote->vehicle_price, 0, ',', '.') }}</td>
                </tr>
                @endif
            </table>
            @endif
        </div>

        @if($quote->property && $quote->monthly_payment)
        <!-- Payment Box (solo kiosko normal con financiamiento) -->
        <div class="monthly-payment-box">
            <div class="monthly-label">
                @if(($quote->payment_frequency ?? 'monthly') === 'annual')
                    Cuota Anual Estimada
                @else
                    Cuota Mensual Estimada
                @endif
            </div>
            <div class="monthly-amount">
                {{ $quote->currency === 'USD' ? '$' : 'CRC ' }}{{ number_format($quote->monthly_payment, 0, ',', '.') }}
            </div>
            <div class="monthly-term">
                @if(($quote->payment_frequency ?? 'monthly') === 'annual')
                    por {{ ceil($quote->term_months / 12) }} {{ ceil($quote->term_months / 12) == 1 ? 'ano' : 'anos' }}
                @else
                    por {{ $quote->term_months }} meses
                @endif
            </div>
        </div>
        @endif

        <!-- Disclaimer -->
        <div class="disclaimer">
            <div class="disclaimer-title">Aviso Importante</div>
            <p>
                Esta cotizacion es de caracter informativo y no constituye una oferta de credito vinculante.
                Los valores presentados son estimaciones basadas en los parametros ingresados.
                Las condiciones finales de financiamiento estan sujetas a la aprobacion crediticia y
                las politicas vigentes de la entidad financiera. Los montos pueden variar segun el
                perfil crediticio del solicitante, seguros requeridos y otros gastos asociados.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-contact">
                @if($quote->event_name)
                    Evento: {{ $quote->event_name }} |
                @endif
                Para mas informacion contactenos
            </div>
            <div class="validity">
                Esta cotizacion tiene validez de 15 dias a partir de la fecha de emision.
            </div>
        </div>
    </div>
</body>
</html>
