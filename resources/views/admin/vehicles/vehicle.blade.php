@extends('admin.main')

@section('title', 'Vehículos')

@section('content')
    @if ($message = Session::has('success'))
        <div class="alert-dismiss">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>{{ Session::get('success') }}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span class="fa fa-times"></span>
                </button>
            </div>
        </div>
    @endif
    @if ($message = Session::has('error'))
        <div class="alert-dismiss">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>{{ Session::get('error') }}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span class="fa fa-times"></span>
                </button>
            </div>
        </div>
    @endif
    @include('admin.vehicles.add')

    {{-- Estilos para spin viewer --}}
    <style>
        .vehicle-spin-viewer {
            position: relative;
            width: 100%;
            height: 170px;
            background: #111;
            overflow: hidden;
            cursor: grab;
        }
        .vehicle-spin-viewer:active {
            cursor: grabbing;
        }
        .vehicle-spin-viewer canvas {
            width: 100%;
            height: 100%;
            display: block;
        }
        .vehicle-spin-viewer .spin-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 6px 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.5));
            pointer-events: none;
        }
        .vehicle-spin-viewer .spin-overlay span {
            color: rgba(255,255,255,0.8);
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .vehicle-spin-viewer .spin-overlay .spin-arrows {
            color: rgba(255,255,255,0.6);
            font-size: 12px;
        }
        .vehicle-img-static {
            height: 170px;
            object-fit: cover;
            width: 100%;
        }
    </style>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-dark"><i class="fa fa-car mr-2"></i>Gestión de Vehículos</h4>
            <button type="button" class="btn btn-rounded btn-outline-info" data-toggle="modal"
                data-target="#addVehicle">
                <i class="fa fa-plus mr-1"></i> Nuevo Vehículo
            </button>
        </div>

        {{-- Filtro por subcategoría --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <form method="GET" action="{{ route('vehicles') }}">
                    <select name="subcategory_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Todas las Subcategorías --</option>
                        @foreach ($subcategories as $sub)
                            <option value="{{ $sub->id }}"
                                {{ request('subcategory_id') == $sub->id ? 'selected' : '' }}>
                                {{ $sub->category->name ?? '' }} → {{ $sub->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="row mt-3">
            @if (count($vehicles) > 0)
                @foreach ($vehicles as $vehicle)
                    @php
                        $spin = $vehicle->active_spin;
                        $hasSpin = !empty($spin);
                    @endphp
                    @include('admin.vehicles.edit')
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="position-relative">
                                @if($hasSpin)
                                    {{-- SPIN 360 VIEWER --}}
                                    <div class="vehicle-spin-viewer" id="spinViewer{{ $vehicle->id }}"
                                         data-frames-dir="{{ $spin->frames_dir }}"
                                         data-frames-count="{{ $spin->frames_count }}"
                                         data-auto-rotate="1">
                                        <canvas></canvas>
                                        <div class="spin-overlay">
                                            <span class="spin-arrows">&larr;</span>
                                            <span>360°</span>
                                            <span class="spin-arrows">&rarr;</span>
                                        </div>
                                    </div>
                                @else
                                    {{-- IMAGEN ESTÁTICA --}}
                                    <img class="card-img-top vehicle-img-static"
                                        src="{{ isset($vehicle->image) ? route('file', $vehicle->image) : url('images/producto-sin-imagen.PNG') }}">
                                @endif
                                <span class="badge badge-dark position-absolute" style="top: 10px; left: 10px; z-index: 10;">
                                    {{ $vehicle->year }}
                                </span>
                                @if ($vehicle->condition)
                                    <span
                                        class="badge badge-{{ $vehicle->condition == 'Nuevo' ? 'success' : 'warning' }} position-absolute"
                                        style="top: 10px; right: 10px; z-index: 10;">
                                        {{ $vehicle->condition }}
                                    </span>
                                @endif
                            </div>
                            <div class="card-body p-3">
                                <h6 class="card-title mb-1 font-weight-bold">
                                    <a href="{{ route('configTyped', ['type' => 'vehicle', 'id' => $vehicle->id]) }}">
                                        {{ $vehicle->brand }} {{ $vehicle->model }}
                                    </a>
                                </h6>
                                <p class="text-muted small mb-2">{{ $vehicle->name }}</p>

                                <div class="mb-2">
                                    <span class="text-primary font-weight-bold">
                                        ₡{{ number_format($vehicle->price) }}
                                    </span>
                                    @if($hasSpin)
                                        <span class="badge badge-info ml-2" title="Tiene Spin 360">
                                            <i class="fa fa-sync-alt"></i> 360°
                                        </span>
                                    @endif
                                </div>

                                <div class="row small text-muted">
                                    @if ($vehicle->mileage_km)
                                        <div class="col-6 mb-1">
                                            <i class="fa fa-tachometer-alt mr-1"></i>{{ $vehicle->mileage_km }} km
                                        </div>
                                    @endif
                                    @if ($vehicle->fuel_type)
                                        <div class="col-6 mb-1">
                                            <i class="fa fa-gas-pump mr-1"></i>{{ $vehicle->fuel_type }}
                                        </div>
                                    @endif
                                    @if ($vehicle->transmission)
                                        <div class="col-6 mb-1">
                                            <i class="fa fa-cogs mr-1"></i>{{ $vehicle->transmission }}
                                        </div>
                                    @endif
                                    @if ($vehicle->engine_cc)
                                        <div class="col-6 mb-1">
                                            <i class="fa fa-bolt mr-1"></i>{{ $vehicle->engine_cc }} CC
                                        </div>
                                    @endif
                                    @if ($vehicle->doors)
                                        <div class="col-6 mb-1">
                                            <i class="fa fa-door-open mr-1"></i>{{ $vehicle->doors }} puertas
                                        </div>
                                    @endif
                                    @if ($vehicle->passengers)
                                        <div class="col-6 mb-1">
                                            <i class="fa fa-users mr-1"></i>{{ $vehicle->passengers }} pasajeros
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-white p-2">
                                <div class="d-flex justify-content-between mb-2">
                                    <a href="{{ route('configTyped', ['type' => 'vehicle', 'id' => $vehicle->id]) }}"
                                        class="btn btn-sm btn-outline-info" title="Configurar Tour">
                                        <i class="fa fa-cog"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                        data-target="#editVehicle{{ $vehicle->id }}" title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <a onclick="if (confirm('Deseas borrar este vehiculo?')) {
                                        document.getElementById('deleteVehicle{{ $vehicle->id }}').submit();
                                    }"
                                        href="#" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    <form id="deleteVehicle{{ $vehicle->id }}" method="post"
                                        action="{{ route('deleteVehicle', $vehicle->id) }}">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                    </form>
                                </div>
                                <a href="{{ route('admin.test-drive.index', $vehicle->id) }}"
                                   class="btn btn-sm btn-warning btn-block" title="Configurar Test Drive">
                                    <i class="fa fa-car mr-1"></i> Test Drive Virtual
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12 text-center mt-5">
                    <i class="fa fa-car fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay vehículos registrados</h5>
                    <p class="text-muted">Crea un sector automotriz y categorías primero, luego agrega vehículos.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Script para Spin Viewer --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var viewers = document.querySelectorAll('.vehicle-spin-viewer[data-frames-dir]');
        if (!viewers.length) return;

        viewers.forEach(function(el) {
            var framesDir = el.dataset.framesDir;
            var totalFrames = parseInt(el.dataset.framesCount) || 1;
            var autoRotate = el.dataset.autoRotate === '1';
            var canvas = el.querySelector('canvas');
            var ctx = canvas.getContext('2d', { alpha: false });

            var baseUrl = '/storage/' + framesDir + '/';
            var images = new Array(totalFrames + 1);
            var loaded = 0;
            var ready = false;
            var orbitIndex = 1;
            var dragging = false;
            var startX = 0;
            var accX = 0;
            var pxPerFrame = 10;
            var autoDir = 1;
            var lastT = 0;
            var autoAcc = 0;
            var secondsPerRev = 8;
            var MIN_READY = Math.min(8, totalFrames);
            var CONCURRENCY = 6;

            function frameSrc(n) {
                return baseUrl + 'frame-' + String(n).padStart(3, '0') + '.webp';
            }

            function resizeCanvas() {
                var rect = canvas.getBoundingClientRect();
                var dpr = window.devicePixelRatio || 1;
                var w = Math.max(1, Math.round(rect.width * dpr));
                var h = Math.max(1, Math.round(rect.height * dpr));
                if (canvas.width !== w || canvas.height !== h) {
                    canvas.width = w;
                    canvas.height = h;
                }
            }

            function drawFrame(idx) {
                if (!ready) return;
                resizeCanvas();
                var n = ((idx - 1) % totalFrames + totalFrames) % totalFrames + 1;
                var img = images[n] || images[1];
                if (!img) return;
                var cw = canvas.width, ch = canvas.height;
                var scale = Math.min(cw / img.naturalWidth, ch / img.naturalHeight);
                var dw = img.naturalWidth * scale, dh = img.naturalHeight * scale;
                ctx.clearRect(0, 0, cw, ch);
                ctx.drawImage(img, (cw - dw) / 2, (ch - dh) / 2, dw, dh);
            }

            function tick(t) {
                if (!lastT) lastT = t;
                var dt = Math.min(0.05, (t - lastT) / 1000);
                lastT = t;

                if (ready && autoRotate && !dragging) {
                    var fps = totalFrames / Math.max(0.5, secondsPerRev);
                    autoAcc += fps * dt;
                    var step = Math.floor(autoAcc);
                    if (step >= 1) {
                        autoAcc -= step;
                        orbitIndex += autoDir * step;
                    }
                }
                if (ready) drawFrame(Math.round(orbitIndex));
                requestAnimationFrame(tick);
            }

            // DRAG
            el.addEventListener('mousedown', function(e) {
                dragging = true; startX = e.clientX; accX = 0;
                e.preventDefault();
            });
            document.addEventListener('mousemove', function(e) {
                if (!dragging) return;
                var dx = e.clientX - startX; startX = e.clientX;
                accX += dx;
                var step = Math.trunc(accX / pxPerFrame);
                if (step !== 0) { accX -= step * pxPerFrame; orbitIndex += step; }
            });
            document.addEventListener('mouseup', function() { dragging = false; });

            // TOUCH
            el.addEventListener('touchstart', function(e) {
                if (!e.touches[0]) return;
                dragging = true; startX = e.touches[0].clientX; accX = 0;
            }, { passive: true });
            el.addEventListener('touchmove', function(e) {
                if (!dragging || !e.touches[0]) return;
                var dx = e.touches[0].clientX - startX; startX = e.touches[0].clientX;
                accX += dx;
                var step = Math.trunc(accX / pxPerFrame);
                if (step !== 0) { accX -= step * pxPerFrame; orbitIndex += step; }
            }, { passive: true });
            el.addEventListener('touchend', function() { dragging = false; }, { passive: true });

            // PRELOAD
            function loadImage(n) {
                return new Promise(function(resolve) {
                    if (images[n]) return resolve();
                    var im = new Image();
                    im.src = frameSrc(n);
                    im.onload = function() {
                        images[n] = im;
                        loaded++;
                        if (!ready && loaded >= MIN_READY) { ready = true; }
                        resolve();
                    };
                    im.onerror = function() { resolve(); };
                });
            }

            async function preload() {
                var quick = [];
                for (var i = 1; i <= MIN_READY; i++) quick.push(i);
                await runQueue(quick);

                var rest = [];
                for (var i = 1; i <= totalFrames; i++) {
                    if (!images[i]) rest.push(i);
                }
                if (rest.length) await runQueue(rest);
            }

            async function runQueue(queue) {
                var idx = 0;
                async function worker() {
                    while (idx < queue.length) {
                        var n = queue[idx++];
                        if (!images[n]) await loadImage(n);
                    }
                }
                var workers = [];
                for (var i = 0; i < CONCURRENCY; i++) workers.push(worker());
                await Promise.all(workers);
            }

            // Init
            requestAnimationFrame(tick);
            preload();
        });
    });
    </script>
@endsection
