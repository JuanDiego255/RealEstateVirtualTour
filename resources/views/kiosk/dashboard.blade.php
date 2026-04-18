@extends('admin.main')

@section('title', 'Dashboard del Evento')

@push('script')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')
<style>
    .event-dashboard {
        padding: 20px;
    }
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .dashboard-header h2 {
        font-size: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .dashboard-header h2 i {
        color: #c2ac1f;
    }
    .event-badge {
        background: linear-gradient(135deg, #c2ac1f, #a89617);
        color: #000;
        padding: 8px 20px;
        border-radius: 30px;
        font-weight: 600;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, rgba(194, 172, 31, 0.1), transparent);
        border-radius: 0 0 0 100%;
    }
    .stat-card .icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        font-size: 22px;
    }
    .stat-card .icon.blue { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .stat-card .icon.green { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
    .stat-card .icon.yellow { background: rgba(234, 179, 8, 0.15); color: #eab308; }
    .stat-card .icon.red { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .stat-card .icon.purple { background: rgba(168, 85, 247, 0.15); color: #a855f7; }
    .stat-card .icon.orange { background: rgba(249, 115, 22, 0.15); color: #f97316; }
    .stat-card .value {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a2e;
        line-height: 1;
    }
    .stat-card .label {
        font-size: 14px;
        color: #666;
        margin-top: 5px;
    }
    .stat-card .trend {
        font-size: 12px;
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .stat-card .trend.up { color: #22c55e; }
    .stat-card .trend.down { color: #ef4444; }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
    }
    @media (max-width: 1200px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    .dashboard-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .dashboard-card .card-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .dashboard-card .card-header h4 {
        font-size: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }
    .dashboard-card .card-header h4 i {
        color: #c2ac1f;
    }
    .dashboard-card .card-body {
        padding: 20px;
    }

    /* Top Vehicles */
    .top-vehicle-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .top-vehicle-item:last-child {
        border-bottom: none;
    }
    .top-vehicle-item .rank {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }
    .top-vehicle-item .rank.gold { background: #fef3c7; color: #d97706; }
    .top-vehicle-item .rank.silver { background: #e5e7eb; color: #6b7280; }
    .top-vehicle-item .rank.bronze { background: #fed7aa; color: #c2410c; }
    .top-vehicle-item .info {
        flex: 1;
    }
    .top-vehicle-item .name {
        font-weight: 600;
        font-size: 14px;
    }
    .top-vehicle-item .meta {
        font-size: 12px;
        color: #666;
    }
    .top-vehicle-item .count {
        font-weight: 700;
        color: #c2ac1f;
    }

    /* Leads Table */
    .leads-table {
        width: 100%;
    }
    .leads-table th {
        text-align: left;
        padding: 12px;
        font-size: 12px;
        text-transform: uppercase;
        color: #666;
        border-bottom: 2px solid #eee;
    }
    .leads-table td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
        vertical-align: middle;
    }
    .leads-table .interest-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .interest-badge.hot { background: #fee2e2; color: #dc2626; }
    .interest-badge.high { background: #fef3c7; color: #d97706; }
    .interest-badge.medium { background: #dbeafe; color: #2563eb; }
    .interest-badge.low { background: #e5e7eb; color: #6b7280; }

    /* Vehicle interest card */
    .vehicle-interest {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 8px;
        border-left: 3px solid #c2ac1f;
    }
    .vehicle-interest img {
        width: 50px;
        height: 35px;
        object-fit: cover;
        border-radius: 4px;
    }
    .vehicle-interest .vehicle-info {
        flex: 1;
    }
    .vehicle-interest .vehicle-name {
        font-weight: 600;
        font-size: 13px;
        color: #333;
    }
    .vehicle-interest .vehicle-price {
        font-size: 12px;
        color: #c2ac1f;
        font-weight: 600;
    }

    /* Action buttons */
    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    .action-btn {
        padding: 6px 10px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
        text-decoration: none;
    }
    .action-btn.whatsapp { background: #25D366; color: #fff; }
    .action-btn.whatsapp:hover { background: #128C7E; }
    .action-btn.call { background: #3b82f6; color: #fff; }
    .action-btn.call:hover { background: #2563eb; }
    .action-btn.crm { background: #8b5cf6; color: #fff; }
    .action-btn.crm:hover { background: #7c3aed; }
    .action-btn.contacted { background: #22c55e; color: #fff; }
    .action-btn.contacted:hover { background: #16a34a; }

    /* Chart placeholder */
    .chart-container {
        height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 12px;
    }

    /* Real-time indicator */
    .realtime-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #22c55e;
    }
    .realtime-indicator .dot {
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
    }

    .refresh-btn {
        padding: 8px 16px;
        background: #f0f0f0;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .refresh-btn:hover {
        background: #e0e0e0;
    }

    /* Source badge */
    .source-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .source-badge.event { background: #dbeafe; color: #1d4ed8; }
    .source-badge.kiosk { background: #fef3c7; color: #b45309; }
    .source-badge.qr { background: #d1fae5; color: #047857; }
    .source-badge.compare { background: #ede9fe; color: #7c3aed; }

    /* Quotes table */
    .quotes-table {
        width: 100%;
    }
    .quotes-table th {
        text-align: left;
        padding: 12px;
        font-size: 12px;
        text-transform: uppercase;
        color: #666;
        border-bottom: 2px solid #eee;
    }
    .quotes-table td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
        vertical-align: middle;
    }
    .quote-amount {
        font-weight: 700;
        color: #c2ac1f;
    }
    .quote-monthly {
        font-size: 12px;
        color: #666;
    }
</style>

<div class="event-dashboard">
    <div class="dashboard-header">
        <h2><i class="fa fa-line-chart"></i> Dashboard del Evento</h2>
        <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
            @if($eventName)
            <span class="event-badge"><i class="fa fa-calendar"></i> {{ $eventName }}</span>
            @endif
            <div class="realtime-indicator">
                <span class="dot"></span>
                Datos en tiempo real
            </div>
            <button class="refresh-btn" onclick="refreshStats()">
                <i class="fa fa-refresh"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon blue"><i class="fa fa-eye"></i></div>
            <div class="value" id="statViews">{{ $topViewed->sum('views') ?? 0 }}</div>
            <div class="label">Vistas totales</div>
            <div class="trend up"><i class="fa fa-arrow-up"></i> Hoy</div>
        </div>

        <div class="stat-card">
            <div class="icon green"><i class="fa fa-users"></i></div>
            <div class="value" id="statLeads">{{ $leadStats['total'] ?? 0 }}</div>
            <div class="label">Leads capturados</div>
            <div class="trend up"><i class="fa fa-arrow-up"></i> {{ $leadStats['today'] ?? 0 }} hoy</div>
        </div>

        <div class="stat-card">
            <div class="icon red"><i class="fa fa-fire"></i></div>
            <div class="value" id="statHot">{{ $leadStats['hot'] ?? 0 }}</div>
            <div class="label">Leads muy interesados</div>
        </div>

        <div class="stat-card">
            <div class="icon yellow"><i class="fa fa-calculator"></i></div>
            <div class="value" id="statQuotes">{{ $quotesToday ?? 0 }}</div>
            <div class="label">Cotizaciones hoy</div>
        </div>

        <div class="stat-card">
            <div class="icon purple"><i class="fa fa-qrcode"></i></div>
            <div class="value" id="statQr">{{ $topQrScans->sum('scan_count') ?? 0 }}</div>
            <div class="label">Escaneos QR</div>
        </div>

        <div class="stat-card">
            <div class="icon orange"><i class="fa fa-heart"></i></div>
            <div class="value" id="statInterested">{{ $leadStats['pending'] ?? 0 }}</div>
            <div class="label">Sin contactar</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Main Content -->
        <div>
            <!-- Leads Table - "Me Interesa" -->
            <div class="dashboard-card" style="margin-bottom: 25px;">
                <div class="card-header">
                    <h4><i class="fa fa-heart"></i> Clientes Interesados (Me Interesa)</h4>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <a href="{{ route('kiosk.export.leads', $eventName ? ['event' => $eventName] : []) }}"
                           class="btn btn-sm btn-success" title="Exportar a CSV">
                            <i class="fa fa-download"></i> CSV
                        </a>
                        <a href="{{ route('admin.crm.leads.index') }}" style="color: #c2ac1f; font-size: 13px;">Ver todos en CRM</a>
                    </div>
                </div>
                <div class="card-body" style="padding: 0; overflow-x: auto;">
                    <table class="leads-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Contacto</th>
                                @if($isOtherKiosk ?? false)
                                <th>Lo que busca</th>
                                @else
                                <th>Vehículo de Interés</th>
                                @endif
                                <th>Origen</th>
                                <th>Interés</th>
                                <th>Agente</th>
                                <th>Hora</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLeads as $lead)
                            <tr>
                                <td>
                                    <strong>{{ $lead->name }}</strong>
                                    @if($lead->email)
                                    <br><small style="color: #666;"><i class="fa fa-envelope"></i> {{ $lead->email }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="tel:{{ $lead->phone }}" style="color: #333; text-decoration: none;">
                                        <i class="fa fa-phone"></i> {{ $lead->phone }}
                                    </a>
                                </td>
                                <td>
                                    @if($isOtherKiosk ?? false)
                                        {{-- Kiosko Other: descripción + foto si existe --}}
                                        <div style="display:flex;gap:8px;align-items:flex-start;">
                                            @if($lead->photo_path)
                                            <a href="{{ asset('storage/'.$lead->photo_path) }}" target="_blank">
                                                <img src="{{ asset('storage/'.$lead->photo_path) }}"
                                                     style="width:48px;height:48px;object-fit:cover;border-radius:6px;flex-shrink:0;" alt="foto">
                                            </a>
                                            @endif
                                            <div>
                                                @if($lead->description)
                                                    <div style="font-size:13px; max-width:160px; line-height:1.4;">{{ Str::limit($lead->description, 70) }}</div>
                                                @elseif($lead->notes)
                                                    <div style="font-size:12px; color:#888; max-width:160px;">{{ Str::limit($lead->notes, 50) }}</div>
                                                @else
                                                    <span style="color: #999;">—</span>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($lead->vehicle)
                                    <div class="vehicle-interest">
                                        <img src="{{ $lead->vehicle->image ? route('file', $lead->vehicle->image) : url('images/producto-sin-imagen.PNG') }}"
                                             alt="{{ $lead->vehicle->brand }}">
                                        <div class="vehicle-info">
                                            <div class="vehicle-name">{{ $lead->vehicle->brand }} {{ $lead->vehicle->model }}</div>
                                            <div class="vehicle-price">₡{{ number_format($lead->vehicle->price) }}</div>
                                        </div>
                                    </div>
                                    @else
                                    <span style="color: #999;">Sin vehículo específico</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="source-badge {{ $lead->source }}">
                                        @if($lead->source == 'event')
                                            <i class="fa fa-calendar"></i> Evento
                                        @elseif($lead->source == 'kiosk')
                                            <i class="fa fa-desktop"></i> Kiosko
                                        @elseif($lead->source == 'qr')
                                            <i class="fa fa-qrcode"></i> QR
                                        @elseif($lead->source == 'compare')
                                            <i class="fa fa-balance-scale"></i> Comparador
                                        @else
                                            {{ $lead->source }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="interest-badge {{ $lead->interest_level }}">
                                        @if($lead->interest_level == 'hot')
                                            <i class="fa fa-fire"></i> Muy Alto
                                        @elseif($lead->interest_level == 'high')
                                            <i class="fa fa-thermometer-three-quarters"></i> Alto
                                        @elseif($lead->interest_level == 'medium')
                                            <i class="fa fa-thermometer-half"></i> Medio
                                        @else
                                            <i class="fa fa-thermometer-empty"></i> Bajo
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if($lead->capturedBy)
                                        <small><i class="fa fa-user"></i> {{ $lead->capturedBy->name }}</small>
                                    @else
                                        <small style="color:#999;">—</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $lead->created_at->format('H:i') }}</div>
                                    <small style="color: #999;">{{ $lead->created_at->format('d/m') }}</small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        @if($lead->phone)
                                        <a href="https://wa.me/506{{ preg_replace('/[^0-9]/', '', $lead->phone) }}?text={{ urlencode('Hola ' . $lead->name . ', gracias por tu interés en ' . ($lead->vehicle ? $lead->vehicle->brand . ' ' . $lead->vehicle->model : 'nuestros vehículos') . '. ¿En qué te podemos ayudar?') }}"
                                           target="_blank" class="action-btn whatsapp" title="WhatsApp">
                                            <i class="fa fa-whatsapp"></i>
                                        </a>
                                        <a href="tel:{{ $lead->phone }}" class="action-btn call" title="Llamar">
                                            <i class="fa fa-phone"></i>
                                        </a>
                                        @endif
                                        <button onclick="addToCRM({{ $lead->id }})" class="action-btn crm" title="Agregar al CRM">
                                            <i class="fa fa-user-plus"></i>
                                        </button>
                                        @if(!$lead->contacted)
                                        <button onclick="markContacted({{ $lead->id }})" class="action-btn contacted" title="Marcar contactado">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        @else
                                        <span style="color: #22c55e; font-size: 11px; padding: 6px;">
                                            <i class="fa fa-check-circle"></i> OK
                                        </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                                    <i class="fa fa-heart" style="font-size: 32px; color: #ddd; margin-bottom: 10px; display: block;"></i>
                                    No hay clientes interesados registrados aún
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cotizaciones Recientes -->
            <div class="dashboard-card" style="margin-bottom: 25px;">
                <div class="card-header">
                    <h4><i class="fa fa-calculator"></i> Cotizaciones Realizadas</h4>
                    <span style="color: #666; font-size: 13px;">{{ $recentQuotes->count() ?? 0 }} cotizaciones</span>
                </div>
                <div class="card-body" style="padding: 0; overflow-x: auto;">
                    <table class="quotes-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                @if($isOtherKiosk ?? false)
                                <th>Descripción</th>
                                <th>Monto Estimado</th>
                                @else
                                <th>Vehículo Cotizado</th>
                                <th>Precio Vehículo</th>
                                <th>Prima</th>
                                <th>Plazo</th>
                                <th>Cuota Mensual</th>
                                @endif
                                <th>Agente</th>
                                <th>Hora</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentQuotes ?? [] as $quote)
                            <tr>
                                <td>
                                    <strong>{{ $quote->customer_name ?? 'Anónimo' }}</strong>
                                    @if($quote->customer_phone)
                                    <br><small style="color: #666;"><i class="fa fa-phone"></i> {{ $quote->customer_phone }}</small>
                                    @endif
                                </td>
                                @if($isOtherKiosk ?? false)
                                {{-- Kiosko Other: descripción libre --}}
                                <td style="max-width:200px;">
                                    @if($quote->description)
                                        <div style="font-size:13px; line-height:1.4;">{{ Str::limit($quote->description, 80) }}</div>
                                    @else
                                        <span style="color:#999;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($quote->vehicle_price)
                                        <span class="quote-amount">₡{{ number_format($quote->vehicle_price) }}</span>
                                    @else
                                        <span style="color:#999;">—</span>
                                    @endif
                                </td>
                                @else
                                {{-- Kiosko normal: vehículo + financiamiento --}}
                                <td>
                                    @if($quote->property)
                                    <div class="vehicle-interest">
                                        <img src="{{ $quote->property->image ? route('file', $quote->property->image) : url('images/producto-sin-imagen.PNG') }}"
                                             alt="{{ $quote->property->brand }}">
                                        <div class="vehicle-info">
                                            <div class="vehicle-name">{{ $quote->property->brand }} {{ $quote->property->model }}</div>
                                            <div class="vehicle-price">{{ $quote->property->year }}</div>
                                        </div>
                                    </div>
                                    @elseif($quote->description)
                                    <div class="vehicle-info">
                                        <div class="vehicle-name" style="font-size: 12px; max-width: 160px;">{{ Str::limit($quote->description, 60) }}</div>
                                    </div>
                                    @else
                                    <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="quote-amount">₡{{ number_format($quote->vehicle_price) }}</span>
                                </td>
                                <td>
                                    ₡{{ number_format($quote->down_payment) }}
                                    <br><small style="color: #666;">({{ $quote->down_payment_percent }}%)</small>
                                </td>
                                <td>{{ $quote->term_months }} meses</td>
                                <td>
                                    <span class="quote-amount">₡{{ number_format($quote->monthly_payment) }}</span>
                                </td>
                                @endif
                                <td>
                                    @if($quote->capturedBy)
                                        <small><i class="fa fa-user"></i> {{ $quote->capturedBy->name }}</small>
                                    @else
                                        <small style="color:#999;">—</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $quote->created_at->format('H:i') }}</div>
                                    <small style="color: #999;">{{ $quote->created_at->format('d/m') }}</small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        @if($quote->customer_phone)
                                        <a href="https://wa.me/506{{ preg_replace('/[^0-9]/', '', $quote->customer_phone) }}?text={{ urlencode('Hola ' . ($quote->customer_name ?? '') . ', gracias por cotizar con nosotros. Tu cuota mensual estimada es de ₡' . number_format($quote->monthly_payment) . ' para el ' . ($quote->property ? $quote->property->brand . ' ' . $quote->property->model : 'vehículo') . '. ¿Te gustaría agendar una cita?') }}"
                                           target="_blank" class="action-btn whatsapp" title="Enviar por WhatsApp">
                                            <i class="fa fa-whatsapp"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('kiosk.quote.pdf', $quote->id) }}" target="_blank" class="action-btn call" title="Descargar PDF">
                                            <i class="fa fa-file-pdf-o"></i>
                                        </a>
                                        <button onclick="addQuoteToCRM({{ $quote->id }})" class="action-btn crm" title="Crear Lead desde cotización">
                                            <i class="fa fa-user-plus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ ($isOtherKiosk ?? false) ? 6 : 9 }}" style="text-align: center; padding: 40px; color: #666;">
                                    <i class="fa fa-calculator" style="font-size: 32px; color: #ddd; margin-bottom: 10px; display: block;"></i>
                                    No hay cotizaciones realizadas aún
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Views Chart -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-area"></i> Vistas por Hora</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="viewsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Top Vehicles -->
            <div class="dashboard-card" style="margin-bottom: 25px;">
                <div class="card-header">
                    <h4><i class="fa fa-trophy"></i> Top Vehículos</h4>
                </div>
                <div class="card-body">
                    @forelse($topViewed as $index => $view)
                    <div class="top-vehicle-item">
                        <div class="rank {{ $index === 0 ? 'gold' : ($index === 1 ? 'silver' : ($index === 2 ? 'bronze' : '')) }}">
                            {{ $index + 1 }}
                        </div>
                        <div class="info">
                            <div class="name">{{ $view->vehicle->brand ?? '' }} {{ $view->vehicle->model ?? '' }}</div>
                            <div class="meta">{{ $view->vehicle->year ?? '' }}</div>
                        </div>
                        <div class="count">{{ $view->views }} vistas</div>
                    </div>
                    @empty
                    <p style="text-align: center; color: #666; padding: 20px;">Sin datos aún</p>
                    @endforelse
                </div>
            </div>

            <!-- Top QR Scans -->
            <div class="dashboard-card" style="margin-bottom: 25px;">
                <div class="card-header">
                    <h4><i class="fa fa-qrcode"></i> Top Escaneos QR</h4>
                </div>
                <div class="card-body">
                    @forelse($topQrScans as $index => $qr)
                    <div class="top-vehicle-item">
                        <div class="rank {{ $index === 0 ? 'gold' : ($index === 1 ? 'silver' : ($index === 2 ? 'bronze' : '')) }}">
                            {{ $index + 1 }}
                        </div>
                        <div class="info">
                            <div class="name">{{ $qr->vehicle->brand ?? '' }} {{ $qr->vehicle->model ?? '' }}</div>
                            <div class="meta">Código: {{ $qr->qr_code }}</div>
                        </div>
                        <div class="count">{{ $qr->scan_count }} scans</div>
                    </div>
                    @empty
                    <p style="text-align: center; color: #666; padding: 20px;">Sin escaneos aún</p>
                    @endforelse
                </div>
            </div>

            <!-- Pending Follow-ups -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h4><i class="fa fa-bell"></i> Pendientes</h4>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #f0f0f0;">
                        <span><i class="fa fa-exclamation-circle" style="color: #dc2626;"></i> Leads sin contactar</span>
                        <span style="background: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 20px; font-weight: 600;">
                            {{ $leadStats['pending'] ?? 0 }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0;">
                        <span><i class="fa fa-check-circle" style="color: #059669;"></i> Contactados hoy</span>
                        <span style="background: #d1fae5; color: #059669; padding: 4px 12px; border-radius: 20px; font-weight: 600;">
                            {{ $leadStats['contacted'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Views by hour chart
    const viewsByHour = @json($viewsByHour);

    const ctx = document.getElementById('viewsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: viewsByHour.map(v => v.hour + ':00'),
                datasets: [{
                    label: 'Vistas',
                    data: viewsByHour.map(v => v.count),
                    borderColor: '#c2ac1f',
                    backgroundColor: 'rgba(194, 172, 31, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Refresh stats
    async function refreshStats() {
        try {
            const response = await fetch('{{ route("kiosk.stats.realtime", ["event" => $eventName]) }}');
            const data = await response.json();

            document.getElementById('statViews').textContent = data.views_today;
            document.getElementById('statLeads').textContent = data.leads.total;
            document.getElementById('statHot').textContent = data.leads.hot;
            document.getElementById('statQuotes').textContent = data.quotes_today;
            document.getElementById('statQr').textContent = data.qr_scans_today;
            if (document.getElementById('statInterested')) {
                document.getElementById('statInterested').textContent = data.leads.pending;
            }

        } catch (error) {
            console.error('Error refreshing stats:', error);
        }
    }

    // Auto-refresh every 30 seconds
    setInterval(refreshStats, 30000);

    // Mark lead as contacted
    async function markContacted(leadId) {
        if (!confirm('¿Marcar este lead como contactado?')) return;

        try {
            const response = await fetch(`/admin/event-leads/${leadId}/contacted`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                }
            });

            if (response.ok) {
                location.reload();
            } else {
                alert('Error al marcar como contactado');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al marcar como contactado');
        }
    }

    // Add lead to CRM
    async function addToCRM(eventLeadId) {
        if (!confirm('¿Agregar este lead al CRM para seguimiento completo?')) return;

        try {
            const response = await fetch(`/admin/event-leads/${eventLeadId}/add-to-crm`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert('Lead agregado al CRM exitosamente');
                if (data.lead_id) {
                    window.open(`/admin/crm/leads/${data.lead_id}`, '_blank');
                }
            } else {
                alert(data.message || 'Error al agregar al CRM');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al agregar al CRM');
        }
    }

    // Add quote to CRM
    async function addQuoteToCRM(quoteId) {
        if (!confirm('¿Crear un lead en el CRM desde esta cotización?')) return;

        try {
            const response = await fetch(`/admin/quotes/${quoteId}/add-to-crm`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert('Lead creado en el CRM exitosamente');
                if (data.lead_id) {
                    window.open(`/admin/crm/leads/${data.lead_id}`, '_blank');
                }
            } else {
                alert(data.message || 'Error al crear lead');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al crear lead');
        }
    }
</script>
@endsection
