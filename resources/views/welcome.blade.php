<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Virtual Tour | Su propiedad en occidente</title>

    {{-- Bootstrap CSS --}}
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" media="all">
    {{-- Icon --}}
    <link rel="icon" href="{{ asset('img/UnsoedIcon.png') }}">

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css?family=Fahkwang:400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600&display=swap" rel="stylesheet">
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">

    {{-- App CSS --}}
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">

    {{-- Pannellum --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">

    {{-- Estilos personalizados para hotspots --}}
    <style>
        .hotspot-tooltip-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .hotspot-label {
            background-color: rgba(0, 0, 0, 0.75);
            color: #fff;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
            white-space: nowrap;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .hotspot-label-info {
            background-color: rgba(52, 152, 219, 0.9);
            border: 1px solid #2980b9;
        }

        .hotspot-label-scene {
            background-color: rgba(46, 204, 113, 0.9);
            border: 1px solid #27ae60;
        }

        .circular-hotspot-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .circular-hotspot-img:hover {
            transform: scale(1.1);
        }

        .pnlm-hotspot.circular-hotspot {
            background: transparent !important;
            border: none !important;
            width: auto !important;
            height: auto !important;
        }

        /* Ocultar el loading de Pannellum */
        .pnlm-load-box {
            display: none !important;
        }

        /* Nombre de la escena actual */
        .current-scene-name {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            opacity: 0;
            transition: opacity 0.5s ease, transform 0.5s ease;
            z-index: 1000;
        }

        .current-scene-name.visible {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .current-scene-name.hidden {
            opacity: 0;
            transform: translateX(-50%) translateY(-10px);
        }

        /* Contenedor del visor */
        #pannellum {
            position: relative;
        }

        /* Ocultar cualquier overlay de transición */
        .walk-transition-overlay {
            display: none !important;
        }

        /* Video Dron Orbital Viewer */
        #video-viewer-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 5;
            background: #000;
            display: none;
            cursor: grab;
            user-select: none;
            -webkit-user-select: none;
        }

        #video-viewer-overlay.active-dragging {
            cursor: grabbing;
        }

        #drone-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            pointer-events: none;
        }

        #video-scrub-indicator {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        #video-scrub-indicator.visible {
            opacity: 1;
        }

        #video-progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
        }

        #video-progress-fill {
            height: 100%;
            background: #007bff;
            width: 0%;
            transition: width 0.1s linear;
        }

        #video-hotspots-bar {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 6;
        }

        .video-hotspot-btn {
            background: rgba(46, 204, 113, 0.9);
            border: 2px solid #27ae60;
            color: #fff;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: transform 0.2s ease, background 0.2s ease;
            pointer-events: auto;
        }

        .video-hotspot-btn:hover {
            transform: scale(1.05);
            background: rgba(39, 174, 96, 1);
        }

        #video-drag-hint {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        #video-drag-hint.visible {
            opacity: 1;
        }

        /* Hotspots posicionados en el video */
        .video-pos-hotspot {
            position: absolute;
            transform: translate(-50%, -50%);
            z-index: 7;
            cursor: pointer;
            pointer-events: auto;
            transition: opacity 0.3s ease;
        }

        .video-pos-hotspot .hotspot-tooltip-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .video-pos-hotspot .circular-hotspot-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            object-fit: cover;
            transition: transform 0.2s ease;
        }

        .video-pos-hotspot:hover .circular-hotspot-img {
            transform: scale(1.1);
        }
    </style>
</head>

<body id="top">
    <header>
        <nav id="menu-nav-wrap">
            <h3>Virtual Tour</h3>
            <ul class="nav-list">
                @foreach ($scenes as $scene)
                    <li>
                        <a class="smoothscroll js-load-scene" href="#" data-scene-id="{{ $scene->id }}">
                            {{ $scene->title }}
                            <center>
                                <img class="circular text-center"
                                    src="{{ isset($scene->image_ref) ? route('file', $scene->image_ref) : url('images/producto-sin-imagen.PNG') }}"
                                    alt="{{ $scene->title }}">
                            </center>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    {{-- Modal Mapa --}}
    <div class="modal fade" id="denahModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <img src="" alt="mapa">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Overlay de inicio --}}
    <div class="home-content-table">
        <div class="home-content-tablecell">
            <div class="row">
                <div class="col-twelve">
                    <h1 class="animate-intro">Virtual Tour | Descubre {{ $fscene->property_name }}</h1>
                    <div class="more animate-intro">
                        <a id="btn-start-tour" class="button stroke" href="#">Empezar Tour</a>
                        <a class="button stroke" href="{{ url('/') }}">Más Propiedades</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Visor --}}
    <div id="pannellum">
        <div id="controls">
            <div class="ctrl btn-tooltip" id="menu-trigger-ctrl" title="Menú">
                <i class="fa fa-cubes"></i>
            </div>
            <div class="ctrl" id="menu-trigger-ctrl-map" title="Mapa">
                <i class="fa fa-map"></i>
            </div>
        </div>

        {{-- Video Dron Orbital Overlay --}}
        <div id="video-viewer-overlay">
            <video id="drone-video" muted playsinline preload="auto"></video>
            <div id="video-drag-hint"><i class="fa fa-arrows-h"></i> Arrastra para girar alrededor de la propiedad</div>
            <div id="video-scrub-indicator"></div>
            <div id="video-hotspots-bar"></div>
            <div id="video-progress-bar">
                <div id="video-progress-fill"></div>
            </div>
        </div>
    </div>

    {{-- Overlay de transición --}}
    <div class="walk-transition-overlay"></div>

    {{-- Nombre de escena --}}
    <div class="current-scene-name"></div>

    <div id="preloader">
        <div id="loader"></div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    {{-- Popper (requerido por Bootstrap tooltips/modals) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    {{-- Bootstrap JS --}}
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>

    <script src="{{ asset('js/modernizr.js') }}"></script>
    <script src="{{ asset('js/plugins.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    {{-- Pannellum --}}
    <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>

    @php
        // 1) Opciones JSON pre-calculadas (evita usar "|" dentro de @json)
        $jsonOptions = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        // 2) Default Pannellum - Configuración para efecto de caminar con zoom
        $pannellumDefault = [
            'firstScene' => (string) $fscene->id,
            'hfov' => 100,
            'minHfov' => 2,                       // Permitir zoom extremo para transición de caminar
            'maxHfov' => 120,
            'autoLoad' => true,
            'sceneFadeDuration' => 0,             // Sin fade
            'autoRotate' => -2,
            'autoRotateInactivityDelay' => 5000,
            'compass' => false,
            'showControls' => true,
            'mouseZoom' => true,
            'draggable' => true,
            'showFullscreenCtrl' => true,
            'showZoomCtrl' => false,
            'keyboardZoom' => true,
        ];

        // 3) Scenes + hotspots (misma lógica tuya, sólo formateado)
        $scenesConfig = [];
        foreach ($scenes as $scene) {
            $hotspotsForScene = [];
            foreach ($hotspots->where('sourceScene', $scene->id) as $hotspot) {
                // Determinar el texto a mostrar arriba de la imagen
                // Para tipo 'info': mostrar el campo info (ej: "Refrigeradora")
                // Para tipo 'scene': mostrar el nombre de la escena destino
                $displayText = $hotspot->type === 'info'
                    ? $hotspot->info
                    : ($hotspot->targetSceneName ?? $hotspot->info);

                $hs = [
                    'pitch' => (float) $hotspot->pitch,
                    'yaw' => (float) $hotspot->yaw,
                    'cssClass' => 'circular-hotspot',
                    'type' => 'custom',  // Usar tipo custom para control total
                    'createTooltipFunc' => 'hotspotTooltipFunction',
                    'createTooltipArgs' => [
                        'imageUrl' => isset($hotspot->image)
                            ? route('file', $hotspot->image)
                            : url('images/producto-sin-imagen.PNG'),
                        'displayText' => $displayText,
                        'hotspotType' => $hotspot->type,
                        'videoTime' => $hotspot->video_time !== null ? (float) $hotspot->video_time : null,
                        'posX' => $hotspot->pos_x !== null ? (float) $hotspot->pos_x : null,
                        'posY' => $hotspot->pos_y !== null ? (float) $hotspot->pos_y : null,
                    ],
                    'text' => $hotspot->info,
                ];
                // Para hotspots de tipo scene, agregar handler personalizado
                if ($hotspot->type === 'scene' && $hotspot->targetScene) {
                    $hs['clickHandlerFunc'] = 'onHotspotClick';
                    $hs['clickHandlerArgs'] = [
                        'targetSceneId' => (string) $hotspot->targetScene,
                        'yaw' => (float) $hotspot->yaw,
                        'pitch' => (float) $hotspot->pitch
                    ];
                }
                $hotspotsForScene[] = $hs;
            }

            $scenesConfig[(string) $scene->id] = [
                'title' => $scene->title,
                'hfov' => (float) $scene->hfov,
                'pitch' => (float) $scene->pitch,
                'yaw' => (float) $scene->yaw,
                'type' => $scene->type,
                'panorama' => isset($scene->image)
                    ? route('file', $scene->image)
                    : url('images/producto-sin-imagen.PNG'),
                'video' => $scene->video ? route('file', $scene->video) : null,
                'hotSpots' => $hotspotsForScene,
            ];
        }

        // 4) Polígonos por escena
        $polygonsConfig = [];
        if (isset($polygons)) {
            foreach ($polygons as $polygon) {
                $sceneId = (string) $polygon->scene_id;
                if (!isset($polygonsConfig[$sceneId])) {
                    $polygonsConfig[$sceneId] = [];
                }
                $polygonsConfig[$sceneId][] = [
                    'id' => $polygon->id,
                    'name' => $polygon->name,
                    'fillColor' => $polygon->fill_color,
                    'fillOpacity' => (float) $polygon->fill_opacity,
                    'strokeColor' => $polygon->stroke_color,
                    'strokeWidth' => (int) $polygon->stroke_width,
                    'points' => json_decode($polygon->points, true),
                    'edgeLabels' => $polygon->edge_labels ? json_decode($polygon->edge_labels, true) : null,
                    'interiorText' => $polygon->interior_text,
                ];
            }
        }
    @endphp

    <script>
        (function() {
            'use strict';

            // --- Tooltip: función real (global) ---
            function hotspotTooltipFunction(hotSpotDiv, args) {
                // args contiene: { imageUrl, displayText, hotspotType }
                var imageUrl = args.imageUrl || args;
                var displayText = args.displayText || '';
                var hotspotType = args.hotspotType || 'scene';

                // Crear contenedor principal
                const container = document.createElement('div');
                container.classList.add('hotspot-tooltip-container');

                // Crear etiqueta de texto arriba de la imagen
                if (displayText) {
                    const label = document.createElement('div');
                    label.classList.add('hotspot-label');
                    label.textContent = displayText;
                    // Agregar clase adicional según el tipo
                    if (hotspotType === 'info') {
                        label.classList.add('hotspot-label-info');
                    } else {
                        label.classList.add('hotspot-label-scene');
                    }
                    container.appendChild(label);
                }

                // Crear imagen
                const img = document.createElement('img');
                img.classList.add('circular-hotspot-img');
                img.src = imageUrl;
                img.alt = displayText || 'hotspot';
                container.appendChild(img);

                hotSpotDiv.appendChild(container);
            }
            window.hotspotTooltipFunction = hotspotTooltipFunction;

            // --- Handler de clic en hotspot (global) ---
            function onHotspotClick(e, args) {
                if (window.walkToScene && args) {
                    window.walkToScene(args.targetSceneId, args.yaw, args.pitch);
                }
            }
            window.onHotspotClick = onHotspotClick;

            // --- Config generada en Blade (sin operadores "|")
            const pannellumConfig = {
                default: @json($pannellumDefault, $jsonOptions),
                scenes: @json($scenesConfig, $jsonOptions)
            };

            // --- Polígonos por escena ---
            const scenePolygons = @json($polygonsConfig ?? [], $jsonOptions);

            // --- Reconectar strings -> funciones reales (evita TypeError) ---
            Object.keys(pannellumConfig.scenes || {}).forEach(sceneId => {
                const hs = pannellumConfig.scenes[sceneId].hotSpots || [];
                hs.forEach(h => {
                    // Reconectar createTooltipFunc
                    if (typeof h.createTooltipFunc === 'string') {
                        const fn = window[h.createTooltipFunc];
                        if (typeof fn === 'function') {
                            h.createTooltipFunc = fn;
                        } else {
                            delete h.createTooltipFunc;
                        }
                    }
                    // Reconectar clickHandlerFunc
                    if (typeof h.clickHandlerFunc === 'string') {
                        const fn = window[h.clickHandlerFunc];
                        if (typeof fn === 'function') {
                            h.clickHandlerFunc = fn;
                        } else {
                            delete h.clickHandlerFunc;
                        }
                    }
                });
            });

            // --- Elementos de UI ---
            const $sceneNameDisplay = $('.current-scene-name');
            let isTransitioning = false;
            let pendingOrientation = null;

            // --- Función para mostrar nombre de escena ---
            function showSceneName(sceneName) {
                $sceneNameDisplay
                    .text(sceneName)
                    .removeClass('hidden')
                    .addClass('visible');

                setTimeout(function() {
                    $sceneNameDisplay.removeClass('visible').addClass('hidden');
                }, 2500);
            }

            // --- Inicializar visor ---
            let viewer = null;
            try {
                viewer = pannellum.viewer('pannellum', pannellumConfig);
            } catch (err) {
                console.error('[VT] Error inicializando Pannellum:', err);
                return;
            }
            window.viewer = viewer;

            // --- Overlay para transición suave ---
            var $transitionOverlay = $('<div id="transition-overlay"></div>').css({
                position: 'absolute',
                top: 0,
                left: 0,
                width: '100%',
                height: '100%',
                backgroundColor: '#000',
                opacity: 0,
                pointerEvents: 'none',
                zIndex: 1000,
                transition: 'none'
            });
            $('#pannellum').append($transitionOverlay);

            // --- SVG Overlay para polígonos de terreno ---
            var polygonSvgEl = null;
            console.log('[Polygons] Data:', JSON.stringify(scenePolygons));

            function ensurePolygonSvg() {
                // Si el SVG ya existe en el DOM, no recrear
                if (polygonSvgEl && document.contains(polygonSvgEl)) {
                    return true;
                }

                // Remover SVG huérfano si existe
                var old = document.getElementById('polygon-overlay');
                if (old && old.parentNode) old.parentNode.removeChild(old);
                polygonSvgEl = null;

                // Buscar el contenedor .pnlm-container (Pannellum agrega esta clase al #pannellum)
                var pnlmContainer = document.querySelector('#pannellum.pnlm-container');
                if (!pnlmContainer) {
                    pnlmContainer = document.querySelector('#pannellum .pnlm-render-container');
                    if (pnlmContainer) {
                        pnlmContainer = pnlmContainer.parentNode;
                    }
                }
                if (!pnlmContainer) {
                    pnlmContainer = document.getElementById('pannellum');
                }
                if (!pnlmContainer) {
                    console.warn('[Polygons] No se encontró contenedor para SVG');
                    return false;
                }

                polygonSvgEl = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                polygonSvgEl.setAttribute('id', 'polygon-overlay');
                polygonSvgEl.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:2;';
                pnlmContainer.appendChild(polygonSvgEl);
                console.log('[Polygons] SVG creado en:', pnlmContainer.id || pnlmContainer.className);
                return true;
            }

            // Función para obtener coordenadas de pantalla desde yaw/pitch
            // Implementación manual ya que pitchAndYawToScreen no existe en Pannellum 2.5.6
            function getPolygonScreenCoords(targetYaw, targetPitch) {
                if (!viewer) return null;
                try {
                    var vYaw = viewer.getYaw();
                    var vPitch = viewer.getPitch();
                    var hfov = viewer.getHfov();

                    var container = document.getElementById('pannellum');
                    var width = container.clientWidth;
                    var height = container.clientHeight;

                    if (width === 0 || height === 0) return null;

                    var yawRad = (targetYaw - vYaw) * Math.PI / 180;
                    var pitchRad = targetPitch * Math.PI / 180;
                    var vPitchRad = vPitch * Math.PI / 180;
                    var hfovRad = hfov * Math.PI / 180;

                    var x = Math.cos(pitchRad) * Math.sin(yawRad);
                    var y = Math.sin(pitchRad);
                    var z = Math.cos(pitchRad) * Math.cos(yawRad);

                    var cosPitch = Math.cos(vPitchRad);
                    var sinPitch = Math.sin(vPitchRad);
                    var x2 = x;
                    var y2 = y * cosPitch - z * sinPitch;
                    var z2 = y * sinPitch + z * cosPitch;

                    if (z2 <= 0.01) return null;

                    var focalLength = width / (2 * Math.tan(hfovRad / 2));
                    var screenX = (x2 / z2) * focalLength + width / 2;
                    var screenY = -(y2 / z2) * focalLength + height / 2;

                    if (screenX < -50 || screenX > width + 50 || screenY < -50 || screenY > height + 50) {
                        return null;
                    }

                    return { x: screenX, y: screenY };
                } catch(e) {
                    console.warn('[Polygons] Error en coordenadas:', e);
                }
                return null;
            }

            // Función para renderizar polígonos de la escena actual
            function renderScenePolygons() {
                // Auto-crear SVG si no existe o fue removido del DOM
                if (!polygonSvgEl || !document.contains(polygonSvgEl)) {
                    if (!ensurePolygonSvg()) return;
                }

                var svg = polygonSvgEl;
                // Limpiar SVG
                while (svg.firstChild) {
                    svg.removeChild(svg.firstChild);
                }

                var currentSceneId = String(viewer.getScene());
                var polygons = scenePolygons[currentSceneId] || [];

                polygons.forEach(function(poly) {
                    // Manejar points como string o array
                    var points = poly.points;
                    if (!points) return;
                    if (typeof points === 'string') {
                        try { points = JSON.parse(points); } catch(e) { return; }
                    }
                    if (!Array.isArray(points) || points.length < 3) return;

                    var screenPoints = [];
                    var pathData = '';
                    var validPoints = 0;

                    points.forEach(function(p, i) {
                        var screenCoords = getPolygonScreenCoords(p.yaw, p.pitch);
                        screenPoints.push(screenCoords);
                        if (screenCoords) {
                            if (validPoints === 0) {
                                pathData += 'M ' + screenCoords.x + ' ' + screenCoords.y + ' ';
                            } else {
                                pathData += 'L ' + screenCoords.x + ' ' + screenCoords.y + ' ';
                            }
                            validPoints++;
                        }
                    });

                    if (validPoints >= 3) {
                        pathData += 'Z';

                        var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        path.setAttribute('d', pathData);
                        path.setAttribute('fill', poly.fillColor);
                        path.setAttribute('fill-opacity', poly.fillOpacity);
                        path.setAttribute('stroke', poly.strokeColor);
                        path.setAttribute('stroke-width', poly.strokeWidth);
                        path.setAttribute('data-polygon-id', poly.id);
                        path.setAttribute('data-polygon-name', poly.name);
                        svg.appendChild(path);

                        // Dibujar medidas en los lados
                        var edgeLabels = poly.edgeLabels;
                        if (typeof edgeLabels === 'string') {
                            try { edgeLabels = JSON.parse(edgeLabels); } catch(e) { edgeLabels = null; }
                        }
                        if (edgeLabels && Array.isArray(edgeLabels)) {
                            drawPolygonEdgeLabels(svg, screenPoints, edgeLabels);
                        }

                        // Dibujar texto interior
                        if (poly.interiorText) {
                            drawPolygonInteriorText(svg, screenPoints, poly.interiorText);
                        }
                    }
                });
            }

            // Dibujar medidas en los lados del polígono
            function drawPolygonEdgeLabels(svg, screenPoints, labels) {
                var numPoints = screenPoints.length;
                for (var i = 0; i < numPoints; i++) {
                    var label = labels[i];
                    if (!label) continue;

                    var p1 = screenPoints[i];
                    var p2 = screenPoints[(i + 1) % numPoints];
                    if (!p1 || !p2) continue;

                    var mx = (p1.x + p2.x) / 2;
                    var my = (p1.y + p2.y) / 2;

                    var angle = Math.atan2(p2.y - p1.y, p2.x - p1.x) * 180 / Math.PI;
                    if (angle > 90) angle -= 180;
                    if (angle < -90) angle += 180;

                    // Offset perpendicular para no montar sobre la línea
                    var perpAngle = (angle + 90) * Math.PI / 180;
                    var offsetDist = 12;
                    var ox = mx + Math.cos(perpAngle) * offsetDist;
                    var oy = my + Math.sin(perpAngle) * offsetDist;

                    var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                    text.setAttribute('x', ox);
                    text.setAttribute('y', oy);
                    text.setAttribute('fill', '#FFFFFF');
                    text.setAttribute('font-size', '12');
                    text.setAttribute('font-weight', 'bold');
                    text.setAttribute('font-family', 'Arial, sans-serif');
                    text.setAttribute('text-anchor', 'middle');
                    text.setAttribute('dominant-baseline', 'middle');
                    text.setAttribute('transform', 'rotate(' + angle + ' ' + ox + ' ' + oy + ')');
                    text.setAttribute('paint-order', 'stroke');
                    text.setAttribute('stroke', 'rgba(0,0,0,0.7)');
                    text.setAttribute('stroke-width', '3');
                    text.setAttribute('stroke-linecap', 'round');
                    text.setAttribute('stroke-linejoin', 'round');
                    text.textContent = label;
                    svg.appendChild(text);
                }
            }

            // Dibujar texto interior del polígono
            function drawPolygonInteriorText(svg, screenPoints, text) {
                var sumX = 0, sumY = 0, count = 0;
                screenPoints.forEach(function(p) {
                    if (p) { sumX += p.x; sumY += p.y; count++; }
                });
                if (count < 3) return;

                var cx = sumX / count;
                var cy = sumY / count;

                var textEl = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                textEl.setAttribute('x', cx);
                textEl.setAttribute('y', cy);
                textEl.setAttribute('fill', '#FFFFFF');
                textEl.setAttribute('font-size', '14');
                textEl.setAttribute('font-weight', 'bold');
                textEl.setAttribute('font-family', 'Arial, sans-serif');
                textEl.setAttribute('text-anchor', 'middle');
                textEl.setAttribute('dominant-baseline', 'middle');
                textEl.setAttribute('paint-order', 'stroke');
                textEl.setAttribute('stroke', 'rgba(0,0,0,0.7)');
                textEl.setAttribute('stroke-width', '4');
                textEl.setAttribute('stroke-linecap', 'round');
                textEl.setAttribute('stroke-linejoin', 'round');
                textEl.textContent = text;
                svg.appendChild(textEl);
            }

            // Actualizar polígonos periódicamente mientras se navega
            var polygonUpdateInterval = null;
            function startPolygonUpdates() {
                if (polygonUpdateInterval) return;
                polygonUpdateInterval = setInterval(function() {
                    if (!isTransitioning) {
                        renderScenePolygons();
                    }
                }, 50);
            }

            // Iniciar actualizaciones de polígonos
            startPolygonUpdates();

            // ===== VIDEO DRON ORBITAL - Drag-to-scrub (optimizado) =====
            var videoOverlay = document.getElementById('video-viewer-overlay');
            var droneVideo = document.getElementById('drone-video');
            var videoProgressFill = document.getElementById('video-progress-fill');
            var videoScrubIndicator = document.getElementById('video-scrub-indicator');
            var videoHotspotsBar = document.getElementById('video-hotspots-bar');
            var videoDragHint = document.getElementById('video-drag-hint');
            var currentVideoSceneId = null;
            var videoReady = false;
            var videoDragState = {
                isDragging: false,
                startX: 0,
                startTime: 0,
                sensitivity: 0.04, // seconds per pixel of drag
                pendingSeek: null  // throttle seeks via rAF
            };

            // Función de seek optimizada: usa fastSeek cuando está disponible
            function seekVideo(time) {
                if (!droneVideo.duration || droneVideo.duration === 0) return;
                // Wrap around para orbitar continuamente
                while (time < 0) time += droneVideo.duration;
                while (time >= droneVideo.duration) time -= droneVideo.duration;
                time = Math.max(0, Math.min(droneVideo.duration, time));

                if (typeof droneVideo.fastSeek === 'function') {
                    droneVideo.fastSeek(time);
                } else {
                    droneVideo.currentTime = time;
                }
            }

            // Throttle de seeks: solo aplica un seek por frame de animación
            function scheduleSeek(time) {
                videoDragState.pendingSeek = time;
                if (!videoDragState._rafId) {
                    videoDragState._rafId = requestAnimationFrame(function applySeek() {
                        videoDragState._rafId = null;
                        if (videoDragState.pendingSeek !== null) {
                            seekVideo(videoDragState.pendingSeek);
                            // Actualizar indicador
                            var pct = droneVideo.duration ? Math.round((droneVideo.currentTime / droneVideo.duration) * 100) : 0;
                            videoScrubIndicator.textContent = pct + '%';
                            videoDragState.pendingSeek = null;
                        }
                    });
                }
            }

            function isVideoScene(sceneId) {
                var sc = pannellumConfig.scenes[sceneId];
                return sc && sc.type === 'video' && sc.video;
            }

            function showVideoViewer(sceneId) {
                var sc = pannellumConfig.scenes[sceneId];
                if (!sc || !sc.video) return;

                currentVideoSceneId = sceneId;
                videoReady = false;

                // Mostrar overlay con indicador de carga
                videoOverlay.style.display = 'block';
                videoDragHint.textContent = 'Cargando video...';
                videoDragHint.classList.add('visible');

                // Cargar video
                droneVideo.src = sc.video;
                droneVideo.load();

                // Cuando el video tiene suficientes datos para hacer seek
                droneVideo.oncanplay = function() {
                    if (videoReady) return;
                    videoReady = true;
                    droneVideo.currentTime = 0;

                    // Mostrar hint de arrastre
                    videoDragHint.innerHTML = '<i class="fa fa-arrows-h"></i> Arrastra para girar alrededor de la propiedad';
                    setTimeout(function() {
                        videoDragHint.classList.remove('visible');
                    }, 3000);
                };

                // Eventos de buffering
                droneVideo.onwaiting = function() {
                    videoDragHint.textContent = 'Cargando...';
                    videoDragHint.classList.add('visible');
                };
                droneVideo.oncanplaythrough = function() {
                    if (videoDragHint.textContent === 'Cargando...') {
                        videoDragHint.classList.remove('visible');
                    }
                };

                // Generar hotspots posicionados en el video
                buildVideoHotspots(sceneId);

                // Actualizar barra de progreso
                updateVideoProgress();
            }

            function hideVideoViewer() {
                videoOverlay.style.display = 'none';
                currentVideoSceneId = null;
                videoReady = false;
                droneVideo.oncanplay = null;
                droneVideo.onwaiting = null;
                droneVideo.oncanplaythrough = null;
                droneVideo.pause();
                droneVideo.removeAttribute('src');
                droneVideo.load();
                videoHotspotsBar.innerHTML = '';
                // Remover hotspots posicionados
                var posHotspots = videoOverlay.querySelectorAll('.video-pos-hotspot');
                posHotspots.forEach(function(el) { el.remove(); });
            }

            function buildVideoHotspots(sceneId) {
                videoHotspotsBar.innerHTML = '';
                // Remover hotspots posicionados previos
                var oldPosHotspots = videoOverlay.querySelectorAll('.video-pos-hotspot');
                oldPosHotspots.forEach(function(el) { el.remove(); });

                var sc = pannellumConfig.scenes[sceneId];
                if (!sc || !sc.hotSpots) return;

                sc.hotSpots.forEach(function(hs) {
                    if (!hs.clickHandlerArgs || !hs.clickHandlerArgs.targetSceneId) return;
                    var targetId = hs.clickHandlerArgs.targetSceneId;
                    var targetScene = pannellumConfig.scenes[targetId];
                    if (!targetScene) return;

                    var displayText = hs.createTooltipArgs ? hs.createTooltipArgs.displayText : targetScene.title;
                    var imageUrl = hs.createTooltipArgs ? hs.createTooltipArgs.imageUrl : null;

                    // Verificar si tiene posición en video (video_time + pos_x + pos_y)
                    var vt = hs.createTooltipArgs ? hs.createTooltipArgs.videoTime : null;
                    var px = hs.createTooltipArgs ? hs.createTooltipArgs.posX : null;
                    var py = hs.createTooltipArgs ? hs.createTooltipArgs.posY : null;

                    if (vt !== null && vt !== undefined && px !== null && py !== null) {
                        // Hotspot posicionado en el video
                        var posDiv = document.createElement('div');
                        posDiv.className = 'video-pos-hotspot';
                        posDiv.style.left = px + '%';
                        posDiv.style.top = py + '%';
                        posDiv.setAttribute('data-video-time', vt);
                        posDiv.setAttribute('data-time-range', '3'); // visible ±3 segundos

                        var container = document.createElement('div');
                        container.classList.add('hotspot-tooltip-container');

                        var label = document.createElement('div');
                        label.classList.add('hotspot-label', 'hotspot-label-scene');
                        label.textContent = displayText;
                        container.appendChild(label);

                        if (imageUrl) {
                            var img = document.createElement('img');
                            img.classList.add('circular-hotspot-img');
                            img.src = imageUrl;
                            img.alt = displayText;
                            container.appendChild(img);
                        }

                        posDiv.appendChild(container);
                        posDiv.addEventListener('click', function(e) {
                            e.stopPropagation();
                            navigateFromVideo(targetId);
                        });
                        videoOverlay.appendChild(posDiv);
                    } else {
                        // Hotspot sin posición en video → botón en barra inferior
                        var btn = document.createElement('button');
                        btn.className = 'video-hotspot-btn';
                        btn.textContent = displayText;
                        btn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            navigateFromVideo(targetId);
                        });
                        videoHotspotsBar.appendChild(btn);
                    }
                });
            }

            // Actualizar visibilidad de hotspots posicionados según tiempo del video
            function updateVideoPositionedHotspots() {
                if (!currentVideoSceneId) return;
                var currentTime = droneVideo.currentTime;
                var duration = droneVideo.duration || 0;
                var posHotspots = videoOverlay.querySelectorAll('.video-pos-hotspot');

                posHotspots.forEach(function(el) {
                    var vt = parseFloat(el.getAttribute('data-video-time'));
                    var range = parseFloat(el.getAttribute('data-time-range')) || 3;

                    // Calcular distancia temporal (con wrap-around)
                    var diff = Math.abs(currentTime - vt);
                    if (duration > 0) {
                        diff = Math.min(diff, duration - diff);
                    }

                    if (diff <= range) {
                        el.style.display = 'block';
                        var opacity = 1 - (diff / range) * 0.6; // fade basado en proximidad
                        el.style.opacity = opacity;
                    } else {
                        el.style.display = 'none';
                    }
                });
            }

            function navigateFromVideo(targetSceneId) {
                if (isTransitioning) return;
                isTransitioning = true;

                // Fade out video
                $transitionOverlay.css({ opacity: 0, transition: 'opacity 0.4s ease' });
                setTimeout(function() {
                    $transitionOverlay.css('opacity', 1);
                }, 10);

                setTimeout(function() {
                    hideVideoViewer();
                    viewer.loadScene(targetSceneId);
                    $transitionOverlay.css({ transition: 'opacity 0.6s ease' });
                    setTimeout(function() {
                        $transitionOverlay.css('opacity', 0);
                        setTimeout(function() {
                            $transitionOverlay.css('transition', 'none');
                            isTransitioning = false;
                        }, 600);
                    }, 100);
                }, 500);
            }

            function updateVideoProgress() {
                if (!currentVideoSceneId) return;
                if (droneVideo.duration && droneVideo.duration > 0) {
                    var pct = (droneVideo.currentTime / droneVideo.duration) * 100;
                    videoProgressFill.style.width = pct + '%';
                }
                // Actualizar hotspots posicionados
                updateVideoPositionedHotspots();
                requestAnimationFrame(updateVideoProgress);
            }

            // --- Drag-to-scrub: Mouse events (optimizado con throttle) ---
            videoOverlay.addEventListener('mousedown', function(e) {
                if (e.target.closest('.video-hotspot-btn, .video-pos-hotspot')) return;
                if (!videoReady) return;
                videoDragState.isDragging = true;
                videoDragState.startX = e.clientX;
                videoDragState.startTime = droneVideo.currentTime;
                videoOverlay.classList.add('active-dragging');
                videoScrubIndicator.classList.add('visible');
                e.preventDefault();
            });

            document.addEventListener('mousemove', function(e) {
                if (!videoDragState.isDragging) return;
                var deltaX = e.clientX - videoDragState.startX;
                var newTime = videoDragState.startTime + deltaX * videoDragState.sensitivity;
                scheduleSeek(newTime);
            });

            document.addEventListener('mouseup', function() {
                if (videoDragState.isDragging) {
                    videoDragState.isDragging = false;
                    videoOverlay.classList.remove('active-dragging');
                    setTimeout(function() {
                        videoScrubIndicator.classList.remove('visible');
                    }, 800);
                }
            });

            // --- Drag-to-scrub: Touch events (mobile, optimizado) ---
            videoOverlay.addEventListener('touchstart', function(e) {
                if (e.target.closest('.video-hotspot-btn, .video-pos-hotspot')) return;
                if (!videoReady) return;
                var touch = e.touches[0];
                videoDragState.isDragging = true;
                videoDragState.startX = touch.clientX;
                videoDragState.startTime = droneVideo.currentTime;
                videoOverlay.classList.add('active-dragging');
                videoScrubIndicator.classList.add('visible');
            }, { passive: true });

            document.addEventListener('touchmove', function(e) {
                if (!videoDragState.isDragging) return;
                var touch = e.touches[0];
                var deltaX = touch.clientX - videoDragState.startX;
                var newTime = videoDragState.startTime + deltaX * videoDragState.sensitivity;
                scheduleSeek(newTime);
            }, { passive: true });

            document.addEventListener('touchend', function() {
                if (videoDragState.isDragging) {
                    videoDragState.isDragging = false;
                    videoOverlay.classList.remove('active-dragging');
                    setTimeout(function() {
                        videoScrubIndicator.classList.remove('visible');
                    }, 800);
                }
            });

            // --- Efecto de caminar: zoom continuo sin girar ---
            window.walkToScene = function(targetSceneId, hotspotYaw, hotspotPitch) {
                if (isTransitioning) return;
                isTransitioning = true;

                var startHfov = viewer.getHfov();

                // Zoom muy profundo para simular caminar hasta casi llegar a la escena
                var minHfov = 2;
                var zoomInDuration = 1200;
                var fadeDuration = 300;
                var startTime = Date.now();

                // Obtener el yaw/pitch configurado de la escena destino
                var targetScene = pannellumConfig.scenes[targetSceneId];
                var targetYaw = targetScene ? targetScene.yaw : 0;
                var targetPitch = targetScene ? targetScene.pitch : 0;

                // Guardar datos para la nueva escena (usar yaw/pitch de la escena, no del hotspot)
                pendingOrientation = {
                    yaw: targetYaw,
                    pitch: targetPitch,
                    hfov: startHfov,
                    minHfov: minHfov
                };

                // Zoom IN continuo (sin girar, solo acercarse)
                function animateWalkIn() {
                    var elapsed = Date.now() - startTime;
                    var progress = Math.min(elapsed / zoomInDuration, 1);

                    // Easing: empieza lento, acelera (como caminar)
                    var eased = progress * progress * progress;

                    // Solo interpolar el zoom, no la rotación
                    var newHfov = startHfov - ((startHfov - minHfov) * eased);
                    viewer.setHfov(newHfov);

                    // Fade in del overlay en el último 25% del zoom
                    if (progress > 0.75) {
                        var fadeProgress = (progress - 0.75) / 0.25;
                        $transitionOverlay.css('opacity', fadeProgress * 0.9);
                    }

                    if (progress < 1) {
                        requestAnimationFrame(animateWalkIn);
                    } else {
                        // Pequeña pausa antes de cambiar escena
                        setTimeout(function() {
                            viewer.loadScene(targetSceneId);
                        }, 100);
                    }
                }

                requestAnimationFrame(animateWalkIn);
            }

            // --- Cuando la escena carga, continuar el zoom out ---
            viewer.on('load', function() {
                var loadedSceneId = String(viewer.getScene());

                // Verificar si es una escena de video
                if (isVideoScene(loadedSceneId)) {
                    console.log('[Video] Escena de video cargada:', loadedSceneId);
                    showVideoViewer(loadedSceneId);
                    // Mostrar nombre de escena
                    var sceneName = pannellumConfig.scenes[loadedSceneId]?.title || 'Escena';
                    showSceneName(sceneName);
                    // Completar transición pendiente
                    if (pendingOrientation) {
                        $transitionOverlay.css('opacity', 0);
                        isTransitioning = false;
                        pendingOrientation = null;
                    }
                    return;
                }

                // Escena 360 normal - ocultar video si estaba activo
                if (currentVideoSceneId) {
                    hideVideoViewer();
                }

                // Recrear SVG de polígonos dentro del contenedor Pannellum
                ensurePolygonSvg();
                console.log('[Polygons] Escena cargada:', viewer.getScene(), '- Polígonos disponibles:', (scenePolygons[String(viewer.getScene())] || []).length);
                renderScenePolygons();

                if (pendingOrientation) {
                    // Aplicar orientación y mantener zoom cercano
                    viewer.setYaw(pendingOrientation.yaw);
                    viewer.setPitch(pendingOrientation.pitch);
                    viewer.setHfov(pendingOrientation.minHfov);

                    // Zoom OUT continuo (simula llegar al destino)
                    var startHfov = pendingOrientation.minHfov;
                    var targetHfov = pendingOrientation.hfov;
                    var zoomOutDuration = 800;
                    var startTime = Date.now();

                    function animateWalkOut() {
                        var elapsed = Date.now() - startTime;
                        var progress = Math.min(elapsed / zoomOutDuration, 1);

                        // Easing: empieza rápido, desacelera (como llegar)
                        var eased = 1 - Math.pow(1 - progress, 3);

                        var newHfov = startHfov + ((targetHfov - startHfov) * eased);
                        viewer.setHfov(newHfov);

                        // Fade out del overlay en el primer 40% del zoom out
                        if (progress < 0.4) {
                            var fadeProgress = 1 - (progress / 0.4);
                            $transitionOverlay.css('opacity', fadeProgress * 0.9);
                        } else {
                            $transitionOverlay.css('opacity', 0);
                        }

                        if (progress < 1) {
                            requestAnimationFrame(animateWalkOut);
                        } else {
                            isTransitioning = false;
                            pendingOrientation = null;
                            $transitionOverlay.css('opacity', 0);
                        }
                    }

                    requestAnimationFrame(animateWalkOut);
                } else {
                    isTransitioning = false;
                    $transitionOverlay.css('opacity', 0);
                }

                var currentScene = viewer.getScene();
                if (currentScene && pannellumConfig.scenes[currentScene]) {
                    showSceneName(pannellumConfig.scenes[currentScene].title);
                }
            });

            // --- Eventos de Pannellum ---
            viewer.on('scenechange', function(sceneId) {
                var sceneName = pannellumConfig.scenes[sceneId]?.title || 'Escena';
                showSceneName(sceneName);

                setTimeout(function() {
                    preloadAdjacentScenes(sceneId);
                }, 500);
            });

            // --- UI / Interacciones ---
            $(document).on('click', '#btn-start-tour', function(e) {
                e.preventDefault();
                $('.home-content-table').fadeOut(800, function() {
                    var firstSceneId = pannellumConfig.default.firstScene;
                    if (pannellumConfig.scenes[firstSceneId]) {
                        showSceneName(pannellumConfig.scenes[firstSceneId].title);
                        // Si la primera escena es video, mostrar el visor de video
                        if (isVideoScene(firstSceneId)) {
                            showVideoViewer(firstSceneId);
                        }
                    }
                });
            });

            $(document).on('click', '#menu-trigger-ctrl-map', function() {
                $('#denahModal').modal('show');
            });

            // Cambiar escena desde menú lateral (con zoom)
            document.addEventListener('click', function(e) {
                var link = e.target.closest ? e.target.closest('a.js-load-scene') : null;
                if (!link) return;

                e.preventDefault();
                var sceneId = link.getAttribute('data-scene-id');

                if (sceneId && window.viewer && !isTransitioning) {
                    isTransitioning = true;

                    // Cerrar el menú lateral
                    $('#menu-trigger-ctrl').removeClass('is-clicked');
                    $('body').removeClass('menu-is-open');

                    // Si estamos en un video scene, navegar con fade en vez de zoom
                    if (currentVideoSceneId) {
                        $transitionOverlay.css({ opacity: 0, transition: 'opacity 0.4s ease' });
                        setTimeout(function() {
                            $transitionOverlay.css('opacity', 1);
                        }, 10);
                        setTimeout(function() {
                            hideVideoViewer();
                            pendingOrientation = null;
                            viewer.loadScene(sceneId);
                            $transitionOverlay.css({ transition: 'opacity 0.6s ease' });
                            setTimeout(function() {
                                $transitionOverlay.css('opacity', 0);
                                setTimeout(function() {
                                    $transitionOverlay.css('transition', 'none');
                                    isTransitioning = false;
                                }, 600);
                            }, 100);
                        }, 500);
                        return;
                    }

                    // Guardar orientación para zoom out
                    pendingOrientation = {
                        yaw: viewer.getYaw(),
                        pitch: viewer.getPitch(),
                        hfov: viewer.getHfov(),
                        minHfov: 15
                    };

                    // Zoom in rápido, luego cambiar escena
                    var startHfov = viewer.getHfov();
                    var minHfov = 15;
                    var zoomDuration = 400;
                    var startTime = Date.now();

                    function zoomAndChange() {
                        var elapsed = Date.now() - startTime;
                        var progress = Math.min(elapsed / zoomDuration, 1);
                        var eased = progress * progress;

                        var newHfov = startHfov - ((startHfov - minHfov) * eased);
                        viewer.setHfov(newHfov);

                        if (progress < 1) {
                            requestAnimationFrame(zoomAndChange);
                        } else {
                            viewer.loadScene(sceneId);
                        }
                    }

                    requestAnimationFrame(zoomAndChange);
                }
            }, true);

            // Pre-cargar imágenes de escenas adyacentes
            function preloadAdjacentScenes(currentSceneId) {
                var currentScene = pannellumConfig.scenes[currentSceneId];
                if (!currentScene || !currentScene.hotSpots) return;

                currentScene.hotSpots.forEach(function(hs) {
                    if (hs.sceneId && pannellumConfig.scenes[hs.sceneId]) {
                        var img = new Image();
                        img.src = pannellumConfig.scenes[hs.sceneId].panorama;
                    }
                });
            }

            setTimeout(function() {
                preloadAdjacentScenes(pannellumConfig.default.firstScene);
            }, 1000);
        })();
    </script>

</body>

</html>
