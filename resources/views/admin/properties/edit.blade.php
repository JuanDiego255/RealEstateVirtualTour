<div class="modal fade" id="editProperty{{ $property['id'] }}" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-normal" id="exampleModalLabel">Editar Imagen</h5>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="updateProperty" action="{{ url('property/update/' . $property->id) }}" method="post"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="text">Nombre</label>
                            <input value="{{$property->name}}" class="form-control form-control-lg input-rounded mb-4" required type="text"
                                name="name">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Código</label>
                            <input value="{{$property->code}}" class="form-control form-control-lg input-rounded mb-4" required type="text"
                                name="code">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Dormitorios</label>
                            <input value="{{$property->rooms}}" class="form-control form-control-lg input-rounded mb-4" required type="number"
                                name="rooms">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Baños</label>
                            <input value="{{$property->bathrooms}}" class="form-control form-control-lg input-rounded mb-4" required type="number"
                                name="bathrooms">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Parqueos</label>
                            <input value="{{$property->garage}}" class="form-control form-control-lg input-rounded mb-4" required type="number"
                                name="garage">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Pisos</label>
                            <input value="{{$property->floor_levels}}" class="form-control form-control-lg input-rounded mb-4" required type="number"
                                name="floor_levels">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Construcción Mt2</label>
                            <input value="{{$property->construction}}" class="form-control form-control-lg input-rounded mb-4" required type="number"
                                name="construction">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Terreno Mt2</label>
                            <input value="{{$property->land}}" class="form-control form-control-lg input-rounded mb-4" required type="number"
                                name="land">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Año de construcción</label>
                            <input value="{{$property->construction_year}}" class="form-control form-control-lg input-rounded mb-4" required type="text"
                                name="construction_year">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Mantenimiento</label>
                            <input value="{{$property->maintenance}}" class="form-control form-control-lg input-rounded mb-4" required type="text"
                                name="maintenance">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text">Precio</label>                           
                            <input value="{{$property->price}}" class="form-control form-control-lg input-rounded mb-4" required type="text"
                                name="price">
                        </div>
                        <div class="form-group col-md-12">
                           
                            <img class="card-img-top img-fluid w-50" src="{{ asset('storage' . '/' . $property->image) }}">
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
