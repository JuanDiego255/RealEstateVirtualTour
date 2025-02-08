<div class="modal fade" id="addProperty">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar propiedad</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('addProperty') }}" method="POST" enctype="multipart/form-data">
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
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="text">Nombre</label>
                            <input class="form-control form-control-lg input-rounded mb-4" required type="text"
                                name="name">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Código</label>
                            <input class="form-control form-control-lg input-rounded mb-4" required type="text"
                                name="code">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Dormitorios</label>
                            <input class="form-control form-control-lg input-rounded mb-4" required type="number"
                                name="rooms">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Baños</label>
                            <input class="form-control form-control-lg input-rounded mb-4" required type="number"
                                name="bathrooms">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Parqueos</label>
                            <input class="form-control form-control-lg input-rounded mb-4" required type="number"
                                name="garage">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Pisos</label>
                            <input class="form-control form-control-lg input-rounded mb-4" required type="number"
                                name="floor_levels">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Construcción Mt2</label>
                            <input class="form-control form-control-lg input-rounded mb-4" required type="number"
                                name="construction">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Terreno Mt2</label>
                            <input class="form-control form-control-lg input-rounded mb-4" required type="number"
                                name="land">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Año de construcción</label>
                            <input class="form-control form-control-lg input-rounded mb-4" required type="text"
                                name="construction_year">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Mantenimiento</label>
                            <input class="form-control form-control-lg input-rounded mb-4" required type="text"
                                name="maintenance">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Precio</label>
                            <input class="form-control form-control-lg input-rounded mb-4" required type="text"
                                name="price">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="image">Imagen</label>
                            <img class="card-img-top img-fluid" id="image-preview" alt="Image Preview" />
                            <div class="custom-file">
                                <input type="file" class="form-control-file" id="image-upload" name="image"
                                    required onchange="previewImage()" accept="image/*">
                            </div>
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
