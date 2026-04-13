<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Virtual Tour | Space 360 CR</title>

    {{-- Bootstrap CSS --}}
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" media="all">
    {{-- Icon --}}
    <link rel="icon" href="{{ asset('img/space360icon.png') }}">

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

        /* ── Floor-projection hotspot (sin imagen personalizada) ── */

        /* Contenedor: aplica la perspectiva para dar el efecto 3D de suelo.
           rotateX aplana el círculo en el eje vertical de pantalla,
           haciendo que parezca un óvalo acostado sobre la cerámica.
           Esto funciona igual en cualquier orientación horizontal. */
        .floor-hotspot {
            width: 96px;
            height: 96px;
            position: relative;
            cursor: pointer;
            /* Perspectiva que aplana el elemento como si estuviera en el suelo */
            transform: perspective(90px) rotateX(64deg);
        }

        /* Capa visual: anillo blanco delgado, centro transparente, sombra suave */
        .floor-hotspot-inner {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2.5px solid rgba(255, 255, 255, 0.92);
            box-shadow:
                0 0 10px rgba(255, 255, 255, 0.65),
                0 0 22px rgba(255, 255, 255, 0.25),
                inset 0 0 6px rgba(255, 255, 255, 0.08);
            animation: floor-pulse 2.6s ease-in-out infinite;
        }

        @keyframes floor-pulse {
            0%, 100% { transform: scale(1);    opacity: 0.82; }
            50%       { transform: scale(1.07); opacity: 1;    }
        }

        .floor-hotspot:hover .floor-hotspot-inner {
            animation: none;
            transform: scale(1.1);
            opacity: 1;
            box-shadow:
                0 0 16px rgba(255, 255, 255, 0.9),
                0 0 32px rgba(255, 255, 255, 0.4),
                inset 0 0 8px rgba(255, 255, 255, 0.12);
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

        #drone-canvas {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        #video-extract-progress {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 8;
            display: none;
        }

        #video-extract-bar {
            width: 220px;
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        #video-extract-fill {
            height: 100%;
            width: 0%;
            background: #007bff;
            border-radius: 3px;
            transition: width 0.15s linear;
        }

        #video-extract-text {
            color: #fff;
            font-size: 13px;
            font-weight: 500;
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

        /* Tooltip para polígonos */
        #polygon-tooltip {
            position: fixed;
            background: rgba(0, 0, 0, 0.85);
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            pointer-events: none;
            z-index: 1001;
            opacity: 0;
            transition: opacity 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            white-space: nowrap;
            max-width: 250px;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        #polygon-tooltip.visible {
            opacity: 1;
        }

        #polygon-tooltip::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px 6px 0 6px;
            border-style: solid;
            border-color: rgba(0, 0, 0, 0.85) transparent transparent transparent;
        }
        /* ====== Responsive: móviles ====== */
        @media (max-width: 768px) {
            /* Hotspot imágenes circulares más pequeñas */
            .circular-hotspot-img,
            .video-pos-hotspot .circular-hotspot-img {
                width: 32px;
                height: 32px;
                border-width: 1.5px;
            }

            /* Labels más compactos */
            .hotspot-label {
                font-size: 10px;
                padding: 2px 6px;
                margin-bottom: 3px;
                max-width: 110px;
            }

            /* Botones de hotspot en video */
            .video-hotspot-btn {
                font-size: 10px;
                padding: 4px 10px;
            }

            /* Nombre de escena */
            .current-scene-name {
                font-size: 12px;
                padding: 5px 14px;
                top: 10px;
            }

            /* Controles del visor */
            #controls .ctrl {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }

            /* Tooltip polígonos */
            #polygon-tooltip {
                font-size: 12px;
                padding: 6px 10px;
            }
        }

        /* ====== MATTERPORT STYLE UI ====== */

        /* Ocultar menú lateral antiguo por defecto - ahora como modal de búsqueda */
        #menu-nav-wrap {
            display: none;
            position: fixed;
            top: 70px;
            left: 16px;
            right: auto;
            margin: 0;
            width: 280px;
            max-height: 70vh;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            z-index: 1100;
            visibility: visible;
            transform: none;
            padding: 16px;
            overflow-y: auto;
        }

        #menu-nav-wrap.matterport-menu-open {
            display: block;
        }

        #menu-nav-wrap h3 {
            font-size: 16px;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        #menu-nav-wrap .nav-list {
            margin: 0;
            padding: 0;
        }

        #menu-nav-wrap .nav-list li {
            line-height: 1.4;
            margin-bottom: 10px;
        }

        #menu-nav-wrap .nav-list li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        #menu-nav-wrap .nav-list li a:hover {
            background: rgba(255,255,255,0.1);
        }

        #menu-nav-wrap .nav-list li a .circular {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            box-shadow: none;
        }

        /* Botón volver al origen (kiosk / subcategory) */
        .matterport-back-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        .matterport-back-btn:hover {
            background: rgba(0,0,0,0.85);
            color: #fff;
        }

        /* Header info superior izquierda */
        .matterport-header {
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 1000;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            transition: opacity 0.4s ease;
        }

        .matterport-toolbar,
        .matterport-actions,
        .matterport-scenes-carousel,
        .matterport-carousel-toggle {
            transition: opacity 0.4s ease;
        }

        .matterport-search-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.7);
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            backdrop-filter: blur(10px);
        }

        .matterport-search-btn:hover {
            background: rgba(0, 0, 0, 0.85);
            transform: scale(1.05);
        }

        .matterport-info {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            padding: 10px 16px;
            color: #fff;
        }

        .matterport-info-provider {
            font-size: 11px;
            opacity: 0.7;
            margin-bottom: 2px;
        }

        .matterport-info-title {
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .matterport-info-title i {
            color: #e74c3c;
            font-size: 12px;
        }

        /* Barra de herramientas inferior izquierda */
        .matterport-toolbar {
            position: fixed;
            bottom: 100px;
            left: 16px;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 4px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            padding: 6px 8px;
        }

        .matterport-toolbar-btn {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            background: transparent;
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            position: relative;
        }

        .matterport-toolbar-btn:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .matterport-toolbar-btn.active {
            background: rgba(255, 255, 255, 0.2);
            color: #e91e63;
        }

        .matterport-toolbar-btn.active i {
            color: #e91e63;
        }

        .matterport-toolbar-divider {
            width: 1px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            margin: 0 4px;
        }

        /* Tooltip para botones */
        .matterport-toolbar-btn[data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: #fff;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            margin-bottom: 8px;
        }

        /* Botones de acciones a la derecha */
        .matterport-actions {
            position: fixed;
            bottom: 100px;
            right: 16px;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .matterport-action-btn {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .matterport-action-btn:hover {
            background: rgba(0, 0, 0, 0.85);
            transform: scale(1.05);
        }

        /* Carrusel de escenas en la parte inferior */
        .matterport-scenes-carousel {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 999;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 70%, transparent 100%);
            padding: 16px 16px 20px;
        }

        .matterport-scenes-wrapper {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 4px 0;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .matterport-scenes-wrapper::-webkit-scrollbar {
            height: 4px;
        }

        .matterport-scenes-wrapper::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 2px;
        }

        .matterport-scenes-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 2px;
        }

        .matterport-scene-item {
            flex-shrink: 0;
            width: 120px;
            height: 70px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            position: relative;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .matterport-scene-item:hover {
            transform: scale(1.05);
            border-color: rgba(255,255,255,0.5);
        }

        .matterport-scene-item.active {
            border-color: #e91e63;
            box-shadow: 0 0 12px rgba(233, 30, 99, 0.5);
        }

        .matterport-scene-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .matterport-scene-item .scene-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 28px;
            height: 28px;
            background: rgba(255,255,255,0.95);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .matterport-scene-item:hover .scene-icon {
            opacity: 1;
        }

        .matterport-scene-item .scene-icon i {
            color: #333;
            font-size: 12px;
        }

        .matterport-scene-item.video-scene .scene-badge {
            position: absolute;
            top: 6px;
            left: 6px;
            background: rgba(0,0,0,0.7);
            color: #fff;
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .matterport-scene-item .scene-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: #fff;
            font-size: 10px;
            padding: 16px 6px 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Toggle para mostrar/ocultar carrusel */
        .matterport-carousel-toggle {
            position: fixed;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1001;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            border: none;
            color: #fff;
            width: 44px;
            height: 28px;
            border-radius: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .matterport-carousel-toggle:hover {
            background: rgba(0, 0, 0, 0.85);
        }

        .matterport-carousel-toggle i {
            transition: transform 0.3s ease;
        }

        .matterport-carousel-toggle.collapsed i {
            transform: rotate(180deg);
        }

        .matterport-scenes-carousel.collapsed {
            transform: translateY(calc(100% - 28px));
        }

        /* Ocultar controles antiguos */
        #controls {
            display: none !important;
        }

        /* Ajustar nombre de escena para nueva UI */
        .current-scene-name {
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
        }

        /* ====== Responsive Matterport UI ====== */
        @media (max-width: 768px) {
            .matterport-header {
                top: 10px;
                left: 10px;
                gap: 8px;
            }

            .matterport-search-btn {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }

            .matterport-info {
                padding: 8px 12px;
            }

            .matterport-info-provider {
                font-size: 10px;
            }

            .matterport-info-title {
                font-size: 12px;
            }

            /* Carrusel de escenas primero (más abajo en z-index) */
            .matterport-scenes-carousel {
                padding: 12px 10px 16px;
                z-index: 998;
            }

            .matterport-scene-item {
                width: 90px;
                height: 55px;
            }

            .matterport-scene-item .scene-title {
                font-size: 9px;
                padding: 12px 4px 4px;
            }

            /* Toolbar centrada ENCIMA del carrusel */
            .matterport-toolbar {
                bottom: 95px;
                left: 50%;
                transform: translateX(-50%);
                padding: 6px 10px;
                border-radius: 10px;
                z-index: 1000;
            }

            .matterport-toolbar-btn {
                width: 36px;
                height: 36px;
                font-size: 15px;
            }

            /* Destacar botones de navegación entre escenas */
            .matterport-toolbar-btn#matterport-prev-scene,
            .matterport-toolbar-btn#matterport-next-scene {
                background: rgba(255, 255, 255, 0.12);
            }

            /* Botones de acciones ENCIMA del carrusel */
            .matterport-actions {
                bottom: 95px;
                right: 10px;
                z-index: 1000;
            }

            .matterport-action-btn {
                width: 38px;
                height: 38px;
                font-size: 15px;
            }

            .current-scene-name {
                top: 65px;
            }

            /* Ocultar toggle separado en móvil - usar el de la toolbar */
            .matterport-carousel-toggle {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .matterport-info {
                max-width: 180px;
            }

            .matterport-info-title {
                font-size: 11px;
            }

            /* Carrusel más compacto en móviles pequeños */
            .matterport-scenes-carousel {
                padding: 10px 8px 14px;
                z-index: 998;
            }

            .matterport-scene-item {
                width: 75px;
                height: 48px;
            }

            /* Toolbar ENCIMA del carrusel - ajustado para altura del carrusel */
            .matterport-toolbar {
                left: 50%;
                transform: translateX(-50%);
                gap: 3px;
                padding: 6px 10px;
                bottom: 85px;
                border-radius: 12px;
                z-index: 1000;
            }

            .matterport-toolbar-btn {
                width: 38px;
                height: 38px;
                font-size: 15px;
                border-radius: 8px;
            }

            /* Destacar botones de navegación entre escenas en móvil */
            .matterport-toolbar-btn#matterport-prev-scene,
            .matterport-toolbar-btn#matterport-next-scene {
                background: rgba(255, 255, 255, 0.15);
            }

            .matterport-toolbar-btn#matterport-prev-scene:active,
            .matterport-toolbar-btn#matterport-next-scene:active {
                background: rgba(255, 255, 255, 0.3);
                transform: scale(0.95);
            }

            .matterport-toolbar-divider {
                height: 20px;
                margin: 0 2px;
            }

            /* Botones de acciones ENCIMA del carrusel */
            .matterport-actions {
                bottom: 85px;
                right: 10px;
                z-index: 1000;
            }

            .matterport-action-btn {
                width: 38px;
                height: 38px;
                font-size: 15px;
            }
        }
    </style>
</head>

<body id="top">
    {{-- Antiguo menú lateral (oculto por CSS pero mantenido para compatibilidad) --}}
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

    {{-- ====== NUEVA UI ESTILO MATTERPORT ====== --}}

    {{-- Header con info de la propiedad --}}
    <div class="matterport-header">
        @php
            $backUrl = request()->get('back_url', '');
            // Bloquear solo protocolos activos peligrosos; el parámetro lo generan
            // nuestras propias vistas (kiosk/index, subcategory), no entrada del usuario.
            if ($backUrl && !preg_match('#^https?://#i', $backUrl) && !str_starts_with($backUrl, '/')) {
                $backUrl = '';
            }
        @endphp
        @if($backUrl)
        <a href="{{ $backUrl }}" class="matterport-back-btn" title="Volver">
            <i class="fa fa-arrow-left"></i>
        </a>
        @endif
        <button class="matterport-search-btn" id="matterport-menu-toggle" title="Buscar escenas">
            <i class="fa fa-search"></i>
        </button>
        <div class="matterport-info">
            <div class="matterport-info-provider">Virtual Tour 360</div>
            <div class="matterport-info-title">
                <i class="fa fa-map-marker"></i>
                <span>{{ $fscene ? ($fscene->property_name ?? 'Propiedad') : 'Propiedad' }}</span>
            </div>
        </div>
    </div>

    {{-- Barra de herramientas inferior izquierda --}}
    <div class="matterport-toolbar">
        <button class="matterport-toolbar-btn" id="matterport-toggle-carousel" data-tooltip="Escenas">
            <i class="fa fa-chevron-down"></i>
        </button>
        <button class="matterport-toolbar-btn" id="matterport-autorotate" data-tooltip="Auto-rotar">
            <i class="fa fa-play"></i>
        </button>
        <div class="matterport-toolbar-divider"></div>
        <button class="matterport-toolbar-btn" id="matterport-prev-scene" data-tooltip="Escena anterior">
            <i class="fa fa-chevron-left"></i>
        </button>
        <button class="matterport-toolbar-btn active" id="matterport-walk-mode" data-tooltip="Modo caminar">
            <i class="fa fa-male"></i>
        </button>
        <button class="matterport-toolbar-btn" id="matterport-next-scene" data-tooltip="Siguiente escena">
            <i class="fa fa-chevron-right"></i>
        </button>
    </div>

    {{-- Botones de acciones a la derecha --}}
    <div class="matterport-actions">
        <button class="matterport-action-btn" id="matterport-share" title="Compartir">
            <i class="fa fa-share-alt"></i>
        </button>
        <button class="matterport-action-btn" id="matterport-fullscreen" title="Pantalla completa">
            <i class="fa fa-expand"></i>
        </button>
    </div>

    {{-- Carrusel de escenas --}}
    <div class="matterport-scenes-carousel" id="matterport-scenes-carousel">
        <div class="matterport-scenes-wrapper">
            @foreach ($scenes as $index => $scene)
                <div class="matterport-scene-item {{ $scene->type === 'video' ? 'video-scene' : '' }} {{ $index === 0 ? 'active' : '' }}"
                     data-scene-id="{{ $scene->id }}"
                     data-scene-type="{{ $scene->type }}">
                    <img src="{{ isset($scene->image_ref) ? route('file', $scene->image_ref) : (isset($scene->image) ? route('file', $scene->image) : url('images/producto-sin-imagen.PNG')) }}"
                         alt="{{ $scene->title }}">
                    @if($scene->type === 'video')
                        <div class="scene-badge">
                            <i class="fa fa-video-camera"></i>
                            360
                        </div>
                    @endif
                    <div class="scene-icon">
                        <i class="fa {{ $scene->type === 'video' ? 'fa-play' : 'fa-male' }}"></i>
                    </div>
                    <div class="scene-title">{{ $scene->title }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Toggle del carrusel --}}
    <button class="matterport-carousel-toggle" id="matterport-carousel-toggle">
        <i class="fa fa-chevron-down"></i>
    </button>

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
                    <h1 class="animate-intro">Virtual Tour | Descubre {{ $fscene ? ($fscene->property_name ?? 'la propiedad') : 'la propiedad' }}</h1>
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
        {{-- Controles antiguos (ocultos) --}}
        <div id="controls" style="display: none;">
            <div class="ctrl btn-tooltip" id="menu-trigger-ctrl" title="Menú">
                <i class="fa fa-cubes"></i>
            </div>
            <div class="ctrl" id="menu-trigger-ctrl-map" title="Mapa">
                <i class="fa fa-map"></i>
            </div>
        </div>

        {{-- Video Dron Orbital Overlay --}}
        <div id="video-viewer-overlay">
            <video id="drone-video" muted playsinline preload="auto" style="display:none;"></video>
            <canvas id="drone-canvas"></canvas>
            <div id="video-extract-progress">
                <div id="video-extract-bar">
                    <div id="video-extract-fill"></div>
                </div>
                <div id="video-extract-text">Preparando vista interactiva: 0%</div>
            </div>
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

    {{-- Tooltip para polígonos --}}
    <div id="polygon-tooltip"></div>

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

        // Si no hay escena principal ($fscene es null), usar la primera escena disponible
        if (!$fscene && $scenes->isNotEmpty()) {
            $fscene = $scenes->first();
        }

        // Detectar si la primera escena es de tipo video
        $firstSceneIsVideo = $fscene ? ($fscene->type === 'video') : false;

        // Si la primera escena es video, buscar la primera escena panorama como fallback para Pannellum
        $pannellumFirstScene = $fscene;
        if ($firstSceneIsVideo) {
            $firstPanorama = $scenes->firstWhere('type', '!=', 'video');
            if ($firstPanorama) {
                $pannellumFirstScene = $firstPanorama;
            }
        }

        // 2) Default Pannellum - Configuración para efecto de caminar con zoom
        $pannellumDefault = [
            'firstScene' => $pannellumFirstScene ? (string) $pannellumFirstScene->id : '',
            'hfov' => 100,
            'minHfov' => 2,
            'maxHfov' => 120,
            'autoLoad' => true,
            'sceneFadeDuration' => 0,
            'autoRotate' => 0, // ✅ arranca estático
            'autoRotateInactivityDelay' => 0, // (lo controlaremos nosotros)
            'compass' => false,
            'showControls' => true,
            'mouseZoom' => true,
            'draggable' => true,
            'showFullscreenCtrl' => true,
            'showZoomCtrl' => false,
            'keyboardZoom' => true,
        ];

        // Guardar el ID real de la primera escena (puede ser video)
        $realFirstSceneId = $fscene ? (string) $fscene->id : '';

        // 3) Scenes + hotspots (misma lógica tuya, sólo formateado)
        $scenesConfig = [];
        foreach ($scenes as $scene) {
            $hotspotsForScene = [];
            foreach ($hotspots->where('sourceScene', $scene->id) as $hotspot) {
                // Determinar el texto a mostrar arriba de la imagen
                // Para tipo 'info': mostrar el campo info (ej: "Refrigeradora")
                // Para tipo 'scene': mostrar el nombre de la escena destino
                $displayText = $hotspot->info;

                $hs = [
                    'pitch' => (float) $hotspot->pitch,
                    'yaw' => (float) $hotspot->yaw,
                    'cssClass' => 'circular-hotspot',
                    'type' => 'custom', // Usar tipo custom para control total
                    'createTooltipFunc' => 'hotspotTooltipFunction',
                    'createTooltipArgs' => [
                        'imageUrl' => !empty($hotspot->image)
                            ? route('file', $hotspot->image)
                            : null,
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
                    $clickArgs = [
                        'targetSceneId' => (string) $hotspot->targetScene,
                        'yaw' => (float) $hotspot->yaw,
                        'pitch' => (float) $hotspot->pitch,
                    ];
                    // Agregar target_yaw/target_pitch si el hotspot los tiene
                    if ($hotspot->target_yaw !== null) {
                        $clickArgs['targetYaw'] = (float) $hotspot->target_yaw;
                    }
                    if ($hotspot->target_pitch !== null) {
                        $clickArgs['targetPitch'] = (float) $hotspot->target_pitch;
                    }
                    $hs['clickHandlerArgs'] = $clickArgs;
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
                'spinFramesDir' => $scene->spin_frames_dir ?? null,
                'spinFramesCount' => $scene->spin_frames_count ?? null,
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
                var imageUrl    = args.imageUrl || null;
                var displayText = args.displayText || '';
                var hotspotType = args.hotspotType || 'scene';

                // Crear contenedor principal
                const container = document.createElement('div');
                container.classList.add('hotspot-tooltip-container');

                // Crear etiqueta de texto arriba del ícono (solo si hay texto)
                if (displayText) {
                    const label = document.createElement('div');
                    label.classList.add('hotspot-label');
                    label.textContent = displayText;
                    label.classList.add(hotspotType === 'info' ? 'hotspot-label-info' : 'hotspot-label-scene');
                    container.appendChild(label);
                }

                if (imageUrl) {
                    // Imagen personalizada — círculo con foto
                    const img = document.createElement('img');
                    img.classList.add('circular-hotspot-img');
                    img.src = imageUrl;
                    img.alt = displayText || 'hotspot';
                    container.appendChild(img);
                } else {
                    // Sin imagen → óvalo proyectado sobre el suelo
                    // .floor-hotspot aplica perspective+rotateX (estático)
                    // .floor-hotspot-inner contiene el gradiente y la animación de pulso
                    const floor = document.createElement('div');
                    floor.classList.add('floor-hotspot');
                    const inner = document.createElement('div');
                    inner.classList.add('floor-hotspot-inner');
                    floor.appendChild(inner);
                    container.appendChild(floor);
                }

                hotSpotDiv.appendChild(container);
            }
            window.hotspotTooltipFunction = hotspotTooltipFunction;

            // --- Handler de clic en hotspot (global) ---
            function onHotspotClick(e, args) {
                if (window.walkToScene && args) {
                    window.walkToScene(args.targetSceneId, args.yaw, args.pitch, args.targetYaw, args.targetPitch);
                }
            }
            window.onHotspotClick = onHotspotClick;

            // --- Config generada en Blade (sin operadores "|")
            const pannellumConfig = {
                default: @json($pannellumDefault, $jsonOptions),
                scenes: @json($scenesConfig, $jsonOptions)
            };

            // --- Ajustar hfov en móviles para que la perspectiva sea natural ---
            (function() {
                var w = window.innerWidth;
                if (w <= 768) {
                    var mobileHfov = 75;
                    pannellumConfig.default.hfov = mobileHfov;
                    pannellumConfig.default.maxHfov = 100;
                    // Ajustar también cada escena
                    Object.keys(pannellumConfig.scenes).forEach(function(sid) {
                        var s = pannellumConfig.scenes[sid];
                        if (s.hfov > mobileHfov) s.hfov = mobileHfov;
                    });
                }
            })();

            // Guardar yaw/pitch originales de cada escena para no perderlos al sobreescribir
            var originalSceneOrientation = {};
            Object.keys(pannellumConfig.scenes).forEach(function(sid) {
                var s = pannellumConfig.scenes[sid];
                originalSceneOrientation[sid] = { yaw: s.yaw || 0, pitch: s.pitch || 0 };
            });

            // --- Polígonos por escena ---
            const scenePolygons = @json($polygonsConfig ?? [], $jsonOptions);

            // --- Primera escena real (puede ser video) ---
            const realFirstSceneId = @json($realFirstSceneId);
            const firstSceneIsVideo = @json($firstSceneIsVideo);

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

            // ===== Inercia / momentum al arrastrar el visor =====
            (function() {
                var pnlmEl = document.getElementById('pannellum');
                if (!pnlmEl) return;

                var dragging   = false;
                var lastYaw    = 0, lastPitch = 0, lastTime = 0;
                var velYaw     = 0, velPitch  = 0;
                var rafId      = null;
                var FRICTION   = 0.88;   // cuánto se desacelera por frame (0=para ya, 1=nunca para)
                var MIN_VEL    = 0.04;   // velocidad mínima antes de parar
                var MAX_VEL    = 6;      // cap para evitar lanzadas extremas

                function clamp(v, lo, hi) { return v < lo ? lo : v > hi ? hi : v; }

                function applyMomentum() {
                    velYaw   *= FRICTION;
                    velPitch *= FRICTION;

                    if (Math.abs(velYaw) < MIN_VEL && Math.abs(velPitch) < MIN_VEL) return;

                    try {
                        viewer.setYaw(viewer.getYaw()     - velYaw);
                        viewer.setPitch(viewer.getPitch() + velPitch);
                    } catch(e) { return; }

                    rafId = requestAnimationFrame(applyMomentum);
                }

                function stopMomentum() {
                    velYaw = velPitch = 0;
                    if (rafId) { cancelAnimationFrame(rafId); rafId = null; }
                }

                function onDragStart(cx, cy) {
                    stopMomentum();
                    dragging  = true;
                    lastTime  = performance.now();
                    try {
                        lastYaw   = viewer.getYaw();
                        lastPitch = viewer.getPitch();
                    } catch(e) {}
                }

                function onDragMove(cx, cy) {
                    if (!dragging) return;
                    var now = performance.now();
                    var dt  = now - lastTime;
                    if (dt < 1) return;

                    try {
                        var curYaw   = viewer.getYaw();
                        var curPitch = viewer.getPitch();
                        var dYaw     = lastYaw   - curYaw;
                        var dPitch   = curPitch  - lastPitch;
                        // Exponential moving average — suaviza picos bruscos
                        velYaw   = velYaw   * 0.55 + clamp(dYaw   / dt * 16, -MAX_VEL, MAX_VEL) * 0.45;
                        velPitch = velPitch * 0.55 + clamp(dPitch / dt * 16, -MAX_VEL, MAX_VEL) * 0.45;
                        lastYaw   = curYaw;
                        lastPitch = curPitch;
                    } catch(e) {}

                    lastTime = now;
                }

                function onDragEnd() {
                    if (!dragging) return;
                    dragging = false;
                    if (Math.abs(velYaw) > MIN_VEL || Math.abs(velPitch) > MIN_VEL) {
                        rafId = requestAnimationFrame(applyMomentum);
                    }
                }

                // Mouse
                pnlmEl.addEventListener('mousedown', function(e) { onDragStart(e.clientX, e.clientY); });
                window.addEventListener('mousemove', function(e) { onDragMove(e.clientX,  e.clientY); });
                window.addEventListener('mouseup',   onDragEnd);

                // Touch
                pnlmEl.addEventListener('touchstart', function(e) {
                    if (e.touches[0]) onDragStart(e.touches[0].clientX, e.touches[0].clientY);
                }, { passive: true });
                window.addEventListener('touchmove', function(e) {
                    if (e.touches[0]) onDragMove(e.touches[0].clientX, e.touches[0].clientY);
                }, { passive: true });
                window.addEventListener('touchend',  onDragEnd);

                // Cancelar inercia si el usuario empieza a rotar automático
                viewer.on('autorotatechange', stopMomentum);
            })();

            // ===== Auto-rotate solo tras inactividad (incluye inicio del tour) =====
            const IDLE_ROTATE = (function() {
                const ROTATE_SPEED = -1; // velocidad de giro
                const IDLE_MS = 10000; // ✅ 10 segundos sin interacción

                let idleTimer = null;
                let enabled = false;

                function startRotate() {
                    if (!enabled) return;
                    if (isTransitioning) return; // evita girar en transiciones

                    if (typeof viewer.startAutoRotate === 'function') {
                        viewer.startAutoRotate(ROTATE_SPEED);
                    } else if (typeof viewer.setAutoRotate === 'function') {
                        viewer.setAutoRotate(ROTATE_SPEED);
                    } else {
                        console.warn('[IdleRotate] startAutoRotate/setAutoRotate no disponible en este build.');
                    }
                }

                function stopRotate() {
                    if (typeof viewer.stopAutoRotate === 'function') {
                        viewer.stopAutoRotate();
                    } else if (typeof viewer.setAutoRotate === 'function') {
                        viewer.setAutoRotate(0);
                    }
                }

                function reset() {
                    stopRotate();
                    if (idleTimer) clearTimeout(idleTimer);
                    idleTimer = setTimeout(startRotate, IDLE_MS);
                }

                function enable() {
                    enabled = true;
                    reset(); // empieza quieto y arranca conteo
                }

                function disable() {
                    enabled = false;
                    if (idleTimer) clearTimeout(idleTimer);
                    stopRotate();
                }

                // Cualquier interacción = reset
                const events = ['mousemove', 'mousedown', 'wheel', 'touchstart', 'touchmove', 'keydown'];
                events.forEach(evt => document.addEventListener(evt, reset, {
                    passive: true
                }));

                // Cambios de escena también resetean
                viewer.on('scenechange', reset);
                viewer.on('load', reset);

                return {
                    enable,
                    disable,
                    reset
                };
            })();


            // --- Overlay para transición suave ---
            var $transitionOverlay = $('<div id="transition-overlay"></div>').css({
                position: 'absolute',
                top: 0,
                left: 0,
                width: '100%',
                height: '100%',
                backgroundColor: 'rgba(255,255,255,0)',
                opacity: 0,
                pointerEvents: 'none',
                zIndex: 1000,
                transition: 'none',
                backdropFilter: 'blur(0px)',
                webkitBackdropFilter: 'blur(0px)'
            });
            $('#pannellum').append($transitionOverlay);

            // --- SVG Overlay para polígonos de terreno ---
            var polygonSvgEl = null;
            var polygonTooltip = document.getElementById('polygon-tooltip');
            var hoveredPolygonId = null; // Track qué polígono está en hover
            var lastMousePos = {
                x: 0,
                y: 0
            }; // Última posición del mouse
            console.log('[Polygons] Data:', JSON.stringify(scenePolygons));

            // Actualizar posición del mouse globalmente
            document.addEventListener('mousemove', function(e) {
                lastMousePos.x = e.clientX;
                lastMousePos.y = e.clientY;
            });

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
                polygonSvgEl.style.cssText =
                    'position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:2;';
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

                    return {
                        x: screenX,
                        y: screenY
                    };
                } catch (e) {
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
                var createdPaths = []; // Para restaurar hover después

                polygons.forEach(function(poly) {
                    // Manejar points como string o array
                    var points = poly.points;
                    if (!points) return;
                    if (typeof points === 'string') {
                        try {
                            points = JSON.parse(points);
                        } catch (e) {
                            return;
                        }
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

                        var isHovered = (hoveredPolygonId === poly.id);

                        var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        path.setAttribute('d', pathData);
                        path.setAttribute('fill', poly.fillColor);
                        // Aplicar estado hover si corresponde
                        path.setAttribute('fill-opacity', isHovered ? Math.min(1, poly.fillOpacity + 0.3) : poly
                            .fillOpacity);
                        path.setAttribute('stroke', poly.strokeColor);
                        path.setAttribute('stroke-width', isHovered ? poly.strokeWidth + 2 : poly.strokeWidth);
                        path.setAttribute('data-polygon-id', poly.id);
                        path.setAttribute('data-polygon-name', poly.name || '');
                        path.setAttribute('data-base-opacity', poly.fillOpacity);
                        path.setAttribute('data-base-stroke', poly.strokeWidth);
                        path.style.pointerEvents = 'auto';
                        path.style.cursor = 'pointer';

                        // Eventos de hover para tooltip
                        path.addEventListener('mouseenter', function(e) {
                            hoveredPolygonId = poly.id;
                            // Resaltar polígono
                            path.setAttribute('fill-opacity', Math.min(1, poly.fillOpacity + 0.3));
                            path.setAttribute('stroke-width', poly.strokeWidth + 2);

                            // Mostrar tooltip
                            var name = path.getAttribute('data-polygon-name');
                            if (name && polygonTooltip) {
                                polygonTooltip.textContent = name;
                                polygonTooltip.style.left = e.clientX + 'px';
                                polygonTooltip.style.top = (e.clientY - 45) + 'px';
                                polygonTooltip.classList.add('visible');
                            }
                        });

                        path.addEventListener('mousemove', function(e) {
                            if (polygonTooltip && hoveredPolygonId === poly.id) {
                                polygonTooltip.style.left = e.clientX + 'px';
                                polygonTooltip.style.top = (e.clientY - 45) + 'px';
                            }
                        });

                        path.addEventListener('mouseleave', function(e) {
                            hoveredPolygonId = null;
                            // Restaurar estilo original
                            path.setAttribute('fill-opacity', poly.fillOpacity);
                            path.setAttribute('stroke-width', poly.strokeWidth);

                            // Ocultar tooltip
                            if (polygonTooltip) {
                                polygonTooltip.classList.remove('visible');
                            }
                        });

                        svg.appendChild(path);
                        createdPaths.push({
                            path: path,
                            poly: poly
                        });

                        // Dibujar medidas en los lados
                        var edgeLabels = poly.edgeLabels;
                        if (typeof edgeLabels === 'string') {
                            try {
                                edgeLabels = JSON.parse(edgeLabels);
                            } catch (e) {
                                edgeLabels = null;
                            }
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

                // Mantener tooltip visible si hay un polígono en hover
                if (hoveredPolygonId && polygonTooltip) {
                    polygonTooltip.style.left = lastMousePos.x + 'px';
                    polygonTooltip.style.top = (lastMousePos.y - 45) + 'px';
                }
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
                var sumX = 0,
                    sumY = 0,
                    count = 0;
                screenPoints.forEach(function(p) {
                    if (p) {
                        sumX += p.x;
                        sumY += p.y;
                        count++;
                    }
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

            // ===== VIDEO DRON ORBITAL - Canvas Frame Cache (rendimiento instantáneo) =====
            var videoOverlay = document.getElementById('video-viewer-overlay');
            var droneVideo = document.getElementById('drone-video');
            var droneCanvas = document.getElementById('drone-canvas');
            var droneCtx = droneCanvas.getContext('2d');
            var videoProgressFill = document.getElementById('video-progress-fill');
            var videoScrubIndicator = document.getElementById('video-scrub-indicator');
            var videoHotspotsBar = document.getElementById('video-hotspots-bar');
            var videoDragHint = document.getElementById('video-drag-hint');
            var videoExtractProgress = document.getElementById('video-extract-progress');
            var videoExtractFill = document.getElementById('video-extract-fill');
            var videoExtractText = document.getElementById('video-extract-text');
            var currentVideoSceneId = null;
            var videoReady = false;

            // Frame cache: almacena frames extraídos del video como ImageBitmap/Image
            var frameCache = {
                frames: [], // Array de Image objects
                times: [], // Tiempo correspondiente a cada frame
                totalFrames: 0,
                duration: 0,
                currentIndex: 0, // Frame actualmente mostrado
                extracting: false,
                aborted: false,
                canvasWidth: 0,
                canvasHeight: 0
            };

            // Spin cache: para frames pre-generados por CloudConvert
            var spinCache = {
                frames: [],
                totalFrames: 0,
                currentIndex: 0,
                framesDir: null,
                loaded: 0,
                ready: false,
                canvasWidth: 0,
                canvasHeight: 0
            };
            var isSpinMode = false; // true cuando se usa spin pre-generado, false para video

            var videoDragState = {
                isDragging: false,
                startX: 0,
                startNorm: 0, // posición normalizada (0-1) al inicio del drag
                sensitivity: 0.0015 // cambio normalizado por pixel de drag
            };

            function isVideoScene(sceneId) {
                var sc = pannellumConfig.scenes[sceneId];
                return sc && sc.type === 'video' && sc.video;
            }

            // Verificar si una escena de video tiene frames de spin pre-generados
            function hasSpinFrames(sceneId) {
                var sc = pannellumConfig.scenes[sceneId];
                return sc && sc.type === 'video' && sc.spinFramesDir && sc.spinFramesCount > 0;
            }

            // Mostrar un frame del cache en el canvas (INSTANTÁNEO)
            function showFrameAt(normalizedTime) {
                // Usar spin cache o video cache según el modo
                if (isSpinMode) {
                    showSpinFrameAt(normalizedTime);
                    return;
                }

                if (frameCache.frames.length === 0) return;

                // Wrap around 0-1
                while (normalizedTime < 0) normalizedTime += 1;
                while (normalizedTime >= 1) normalizedTime -= 1;

                var idx = Math.round(normalizedTime * (frameCache.frames.length - 1));
                idx = Math.max(0, Math.min(frameCache.frames.length - 1, idx));

                if (idx === frameCache.currentIndex && droneCanvas.width > 0) return; // ya mostrado

                var frame = frameCache.frames[idx];
                if (!frame) return;

                frameCache.currentIndex = idx;

                // Dibujar frame en el canvas
                droneCtx.drawImage(frame, 0, 0, droneCanvas.width, droneCanvas.height);

                // Actualizar progreso visual
                var pct = Math.round(normalizedTime * 100);
                videoProgressFill.style.width = pct + '%';

                // Actualizar hotspots posicionados
                updateVideoPositionedHotspots();
            }

            // Mostrar frame del spin pre-generado
            function showSpinFrameAt(normalizedTime) {
                if (spinCache.frames.length === 0 || !spinCache.ready) return;

                // Wrap around 0-1
                while (normalizedTime < 0) normalizedTime += 1;
                while (normalizedTime >= 1) normalizedTime -= 1;

                // Los frames de spin están indexados 1-N
                var idx = Math.round(normalizedTime * (spinCache.totalFrames - 1)) + 1;
                idx = Math.max(1, Math.min(spinCache.totalFrames, idx));

                if (idx === spinCache.currentIndex && droneCanvas.width > 0) return;

                var frame = spinCache.frames[idx];
                if (!frame) {
                    // Buscar frame más cercano disponible
                    for (var offset = 1; offset <= spinCache.totalFrames; offset++) {
                        if (spinCache.frames[idx + offset]) { frame = spinCache.frames[idx + offset]; break; }
                        if (spinCache.frames[idx - offset]) { frame = spinCache.frames[idx - offset]; break; }
                    }
                }
                if (!frame) return;

                spinCache.currentIndex = idx;

                // Dibujar frame
                droneCtx.drawImage(frame, 0, 0, droneCanvas.width, droneCanvas.height);

                // Actualizar progreso visual
                var pct = Math.round(normalizedTime * 100);
                videoProgressFill.style.width = pct + '%';

                // Actualizar hotspots posicionados para spin
                updateSpinPositionedHotspots();
            }

            // Obtener el tiempo actual del video basado en el frame mostrado
            function getCurrentVideoTime() {
                if (isSpinMode) {
                    // Para spin, calcular tiempo "virtual" basado en frame index
                    if (spinCache.totalFrames <= 1) return 0;
                    // Asumimos una duración virtual de ~10 segundos para el spin
                    var virtualDuration = 10;
                    return ((spinCache.currentIndex - 1) / (spinCache.totalFrames - 1)) * virtualDuration;
                }
                if (frameCache.frames.length === 0 || frameCache.duration === 0) return 0;
                return (frameCache.currentIndex / (frameCache.frames.length - 1)) * frameCache.duration;
            }

            // Obtener posición normalizada (0-1) actual
            function getCurrentNormalized() {
                if (isSpinMode) {
                    if (spinCache.totalFrames <= 1) return 0;
                    return (spinCache.currentIndex - 1) / (spinCache.totalFrames - 1);
                }
                if (frameCache.frames.length <= 1) return 0;
                return frameCache.currentIndex / (frameCache.frames.length - 1);
            }

            // Extraer frames del video al cache
            function extractFrames(videoEl, callback) {
                frameCache.extracting = true;
                frameCache.aborted = false;
                frameCache.frames = [];
                frameCache.times = [];
                frameCache.currentIndex = 0;

                var duration = videoEl.duration;
                frameCache.duration = duration;

                // Calcular número de frames: ~4fps, máximo 200, mínimo 30
                var targetFps = 4;
                var totalFrames = Math.min(200, Math.max(30, Math.round(duration * targetFps)));
                frameCache.totalFrames = totalFrames;

                // Canvas offscreen para extracción (resolución alta para mejor calidad)
                var maxW = 1920; // Full HD
                var vw = videoEl.videoWidth || 1920;
                var vh = videoEl.videoHeight || 1080;
                var scale = Math.min(1, maxW / vw);
                var ew = Math.round(vw * scale);
                var eh = Math.round(vh * scale);

                var offscreen = document.createElement('canvas');
                offscreen.width = ew;
                offscreen.height = eh;
                var offCtx = offscreen.getContext('2d');

                // Configurar canvas de display
                droneCanvas.width = ew;
                droneCanvas.height = eh;
                frameCache.canvasWidth = ew;
                frameCache.canvasHeight = eh;

                var extracted = 0;
                var interval = duration / totalFrames;

                // Mostrar progreso
                videoExtractProgress.style.display = 'block';

                function extractNext() {
                    if (frameCache.aborted) {
                        videoExtractProgress.style.display = 'none';
                        frameCache.extracting = false;
                        return;
                    }

                    if (extracted >= totalFrames) {
                        // Extracción completa
                        videoExtractProgress.style.display = 'none';
                        frameCache.extracting = false;
                        if (callback) callback();
                        return;
                    }

                    var targetTime = extracted * interval;
                    videoEl.currentTime = targetTime;
                }

                videoEl.onseeked = function() {
                    if (frameCache.aborted) {
                        videoExtractProgress.style.display = 'none';
                        frameCache.extracting = false;
                        return;
                    }

                    // Dibujar frame actual en canvas offscreen
                    offCtx.drawImage(videoEl, 0, 0, ew, eh);

                    // Crear Image desde data URL (JPEG comprimido)
                    var dataUrl = offscreen.toDataURL('image/jpeg', 0.85);
                    var img = new Image();
                    img.onload = function() {
                        frameCache.frames[extracted] = img;
                        frameCache.times[extracted] = videoEl.currentTime;

                        // Mostrar el primer frame inmediatamente en el canvas
                        if (extracted === 0) {
                            droneCtx.drawImage(img, 0, 0, droneCanvas.width, droneCanvas.height);
                        }

                        extracted++;

                        // Actualizar progreso
                        var pct = Math.round((extracted / totalFrames) * 100);
                        videoExtractFill.style.width = pct + '%';
                        videoExtractText.textContent = 'Preparando vista interactiva: ' + pct + '%';

                        // Permitir interacción con frames ya extraídos (desde el 15%)
                        if (extracted >= Math.ceil(totalFrames * 0.15) && !videoReady) {
                            videoReady = true;
                            videoDragHint.innerHTML =
                                '<i class="fa fa-arrows-h"></i> Arrastra para girar alrededor de la propiedad';
                            setTimeout(function() {
                                videoDragHint.classList.remove('visible');
                            }, 3000);
                        }

                        // Siguiente frame
                        extractNext();
                    };
                    img.onerror = function() {
                        // Si falla un frame, saltarlo
                        extracted++;
                        extractNext();
                    };
                    img.src = dataUrl;
                };

                // Iniciar extracción
                extractNext();
            }

            function showVideoViewer(sceneId) {
                var sc = pannellumConfig.scenes[sceneId];
                if (!sc || !sc.video) return;

                // Si tiene frames de spin pre-generados, usar spin viewer
                if (hasSpinFrames(sceneId)) {
                    showSpinViewer(sceneId);
                    return;
                }

                currentVideoSceneId = sceneId;
                videoReady = false;
                isSpinMode = false;

                // Mostrar overlay
                videoOverlay.style.display = 'block';
                videoDragHint.textContent = 'Cargando video...';
                videoDragHint.classList.add('visible');

                // Limpiar canvas
                droneCanvas.width = droneCanvas.width; // reset

                // Cargar video (oculto, solo para extracción)
                droneVideo.src = sc.video;
                droneVideo.load();

                // Cuando hay suficiente data, iniciar extracción de frames
                droneVideo.onloadeddata = function() {
                    videoDragHint.textContent = 'Preparando vista...';
                    extractFrames(droneVideo, function() {
                        // Extracción completa
                        console.log('[Video] Frame cache listo:', frameCache.frames.length, 'frames');
                        // Liberar video de memoria
                        droneVideo.onseeked = null;
                        droneVideo.onloadeddata = null;
                        droneVideo.pause();
                        droneVideo.removeAttribute('src');
                        droneVideo.load();
                    });
                };

                // Generar hotspots posicionados en el video
                buildVideoHotspots(sceneId);
            }

            // Mostrar spin viewer con frames pre-generados (carga más rápida)
            function showSpinViewer(sceneId) {
                var sc = pannellumConfig.scenes[sceneId];
                if (!sc || !sc.spinFramesDir || !sc.spinFramesCount) return;

                currentVideoSceneId = sceneId;
                videoReady = false;
                isSpinMode = true;

                // Reset spin cache
                spinCache.frames = [];
                spinCache.totalFrames = sc.spinFramesCount;
                spinCache.currentIndex = 1;
                spinCache.framesDir = sc.spinFramesDir;
                spinCache.loaded = 0;
                spinCache.ready = false;

                // Mostrar overlay
                videoOverlay.style.display = 'block';
                videoDragHint.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cargando...';
                videoDragHint.classList.add('visible');
                videoExtractProgress.style.display = 'block';
                videoExtractFill.style.width = '0%';
                videoExtractText.textContent = 'Cargando vista 360: 0%';

                // URL base de frames
                var baseUrl = '/storage/' + sc.spinFramesDir + '/';
                var totalFrames = sc.spinFramesCount;
                var MIN_READY = Math.min(8, totalFrames);
                var CONCURRENCY = 6;

                function frameSrc(n) {
                    return baseUrl + 'frame-' + String(n).padStart(3, '0') + '.webp';
                }

                function loadImage(n) {
                    return new Promise(function(resolve) {
                        if (spinCache.frames[n]) return resolve();
                        var img = new Image();
                        img.src = frameSrc(n);
                        img.onload = function() {
                            spinCache.frames[n] = img;
                            spinCache.loaded++;

                            // Actualizar progreso
                            var pct = Math.round((spinCache.loaded / totalFrames) * 100);
                            videoExtractFill.style.width = pct + '%';
                            videoExtractText.textContent = 'Cargando vista 360: ' + pct + '%';

                            // Configurar canvas con el primer frame
                            if (spinCache.loaded === 1 && img.naturalWidth > 0) {
                                droneCanvas.width = img.naturalWidth;
                                droneCanvas.height = img.naturalHeight;
                                spinCache.canvasWidth = img.naturalWidth;
                                spinCache.canvasHeight = img.naturalHeight;
                                // Dibujar primer frame
                                droneCtx.drawImage(img, 0, 0, droneCanvas.width, droneCanvas.height);
                            }

                            // Listo para interacción cuando hay suficientes frames
                            if (!spinCache.ready && spinCache.loaded >= MIN_READY) {
                                spinCache.ready = true;
                                videoReady = true;
                                videoDragHint.innerHTML = '<i class="fa fa-arrows-h"></i> Arrastra para girar alrededor de la propiedad';
                                setTimeout(function() {
                                    videoDragHint.classList.remove('visible');
                                }, 3000);
                            }
                            resolve();
                        };
                        img.onerror = function() { resolve(); };
                    });
                }

                async function runQueue(queue) {
                    var idx = 0;
                    async function worker() {
                        while (idx < queue.length) {
                            var n = queue[idx++];
                            if (!spinCache.frames[n]) await loadImage(n);
                        }
                    }
                    var workers = [];
                    for (var i = 0; i < CONCURRENCY; i++) workers.push(worker());
                    await Promise.all(workers);
                }

                async function preloadFrames() {
                    // Cargar primeros frames rápido
                    var quick = [];
                    for (var i = 1; i <= MIN_READY && i <= totalFrames; i++) quick.push(i);
                    await runQueue(quick);

                    // Cargar el resto
                    var rest = [];
                    for (var i = 1; i <= totalFrames; i++) {
                        if (!spinCache.frames[i]) rest.push(i);
                    }
                    if (rest.length) await runQueue(rest);

                    // Carga completa
                    videoExtractProgress.style.display = 'none';
                    console.log('[Spin] Frame cache listo:', spinCache.loaded, 'frames');
                }

                preloadFrames();

                // Generar hotspots para el spin
                buildSpinHotspots(sceneId);
            }

            function hideVideoViewer() {
                // Abortar extracción en curso
                frameCache.aborted = true;
                frameCache.extracting = false;

                videoOverlay.style.display = 'none';
                videoExtractProgress.style.display = 'none';
                currentVideoSceneId = null;
                videoReady = false;

                // Limpiar video
                droneVideo.onseeked = null;
                droneVideo.onloadeddata = null;
                droneVideo.pause();
                droneVideo.removeAttribute('src');
                droneVideo.load();

                // Limpiar frame cache (liberar memoria)
                frameCache.frames = [];
                frameCache.times = [];
                frameCache.totalFrames = 0;
                frameCache.duration = 0;
                frameCache.currentIndex = 0;

                // Limpiar spin cache (liberar memoria)
                spinCache.frames = [];
                spinCache.totalFrames = 0;
                spinCache.currentIndex = 0;
                spinCache.framesDir = null;
                spinCache.loaded = 0;
                spinCache.ready = false;
                isSpinMode = false;

                videoHotspotsBar.innerHTML = '';
                // Remover hotspots posicionados
                var posHotspots = videoOverlay.querySelectorAll('.video-pos-hotspot');
                posHotspots.forEach(function(el) {
                    el.remove();
                });
            }

            function buildVideoHotspots(sceneId) {
                videoHotspotsBar.innerHTML = '';
                // Remover hotspots posicionados previos
                var oldPosHotspots = videoOverlay.querySelectorAll('.video-pos-hotspot');
                oldPosHotspots.forEach(function(el) {
                    el.remove();
                });

                var sc = pannellumConfig.scenes[sceneId];
                if (!sc || !sc.hotSpots) return;

                sc.hotSpots.forEach(function(hs) {
                    if (!hs.clickHandlerArgs || !hs.clickHandlerArgs.targetSceneId) return;
                    var targetId = hs.clickHandlerArgs.targetSceneId;
                    var targetScene = pannellumConfig.scenes[targetId];
                    if (!targetScene) return;

                    var displayText = hs.createTooltipArgs ? hs.createTooltipArgs.displayText : targetScene
                        .title;
                    var imageUrl = hs.createTooltipArgs ? hs.createTooltipArgs.imageUrl : null;

                    // Verificar si tiene posición en video (video_time + pos_x + pos_y)
                    var vt = hs.createTooltipArgs ? hs.createTooltipArgs.videoTime : null;
                    var px = hs.createTooltipArgs ? hs.createTooltipArgs.posX : null;
                    var py = hs.createTooltipArgs ? hs.createTooltipArgs.posY : null;

                    // Capturar targetYaw/targetPitch del hotspot
                    var hsTargetYaw = hs.clickHandlerArgs ? hs.clickHandlerArgs.targetYaw : undefined;
                    var hsTargetPitch = hs.clickHandlerArgs ? hs.clickHandlerArgs.targetPitch : undefined;

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
                        (function(tid, tYaw, tPitch) {
                            posDiv.addEventListener('click', function(e) {
                                e.stopPropagation();
                                navigateFromVideo(tid, tYaw, tPitch);
                            });
                        })(targetId, hsTargetYaw, hsTargetPitch);
                        videoOverlay.appendChild(posDiv);
                    } else {
                        // Hotspot sin posición en video → botón en barra inferior
                        var btn = document.createElement('button');
                        btn.className = 'video-hotspot-btn';
                        btn.textContent = displayText;
                        (function(tid, tYaw, tPitch) {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                navigateFromVideo(tid, tYaw, tPitch);
                            });
                        })(targetId, hsTargetYaw, hsTargetPitch);
                        videoHotspotsBar.appendChild(btn);
                    }
                });
            }

            // Actualizar visibilidad de hotspots posicionados según tiempo del video
            function updateVideoPositionedHotspots() {
                if (!currentVideoSceneId) return;
                var currentTime = getCurrentVideoTime();
                var duration = frameCache.duration || 0;
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

            // Construir hotspots para spin (mapea yaw del hotspot a frame_index)
            function buildSpinHotspots(sceneId) {
                videoHotspotsBar.innerHTML = '';
                // Remover hotspots posicionados previos
                var oldPosHotspots = videoOverlay.querySelectorAll('.video-pos-hotspot');
                oldPosHotspots.forEach(function(el) {
                    el.remove();
                });

                var sc = pannellumConfig.scenes[sceneId];
                if (!sc || !sc.hotSpots) return;

                var totalFrames = spinCache.totalFrames || sc.spinFramesCount || 72;

                sc.hotSpots.forEach(function(hs) {
                    if (!hs.clickHandlerArgs || !hs.clickHandlerArgs.targetSceneId) return;
                    var targetId = hs.clickHandlerArgs.targetSceneId;
                    var targetScene = pannellumConfig.scenes[targetId];
                    if (!targetScene) return;

                    var displayText = hs.createTooltipArgs ? hs.createTooltipArgs.displayText : targetScene.title;
                    var imageUrl = hs.createTooltipArgs ? hs.createTooltipArgs.imageUrl : null;

                    // Obtener datos de posición visual del hotspot (px, py)
                    var px = hs.createTooltipArgs ? hs.createTooltipArgs.posX : null;
                    var py = hs.createTooltipArgs ? hs.createTooltipArgs.posY : null;

                    // Obtener el yaw del hotspot para calcular el frame correspondiente
                    var hsYaw = hs.yaw !== undefined ? hs.yaw : 0;

                    // Capturar targetYaw/targetPitch del hotspot
                    var hsTargetYaw = hs.clickHandlerArgs ? hs.clickHandlerArgs.targetYaw : undefined;
                    var hsTargetPitch = hs.clickHandlerArgs ? hs.clickHandlerArgs.targetPitch : undefined;

                    // Usar posX/posY si están definidos, sino usar valores por defecto centrados
                    var posX = px !== null ? px : 50;
                    var posY = py !== null ? py : 50;

                    // Calcular el frame correspondiente al yaw del hotspot
                    // Normalizar yaw a rango [0, 360)
                    var normalizedYaw = ((hsYaw % 360) + 360) % 360;
                    // Mapear yaw a frame index (frame 1 = yaw 0, frame totalFrames = yaw ~360)
                    var frameIndex = Math.round((normalizedYaw / 360) * totalFrames) + 1;
                    if (frameIndex > totalFrames) frameIndex = 1; // wrap around

                    // Crear el hotspot posicionado
                    var posDiv = document.createElement('div');
                    posDiv.className = 'video-pos-hotspot';
                    posDiv.style.left = posX + '%';
                    posDiv.style.top = posY + '%';
                    posDiv.setAttribute('data-frame-index', frameIndex);
                    posDiv.setAttribute('data-frame-range', '3'); // visible ±3 frames (~30° con 72 frames)

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
                    (function(tid, tYaw, tPitch) {
                        posDiv.addEventListener('click', function(e) {
                            e.stopPropagation();
                            navigateFromVideo(tid, tYaw, tPitch);
                        });
                    })(targetId, hsTargetYaw, hsTargetPitch);
                    videoOverlay.appendChild(posDiv);
                });
            }

            // Actualizar visibilidad de hotspots posicionados según frame actual del spin
            function updateSpinPositionedHotspots() {
                if (!currentVideoSceneId || !isSpinMode) return;
                var currentFrame = spinCache.currentIndex;
                var totalFrames = spinCache.totalFrames;
                var posHotspots = videoOverlay.querySelectorAll('.video-pos-hotspot');

                posHotspots.forEach(function(el) {
                    var targetFrame = parseInt(el.getAttribute('data-frame-index')) || 1;
                    var range = parseInt(el.getAttribute('data-frame-range')) || 3;

                    // Calcular distancia de frames (con wrap-around)
                    var diff = Math.abs(currentFrame - targetFrame);
                    if (totalFrames > 0) {
                        diff = Math.min(diff, totalFrames - diff);
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

            function navigateFromVideo(targetSceneId, targetYawOverride, targetPitchOverride) {
                if (isTransitioning) return;
                isTransitioning = true;

                // Determinar orientación al llegar (usar originales como fallback, no la config que pudo ser sobreescrita)
                var origOri = originalSceneOrientation[targetSceneId] || { yaw: 0, pitch: 0 };
                var arrivalYaw = (targetYawOverride !== undefined && targetYawOverride !== null)
                    ? targetYawOverride : origOri.yaw;
                var arrivalPitch = (targetPitchOverride !== undefined && targetPitchOverride !== null)
                    ? targetPitchOverride : origOri.pitch;

                pendingOrientation = {
                    yaw: arrivalYaw,
                    pitch: arrivalPitch,
                    hfov: pannellumConfig.default.hfov || 100,
                    minHfov: 2
                };

                // Fade out video (usar negro para video→panorama)
                $transitionOverlay.css({
                    backgroundColor: 'rgba(0,0,0,0.95)',
                    backdropFilter: 'none',
                    webkitBackdropFilter: 'none',
                    opacity: 0,
                    transition: 'opacity 0.4s ease'
                });
                setTimeout(function() {
                    $transitionOverlay.css('opacity', 1);
                }, 10);

                setTimeout(function() {
                    hideVideoViewer();
                    // Modificar config ANTES de cargar para que abra directo en la orientación
                    if (pendingOrientation && pannellumConfig.scenes[targetSceneId]) {
                        pannellumConfig.scenes[targetSceneId].yaw = pendingOrientation.yaw;
                        pannellumConfig.scenes[targetSceneId].pitch = pendingOrientation.pitch;
                    }
                    viewer.loadScene(targetSceneId);
                    $transitionOverlay.css({
                        transition: 'opacity 0.6s ease'
                    });
                    setTimeout(function() {
                        $transitionOverlay.css('opacity', 0);
                        setTimeout(function() {
                            $transitionOverlay.css({
                                transition: 'none',
                                backgroundColor: 'rgba(255,255,255,0)',
                                backdropFilter: 'blur(0px)',
                                webkitBackdropFilter: 'blur(0px)'
                            });
                            isTransitioning = false;
                        }, 600);
                    }, 100);
                }, 500);
            }

            // --- Drag-to-scrub: Mouse events (usa frame cache = INSTANTÁNEO) ---
            videoOverlay.addEventListener('mousedown', function(e) {
                if (e.target.closest('.video-hotspot-btn, .video-pos-hotspot')) return;
                if (!videoReady) return;
                videoDragState.isDragging = true;
                videoDragState.startX = e.clientX;
                videoDragState.startNorm = getCurrentNormalized();
                videoOverlay.classList.add('active-dragging');
                videoScrubIndicator.classList.add('visible');
                e.preventDefault();
            });

            document.addEventListener('mousemove', function(e) {
                if (!videoDragState.isDragging) return;
                var deltaX = e.clientX - videoDragState.startX;
                var newNorm = videoDragState.startNorm + deltaX * videoDragState.sensitivity;
                showFrameAt(newNorm);
                var pct = Math.round(((newNorm % 1) + 1) % 1 * 100);
                videoScrubIndicator.textContent = pct + '%';
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

            // --- Drag-to-scrub: Touch events (mobile, usa frame cache) ---
            videoOverlay.addEventListener('touchstart', function(e) {
                if (e.target.closest('.video-hotspot-btn, .video-pos-hotspot')) return;
                if (!videoReady) return;
                var touch = e.touches[0];
                videoDragState.isDragging = true;
                videoDragState.startX = touch.clientX;
                videoDragState.startNorm = getCurrentNormalized();
                videoOverlay.classList.add('active-dragging');
                videoScrubIndicator.classList.add('visible');
            }, {
                passive: true
            });

            document.addEventListener('touchmove', function(e) {
                if (!videoDragState.isDragging) return;
                var touch = e.touches[0];
                var deltaX = touch.clientX - videoDragState.startX;
                var newNorm = videoDragState.startNorm + deltaX * videoDragState.sensitivity;
                showFrameAt(newNorm);
                var pct = Math.round(((newNorm % 1) + 1) % 1 * 100);
                videoScrubIndicator.textContent = pct + '%';
            }, {
                passive: true
            });

            document.addEventListener('touchend', function() {
                if (videoDragState.isDragging) {
                    videoDragState.isDragging = false;
                    videoOverlay.classList.remove('active-dragging');
                    setTimeout(function() {
                        videoScrubIndicator.classList.remove('visible');
                    }, 800);
                }
            });

            // --- Efecto de caminar: zoom continuo con blur suave (sin negro) ---
            window.walkToScene = function(targetSceneId, hotspotYaw, hotspotPitch, targetYawOverride, targetPitchOverride) {
                if (isTransitioning) return;
                isTransitioning = true;

                // Resetear estado de hover de polígonos al iniciar transición
                hoveredPolygonId = null;
                if (polygonTooltip) {
                    polygonTooltip.classList.remove('visible');
                }

                // Si el destino es video, usar transición fade en lugar de zoom
                if (isVideoScene(targetSceneId)) {
                    $transitionOverlay.css({
                        backgroundColor: 'rgba(0,0,0,0.95)',
                        backdropFilter: 'none',
                        webkitBackdropFilter: 'none',
                        opacity: 0,
                        transition: 'opacity 0.4s ease'
                    });
                    setTimeout(function() {
                        $transitionOverlay.css('opacity', 1);
                    }, 10);

                    setTimeout(function() {
                        showVideoViewer(targetSceneId);
                        var sceneName = pannellumConfig.scenes[targetSceneId]?.title || 'Escena';
                        showSceneName(sceneName);
                        $transitionOverlay.css({
                            transition: 'opacity 0.6s ease'
                        });
                        setTimeout(function() {
                            $transitionOverlay.css('opacity', 0);
                            setTimeout(function() {
                                $transitionOverlay.css({
                                    transition: 'none',
                                    backgroundColor: 'rgba(255,255,255,0)',
                                    backdropFilter: 'blur(0px)',
                                    webkitBackdropFilter: 'blur(0px)'
                                });
                                isTransitioning = false;
                            }, 600);
                        }, 100);
                    }, 500);
                    return;
                }

                var startHfov = viewer.getHfov();

                // Zoom profundo para simular caminar - a hfov tan bajo el cambio de escena es imperceptible
                var minHfov = 2;
                var zoomInDuration = 550;
                var maxBlur = 14; // px de blur máximo durante el cambio
                var startTime = Date.now();

                // Determinar orientación al llegar:
                var origOri = originalSceneOrientation[targetSceneId] || { yaw: 0, pitch: 0 };
                var arrivalYaw, arrivalPitch;

                if (targetYawOverride !== undefined && targetYawOverride !== null) {
                    arrivalYaw = targetYawOverride;
                } else {
                    arrivalYaw = origOri.yaw;
                }

                if (targetPitchOverride !== undefined && targetPitchOverride !== null) {
                    arrivalPitch = targetPitchOverride;
                } else {
                    arrivalPitch = origOri.pitch;
                }

                // Guardar datos para la nueva escena
                pendingOrientation = {
                    yaw: arrivalYaw,
                    pitch: arrivalPitch,
                    hfov: startHfov,
                    minHfov: minHfov
                };

                // Asegurar que el overlay está limpio al inicio
                $transitionOverlay.css({
                    opacity: 1,
                    backgroundColor: 'rgba(255,255,255,0)',
                    backdropFilter: 'blur(0px)',
                    webkitBackdropFilter: 'blur(0px)',
                    transition: 'none'
                });

                // Zoom IN continuo con blur progresivo (sin negro)
                function animateWalkIn() {
                    var elapsed = Date.now() - startTime;
                    var progress = Math.min(elapsed / zoomInDuration, 1);

                    // Easing: empieza lento, acelera (como dar un paso hacia adelante)
                    var eased = progress * progress * (3 - 2 * progress); // smoothstep

                    // Interpolar el zoom
                    var newHfov = startHfov - ((startHfov - minHfov) * eased);
                    viewer.setHfov(newHfov);

                    // Blur progresivo en el último 20% del zoom (más zoom visible antes del blur)
                    if (progress > 0.8) {
                        var blurProgress = (progress - 0.8) / 0.2;
                        var blurVal = blurProgress * maxBlur;
                        $transitionOverlay.css({
                            backdropFilter: 'blur(' + blurVal + 'px)',
                            webkitBackdropFilter: 'blur(' + blurVal + 'px)'
                        });
                    }

                    if (progress < 1) {
                        requestAnimationFrame(animateWalkIn);
                    } else {
                        // En este punto el hfov es tan bajo que casi no se ve nada
                        // El blur cubre cualquier cambio visual al cargar la nueva escena
                        if (pendingOrientation && pannellumConfig.scenes[targetSceneId]) {
                            pannellumConfig.scenes[targetSceneId].yaw = pendingOrientation.yaw;
                            pannellumConfig.scenes[targetSceneId].pitch = pendingOrientation.pitch;
                        }
                        // Cargar escena inmediatamente (el blur oculta el cambio)
                        viewer.loadScene(targetSceneId);
                    }
                }

                requestAnimationFrame(animateWalkIn);
            }

            // --- Cuando la escena carga, continuar el zoom out ---
            viewer.on('load', function() {
                var loadedSceneId = String(viewer.getScene());

                // Resetear estado de hover de polígonos al cambiar de escena
                hoveredPolygonId = null;
                if (polygonTooltip) {
                    polygonTooltip.classList.remove('visible');
                }

                // Las escenas de video ya no se cargan a través de Pannellum
                // Solo manejar escenas panorámicas aquí

                // Si hay video activo, ocultarlo al cargar una escena panorámica
                if (currentVideoSceneId) {
                    hideVideoViewer();
                }

                // Recrear SVG de polígonos dentro del contenedor Pannellum
                ensurePolygonSvg();
                console.log('[Polygons] Escena cargada:', viewer.getScene(), '- Polígonos disponibles:', (
                    scenePolygons[String(viewer.getScene())] || []).length);
                renderScenePolygons();

                if (pendingOrientation) {
                    // La orientación ya se aplicó via config antes de loadScene,
                    // solo forzar el zoom cercano para el efecto de llegada
                    viewer.setHfov(pendingOrientation.minHfov);

                    // Restaurar config original para que futuras navegaciones sin target usen el valor correcto
                    var loadedId = String(viewer.getScene());
                    if (originalSceneOrientation[loadedId] && pannellumConfig.scenes[loadedId]) {
                        pannellumConfig.scenes[loadedId].yaw = originalSceneOrientation[loadedId].yaw;
                        pannellumConfig.scenes[loadedId].pitch = originalSceneOrientation[loadedId].pitch;
                    }

                    var startHfov = pendingOrientation.minHfov;
                    var targetHfov = pendingOrientation.hfov;
                    var isMenuTransition = !!pendingOrientation.fromMenu;
                    var maxBlurOut = 14;
                    var zoomOutDuration = isMenuTransition ? 400 : 450;
                    var startTime = Date.now();

                    if (isMenuTransition) {
                        // Menú: zoom out simple sin blur
                        function animateMenuZoomOut() {
                            var elapsed = Date.now() - startTime;
                            var progress = Math.min(elapsed / zoomOutDuration, 1);
                            var eased = 1 - Math.pow(1 - progress, 3);

                            var newHfov = startHfov + ((targetHfov - startHfov) * eased);
                            viewer.setHfov(newHfov);

                            if (progress < 1) {
                                requestAnimationFrame(animateMenuZoomOut);
                            } else {
                                isTransitioning = false;
                                pendingOrientation = null;
                            }
                        }
                        requestAnimationFrame(animateMenuZoomOut);
                    } else {
                        // Hotspot: zoom out con blur desvaneciéndose (efecto caminar)
                        function animateWalkOut() {
                            var elapsed = Date.now() - startTime;
                            var progress = Math.min(elapsed / zoomOutDuration, 1);

                            // Easing: desacelera suavemente (como desacelerar al llegar)
                            var eased = 1 - Math.pow(1 - progress, 2.5);

                            var newHfov = startHfov + ((targetHfov - startHfov) * eased);
                            viewer.setHfov(newHfov);

                            // Blur se desvanece durante el primer 50% del zoom out
                            if (progress < 0.5) {
                                var blurVal = maxBlurOut * (1 - progress / 0.5);
                                $transitionOverlay.css({
                                    backdropFilter: 'blur(' + blurVal + 'px)',
                                    webkitBackdropFilter: 'blur(' + blurVal + 'px)'
                                });
                            } else {
                                $transitionOverlay.css({
                                    backdropFilter: 'blur(0px)',
                                    webkitBackdropFilter: 'blur(0px)'
                                });
                            }

                            if (progress < 1) {
                                requestAnimationFrame(animateWalkOut);
                            } else {
                                isTransitioning = false;
                                pendingOrientation = null;
                                $transitionOverlay.css({
                                    opacity: 0,
                                    backdropFilter: 'blur(0px)',
                                    webkitBackdropFilter: 'blur(0px)'
                                });
                            }
                        }
                        requestAnimationFrame(animateWalkOut);
                    }
                } else {
                    isTransitioning = false;
                    $transitionOverlay.css({
                        opacity: 0,
                        backdropFilter: 'blur(0px)',
                        webkitBackdropFilter: 'blur(0px)'
                    });
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
                    // Usar el ID real de la primera escena (puede ser video)
                    IDLE_ROTATE.enable(); // ✅ empieza estático y si pasan 10s sin mover nada, gira
                    var firstSceneId = realFirstSceneId;
                    if (pannellumConfig.scenes[firstSceneId]) {
                        showSceneName(pannellumConfig.scenes[firstSceneId].title);
                        // Si la primera escena es video, mostrar el visor de video
                        if (firstSceneIsVideo) {
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

                    // Resetear estado de hover de polígonos
                    hoveredPolygonId = null;
                    if (polygonTooltip) {
                        polygonTooltip.classList.remove('visible');
                    }

                    // Cerrar el menú lateral
                    $('#menu-trigger-ctrl').removeClass('is-clicked');
                    $('body').removeClass('menu-is-open');

                    var targetIsVideo = isVideoScene(sceneId);

                    // Función para transición fade (usada para video)
                    function fadeTransition(onMiddle) {
                        $transitionOverlay.css({
                            backgroundColor: 'rgba(0,0,0,0.95)',
                            backdropFilter: 'none',
                            webkitBackdropFilter: 'none',
                            opacity: 0,
                            transition: 'opacity 0.4s ease'
                        });
                        setTimeout(function() {
                            $transitionOverlay.css('opacity', 1);
                        }, 10);
                        setTimeout(function() {
                            onMiddle();
                            $transitionOverlay.css({
                                transition: 'opacity 0.6s ease'
                            });
                            setTimeout(function() {
                                $transitionOverlay.css('opacity', 0);
                                setTimeout(function() {
                                    $transitionOverlay.css({
                                        transition: 'none',
                                        backgroundColor: 'rgba(255,255,255,0)',
                                        backdropFilter: 'blur(0px)',
                                        webkitBackdropFilter: 'blur(0px)'
                                    });
                                    isTransitioning = false;
                                }, 600);
                            }, 100);
                        }, 500);
                    }

                    // Si estamos en un video scene
                    if (currentVideoSceneId) {
                        fadeTransition(function() {
                            hideVideoViewer();
                            pendingOrientation = null;
                            if (targetIsVideo) {
                                // Video → Video
                                showVideoViewer(sceneId);
                                showSceneName(pannellumConfig.scenes[sceneId]?.title || 'Escena');
                            } else {
                                // Video → Panorama
                                viewer.loadScene(sceneId);
                            }
                        });
                        return;
                    }

                    // Si el destino es video (Panorama → Video)
                    if (targetIsVideo) {
                        fadeTransition(function() {
                            showVideoViewer(sceneId);
                            showSceneName(pannellumConfig.scenes[sceneId]?.title || 'Escena');
                        });
                        return;
                    }

                    // Panorama → Panorama: usar zoom (menú = sin blur)
                    pendingOrientation = {
                        yaw: viewer.getYaw(),
                        pitch: viewer.getPitch(),
                        hfov: viewer.getHfov(),
                        minHfov: 15,
                        fromMenu: true
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

            // ===== MATTERPORT STYLE UI - JavaScript =====

            // Lista de IDs de escenas para navegación
            var sceneIds = Object.keys(pannellumConfig.scenes);
            var currentSceneIndex = 0;

            // Función para actualizar escena activa en el carrusel
            function updateActiveSceneInCarousel(sceneId) {
                $('.matterport-scene-item').removeClass('active');
                $('.matterport-scene-item[data-scene-id="' + sceneId + '"]').addClass('active');

                // Actualizar índice actual
                currentSceneIndex = sceneIds.indexOf(String(sceneId));
                if (currentSceneIndex === -1) currentSceneIndex = 0;

                // Scroll al elemento activo
                var $activeItem = $('.matterport-scene-item.active');
                if ($activeItem.length) {
                    var $wrapper = $('.matterport-scenes-wrapper');
                    var scrollLeft = $activeItem.position().left + $wrapper.scrollLeft() - ($wrapper.width() / 2) + ($activeItem.width() / 2);
                    $wrapper.animate({ scrollLeft: scrollLeft }, 300);
                }
            }

            // Escuchar cambios de escena en Pannellum
            viewer.on('scenechange', function(sceneId) {
                updateActiveSceneInCarousel(sceneId);
            });

            // Toggle del carrusel de escenas
            $('#matterport-carousel-toggle, #matterport-toggle-carousel').on('click', function() {
                var $carousel = $('#matterport-scenes-carousel');
                var $toggle = $('#matterport-carousel-toggle');
                var $toolbarBtn = $('#matterport-toggle-carousel');

                $carousel.toggleClass('collapsed');
                $toggle.toggleClass('collapsed');

                if ($carousel.hasClass('collapsed')) {
                    $toolbarBtn.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                } else {
                    $toolbarBtn.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                }
            });

            // Clic en una escena del carrusel
            $('.matterport-scene-item').on('click', function() {
                var sceneId = $(this).data('scene-id');
                var sceneType = $(this).data('scene-type');

                if (sceneType === 'video') {
                    // Mostrar visor de video
                    if (typeof showVideoViewer === 'function') {
                        fadeTransition(function() {
                            showVideoViewer(sceneId);
                            showSceneName(pannellumConfig.scenes[sceneId]?.title || 'Escena');
                        });
                    }
                } else {
                    // Cargar escena panorámica
                    viewer.loadScene(String(sceneId));
                }
            });

            // Auto-rotar toggle
            var autoRotateEnabled = false;
            $('#matterport-autorotate').on('click', function() {
                autoRotateEnabled = !autoRotateEnabled;
                var $btn = $(this);

                if (autoRotateEnabled) {
                    $btn.addClass('active');
                    $btn.find('i').removeClass('fa-play').addClass('fa-pause');
                    IDLE_ROTATE.enable();
                    // Forzar inicio inmediato
                    if (typeof viewer.startAutoRotate === 'function') {
                        viewer.startAutoRotate(-1);
                    } else if (typeof viewer.setAutoRotate === 'function') {
                        viewer.setAutoRotate(-1);
                    }
                } else {
                    $btn.removeClass('active');
                    $btn.find('i').removeClass('fa-pause').addClass('fa-play');
                    IDLE_ROTATE.disable();
                }
            });

            // Navegación entre escenas
            $('#matterport-prev-scene').on('click', function() {
                currentSceneIndex = (currentSceneIndex - 1 + sceneIds.length) % sceneIds.length;
                var sceneId = sceneIds[currentSceneIndex];
                var sceneConfig = pannellumConfig.scenes[sceneId];

                if (sceneConfig && sceneConfig.type === 'video') {
                    if (typeof showVideoViewer === 'function') {
                        fadeTransition(function() {
                            showVideoViewer(sceneId);
                            showSceneName(sceneConfig.title || 'Escena');
                        });
                    }
                } else {
                    viewer.loadScene(sceneId);
                }
            });

            $('#matterport-next-scene').on('click', function() {
                currentSceneIndex = (currentSceneIndex + 1) % sceneIds.length;
                var sceneId = sceneIds[currentSceneIndex];
                var sceneConfig = pannellumConfig.scenes[sceneId];

                if (sceneConfig && sceneConfig.type === 'video') {
                    if (typeof showVideoViewer === 'function') {
                        fadeTransition(function() {
                            showVideoViewer(sceneId);
                            showSceneName(sceneConfig.title || 'Escena');
                        });
                    }
                } else {
                    viewer.loadScene(sceneId);
                }
            });

            // Pantalla completa
            $('#matterport-fullscreen').on('click', function() {
                var elem = document.documentElement;
                var $btn = $(this);

                if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                    if (elem.requestFullscreen) {
                        elem.requestFullscreen();
                    } else if (elem.webkitRequestFullscreen) {
                        elem.webkitRequestFullscreen();
                    }
                    $btn.find('i').removeClass('fa-expand').addClass('fa-compress');
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    }
                    $btn.find('i').removeClass('fa-compress').addClass('fa-expand');
                }
            });

            // Detectar salida de pantalla completa
            document.addEventListener('fullscreenchange', function() {
                if (!document.fullscreenElement) {
                    $('#matterport-fullscreen i').removeClass('fa-compress').addClass('fa-expand');
                }
            });

            // Compartir
            $('#matterport-share').on('click', function() {
                var url = window.location.href;
                var title = '{{ $fscene ? ($fscene->property_name ?? "Virtual Tour") : "Virtual Tour" }}';

                if (navigator.share) {
                    navigator.share({
                        title: title,
                        text: 'Mira este tour virtual: ' + title,
                        url: url
                    }).catch(function() {});
                } else {
                    // Fallback: copiar al portapapeles
                    navigator.clipboard.writeText(url).then(function() {
                        alert('Enlace copiado al portapapeles');
                    }).catch(function() {
                        prompt('Copia este enlace:', url);
                    });
                }
            });

            // Ocultar/mostrar UI durante el intro
            function hideMatterportUI() {
                $('.matterport-header, .matterport-toolbar, .matterport-actions, .matterport-scenes-carousel, .matterport-carousel-toggle').css('opacity', '0').css('pointer-events', 'none');
            }

            function showMatterportUI() {
                $('.matterport-header, .matterport-toolbar, .matterport-actions, .matterport-scenes-carousel, .matterport-carousel-toggle').css('opacity', '1').css('pointer-events', 'auto');
            }

            // Inicialmente ocultar UI (se muestra cuando inicia el tour)
            hideMatterportUI();

            // Mostrar UI cuando se inicia el tour
            $(document).on('click', '#btn-start-tour', function() {
                setTimeout(showMatterportUI, 800);
            });

            // Si hay solo una escena o se quiere iniciar automáticamente
            if ($('.home-content-table').is(':hidden') || $('.home-content-table').css('display') === 'none') {
                showMatterportUI();
            }

            // Menú de búsqueda/escenas (reutiliza el menú antiguo)
            $('#matterport-menu-toggle').on('click', function(e) {
                e.stopPropagation();
                var $menu = $('#menu-nav-wrap');
                $menu.toggleClass('matterport-menu-open');

                // Cambiar icono
                var $icon = $(this).find('i');
                if ($menu.hasClass('matterport-menu-open')) {
                    $icon.removeClass('fa-search').addClass('fa-times');
                } else {
                    $icon.removeClass('fa-times').addClass('fa-search');
                }
            });

            // Cerrar menú al hacer clic fuera
            $(document).on('click', function(e) {
                var $menu = $('#menu-nav-wrap');
                var $toggle = $('#matterport-menu-toggle');

                if ($menu.hasClass('matterport-menu-open') &&
                    !$menu.is(e.target) &&
                    $menu.has(e.target).length === 0 &&
                    !$toggle.is(e.target) &&
                    $toggle.has(e.target).length === 0) {
                    $menu.removeClass('matterport-menu-open');
                    $toggle.find('i').removeClass('fa-times').addClass('fa-search');
                }
            });

            // Cerrar menú al seleccionar una escena
            $('#menu-nav-wrap .js-load-scene').on('click', function() {
                $('#menu-nav-wrap').removeClass('matterport-menu-open');
                $('#matterport-menu-toggle i').removeClass('fa-times').addClass('fa-search');
            });

        })();
    </script>

</body>

</html>
