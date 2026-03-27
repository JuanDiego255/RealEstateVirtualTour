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

                        {{-- Visor spin (escenas 360 de vehículos) --}}
                        <div id="spin-multi" style="width: 100%; height: 450px; display: none; position: relative; background: #1a1a1a; cursor: ew-resize; overflow: hidden;">
                            <canvas id="spin-multi-canvas" style="width:100%; height:100%; object-fit:contain;"></canvas>
                            <div id="spin-multi-marker" style="display:none; position:absolute; width:24px; height:24px; border:3px solid #27ae60; border-radius:50%; background:rgba(39,174,96,0.3); transform:translate(-50%,-50%); pointer-events:none; z-index:5;"></div>
                            <div id="spin-multi-loading" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); color:#fff; font-size:14px; background:rgba(0,0,0,0.7); padding:10px 20px; border-radius:8px;">
                                <i class="fa fa-spinner fa-spin mr-2"></i> Cargando frames... <span id="spin-load-progress">0%</span>
                            </div>
                            <div style="position:absolute; bottom:0; left:0; width:100%; height:4px; background:rgba(255,255,255,0.2);">
                                <div id="spin-multi-progress" style="height:100%; background:#27ae60; width:0%;"></div>
                            </div>
                            <div id="spin-multi-frame-indicator" style="position:absolute; top:10px; right:10px; background:rgba(0,0,0,0.6); color:#fff; padding:4px 10px; border-radius:6px; font-size:12px;">
                                Frame: <span id="spin-current-frame">1</span> / <span id="spin-total-frames">72</span> | Yaw: <span id="spin-current-yaw">0</span>&deg;
                            </div>
                            <div style="position:absolute; bottom:10px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.6); color:#fff; padding:4px 12px; border-radius:10px; font-size:11px;">
                                <i class="fa fa-arrows-h"></i> Arrastra para rotar, haz clic para posicionar hotspot
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

                                {{-- Mini visor para elegir dirección de llegada --}}
                                <div class="form-group mb-2" id="cardTargetYawContainer" style="display: none;">
                                    <label class="small mb-1"><i class="fa fa-eye mr-1"></i> Dirección al llegar <small class="text-muted">(mueve el visor)</small></label>
                                    <div id="cardTargetViewer" style="width:100%; height:140px; border-radius:6px; overflow:hidden; border:1px solid #ddd; background:#111;"></div>
                                    <input type="hidden" id="cardTargetYaw">
                                    <input type="hidden" id="cardTargetPitch">
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

{{-- Botón Editar Hotspots --}}
<button type="button" class="btn btn-rounded btn-outline-warning mb-3 ml-2" data-toggle="modal" data-target="#editHotspotMulti">
    <i class="fa fa-pencil mr-1"></i> Editar HotSpots
</button>

{{-- Modal unificado para edición de hotspots --}}
<div class="modal fade" id="editHotspotMulti">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fa fa-pencil mr-2"></i>Editar Puntos de Acceso</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editPropertyId" value="{{ $id }}">

                {{-- Paso 1: Seleccionar escena origen --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="editSourceScene" class="font-weight-bold">
                            <i class="fa fa-image mr-1"></i> Escena Origen
                        </label>
                        <select class="form-control form-control-lg" id="editSourceScene">
                            <option value="" disabled selected>Seleccione una escena</option>
                            @foreach ($scene as $item)
                                <option value="{{ $item->id }}" data-type="{{ $item->type }}">{{ $item->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="alert alert-warning mb-0 w-100" style="font-size: 13px;">
                            <i class="fa fa-info-circle mr-1"></i>
                            Selecciona una escena para ver sus hotspots. Haz clic en <b>Editar</b> en la tabla para modificar cada uno.
                        </div>
                    </div>
                </div>

                {{-- Visor con card flotante --}}
                <div id="editViewerWrapper" style="position: relative; display: none;">
                    <div id="panorama-edit-multi" style="width: 100%; height: 450px;"></div>
                    <div id="video-edit-multi" style="width: 100%; height: 450px; display: none; position: relative; background: #000; cursor: crosshair; overflow: hidden;">
                        <video id="video-edit-multi-player" muted playsinline preload="auto" style="width:100%; height:100%; object-fit:cover; pointer-events:none;"></video>
                        <div id="video-edit-multi-marker" style="display:none; position:absolute; width:20px; height:20px; border:3px solid #e74c3c; border-radius:50%; background:rgba(231,76,60,0.3); transform:translate(-50%,-50%); pointer-events:none; z-index:5;"></div>
                        <div style="position:absolute; bottom:0; left:0; width:100%; height:4px; background:rgba(255,255,255,0.2);">
                            <div id="video-edit-multi-progress" style="height:100%; background:#ffc107; width:0%;"></div>
                        </div>
                        <div style="position:absolute; bottom:10px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.6); color:#fff; padding:4px 12px; border-radius:10px; font-size:11px;">
                            <i class="fa fa-arrows-h"></i> Arrastra para navegar, haz clic para reubicar
                        </div>
                    </div>

                    {{-- Card flotante (reutiliza misma estructura del add) --}}
                    <div id="editHotspotCard" class="card shadow" style="display: none; position: absolute; width: 320px; z-index: 1000; border: 2px solid #ffc107;">
                        <div class="card-header bg-warning text-dark py-2 d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-pencil mr-1"></i> Editar Hotspot</span>
                            <button type="button" class="close" id="closeEditCard" style="font-size: 18px;">&times;</button>
                        </div>
                        <div class="card-body py-2">
                            <input type="hidden" id="editCardHotspotId">
                            <input type="hidden" id="editCardYaw">
                            <input type="hidden" id="editCardPitch">
                            <input type="hidden" id="editCardVideoTime">
                            <input type="hidden" id="editCardPosX">
                            <input type="hidden" id="editCardPosY">

                            <div class="form-group mb-2">
                                <label class="small mb-1"><i class="fa fa-tag mr-1"></i> Tipo</label>
                                <select class="form-control form-control-sm" id="editCardType">
                                    <option value="info">Información</option>
                                    <option value="scene">Enlace a escena</option>
                                </select>
                            </div>

                            <div class="form-group mb-2" id="editCardTargetContainer" style="display: none;">
                                <label class="small mb-1"><i class="fa fa-link mr-1"></i> Escena Destino</label>
                                <select class="form-control form-control-sm" id="editCardTargetScene">
                                    <option value="">Seleccione</option>
                                    @foreach ($scene as $item)
                                        <option value="{{ $item->id }}">{{ $item->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-2" id="editCardTargetYawContainer" style="display: none;">
                                <label class="small mb-1"><i class="fa fa-eye mr-1"></i> Dirección al llegar <small class="text-muted">(mueve el visor)</small></label>
                                <div id="editCardTargetViewer" style="width:100%; height:140px; border-radius:6px; overflow:hidden; border:1px solid #ddd; background:#111;"></div>
                                <input type="hidden" id="editCardTargetYaw">
                                <input type="hidden" id="editCardTargetPitch">
                            </div>

                            <div class="form-group mb-2">
                                <label class="small mb-1"><i class="fa fa-comment mr-1"></i> Información</label>
                                <textarea class="form-control form-control-sm" id="editCardInfo" rows="2"></textarea>
                            </div>

                            <div class="form-group mb-2">
                                <label class="small mb-1"><i class="fa fa-image mr-1"></i> Imagen</label>
                                <div class="d-flex align-items-center">
                                    <img id="editCardImagePreview" src="{{ url('virtualtour/images/hotspot.png') }}" alt="Preview" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid #ddd; margin-right: 8px;">
                                    <div style="flex: 1;">
                                        <input type="file" class="form-control-file" id="editCardImageFile" accept="image/*" style="font-size: 11px;">
                                        <small class="text-muted">Dejar vacío para mantener la imagen actual.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="cancelEditCard">
                                    <i class="fa fa-times"></i> Cancelar
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" id="applyEditBtn">
                                    <i class="fa fa-check"></i> Aplicar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabla de hotspots existentes --}}
                <div id="editHotspotsTableSection" style="display: none;" class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><i class="fa fa-list mr-1"></i> Hotspots de esta escena</h6>
                        <span class="badge badge-warning" id="editHotspotCount">0</span>
                    </div>
                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0" id="editHotspotsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 30px;">#</th>
                                    <th style="width: 50px;">Img</th>
                                    <th>Tipo</th>
                                    <th>Destino</th>
                                    <th>Info</th>
                                    <th style="width: 60px;">Estado</th>
                                    <th style="width: 110px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="editHotspotsTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer px-0 pb-0 mt-3">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-warning" id="saveEditedHotspots" disabled>
                        <i class="fa fa-save mr-1"></i> Guardar Cambios (<span id="editSaveCount">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabla principal de hotspots (solo lectura, DataTables) --}}
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
        var scenePanoramaMap = {
            @foreach ($scene as $scenes)
                @if($scenes->image && $scenes->type === 'equirectangular')
                {{ $scenes->id }}: "{{ route('file', $scenes->image) }}",
                @endif
            @endforeach
        };
        var sceneDefaultYaw = {
            @foreach ($scene as $scenes)
                {{ $scenes->id }}: {{ (float) $scenes->yaw }},
            @endforeach
        };
        var sceneDefaultPitch = {
            @foreach ($scene as $scenes)
                {{ $scenes->id }}: {{ (float) $scenes->pitch }},
            @endforeach
        };
        var sceneSpinFramesDirMap = {
            @foreach ($scene as $scenes)
                @if($scenes->spin_frames_dir)
                {{ $scenes->id }}: "{{ $scenes->spin_frames_dir }}",
                @endif
            @endforeach
        };
        var sceneSpinFramesCountMap = {
            @foreach ($scene as $scenes)
                @if($scenes->spin_frames_count)
                {{ $scenes->id }}: {{ (int) $scenes->spin_frames_count }},
                @endif
            @endforeach
        };

        // ====== Variables globales para creación múltiple ======
        var viewerMulti = null;
        var pendingHotspots = []; // Cada item: { ...datos, imageFile: File|null, imagePreviewUrl: string|null }
        var currentSceneId = null;
        var isVideoScene = false;
        var isSpinScene = false;
        var defaultHotspotImage = '{{ url("virtualtour/images/hotspot.png") }}';

        // ====== Variables para visor spin ======
        var spinCache = {
            frames: {},
            totalFrames: 0,
            currentIndex: 1,
            framesDir: null,
            loaded: 0,
            ready: false
        };
        var spinDragState = {
            isDragging: false,
            startX: 0,
            startIndex: 1,
            sensitivity: 0.15
        };

        // Estado para detectar click vs drag en panorama
        var panoramaDragState = {
            isMouseDown: false,
            startX: 0,
            startY: 0,
            hasMoved: false,
            coords: null,
            clientX: 0,
            clientY: 0
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

        // ====== Funciones para visor spin ======
        function resetSpinCache() {
            spinCache.frames = {};
            spinCache.totalFrames = 0;
            spinCache.currentIndex = 1;
            spinCache.framesDir = null;
            spinCache.loaded = 0;
            spinCache.ready = false;
            spinDragState.isDragging = false;
            document.getElementById('spin-multi-marker').style.display = 'none';
            document.getElementById('spin-multi-loading').style.display = 'block';
        }

        function loadSpinFrames(sceneId) {
            var framesDir = sceneSpinFramesDirMap[sceneId];
            var totalFrames = sceneSpinFramesCountMap[sceneId] || 72;

            if (!framesDir) {
                console.error('No frames directory for scene', sceneId);
                return;
            }

            resetSpinCache();
            spinCache.framesDir = framesDir;
            spinCache.totalFrames = totalFrames;

            document.getElementById('spin-total-frames').textContent = totalFrames;

            var baseUrl = '{{ url("storage") }}/' + framesDir + '/';
            var extension = '.webp';

            // Cargar todos los frames
            for (var i = 1; i <= totalFrames; i++) {
                (function(idx) {
                    var img = new Image();
                    img.onload = function() {
                        spinCache.frames[idx] = img;
                        spinCache.loaded++;
                        var progress = Math.round((spinCache.loaded / totalFrames) * 100);
                        document.getElementById('spin-load-progress').textContent = progress + '%';

                        if (spinCache.loaded === totalFrames) {
                            spinCache.ready = true;
                            document.getElementById('spin-multi-loading').style.display = 'none';
                            showSpinFrame(1);
                        }
                    };
                    img.onerror = function() {
                        // Intentar con otra extensión
                        var img2 = new Image();
                        img2.onload = function() {
                            spinCache.frames[idx] = img2;
                            spinCache.loaded++;
                            var progress = Math.round((spinCache.loaded / totalFrames) * 100);
                            document.getElementById('spin-load-progress').textContent = progress + '%';

                            if (spinCache.loaded === totalFrames) {
                                spinCache.ready = true;
                                document.getElementById('spin-multi-loading').style.display = 'none';
                                showSpinFrame(1);
                            }
                        };
                        img2.onerror = function() {
                            console.warn('Failed to load frame', idx);
                            spinCache.loaded++;
                            if (spinCache.loaded === totalFrames) {
                                spinCache.ready = true;
                                document.getElementById('spin-multi-loading').style.display = 'none';
                                showSpinFrame(1);
                            }
                        };
                        img2.src = baseUrl + 'frame-' + String(idx).padStart(3, '0') + '.png';
                    };
                    img.src = baseUrl + 'frame-' + String(idx).padStart(3, '0') + extension;
                })(i);
            }
        }

        function showSpinFrame(idx) {
            if (!spinCache.ready) return;
            idx = Math.max(1, Math.min(spinCache.totalFrames, idx));
            spinCache.currentIndex = idx;

            var canvas = document.getElementById('spin-multi-canvas');
            var ctx = canvas.getContext('2d');
            var frame = spinCache.frames[idx];

            if (!frame) return;

            // Ajustar tamaño del canvas
            var container = document.getElementById('spin-multi');
            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;

            // Dibujar frame centrado
            var scale = Math.min(canvas.width / frame.width, canvas.height / frame.height);
            var w = frame.width * scale;
            var h = frame.height * scale;
            var x = (canvas.width - w) / 2;
            var y = (canvas.height - h) / 2;

            ctx.fillStyle = '#1a1a1a';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(frame, x, y, w, h);

            // Actualizar indicadores
            var yaw = calculateYawFromFrame(idx);
            document.getElementById('spin-current-frame').textContent = idx;
            document.getElementById('spin-current-yaw').textContent = yaw.toFixed(1);

            // Actualizar barra de progreso
            var pct = ((idx - 1) / (spinCache.totalFrames - 1)) * 100;
            document.getElementById('spin-multi-progress').style.width = pct + '%';
        }

        function calculateYawFromFrame(frameIdx) {
            // Mapear frame a yaw (frame 1 = 0°, frame N = ~360°)
            if (spinCache.totalFrames <= 1) return 0;
            return ((frameIdx - 1) / spinCache.totalFrames) * 360;
        }

        function calculateFrameFromYaw(yaw) {
            // Normalizar yaw a [0, 360)
            yaw = ((yaw % 360) + 360) % 360;
            // Mapear yaw a frame
            return Math.round((yaw / 360) * spinCache.totalFrames) + 1;
        }

        // Eventos drag para spin viewer
        (function() {
            var spinEl = document.getElementById('spin-multi');
            var spinMarker = document.getElementById('spin-multi-marker');
            var clickState = { startX: 0, startY: 0, hasMoved: false, clientX: 0, clientY: 0 };

            spinEl.addEventListener('mousedown', function(e) {
                if (!spinCache.ready) return;
                spinDragState.isDragging = true;
                spinDragState.startX = e.clientX;
                spinDragState.startIndex = spinCache.currentIndex;
                clickState.startX = e.clientX;
                clickState.startY = e.clientY;
                clickState.hasMoved = false;
                clickState.clientX = e.clientX;
                clickState.clientY = e.clientY;
                e.preventDefault();
            });

            document.addEventListener('mousemove', function(e) {
                if (!spinDragState.isDragging) return;
                var deltaX = e.clientX - spinDragState.startX;
                if (Math.abs(deltaX) > 3) clickState.hasMoved = true;

                // Calcular nuevo frame
                var deltaFrames = Math.round(deltaX * spinDragState.sensitivity);
                var newIdx = spinDragState.startIndex + deltaFrames;

                // Wrap around
                while (newIdx < 1) newIdx += spinCache.totalFrames;
                while (newIdx > spinCache.totalFrames) newIdx -= spinCache.totalFrames;

                showSpinFrame(newIdx);
            });

            document.addEventListener('mouseup', function(e) {
                if (!spinDragState.isDragging) return;
                var wasDragging = clickState.hasMoved;
                spinDragState.isDragging = false;

                // Si no arrastró, es un click para colocar hotspot
                if (!wasDragging && spinCache.ready && isSpinScene) {
                    var rect = spinEl.getBoundingClientRect();
                    var posX = ((e.clientX - rect.left) / rect.width) * 100;
                    var posY = ((e.clientY - rect.top) / rect.height) * 100;
                    posX = Math.max(0, Math.min(100, posX));
                    posY = Math.max(0, Math.min(100, posY));

                    // Calcular yaw desde el frame actual
                    var yaw = calculateYawFromFrame(spinCache.currentIndex);

                    // Mostrar marcador
                    spinMarker.style.display = 'block';
                    spinMarker.style.left = posX + '%';
                    spinMarker.style.top = posY + '%';

                    // Mostrar card - yaw calculado dinámicamente, pitch = 0 para spin
                    showHotspotCard(clickState.clientX, clickState.clientY, round3(yaw), 0, null, round3(posX), round3(posY));
                }
            });

            // Touch events
            spinEl.addEventListener('touchstart', function(e) {
                if (!spinCache.ready) return;
                var touch = e.touches[0];
                spinDragState.isDragging = true;
                spinDragState.startX = touch.clientX;
                spinDragState.startIndex = spinCache.currentIndex;
                clickState.startX = touch.clientX;
                clickState.startY = touch.clientY;
                clickState.hasMoved = false;
            }, { passive: true });

            document.addEventListener('touchmove', function(e) {
                if (!spinDragState.isDragging) return;
                var touch = e.touches[0];
                var deltaX = touch.clientX - spinDragState.startX;
                if (Math.abs(deltaX) > 3) clickState.hasMoved = true;

                var deltaFrames = Math.round(deltaX * spinDragState.sensitivity);
                var newIdx = spinDragState.startIndex + deltaFrames;

                while (newIdx < 1) newIdx += spinCache.totalFrames;
                while (newIdx > spinCache.totalFrames) newIdx -= spinCache.totalFrames;

                showSpinFrame(newIdx);
            }, { passive: true });

            document.addEventListener('touchend', function(e) {
                if (!spinDragState.isDragging) return;
                spinDragState.isDragging = false;
            });
        })();

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
            $('#cardTargetYawContainer').hide();
            $('#cardTargetYaw').val('');
            $('#cardTargetPitch').val('');
            if (cardTargetViewerInstance) { cardTargetViewerInstance.destroy(); cardTargetViewerInstance = null; }
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
            isSpinScene = false;
            destroyViewer(viewerMulti);
            viewerMulti = null;
            $('#viewerWrapper').hide();
            $('#sourceSceneMulti').val('');
            hideHotspotCard();
            updatePendingList();
            videoMultiMarker.style.display = 'none';
            resetSpinCache();
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
            resetSpinCache();
        });

        // Cambio de escena origen
        $('#sourceSceneMulti').on('change', function() {
            currentSceneId = $(this).val();
            var sceneType = sceneTypeMap[currentSceneId] || 'equirectangular';
            var hasSpinFrames = sceneSpinFramesDirMap[currentSceneId];

            isVideoScene = (sceneType === 'video' && !hasSpinFrames);
            isSpinScene = !!hasSpinFrames;

            $('#viewerWrapper').show();
            hideHotspotCard();

            // Limpiar hotspots pendientes al cambiar de escena
            pendingHotspots = [];
            updatePendingList();

            // Ocultar todos los visores primero
            panoramaMultiEl.style.display = 'none';
            videoMultiEl.style.display = 'none';
            document.getElementById('spin-multi').style.display = 'none';

            if (isSpinScene) {
                // Escena spin (360 de vehículos)
                destroyViewer(viewerMulti);
                viewerMulti = null;
                if (videoMultiPlayer) {
                    videoMultiPlayer.pause();
                    videoMultiPlayer.removeAttribute('src');
                }

                document.getElementById('spin-multi').style.display = 'block';
                loadSpinFrames(currentSceneId);
            } else if (isVideoScene) {
                // Escena de video
                destroyViewer(viewerMulti);
                viewerMulti = null;
                resetSpinCache();
                videoMultiEl.style.display = 'block';

                var videoUrl = sceneVideoMap[currentSceneId];
                if (videoUrl) {
                    videoMultiPlayer.src = videoUrl;
                    videoMultiPlayer.load();
                }
                videoMultiMarker.style.display = 'none';
            } else {
                // Escena panorama 360
                if (videoMultiPlayer) {
                    videoMultiPlayer.pause();
                    videoMultiPlayer.removeAttribute('src');
                }
                resetSpinCache();
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

                // Click en panorama → detectar si es click o drag
                viewerMulti.on('mousedown', function(ev) {
                    var coords = viewerMulti.mouseEventToCoords(ev);
                    if (!coords) return;
                    panoramaDragState.isMouseDown = true;
                    panoramaDragState.startX = ev.clientX;
                    panoramaDragState.startY = ev.clientY;
                    panoramaDragState.hasMoved = false;
                    panoramaDragState.coords = coords;
                    panoramaDragState.clientX = ev.clientX;
                    panoramaDragState.clientY = ev.clientY;
                });
            }
        });

        // Detectar movimiento para distinguir click de drag en panorama
        $(document).on('mousemove.panoramaDrag', function(e) {
            if (!panoramaDragState.isMouseDown) return;
            var deltaX = Math.abs(e.clientX - panoramaDragState.startX);
            var deltaY = Math.abs(e.clientY - panoramaDragState.startY);
            if (deltaX > 5 || deltaY > 5) {
                panoramaDragState.hasMoved = true;
            }
        });

        // Al soltar el mouse, si no hubo drag, mostrar card
        $(document).on('mouseup.panoramaDrag', function(e) {
            if (!panoramaDragState.isMouseDown) return;
            var wasDragging = panoramaDragState.hasMoved;
            var coords = panoramaDragState.coords;

            // Resetear estado
            panoramaDragState.isMouseDown = false;

            // Si no arrastró y tenemos coordenadas válidas, mostrar card
            if (!wasDragging && coords && viewerMulti && !isVideoScene) {
                var pitch = coords[0];
                var yaw = coords[1];
                showHotspotCard(panoramaDragState.clientX, panoramaDragState.clientY, round3(yaw), round3(pitch), null, null, null);
            }
        });

        // Cerrar card flotante
        $('#closeHotspotCard, #cancelHotspotCard').on('click', function() {
            hideHotspotCard();
            videoMultiMarker.style.display = 'none';
        });

        // Cambio de tipo en card
        var cardTargetViewerInstance = null;

        $('#cardType').on('change', function() {
            if ($(this).val() === 'scene') {
                $('#cardTargetContainer').show();
            } else {
                $('#cardTargetContainer').hide();
                $('#cardTargetYawContainer').hide();
                $('#cardTargetScene').val('');
                if (cardTargetViewerInstance) { cardTargetViewerInstance.destroy(); cardTargetViewerInstance = null; }
            }
        });

        $('#cardTargetScene').on('change', function() {
            var sceneId = $(this).val();
            if (!sceneId || !scenePanoramaMap[sceneId]) {
                $('#cardTargetYawContainer').hide();
                if (cardTargetViewerInstance) { cardTargetViewerInstance.destroy(); cardTargetViewerInstance = null; }
                return;
            }
            $('#cardTargetYawContainer').show();
            if (cardTargetViewerInstance) { cardTargetViewerInstance.destroy(); cardTargetViewerInstance = null; }
            var initYaw = parseFloat($('#cardTargetYaw').val()) || sceneDefaultYaw[sceneId] || 0;
            var initPitch = parseFloat($('#cardTargetPitch').val()) || sceneDefaultPitch[sceneId] || 0;
            cardTargetViewerInstance = pannellum.viewer('cardTargetViewer', {
                type: 'equirectangular',
                panorama: scenePanoramaMap[sceneId],
                autoLoad: true,
                showControls: false,
                compass: false,
                mouseZoom: false,
                hfov: 100,
                yaw: initYaw,
                pitch: initPitch
            });
            cardTargetViewerInstance.on('mouseup', function() {
                $('#cardTargetYaw').val(cardTargetViewerInstance.getYaw().toFixed(2));
                $('#cardTargetPitch').val(cardTargetViewerInstance.getPitch().toFixed(2));
            });
            cardTargetViewerInstance.on('touchend', function() {
                $('#cardTargetYaw').val(cardTargetViewerInstance.getYaw().toFixed(2));
                $('#cardTargetPitch').val(cardTargetViewerInstance.getPitch().toFixed(2));
            });
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
                target_yaw: type === 'scene' ? ($('#cardTargetYaw').val() || '') : '',
                target_pitch: type === 'scene' ? ($('#cardTargetPitch').val() || '') : '',
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
            $('#cardTargetYaw').val(h.target_yaw || '');
            $('#cardTargetPitch').val(h.target_pitch || '');
            $('#cardType').val(h.type).trigger('change');
            if (h.type === 'scene' && h.targetScene) {
                $('#cardTargetScene').val(h.targetScene).trigger('change');
            } else {
                $('#cardTargetScene').val('');
            }
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
                if (h.target_yaw) formData.append('hotspots[' + idx + '][target_yaw]', h.target_yaw);
                if (h.target_pitch) formData.append('hotspots[' + idx + '][target_pitch]', h.target_pitch);
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

        // ====== EDICIÓN UNIFICADA DE HOTSPOTS ======
        var editViewerMulti = null;
        var editSceneId = null;
        var editIsVideo = false;
        var editExistingHotspots = []; // hotspots cargados del server
        var editModifiedIds = {};     // { id: { ...changes } }  hotspots con cambios pendientes
        var editNewImageFiles = {};   // { id: File } nuevas imágenes por subir
        var editCardTargetViewerInst = null;
        var editDragState = { isMouseDown: false, startX: 0, startY: 0, hasMoved: false, coords: null, clientX: 0, clientY: 0 };

        function hideEditCard() { $('#editHotspotCard').hide(); }

        function showEditCard(x, y) {
            var $card = $('#editHotspotCard');
            var $wrapper = $('#editViewerWrapper');
            var wrapperOffset = $wrapper.offset();
            var wrapperW = $wrapper.width();
            var wrapperH = $wrapper.height();
            var cardW = 320, cardH = 420;
            var cardLeft = x - wrapperOffset.left + 15;
            var cardTop = y - wrapperOffset.top - 20;
            if (cardLeft + cardW > wrapperW) cardLeft = wrapperW - cardW - 10;
            if (cardTop + cardH > wrapperH) cardTop = wrapperH - cardH - 10;
            if (cardLeft < 10) cardLeft = 10;
            if (cardTop < 10) cardTop = 10;
            $card.css({ left: cardLeft + 'px', top: cardTop + 'px' }).show();
        }

        function renderEditTable() {
            var $tbody = $('#editHotspotsTableBody');
            $tbody.empty();
            var modCount = Object.keys(editModifiedIds).length;
            editExistingHotspots.forEach(function(h, idx) {
                var imgSrc = h.image_url || defaultHotspotImage;
                var destName = h.targetScene ? (sceneTitleMap[h.targetScene] || 'Escena ' + h.targetScene) : '-';
                var typeLabel = h.type === 'scene' ? '<span class="badge badge-info">Enlace</span>' : '<span class="badge badge-secondary">Info</span>';
                var shortInfo = (h.info || '').substring(0, 30);
                var isModified = !!editModifiedIds[h.id];
                var stateHtml = isModified ? '<span class="badge badge-warning" title="Modificado"><i class="fa fa-pencil"></i></span>' : '<span class="badge badge-light" title="Sin cambios">-</span>';
                $tbody.append(
                    '<tr' + (isModified ? ' class="table-warning"' : '') + '>' +
                    '<td>' + (idx + 1) + '</td>' +
                    '<td><img src="' + imgSrc + '" style="width:30px;height:30px;object-fit:cover;border-radius:50%;"></td>' +
                    '<td>' + typeLabel + '</td>' +
                    '<td style="font-size:12px;">' + destName + '</td>' +
                    '<td style="font-size:12px;" title="' + (h.info || '').replace(/"/g, '&quot;') + '">' + shortInfo + '</td>' +
                    '<td>' + stateHtml + '</td>' +
                    '<td>' +
                        '<button class="btn btn-sm btn-outline-warning edit-existing-hotspot mr-1" data-index="' + idx + '" title="Editar"><i class="fa fa-pencil"></i></button>' +
                        '<button class="btn btn-sm btn-outline-danger delete-existing-hotspot" data-index="' + idx + '" data-id="' + h.id + '" title="Eliminar"><i class="fa fa-trash"></i></button>' +
                    '</td>' +
                    '</tr>'
                );
            });
            $('#editHotspotCount').text(editExistingHotspots.length);
            $('#editSaveCount').text(modCount);
            $('#saveEditedHotspots').prop('disabled', modCount === 0);
            $('#editHotspotsTableSection').toggle(editExistingHotspots.length > 0);
        }

        function updateEditViewerMarkers() {
            if (!editViewerMulti || editIsVideo) return;
            try {
                // Quitar marcadores previos
                var allHs = editViewerMulti.getConfig().hotSpots || [];
                allHs.forEach(function(h) { try { editViewerMulti.removeHotSpot(h.id); } catch(e) {} });
            } catch(e) {}
            editExistingHotspots.forEach(function(h, idx) {
                if (h.yaw !== null && h.pitch !== null) {
                    try {
                        editViewerMulti.addHotSpot({
                            id: 'edit_marker_' + idx,
                            pitch: parseFloat(h.pitch),
                            yaw: parseFloat(h.yaw),
                            type: 'info',
                            text: (idx + 1) + '',
                            cssClass: 'pending-hotspot-marker'
                        });
                    } catch(e) {}
                }
            });
        }

        function loadEditSceneHotspots(sceneId) {
            editExistingHotspots = [];
            editModifiedIds = {};
            editNewImageFiles = {};
            hideEditCard();
            $.get('{{ route("getHotspotsByScene") }}', { scene_id: sceneId }, function(resp) {
                if (resp.success) {
                    editExistingHotspots = resp.hotspots;
                    renderEditTable();
                    updateEditViewerMarkers();
                }
            });
        }

        // Cambio de escena origen en el modal de edición
        $('#editSourceScene').on('change', function() {
            var sceneId = $(this).val();
            if (!sceneId) return;
            editSceneId = sceneId;
            hideEditCard();

            var type = sceneTypeMap[sceneId] || 'equirectangular';
            editIsVideo = (type === 'video');
            var $wrapper = $('#editViewerWrapper');
            $wrapper.show();

            if (editIsVideo) {
                $('#panorama-edit-multi').hide();
                destroyViewer(editViewerMulti); editViewerMulti = null;
                var $videoC = $('#video-edit-multi').show();
                var videoEl = document.getElementById('video-edit-multi-player');
                var progressEl = document.getElementById('video-edit-multi-progress');
                var videoUrl = sceneVideoMap[sceneId];
                if (videoUrl && videoEl) { videoEl.src = videoUrl; videoEl.load(); }
                if (!$videoC.data('editScrub')) {
                    setupVideoScrub($videoC[0], videoEl, progressEl, function(x, y, time) {
                        // Reubicar hotspot activo si hay card abierta
                        var activeId = $('#editCardHotspotId').val();
                        if (activeId && $('#editHotspotCard').is(':visible')) {
                            $('#editCardVideoTime').val(round3(time));
                            $('#editCardPosX').val(round3(x));
                            $('#editCardPosY').val(round3(y));
                            var marker = document.getElementById('video-edit-multi-marker');
                            marker.style.display = 'block';
                            marker.style.left = x + '%';
                            marker.style.top = y + '%';
                        }
                    });
                    $videoC.data('editScrub', true);
                }
            } else {
                $('#video-edit-multi').hide();
                var videoPlayer = document.getElementById('video-edit-multi-player');
                if (videoPlayer) { videoPlayer.pause(); videoPlayer.removeAttribute('src'); }
                $('#panorama-edit-multi').show();
                destroyViewer(editViewerMulti);
                var imgUrl = sceneImageMap[sceneId];
                if (imgUrl) {
                    editViewerMulti = pannellum.viewer('panorama-edit-multi', {
                        type: 'equirectangular', panorama: imgUrl, autoLoad: true, showControls: false
                    });
                    editViewerMulti.on('mousedown', function(ev) {
                        var coords = editViewerMulti.mouseEventToCoords(ev);
                        editDragState.isMouseDown = true;
                        editDragState.hasMoved = false;
                        editDragState.startX = ev.clientX;
                        editDragState.startY = ev.clientY;
                        editDragState.coords = coords;
                        editDragState.clientX = ev.clientX;
                        editDragState.clientY = ev.clientY;
                    });
                    $(document).off('mousemove.editDrag').on('mousemove.editDrag', function(e) {
                        if (!editDragState.isMouseDown) return;
                        var dx = e.clientX - editDragState.startX;
                        var dy = e.clientY - editDragState.startY;
                        if (Math.abs(dx) > 5 || Math.abs(dy) > 5) editDragState.hasMoved = true;
                    });
                    $(document).off('mouseup.editDrag').on('mouseup.editDrag', function(e) {
                        if (!editDragState.isMouseDown) return;
                        editDragState.isMouseDown = false;
                        if (editDragState.hasMoved) return;
                        // Clic (no drag): si hay un hotspot activo, reubicar
                        var activeId = $('#editCardHotspotId').val();
                        if (activeId && $('#editHotspotCard').is(':visible') && editDragState.coords) {
                            $('#editCardYaw').val(round3(editDragState.coords[1]));
                            $('#editCardPitch').val(round3(editDragState.coords[0]));
                        }
                    });
                }
            }

            loadEditSceneHotspots(sceneId);
        });

        // Click en "Editar" en tabla de hotspots existentes
        $(document).on('click', '.edit-existing-hotspot', function() {
            var idx = $(this).data('index');
            var h = editExistingHotspots[idx];
            if (!h) return;
            // Aplicar cambios pendientes si existen
            var merged = $.extend({}, h, editModifiedIds[h.id] || {});

            $('#editCardHotspotId').val(h.id);
            $('#editCardYaw').val(merged.yaw || '');
            $('#editCardPitch').val(merged.pitch || '');
            $('#editCardVideoTime').val(merged.video_time || '');
            $('#editCardPosX').val(merged.pos_x || '');
            $('#editCardPosY').val(merged.pos_y || '');
            $('#editCardTargetYaw').val(merged.target_yaw || '');
            $('#editCardTargetPitch').val(merged.target_pitch || '');
            $('#editCardInfo').val(merged.info || '');
            $('#editCardImageFile').val('');
            $('#editCardImagePreview').attr('src', merged.image_url || defaultHotspotImage);

            // Tipo y destino
            $('#editCardType').val(merged.type || 'info');
            if (merged.type === 'scene') {
                $('#editCardTargetContainer').show();
                $('#editCardTargetScene').val(merged.targetScene || '');
                if (merged.targetScene && scenePanoramaMap[merged.targetScene]) {
                    $('#editCardTargetYawContainer').show();
                    initEditCardTargetViewer(merged.targetScene, merged.target_yaw, merged.target_pitch);
                } else {
                    $('#editCardTargetYawContainer').hide();
                }
            } else {
                $('#editCardTargetContainer').hide();
                $('#editCardTargetYawContainer').hide();
            }

            // Posicionar card cerca del hotspot en el visor
            var $wrapper = $('#editViewerWrapper');
            var wrapperOff = $wrapper.offset();
            var cx = wrapperOff.left + $wrapper.width() / 2;
            var cy = wrapperOff.top + 60;
            showEditCard(cx, cy);

            $('#applyEditBtn').html('<i class="fa fa-check"></i> Aplicar');
        });

        // Cerrar card de edición
        $('#closeEditCard, #cancelEditCard').on('click', hideEditCard);

        // Tipo cambia en card de edición
        $('#editCardType').on('change', function() {
            if ($(this).val() === 'scene') {
                $('#editCardTargetContainer').show();
                var ts = $('#editCardTargetScene').val();
                if (ts && scenePanoramaMap[ts]) {
                    $('#editCardTargetYawContainer').show();
                    initEditCardTargetViewer(ts, '', '');
                }
            } else {
                $('#editCardTargetContainer').hide();
                $('#editCardTargetYawContainer').hide();
                destroyEditCardTargetViewer();
            }
        });

        // Escena destino cambia en card de edición
        $('#editCardTargetScene').on('change', function() {
            var sid = $(this).val();
            if (sid && scenePanoramaMap[sid]) {
                $('#editCardTargetYawContainer').show();
                $('#editCardTargetYaw').val('');
                $('#editCardTargetPitch').val('');
                initEditCardTargetViewer(sid, '', '');
            } else {
                $('#editCardTargetYawContainer').hide();
                destroyEditCardTargetViewer();
            }
        });

        // Preview imagen en card edición
        $('#editCardImageFile').on('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) { $('#editCardImagePreview').attr('src', e.target.result); };
                reader.readAsDataURL(file);
            }
        });

        // Mini visor target para el card de edición
        function destroyEditCardTargetViewer() {
            if (editCardTargetViewerInst) { try { editCardTargetViewerInst.destroy(); } catch(e) {} editCardTargetViewerInst = null; }
        }
        function initEditCardTargetViewer(sceneId, tyaw, tpitch) {
            destroyEditCardTargetViewer();
            if (!scenePanoramaMap[sceneId]) return;
            var initYaw = parseFloat(tyaw) || sceneDefaultYaw[sceneId] || 0;
            var initPitch = parseFloat(tpitch) || sceneDefaultPitch[sceneId] || 0;
            editCardTargetViewerInst = pannellum.viewer('editCardTargetViewer', {
                type: 'equirectangular', panorama: scenePanoramaMap[sceneId],
                autoLoad: true, showControls: false, compass: false, mouseZoom: false, hfov: 100,
                yaw: initYaw, pitch: initPitch
            });
            editCardTargetViewerInst.on('mouseup', function() {
                $('#editCardTargetYaw').val(editCardTargetViewerInst.getYaw().toFixed(2));
                $('#editCardTargetPitch').val(editCardTargetViewerInst.getPitch().toFixed(2));
            });
            editCardTargetViewerInst.on('touchend', function() {
                $('#editCardTargetYaw').val(editCardTargetViewerInst.getYaw().toFixed(2));
                $('#editCardTargetPitch').val(editCardTargetViewerInst.getPitch().toFixed(2));
            });
        }

        // Aplicar cambios del card al hotspot
        $('#applyEditBtn').on('click', function() {
            var hId = $('#editCardHotspotId').val();
            if (!hId) return;

            var type = $('#editCardType').val();
            var info = $('#editCardInfo').val();
            if (!info) { alert('Ingrese información del hotspot'); return; }
            if (type === 'scene' && !$('#editCardTargetScene').val()) { alert('Seleccione escena destino'); return; }

            var changes = {
                type: type,
                info: info,
                targetScene: type === 'scene' ? $('#editCardTargetScene').val() : null,
                target_yaw: $('#editCardTargetYaw').val() || null,
                target_pitch: $('#editCardTargetPitch').val() || null,
                yaw: $('#editCardYaw').val(),
                pitch: $('#editCardPitch').val(),
                video_time: $('#editCardVideoTime').val() || null,
                pos_x: $('#editCardPosX').val() || null,
                pos_y: $('#editCardPosY').val() || null,
            };

            // Guardar imagen nueva si se seleccionó
            var imageFile = $('#editCardImageFile')[0].files[0];
            if (imageFile) {
                editNewImageFiles[hId] = imageFile;
                // Actualizar preview en la tabla
                var reader = new FileReader();
                reader.onload = function(e) {
                    // Actualizar image_url en el array para que la tabla muestre la nueva
                    var idx = editExistingHotspots.findIndex(function(x) { return x.id == hId; });
                    if (idx >= 0) editExistingHotspots[idx].image_url = e.target.result;
                    renderEditTable();
                };
                reader.readAsDataURL(imageFile);
            }

            editModifiedIds[hId] = changes;

            // Actualizar el array local para reflejar cambios en tabla
            var idx = editExistingHotspots.findIndex(function(x) { return x.id == hId; });
            if (idx >= 0) {
                $.extend(editExistingHotspots[idx], changes);
            }

            renderEditTable();
            updateEditViewerMarkers();
            hideEditCard();
        });

        // Eliminar hotspot desde la tabla de edición
        $(document).on('click', '.delete-existing-hotspot', function() {
            var hId = $(this).data('id');
            var idx = $(this).data('index');
            if (!confirm('¿Eliminar este hotspot?')) return;

            $.ajax({
                url: '{{ url("delHotspotAjax") }}/' + hId,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(resp) {
                    if (resp.success) {
                        editExistingHotspots.splice(idx, 1);
                        delete editModifiedIds[hId];
                        delete editNewImageFiles[hId];
                        renderEditTable();
                        updateEditViewerMarkers();
                    } else {
                        alert('Error: ' + (resp.message || 'No se pudo eliminar'));
                    }
                },
                error: function() { alert('Error al eliminar el hotspot'); }
            });
        });

        // Guardar todos los cambios editados
        $('#saveEditedHotspots').on('click', function() {
            var modIds = Object.keys(editModifiedIds);
            if (modIds.length === 0) return;

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('property_id', $('#editPropertyId').val());

            var formIdx = 0;
            modIds.forEach(function(hId) {
                var c = editModifiedIds[hId];
                var h = editExistingHotspots.find(function(x) { return x.id == hId; });
                formData.append('hotspots[' + formIdx + '][id]', hId);
                formData.append('hotspots[' + formIdx + '][sourceScene]', h ? h.sourceScene : editSceneId);
                formData.append('hotspots[' + formIdx + '][type]', c.type || '');
                formData.append('hotspots[' + formIdx + '][info]', c.info || '');
                if (c.targetScene) formData.append('hotspots[' + formIdx + '][targetScene]', c.targetScene);
                if (c.target_yaw) formData.append('hotspots[' + formIdx + '][target_yaw]', c.target_yaw);
                if (c.target_pitch) formData.append('hotspots[' + formIdx + '][target_pitch]', c.target_pitch);
                if (c.yaw) formData.append('hotspots[' + formIdx + '][yaw]', c.yaw);
                if (c.pitch) formData.append('hotspots[' + formIdx + '][pitch]', c.pitch);
                if (c.video_time) formData.append('hotspots[' + formIdx + '][video_time]', c.video_time);
                if (c.pos_x) formData.append('hotspots[' + formIdx + '][pos_x]', c.pos_x);
                if (c.pos_y) formData.append('hotspots[' + formIdx + '][pos_y]', c.pos_y);
                if (editNewImageFiles[hId]) formData.append('images_' + formIdx, editNewImageFiles[hId]);
                formIdx++;
            });

            $.ajax({
                url: '{{ route("updateHotspotBatch") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(resp) {
                    if (resp.success) {
                        alert(resp.message);
                        window.location.href = resp.redirect;
                    } else {
                        alert('Error: ' + resp.message);
                        $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Guardar Cambios (<span id="editSaveCount">' + modIds.length + '</span>)');
                    }
                },
                error: function(xhr) {
                    alert('Error al guardar: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Error desconocido'));
                    $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Guardar Cambios (<span id="editSaveCount">' + modIds.length + '</span>)');
                }
            });
        });

        // Limpiar al cerrar el modal de edición
        $('#editHotspotMulti').on('hidden.bs.modal', function() {
            destroyViewer(editViewerMulti); editViewerMulti = null;
            destroyEditCardTargetViewer();
            hideEditCard();
            var videoEl = document.getElementById('video-edit-multi-player');
            if (videoEl) { videoEl.pause(); videoEl.removeAttribute('src'); }
            editExistingHotspots = [];
            editModifiedIds = {};
            editNewImageFiles = {};
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
