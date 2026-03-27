@extends('admin.main')

@section('title', 'Dashboard - Super Admin')

@section('content')
<div class="main-content-inner">
    {{-- Stats Cards --}}
    <div class="row mt-4">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Empresas Registradas
                            </div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['total_companies']) }}</div>
                        </div>
                        <div>
                            <i class="fa fa-building fa-3x text-primary" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Suscripciones Activas
                            </div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['active_subscriptions']) }}</div>
                        </div>
                        <div>
                            <i class="fa fa-check-circle fa-3x text-success" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Propiedades Totales
                            </div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['total_properties']) }}</div>
                        </div>
                        <div>
                            <i class="fa fa-home fa-3x text-info" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Ventas
                            </div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['total_sales']) }}</div>
                        </div>
                        <div>
                            <i class="fa fa-money fa-3x text-warning" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-left-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Ingresos Este Mes
                            </div>
                            <div class="h3 mb-0 font-weight-bold">₡{{ number_format($stats['revenue_this_month']) }}</div>
                        </div>
                        <div>
                            <i class="fa fa-dollar fa-3x text-danger" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="row">
        {{-- Ventas por Mes --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa fa-line-chart"></i> Ventas por Mes ({{ now()->year }})</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesByMonthChart" height="80"></canvas>
                </div>
            </div>
        </div>

        {{-- Propiedades por Tipo --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fa fa-pie-chart"></i> Propiedades por Tipo</h5>
                </div>
                <div class="card-body">
                    <canvas id="propertiesByTypeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Top Empresas --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa fa-trophy"></i> Top 10 Empresas por Ventas</h5>
                </div>
                <div class="card-body">
                    <canvas id="topCompaniesChart" height="120"></canvas>
                </div>
            </div>
        </div>

        {{-- Ventas Recientes --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-clock-o"></i> Ventas Recientes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Propiedad</th>
                                    <th>Vendedor</th>
                                    <th>Precio</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.sales.show', $sale->id) }}">
                                                {{ \Illuminate\Support\Str::limit($sale->property->name ?? 'N/A', 25) }}
                                            </a>
                                        </td>
                                        <td>{{ $sale->seller->name ?? 'N/A' }}</td>
                                        <td class="text-success font-weight-bold">
                                            {{ $sale->currency === 'USD' ? '$' : '₡' }}{{ number_format($sale->sale_price) }}
                                        </td>
                                        <td>{{ $sale->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No hay ventas recientes
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    // Colores del tema
    const colors = {
        primary: '#6777ef',
        success: '#47c363',
        warning: '#ffa426',
        danger: '#fc544b',
        info: '#3abaf4',
        secondary: '#6c757d'
    };

    // ==========================================
    // Gráfico: Ventas por Mes (Line Chart)
    // ==========================================
    const salesByMonthCtx = document.getElementById('salesByMonthChart');
    new Chart(salesByMonthCtx, {
        type: 'line',
        data: {
            labels: @json($months),
            datasets: [{
                label: 'Ingresos (₡)',
                data: @json($totals),
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            label += '₡' + new Intl.NumberFormat('es-CR').format(context.parsed.y);
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₡' + new Intl.NumberFormat('es-CR', {notation: 'compact'}).format(value);
                        }
                    }
                }
            }
        }
    });

    // ==========================================
    // Gráfico: Propiedades por Tipo (Doughnut)
    // ==========================================
    const propTypeNames = {
        'house': 'Casas',
        'apartment': 'Apartamentos',
        'land': 'Lotes/Terrenos',
        'vehicle': 'Vehículos',
        'commercial': 'Comerciales',
        'other': 'Otros'
    };

    const propTypeData = @json($propertiesByType);
    const propLabels = propTypeData.map(item => propTypeNames[item.property_type] || item.property_type);
    const propCounts = propTypeData.map(item => item.count);

    const propertiesByTypeCtx = document.getElementById('propertiesByTypeChart');
    new Chart(propertiesByTypeCtx, {
        type: 'doughnut',
        data: {
            labels: propLabels,
            datasets: [{
                data: propCounts,
                backgroundColor: [
                    colors.primary,
                    colors.success,
                    colors.warning,
                    colors.danger,
                    colors.info,
                    colors.secondary
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // ==========================================
    // Gráfico: Top Empresas (Horizontal Bar)
    // ==========================================
    const topCompanies = @json($topCompanies);
    const companyNames = topCompanies.map(c => c.name);
    const companySales = topCompanies.map(c => c.sales_count);

    const topCompaniesCtx = document.getElementById('topCompaniesChart');
    new Chart(topCompaniesCtx, {
        type: 'bar',
        data: {
            labels: companyNames,
            datasets: [{
                label: 'Ventas',
                data: companySales,
                backgroundColor: colors.success,
                borderColor: colors.success,
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endpush

@push('styles')
<style>
    .border-left-primary {
        border-left: 4px solid #6777ef !important;
    }
    .border-left-success {
        border-left: 4px solid #47c363 !important;
    }
    .border-left-info {
        border-left: 4px solid #3abaf4 !important;
    }
    .border-left-warning {
        border-left: 4px solid #ffa426 !important;
    }
    .border-left-danger {
        border-left: 4px solid #fc544b !important;
    }
    .text-xs {
        font-size: 0.75rem;
    }
</style>
@endpush
