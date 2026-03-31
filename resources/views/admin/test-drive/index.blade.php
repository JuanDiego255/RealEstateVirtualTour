@extends('admin.main')

@section('title', 'Test Drive Virtual - ' . $vehicle->brand . ' ' . $vehicle->model)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-light p-3 rounded">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('property') }}">Publicaciones</a></li>
                    <li class="breadcrumb-item active">Test Drive Virtual</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fa fa-car fa-2x text-dark"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h2 class="mb-1">Test Drive Virtual</h2>
                            <h4 class="mb-0 text-warning">{{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->year }}</h4>
                            <p class="mb-0 mt-2 opacity-75">Configura la experiencia inmersiva para tus clientes</p>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.test-drive.preview', $vehicle->id) }}" class="btn btn-warning btn-lg" target="_blank">
                                <i class="fa fa-eye mr-2"></i>Vista Previa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <div class="row">
        <!-- Columna izquierda: Videos -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-video-camera mr-2"></i>Videos del Test Drive</h5>
                    <button class="btn btn-light btn-sm" data-toggle="modal" data-target="#addVideoModal">
                        <i class="fa fa-plus mr-1"></i>Agregar Video
                    </button>
                </div>
                <div class="card-body">
                    @if($videos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" id="videosTable">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50"><i class="fa fa-arrows"></i></th>
                                    <th width="120">Preview</th>
                                    <th>Titulo</th>
                                    <th>Tipo</th>
                                    <th width="80">Duracion</th>
                                    <th width="80">Estado</th>
                                    <th width="150">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="sortableVideos">
                                @foreach($videos as $video)
                                <tr data-id="{{ $video->id }}">
                                    <td class="drag-handle text-center" style="cursor: grab;">
                                        <i class="fa fa-bars text-muted"></i>
                                    </td>
                                    <td>
                                        <div class="video-preview position-relative" style="width: 100px; height: 56px; background: #000; border-radius: 5px; overflow: hidden;">
                                            @if($video->thumbnail)
                                            <img src="{{ route('file', $video->thumbnail) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                            <div class="d-flex align-items-center justify-content-center h-100">
                                                <i class="fa fa-film text-secondary"></i>
                                            </div>
                                            @endif
                                            <div class="position-absolute" style="bottom: 2px; right: 2px; background: rgba(0,0,0,0.7); color: #fff; font-size: 10px; padding: 1px 4px; border-radius: 3px;">
                                                {{ $video->formatted_duration }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $video->title }}</strong>
                                        @if($video->engine_audio)
                                        <br><small class="text-success"><i class="fa fa-volume-up"></i> Con audio de motor</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $video->video_type == 'engine' ? 'danger' : ($video->video_type == 'interior' ? 'info' : 'secondary') }}">
                                            {{ $videoTypes[$video->video_type] ?? $video->video_type }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $video->formatted_duration }}</td>
                                    <td class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input video-status" id="status{{ $video->id }}" {{ $video->status ? 'checked' : '' }} data-video-id="{{ $video->id }}">
                                            <label class="custom-control-label" for="status{{ $video->id }}"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editVideo({{ $video->id }})" title="Editar">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <a href="{{ route('file', $video->video_path) }}" class="btn btn-outline-info" target="_blank" title="Ver">
                                                <i class="fa fa-play"></i>
                                            </a>
                                            <button class="btn btn-outline-danger" onclick="deleteVideo({{ $video->id }}, '{{ $video->title }}')" title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fa fa-video-camera fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay videos de Test Drive</h5>
                        <p class="text-muted">Agrega videos para crear una experiencia inmersiva para tus clientes</p>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addVideoModal">
                            <i class="fa fa-plus mr-2"></i>Agregar Primer Video
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Tips -->
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fa fa-lightbulb-o mr-2"></i>Tips para Videos de Impacto</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6><i class="fa fa-film text-primary mr-2"></i>Exterior</h6>
                            <small class="text-muted">Graba el vehiculo en movimiento, de frente y en curvas. Muestra la presencia del auto.</small>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fa fa-car text-success mr-2"></i>Interior POV</h6>
                            <small class="text-muted">Vista desde el asiento del conductor. Graba el encendido, tablero, y conduccion.</small>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fa fa-cog text-danger mr-2"></i>Motor</h6>
                            <small class="text-muted">Muestra el motor arrancando. El sonido real es clave para la experiencia.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Audio del Motor -->
        <div class="col-lg-4">
            <!-- Audio del Motor Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fa fa-volume-up mr-2"></i>Audio del Motor</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">
                        Sube los audios reales del motor para la experiencia inmersiva. Estos se reproduciran cuando el usuario "encienda" el vehiculo.
                    </p>

                    <form action="{{ route('admin.test-drive.engine-audio', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Sonido de Arranque -->
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fa fa-power-off text-warning mr-1"></i>Sonido de Arranque
                            </label>
                            <small class="text-muted d-block mb-2">Audio corto (2-4 segundos) del motor encendiendo</small>

                            @if($vehicle->engine_startup_audio)
                            <div class="alert alert-success py-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fa fa-check-circle mr-2"></i>
                                    <audio controls style="height: 30px; vertical-align: middle;">
                                        <source src="{{ route('file', $vehicle->engine_startup_audio) }}" type="audio/mpeg">
                                    </audio>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEngineAudio('engine_startup_audio')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                            @endif

                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="engine_startup_audio" name="engine_startup_audio" accept="audio/*">
                                <label class="custom-file-label" for="engine_startup_audio">Seleccionar audio...</label>
                            </div>
                        </div>

                        <!-- Sonido en Idle -->
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fa fa-circle text-success mr-1"></i>Sonido en Ralenti (Idle)
                            </label>
                            <small class="text-muted d-block mb-2">Audio en loop del motor encendido sin acelerar</small>

                            @if($vehicle->engine_idle_audio)
                            <div class="alert alert-success py-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fa fa-check-circle mr-2"></i>
                                    <audio controls style="height: 30px; vertical-align: middle;">
                                        <source src="{{ route('file', $vehicle->engine_idle_audio) }}" type="audio/mpeg">
                                    </audio>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEngineAudio('engine_idle_audio')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                            @endif

                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="engine_idle_audio" name="engine_idle_audio" accept="audio/*">
                                <label class="custom-file-label" for="engine_idle_audio">Seleccionar audio...</label>
                            </div>
                        </div>

                        <!-- Sonido Acelerando -->
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fa fa-tachometer text-danger mr-1"></i>Sonido Acelerando
                            </label>
                            <small class="text-muted d-block mb-2">Audio del motor subiendo revoluciones</small>

                            @if($vehicle->engine_rev_audio)
                            <div class="alert alert-success py-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fa fa-check-circle mr-2"></i>
                                    <audio controls style="height: 30px; vertical-align: middle;">
                                        <source src="{{ route('file', $vehicle->engine_rev_audio) }}" type="audio/mpeg">
                                    </audio>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEngineAudio('engine_rev_audio')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                            @endif

                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="engine_rev_audio" name="engine_rev_audio" accept="audio/*">
                                <label class="custom-file-label" for="engine_rev_audio">Seleccionar audio...</label>
                            </div>
                        </div>

                        <hr>

                        <!-- Video POV Interior -->
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fa fa-eye text-info mr-1"></i>Video POV Interior
                            </label>
                            <small class="text-muted d-block mb-2">Video desde el asiento del conductor (primera persona)</small>

                            @if($vehicle->interior_pov_video)
                            <div class="alert alert-success py-2 mb-2">
                                <i class="fa fa-check-circle mr-2"></i>Video POV cargado
                                <a href="{{ route('file', $vehicle->interior_pov_video) }}" target="_blank" class="ml-2">
                                    <i class="fa fa-external-link"></i> Ver
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger float-right" onclick="deleteEngineAudio('interior_pov_video')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                            @endif

                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="interior_pov_video" accept="video/*">
                                <label class="custom-file-label" for="interior_pov_video">Seleccionar video...</label>
                            </div>
                            <input type="hidden" name="interior_pov_video" id="interior_pov_video_path" value="">

                            <!-- Barra de progreso POV -->
                            <div id="pov-upload-progress" class="mt-3" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span id="pov-upload-status" class="text-primary">
                                        <i class="fa fa-spinner fa-spin mr-1"></i>Subiendo video...
                                    </span>
                                    <span id="pov-upload-percent" class="font-weight-bold">0%</span>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div id="pov-upload-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                         role="progressbar" style="width: 0%"></div>
                                </div>
                                <small id="pov-upload-details" class="text-muted mt-1 d-block"></small>
                            </div>

                            <!-- Estado de video POV subido -->
                            <div id="pov-upload-complete" class="alert alert-success mt-3" style="display: none;">
                                <i class="fa fa-check-circle mr-2"></i>
                                <span id="pov-upload-complete-text">Video POV subido correctamente</span>
                            </div>
                        </div>

                        <!-- Imagen Destacada -->
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fa fa-image text-primary mr-1"></i>Imagen Interior (Fondo)
                            </label>
                            <small class="text-muted d-block mb-2">Imagen del interior para mostrar cuando no hay video</small>

                            @if($vehicle->featured_image)
                            <div class="mb-2">
                                <img src="{{ route('file', $vehicle->featured_image) }}" class="img-fluid rounded" style="max-height: 100px;">
                                <button type="button" class="btn btn-sm btn-outline-danger ml-2" onclick="deleteEngineAudio('featured_image')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                            @endif

                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="featured_image" name="featured_image" accept="image/*">
                                <label class="custom-file-label" for="featured_image">Seleccionar imagen...</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-danger btn-block mt-4">
                            <i class="fa fa-save mr-2"></i>Guardar Configuracion de Audio
                        </button>
                    </form>
                </div>
            </div>

            <!-- Estado de la Experiencia -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="fa fa-check-circle mr-2"></i>Estado de la Experiencia</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-video-camera mr-2"></i>Videos</span>
                            @if($videos->count() > 0)
                            <span class="badge badge-success badge-pill">{{ $videos->count() }} video(s)</span>
                            @else
                            <span class="badge badge-warning badge-pill">Sin videos</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-power-off mr-2"></i>Audio Arranque</span>
                            @if($vehicle->engine_startup_audio)
                            <span class="badge badge-success badge-pill"><i class="fa fa-check"></i></span>
                            @else
                            <span class="badge badge-secondary badge-pill">-</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-circle mr-2"></i>Audio Idle</span>
                            @if($vehicle->engine_idle_audio)
                            <span class="badge badge-success badge-pill"><i class="fa fa-check"></i></span>
                            @else
                            <span class="badge badge-secondary badge-pill">-</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-tachometer mr-2"></i>Audio Rev</span>
                            @if($vehicle->engine_rev_audio)
                            <span class="badge badge-success badge-pill"><i class="fa fa-check"></i></span>
                            @else
                            <span class="badge badge-secondary badge-pill">-</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-eye mr-2"></i>Video POV</span>
                            @if($vehicle->interior_pov_video)
                            <span class="badge badge-success badge-pill"><i class="fa fa-check"></i></span>
                            @else
                            <span class="badge badge-secondary badge-pill">-</span>
                            @endif
                        </li>
                    </ul>
                </div>
                <div class="card-footer">
                    @php
                        $readiness = 0;
                        if ($videos->count() > 0) $readiness += 40;
                        if ($vehicle->engine_startup_audio) $readiness += 15;
                        if ($vehicle->engine_idle_audio) $readiness += 15;
                        if ($vehicle->engine_rev_audio) $readiness += 15;
                        if ($vehicle->interior_pov_video) $readiness += 15;
                    @endphp
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-{{ $readiness >= 70 ? 'success' : ($readiness >= 40 ? 'warning' : 'danger') }}"
                             style="width: {{ $readiness }}%;">
                            {{ $readiness }}% Listo
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2 text-center">
                        @if($readiness >= 70)
                        <i class="fa fa-star text-warning"></i> Experiencia completa
                        @elseif($readiness >= 40)
                        <i class="fa fa-exclamation-triangle text-warning"></i> Experiencia basica
                        @else
                        <i class="fa fa-times-circle text-danger"></i> Agregar mas contenido
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Agregar Video -->
<div class="modal fade" id="addVideoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-plus mr-2"></i>Agregar Video de Test Drive</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="addVideoForm" action="{{ route('admin.test-drive.store-video', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="video_path" id="video_path_add" value="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Titulo <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="add_title" class="form-control" required placeholder="Ej: Test Drive Exterior">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Video <span class="text-danger">*</span></label>
                                <select name="video_type" id="add_video_type" class="form-control" required>
                                    @foreach($videoTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripcion</label>
                        <textarea name="description" id="add_description" class="form-control" rows="2" placeholder="Descripcion breve del video..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Video <span class="text-danger">*</span></label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="video_file_add" accept="video/*">
                                    <label class="custom-file-label" for="video_file_add">Seleccionar video...</label>
                                </div>
                                <small class="text-muted">Formatos: MP4, WebM. Sin limite de tamano (subida por chunks)</small>

                                <!-- Barra de progreso de subida -->
                                <div id="video-upload-progress-add" class="mt-3" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span id="video-upload-status-add" class="text-primary">
                                            <i class="fa fa-spinner fa-spin mr-1"></i>Subiendo video...
                                        </span>
                                        <span id="video-upload-percent-add" class="font-weight-bold">0%</span>
                                    </div>
                                    <div class="progress" style="height: 20px;">
                                        <div id="video-upload-bar-add" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                             role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small id="video-upload-details-add" class="text-muted mt-1 d-block"></small>
                                </div>

                                <!-- Estado de video subido -->
                                <div id="video-upload-complete-add" class="alert alert-success mt-3" style="display: none;">
                                    <i class="fa fa-check-circle mr-2"></i>
                                    <span id="video-upload-complete-text-add">Video subido correctamente</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Miniatura (Thumbnail)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="thumbnail" accept="image/*">
                                    <label class="custom-file-label">Seleccionar imagen...</label>
                                </div>
                                <small class="text-muted">Si no subes una, se generara automaticamente</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Audio del Motor (para este video)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="engine_audio" accept="audio/*">
                            <label class="custom-file-label">Seleccionar audio...</label>
                        </div>
                        <small class="text-muted">Audio sincronizado con el video. Formatos: MP3, WAV, OGG</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="autoplay" name="autoplay" value="1">
                                <label class="custom-control-label" for="autoplay">Reproducir automaticamente</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="loop" name="loop" value="1">
                                <label class="custom-control-label" for="loop">Reproducir en loop</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelAddVideoBtn">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="submitAddVideoBtn" disabled>
                        <i class="fa fa-save mr-2"></i>Guardar Video
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Video -->
<div class="modal fade" id="editVideoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-pencil mr-2"></i>Editar Video</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editVideoForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Titulo <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="edit_title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Video <span class="text-danger">*</span></label>
                                <select name="video_type" id="edit_video_type" class="form-control" required>
                                    @foreach($videoTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripcion</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Reemplazar Video</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="video" accept="video/*">
                                    <label class="custom-file-label">Seleccionar nuevo video...</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Reemplazar Miniatura</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="thumbnail" accept="image/*">
                                    <label class="custom-file-label">Seleccionar imagen...</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Reemplazar Audio del Motor</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="engine_audio" accept="audio/*">
                            <label class="custom-file-label">Seleccionar audio...</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="edit_autoplay" name="autoplay" value="1">
                                <label class="custom-control-label" for="edit_autoplay">Reproducir automaticamente</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="edit_loop" name="loop" value="1">
                                <label class="custom-control-label" for="edit_loop">Reproducir en loop</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fa fa-save mr-2"></i>Actualizar Video
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Eliminar -->
<div class="modal fade" id="deleteVideoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa fa-trash mr-2"></i>Eliminar Video</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Estas seguro de eliminar el video "<strong id="deleteVideoTitle"></strong>"?</p>
                <p class="text-danger mb-0"><small>Esta accion no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <form id="deleteVideoForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash mr-2"></i>Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const vehicleId = {{ $vehicle->id }};
    const videos = @json($videos);

    // Custom file input labels
    document.querySelectorAll('.custom-file-input').forEach(input => {
        input.addEventListener('change', function(e) {
            const fileName = this.files[0]?.name || 'Seleccionar archivo...';
            this.nextElementSibling.textContent = fileName;
        });
    });

    // Sortable videos
    const sortable = document.getElementById('sortableVideos');
    if (sortable) {
        new Sortable(sortable, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function() {
                const order = Array.from(sortable.querySelectorAll('tr')).map(tr => tr.dataset.id);
                fetch(`/admin/test-drive/${vehicleId}/reorder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ order })
                });
            }
        });
    }

    // Toggle video status
    document.querySelectorAll('.video-status').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const videoId = this.dataset.videoId;
            fetch(`/admin/test-drive/${vehicleId}/video/${videoId}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
        });
    });

    // Edit video
    function editVideo(videoId) {
        const video = videos.find(v => v.id === videoId);
        if (!video) return;

        document.getElementById('edit_title').value = video.title;
        document.getElementById('edit_description').value = video.description || '';
        document.getElementById('edit_video_type').value = video.video_type;
        document.getElementById('edit_autoplay').checked = video.autoplay;
        document.getElementById('edit_loop').checked = video.loop;

        document.getElementById('editVideoForm').action = `/admin/test-drive/${vehicleId}/video/${videoId}`;
        $('#editVideoModal').modal('show');
    }

    // Delete video
    function deleteVideo(videoId, title) {
        document.getElementById('deleteVideoTitle').textContent = title;
        document.getElementById('deleteVideoForm').action = `/admin/test-drive/${vehicleId}/video/${videoId}`;
        $('#deleteVideoModal').modal('show');
    }

    // Delete engine audio
    function deleteEngineAudio(field) {
        if (!confirm('Eliminar este archivo?')) return;

        fetch(`/admin/test-drive/${vehicleId}/engine-audio/${field}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(res => res.json()).then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    // ============================================
    // SUBIDA DE VIDEO POR CHUNKS (usando jQuery AJAX)
    // ============================================

    const CHUNK_SIZE = 2 * 1024 * 1024; // 2MB por chunk

    var videoUploadState = {
        uploadId: null,
        aborted: false,
        uploading: false
    };

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function updateUploadProgress(percent, detail) {
        $('#video-upload-bar-add').css('width', percent + '%');
        $('#video-upload-percent-add').text(percent + '%');
        if (detail) {
            $('#video-upload-details-add').text(detail);
        }
    }

    // Manejar seleccion de archivo de video
    $('#video_file_add').on('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;

        // Actualizar label
        $(this).next('.custom-file-label').text(file.name);

        // Validar tipo
        var validTypes = ['video/mp4', 'video/webm', 'video/quicktime'];
        if (!validTypes.includes(file.type)) {
            alert('Formato no valido. Use MP4 o WebM.');
            this.value = '';
            $(this).next('.custom-file-label').text('Seleccionar video...');
            return;
        }

        // Iniciar subida por chunks
        uploadVideoInChunks(file);
    });

    async function uploadVideoInChunks(file) {
        var totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        var uploadId = 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        videoUploadState.uploadId = uploadId;
        videoUploadState.aborted = false;
        videoUploadState.uploading = true;

        // Mostrar UI de progreso
        $('#video-upload-progress-add').show();
        $('#video-upload-complete-add').hide();
        $('#video-upload-bar-add').removeClass('bg-danger').addClass('bg-primary');
        $('#video-upload-status-add').html('<i class="fa fa-spinner fa-spin mr-1"></i>Subiendo video...');
        updateUploadProgress(0, 'Iniciando subida de ' + formatBytes(file.size) + ' en ' + totalChunks + ' partes...');
        $('#submitAddVideoBtn').prop('disabled', true);

        var csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();

        // Subir cada chunk
        for (var i = 0; i < totalChunks; i++) {
            if (videoUploadState.aborted) {
                console.log('Upload abortado por usuario');
                return;
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
                    url: '{{ route("admin.test-drive.upload-chunk") }}',
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
                $('#video-upload-status-add').html('<i class="fa fa-exclamation-triangle text-danger mr-1"></i>Error al subir');
                $('#video-upload-bar-add').removeClass('bg-primary').addClass('bg-danger');
                updateUploadProgress(0, 'Error: ' + (error.responseJSON?.message || error.message || 'Fallo en la subida'));
                videoUploadState.uploading = false;
                return;
            }
        }

        // Todos los chunks subidos, ahora ensamblar
        $('#video-upload-status-add').html('<i class="fa fa-spinner fa-spin mr-1"></i>Ensamblando video...');
        updateUploadProgress(100, 'Ensamblando archivo final...');

        try {
            var completeResponse = await $.ajax({
                url: '{{ route("admin.test-drive.complete-upload") }}',
                type: 'POST',
                data: {
                    uploadId: uploadId,
                    fileName: file.name,
                    totalChunks: totalChunks,
                    vehicleId: vehicleId,
                    _token: csrfToken
                },
                timeout: 300000 // 5 minutos para ensamblar
            });

            if (completeResponse.success) {
                // Exito: guardar el path del video
                $('#video_path_add').val(completeResponse.videoPath);

                // Actualizar UI
                $('#video-upload-progress-add').hide();
                $('#video-upload-complete-add').show();
                $('#video-upload-complete-text-add').text('Video "' + file.name + '" subido correctamente');
                $('#submitAddVideoBtn').prop('disabled', false);
                videoUploadState.uploading = false;
            } else {
                throw new Error(completeResponse.error || 'Error al ensamblar');
            }
        } catch (error) {
            console.error('Error ensamblando video', error);
            $('#video-upload-status-add').html('<i class="fa fa-exclamation-triangle text-danger mr-1"></i>Error al ensamblar');
            $('#video-upload-bar-add').removeClass('bg-primary').addClass('bg-danger');
            updateUploadProgress(0, 'Error: ' + (error.responseJSON?.error || error.message || 'Fallo al ensamblar'));
            videoUploadState.uploading = false;
        }
    }

    function cancelUpload() {
        if (videoUploadState.uploadId) {
            var csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
            $.post('{{ route("admin.test-drive.cancel-upload") }}', {
                uploadId: videoUploadState.uploadId,
                _token: csrfToken
            }).fail(function(e) {
                console.warn('Error al cancelar subida:', e);
            });
        }
        videoUploadState.uploadId = null;
        videoUploadState.aborted = true;
    }

    // Cancelar subida al cerrar modal
    $('#cancelAddVideoBtn').on('click', function() {
        if (videoUploadState.uploading) {
            videoUploadState.aborted = true;
            cancelUpload();
        }
    });

    // Resetear modal al cerrar
    $('#addVideoModal').on('hidden.bs.modal', function() {
        if (videoUploadState.uploading) {
            videoUploadState.aborted = true;
            cancelUpload();
        }

        // Resetear estado
        videoUploadState = { uploadId: null, aborted: false, uploading: false };

        // Resetear formulario
        $('#addVideoForm')[0].reset();
        $('#video_path_add').val('');
        $('#video-upload-progress-add').hide();
        $('#video-upload-complete-add').hide();
        $('#submitAddVideoBtn').prop('disabled', true);

        // Resetear labels de archivo
        $('#addVideoModal .custom-file-label').text('Seleccionar...');
    });

    // Validar formulario antes de enviar
    document.getElementById('addVideoForm').addEventListener('submit', function(e) {
        const videoPath = document.getElementById('video_path_add').value;
        if (!videoPath) {
            e.preventDefault();
            alert('Debe subir un video primero');
            return false;
        }
    });

    // ============================================
    // SUBIDA DE VIDEO POV POR CHUNKS (usando jQuery AJAX)
    // ============================================

    var povUploadState = {
        uploadId: null,
        aborted: false,
        uploading: false
    };

    function updatePovUploadProgress(percent, detail) {
        $('#pov-upload-bar').css('width', percent + '%');
        $('#pov-upload-percent').text(percent + '%');
        if (detail) {
            $('#pov-upload-details').text(detail);
        }
    }

    $('#interior_pov_video').on('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;

        // Actualizar label
        $(this).next('.custom-file-label').text(file.name);

        // Validar tipo
        var validTypes = ['video/mp4', 'video/webm', 'video/quicktime'];
        if (!validTypes.includes(file.type)) {
            alert('Formato no valido. Use MP4 o WebM.');
            this.value = '';
            $(this).next('.custom-file-label').text('Seleccionar video...');
            return;
        }

        // Iniciar subida por chunks
        uploadPovVideoInChunks(file);
    });

    async function uploadPovVideoInChunks(file) {
        var totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        var uploadId = 'pov_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        povUploadState.uploadId = uploadId;
        povUploadState.aborted = false;
        povUploadState.uploading = true;

        // Mostrar UI de progreso
        $('#pov-upload-progress').show();
        $('#pov-upload-complete').hide();
        $('#pov-upload-bar').removeClass('bg-danger').addClass('bg-primary');
        $('#pov-upload-status').html('<i class="fa fa-spinner fa-spin mr-1"></i>Subiendo video POV...');
        updatePovUploadProgress(0, 'Iniciando subida de ' + formatBytes(file.size) + ' en ' + totalChunks + ' partes...');

        var csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();

        // Subir cada chunk
        for (var i = 0; i < totalChunks; i++) {
            if (povUploadState.aborted) {
                console.log('Upload POV abortado por usuario');
                return;
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
                    url: '{{ route("admin.test-drive.upload-chunk") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    timeout: 120000 // 2 minutos por chunk
                });

                if (response.success) {
                    var percent = Math.round(((i + 1) / totalChunks) * 100);
                    updatePovUploadProgress(percent, 'Parte ' + (i + 1) + ' de ' + totalChunks + ' completada');
                } else {
                    throw new Error(response.error || 'Error desconocido');
                }
            } catch (error) {
                console.error('Error subiendo chunk POV ' + i, error);
                $('#pov-upload-status').html('<i class="fa fa-exclamation-triangle text-danger mr-1"></i>Error al subir');
                $('#pov-upload-bar').removeClass('bg-primary').addClass('bg-danger');
                updatePovUploadProgress(0, 'Error: ' + (error.responseJSON?.message || error.message || 'Fallo en la subida'));
                povUploadState.uploading = false;
                return;
            }
        }

        // Todos los chunks subidos, ahora ensamblar
        $('#pov-upload-status').html('<i class="fa fa-spinner fa-spin mr-1"></i>Ensamblando video...');
        updatePovUploadProgress(100, 'Ensamblando archivo final...');

        try {
            var completeResponse = await $.ajax({
                url: '{{ route("admin.test-drive.complete-upload") }}',
                type: 'POST',
                data: {
                    uploadId: uploadId,
                    fileName: file.name,
                    totalChunks: totalChunks,
                    vehicleId: vehicleId,
                    isPov: true,
                    _token: csrfToken
                },
                timeout: 300000 // 5 minutos para ensamblar
            });

            if (completeResponse.success) {
                // Exito: guardar el path del video
                $('#interior_pov_video_path').val(completeResponse.videoPath);

                // Actualizar UI
                $('#pov-upload-progress').hide();
                $('#pov-upload-complete').show();
                $('#pov-upload-complete-text').text('Video POV "' + file.name + '" subido correctamente');
                povUploadState.uploading = false;
            } else {
                throw new Error(completeResponse.error || 'Error al ensamblar');
            }
        } catch (error) {
            console.error('Error ensamblando video POV', error);
            $('#pov-upload-status').html('<i class="fa fa-exclamation-triangle text-danger mr-1"></i>Error al ensamblar');
            $('#pov-upload-bar').removeClass('bg-primary').addClass('bg-danger');
            updatePovUploadProgress(0, 'Error: ' + (error.responseJSON?.error || error.message || 'Fallo al ensamblar'));
            povUploadState.uploading = false;
        }
    }
</script>
@endpush
