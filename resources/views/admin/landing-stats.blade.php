@extends('admin.main')
@section('title', 'Estadísticas — Vende tu Vehículo')
@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="fa fa-line-chart text-primary"></i> Estadísticas — /vende-tu-vehiculo</h4>
            <small class="text-muted">Visitas por QR y enlace directo</small>
        </div>
        <a href="{{ $landingUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-external-link"></i> Ver página
        </a>
    </div>

    {{-- Tarjetas resumen --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center py-3 border-0 shadow-sm">
                <div style="font-size:36px;font-weight:800;color:#3498db;">{{ $totalToday }}</div>
                <small class="text-muted">Visitas hoy</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center py-3 border-0 shadow-sm">
                <div style="font-size:36px;font-weight:800;color:#2ecc71;">{{ $totalAll }}</div>
                <small class="text-muted">Total visitas</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center py-3 border-0 shadow-sm">
                <div style="font-size:36px;font-weight:800;color:#e74c3c;">{{ $bySource['qr'] ?? 0 }}</div>
                <small class="text-muted">Desde QR</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center py-3 border-0 shadow-sm">
                <div style="font-size:36px;font-weight:800;color:#9b59b6;">{{ ($bySource['direct'] ?? 0) + ($bySource['link'] ?? 0) }}</div>
                <small class="text-muted">Directo / compartido</small>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Gráfico últimos 14 días --}}
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <strong><i class="fa fa-bar-chart text-primary"></i> Visitas últimos 14 días</strong>
                </div>
                <div class="card-body">
                    @php $maxDay = max($days->values()->toArray() ?: [1]); @endphp
                    <div style="display:flex;align-items:flex-end;gap:6px;height:160px;">
                        @foreach($days as $label => $count)
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
                            @if($count > 0)
                                <span style="font-size:10px;color:#666;">{{ $count }}</span>
                            @else
                                <span style="font-size:10px;color:transparent;">0</span>
                            @endif
                            <div style="width:100%;border-radius:4px 4px 0 0;min-height:4px;
                                        background:{{ $label === now()->format('d/m') ? '#3498db' : '#bdc3c7' }};
                                        height:{{ $maxDay > 0 ? round($count / $maxDay * 130) : 4 }}px;">
                            </div>
                            <span style="font-size:9px;color:#999;white-space:nowrap;">{{ $label }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- QR para imprimir --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm text-center">
                <div class="card-header bg-white">
                    <strong><i class="fa fa-qrcode text-danger"></i> QR para el evento</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Imprime este QR y colócalo en tu stand. Cada escaneo queda registrado como origen "QR".</p>

                    <div class="d-flex justify-content-center mb-3" id="qrDiv">
                        {!! QrCode::size(180)->generate($qrUrl) !!}
                    </div>

                    <p class="small text-muted mb-3" style="word-break:break-all;">{{ $qrUrl }}</p>

                    <div class="d-flex justify-content-center" style="gap:8px;">
                        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-print"></i> Imprimir QR
                        </button>
                        <button id="copyBtn" class="btn btn-sm btn-outline-primary" onclick="copyLink()">
                            <i class="fa fa-copy"></i> Copiar enlace
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Desglose por origen --}}
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <strong><i class="fa fa-filter"></i> Por origen</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Origen</th>
                                <th class="text-right">Visitas</th>
                                <th class="text-right">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $sourceLabels = [
                                    'qr'     => 'QR (evento)',
                                    'direct' => 'Directo',
                                    'link'   => 'Enlace compartido',
                                    'social' => 'Redes sociales',
                                    'email'  => 'Email',
                                ];
                            @endphp
                            @forelse($bySource->sortDesc() as $src => $cnt)
                            <tr>
                                <td>{{ $sourceLabels[$src] ?? $src }}</td>
                                <td class="text-right font-weight-bold">{{ $cnt }}</td>
                                <td class="text-right text-muted">
                                    {{ $totalAll > 0 ? round($cnt / $totalAll * 100) : 0 }}%
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Sin visitas aún</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyLink() {
    navigator.clipboard.writeText('{{ $landingUrl }}?ref=link').then(function () {
        var btn = document.getElementById('copyBtn');
        btn.textContent = '¡Copiado!';
        setTimeout(function () {
            btn.innerHTML = '<i class="fa fa-copy"></i> Copiar enlace';
        }, 2000);
    });
}
</script>

<style>
@media print {
    .sidebar-menu, .header-area, .card:not(.qr-card), .btn { display: none !important; }
    .qr-card { display: block !important; }
}
</style>

@endsection
