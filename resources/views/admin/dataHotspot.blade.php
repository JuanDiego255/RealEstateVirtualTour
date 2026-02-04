{{-- Vista Hotspots (Blade) --}}
<div class="d-flex justify-content-end">
    <!-- Add Hotspot -->
    <button type="button" class="btn btn-rounded btn-outline-info mb-3" data-toggle="modal" data-target="#addHotspot">
        Nuevo HotSpot
    </button>

    <div class="modal fade" id="addHotspot">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar punto de acceso</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <form action="{{ route('addHotspot') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @if ($errors->any())
                            @foreach ($errors->all() as $error)
                                <div class="alert-dismiss">
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <strong>{{ $error }}</strong>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span class="fa fa-times"></span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <input type="hidden" name="property_id" value="{{ $id }}">

                        {{-- Visor panorama (escenas 360) --}}
                        <div id="panorama-hotspot-add" style="width: 100%; height: 500px;"></div>

                        {{-- Visor video (escenas de video dron) --}}
                        <div id="video-hotspot-add" style="width: 100%; height: 500px; display: none; position: relative; background: #000; cursor: crosshair; overflow: hidden;">
                            <video id="video-hotspot-add-player" muted playsinline preload="auto" style="width:100%; height:100%; object-fit:cover; pointer-events:none;"></video>
                            <div id="video-hotspot-add-marker" style="display:none; position:absolute; width:20px; height:20px; border:3px solid #e74c3c; border-radius:50%; background:rgba(231,76,60,0.3); transform:translate(-50%,-50%); pointer-events:none; z-index:5;"></div>
                            <div style="position:absolute; bottom:0; left:0; width:100%; height:4px; background:rgba(255,255,255,0.2);">
                                <div id="video-hotspot-add-progress" style="height:100%; background:#007bff; width:0%;"></div>
                            </div>
                            <div style="position:absolute; bottom:10px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.6); color:#fff; padding:4px 12px; border-radius:10px; font-size:11px;">
                                <i class="fa fa-arrows-h"></i> Arrastra para navegar, haz clic para posicionar el hotspot
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="sourceSceneAdd">Origen de la escena</label>
                                <select class="form-control form-control-lg input-rounded mb-4" id="sourceSceneAdd"
                                    name="sourceScene" required>
                                    <option value="" disabled selected>Seleccione uno</option>
                                    @foreach ($scene as $item)
                                        <option value="{{ $item->id }}" data-type="{{ $item->type }}">{{ $item->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="typeAdd">Tipo</label>
                                <select class="form-control form-control-lg input-rounded mb-4" id="typeAdd"
                                    name="type" required>
                                    <option value="" disabled selected>Seleccione uno</option>
                                    <option value="info">Información</option>
                                    <option value="scene">Enlace</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6" id="targetSceneAddContainer" style="display: none;">
                                <label for="targetSceneAdd">Objetivo de la escena</label>
                                <select class="form-control form-control-lg input-rounded mb-4" id="targetSceneAdd"
                                    name="targetScene">
                                    <option value="" disabled selected>Seleccione uno</option>
                                    @foreach ($scene as $item)
                                        <option value="{{ $item->id }}">{{ $item->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Campos de posición para panorama 360 --}}
                            <div class="form-group col-md-6 panorama-pos-add">
                                <label for="yawAdd">Yaw</label>
                                <input id="yawAdd" name="yaw"
                                    class="form-control form-control-lg input-rounded mb-4" required type="text"
                                    step="0.1" value="0">
                            </div>

                            <div class="form-group col-md-6 panorama-pos-add">
                                <label for="pitchAdd">Pitch</label>
                                <input id="pitchAdd" name="pitch"
                                    class="form-control form-control-lg input-rounded mb-4" required type="text"
                                    step="0.1" value="0">
                            </div>

                            {{-- Campos de posición para video --}}
                            <div class="form-group col-md-4 video-pos-add" style="display:none;">
                                <label for="videoTimeAdd"><i class="fa fa-clock-o"></i> Tiempo en video (seg)</label>
                                <input id="videoTimeAdd" name="video_time"
                                    class="form-control form-control-lg input-rounded mb-4" type="number"
                                    step="0.1" value="">
                            </div>

                            <div class="form-group col-md-4 video-pos-add" style="display:none;">
                                <label for="posXAdd">Posición X (%)</label>
                                <input id="posXAdd" name="pos_x"
                                    class="form-control form-control-lg input-rounded mb-4" type="text"
                                    step="0.1" min="0" max="100" value="">
                            </div>

                            <div class="form-group col-md-4 video-pos-add" style="display:none;">
                                <label for="posYAdd">Posición Y (%)</label>
                                <input id="posYAdd" name="pos_y"
                                    class="form-control form-control-lg input-rounded mb-4" type="text"
                                    step="0.1" min="0" max="100" value="">
                            </div>

                            <div class="form-group col-md-12">
                                <label for="textAdd">Información</label>
                                <textarea id="textAdd" class="form-control form-control-lg input-rounded mb-4" required name="text"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="image">Imagen Referencia</label>
                            <img class="card-img-top img-fluid" id="image-preview" alt="Image Preview" />
                            <div class="custom-file">
                                <input type="file" class="form-control-file" id="image-upload" name="image"
                                    onchange="previewImage()" accept="image/*">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive" style="width:100%;">
    <table class="table table-hover progress-table text-center hotspotTable" style="width:100%">
        <thead class="text-uppercase">
            <tr>
                <th scope="col">Imagen</th>
                <th scope="col">Orígen de la escena</th>
                <th scope="col">Objetivo</th>
                <th scope="col">Tipo</th>
                <th scope="col">Info</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        {{-- ... tu tbody vía DataTables/Ajax ... --}}
    </table>
</div>

@foreach ($hotspots as $hotspot)
    {{-- Detail Modal --}}
    <div class="modal fade" id="detailHotspot{{ $hotspot->id }}">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title justify-content-">Información punto de acceso</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <input type="hidden" name="property_id" value="{{ $id }}">
                <div class="modal-body">
                    <p class="d-flex justify-content-left"><b>Tipo: </b>&nbsp;{{ $hotspot->type }}</p><br>
                    <p class="d-flex justify-content-left"><b>Movimiento de rotación horizontal:
                        </b>&nbsp;{{ $hotspot->yaw }}</p><br>
                    <p class="d-flex justify-content-left"><b>Movimiento de rotación vertical:
                        </b>&nbsp;{{ $hotspot->pitch }}</p><br>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <center>
        <div class="modal modal-xl fade text-center" id="editHotspot{{ $hotspot->id }}">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content modal-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">Cambiar punto de acceso</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('editHotspot') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert-dismiss">
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <strong>{{ $error }}</strong>
                                            <button type="button" class="close" data-dismiss="alert"
                                                aria-label="Close">
                                                <span class="fa fa-times"></span>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @php
                                $sourceScene = $scene->firstWhere('id', $hotspot->sourceScene);
                                $isVideoHotspot = $sourceScene && $sourceScene->type === 'video';
                            @endphp

                            {{-- Visor panorama (escenas 360) --}}
                            <div id="panorama-hotspot{{ $hotspot->id }}" style="width: 100%; height: 500px; {{ $isVideoHotspot ? 'display:none;' : '' }}"></div>

                            {{-- Visor video (escenas de video dron) --}}
                            <div id="video-hotspot-edit-{{ $hotspot->id }}" class="video-hotspot-edit-viewer" style="width: 100%; height: 500px; {{ !$isVideoHotspot ? 'display:none;' : '' }} position: relative; background: #000; cursor: crosshair; overflow: hidden;" data-hotspot-id="{{ $hotspot->id }}">
                                <video class="video-hotspot-edit-player" muted playsinline preload="auto" style="width:100%; height:100%; object-fit:cover; pointer-events:none;"></video>
                                <div class="video-hotspot-edit-marker" style="{{ $isVideoHotspot && $hotspot->pos_x !== null ? 'display:block;' : 'display:none;' }} position:absolute; width:20px; height:20px; border:3px solid #e74c3c; border-radius:50%; background:rgba(231,76,60,0.3); transform:translate(-50%,-50%); pointer-events:none; z-index:5; left:{{ $hotspot->pos_x ?? 50 }}%; top:{{ $hotspot->pos_y ?? 50 }}%;"></div>
                                <div style="position:absolute; bottom:0; left:0; width:100%; height:4px; background:rgba(255,255,255,0.2);">
                                    <div class="video-hotspot-edit-progress" style="height:100%; background:#007bff; width:0%;"></div>
                                </div>
                                <div style="position:absolute; bottom:10px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.6); color:#fff; padding:4px 12px; border-radius:10px; font-size:11px;">
                                    <i class="fa fa-arrows-h"></i> Arrastra para navegar, haz clic para posicionar
                                </div>
                            </div>

                            <input type="hidden" value="{{ $hotspot->id }}" name="id"
                                id="id-{{ $hotspot->id }}">
                            <input type="hidden" name="property_id" value="{{ $id }}">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="sourceScene-{{ $hotspot->id }}"
                                        class="d-flex justify-content-left">Escena Principal</label>
                                    <select class="form-control form-control-lg input-rounded mb-4 source-scene-edit" name="sourceScene"
                                        id="sourceScene-{{ $hotspot->id }}" data-hotspot-id="{{ $hotspot->id }}" required>
                                        <option value="" disabled>Seleccione uno</option>
                                        @foreach ($scene as $scenes)
                                            <option value="{{ $scenes->id }}" data-type="{{ $scenes->type }}"
                                                {{ $hotspot->sourceScene == $scenes->id ? 'selected' : '' }}>
                                                {{ $scenes->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="type-{{ $hotspot->id }}"
                                        class="d-flex justify-content-left">Tipo</label>
                                    <select class="form-control form-control-lg input-rounded mb-4 hotspot-type-select" name="type"
                                        id="type-{{ $hotspot->id }}" data-hotspot-id="{{ $hotspot->id }}" required>
                                        <option value="" disabled>Seleccione uno</option>
                                        <option value="info" {{ $hotspot->type == 'info' ? 'selected' : '' }}>
                                            Información</option>
                                        <option value="scene" {{ $hotspot->type == 'scene' ? 'selected' : '' }}>
                                            Enlace</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6 target-scene-container" id="targetSceneContainer-{{ $hotspot->id }}" style="{{ $hotspot->type == 'info' ? 'display: none;' : '' }}">
                                    <label for="targetScene-{{ $hotspot->id }}"
                                        class="d-flex justify-content-left">Objetivo de la escena</label>
                                    <select class="form-control form-control-lg input-rounded mb-4" name="targetScene"
                                        id="targetScene-{{ $hotspot->id }}">
                                        <option value="" disabled {{ !$hotspot->targetScene ? 'selected' : '' }}>Seleccione uno</option>
                                        @foreach ($scene as $scenes)
                                            <option value="{{ $scenes->id }}"
                                                {{ $hotspot->targetScene == $scenes->id ? 'selected' : '' }}>
                                                {{ $scenes->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Campos panorama 360 --}}
                                <div class="form-group col-md-6 panorama-pos-edit-{{ $hotspot->id }}" style="{{ $isVideoHotspot ? 'display:none;' : '' }}">
                                    <label for="yaw-{{ $hotspot->id }}"
                                        class="d-flex justify-content-left">Movimiento de rotación horizontal</label>
                                    <input id="yaw-{{ $hotspot->id }}" name="yaw"
                                        class="form-control form-control-lg input-rounded mb-4" required
                                        type="text" step="0.2" value="{{ $hotspot->yaw }}">
                                </div>

                                <div class="form-group col-md-6 panorama-pos-edit-{{ $hotspot->id }}" style="{{ $isVideoHotspot ? 'display:none;' : '' }}">
                                    <label for="pitch-{{ $hotspot->id }}"
                                        class="d-flex justify-content-left">Movimiento de rotación vertical</label>
                                    <input id="pitch-{{ $hotspot->id }}" name="pitch"
                                        class="form-control form-control-lg input-rounded mb-4" required
                                        type="text" step="0.1" value="{{ $hotspot->pitch }}">
                                </div>

                                {{-- Campos video --}}
                                <div class="form-group col-md-4 video-pos-edit-{{ $hotspot->id }}" style="{{ !$isVideoHotspot ? 'display:none;' : '' }}">
                                    <label for="videoTime-{{ $hotspot->id }}"><i class="fa fa-clock-o"></i> Tiempo en video (seg)</label>
                                    <input id="videoTime-{{ $hotspot->id }}" name="video_time"
                                        class="form-control form-control-lg input-rounded mb-4" type="number"
                                        step="0.1" value="{{ $hotspot->video_time }}">
                                </div>

                                <div class="form-group col-md-4 video-pos-edit-{{ $hotspot->id }}" style="{{ !$isVideoHotspot ? 'display:none;' : '' }}">
                                    <label for="posX-{{ $hotspot->id }}">Posición X (%)</label>
                                    <input id="posX-{{ $hotspot->id }}" name="pos_x"
                                        class="form-control form-control-lg input-rounded mb-4" type="text"
                                        step="0.1" min="0" max="100" value="{{ $hotspot->pos_x }}">
                                </div>

                                <div class="form-group col-md-4 video-pos-edit-{{ $hotspot->id }}" style="{{ !$isVideoHotspot ? 'display:none;' : '' }}">
                                    <label for="posY-{{ $hotspot->id }}">Posición Y (%)</label>
                                    <input id="posY-{{ $hotspot->id }}" name="pos_y"
                                        class="form-control form-control-lg input-rounded mb-4" type="text"
                                        step="0.1" min="0" max="100" value="{{ $hotspot->pos_y }}">
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="text-{{ $hotspot->id }}"
                                        class="d-flex justify-content-left">Texto</label>
                                    <textarea id="text-{{ $hotspot->id }}" class="form-control form-control-lg input-rounded mb-4" name="text"
                                        required>{{ $hotspot->info }}</textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="d-flex justify-content-left">Imagen Referencia</label>
                                @if ($hotspot->image)
                                    <img class="card-img-top img-fluid w-50"
                                        src="{{ isset($hotspot->image) ? route('file', $hotspot->image) : url('images/producto-sin-imagen.PNG') }}">
                                @endif
                                <div class="custom-file">
                                    <input class="form-control" type="file" name="image">
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Editar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </center>

    {{-- Delete Modal --}}
    <div id="deleteHotspot{{ $hotspot->id }}" class="modal fade">
        <div class="modal-dialog modal-dialog-centered modal-confirm">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <div class="icon-box">
                        <i class="fa fa-times-circle"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <p class="text-center">¿Está seguro de que desea eliminar este punto de acceso?</p>
                    <form method="POST" action="{{ route('delHotspot', ['id' => $hotspot->id]) }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="property_id" value="{{ $id }}">
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- JS --}}
<script>
    $(document).ready(function() {
        // ---------- Utils ----------
        function round3(n) {
            return Number.parseFloat(n).toFixed(3);
        }

        function destroyViewer(v) {
            try {
                v && v.destroy && v.destroy();
            } catch (e) {}
        }

        // ---------- Map escena -> imagen / video / tipo ----------
        var sceneImageMap = {
            @foreach ($scene as $scenes)
                {{ $scenes->id }}: "{{ isset($scenes->image) ? route('file', $scenes->image) : url('images/producto-sin-imagen.PNG') }}",
            @endforeach
        };
        var sceneTypeMap = {
            @foreach ($scene as $scenes)
                {{ $scenes->id }}: "{{ $scenes->type }}",
            @endforeach
        };
        var sceneVideoMap = {
            @foreach ($scene as $scenes)
                @if($scenes->video)
                {{ $scenes->id }}: "{{ route('file', $scenes->video) }}",
                @endif
            @endforeach
        };

        // ====== Utilidad: drag-to-scrub en un contenedor de video ======
        function setupVideoScrub(containerEl, videoEl, progressEl, onClickCallback) {
            var dragState = { isDragging: false, startX: 0, startTime: 0, hasMoved: false };
            var sensitivity = 0.04;

            containerEl.addEventListener('mousedown', function(e) {
                dragState.isDragging = true;
                dragState.startX = e.clientX;
                dragState.startTime = videoEl.currentTime || 0;
                dragState.hasMoved = false;
                e.preventDefault();
            });

            document.addEventListener('mousemove', function(e) {
                if (!dragState.isDragging) return;
                var deltaX = e.clientX - dragState.startX;
                if (Math.abs(deltaX) > 3) dragState.hasMoved = true;
                var newTime = dragState.startTime + deltaX * sensitivity;
                if (videoEl.duration && videoEl.duration > 0) {
                    while (newTime < 0) newTime += videoEl.duration;
                    while (newTime >= videoEl.duration) newTime -= videoEl.duration;
                }
                if (typeof videoEl.fastSeek === 'function') {
                    videoEl.fastSeek(newTime);
                } else {
                    videoEl.currentTime = newTime;
                }
                if (progressEl && videoEl.duration) {
                    progressEl.style.width = ((videoEl.currentTime / videoEl.duration) * 100) + '%';
                }
            });

            document.addEventListener('mouseup', function(e) {
                if (!dragState.isDragging) return;
                var wasDragging = dragState.hasMoved;
                dragState.isDragging = false;

                // Si no arrastró, es un clic → posicionar hotspot
                if (!wasDragging && onClickCallback) {
                    var rect = containerEl.getBoundingClientRect();
                    var x = ((e.clientX - rect.left) / rect.width) * 100;
                    var y = ((e.clientY - rect.top) / rect.height) * 100;
                    x = Math.max(0, Math.min(100, x));
                    y = Math.max(0, Math.min(100, y));
                    onClickCallback(x, y, videoEl.currentTime);
                }
            });

            // Touch events
            containerEl.addEventListener('touchstart', function(e) {
                var touch = e.touches[0];
                dragState.isDragging = true;
                dragState.startX = touch.clientX;
                dragState.startTime = videoEl.currentTime || 0;
                dragState.hasMoved = false;
            }, { passive: true });

            document.addEventListener('touchmove', function(e) {
                if (!dragState.isDragging) return;
                var touch = e.touches[0];
                var deltaX = touch.clientX - dragState.startX;
                if (Math.abs(deltaX) > 3) dragState.hasMoved = true;
                var newTime = dragState.startTime + deltaX * sensitivity;
                if (videoEl.duration && videoEl.duration > 0) {
                    while (newTime < 0) newTime += videoEl.duration;
                    while (newTime >= videoEl.duration) newTime -= videoEl.duration;
                }
                if (typeof videoEl.fastSeek === 'function') {
                    videoEl.fastSeek(newTime);
                } else {
                    videoEl.currentTime = newTime;
                }
                if (progressEl && videoEl.duration) {
                    progressEl.style.width = ((videoEl.currentTime / videoEl.duration) * 100) + '%';
                }
            }, { passive: true });

            document.addEventListener('touchend', function(e) {
                if (!dragState.isDragging) return;
                dragState.isDragging = false;
            });
        }

        // ====== Función para alternar visor panorama/video según tipo de escena ======
        function toggleViewerType(sceneId, panoramaContainerId, videoContainerId, posFieldsClass, isAdd) {
            var sceneType = sceneTypeMap[sceneId] || 'equirectangular';
            var isVideo = (sceneType === 'video');

            if (isAdd) {
                // ADD: mostrar/ocultar contenedores y campos
                var panoramaEl = document.getElementById(panoramaContainerId);
                var videoEl = document.getElementById(videoContainerId);

                if (isVideo) {
                    if (panoramaEl) panoramaEl.style.display = 'none';
                    if (videoEl) videoEl.style.display = 'block';
                    $('.panorama-pos-add').hide();
                    $('.video-pos-add').show();
                    // Cargar video
                    var videoUrl = sceneVideoMap[sceneId];
                    if (videoUrl) {
                        var player = document.getElementById('video-hotspot-add-player');
                        player.src = videoUrl;
                        player.load();
                    }
                } else {
                    if (panoramaEl) panoramaEl.style.display = 'block';
                    if (videoEl) videoEl.style.display = 'none';
                    $('.panorama-pos-add').show();
                    $('.video-pos-add').hide();
                }
            }
        }

        // ====== ADD ======
        var viewerAdd = null;
        var panoramaAddEl = document.getElementById("panorama-hotspot-add");
        var videoAddEl = document.getElementById("video-hotspot-add");
        if (panoramaAddEl) panoramaAddEl.style.display = "none";

        // Setup video scrub para ADD
        var videoAddPlayer = document.getElementById('video-hotspot-add-player');
        var videoAddProgress = document.getElementById('video-hotspot-add-progress');
        var videoAddMarker = document.getElementById('video-hotspot-add-marker');

        setupVideoScrub(videoAddEl, videoAddPlayer, videoAddProgress, function(x, y, time) {
            // Clic en video → posicionar hotspot
            $('#videoTimeAdd').val(round3(time));
            $('#posXAdd').val(round3(x));
            $('#posYAdd').val(round3(y));
            // Mostrar marcador
            videoAddMarker.style.display = 'block';
            videoAddMarker.style.left = x + '%';
            videoAddMarker.style.top = y + '%';
        });

        $("#addHotspot").on('shown.bs.modal', function() {
            if (panoramaAddEl) panoramaAddEl.style.display = "none";
            if (videoAddEl) videoAddEl.style.display = "none";
            destroyViewer(viewerAdd);
            $("#yawAdd").val('0');
            $("#pitchAdd").val('0');
            $("#videoTimeAdd").val('');
            $("#posXAdd").val('');
            $("#posYAdd").val('');
            videoAddMarker.style.display = 'none';
            $("#sourceSceneAdd").val('');
            $('.panorama-pos-add').show();
            $('.video-pos-add').hide();
        });

        $("#sourceSceneAdd").on("change", function() {
            var selectedSceneId = $(this).val();
            var sceneType = sceneTypeMap[selectedSceneId] || 'equirectangular';

            if (sceneType === 'video') {
                // Escena de video
                if (panoramaAddEl) panoramaAddEl.style.display = 'none';
                destroyViewer(viewerAdd);
                viewerAdd = null;

                videoAddEl.style.display = 'block';
                $('.panorama-pos-add').hide();
                $('.video-pos-add').show();

                var videoUrl = sceneVideoMap[selectedSceneId];
                if (videoUrl) {
                    videoAddPlayer.src = videoUrl;
                    videoAddPlayer.load();
                }
                videoAddMarker.style.display = 'none';
                $('#videoTimeAdd').val('');
                $('#posXAdd').val('');
                $('#posYAdd').val('');
            } else {
                // Escena panorama 360
                videoAddEl.style.display = 'none';
                videoAddPlayer.pause();
                videoAddPlayer.removeAttribute('src');
                $('.panorama-pos-add').show();
                $('.video-pos-add').hide();

                var imageUrl = sceneImageMap[selectedSceneId];
                if (!imageUrl) return;

                panoramaAddEl.style.display = "block";
                destroyViewer(viewerAdd);

                viewerAdd = pannellum.viewer('panorama-hotspot-add', {
                    type: "equirectangular",
                    panorama: imageUrl,
                    autoLoad: true,
                    showControls: true
                });

                viewerAdd.on('mousedown', function(ev) {
                    var coords = viewerAdd.mouseEventToCoords(ev);
                    if (!coords) return;
                    var pitch = coords[0];
                    var yaw = coords[1];
                    $("#yawAdd").val(round3(yaw));
                    $("#pitchAdd").val(round3(pitch));
                });
            }
        });

        // ====== EDIT ======
        $('[id^="editHotspot"]').on('shown.bs.modal', function() {
            var $modal = $(this);
            var idNum = $modal.attr('id').match(/\d+/)[0];

            var panoramaContainerId = 'panorama-hotspot' + idNum;
            var videoContainerId = 'video-hotspot-edit-' + idNum;
            var $sourceSelect = $modal.find('#sourceScene-' + idNum);
            var sceneId = $sourceSelect.val();
            var sceneType = sceneTypeMap[sceneId] || 'equirectangular';

            if (sceneType === 'video') {
                // Escena de video - cargar video en el visor
                var $videoViewer = $modal.find('.video-hotspot-edit-viewer');
                var videoPlayer = $videoViewer.find('.video-hotspot-edit-player')[0];
                var videoProgress = $videoViewer.find('.video-hotspot-edit-progress')[0];
                var videoMarker = $videoViewer.find('.video-hotspot-edit-marker')[0];

                var videoUrl = sceneVideoMap[sceneId];
                if (videoUrl && videoPlayer) {
                    videoPlayer.src = videoUrl;
                    videoPlayer.load();

                    // Si tiene video_time, navegar a ese punto
                    var vt = $('#videoTime-' + idNum).val();
                    if (vt) {
                        videoPlayer.oncanplay = function() {
                            videoPlayer.currentTime = parseFloat(vt) || 0;
                            videoPlayer.oncanplay = null;
                        };
                    }
                }

                // Setup drag-to-scrub para este edit viewer
                if (!$videoViewer.data('scrubSetup')) {
                    setupVideoScrub($videoViewer[0], videoPlayer, videoProgress, function(x, y, time) {
                        $('#videoTime-' + idNum).val(round3(time));
                        $('#posX-' + idNum).val(round3(x));
                        $('#posY-' + idNum).val(round3(y));
                        videoMarker.style.display = 'block';
                        videoMarker.style.left = x + '%';
                        videoMarker.style.top = y + '%';
                    });
                    $videoViewer.data('scrubSetup', true);
                }
            } else {
                // Escena panorama 360
                var imageUrl = sceneImageMap[sceneId];
                destroyViewer($modal.data('viewerEdit'));

                if (imageUrl) {
                    var viewerEdit = pannellum.viewer(panoramaContainerId, {
                        type: "equirectangular",
                        panorama: imageUrl,
                        autoLoad: true
                    });
                    $modal.data('viewerEdit', viewerEdit);

                    viewerEdit.on('mousedown', function(ev) {
                        var coords = viewerEdit.mouseEventToCoords(ev);
                        if (!coords) return;
                        $('#yaw-' + idNum).val(round3(coords[1]));
                        $('#pitch-' + idNum).val(round3(coords[0]));
                    });
                }
            }

            // Cambio de escena dentro del modal de edición
            $sourceSelect.off('change._edit').on('change._edit', function() {
                var newSceneId = $(this).val();
                var newType = sceneTypeMap[newSceneId] || 'equirectangular';

                if (newType === 'video') {
                    // Cambiar a video
                    $('#' + panoramaContainerId).hide();
                    destroyViewer($modal.data('viewerEdit'));
                    $('#' + videoContainerId).show();
                    $('.panorama-pos-edit-' + idNum).hide();
                    $('.video-pos-edit-' + idNum).show();

                    var videoUrl = sceneVideoMap[newSceneId];
                    var $videoViewer = $('#' + videoContainerId);
                    var videoPlayer = $videoViewer.find('.video-hotspot-edit-player')[0];
                    if (videoUrl && videoPlayer) {
                        videoPlayer.src = videoUrl;
                        videoPlayer.load();
                    }
                } else {
                    // Cambiar a panorama
                    $('#' + videoContainerId).hide();
                    var videoPlayer2 = $('#' + videoContainerId).find('.video-hotspot-edit-player')[0];
                    if (videoPlayer2) { videoPlayer2.pause(); videoPlayer2.removeAttribute('src'); }
                    $('#' + panoramaContainerId).show();
                    $('.panorama-pos-edit-' + idNum).show();
                    $('.video-pos-edit-' + idNum).hide();

                    var newUrl = sceneImageMap[newSceneId];
                    if (newUrl) {
                        destroyViewer($modal.data('viewerEdit'));
                        var viewerEdit2 = pannellum.viewer(panoramaContainerId, {
                            type: "equirectangular",
                            panorama: newUrl,
                            autoLoad: true
                        });
                        $modal.data('viewerEdit', viewerEdit2);
                        viewerEdit2.on('mousedown', function(ev) {
                            var coords = viewerEdit2.mouseEventToCoords(ev);
                            if (!coords) return;
                            $('#yaw-' + idNum).val(round3(coords[1]));
                            $('#pitch-' + idNum).val(round3(coords[0]));
                        });
                    }
                }
            });
        });

        // Limpia viewer al cerrar
        $('[id^="editHotspot"]').on('hidden.bs.modal', function() {
            var $modal = $(this);
            destroyViewer($modal.data('viewerEdit'));
            $modal.removeData('viewerEdit');
            // Pausar video si existe
            $modal.find('.video-hotspot-edit-player').each(function() {
                this.pause();
            });
        });

        // ====== Control de visibilidad de targetScene según tipo ======
        $('#typeAdd').on('change', function() {
            var selectedType = $(this).val();
            if (selectedType === 'scene') {
                $('#targetSceneAddContainer').show();
                $('#targetSceneAdd').prop('required', true);
            } else {
                $('#targetSceneAddContainer').hide();
                $('#targetSceneAdd').prop('required', false).val('');
            }
        });

        $(document).on('change', '.hotspot-type-select', function() {
            var selectedType = $(this).val();
            var hotspotId = $(this).data('hotspot-id');
            var $container = $('#targetSceneContainer-' + hotspotId);
            var $select = $('#targetScene-' + hotspotId);

            if (selectedType === 'scene') {
                $container.show();
                $select.prop('required', true);
            } else {
                $container.hide();
                $select.prop('required', false).val('');
            }
        });
    });
</script>
