<div class="d-flex justify-content-end">
    <!-- Add Hotspot -->
    <button type="button" class="btn btn-rounded btn-outline-info mb-3" data-toggle="modal" data-target="#addHotspot">Nuevo
        HotSpot</button>
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
                                <label for="sourceScene">
                                    Origen de la escena</label>
                                <select class="form-control form-control-lg input-rounded mb-4" id="sourceSceneAdd"
                                    name="sourceScene" required>
                                    <option value="" disabled selected>Seleccione uno</option>
                                    @foreach ($scene as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="scene">Objetivo de la escena</label>
                                <select class="form-control form-control-lg input-rounded mb-4" name="targetScene"
                                    required>
                                    <option value="" disabled selected>Seleccione uno </option>
                                    @foreach ($scene as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="type">Tipo</label>
                                <select class="form-control form-control-lg input-rounded mb-4" name="type" required>
                                    <option value="" disabled selected>Seleccione uno </option>
                                    <option value="info">Información</option>
                                    <option value="scene">Enlace</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="yaw">Yaw</label>
                                <input id="yawAdd" class="form-control form-control-lg input-rounded mb-4" required
                                    type="text" step="0.1" name="yaw" value="0">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="pitch">Pitch</label>
                                <input id="pitchAdd" class="form-control form-control-lg input-rounded mb-4" required
                                    type="text" step="0.1" name="pitch" value="0">
                            </div>

                            <div class="form-group col-md-12">
                                <label for="text">Información</label>
                                <textarea class="form-control form-control-lg input-rounded mb-4" required type="text" name="text"></textarea>
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
    </table>
</div>

<!-- Modal -->
@foreach ($hotspots as $hotspot)
    <!-- Detail Modal -->
    <div class="modal fade" id="detailHotspot{{ $hotspot['id'] }}">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title justify-content-">Información punto de acceso</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <input type="hidden" name="property_id" value="{{ $id }}">
                <div class="modal-body">

                    <p class="d-flex justify-content-left">
                        <b>Tipo: </b> {{ $hotspot->type }}
                    </p><br>

                    <p class="d-flex justify-content-left">
                        <b>Movimiento de rotación horizontal: </b> {{ $hotspot->yaw }}
                    </p><br>

                    <p class="d-flex justify-content-left">
                        <b>Movimiento de rotación vertical: </b> {{ $hotspot->pitch }}
                    </p><br>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <center>
        <div class="modal modal-xl fade text-center" id="editHotspot{{ $hotspot['id'] }}">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content  modal-lg">
                    <div class="modal-header">
                        <h5 class="modal-title">Cambiar punto de acceso </h5>
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
                            <div id="panorama-hotspot{{$hotspot->id}}" style="width: 100%; height: 500px;"></div>
                            <input type="hidden" value="{{ $hotspot->id }}" name="id" id="id">
                            <input type="hidden" name="property_id" value="{{ $id }}">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="sourceScene" class="d-flex justify-content-left">Escena
                                        Principal</label>
                                    <select class="form-control form-control-lg input-rounded mb-4" name="sourceScene"
                                        id="sourceScene" required>
                                        <option value="" disabled>Seleccione uno</option>
                                        @foreach ($scene as $scenes)
                                            @if ($hotspot->sourceScene == $scenes->id)
                                                <option value="{{ $hotspot->sourceScene }}" selected>
                                                    {{ $scenes->title }}
                                                </option>
                                            @else
                                                <option value="{{ $scenes->id }}">{{ $scenes->title }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>


                                <div class="form-group col-md-6">
                                    <label for="scene" class="d-flex justify-content-left">Objetivo de la
                                        escena</label>
                                    <select class="form-control form-control-lg input-rounded mb-4" name="targetScene"
                                        required>
                                        <option value="" disabled>Seleccione uno </option>
                                        @foreach ($scene as $scenes)
                                            @if ($hotspot->targetScene == $scenes->id)
                                                <option value="{{ $hotspot->targetScene }}" selected>
                                                    {{ $scenes->title }}
                                                </option>
                                            @else
                                                <option value="{{ $scenes->id }}"> {{ $scenes->title }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="type" class="d-flex justify-content-left">Tipo</label>
                                    <select class="form-control form-control-lg input-rounded mb-4" name="type"
                                        required>
                                        <option value="" disabled>Seleccione uno </option>
                                        <option value="info"
                                            @if ($hotspot->type == 'info') {{ 'selected' }} @endif>
                                            Información</option>
                                        <option value="scene"
                                            @if ($hotspot->type == 'scene') {{ 'selected' }} @endif>
                                            Enlace</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="yaw" class="d-flex justify-content-left">Movimiento de rotación
                                        horizontal</label>
                                    <input id="yaw" class="form-control form-control-lg input-rounded mb-4"
                                        required type="text" step="0.20" name="yaw"
                                        value="{{ $hotspot->yaw }}">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="pitch" class="d-flex justify-content-left">Movimiento de rotación
                                        vertical</label>
                                    <input id="pitch" class="form-control form-control-lg input-rounded mb-4"
                                        required type="text" step="0.1" name="pitch"
                                        value="{{ $hotspot->pitch }}">
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="text" class="d-flex justify-content-left">Texto</label>
                                    <textarea class="form-control form-control-lg input-rounded mb-4" name="text" required> {{ $hotspot->info }} </textarea>
                                </div>
                            </div>


                            <div class="form-group">
                                <label for="text" class="d-flex justify-content-left">Imagen Referencia</label>
                                @if ($hotspot->image)
                                    <img class="card-img-top img-fluid w-50"
                                        src="{{ asset('storage' . '/' . $hotspot->image) }}">
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


    <!-- Delete Modal -->
    <div id="deleteHotspot{{ $hotspot['id'] }}" class="modal fade">
        <div class="modal-dialog modal-dialog-centered modal-confirm">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <div class="icon-box">
                        <i class="fa fa-times-circle"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <p class="text-center">¿Está seguro de que desea eliminar estos datos?</p>
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
<script>
    $(document).ready(function() {
        // Define un objeto que mapea los IDs de las escenas a las URLs de las imágenes
        var sceneImageMap = {
            @foreach ($scene as $scenes)
                {{ $scenes->id }}: "{{ asset('storage' . '/' . $scenes->image) }}",
            @endforeach
        };
        var viewer = null;
        var defaultSceneId = $("#sourceScene").val();
        var panoramaAdd = document.getElementById("panorama-hotspot-add");

        panoramaAdd.style.display = "none";

        // Si hay una escena seleccionada por defecto, actualiza la imagen del panorama

        // Función para cambiar la imagen del panorama al seleccionar una nueva escena
        $("#sourceScene").change(function() {
            var selectedSceneId = $(this).val();

            var imageUrl = sceneImageMap[selectedSceneId];

            // Actualiza la imagen del panorama utilizando Pannellum
            viewer = pannellum.viewer('panorama-hotspot', {
                "type": "equirectangular",
                "panorama": imageUrl,
                "autoLoad": true
            });

            viewer.on('mousedown', function(event) {
                // Obtiene las coordenadas yaw y pitch al hacer clic en la imagen
                var yaw = viewer.getYaw();
                var pitch = viewer.getPitch();

                // Puedes hacer lo que quieras con las coordenadas yaw y pitch
                $("#yaw").val(yaw);
                $("#pitch").val(pitch);
            });
        });

        $("#sourceSceneAdd").change(function() {
            panoramaAdd.style.display = "block";
            var selectedSceneId = $(this).val();

            var imageUrl = sceneImageMap[selectedSceneId];

            // Actualiza la imagen del panorama utilizando Pannellum
            viewer = pannellum.viewer('panorama-hotspot-add', {
                "type": "equirectangular",
                "panorama": imageUrl,
                "autoLoad": true
            });

            viewer.on('mousedown', function(event) {
                // Obtiene las coordenadas yaw y pitch al hacer clic en la imagen
                var yaw = viewer.getYaw();
                var pitch = viewer.getPitch();

                // Puedes hacer lo que quieras con las coordenadas yaw y pitch
                $("#yawAdd").val(yaw);
                $("#pitchAdd").val(pitch);
            });
        });

    });
</script>
@if (count($hotspots) != 0)
    {
    <script>
        $(document).ready(function() {

            var modals = document.querySelectorAll('[id^="editHotspot"]');

            // Itera sobre los modales y agrega el evento a cada uno
            modals.forEach(function(modal) {
                $('#'+modal.id).on('shown.bs.modal', function() {
                    var id = modal.id;
                    var numero = id.match(/\d+/);
                    var sceneImageMap = {
                        @foreach ($scene as $scenes)
                            {{ $scenes->id }}: "{{ asset('storage' . '/' . $scenes->image) }}",
                        @endforeach
                    };
                    var defaultSceneId = $("#sourceScene").val();

                    if (defaultSceneId) {

                        var imageUrl = sceneImageMap[defaultSceneId];

                        // Inicializa Pannellum con la imagen de la primera escena
                        viewer = pannellum.viewer('panorama-hotspot'+numero, {
                            "type": "equirectangular",
                            "panorama": imageUrl,
                            "autoLoad": true
                        });

                        viewer.on('mousedown', function(event) {
                            // Obtiene las coordenadas yaw y pitch al hacer clic en la imagen
                            var yaw = viewer.getYaw();
                            var pitch = viewer.getPitch();
                            // Puedes hacer lo que quieras con las coordenadas yaw y pitch
                            $("#yaw").val(yaw);
                            $("#pitch").val(pitch);
                        });
                    }
                });
            });


        });
    </script>
    }
@endif
