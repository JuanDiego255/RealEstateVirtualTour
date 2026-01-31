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
                        <div class="form-group col-md-6">
                            <input class="form-control form-control-lg input-rounded mb-4" type="hidden" name="type"
                                value="equirectangular">
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="title">Escena</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="text"
                                    name="title" required>
                            </div>



                            <div class="form-group col-md-6">
                                <label for="hfov">Campo de Visión Horizontal</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="number"
                                    step="0.1" name="hfov" min="-360" max="360" value="200" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="yaw">Movimiento de rotación horizontal</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="number"
                                    step="0.1" name="yaw" min="-360" max="360" value="0" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="pitch">Movimiento de rotación vertical</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="number"
                                    step="0.1" name="pitch" min="-360" max="360" value="0" required>
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="image">Imagen</label>
                            <img class="card-img-top img-fluid" id="image-preview" alt="Image Preview" />
                            <div class="custom-file">
                                <input type="file" class="form-control-file" id="image-upload" name="image"
                                    required onchange="previewImage()" accept="image/*">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="image">Imagen de referencia</label>
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
                        src="{{ isset($item->image) ? route('file', $item->image) : url('images/producto-sin-imagen.PNG') }}">
                    <br> <br>
                    <hr>
                    <h5>Información {{ $item->title }}</h5><br>

                    <p class="d-flex justify-content-left"><b> Tipo: </b> {{ $item->type }} </p><br>

                    <p class="d-flex justify-content-left">
                        <b> Campo de Visión Horizontal: </b> {{ $item->hfov }}
                    </p><br>

                    <p class="d-flex justify-content-left">
                        <b> Movimiento de rotación horizontal: </b> {{ $item->yaw }}
                    </p><br>

                    <p class="d-flex justify-content-left">
                        <b> Movimiento de rotación vertical: </b> {{ $item->pitch }}
                    </p><br>
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

                        <div class="form-group">
                            <input class="form-control form-control-lg input-rounded mb-4" type="hidden"
                                name="type" value="{{ $item->type }}">
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="title" class="d-flex justify-content-left">Título de la escena</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="text"
                                    name="title" required value="{{ $item->title }}">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="hfov" class=" d-flex justify-content-left">Campo de Visión
                                    Horizontal</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="number"
                                    step="0.1" name="hfov" min="-360" max="360"
                                    value="{{ $item->hfov }}" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="yaw" class=" d-flex justify-content-left">Movimiento de rotación
                                    horizontal</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="number"
                                    step="0.1" name="yaw" min="-360" max="360"
                                    value="{{ $item->yaw }}" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="pitch" class=" d-flex justify-content-left">Movimiento de rotación
                                    vertical</label>
                                <input class="form-control form-control-lg input-rounded mb-4" type="number"
                                    step="0.1" name="pitch" min="-360" max="360"
                                    value="{{ $item->pitch }}" required>
                            </div>
                        </div>



                        <div class="form-group">
                            <label for="image" class=" d-flex justify-content-left">Imagen (dejar vacío para mantener la actual)</label>
                            <img class="card-img-top img-fluid w-25"
                                src="{{ isset($item->image) ? route('file', $item->image) : url('images/producto-sin-imagen.PNG') }}">
                            <div class="custom-file">
                                <input type="file" class="form-control-file" name="image"
                                    accept="image/*">
                            </div>
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
