@extends('admin.main')

@section('title', 'Dashboard - Mi Panel')

@section('content')
<div class="main-content-inner">
    {{-- Stats Cards --}}
    <div class="row mt-4">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Mis Propiedades
                            </div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['my_properties']) }}</div>
                            <small class="text-muted">{{ number_format($stats['active_properties']) }} activas</small>
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
                                Mis Ventas
                            </div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['my_sales']) }}</div>
                        </div>
                        <div>
                            <i class="fa fa-check-circle fa-3x text-success" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Ganado
                            </div>
                            <div class="h3 mb-0 font-weight-bold">₡{{ number_format($stats['total_earned']) }}</div>
                        </div>
                        <div>
                            <i class="fa fa-money fa-3x text-warning" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Vistas Totales en Mis Propiedades
                            </div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['properties_views']) }}</div>
                            <small class="text-muted">Total de visualizaciones acumuladas</small>
                        </div>
                        <div>
                            <i class="fa fa-eye fa-3x text-primary" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="row">
        {{-- Mis Ventas por Mes --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa fa-line-chart"></i> Mis Ventas por Mes ({{ now()->year }})</h5>
                </div>
                <div class="card-body">
                    <canvas id="mySalesByMonthChart" height="80"></canvas>
                </div>
            </div>
        </div>

        {{-- Mis Propiedades por Estado --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fa fa-pie-chart"></i> Mis Propiedades</h5>
                </div>
                <div class="card-body">
                    <canvas id="myPropertiesByStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Propiedades Más Vistas --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-fire"></i> Mis Propiedades Más Vistas</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Propiedad</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Vistas</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostViewedProperties as $property)
                                    <tr>
                                        <td>{{ \Illuminate\Support\Str::limit($property->name, 30) }}</td>
                                        <td>
                                            <span class="badge badge-light">
                                                {{ $property->property_type_name }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">
                                                <i class="fa fa-eye"></i> {{ number_format($property->views_count) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('property.show', $property->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fa fa-external-link"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No tienes propiedades aún
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mis Ventas Recientes --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-clock-o"></i> Mis Ventas Recientes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Propiedad</th>
                                    <th>Precio</th>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myRecentSales as $sale)
                                    <tr>
                                        <td>{{ \Illuminate\Support\Str::limit($sale->property->name ?? 'N/A', 25) }}</td>
                                        <td class="text-success font-weight-bold">
                                            {{ $sale->currency === 'USD' ? '$' : '₡' }}{{ number_format($sale->sale_price) }}
                                        </td>
                                        <td>{{ $sale->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.sales.show', $sale->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-eye"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No tienes ventas registradas
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
    // Gráfico: Mis Ventas por Mes (Line + Bar Chart)
    // ==========================================
    const mySalesByMonthCtx = document.getElementById('mySalesByMonthChart');
    new Chart(mySalesByMonthCtx, {
        type: 'bar',
        data: {
            labels: @json($months),
            datasets: [{
                label: 'Cantidad de Ventas',
                data: @json($salesCounts),
                backgroundColor: colors.primary,
                borderColor: colors.primary,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
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
    // Gráfico: Mis Propiedades por Estado (Doughnut)
    // ==========================================
    const statusNames = {
        'available': 'Disponible',
        'reserved': 'Reservado',
        'negotiating': 'En negociación',
        'sold': 'Vendido',
        'rented': 'Alquilado',
        'inactive': 'Inactivo'
    };

    const myPropStatus = @json($myPropertiesByStatus);
    const myStatusLabels = myPropStatus.map(item => statusNames[item.status] || item.status);
    const myStatusCounts = myPropStatus.map(item => item.count);

    const myPropertiesByStatusCtx = document.getElementById('myPropertiesByStatusChart');
    new Chart(myPropertiesByStatusCtx, {
        type: 'doughnut',
        data: {
            labels: myStatusLabels,
            datasets: [{
                data: myStatusCounts,
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
    .text-xs {
        font-size: 0.75rem;
    }
</style>
@endpush
