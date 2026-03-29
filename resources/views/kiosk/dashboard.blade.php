@extends('admin.main')

@section('title', 'Dashboard del Evento')

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
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
</style>

<div class="event-dashboard">
    <div class="dashboard-header">
        <h2><i class="fas fa-chart-line"></i> Dashboard del Evento</h2>
        <div style="display: flex; align-items: center; gap: 20px;">
            @if($eventName)
            <span class="event-badge"><i class="fas fa-calendar-alt"></i> {{ $eventName }}</span>
            @endif
            <div class="realtime-indicator">
                <span class="dot"></span>
                Datos en tiempo real
            </div>
            <button class="refresh-btn" onclick="refreshStats()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon blue"><i class="fas fa-eye"></i></div>
            <div class="value" id="statViews">{{ $topViewed->sum('views') ?? 0 }}</div>
            <div class="label">Vistas totales</div>
            <div class="trend up"><i class="fas fa-arrow-up"></i> Hoy</div>
        </div>

        <div class="stat-card">
            <div class="icon green"><i class="fas fa-users"></i></div>
            <div class="value" id="statLeads">{{ $leadStats['total'] ?? 0 }}</div>
            <div class="label">Leads capturados</div>
            <div class="trend up"><i class="fas fa-arrow-up"></i> {{ $leadStats['today'] ?? 0 }} hoy</div>
        </div>

        <div class="stat-card">
            <div class="icon red"><i class="fas fa-fire"></i></div>
            <div class="value" id="statHot">{{ $leadStats['hot'] ?? 0 }}</div>
            <div class="label">Leads muy interesados</div>
        </div>

        <div class="stat-card">
            <div class="icon yellow"><i class="fas fa-calculator"></i></div>
            <div class="value" id="statQuotes">{{ $quotesToday ?? 0 }}</div>
            <div class="label">Cotizaciones hoy</div>
        </div>

        <div class="stat-card">
            <div class="icon purple"><i class="fas fa-qrcode"></i></div>
            <div class="value" id="statQr">{{ $topQrScans->sum('scan_count') ?? 0 }}</div>
            <div class="label">Escaneos QR</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Main Content -->
        <div>
            <!-- Leads Table -->
            <div class="dashboard-card" style="margin-bottom: 25px;">
                <div class="card-header">
                    <h4><i class="fas fa-user-plus"></i> Leads Recientes</h4>
                    <a href="#" style="color: #c2ac1f; font-size: 13px;">Ver todos</a>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="leads-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Vehículo</th>
                                <th>Interés</th>
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
                                    <br><small style="color: #666;">{{ $lead->email }}</small>
                                    @endif
                                </td>
                                <td>{{ $lead->phone }}</td>
                                <td>
                                    @if($lead->vehicle)
                                    {{ $lead->vehicle->brand }} {{ $lead->vehicle->model }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    <span class="interest-badge {{ $lead->interest_level }}">
                                        {{ ucfirst($lead->interest_level) }}
                                    </span>
                                </td>
                                <td>{{ $lead->created_at->format('H:i') }}</td>
                                <td>
                                    @if(!$lead->contacted)
                                    <button onclick="markContacted({{ $lead->id }})"
                                            style="padding: 5px 10px; background: #22c55e; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 11px;">
                                        <i class="fas fa-check"></i> Contactar
                                    </button>
                                    @else
                                    <span style="color: #22c55e; font-size: 12px;">
                                        <i class="fas fa-check-circle"></i> Contactado
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                                    No hay leads registrados aún
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
                    <h4><i class="fas fa-trophy"></i> Top Vehículos</h4>
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
                    <h4><i class="fas fa-qrcode"></i> Top Escaneos QR</h4>
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
                    <h4><i class="fas fa-bell"></i> Pendientes</h4>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #f0f0f0;">
                        <span>Leads sin contactar</span>
                        <span style="background: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 20px; font-weight: 600;">
                            {{ $leadStats['pending'] ?? 0 }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0;">
                        <span>Contactados hoy</span>
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

        } catch (error) {
            console.error('Error refreshing stats:', error);
        }
    }

    // Auto-refresh every 30 seconds
    setInterval(refreshStats, 30000);

    // Mark lead as contacted
    async function markContacted(leadId) {
        // In production, this would make an API call
        alert('Lead marcado como contactado (demo)');
        location.reload();
    }
</script>
@endsection
