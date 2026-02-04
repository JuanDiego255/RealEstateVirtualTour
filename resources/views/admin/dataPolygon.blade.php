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
    var polygonUpdateTimer = null;

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
        if (polygonViewer) {
            try { polygonViewer.destroy(); } catch(e) {}
        }
        if (polygonUpdateTimer) {
            clearInterval(polygonUpdateTimer);
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

            // Iniciar actualización periódica de polígonos
            polygonUpdateTimer = setInterval(function() {
                if (currentPoints.length > 0 || savedPolygons.length > 0) {
                    renderAllPolygons();
                }
            }, 50);
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
        } else if (currentPoints.length < 3) {
            html += '<div class="text-warning mt-1"><small>Mínimo 3 puntos para formar un polígono</small></div>';
        } else {
            html += '<div class="text-success mt-1"><small>Polígono válido (' + currentPoints.length + ' puntos)</small></div>';
        }
        $('#points-list').html(html);
    }

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
                // Si points es string, parsear
                try { pts = typeof pts === 'string' ? JSON.parse(pts) : pts; } catch(e) { return; }
            }
            if (pts.length >= 3) {
                drawPolygonSVG(pts, poly.fill_color, poly.fill_opacity, poly.stroke_color, poly.stroke_width);
            }
        });

        // 2. Dibujar polígono actual (en edición)
        if (currentPoints.length >= 3) {
            var fillColor = $('#polygon-fill-color').val();
            var fillOpacity = parseFloat($('#polygon-fill-opacity').val());
            var strokeColor = $('#polygon-stroke-color').val();
            var strokeWidth = parseInt($('#polygon-stroke-width').val());
            drawPolygonSVG(currentPoints, fillColor, fillOpacity, strokeColor, strokeWidth);
        }

        // 3. Dibujar líneas entre puntos actuales (vista previa aunque no sean 3)
        if (currentPoints.length >= 2) {
            var lineData = '';
            var validLinePoints = 0;
            currentPoints.forEach(function(p) {
                var sc = getScreenCoords(p.yaw, p.pitch);
                if (sc) {
                    lineData += (validLinePoints === 0 ? 'M ' : 'L ') + sc.x + ' ' + sc.y + ' ';
                    validLinePoints++;
                }
            });
            if (validLinePoints >= 2) {
                var line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                line.setAttribute('d', lineData);
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
    }

    // ====== Dibujar un polígono en SVG ======
    function drawPolygonSVG(points, fillColor, fillOpacity, strokeColor, strokeWidth) {
        var pathData = '';
        var validPoints = 0;

        points.forEach(function(p) {
            var sc = getScreenCoords(p.yaw, p.pitch);
            if (sc) {
                pathData += (validPoints === 0 ? 'M ' : 'L ') + sc.x + ' ' + sc.y + ' ';
                validPoints++;
            }
        });

        if (validPoints >= 3) {
            pathData += 'Z';
            var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', pathData);
            path.setAttribute('fill', fillColor);
            path.setAttribute('fill-opacity', fillOpacity);
            path.setAttribute('stroke', strokeColor);
            path.setAttribute('stroke-width', strokeWidth);
            svgOverlay.appendChild(path);
        }
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
            btnGroup.append('<button class="btn btn-outline-primary btn-edit-polygon" data-id="' + poly.id + '" title="Editar"><i class="fa fa-edit"></i></button>');
            btnGroup.append('<button class="btn btn-outline-danger btn-delete-polygon" data-id="' + poly.id + '" title="Eliminar"><i class="fa fa-trash"></i></button>');
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

        if (currentPoints.length < 3) {
            alert('Necesita al menos 3 puntos para crear un polígono');
            return;
        }

        var data = {
            scene_id: currentSceneId,
            name: name,
            fill_color: $('#polygon-fill-color').val(),
            fill_opacity: $('#polygon-fill-opacity').val(),
            stroke_color: $('#polygon-stroke-color').val(),
            stroke_width: $('#polygon-stroke-width').val(),
            points: JSON.stringify(currentPoints),
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
        updatePointsList();
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
