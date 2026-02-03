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
                        <div id="points-list" class="small text-muted" style="max-height: 100px; overflow-y: auto;"></div>
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
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Visor - Haga clic para agregar puntos del polígono</h5>
            </div>
            <div class="card-body p-0">
                <div id="polygon-panorama-container" style="position: relative;">
                    <div id="polygon-panorama" style="width: 100%; height: 500px; background: #1a1a1a;">
                        <div class="d-flex align-items-center justify-content-center h-100 text-white">
                            <p>Seleccione una escena para comenzar</p>
                        </div>
                    </div>
                    <!-- SVG overlay para dibujar polígonos -->
                    <svg id="polygon-svg-overlay"
                         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
                    </svg>
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

    // Mapa de imágenes de escenas
    var sceneImageMap = {
        @foreach ($scene as $item)
            {{ $item->id }}: "{{ isset($item->image) ? route('file', $item->image) : '' }}",
        @endforeach
    };

    // Actualizar valor de opacidad
    $('#polygon-fill-opacity').on('input', function() {
        $('#opacity-value').text($(this).val());
        updatePolygonPreview();
    });

    // Actualizar preview cuando cambian los colores
    $('#polygon-fill-color, #polygon-stroke-color, #polygon-stroke-width').on('change', function() {
        updatePolygonPreview();
    });

    // Seleccionar escena
    $('#polygon-scene-select').on('change', function() {
        var sceneId = $(this).val();
        if (!sceneId) {
            $('#polygon-form-container').hide();
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

        // Limpiar puntos actuales
        clearPoints();

        // Destruir visor anterior
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

        // Evento de clic para agregar puntos
        polygonViewer.on('mousedown', function(ev) {
            // Solo agregar punto si no está arrastrando
            if (ev.button === 0) { // Clic izquierdo
                var coords = polygonViewer.mouseEventToCoords(ev);
                if (coords) {
                    addPoint(coords[1], coords[0]); // yaw, pitch
                }
            }
        });

        // Cargar polígonos existentes de esta escena
        loadPolygons(sceneId);
    });

    // Agregar punto
    function addPoint(yaw, pitch) {
        currentPoints.push({ yaw: parseFloat(yaw.toFixed(3)), pitch: parseFloat(pitch.toFixed(3)) });
        updatePointsList();
        updatePolygonPreview();
    }

    // Actualizar lista de puntos
    function updatePointsList() {
        $('#points-count').text(currentPoints.length);
        var html = '';
        currentPoints.forEach(function(p, i) {
            html += '<div>Punto ' + (i+1) + ': yaw=' + p.yaw + ', pitch=' + p.pitch + '</div>';
        });
        $('#points-list').html(html || '<em>Sin puntos</em>');
    }

    // Deshacer último punto
    $('#btn-undo-point').on('click', function() {
        if (currentPoints.length > 0) {
            currentPoints.pop();
            updatePointsList();
            updatePolygonPreview();
        }
    });

    // Limpiar todos los puntos
    $('#btn-clear-points').on('click', function() {
        clearPoints();
    });

    function clearPoints() {
        currentPoints = [];
        editingPolygonId = null;
        updatePointsList();
        updatePolygonPreview();
        $('#polygon-name').val('');
    }

    // Actualizar preview del polígono en el SVG
    function updatePolygonPreview() {
        if (!polygonViewer) return;

        var svg = $('#polygon-svg-overlay');
        svg.empty();

        // Dibujar polígonos guardados
        savedPolygons.forEach(function(poly) {
            drawPolygonOnSVG(svg, poly.points, poly.fill_color, poly.fill_opacity, poly.stroke_color, poly.stroke_width, poly.id);
        });

        // Dibujar polígono actual (en edición)
        if (currentPoints.length >= 2) {
            var fillColor = $('#polygon-fill-color').val();
            var fillOpacity = $('#polygon-fill-opacity').val();
            var strokeColor = $('#polygon-stroke-color').val();
            var strokeWidth = $('#polygon-stroke-width').val();
            drawPolygonOnSVG(svg, currentPoints, fillColor, fillOpacity, strokeColor, strokeWidth, 'current');
        }

        // Dibujar puntos actuales
        currentPoints.forEach(function(p, i) {
            var screenCoords = getScreenCoords(p.yaw, p.pitch);
            if (screenCoords) {
                var circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', screenCoords.x);
                circle.setAttribute('cy', screenCoords.y);
                circle.setAttribute('r', 6);
                circle.setAttribute('fill', '#FF0000');
                circle.setAttribute('stroke', '#FFFFFF');
                circle.setAttribute('stroke-width', 2);
                svg[0].appendChild(circle);

                // Número del punto
                var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', screenCoords.x + 10);
                text.setAttribute('y', screenCoords.y + 4);
                text.setAttribute('fill', '#FFFFFF');
                text.setAttribute('font-size', '12');
                text.setAttribute('font-weight', 'bold');
                text.textContent = (i + 1);
                svg[0].appendChild(text);
            }
        });
    }

    // Dibujar polígono en SVG
    function drawPolygonOnSVG(svg, points, fillColor, fillOpacity, strokeColor, strokeWidth, id) {
        if (points.length < 3) return;

        var pathData = '';
        var validPoints = 0;

        points.forEach(function(p, i) {
            var screenCoords = getScreenCoords(p.yaw, p.pitch);
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
            path.setAttribute('fill', fillColor);
            path.setAttribute('fill-opacity', fillOpacity);
            path.setAttribute('stroke', strokeColor);
            path.setAttribute('stroke-width', strokeWidth);
            path.setAttribute('data-polygon-id', id);
            svg[0].appendChild(path);
        }
    }

    // Obtener coordenadas de pantalla desde yaw/pitch
    function getScreenCoords(yaw, pitch) {
        if (!polygonViewer) return null;
        try {
            var coords = polygonViewer.pitchAndYawToScreen(pitch, yaw);
            if (coords && coords.x !== false && coords.y !== false) {
                return { x: coords.x, y: coords.y };
            }
        } catch(e) {}
        return null;
    }

    // Actualizar SVG cuando se mueve la vista
    if (polygonViewer) {
        setInterval(updatePolygonPreview, 100);
    }

    // Cargar polígonos de la escena
    function loadPolygons(sceneId) {
        $.ajax({
            url: '/scene/' + sceneId + '/polygons',
            method: 'GET',
            success: function(data) {
                savedPolygons = data;
                renderPolygonsList();
                updatePolygonPreview();
            },
            error: function() {
                savedPolygons = [];
                renderPolygonsList();
            }
        });
    }

    // Renderizar lista de polígonos
    function renderPolygonsList() {
        var container = $('#polygons-list');
        container.empty();

        if (savedPolygons.length === 0) {
            container.html('<div class="list-group-item text-muted text-center">No hay polígonos en esta escena</div>');
            return;
        }

        savedPolygons.forEach(function(poly) {
            var item = $('<div class="list-group-item d-flex justify-content-between align-items-center"></div>');
            item.append('<span><span class="badge" style="background-color:' + poly.fill_color + '">&nbsp;&nbsp;</span> ' + poly.name + '</span>');

            var btnGroup = $('<div class="btn-group btn-group-sm"></div>');
            btnGroup.append('<button class="btn btn-outline-primary btn-edit-polygon" data-id="' + poly.id + '"><i class="fa fa-edit"></i></button>');
            btnGroup.append('<button class="btn btn-outline-danger btn-delete-polygon" data-id="' + poly.id + '"><i class="fa fa-trash"></i></button>');
            item.append(btnGroup);

            container.append(item);
        });
    }

    // Guardar polígono
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

        var url = '/polygon';
        var method = 'POST';

        if (editingPolygonId) {
            url = '/polygon/' + editingPolygonId;
            method = 'PUT';
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
                alert('Error al guardar: ' + (xhr.responseJSON?.message || 'Error desconocido'));
            }
        });
    });

    // Editar polígono
    $(document).on('click', '.btn-edit-polygon', function() {
        var id = $(this).data('id');
        var poly = savedPolygons.find(function(p) { return p.id == id; });
        if (poly) {
            editingPolygonId = id;
            currentPoints = poly.points.slice();
            $('#polygon-name').val(poly.name);
            $('#polygon-fill-color').val(poly.fill_color);
            $('#polygon-fill-opacity').val(poly.fill_opacity);
            $('#opacity-value').text(poly.fill_opacity);
            $('#polygon-stroke-color').val(poly.stroke_color);
            $('#polygon-stroke-width').val(poly.stroke_width);
            updatePointsList();
            updatePolygonPreview();
        }
    });

    // Eliminar polígono
    $(document).on('click', '.btn-delete-polygon', function() {
        if (!confirm('¿Está seguro de eliminar este polígono?')) return;

        var id = $(this).data('id');
        $.ajax({
            url: '/polygon/' + id,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                alert(response.message);
                loadPolygons(currentSceneId);
            },
            error: function() {
                alert('Error al eliminar');
            }
        });
    });

    // Actualizar SVG periódicamente mientras se navega
    setInterval(function() {
        if (polygonViewer && (currentPoints.length > 0 || savedPolygons.length > 0)) {
            updatePolygonPreview();
        }
    }, 50);
});
</script>
