@extends('admin.main')

@section('title', 'Dashboard - Administrador de Empresa')

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
                                Agentes del Equipo
                            </div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['total_agents']) }}</div>
                        </div>
                        <div>
                            <i class="fa fa-users fa-3x text-primary" style="opacity: 0.3;"></i>
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
                                Propiedades Activas
                            </div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['active_properties']) }}/{{ number_format($stats['total_properties']) }}</div>
                        </div>
                        <div>
                            <i class="fa fa-home fa-3x text-info" style="opacity: 0.3;"></i>
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
                                Total Ventas
                            </div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['total_sales']) }}</div>
                        </div>
                        <div>
                            <i class="fa fa-check-circle fa-3x text-success" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Ingresos Este Mes
                            </div>
                            <div class="h3 mb-0 font-weight-bold">₡{{ number_format($stats['revenue_this_month']) }}</div>
                        </div>
                        <div>
                            <i class="fa fa-money fa-3x text-warning" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="row">
        {{-- Ventas por Agente --}}
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa fa-bar-chart"></i> Ventas por Agente</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesByAgentChart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Propiedades por Estado --}}
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fa fa-pie-chart"></i> Propiedades por Estado</h5>
                </div>
                <div class="card-body">
                    <canvas id="propertiesByStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Ventas Recientes --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-list"></i> Ventas Recientes de la Empresa</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Propiedad</th>
                                    <th>Vendedor</th>
                                    <th>Precio de Venta</th>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                    <tr>
                                        <td>{{ \Illuminate\Support\Str::limit($sale->property->name ?? 'N/A', 30) }}</td>
                                        <td>{{ $sale->seller->name ?? 'N/A' }}</td>
                                        <td class="text-success font-weight-bold">
                                            {{ $sale->currency === 'USD' ? '$' : '₡' }}{{ number_format($sale->sale_price) }}
                                        </td>
                                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.sales.show', $sale->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-eye"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No hay ventas registradas
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
    const colors = {
        primary: '#6777ef',
        success: '#47c363',
        warning: '#ffa426',
        danger: '#fc544b',
        info: '#3abaf4',
        secondary: '#6c757d'
    };

    // ==========================================
    // Gráfico: Ventas por Agente (Bar Chart)
    // ==========================================
    const salesByAgent = @json($salesByAgent);
    const agentNames = salesByAgent.map(item => item.seller ? item.seller.name : 'N/A');
    const agentSalesCounts = salesByAgent.map(item => item.count);

    const salesByAgentCtx = document.getElementById('salesByAgentChart');
    new Chart(salesByAgentCtx, {
        type: 'bar',
        data: {
            labels: agentNames,
            datasets: [{
                label: 'Ventas',
                data: agentSalesCounts,
                backgroundColor: colors.success,
                borderColor: colors.success,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // ==========================================
    // Gráfico: Propiedades por Estado (Doughnut)
    // ==========================================
    const statusNames = {
        'available': 'Disponible',
        'reserved': 'Reservado',
        'negotiating': 'En negociación',
        'sold': 'Vendido',
        'rented': 'Alquilado',
        'inactive': 'Inactivo'
    };

    const propStatus = @json($propertiesByStatus);
    const statusLabels = propStatus.map(item => statusNames[item.status] || item.status);
    const statusCounts = propStatus.map(item => item.count);

    const propertiesByStatusCtx = document.getElementById('propertiesByStatusChart');
    new Chart(propertiesByStatusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: [
                    colors.success,
                    colors.warning,
                    colors.info,
                    colors.danger,
                    colors.primary,
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
