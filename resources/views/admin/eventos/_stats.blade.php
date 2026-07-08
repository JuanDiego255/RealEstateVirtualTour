@if($type === 'quotes')
    <div class="stat-card">
        <div class="sc-icon gold"><i class="fa fa-calculator"></i></div>
        <div class="sc-value">{{ $stats['total'] }}</div>
        <div class="sc-label">Cotizaciones totales</div>
    </div>
    <div class="stat-card">
        <div class="sc-icon blue"><i class="fa fa-clock-o"></i></div>
        <div class="sc-value">{{ $stats['today'] }}</div>
        <div class="sc-label">Hoy</div>
    </div>
@else
    <div class="stat-card">
        <div class="sc-icon gold"><i class="fa fa-users"></i></div>
        <div class="sc-value">{{ $stats['total'] }}</div>
        <div class="sc-label">Interesados totales</div>
    </div>
    <div class="stat-card">
        <div class="sc-icon blue"><i class="fa fa-clock-o"></i></div>
        <div class="sc-value">{{ $stats['today'] }}</div>
        <div class="sc-label">Hoy</div>
    </div>
    <div class="stat-card">
        <div class="sc-icon red"><i class="fa fa-fire"></i></div>
        <div class="sc-value">{{ $stats['hot'] }}</div>
        <div class="sc-label">Interés alto (hot)</div>
    </div>
    <div class="stat-card">
        <div class="sc-icon orange"><i class="fa fa-bell"></i></div>
        <div class="sc-value">{{ $stats['pending'] }}</div>
        <div class="sc-label">Sin contactar</div>
    </div>
@endif
