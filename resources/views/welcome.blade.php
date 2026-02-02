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

        /* Contenedor del visor con transiciones suaves */
        #pannellum {
            position: relative;
        }

        /* Overlay para efecto de transición suave */
        .walk-transition-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(ellipse at center,
                rgba(0,0,0,0) 0%,
                rgba(0,0,0,0.4) 50%,
                rgba(0,0,0,0.8) 100%);
            opacity: 0;
            pointer-events: none;
            z-index: 998;
            transition: opacity 0.3s ease-out;
        }

        .walk-transition-overlay.active {
            opacity: 1;
        }

        /* Efecto de movimiento hacia adelante */
        .pnlm-render-container {
            transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
        }

        .walking .pnlm-render-container {
            transform: scale(1.08);
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

        // 2) Default Pannellum - Configuración optimizada para efecto de "caminar"
        $pannellumDefault = [
            'firstScene' => (string) $fscene->id,
            'hfov' => 100,
            'minHfov' => 30,                      // Permitir zoom más cercano
            'maxHfov' => 120,
            'autoLoad' => true,
            'sceneFadeDuration' => 0,             // Sin fade - usamos zoom
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
                    'type' => $hotspot->type,
                    'createTooltipFunc' => 'hotspotTooltipFunction',
                    'createTooltipArgs' => [
                        'imageUrl' => isset($hotspot->image)
                            ? route('file', $hotspot->image)
                            : url('images/producto-sin-imagen.PNG'),
                        'displayText' => $displayText,
                        'hotspotType' => $hotspot->type
                    ],
                    'text' => $hotspot->info,
                ];
                // Solo agregar sceneId si es tipo 'scene' (enlace) - esto permite navegar
                if ($hotspot->type === 'scene' && $hotspot->targetScene) {
                    $hs['sceneId'] = (string) $hotspot->targetScene;
                }
                $hotspotsForScene[] = $hs;
            }

            $scenesConfig[(string) $scene->id] = [
                'title' => $scene->title,
                'hfov' => (float) $scene->hfov,
                'pitch' => (float) $scene->pitch,
                'yaw' => (float) $scene->yaw + 270,
                'type' => $scene->type,
                'panorama' => isset($scene->image)
                    ? route('file', $scene->image)
                    : url('images/producto-sin-imagen.PNG'),
                'hotSpots' => $hotspotsForScene,
            ];
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

            // --- Config generada en Blade (sin operadores "|")
            const pannellumConfig = {
                default: @json($pannellumDefault, $jsonOptions),
                scenes: @json($scenesConfig, $jsonOptions)
            };

            // --- Reconectar strings -> funciones reales (evita TypeError) ---
            Object.keys(pannellumConfig.scenes || {}).forEach(sceneId => {
                const hs = pannellumConfig.scenes[sceneId].hotSpots || [];
                hs.forEach(h => {
                    if (typeof h.createTooltipFunc === 'string') {
                        const fn = window[h.createTooltipFunc];
                        if (typeof fn === 'function') {
                            h.createTooltipFunc = fn;
                        } else {
                            console.warn('[VT] Tooltip function not found:', h.createTooltipFunc,
                                'in scene', sceneId);
                            delete h.createTooltipFunc;
                        }
                    }
                });
            });

            // --- Elementos de UI ---
            const $sceneNameDisplay = $('.current-scene-name');
            const $pannellumContainer = $('#pannellum');
            const $transitionOverlay = $('.walk-transition-overlay');
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

            // --- Efecto de "caminar" con zoom continuo hacia el hotspot ---
            function walkToScene(targetSceneId, hotspotYaw, hotspotPitch) {
                if (isTransitioning) return;
                isTransitioning = true;

                var startHfov = viewer.getHfov();
                var startYaw = viewer.getYaw();
                var startPitch = viewer.getPitch();

                // Fase 1: Zoom hacia el hotspot (simula caminar hacia él)
                var zoomDuration = 800; // Duración del zoom
                var targetHfov = 30; // Zoom muy cercano (campo de visión estrecho = más cerca)
                var startTime = Date.now();

                // Calcular la ruta más corta para el yaw
                var deltaYaw = hotspotYaw - startYaw;
                if (deltaYaw > 180) deltaYaw -= 360;
                if (deltaYaw < -180) deltaYaw += 360;

                // Calcular orientación de llegada
                var arrivalYaw = hotspotYaw + 180;
                if (arrivalYaw > 180) arrivalYaw -= 360;
                if (arrivalYaw < -180) arrivalYaw += 360;

                // Guardar orientación para la nueva escena
                pendingOrientation = {
                    yaw: arrivalYaw,
                    pitch: 0,
                    hfov: startHfov,
                    needsZoomOut: true // Indicar que necesita animación de zoom out
                };

                function animateZoomIn() {
                    var elapsed = Date.now() - startTime;
                    var progress = Math.min(elapsed / zoomDuration, 1);

                    // Easing: acelerar al principio, mantener velocidad al final
                    var eased = 1 - Math.pow(1 - progress, 2);

                    // Interpolar hacia el hotspot
                    var newYaw = startYaw + (deltaYaw * eased);
                    var newPitch = startPitch + ((hotspotPitch - startPitch) * eased);
                    var newHfov = startHfov + ((targetHfov - startHfov) * eased);

                    viewer.setYaw(newYaw);
                    viewer.setPitch(newPitch);
                    viewer.setHfov(newHfov);

                    if (progress < 1) {
                        requestAnimationFrame(animateZoomIn);
                    } else {
                        // Cuando el zoom llega al máximo, cambiar escena
                        // Aplicar un pequeño blur/fade para suavizar
                        $transitionOverlay.addClass('active');

                        setTimeout(function() {
                            viewer.loadScene(targetSceneId);
                        }, 100);
                    }
                }

                requestAnimationFrame(animateZoomIn);
            }

            // --- Aplicar orientación y zoom out cuando la escena termine de cargar ---
            viewer.on('load', function() {
                if (pendingOrientation) {
                    // Aplicar orientación inmediatamente
                    viewer.setYaw(pendingOrientation.yaw);
                    viewer.setPitch(pendingOrientation.pitch);

                    if (pendingOrientation.needsZoomOut) {
                        // Iniciar con zoom cercano y animar hacia zoom normal
                        var startHfov = 30; // Empezar con zoom cercano
                        var targetHfov = pendingOrientation.hfov;
                        var zoomOutDuration = 600;
                        var startTime = Date.now();

                        viewer.setHfov(startHfov);
                        $transitionOverlay.removeClass('active');

                        function animateZoomOut() {
                            var elapsed = Date.now() - startTime;
                            var progress = Math.min(elapsed / zoomOutDuration, 1);

                            // Easing suave
                            var eased = 1 - Math.pow(1 - progress, 3);

                            var newHfov = startHfov + ((targetHfov - startHfov) * eased);
                            viewer.setHfov(newHfov);

                            if (progress < 1) {
                                requestAnimationFrame(animateZoomOut);
                            } else {
                                isTransitioning = false;
                                pendingOrientation = null;
                            }
                        }

                        requestAnimationFrame(animateZoomOut);
                    } else {
                        viewer.setHfov(pendingOrientation.hfov);
                        $transitionOverlay.removeClass('active');
                        isTransitioning = false;
                        pendingOrientation = null;
                    }
                } else {
                    $transitionOverlay.removeClass('active');
                    isTransitioning = false;
                }

                var currentScene = viewer.getScene();
                if (currentScene && pannellumConfig.scenes[currentScene]) {
                    showSceneName(pannellumConfig.scenes[currentScene].title);
                }
            });

            // --- Interceptar clics en hotspots de tipo scene ---
            $pannellumContainer.on('click', '.pnlm-hotspot', function(e) {
                if (isTransitioning) return;

                var currentSceneId = viewer.getScene();
                var currentScene = pannellumConfig.scenes[currentSceneId];
                if (!currentScene || !currentScene.hotSpots) return;

                var coords = viewer.mouseEventToCoords(e.originalEvent);
                if (!coords) return;

                var clickPitch = coords[0];
                var clickYaw = coords[1];

                var closestHotspot = null;
                var minDistance = Infinity;

                currentScene.hotSpots.forEach(function(hs) {
                    if (hs.type === 'scene' && hs.sceneId) {
                        var distance = Math.sqrt(
                            Math.pow(hs.pitch - clickPitch, 2) +
                            Math.pow(hs.yaw - clickYaw, 2)
                        );
                        if (distance < minDistance) {
                            minDistance = distance;
                            closestHotspot = hs;
                        }
                    }
                });

                if (closestHotspot && closestHotspot.sceneId && minDistance < 50) {
                    e.preventDefault();
                    e.stopPropagation();
                    walkToScene(closestHotspot.sceneId, closestHotspot.yaw, closestHotspot.pitch);
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
                    }
                });
            });

            $(document).on('click', '#menu-trigger-ctrl-map', function() {
                $('#denahModal').modal('show');
            });

            // Cambiar escena desde menú lateral
            document.addEventListener('click', function(e) {
                var link = e.target.closest ? e.target.closest('a.js-load-scene') : null;
                if (!link) return;

                e.preventDefault();
                var sceneId = link.getAttribute('data-scene-id');

                if (sceneId && window.viewer && !isTransitioning) {
                    isTransitioning = true;
                    $transitionOverlay.addClass('active');

                    setTimeout(function() {
                        viewer.loadScene(sceneId);
                        $('#menu-trigger-ctrl').removeClass('is-clicked');
                        $('body').removeClass('menu-is-open');
                    }, 200);
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
