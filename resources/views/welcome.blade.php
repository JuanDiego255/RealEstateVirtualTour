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

        // 2) Default Pannellum (ajusta si ya lo tienes)
        $pannellumDefault = [
            'firstScene' => (string) $fscene->id,
            'hfov' => -1000,
            'autoLoad' => true,
            'sceneFadeDuration' => 2000,
            'autoRotate' => -1,
            'resolution' => 4096,
            'autoRotateInactivityDelay' => 30000,
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

            // --- Inicializar visor ---
            let viewer = null;
            try {
                viewer = pannellum.viewer('pannellum', pannellumConfig);
            } catch (err) {
                console.error('[VT] Error inicializando Pannellum:', err);
                return;
            }
            window.viewer = viewer;

            // --- UI / Interacciones ---
            // Cerrar overlay inicio
            $(document).on('click', '#btn-start-tour', function(e) {
                e.preventDefault();
                $('.home-content-table').fadeOut(1000);
            });

            // Abrir modal de mapa
            $(document).on('click', '#menu-trigger-ctrl-map', function() {
                $('#denahModal').modal('show');
            });

            // Cambiar escena desde menú
            document.addEventListener('click', function(e) {
                var link = e.target.closest ? e.target.closest('a.js-load-scene') : null;
                if (!link) return;

                e.preventDefault(); // no navegues a "#"
                var sceneId = link.getAttribute('data-scene-id');
                console.log('[VT] load-scene →', sceneId);

                if (sceneId && window.viewer) {
                    try {
                        window.viewer.loadScene(sceneId);
                    } catch (err) {
                        console.error('[VT] No se pudo cargar la escena', sceneId, err);
                    }
                } else {
                    console.warn('[VT] Sin sceneId o viewer no disponible');
                }

                // Si usas un botón para cerrar el menú lateral:
                if (window.$) $('.close-button').trigger('click');
            }, true); // ← fase de captura

            // (Opcional) tooltips Bootstrap
            if (typeof $().tooltip === 'function') {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        })();
    </script>

</body>

</html>
