<div class="modal fade" id="editSector{{ $sector->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-edit mr-2"></i>Editar Sector</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" action="{{ url('sector/update/' . $sector->id) }}" method="post"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}

                    <div class="row">
                        <div class="form-group col-md-8">
                            <label>Nombre del Sector <span class="text-danger">*</span></label>
                            <input value="{{ $sector->name }}"
                                class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Ícono (FontAwesome)</label>
                            <input value="{{ $sector->icon }}"
                                class="form-control form-control-lg input-rounded mb-2" type="text"
                                name="icon">
                        </div>
                        <div class="form-group col-md-12">
                            <label>Descripción</label>
                            <textarea class="form-control mb-2" name="description"
                                rows="3">{{ $sector->description }}</textarea>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Imagen del Sector</label>
                            @if ($sector->image)
                                <img class="card-img-top img-fluid w-50 mb-2"
                                    src="{{ route('file', $sector->image) }}">
                            @endif
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
