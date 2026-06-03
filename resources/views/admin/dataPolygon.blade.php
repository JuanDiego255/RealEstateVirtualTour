{{-- Vista Polígonos/Marcadores (Blade) --}}
<div class="alert alert-info">
    <strong>Marcadores de terreno:</strong> Seleccione una escena (ideal vistas aéreas con dron) y dibuje polígonos haciendo clic en los vértices. Útil para marcar lotes, áreas o límites de propiedad.
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Configuración del Polígono</h5>
            </div>
            <div class="card-body">
                <input type="hidden" id="polygon-property-id" value="{{ $id }}">

                <div class="form-group">
                    <label for="polygon-scene-select">Seleccionar Escena</label>
                    <select class="form-control" id="polygon-scene-select">
                        <option value="">-- Seleccione una escena --</option>
                        @foreach ($scene as $item)
                            <option value="{{ $item->id }}"
                                    data-image="{{ isset($item->image) ? route('file', $item->image) : '' }}">
                                {{ $item->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="polygon-form-container" style="display: none;">
                    <hr>
                    <div class="form-group">
                        <label for="polygon-name">Nombre del área</label>
                        <input type="text" class="form-control" id="polygon-name" placeholder="Ej: Lote A-1, Área común">
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="polygon-fill-color">Color de relleno</label>
                                <input type="color" class="form-control" id="polygon-fill-color" value="#00FF00">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="polygon-fill-opacity">Opacidad</label>
                                <input type="range" class="form-control-range" id="polygon-fill-opacity"
                                       min="0" max="1" step="0.05" value="0.35">
                                <small class="text-muted">Valor: <span id="opacity-value">0.35</span></small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="polygon-stroke-color">Color del borde</label>
                                <input type="color" class="form-control" id="polygon-stroke-color" value="#FFFFFF">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="polygon-stroke-width">Grosor del borde</label>
                                <input type="number" class="form-control" id="polygon-stroke-width"
                                       min="0" max="10" value="2">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Puntos del polígono: <span id="points-count" class="badge badge-info">0</span></label>
                        <div id="points-list" class="small" style="max-height: 120px; overflow-y: auto;"></div>
                    </div>

                    {{-- Medidas de lados --}}
                    <div id="edge-labels-container" style="display: none;">
                        <hr>
                        <label><i class="fa fa-arrows-h"></i> Medidas por lado <small class="text-muted">(opcional)</small></label>
                        <div id="edge-labels-list" class="small" style="max-height: 150px; overflow-y: auto;"></div>
                    </div>

                    {{-- Texto interior --}}
                    <div id="interior-text-container" style="display: none;">
                        <hr>
                        <div class="form-group mb-1">
                            <label for="polygon-interior-text"><i class="fa fa-font"></i> Texto interior <small class="text-muted">(opcional, ej: 450 m²)</small></label>
                            <input type="text" class="form-control form-control-sm" id="polygon-interior-text" placeholder="Ej: 450 m²">
                        </div>
                    </div>
                    <hr>

                    <div class="btn-group btn-block mb-2">
                        <button type="button" class="btn btn-warning btn-sm" id="btn-undo-point">
                            <i class="fa fa-undo"></i> Deshacer punto
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" id="btn-clear-points">
                            <i class="fa fa-trash"></i> Limpiar todo
                        </button>
                    </div>

                    <button type="button" class="btn btn-success btn-block" id="btn-save-polygon">
                        <i class="fa fa-save"></i> Guardar Polígono
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de polígonos existentes -->
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Polígonos Guardados</h5>
            </div>
            <div class="card-body p-0">
                <div id="polygons-list" class="list-group list-group-flush">
                    <div class="list-group-item text-muted text-center">
                        Seleccione una escena para ver sus polígonos
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Visor - Haga clic para agregar puntos del polígono</h5>
                <span id="polygon-mode-badge" class="badge badge-success" style="display:none;">Modo dibujo activo</span>
            </div>
            <div class="card-body p-0">
                <div id="polygon-panorama" style="width: 100%; height: 500px; background: #1a1a1a; position: relative;">
                    <div class="d-flex align-items-center justify-content-center h-100 text-white">
                        <p>Seleccione una escena para comenzar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript para dibujo de polígonos --}}
<script>
$(document).ready(function() {
    var polygonViewer = null;
    var currentPoints = [];
    var currentSceneId = null;
    var savedPolygons = [];
    var editingPolygonId = null;
    var svgOverlay = null;
    var polygonUpdateTimer = null; // kept for clearInterval on scene change
    var _previewCursorPos = null;
    var _polyRafRunning = false;

    // URL base para AJAX de polígonos
    var polygonBaseUrl = "{{ url('/') }}";

    // Mapa de imágenes de escenas
    var sceneImageMap = {
        @foreach ($scene as $item)
            {{ $item->id }}: "{{ isset($item->image) ? route('file', $item->image) : '' }}",
        @endforeach
    };

    // Actualizar valor de opacidad
    $('#polygon-fill-opacity').on('input', function() {
        $('#opacity-value').text($(this).val());
    });

    // ====== Crear SVG overlay sobre Pannellum ======
    function ensureSvgOverlay() {
        // Buscar el contenedor real de Pannellum (el .pnlm-container)
        var pnlmContainer = $('#polygon-panorama .pnlm-render-container').parent();
        if (pnlmContainer.length === 0) {
            pnlmContainer = $('#polygon-panorama');
        }

        // Remover SVG anterior si existe
        $('#polygon-svg-overlay').remove();

        svgOverlay = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svgOverlay.setAttribute('id', 'polygon-svg-overlay');
        svgOverlay.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:2;';
        pnlmContainer[0].appendChild(svgOverlay);
    }

    // ====== Seleccionar escena ======
    $('#polygon-scene-select').on('change', function() {
        var sceneId = $(this).val();
        if (!sceneId) {
            $('#polygon-form-container').hide();
            $('#polygon-mode-badge').hide();
            return;
        }

        currentSceneId = sceneId;
        var imageUrl = sceneImageMap[sceneId];

        if (!imageUrl) {
            alert('Esta escena no tiene imagen');
            return;
        }

        // Mostrar formulario
        $('#polygon-form-container').show();
        $('#polygon-mode-badge').show();
        clearPoints();

        // Destruir visor anterior
        _polyRafRunning = false; // detiene el rAF loop de la escena anterior
        _previewCursorPos = null;
        if (polygonViewer) {
            try { polygonViewer.destroy(); } catch(e) {}
        }

        // Crear nuevo visor
        polygonViewer = pannellum.viewer('polygon-panorama', {
            type: "equirectangular",
            panorama: imageUrl,
            autoLoad: true,
            showControls: true,
            mouseZoom: true,
            draggable: true
        });

        // Esperar a que cargue para crear el SVG overlay
        polygonViewer.on('load', function() {
            ensureSvgOverlay();
            _previewCursorPos = null;

            // rAF loop: sólo re-renderiza cuando la vista cambia
            _polyRafRunning = false;
            var _lastRafState = '';
            _polyRafRunning = true;
            (function _rafLoop() {
                if (!polygonViewer || !_polyRafRunning) return;
                var st = polygonViewer.getYaw().toFixed(2) + ',' +
                         polygonViewer.getPitch().toFixed(2) + ',' +
                         polygonViewer.getHfov().toFixed(2);
                if (st !== _lastRafState) {
                    _lastRafState = st;
                    if (currentPoints.length > 0 || savedPolygons.length > 0) renderAllPolygons();
                }
                requestAnimationFrame(_rafLoop);
            })();

            // Línea de previsualización: desde el último punto hasta el cursor
            var _panoEl = document.getElementById('polygon-panorama');
            _panoEl.addEventListener('mousemove', function(ev) {
                if (currentPoints.length > 0) {
                    var rect = _panoEl.getBoundingClientRect();
                    _previewCursorPos = { x: ev.clientX - rect.left, y: ev.clientY - rect.top };
                    renderAllPolygons();
                }
            });
            _panoEl.addEventListener('mouseleave', function() {
                if (_previewCursorPos) { _previewCursorPos = null; renderAllPolygons(); }
            });
        });

        // Detectar clic vs arrastre
        var mouseDownPos = null;
        var mouseDownTime = 0;

        polygonViewer.on('mousedown', function(ev) {
            mouseDownPos = { x: ev.clientX, y: ev.clientY };
            mouseDownTime = Date.now();
        });

        polygonViewer.on('mouseup', function(ev) {
            if (!mouseDownPos) return;

            var dx = ev.clientX - mouseDownPos.x;
            var dy = ev.clientY - mouseDownPos.y;
            var distance = Math.sqrt(dx * dx + dy * dy);
            var elapsed = Date.now() - mouseDownTime;

            // Solo considerar como clic si no se movió mucho y fue rápido
            if (distance < 5 && elapsed < 300) {
                var coords = polygonViewer.mouseEventToCoords(ev);
                if (coords) {
                    addPoint(coords[1], coords[0]); // yaw, pitch
                }
            }

            mouseDownPos = null;
        });

        // Cargar polígonos existentes de esta escena
        loadPolygons(sceneId);
    });

    // ====== Agregar punto ======
    function addPoint(yaw, pitch) {
        currentPoints.push({
            yaw: parseFloat(yaw.toFixed(3)),
            pitch: parseFloat(pitch.toFixed(3))
        });
        updatePointsList();
        renderAllPolygons();
    }

    // ====== Actualizar lista visual de puntos ======
    function updatePointsList() {
        $('#points-count').text(currentPoints.length);
        var html = '';
        currentPoints.forEach(function(p, i) {
            html += '<div class="mb-1"><span class="badge badge-danger">' + (i + 1) + '</span> ';
            html += 'yaw: ' + p.yaw + ', pitch: ' + p.pitch + '</div>';
        });
        if (currentPoints.length === 0) {
            html = '<em class="text-muted">Haga clic en el visor para agregar puntos</em>';
        } else if (currentPoints.length < 2) {
            html += '<div class="text-warning mt-1"><small>Agrega un punto más para formar una línea</small></div>';
        } else if (currentPoints.length === 2) {
            html += '<div class="text-info mt-1"><small>Línea de 2 puntos (agrega más para un polígono)</small></div>';
        } else {
            html += '<div class="text-success mt-1"><small>Polígono válido (' + currentPoints.length + ' puntos)</small></div>';
        }
        $('#points-list').html(html);
        updateEdgeLabelsUI();
    }

    // ====== Generar inputs de medidas por lado ======
    function updateEdgeLabelsUI() {
        if (currentPoints.length < 2) {
            $('#edge-labels-container').hide();
            $('#interior-text-container').hide();
            return;
        }

        $('#edge-labels-container').show();
        // Texto interior sólo tiene sentido con ≥3 puntos (polígono)
        if (currentPoints.length >= 3) {
            $('#interior-text-container').show();
        } else {
            $('#interior-text-container').hide();
        }

        // Para una línea (2 pts) hay 1 lado; para polígono los lados se cierran
        var closed = currentPoints.length >= 3;
        var numEdges = closed ? currentPoints.length : currentPoints.length - 1;

        var container = $('#edge-labels-list');
        var existingValues = [];
        container.find('.edge-label-input').each(function() {
            existingValues.push($(this).val());
        });

        var html = '';
        for (var i = 0; i < numEdges; i++) {
            var nextIdx = closed ? (i + 1) % currentPoints.length : i + 1;
            var prevVal = (i < existingValues.length) ? existingValues[i] : '';
            var isSmooth = !!(currentPoints[i] && currentPoints[i].smooth);
            var smoothClass = isSmooth ? 'btn-primary' : 'btn-outline-secondary';
            var smoothTitle = isSmooth ? 'Curva activa — clic para recta' : 'Línea recta — clic para curva';

            html += '<div class="input-group input-group-sm mb-1">';
            html += '<div class="input-group-prepend"><span class="input-group-text" style="min-width:70px;">Lado ' + (i + 1) + '-' + (nextIdx + 1) + '</span></div>';
            html += '<input type="text" class="form-control edge-label-input" data-edge="' + i + '" placeholder="Ej: 23 mts" value="' + prevVal + '">';
            html += '<div class="input-group-append">';
            html += '<button type="button" class="btn ' + smoothClass + ' btn-curve-toggle" data-edge="' + i + '" title="' + smoothTitle + '">〜</button>';
            html += '</div>';
            html += '</div>';
        }
        container.html(html);
    }

    // ====== Obtener edge labels del formulario ======
    function getEdgeLabelsFromForm() {
        var labels = [];
        var hasAny = false;
        $('.edge-label-input').each(function() {
            var val = $(this).val().trim();
            labels.push(val);
            if (val) hasAny = true;
        });
        return hasAny ? labels : null;
    }

    // ====== Re-renderizar al cambiar medidas o texto interior ======
    $(document).on('input', '.edge-label-input, #polygon-interior-text', function() {
        renderAllPolygons();
    });

    // ====== Toggle curva/recta por segmento ======
    $(document).on('click', '.btn-curve-toggle', function() {
        var edgeIdx = parseInt($(this).data('edge'));
        if (edgeIdx >= 0 && edgeIdx < currentPoints.length) {
            currentPoints[edgeIdx].smooth = !currentPoints[edgeIdx].smooth;
            updateEdgeLabelsUI();
            renderAllPolygons();
        }
    });

    // ====== Deshacer / Limpiar ======
    $('#btn-undo-point').on('click', function() {
        if (currentPoints.length > 0) {
            currentPoints.pop();
            updatePointsList();
            renderAllPolygons();
        }
    });

    $('#btn-clear-points').on('click', function() {
        clearPoints();
    });

    function clearPoints() {
        currentPoints = [];
        editingPolygonId = null;
        updatePointsList();
        renderAllPolygons();
        $('#polygon-name').val('');
        $('#polygon-fill-color').val('#00FF00');
        $('#polygon-fill-opacity').val(0.35);
        $('#opacity-value').text('0.35');
        $('#polygon-stroke-color').val('#FFFFFF');
        $('#polygon-stroke-width').val(2);
        $('#polygon-interior-text').val('');
        $('#edge-labels-list').empty();
        $('#edge-labels-container').hide();
        $('#interior-text-container').hide();
    }

    // ====== Obtener coordenadas de pantalla desde yaw/pitch ======
    // Implementación manual ya que pitchAndYawToScreen no existe en Pannellum 2.5.6
    function getScreenCoords(targetYaw, targetPitch) {
        if (!polygonViewer) return null;

        try {
            // Estado actual del visor
            var vYaw = polygonViewer.getYaw();
            var vPitch = polygonViewer.getPitch();
            var hfov = polygonViewer.getHfov();

            var container = document.getElementById('polygon-panorama');
            var width = container.clientWidth;
            var height = container.clientHeight;

            // Convertir a radianes
            var yawRad = (targetYaw - vYaw) * Math.PI / 180;
            var pitchRad = targetPitch * Math.PI / 180;
            var vPitchRad = vPitch * Math.PI / 180;
            var hfovRad = hfov * Math.PI / 180;

            // Punto en coordenadas 3D (esfera unitaria, relativo al yaw de la cámara)
            var x = Math.cos(pitchRad) * Math.sin(yawRad);
            var y = Math.sin(pitchRad);
            var z = Math.cos(pitchRad) * Math.cos(yawRad);

            // Rotar por el pitch de la cámara (eje X)
            var cosPitch = Math.cos(vPitchRad);
            var sinPitch = Math.sin(vPitchRad);
            var x2 = x;
            var y2 = y * cosPitch - z * sinPitch;
            var z2 = y * sinPitch + z * cosPitch;

            // Si el punto está detrás de la cámara, no mostrar
            if (z2 <= 0.01) return null;

            // Proyección perspectiva
            var focalLength = width / (2 * Math.tan(hfovRad / 2));

            var screenX = (x2 / z2) * focalLength + width / 2;
            var screenY = -(y2 / z2) * focalLength + height / 2;

            // Verificar que está dentro de la pantalla (con margen)
            if (screenX < -50 || screenX > width + 50 || screenY < -50 || screenY > height + 50) {
                return null;
            }

            return { x: screenX, y: screenY };
        } catch(e) {
            console.warn('Error calculando coordenadas:', e);
        }
        return null;
    }

    // ====== Renderizar todos los polígonos en SVG ======
    function renderAllPolygons() {
        if (!svgOverlay || !polygonViewer) return;

        // Limpiar SVG
        while (svgOverlay.firstChild) {
            svgOverlay.removeChild(svgOverlay.firstChild);
        }

        // 1. Dibujar polígonos guardados
        savedPolygons.forEach(function(poly) {
            var pts = poly.points;
            if (!pts || !Array.isArray(pts)) {
                try { pts = typeof pts === 'string' ? JSON.parse(pts) : pts; } catch(e) { return; }
            }
            var eLabels = poly.edge_labels;
            if (typeof eLabels === 'string') {
                try { eLabels = JSON.parse(eLabels); } catch(e) { eLabels = null; }
            }
            if (pts.length >= 3) {
                drawPolygonSVG(pts, poly.fill_color, poly.fill_opacity, poly.stroke_color, poly.stroke_width, eLabels, poly.interior_text);
            }
        });

        // 2. Dibujar polígono actual (en edición)
        if (currentPoints.length >= 3) {
            var fillColor = $('#polygon-fill-color').val();
            var fillOpacity = parseFloat($('#polygon-fill-opacity').val());
            var strokeColor = $('#polygon-stroke-color').val();
            var strokeWidth = parseInt($('#polygon-stroke-width').val());
            var currentEdgeLabels = getEdgeLabelsFromForm();
            var currentInteriorText = $('#polygon-interior-text').val().trim() || null;
            drawPolygonSVG(currentPoints, fillColor, fillOpacity, strokeColor, strokeWidth, currentEdgeLabels, currentInteriorText);
        }

        // 3. Dibujar líneas entre puntos actuales (vista previa cuando < 3 puntos)
        if (currentPoints.length === 2) {
            var prevScreenPts = [];
            currentPoints.forEach(function(p) { prevScreenPts.push(getScreenCoords(p.yaw, p.pitch)); });
            var prevLineData = buildPolyPathData(prevScreenPts, currentPoints, false);
            if (prevLineData) {
                var line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                line.setAttribute('d', prevLineData);
                line.setAttribute('fill', 'none');
                line.setAttribute('stroke', $('#polygon-stroke-color').val());
                line.setAttribute('stroke-width', 2);
                line.setAttribute('stroke-dasharray', '5,5');
                svgOverlay.appendChild(line);
            }
        }

        // 4. Dibujar marcadores de puntos actuales
        currentPoints.forEach(function(p, i) {
            var sc = getScreenCoords(p.yaw, p.pitch);
            if (sc) {
                // Círculo rojo
                var circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', sc.x);
                circle.setAttribute('cy', sc.y);
                circle.setAttribute('r', 7);
                circle.setAttribute('fill', '#FF0000');
                circle.setAttribute('stroke', '#FFFFFF');
                circle.setAttribute('stroke-width', 2);
                svgOverlay.appendChild(circle);

                // Número del punto
                var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', sc.x);
                text.setAttribute('y', sc.y + 4);
                text.setAttribute('fill', '#FFFFFF');
                text.setAttribute('font-size', '10');
                text.setAttribute('font-weight', 'bold');
                text.setAttribute('text-anchor', 'middle');
                text.textContent = (i + 1);
                svgOverlay.appendChild(text);
            }
        });

        // 5. Línea fantasma desde el último punto hasta el cursor
        if (_previewCursorPos && currentPoints.length > 0) {
            var lastPt = currentPoints[currentPoints.length - 1];
            var lastSc = getScreenCoords(lastPt.yaw, lastPt.pitch);
            if (lastSc) {
                var ghostLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                ghostLine.setAttribute('x1', lastSc.x);
                ghostLine.setAttribute('y1', lastSc.y);
                ghostLine.setAttribute('x2', _previewCursorPos.x);
                ghostLine.setAttribute('y2', _previewCursorPos.y);
                ghostLine.setAttribute('stroke', '#FF9900');
                ghostLine.setAttribute('stroke-width', '2');
                ghostLine.setAttribute('stroke-dasharray', '6,3');
                ghostLine.setAttribute('opacity', '0.85');
                svgOverlay.appendChild(ghostLine);
            }
        }
    }

    // ====== Construir path SVG con curvas Catmull-Rom opcionales por segmento ======
    function buildPolyPathData(screenPoints, sourcePoints, closed) {
        var n = screenPoints.length;
        var pathData = '';
        var started = false;
        var lastValidIdx = -1;

        for (var i = 0; i < n; i++) {
            var sc = screenPoints[i];
            if (!sc) continue;

            if (!started) {
                pathData = 'M ' + sc.x.toFixed(1) + ' ' + sc.y.toFixed(1);
                started = true;
                lastValidIdx = i;
                continue;
            }

            var prevSrc = sourcePoints && sourcePoints[lastValidIdx];
            if (prevSrc && prevSrc.smooth) {
                // Catmull-Rom → Cubic Bezier
                var prevPrevIdx = lastValidIdx > 0 ? lastValidIdx - 1 : (closed ? n - 1 : lastValidIdx);
                var nextIdx     = i < n - 1 ? i + 1 : (closed ? 0 : i);
                var P0 = screenPoints[prevPrevIdx] || screenPoints[lastValidIdx];
                var P1 = screenPoints[lastValidIdx];
                var P2 = sc;
                var P3 = screenPoints[nextIdx] || sc;

                var cp1x = P1.x + (P2.x - P0.x) / 6;
                var cp1y = P1.y + (P2.y - P0.y) / 6;
                var cp2x = P2.x - (P3.x - P1.x) / 6;
                var cp2y = P2.y - (P3.y - P1.y) / 6;

                pathData += ' C ' + cp1x.toFixed(1) + ',' + cp1y.toFixed(1) +
                            ' ' + cp2x.toFixed(1) + ',' + cp2y.toFixed(1) +
                            ' ' + sc.x.toFixed(1) + ',' + sc.y.toFixed(1);
            } else {
                pathData += ' L ' + sc.x.toFixed(1) + ' ' + sc.y.toFixed(1);
            }
            lastValidIdx = i;
        }

        if (!started) return null;
        if (closed) pathData += ' Z';
        return pathData;
    }

    // ====== Dibujar un polígono en SVG con etiquetas ======
    function drawPolygonSVG(points, fillColor, fillOpacity, strokeColor, strokeWidth, edgeLabels, interiorText) {
        var screenPoints = [];
        var validPoints = 0;

        points.forEach(function(p) {
            var sc = getScreenCoords(p.yaw, p.pitch);
            screenPoints.push(sc);
            if (sc) validPoints++;
        });

        if (validPoints < 2) return;

        var closed = points.length >= 3;
        var pathData = buildPolyPathData(screenPoints, points, closed);
        if (!pathData) return;

        var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', pathData);
        path.setAttribute('fill', closed ? fillColor : 'none');
        path.setAttribute('fill-opacity', closed ? fillOpacity : 0);
        path.setAttribute('stroke', strokeColor);
        path.setAttribute('stroke-width', strokeWidth);
        svgOverlay.appendChild(path);

        // Dibujar etiquetas de lados
        if (edgeLabels && Array.isArray(edgeLabels)) {
            drawEdgeLabels(screenPoints, edgeLabels, strokeColor);
        }

        // Dibujar texto interior
        if (interiorText) {
            drawInteriorText(screenPoints, interiorText);
        }
    }

    // ====== Dibujar medidas en los lados del polígono ======
    function drawEdgeLabels(screenPoints, labels, strokeColor) {
        var numPoints = screenPoints.length;
        for (var i = 0; i < numPoints; i++) {
            var label = labels[i];
            if (!label) continue;

            var p1 = screenPoints[i];
            var p2 = screenPoints[(i + 1) % numPoints];
            if (!p1 || !p2) continue;

            // Punto medio del lado
            var mx = (p1.x + p2.x) / 2;
            var my = (p1.y + p2.y) / 2;

            // Ángulo del lado (en grados)
            var angle = Math.atan2(p2.y - p1.y, p2.x - p1.x) * 180 / Math.PI;

            // Asegurar que el texto sea legible (no al revés)
            if (angle > 90) angle -= 180;
            if (angle < -90) angle += 180;

            // Offset perpendicular al lado para que no se monte sobre la línea
            var perpAngle = (angle + 90) * Math.PI / 180;
            var offsetDist = 12;
            var ox = mx + Math.cos(perpAngle) * offsetDist;
            var oy = my + Math.sin(perpAngle) * offsetDist;

            // Fondo del texto (rect detrás)
            var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', ox);
            text.setAttribute('y', oy);
            text.setAttribute('fill', '#FFFFFF');
            text.setAttribute('font-size', '10');
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
            svgOverlay.appendChild(text);
        }
    }

    // ====== Dibujar texto interior del polígono ======
    function drawInteriorText(screenPoints, text) {
        // Calcular centroide de los puntos visibles
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
        svgOverlay.appendChild(textEl);
    }

    // ====== Cargar polígonos de la escena ======
    function loadPolygons(sceneId) {
        $.ajax({
            url: polygonBaseUrl + '/scene/' + sceneId + '/polygons',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log('Polígonos cargados:', data);
                savedPolygons = Array.isArray(data) ? data : [];
                renderPolygonsList();
                renderAllPolygons();
            },
            error: function(xhr, status, error) {
                console.error('Error cargando polígonos:', status, error, xhr.responseText);
                savedPolygons = [];
                renderPolygonsList();
            }
        });
    }

    // ====== Renderizar lista de polígonos guardados ======
    function renderPolygonsList() {
        var container = $('#polygons-list');
        container.empty();

        if (savedPolygons.length === 0) {
            container.html('<div class="list-group-item text-muted text-center">No hay polígonos en esta escena</div>');
            return;
        }

        savedPolygons.forEach(function(poly) {
            var item = $('<div class="list-group-item d-flex justify-content-between align-items-center"></div>');
            var pts = poly.points;
            if (typeof pts === 'string') {
                try { pts = JSON.parse(pts); } catch(e) { pts = []; }
            }
            var numPoints = Array.isArray(pts) ? pts.length : 0;
            item.append(
                '<span>' +
                '<span class="badge" style="background-color:' + poly.fill_color + ';display:inline-block;width:16px;height:16px;vertical-align:middle;border:1px solid #ccc;">&nbsp;</span> ' +
                poly.name + ' <small class="text-muted">(' + numPoints + ' pts)</small>' +
                '</span>'
            );

            var btnGroup = $('<div class="btn-group btn-group-sm"></div>');
            btnGroup.append('<button class="btn btn-outline-primary btn-edit-polygon" data-id="' + poly.id + '" title="Editar"><i class="ti-pencil"></i></button>');
            btnGroup.append('<button class="btn btn-outline-danger btn-delete-polygon" data-id="' + poly.id + '" title="Eliminar"><i class="ti-trash"></i></button>');
            item.append(btnGroup);

            container.append(item);
        });
    }

    // ====== Guardar polígono ======
    $('#btn-save-polygon').on('click', function() {
        var name = $('#polygon-name').val().trim();
        if (!name) {
            alert('Por favor ingrese un nombre para el área');
            return;
        }

        if (currentPoints.length < 2) {
            alert('Necesita al menos 2 puntos para crear una línea o polígono');
            return;
        }

        var edgeLabels = getEdgeLabelsFromForm();
        var interiorText = $('#polygon-interior-text').val().trim();

        var data = {
            scene_id: currentSceneId,
            name: name,
            fill_color: $('#polygon-fill-color').val(),
            fill_opacity: $('#polygon-fill-opacity').val(),
            stroke_color: $('#polygon-stroke-color').val(),
            stroke_width: $('#polygon-stroke-width').val(),
            points: JSON.stringify(currentPoints),
            edge_labels: edgeLabels ? JSON.stringify(edgeLabels) : '',
            interior_text: interiorText || '',
            _token: '{{ csrf_token() }}'
        };

        var url, method;

        if (editingPolygonId) {
            url = polygonBaseUrl + '/polygon/' + editingPolygonId;
            method = 'PUT';
        } else {
            url = polygonBaseUrl + '/polygon';
            method = 'POST';
        }

        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function(response) {
                alert(response.message);
                clearPoints();
                loadPolygons(currentSceneId);
            },
            error: function(xhr) {
                console.error('Error guardando:', xhr.responseText);
                alert('Error al guardar: ' + (xhr.responseJSON?.message || 'Error desconocido'));
            }
        });
    });

    // ====== Editar polígono ======
    $(document).on('click', '.btn-edit-polygon', function() {
        var id = $(this).data('id');
        var poly = savedPolygons.find(function(p) { return p.id == id; });
        if (!poly) return;

        editingPolygonId = id;
        var pts = poly.points;
        if (typeof pts === 'string') {
            try { pts = JSON.parse(pts); } catch(e) { pts = []; }
        }
        currentPoints = Array.isArray(pts) ? pts.slice() : [];
        $('#polygon-name').val(poly.name);
        $('#polygon-fill-color').val(poly.fill_color);
        $('#polygon-fill-opacity').val(poly.fill_opacity);
        $('#opacity-value').text(poly.fill_opacity);
        $('#polygon-stroke-color').val(poly.stroke_color);
        $('#polygon-stroke-width').val(poly.stroke_width);
        $('#polygon-interior-text').val(poly.interior_text || '');
        updatePointsList();

        // Cargar etiquetas de lados existentes
        var eLabels = poly.edge_labels;
        if (typeof eLabels === 'string') {
            try { eLabels = JSON.parse(eLabels); } catch(e) { eLabels = null; }
        }
        if (eLabels && Array.isArray(eLabels)) {
            $('.edge-label-input').each(function(i) {
                if (i < eLabels.length && eLabels[i]) {
                    $(this).val(eLabels[i]);
                }
            });
        }

        renderAllPolygons();
    });

    // ====== Eliminar polígono ======
    $(document).on('click', '.btn-delete-polygon', function() {
        if (!confirm('¿Está seguro de eliminar este polígono?')) return;

        var id = $(this).data('id');
        $.ajax({
            url: polygonBaseUrl + '/polygon/' + id,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                alert(response.message);
                loadPolygons(currentSceneId);
            },
            error: function(xhr) {
                console.error('Error eliminando:', xhr.responseText);
                alert('Error al eliminar');
            }
        });
    });
});
</script>
