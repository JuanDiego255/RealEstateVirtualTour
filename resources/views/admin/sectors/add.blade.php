<div class="modal fade" id="addSector">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-th-large mr-2"></i>Agregar Sector</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('addSector') }}" method="POST" enctype="multipart/form-data">
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
                        <div class="form-group col-md-8">
                            <label>Nombre del Sector <span class="text-danger">*</span></label>
                            <input class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="name" placeholder="Ej: Sector Inmobiliario">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Ícono (FontAwesome)</label>
                            <input class="form-control form-control-lg input-rounded mb-2" type="text"
                                name="icon" placeholder="fa-building">
                            <small class="text-muted">Ej: fa-building, fa-car</small>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Descripción</label>
                            <textarea class="form-control mb-2" name="description" rows="3"
                                placeholder="Descripción breve del sector..."></textarea>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Imagen del Sector</label>
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
