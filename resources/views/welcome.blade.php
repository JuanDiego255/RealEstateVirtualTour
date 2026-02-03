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
                        'hotspotType' => $hotspot->type
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
