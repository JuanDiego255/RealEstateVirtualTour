<!-- Data Scene -->
<div class="container">
    <div class="modal modal-xl fade" id="addScene">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Escena</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('addScene') }}" method="POST" enctype="multipart/form-data">
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

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="title">Escena</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="text"
                                    name="title" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="scene_type_add">Tipo de escena</label>
                                <select class="form-control form-control-lg input-rounded mb-4" id="scene-type-add" name="type">
                                    <option value="equirectangular">Panorama 360</option>
                                    <option value="video">Video Dron Orbital</option>
                                </select>
                            </div>

                            {{-- Campos solo para panorama 360 --}}
                            <div class="form-group col-md-6 panorama-fields-add">
                                <label for="hfov">Campo de Visión Horizontal</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="number"
                                    step="0.1" name="hfov" min="-360" max="360" value="200">
                            </div>

                            <div class="form-group col-md-6 panorama-fields-add">
                                <label for="yaw">Rotación horizontal (Yaw)</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="text"
                                    step="0.1" name="yaw" id="yaw-add" value="0" readonly
                                    style="background-color: #e9ecef; cursor: not-allowed;">
                                <small class="form-text text-muted">Se actualiza al hacer clic en el visor</small>
                            </div>

                            <div class="form-group col-md-6 panorama-fields-add">
                                <label for="pitch">Rotación vertical (Pitch)</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="text"
                                    step="0.1" name="pitch" id="pitch-add" value="0" readonly
                                    style="background-color: #e9ecef; cursor: not-allowed;">
                                <small class="form-text text-muted">Se actualiza al hacer clic en el visor</small>
                            </div>
                        </div>

                        {{-- Imagen 360 (solo panorama) --}}
                        <div class="form-group panorama-fields-add">
                            <label for="image">Imagen 360</label>
                            <img class="card-img-top img-fluid" id="image-preview-add" alt="Image Preview" style="display: none;" />
                            <div class="custom-file">
                                <input type="file" class="form-control-file" id="image-upload-add" name="image"
                                    accept="image/*">
                            </div>
                        </div>

                        <!-- Visor Pannellum para seleccionar yaw/pitch (solo panorama) -->
                        <div class="form-group panorama-fields-add" id="pannellum-container-add" style="display: none;">
                            <label><strong>Seleccione la vista inicial (haga clic donde desea que inicie la escena)</strong></label>
                            <div id="panorama-scene-add" style="width: 100%; height: 400px; border: 2px solid #007bff; border-radius: 5px;"></div>
                            <small class="form-text text-muted">Navegue por la imagen y haga clic en el punto donde desea que el visor quede al frente cuando cargue esta escena.</small>
                        </div>

                        {{-- Video dron (solo video) - Con subida en chunks para archivos grandes --}}
                        <div class="form-group video-fields-add" style="display: none;">
                            <label for="video"><i class="fa fa-video-camera"></i> Video del dron</label>
                            <div class="custom-file">
                                <input type="file" class="form-control-file" id="video-upload-add" name="video"
                                    accept="video/mp4,video/webm,video/ogg">
                            </div>
                            <small class="form-text text-muted">Suba el video del dron que orbita la propiedad (MP4, WebM u OGG). Videos grandes se suben en segundo plano.</small>

                            {{-- Contenedor de progreso de subida --}}
                            <div id="video-upload-progress-add" class="mt-3" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span id="video-upload-status-add" class="text-primary">
                                        <i class="fa fa-spinner fa-spin"></i> Subiendo video...
                                    </span>
                                    <span id="video-upload-percent-add" class="badge badge-info">0%</span>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div id="video-upload-bar-add" class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <small id="video-upload-detail-add" class="text-muted mt-1 d-block">Preparando...</small>
                                <button type="button" id="video-upload-cancel-add" class="btn btn-sm btn-outline-danger mt-2">
                                    <i class="fa fa-times"></i> Cancelar subida
                                </button>
                            </div>

                            {{-- Indicador de video listo --}}
                            <div id="video-ready-add" class="alert alert-success mt-3" style="display: none;">
                                <i class="fa fa-check-circle"></i> Video subido correctamente. Puede guardar la escena.
                            </div>

                            {{-- Campo oculto para el path del video subido por chunks --}}
                            <input type="hidden" id="video-path-add" name="video_path" value="">

                            <video id="video-preview-add" style="display:none; width:100%; max-height:200px; margin-top:10px; border-radius:5px;" muted></video>
                        </div>

                        <div class="form-group">
                            <label for="image">Imagen de referencia (miniatura)</label>
                            <img class="card-img-top img-fluid" id="image-preview" alt="Image Preview" />
                            <div class="custom-file">
                                <input type="file" class="form-control-file" id="image-upload" name="image_ref"
                                    required onchange="previewImage()" accept="image/*">
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
<div class="d-flex justify-content-end">
    <!-- Add Scene -->
    <button type="button" class="btn btn-rounded btn-outline-info mb-3" data-toggle="modal"
        data-target="#addScene">Nueva
        Escena</button>

</div>

<div class="table-responsive" style="width:100%">
    <table class="table table-hover progress-table text-center sceneTable" style="width:100%">
        <thead class="text-uppercase">
            <tr>
                <th scope="col">No.</th>
                <th scope="col">Nombre</th>
                <th scope="col">Imagen</th>
                <th scope="col">Escena Principal</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal -->

@foreach ($scene as $item)
    <!-- Detail Modal -->
    <div class="modal fade" id="detailScene{{ $item['id'] }}">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $item->title }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">

                    <img id="hotspot-image" class="card-img-top img-fluid"
                        src="{{ isset($item->image_ref) ? route('file', $item->image_ref) : (isset($item->image) ? route('file', $item->image) : url('images/producto-sin-imagen.PNG')) }}">
                    <br> <br>
                    <hr>
                    <h5>Información {{ $item->title }}</h5><br>

                    <p class="d-flex justify-content-left"><b> Tipo: </b> {{ $item->type === 'video' ? 'Video Dron Orbital' : 'Panorama 360' }} </p><br>

                    @if($item->type !== 'video')
                    <p class="d-flex justify-content-left">
                        <b> Campo de Visión Horizontal: </b> {{ $item->hfov }}
                    </p><br>

                    <p class="d-flex justify-content-left">
                        <b> Movimiento de rotación horizontal: </b> {{ $item->yaw }}
                    </p><br>

                    <p class="d-flex justify-content-left">
                        <b> Movimiento de rotación vertical: </b> {{ $item->pitch }}
                    </p><br>
                    @endif

                    @if($item->type === 'video' && $item->video)
                    <p class="d-flex justify-content-left"><b> Video: </b> {{ basename($item->video) }} </p><br>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade  modal-xl" id="editModal{{ $item['id'] }}">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar {{ $item->title }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('editScene', ['id' => $item->id]) }}" method="POST"
                        enctype="multipart/form-data">
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
                        <input type="hidden" name="property_id" value="{{ $id }}">

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="title" class="d-flex justify-content-left">Título de la escena</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="text"
                                    name="title" required value="{{ $item->title }}">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="type">Tipo de escena</label>
                                <select class="form-control form-control-lg input-rounded mb-4 scene-type-edit"
                                        name="type" data-scene-id="{{ $item->id }}">
                                    <option value="equirectangular" {{ $item->type !== 'video' ? 'selected' : '' }}>Panorama 360</option>
                                    <option value="video" {{ $item->type === 'video' ? 'selected' : '' }}>Video Dron Orbital</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6 panorama-fields-edit-{{ $item->id }}" style="{{ $item->type === 'video' ? 'display:none' : '' }}">
                                <label for="hfov" class="d-flex justify-content-left">Campo de Visión Horizontal</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="number"
                                    step="0.1" name="hfov" min="-360" max="360"
                                    value="{{ $item->hfov }}">
                            </div>

                            <div class="form-group col-md-6 panorama-fields-edit-{{ $item->id }}" style="{{ $item->type === 'video' ? 'display:none' : '' }}">
                                <label for="yaw" class="d-flex justify-content-left">Rotación horizontal (Yaw)</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="text"
                                    step="0.1" name="yaw" id="yaw-edit-{{ $item->id }}"
                                    value="{{ $item->yaw }}" readonly
                                    style="background-color: #e9ecef; cursor: not-allowed;">
                                <small class="form-text text-muted">Se actualiza al hacer clic en el visor</small>
                            </div>

                            <div class="form-group col-md-6 panorama-fields-edit-{{ $item->id }}" style="{{ $item->type === 'video' ? 'display:none' : '' }}">
                                <label for="pitch" class="d-flex justify-content-left">Rotación vertical (Pitch)</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="text"
                                    step="0.1" name="pitch" id="pitch-edit-{{ $item->id }}"
                                    value="{{ $item->pitch }}" readonly
                                    style="background-color: #e9ecef; cursor: not-allowed;">
                                <small class="form-text text-muted">Se actualiza al hacer clic en el visor</small>
                            </div>
                        </div>

                        <!-- Visor Pannellum para seleccionar yaw/pitch en edición (solo panorama) -->
                        <div class="form-group panorama-fields-edit-{{ $item->id }}" style="{{ $item->type === 'video' ? 'display:none' : '' }}">
                            <label><strong>Seleccione la vista inicial (haga clic donde desea que inicie la escena)</strong></label>
                            <div id="panorama-scene-edit-{{ $item->id }}"
                                 style="width: 100%; height: 400px; border: 2px solid #007bff; border-radius: 5px;"
                                 data-image="{{ isset($item->image) ? route('file', $item->image) : '' }}"
                                 data-yaw="{{ $item->yaw }}"
                                 data-pitch="{{ $item->pitch }}"></div>
                            <small class="form-text text-muted">Navegue por la imagen y haga clic en el punto donde desea que el visor quede al frente cuando cargue esta escena.</small>
                        </div>

                        <div class="form-group panorama-fields-edit-{{ $item->id }}" style="{{ $item->type === 'video' ? 'display:none' : '' }}">
                            <label for="image" class="d-flex justify-content-left">Imagen 360 (dejar vacío para mantener la actual)</label>
                            <img class="card-img-top img-fluid w-25" id="image-preview-edit-{{ $item->id }}"
                                src="{{ isset($item->image) ? route('file', $item->image) : url('images/producto-sin-imagen.PNG') }}">
                            <div class="custom-file">
                                <input type="file" class="form-control-file image-upload-edit"
                                    name="image" data-scene-id="{{ $item->id }}"
                                    accept="image/*">
                            </div>
                        </div>

                        {{-- Video dron (solo video) --}}
                        <div class="form-group video-fields-edit-{{ $item->id }}" style="{{ $item->type !== 'video' ? 'display:none' : '' }}">
                            <label for="video"><i class="fa fa-video-camera"></i> Video del dron (dejar vacío para mantener el actual)</label>
                            @if($item->video)
                                <div class="mb-2">
                                    <small class="text-muted">Video actual: {{ basename($item->video) }}</small>
                                </div>
                            @endif
                            <div class="custom-file">
                                <input type="file" class="form-control-file" name="video"
                                    accept="video/mp4,video/webm,video/ogg">
                            </div>
                            <small class="form-text text-muted">Suba el video del dron que orbita la propiedad (MP4, WebM u OGG).</small>
                        </div>
                        <div class="form-group">
                            <label for="image" class=" d-flex justify-content-left">Imagen de referencia (dejar vacío para mantener la actual)</label>
                            <img class="card-img-top img-fluid w-25"
                                src="{{ isset($item->image_ref) ? route('file', $item->image_ref) : url('images/producto-sin-imagen.PNG') }}">
                            <div class="custom-file">
                                <input type="file" class="form-control-file" name="image_ref"
                                    accept="image/*">
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

    <!-- Delete Modal -->
    <div id="deleteModal{{ $item['id'] }}" class="modal fade">
        <div class="modal-dialog modal-dialog-centered modal-confirm">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <div class="icon-box">
                        <i class="fa fa-times-circle"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <p class="text-center">
                        ¿Está seguro de que desea eliminar estos datos?</p>
                    <form method="POST" action="{{ route('delScene', ['id' => $item->id]) }}">
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

{{-- JavaScript para selección dinámica de Yaw/Pitch en escenas --}}
<script>
    $(document).ready(function() {
        // ---------- Utilidades ----------
        function round3(n) {
            return Number.parseFloat(n).toFixed(3);
        }

        function destroyViewer(v) {
            try {
                v && v.destroy && v.destroy();
            } catch (e) {}
        }

        // ====== TOGGLE TIPO DE ESCENA (Panorama vs Video) ======
        $('#scene-type-add').on('change', function() {
            var isVideo = $(this).val() === 'video';
            if (isVideo) {
                $('.panorama-fields-add').hide();
                $('.video-fields-add').show();
            } else {
                $('.panorama-fields-add').show();
                $('.video-fields-add').hide();
            }
        });

        // ====== SUBIDA DE VIDEO EN CHUNKS (para archivos grandes) ======
        var videoUploadState = {
            uploadId: null,
            aborted: false,
            uploading: false
        };

        // Tamaño de cada chunk: 2MB (seguro para la mayoría de servidores)
        var CHUNK_SIZE = 2 * 1024 * 1024;

        function generateUploadId() {
            return 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function updateUploadProgress(percent, detail) {
            $('#video-upload-bar-add').css('width', percent + '%').attr('aria-valuenow', percent);
            $('#video-upload-percent-add').text(percent + '%');
            if (detail) {
                $('#video-upload-detail-add').text(detail);
            }
        }

        async function uploadVideoInChunks(file) {
            var totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            var uploadId = generateUploadId();
            videoUploadState.uploadId = uploadId;
            videoUploadState.aborted = false;
            videoUploadState.uploading = true;

            // Mostrar UI de progreso
            $('#video-upload-progress-add').show();
            $('#video-ready-add').hide();
            $('#video-upload-status-add').html('<i class="fa fa-spinner fa-spin"></i> Subiendo video...');
            updateUploadProgress(0, 'Iniciando subida de ' + formatBytes(file.size) + ' en ' + totalChunks + ' partes...');

            var csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();

            // Subir cada chunk
            for (var i = 0; i < totalChunks; i++) {
                if (videoUploadState.aborted) {
                    console.log('Upload abortado por usuario');
                    return { success: false, aborted: true };
                }

                var start = i * CHUNK_SIZE;
                var end = Math.min(start + CHUNK_SIZE, file.size);
                var chunk = file.slice(start, end);

                var formData = new FormData();
                formData.append('chunk', chunk);
                formData.append('chunkIndex', i);
                formData.append('totalChunks', totalChunks);
                formData.append('uploadId', uploadId);
                formData.append('fileName', file.name);
                formData.append('_token', csrfToken);

                try {
                    var response = await $.ajax({
                        url: '{{ url("/upload-video-chunk") }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        timeout: 120000 // 2 minutos por chunk
                    });

                    if (response.success) {
                        var percent = Math.round(((i + 1) / totalChunks) * 100);
                        updateUploadProgress(percent, 'Parte ' + (i + 1) + ' de ' + totalChunks + ' completada');
                    } else {
                        throw new Error(response.error || 'Error desconocido');
                    }
                } catch (error) {
                    console.error('Error subiendo chunk ' + i, error);
                    $('#video-upload-status-add').html('<i class="fa fa-exclamation-triangle text-danger"></i> Error al subir');
                    updateUploadProgress(0, 'Error: ' + (error.responseJSON?.message || error.message || 'Fallo en la subida'));
                    videoUploadState.uploading = false;
                    return { success: false, error: error };
                }
            }

            // Todos los chunks subidos, ahora ensamblar
            $('#video-upload-status-add').html('<i class="fa fa-spinner fa-spin"></i> Ensamblando video...');
            updateUploadProgress(100, 'Ensamblando archivo final...');

            try {
                var completeResponse = await $.ajax({
                    url: '{{ route("completeVideoUpload") }}',
                    type: 'POST',
                    data: {
                        uploadId: uploadId,
                        fileName: file.name,
                        totalChunks: totalChunks,
                        _token: csrfToken
                    },
                    timeout: 300000 // 5 minutos para ensamblar
                });

                if (completeResponse.success) {
                    // Éxito: guardar el path del video
                    $('#video-path-add').val(completeResponse.videoPath);

                    // Actualizar UI
                    $('#video-upload-progress-add').hide();
                    $('#video-ready-add').show();
                    $('#video-upload-status-add').html('<i class="fa fa-check text-success"></i> Video listo');

                    // IMPORTANTE: Limpiar el input de archivo para evitar que el form envíe el video
                    // (el video ya está en el servidor vía chunks, solo necesitamos video_path)
                    // La vista previa se mantiene porque usa un Object URL separado
                    $('#video-upload-add').val('');
                    videoUploadState.uploading = false;

                    return { success: true, videoPath: completeResponse.videoPath };
                } else {
                    throw new Error(completeResponse.error || 'Error al ensamblar');
                }
            } catch (error) {
                console.error('Error ensamblando video', error);
                $('#video-upload-status-add').html('<i class="fa fa-exclamation-triangle text-danger"></i> Error al ensamblar');
                updateUploadProgress(0, 'Error: ' + (error.responseJSON?.error || error.message || 'Fallo al ensamblar'));
                videoUploadState.uploading = false;
                return { success: false, error: error };
            }
        }

        // Evento cuando se selecciona un video
        $('#video-upload-add').on('change', function(e) {
            var file = e.target.files[0];
            if (!file) return;

            // Mostrar vista previa
            var videoUrl = URL.createObjectURL(file);
            var $preview = $('#video-preview-add');
            $preview.attr('src', videoUrl).show();
            $preview[0].currentTime = 0;

            // Si el archivo es mayor a 5MB, usar subida en chunks
            var THRESHOLD = 5 * 1024 * 1024; // 5MB
            if (file.size > THRESHOLD) {
                console.log('Video grande detectado (' + formatBytes(file.size) + '), usando subida en chunks');

                // Iniciar subida en segundo plano
                uploadVideoInChunks(file).then(function(result) {
                    if (result.success) {
                        console.log('Video subido exitosamente:', result.videoPath);
                    } else if (result.aborted) {
                        console.log('Subida cancelada por el usuario');
                    } else {
                        console.error('Error en subida:', result.error);
                    }
                });
            } else {
                // Video pequeño: subida tradicional (el form lo maneja)
                console.log('Video pequeño (' + formatBytes(file.size) + '), subida tradicional');
                $('#video-upload-progress-add').hide();
                $('#video-ready-add').hide();
                $('#video-path-add').val(''); // Limpiar por si había uno anterior
            }
        });

        // Cancelar subida en progreso
        $('#video-upload-cancel-add').on('click', function() {
            if (videoUploadState.uploading) {
                videoUploadState.aborted = true;

                // Notificar al servidor para limpiar chunks parciales
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
                $.post('{{ route("cancelVideoUpload") }}', {
                    uploadId: videoUploadState.uploadId,
                    _token: csrfToken
                });

                // Resetear UI
                $('#video-upload-progress-add').hide();
                $('#video-upload-status-add').html('<i class="fa fa-times text-warning"></i> Subida cancelada');
                $('#video-path-add').val('');
                videoUploadState.uploading = false;
            }
        });

        // Limpiar estado cuando se cierra el modal
        $('#addScene').on('hidden.bs.modal', function() {
            if (videoUploadState.uploading) {
                videoUploadState.aborted = true;
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
                $.post('{{ route("cancelVideoUpload") }}', {
                    uploadId: videoUploadState.uploadId,
                    _token: csrfToken
                });
            }
            videoUploadState = { uploadId: null, aborted: false, uploading: false };
            $('#video-upload-progress-add').hide();
            $('#video-ready-add').hide();
            $('#video-path-add').val('');
        });

        // ====== FORMULARIO DE AGREGAR ESCENA ======
        var viewerAdd = null;
        var panoramaAddEl = document.getElementById("panorama-scene-add");
        var pannellumContainerAdd = document.getElementById("pannellum-container-add");

        // Cuando se selecciona una imagen 360
        $("#image-upload-add").on("change", function(e) {
            var file = e.target.files[0];
            if (!file) return;

            // Crear URL temporal para la imagen
            var imageUrl = URL.createObjectURL(file);

            // Mostrar el contenedor del visor
            if (pannellumContainerAdd) {
                pannellumContainerAdd.style.display = "block";
            }

            // Destruir visor anterior si existe
            destroyViewer(viewerAdd);

            // Crear nuevo visor Pannellum
            viewerAdd = pannellum.viewer('panorama-scene-add', {
                type: "equirectangular",
                panorama: imageUrl,
                autoLoad: true,
                showControls: true,
                mouseZoom: true,
                draggable: true,
                yaw: 0,
                pitch: 0
            });

            // Resetear campos yaw y pitch
            $("#yaw-add").val('0');
            $("#pitch-add").val('0');

            // Evento de click para seleccionar yaw/pitch
            viewerAdd.on('mousedown', function(ev) {
                var coords = viewerAdd.mouseEventToCoords(ev);
                if (!coords) return;
                var pitch = coords[0];
                var yaw = coords[1];
                console.log('ADD Scene coords:', coords);
                $("#yaw-add").val(round3(yaw));
                $("#pitch-add").val(round3(pitch));
            });
        });

        // Limpiar cuando se cierra el modal de agregar
        $("#addScene").on('hidden.bs.modal', function() {
            destroyViewer(viewerAdd);
            viewerAdd = null;
            if (pannellumContainerAdd) {
                pannellumContainerAdd.style.display = "none";
            }
            $("#yaw-add").val('0');
            $("#pitch-add").val('0');
            $("#image-upload-add").val('');
        });

        // ====== TOGGLE TIPO DE ESCENA EN EDICIÓN ======
        $(document).on('change', '.scene-type-edit', function() {
            var sceneId = $(this).data('scene-id');
            var isVideo = $(this).val() === 'video';
            if (isVideo) {
                $('.panorama-fields-edit-' + sceneId).hide();
                $('.video-fields-edit-' + sceneId).show();
            } else {
                $('.panorama-fields-edit-' + sceneId).show();
                $('.video-fields-edit-' + sceneId).hide();
            }
        });

        // ====== MODALES DE EDICIÓN DE ESCENA ======
        // Inicializar visor cuando se abre el modal de edición
        $('[id^="editModal"]').on('shown.bs.modal', function() {
            var $modal = $(this);
            var idNum = $modal.attr('id').match(/\d+/)[0];
            var containerId = 'panorama-scene-edit-' + idNum;
            var $container = $('#' + containerId);

            // No inicializar Pannellum si es escena de video
            var sceneTypeSelect = $modal.find('.scene-type-edit');
            if (sceneTypeSelect.length && sceneTypeSelect.val() === 'video') {
                return;
            }

            var imageUrl = $container.data('image');
            var initialYaw = parseFloat($container.data('yaw')) || 0;
            var initialPitch = parseFloat($container.data('pitch')) || 0;

            if (!imageUrl) {
                console.log('No hay imagen para la escena', idNum);
                return;
            }

            destroyViewer($modal.data('viewerEdit'));

            var viewerEdit = pannellum.viewer(containerId, {
                type: "equirectangular",
                panorama: imageUrl,
                autoLoad: true,
                showControls: true,
                mouseZoom: true,
                draggable: true,
                yaw: initialYaw,
                pitch: initialPitch
            });
            $modal.data('viewerEdit', viewerEdit);

            // Evento de click para seleccionar yaw/pitch
            viewerEdit.on('mousedown', function(ev) {
                var coords = viewerEdit.mouseEventToCoords(ev);
                if (!coords) return;
                var pitch = coords[0];
                var yaw = coords[1];
                console.log('EDIT Scene coords:', idNum, coords);
                $('#yaw-edit-' + idNum).val(round3(yaw));
                $('#pitch-edit-' + idNum).val(round3(pitch));
            });
        });

        // Limpiar visor cuando se cierra el modal de edición
        $('[id^="editModal"]').on('hidden.bs.modal', function() {
            var $modal = $(this);
            destroyViewer($modal.data('viewerEdit'));
            $modal.removeData('viewerEdit');
        });

        // Cuando se selecciona una nueva imagen en el modal de edición
        $(document).on('change', '.image-upload-edit', function(e) {
            var file = e.target.files[0];
            if (!file) return;

            var sceneId = $(this).data('scene-id');
            var imageUrl = URL.createObjectURL(file);
            var containerId = 'panorama-scene-edit-' + sceneId;
            var $modal = $('#editModal' + sceneId);

            // Actualizar preview de imagen
            $('#image-preview-edit-' + sceneId).attr('src', imageUrl);

            // Actualizar el data-image del contenedor
            $('#' + containerId).data('image', imageUrl);

            // Destruir y recrear visor con nueva imagen
            destroyViewer($modal.data('viewerEdit'));

            var viewerEdit = pannellum.viewer(containerId, {
                type: "equirectangular",
                panorama: imageUrl,
                autoLoad: true,
                showControls: true,
                mouseZoom: true,
                draggable: true,
                yaw: 0,
                pitch: 0
            });
            $modal.data('viewerEdit', viewerEdit);

            // Resetear campos yaw y pitch cuando cambia la imagen
            $('#yaw-edit-' + sceneId).val('0');
            $('#pitch-edit-' + sceneId).val('0');

            // Re-agregar evento de click
            viewerEdit.on('mousedown', function(ev) {
                var coords = viewerEdit.mouseEventToCoords(ev);
                if (!coords) return;
                var pitch = coords[0];
                var yaw = coords[1];
                console.log('EDIT Scene (new image) coords:', sceneId, coords);
                $('#yaw-edit-' + sceneId).val(round3(yaw));
                $('#pitch-edit-' + sceneId).val(round3(pitch));
            });
        });
    });
</script>
