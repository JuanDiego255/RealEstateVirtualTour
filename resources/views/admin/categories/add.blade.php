<div class="modal fade" id="addCategory">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-folder-open mr-2"></i>Agregar Categoría</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('addCategory') }}" method="POST" enctype="multipart/form-data">
                    @csrf

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

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Sector <span class="text-danger">*</span></label>
                            <select class="form-control form-control-lg input-rounded mb-2" name="sector_id" required>
                                <option value="">-- Seleccionar Sector --</option>
                                @foreach ($sectors as $sector)
                                    <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nombre de la Categoría <span class="text-danger">*</span></label>
                            <input class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="name" placeholder="Ej: Condominio Montezuma">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Ubicación</label>
                            <input class="form-control form-control-lg input-rounded mb-2" type="text" name="location"
                                placeholder="Ej: San José, Costa Rica">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Facilidades / Amenidades</label>
                            <input class="form-control form-control-lg input-rounded mb-2" type="text" name="facilities"
                                placeholder="Ej: Piscina, Gimnasio, Seguridad">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Características</label>
                            <textarea class="form-control mb-2" name="features" rows="2"
                                placeholder="Características principales..."></textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Notas</label>
                            <textarea class="form-control mb-2" name="notes" rows="2"
                                placeholder="Notas adicionales..."></textarea>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Descripción</label>
                            <textarea class="form-control mb-2" name="description" rows="2"
                                placeholder="Descripción general de la categoría..."></textarea>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Imagen</label>
                            <div class="custom-file">
                                <input type="file" class="form-control-file" name="image" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-save mr-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
