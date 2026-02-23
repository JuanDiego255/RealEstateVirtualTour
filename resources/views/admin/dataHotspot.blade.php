{{-- Vista Hotspots (Blade) --}}
<div class="d-flex justify-content-end">
    <!-- Add Hotspot -->
    <button type="button" class="btn btn-rounded btn-outline-info mb-3" data-toggle="modal" data-target="#addHotspotMulti">
        <i class="fa fa-plus-circle mr-1"></i> Nuevo HotSpot
    </button>

    {{-- Modal para creación múltiple de hotspots --}}
    <div class="modal fade" id="addHotspotMulti">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-map-marker mr-2"></i>Agregar Puntos de Acceso</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>{{ $error }}</strong>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span class="fa fa-times"></span>
                                </button>
                            </div>
                        @endforeach
                    @endif

                    <input type="hidden" id="propertyIdBatch" value="{{ $id }}">

                    {{-- Paso 1: Seleccionar escena origen --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="sourceSceneMulti" class="font-weight-bold">
                                <i class="fa fa-image mr-1"></i> Escena Origen
                            </label>
                            <select class="form-control form-control-lg" id="sourceSceneMulti" required>
                                <option value="" disabled selected>Seleccione una escena</option>
                                @foreach ($scene as $item)
                                    <option value="{{ $item->id }}" data-type="{{ $item->type }}">{{ $item->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="alert alert-info mb-0 w-100" style="font-size: 13px;">
                                <i class="fa fa-info-circle mr-1"></i>
                                Haz clic en la imagen para posicionar hotspots. Puedes agregar varios antes de guardar.
                            </div>
                        </div>
                    </div>

                    {{-- Contenedor del visor con card flotante --}}
                    <div id="viewerWrapper" style="position: relative; display: none;">
                        {{-- Visor panorama (escenas 360) --}}
                        <div id="panorama-multi" style="width: 100%; height: 450px;"></div>

                        {{-- Visor video (escenas de video dron) --}}
                        <div id="video-multi" style="width: 100%; height: 450px; display: none; position: relative; background: #000; cursor: crosshair; overflow: hidden;">
                            <video id="video-multi-player" muted playsinline preload="auto" style="width:100%; height:100%; object-fit:cover; pointer-events:none;"></video>
                            <div id="video-multi-marker" style="display:none; position:absolute; width:20px; height:20px; border:3px solid #e74c3c; border-radius:50%; background:rgba(231,76,60,0.3); transform:translate(-50%,-50%); pointer-events:none; z-index:5;"></div>
                            <div style="position:absolute; bottom:0; left:0; width:100%; height:4px; background:rgba(255,255,255,0.2);">
                                <div id="video-multi-progress" style="height:100%; background:#007bff; width:0%;"></div>
                            </div>
                            <div style="position:absolute; bottom:10px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.6); color:#fff; padding:4px 12px; border-radius:10px; font-size:11px;">
                                <i class="fa fa-arrows-h"></i> Arrastra para navegar, haz clic para posicionar
                            </div>
                        </div>

                        {{-- Card flotante para configurar hotspot --}}
                        <div id="hotspotCard" class="card shadow" style="display: none; position: absolute; width: 320px; z-index: 1000; border: 2px solid #007bff;">
                            <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-map-pin mr-1"></i> Configurar Hotspot</span>
                                <button type="button" class="close text-white" id="closeHotspotCard" style="font-size: 18px;">&times;</button>
                            </div>
                            <div class="card-body py-2">
                                <input type="hidden" id="cardYaw">
                                <input type="hidden" id="cardPitch">
                                <input type="hidden" id="cardVideoTime">
                                <input type="hidden" id="cardPosX">
                                <input type="hidden" id="cardPosY">
                                <input type="hidden" id="cardEditIndex" value="-1">

                                <div class="form-group mb-2">
                                    <label class="small mb-1"><i class="fa fa-tag mr-1"></i> Tipo</label>
                                    <select class="form-control form-control-sm" id="cardType">
                                        <option value="info">Información</option>
                                        <option value="scene">Enlace a escena</option>
                                    </select>
                                </div>

                                <div class="form-group mb-2" id="cardTargetContainer" style="display: none;">
                                    <label class="small mb-1"><i class="fa fa-link mr-1"></i> Escena Destino</label>
                                    <select class="form-control form-control-sm" id="cardTargetScene">
                                        <option value="">Seleccione</option>
                                        @foreach ($scene as $item)
                                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-2">
                                    <label class="small mb-1"><i class="fa fa-comment mr-1"></i> Información</label>
                                    <textarea class="form-control form-control-sm" id="cardInfo" rows="2" placeholder="Descripción del hotspot..."></textarea>
                                </div>

                                <div class="form-group mb-2">
                                    <label class="small mb-1"><i class="fa fa-image mr-1"></i> Imagen</label>
                                    <div class="d-flex align-items-center">
                                        <img id="cardImagePreview" src="{{ url('virtualtour/images/hotspot.png') }}" alt="Preview" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid #ddd; margin-right: 8px;">
                                        <div style="flex: 1;">
                                            <input type="file" class="form-control-file" id="cardImageFile" accept="image/*" style="font-size: 11px;">
                                            <small class="text-muted">Opcional. Si no se selecciona se usa imagen por defecto.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="cancelHotspotCard">
                                        <i class="fa fa-times"></i> Cancelar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" id="addToListBtn">
                                        <i class="fa fa-plus"></i> Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Lista de hotspots pendientes --}}
                    <div id="pendingHotspotsSection" style="display: none;" class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><i class="fa fa-list mr-1"></i> Hotspots Pendientes</h6>
                            <span class="badge badge-primary" id="pendingCount">0</span>
                        </div>
                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0" id="pendingTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 30px;">#</th>
                                        <th style="width: 50px;">Img</th>
                                        <th>Tipo</th>
                                        <th>Destino</th>
                                        <th>Info</th>
                                        <th style="width: 80px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer px-0 pb-0 mt-3">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times mr-1"></i> Cerrar
                        </button>
                        <button type="button" class="btn btn-primary" id="saveAllHotspots" disabled>
                            <i class="fa fa-save mr-1"></i> Guardar Todos (<span id="saveCount">0</span>)
                        </button>
                    </div>
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
                                        class="form-control form-control-lg input-rounded mb-4" type="text"
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
                                <img class="card-img-top img-fluid w-50 mb-2"
                                    src="{{ !empty($hotspot->image) ? route('file', $hotspot->image) : url('virtualtour/images/hotspot.png') }}">
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

        // ---------- Map escena -> imagen / video / tipo / título ----------
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
        var sceneTitleMap = {
            @foreach ($scene as $scenes)
                {{ $scenes->id }}: "{{ addslashes($scenes->title) }}",
            @endforeach
        };

        // ====== Variables globales para creación múltiple ======
        var viewerMulti = null;
        var pendingHotspots = []; // Cada item: { ...datos, imageFile: File|null, imagePreviewUrl: string|null }
        var currentSceneId = null;
        var isVideoScene = false;
        var defaultHotspotImage = '{{ url("virtualtour/images/hotspot.png") }}';

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
                    onClickCallback(x, y, videoEl.currentTime, e.clientX, e.clientY);
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

        // ====== Mostrar card flotante ======
        function showHotspotCard(x, y, yaw, pitch, videoTime, posX, posY) {
            var $card = $('#hotspotCard');
            var $wrapper = $('#viewerWrapper');
            var wrapperOffset = $wrapper.offset();
            var wrapperWidth = $wrapper.width();
            var wrapperHeight = $wrapper.height();

            // Posición relativa al wrapper
            var cardLeft = x - wrapperOffset.left;
            var cardTop = y - wrapperOffset.top;

            // Ajustar si el card se sale del contenedor
            var cardWidth = 320;
            var cardHeight = 280;
            if (cardLeft + cardWidth > wrapperWidth) {
                cardLeft = wrapperWidth - cardWidth - 10;
            }
            if (cardLeft < 10) cardLeft = 10;
            if (cardTop + cardHeight > wrapperHeight) {
                cardTop = cardTop - cardHeight - 20;
            }
            if (cardTop < 10) cardTop = 10;

            // Guardar coordenadas
            $('#cardYaw').val(yaw || '');
            $('#cardPitch').val(pitch || '');
            $('#cardVideoTime').val(videoTime || '');
            $('#cardPosX').val(posX || '');
            $('#cardPosY').val(posY || '');
            $('#cardEditIndex').val(-1);

            // Resetear campos
            $('#cardType').val('info');
            $('#cardTargetScene').val('');
            $('#cardTargetContainer').hide();
            $('#cardInfo').val('');
            $('#cardImageFile').val('');
            $('#cardImagePreview').attr('src', defaultHotspotImage);
            $('#addToListBtn').html('<i class="fa fa-plus"></i> Agregar');

            // Posicionar y mostrar
            $card.css({
                left: cardLeft + 'px',
                top: cardTop + 'px'
            }).show();
        }

        // ====== Ocultar card flotante ======
        function hideHotspotCard() {
            $('#hotspotCard').hide();
        }

        // ====== Actualizar lista de hotspots pendientes ======
        function updatePendingList() {
            var $tbody = $('#pendingTableBody');
            $tbody.empty();

            if (pendingHotspots.length === 0) {
                $('#pendingHotspotsSection').hide();
                $('#saveAllHotspots').prop('disabled', true);
                $('#saveCount').text('0');
                $('#pendingCount').text('0');
                return;
            }

            $('#pendingHotspotsSection').show();
            $('#saveAllHotspots').prop('disabled', false);
            $('#saveCount').text(pendingHotspots.length);
            $('#pendingCount').text(pendingHotspots.length);

            pendingHotspots.forEach(function(h, idx) {
                var typeLabel = h.type === 'scene' ? '<span class="badge badge-success">Enlace</span>' : '<span class="badge badge-info">Info</span>';
                var targetLabel = h.type === 'scene' && h.targetScene ? sceneTitleMap[h.targetScene] || '-' : '-';
                var infoShort = h.info.length > 30 ? h.info.substring(0, 30) + '...' : h.info;
                var imgSrc = h.imagePreviewUrl || defaultHotspotImage;

                var row = '<tr data-index="' + idx + '">' +
                    '<td>' + (idx + 1) + '</td>' +
                    '<td><img src="' + imgSrc + '" style="width:32px; height:32px; object-fit:cover; border-radius:50%; border:1px solid #ddd;"></td>' +
                    '<td>' + typeLabel + '</td>' +
                    '<td>' + targetLabel + '</td>' +
                    '<td title="' + h.info.replace(/"/g, '&quot;') + '">' + infoShort + '</td>' +
                    '<td>' +
                        '<button type="button" class="btn btn-sm btn-outline-primary edit-pending mr-1" data-index="' + idx + '" title="Editar"><i class="fa fa-pencil"></i></button>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger delete-pending" data-index="' + idx + '" title="Eliminar"><i class="fa fa-trash"></i></button>' +
                    '</td>' +
                '</tr>';
                $tbody.append(row);
            });

            // Actualizar marcadores en el visor
            updateViewerMarkers();
        }

        // ====== Actualizar marcadores en el visor ======
        function updateViewerMarkers() {
            if (isVideoScene) {
                // Para video, actualizar el marcador del último punto
                // (Los videos solo muestran un marcador a la vez, en el tiempo actual)
            } else {
                // Para panorama 360, necesitamos agregar los hotspots pendientes al viewer
                if (viewerMulti && pendingHotspots.length > 0) {
                    // Remover hotspots previos si existen
                    try {
                        var existingHotspots = viewerMulti.getConfig().hotSpots || [];
                        existingHotspots.forEach(function(hs, i) {
                            viewerMulti.removeHotSpot(hs.id || 'pending-' + i);
                        });
                    } catch(e) {}

                    // Agregar marcadores para cada hotspot pendiente
                    pendingHotspots.forEach(function(h, idx) {
                        if (h.yaw !== undefined && h.pitch !== undefined) {
                            try {
                                viewerMulti.addHotSpot({
                                    id: 'pending-' + idx,
                                    pitch: parseFloat(h.pitch),
                                    yaw: parseFloat(h.yaw),
                                    type: 'info',
                                    text: (idx + 1) + '. ' + (h.info.substring(0, 20) || 'Hotspot'),
                                    cssClass: 'pending-hotspot-marker'
                                });
                            } catch(e) {
                                console.log('Error adding hotspot marker:', e);
                            }
                        }
                    });
                }
            }
        }

        // ====== Inicializar modal de creación múltiple ======
        var panoramaMultiEl = document.getElementById('panorama-multi');
        var videoMultiEl = document.getElementById('video-multi');
        var videoMultiPlayer = document.getElementById('video-multi-player');
        var videoMultiProgress = document.getElementById('video-multi-progress');
        var videoMultiMarker = document.getElementById('video-multi-marker');

        // Setup video scrub
        setupVideoScrub(videoMultiEl, videoMultiPlayer, videoMultiProgress, function(x, y, time, clientX, clientY) {
            // Click en video → mostrar card
            videoMultiMarker.style.display = 'block';
            videoMultiMarker.style.left = x + '%';
            videoMultiMarker.style.top = y + '%';
            showHotspotCard(clientX, clientY, null, null, round3(time), round3(x), round3(y));
        });

        // Al abrir el modal
        $('#addHotspotMulti').on('shown.bs.modal', function() {
            pendingHotspots = [];
            currentSceneId = null;
            isVideoScene = false;
            destroyViewer(viewerMulti);
            viewerMulti = null;
            $('#viewerWrapper').hide();
            $('#sourceSceneMulti').val('');
            hideHotspotCard();
            updatePendingList();
            videoMultiMarker.style.display = 'none';
        });

        // Al cerrar el modal
        $('#addHotspotMulti').on('hidden.bs.modal', function() {
            destroyViewer(viewerMulti);
            viewerMulti = null;
            pendingHotspots = [];
            hideHotspotCard();
            if (videoMultiPlayer) {
                videoMultiPlayer.pause();
                videoMultiPlayer.removeAttribute('src');
            }
        });

        // Cambio de escena origen
        $('#sourceSceneMulti').on('change', function() {
            currentSceneId = $(this).val();
            var sceneType = sceneTypeMap[currentSceneId] || 'equirectangular';
            isVideoScene = (sceneType === 'video');

            $('#viewerWrapper').show();
            hideHotspotCard();

            // Limpiar hotspots pendientes al cambiar de escena
            pendingHotspots = [];
            updatePendingList();

            if (isVideoScene) {
                // Escena de video
                panoramaMultiEl.style.display = 'none';
                videoMultiEl.style.display = 'block';
                destroyViewer(viewerMulti);
                viewerMulti = null;

                var videoUrl = sceneVideoMap[currentSceneId];
                if (videoUrl) {
                    videoMultiPlayer.src = videoUrl;
                    videoMultiPlayer.load();
                }
                videoMultiMarker.style.display = 'none';
            } else {
                // Escena panorama 360
                videoMultiEl.style.display = 'none';
                if (videoMultiPlayer) {
                    videoMultiPlayer.pause();
                    videoMultiPlayer.removeAttribute('src');
                }
                panoramaMultiEl.style.display = 'block';

                var imageUrl = sceneImageMap[currentSceneId];
                if (!imageUrl) return;

                destroyViewer(viewerMulti);
                viewerMulti = pannellum.viewer('panorama-multi', {
                    type: "equirectangular",
                    panorama: imageUrl,
                    autoLoad: true,
                    showControls: true,
                    hotSpotDebug: false
                });

                // Click en panorama → mostrar card
                viewerMulti.on('mousedown', function(ev) {
                    var coords = viewerMulti.mouseEventToCoords(ev);
                    if (!coords) return;
                    var pitch = coords[0];
                    var yaw = coords[1];
                    showHotspotCard(ev.clientX, ev.clientY, round3(yaw), round3(pitch), null, null, null);
                });
            }
        });

        // Cerrar card flotante
        $('#closeHotspotCard, #cancelHotspotCard').on('click', function() {
            hideHotspotCard();
            videoMultiMarker.style.display = 'none';
        });

        // Cambio de tipo en card
        $('#cardType').on('change', function() {
            if ($(this).val() === 'scene') {
                $('#cardTargetContainer').show();
            } else {
                $('#cardTargetContainer').hide();
                $('#cardTargetScene').val('');
            }
        });

        // Preview de imagen en el card
        $('#cardImageFile').on('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#cardImagePreview').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            } else {
                $('#cardImagePreview').attr('src', defaultHotspotImage);
            }
        });

        // Agregar hotspot a la lista
        $('#addToListBtn').on('click', function() {
            var type = $('#cardType').val();
            var targetScene = $('#cardTargetScene').val();
            var info = $('#cardInfo').val().trim();

            // Validaciones
            if (!info) {
                alert('Por favor ingresa la información del hotspot');
                return;
            }
            if (type === 'scene' && !targetScene) {
                alert('Por favor selecciona la escena destino');
                return;
            }

            // Capturar archivo de imagen si existe
            var imageFile = null;
            var imagePreviewUrl = null;
            var fileInput = document.getElementById('cardImageFile');
            if (fileInput.files && fileInput.files[0]) {
                imageFile = fileInput.files[0];
                imagePreviewUrl = $('#cardImagePreview').attr('src');
            }

            var hotspotData = {
                sourceScene: currentSceneId,
                type: type,
                targetScene: type === 'scene' ? targetScene : null,
                info: info,
                yaw: $('#cardYaw').val(),
                pitch: $('#cardPitch').val(),
                video_time: $('#cardVideoTime').val(),
                pos_x: $('#cardPosX').val(),
                pos_y: $('#cardPosY').val(),
                imageFile: imageFile,
                imagePreviewUrl: imagePreviewUrl
            };

            var editIndex = parseInt($('#cardEditIndex').val());
            if (editIndex >= 0) {
                // Editar existente
                pendingHotspots[editIndex] = hotspotData;
            } else {
                // Agregar nuevo
                pendingHotspots.push(hotspotData);
            }

            updatePendingList();
            hideHotspotCard();
            videoMultiMarker.style.display = 'none';
        });

        // Editar hotspot pendiente
        $(document).on('click', '.edit-pending', function() {
            var idx = $(this).data('index');
            var h = pendingHotspots[idx];
            if (!h) return;

            // Rellenar card con datos existentes
            $('#cardType').val(h.type).trigger('change');
            $('#cardTargetScene').val(h.targetScene || '');
            $('#cardInfo').val(h.info);
            $('#cardYaw').val(h.yaw || '');
            $('#cardPitch').val(h.pitch || '');
            $('#cardVideoTime').val(h.video_time || '');
            $('#cardPosX').val(h.pos_x || '');
            $('#cardPosY').val(h.pos_y || '');
            $('#cardEditIndex').val(idx);
            $('#cardImageFile').val('');
            $('#cardImagePreview').attr('src', h.imagePreviewUrl || defaultHotspotImage);
            $('#addToListBtn').html('<i class="fa fa-check"></i> Actualizar');

            // Mostrar card en el centro
            var $wrapper = $('#viewerWrapper');
            var $card = $('#hotspotCard');
            $card.css({
                left: ($wrapper.width() / 2 - 160) + 'px',
                top: '100px'
            }).show();

            // Si es panorama, navegar a la posición
            if (!isVideoScene && viewerMulti && h.yaw && h.pitch) {
                viewerMulti.setYaw(parseFloat(h.yaw));
                viewerMulti.setPitch(parseFloat(h.pitch));
            }
            // Si es video, navegar al tiempo
            if (isVideoScene && h.video_time) {
                videoMultiPlayer.currentTime = parseFloat(h.video_time);
                videoMultiMarker.style.display = 'block';
                videoMultiMarker.style.left = h.pos_x + '%';
                videoMultiMarker.style.top = h.pos_y + '%';
            }
        });

        // Eliminar hotspot pendiente
        $(document).on('click', '.delete-pending', function() {
            var idx = $(this).data('index');
            pendingHotspots.splice(idx, 1);
            updatePendingList();
        });

        // Guardar todos los hotspots
        $('#saveAllHotspots').on('click', function() {
            if (pendingHotspots.length === 0) {
                alert('No hay hotspots pendientes para guardar');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

            // Construir FormData para enviar archivos junto con datos
            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('property_id', $('#propertyIdBatch').val());

            pendingHotspots.forEach(function(h, idx) {
                formData.append('hotspots[' + idx + '][sourceScene]', h.sourceScene);
                formData.append('hotspots[' + idx + '][type]', h.type);
                formData.append('hotspots[' + idx + '][info]', h.info);
                if (h.targetScene) formData.append('hotspots[' + idx + '][targetScene]', h.targetScene);
                if (h.yaw) formData.append('hotspots[' + idx + '][yaw]', h.yaw);
                if (h.pitch) formData.append('hotspots[' + idx + '][pitch]', h.pitch);
                if (h.video_time) formData.append('hotspots[' + idx + '][video_time]', h.video_time);
                if (h.pos_x) formData.append('hotspots[' + idx + '][pos_x]', h.pos_x);
                if (h.pos_y) formData.append('hotspots[' + idx + '][pos_y]', h.pos_y);
                // Adjuntar imagen si existe
                if (h.imageFile) {
                    formData.append('images_' + idx, h.imageFile);
                }
            });

            $.ajax({
                url: '{{ route("addHotspotBatch") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location.href = response.redirect;
                    } else {
                        alert('Error: ' + response.message);
                        $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Guardar Todos (<span id="saveCount">' + pendingHotspots.length + '</span>)');
                    }
                },
                error: function(xhr) {
                    var msg = 'Error al guardar los hotspots';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                    $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Guardar Todos (<span id="saveCount">' + pendingHotspots.length + '</span>)');
                }
            });
        });

        // ====== EDIT (mantener funcionalidad existente) ======
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

<style>
/* Estilos para marcadores de hotspots pendientes */
.pending-hotspot-marker {
    background: rgba(40, 167, 69, 0.8) !important;
    border: 2px solid #fff !important;
    border-radius: 50% !important;
    width: 24px !important;
    height: 24px !important;
    font-size: 11px !important;
    color: #fff !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
}
.pending-hotspot-marker span {
    display: none;
}
</style>
