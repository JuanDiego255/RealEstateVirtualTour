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
                        <div id="panorama-hotspot-add" style="width: 100%; height: 500px;"></div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="sourceSceneAdd">Origen de la escena</label>
                                <select class="form-control form-control-lg input-rounded mb-4" id="sourceSceneAdd"
                                    name="sourceScene" required>
                                    <option value="" disabled selected>Seleccione uno</option>
                                    @foreach ($scene as $item)
                                        <option value="{{ $item->id }}">{{ $item->title }}</option>
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

                            <div class="form-group col-md-6">
                                <label for="yawAdd">Yaw</label>
                                <input id="yawAdd" name="yaw"
                                    class="form-control form-control-lg input-rounded mb-4" required type="text"
                                    step="0.1" value="0">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="pitchAdd">Pitch</label>
                                <input id="pitchAdd" name="pitch"
                                    class="form-control form-control-lg input-rounded mb-4" required type="text"
                                    step="0.1" value="0">
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

                            <div id="panorama-hotspot{{ $hotspot->id }}" style="width: 100%; height: 500px;"></div>

                            <input type="hidden" value="{{ $hotspot->id }}" name="id"
                                id="id-{{ $hotspot->id }}">
                            <input type="hidden" name="property_id" value="{{ $id }}">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="sourceScene-{{ $hotspot->id }}"
                                        class="d-flex justify-content-left">Escena Principal</label>
                                    <select class="form-control form-control-lg input-rounded mb-4" name="sourceScene"
                                        id="sourceScene-{{ $hotspot->id }}" required>
                                        <option value="" disabled>Seleccione uno</option>
                                        @foreach ($scene as $scenes)
                                            <option value="{{ $scenes->id }}"
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

                                <div class="form-group col-md-6">
                                    <label for="yaw-{{ $hotspot->id }}"
                                        class="d-flex justify-content-left">Movimiento de rotación horizontal</label>
                                    <input id="yaw-{{ $hotspot->id }}" name="yaw"
                                        class="form-control form-control-lg input-rounded mb-4" required
                                        type="text" step="0.2" value="{{ $hotspot->yaw }}">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="pitch-{{ $hotspot->id }}"
                                        class="d-flex justify-content-left">Movimiento de rotación vertical</label>
                                    <input id="pitch-{{ $hotspot->id }}" name="pitch"
                                        class="form-control form-control-lg input-rounded mb-4" required
                                        type="text" step="0.1" value="{{ $hotspot->pitch }}">
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

        // ---------- Map escena -> imagen ----------
        var sceneImageMap = {
            @foreach ($scene as $scenes)
                {{ $scenes->id }}: "{{ isset($scenes->image) ? route('file', $scenes->image) : url('images/producto-sin-imagen.PNG') }}",
            @endforeach
        };

        // ====== ADD ======
        var viewerAdd = null;
        var panoramaAddEl = document.getElementById("panorama-hotspot-add");
        if (panoramaAddEl) panoramaAddEl.style.display = "none";

        $("#addHotspot").on('shown.bs.modal', function() {
            // opcional: reset visual
            if (panoramaAddEl) panoramaAddEl.style.display = "none";
            destroyViewer(viewerAdd);
            $("#yawAdd").val('0');
            $("#pitchAdd").val('0');
            $("#sourceSceneAdd").val('');
        });

        $("#sourceSceneAdd").on("change", function() {
            var selectedSceneId = $(this).val();
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

            // IMPORTANTE: usar 'mousedown' (no 'click')
            viewerAdd.on('mousedown', function(ev) {
                var coords = viewerAdd.mouseEventToCoords(ev); // [pitch, yaw]
                if (!coords) return;
                var pitch = coords[0];
                var yaw = coords[1];
                console.log('ADD coords:', coords);
                $("#yawAdd").val(round3(yaw));
                $("#pitchAdd").val(round3(pitch));
            });
        });

        // ====== EDIT ======
        // Inicia viewer cuando el modal ya es visible (tiene tamaño)
        $('[id^="editHotspot"]').on('shown.bs.modal', function() {
            var $modal = $(this);
            var idNum = $modal.attr('id').match(/\d+/)[0];

            var containerId = 'panorama-hotspot' + idNum;
            var $sourceSelect = $modal.find('#sourceScene-' + idNum);
            var sceneId = $sourceSelect.val();
            var imageUrl = sceneImageMap[sceneId];

            destroyViewer($modal.data('viewerEdit'));

            var viewerEdit = pannellum.viewer(containerId, {
                type: "equirectangular",
                panorama: imageUrl,
                autoLoad: true
            });
            $modal.data('viewerEdit', viewerEdit);

            viewerEdit.on('mousedown', function(ev) {
                var coords = viewerEdit.mouseEventToCoords(ev);
                if (!coords) return;
                var pitch = coords[0];
                var yaw = coords[1];
                console.log('EDIT coords:', idNum, coords);
                $('#yaw-' + idNum).val(round3(yaw));
                $('#pitch-' + idNum).val(round3(pitch));
            });

            // Cambio de escena dentro del modal
            $sourceSelect.off('change._edit').on('change._edit', function() {
                var newId = $(this).val();
                var newUrl = sceneImageMap[newId];
                if (!newUrl) return;

                try {
                    if (typeof viewerEdit.setPanorama === 'function') {
                        viewerEdit.setPanorama(newUrl);
                    } else {
                        destroyViewer(viewerEdit);
                        viewerEdit = pannellum.viewer(containerId, {
                            type: "equirectangular",
                            panorama: newUrl,
                            autoLoad: true
                        });
                        $modal.data('viewerEdit', viewerEdit);
                        viewerEdit.on('mousedown', function(ev) {
                            var coords = viewerEdit.mouseEventToCoords(ev);
                            if (!coords) return;
                            var pitch = coords[0];
                            var yaw = coords[1];
                            $('#yaw-' + idNum).val(round3(yaw));
                            $('#pitch-' + idNum).val(round3(pitch));
                        });
                    }
                } catch (e) {
                    // Fallback recreando viewer
                    destroyViewer(viewerEdit);
                    viewerEdit = pannellum.viewer(containerId, {
                        type: "equirectangular",
                        panorama: newUrl,
                        autoLoad: true
                    });
                    $modal.data('viewerEdit', viewerEdit);
                    viewerEdit.on('mousedown', function(ev) {
                        var coords = viewerEdit.mouseEventToCoords(ev);
                        if (!coords) return;
                        var pitch = coords[0];
                        var yaw = coords[1];
                        $('#yaw-' + idNum).val(round3(yaw));
                        $('#pitch-' + idNum).val(round3(pitch));
                    });
                }
            });
        });

        // Limpia viewer al cerrar
        $('[id^="editHotspot"]').on('hidden.bs.modal', function() {
            var $modal = $(this);
            destroyViewer($modal.data('viewerEdit'));
            $modal.removeData('viewerEdit');
        });

        // ====== Control de visibilidad de targetScene según tipo ======
        // Para el formulario de agregar
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

        // Para los formularios de edición
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
