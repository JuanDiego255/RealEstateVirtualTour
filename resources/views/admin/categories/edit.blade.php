<div class="modal fade" id="editCategory{{ $category->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-edit mr-2"></i>Editar Categoría</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" action="{{ url('category/update/' . $category->id) }}" method="post"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Sector <span class="text-danger">*</span></label>
                            <select class="form-control form-control-lg input-rounded mb-2" name="sector_id" required>
                                @foreach ($sectors as $sector)
                                    <option value="{{ $sector->id }}"
                                        {{ $category->sector_id == $sector->id ? 'selected' : '' }}>
                                        {{ $sector->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input value="{{ $category->name }}"
                                class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="name">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Ubicación</label>
                            <input value="{{ $category->location }}"
                                class="form-control form-control-lg input-rounded mb-2" type="text" name="location">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Facilidades / Amenidades</label>
                            <input value="{{ $category->facilities }}"
                                class="form-control form-control-lg input-rounded mb-2" type="text" name="facilities">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Características</label>
                            <textarea class="form-control mb-2" name="features"
                                rows="2">{{ $category->features }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Notas</label>
                            <textarea class="form-control mb-2" name="notes"
                                rows="2">{{ $category->notes }}</textarea>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Descripción</label>
                            <textarea class="form-control mb-2" name="description"
                                rows="2">{{ $category->description }}</textarea>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Imagen</label>
                            @if ($category->image)
                                <img class="card-img-top img-fluid w-50 mb-2"
                                    src="{{ route('file', $category->image) }}">
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
