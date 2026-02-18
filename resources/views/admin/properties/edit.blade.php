<div class="modal fade" id="editProperty{{ $property['id'] }}" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-normal" id="exampleModalLabel">Editar Propiedad: {{ $property->name }}</h5>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="updateProperty{{ $property->id }}" action="{{ url('property/update/' . $property->id) }}" method="post"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}

                    {{-- Información básica --}}
                    <h6 class="text-muted mb-3"><i class="fa fa-info-circle"></i> Información básica</h6>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input value="{{ $property->name }}" class="form-control" required type="text" name="name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Código <span class="text-danger">*</span></label>
                            <input value="{{ $property->code }}" class="form-control" required type="text" name="code">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tipo de propiedad</label>
                            <select class="form-control" name="property_type">
                                <option value="house" {{ ($property->property_type ?? '') == 'house' ? 'selected' : '' }}>Casa</option>
                                <option value="apartment" {{ ($property->property_type ?? '') == 'apartment' ? 'selected' : '' }}>Apartamento</option>
                                <option value="land" {{ ($property->property_type ?? '') == 'land' ? 'selected' : '' }}>Lote/Terreno</option>
                                <option value="vehicle" {{ ($property->property_type ?? '') == 'vehicle' ? 'selected' : '' }}>Vehículo</option>
                                <option value="commercial" {{ ($property->property_type ?? '') == 'commercial' ? 'selected' : '' }}>Comercial</option>
                                <option value="other" {{ ($property->property_type ?? '') == 'other' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <label>Categoría</label>
                            <select class="form-control" name="category_id">
                                <option value="">-- Sin categoría --</option>
                                @if(isset($categories))
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $property->category_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->sector->name ?? '' }} → {{ $cat->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Descripción detallada...">{{ $property->description ?? '' }}</textarea>
                    </div>

                    {{-- Características --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-list"></i> Características</h6>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Dormitorios <span class="text-danger">*</span></label>
                            <input value="{{ $property->rooms }}" class="form-control" required type="number" name="rooms" min="0">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Baños <span class="text-danger">*</span></label>
                            <input value="{{ $property->bathrooms }}" class="form-control" required type="number" name="bathrooms" min="0">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Parqueos <span class="text-danger">*</span></label>
                            <input value="{{ $property->garage }}" class="form-control" required type="number" name="garage" min="0">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Pisos <span class="text-danger">*</span></label>
                            <input value="{{ $property->floor_levels }}" class="form-control" required type="number" name="floor_levels" min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Construcción Mt² <span class="text-danger">*</span></label>
                            <input value="{{ $property->construction }}" class="form-control" required type="number" name="construction" min="0">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Terreno Mt² <span class="text-danger">*</span></label>
                            <input value="{{ $property->land }}" class="form-control" required type="number" name="land" min="0">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Año construcción <span class="text-danger">*</span></label>
                            <input value="{{ $property->construction_year }}" class="form-control" required type="text" name="construction_year">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Mantenimiento <span class="text-danger">*</span></label>
                            <input value="{{ $property->maintenance }}" class="form-control" required type="text" name="maintenance">
                        </div>
                    </div>

                    {{-- Precio y moneda --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-money"></i> Precio</h6>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Precio <span class="text-danger">*</span></label>
                            <input value="{{ $property->price }}" class="form-control" required type="text" name="price">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Moneda</label>
                            <select class="form-control" name="currency">
                                <option value="CRC" {{ ($property->currency ?? 'CRC') == 'CRC' ? 'selected' : '' }}>₡ Colones (CRC)</option>
                                <option value="USD" {{ ($property->currency ?? '') == 'USD' ? 'selected' : '' }}>$ Dólares (USD)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Estado</label>
                            <select class="form-control" name="status">
                                <option value="available" {{ ($property->status ?? 'available') == 'available' ? 'selected' : '' }}>Disponible</option>
                                <option value="reserved" {{ ($property->status ?? '') == 'reserved' ? 'selected' : '' }}>Reservado</option>
                                <option value="negotiating" {{ ($property->status ?? '') == 'negotiating' ? 'selected' : '' }}>En negociación</option>
                                <option value="sold" {{ ($property->status ?? '') == 'sold' ? 'selected' : '' }}>Vendido</option>
                                <option value="rented" {{ ($property->status ?? '') == 'rented' ? 'selected' : '' }}>Alquilado</option>
                                <option value="inactive" {{ ($property->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    {{-- Ubicación --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-map-marker"></i> Ubicación</h6>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Ubicación / Zona</label>
                            <input value="{{ $property->location ?? '' }}" class="form-control" type="text" name="location" placeholder="Ej: San José, Escazú">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Dirección</label>
                            <input value="{{ $property->address ?? '' }}" class="form-control" type="text" name="address" placeholder="Dirección completa">
                        </div>
                    </div>

                    {{-- Comisión y exclusividad --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-handshake-o"></i> Comisión y Bolsa</h6>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>% Comisión</label>
                            <input value="{{ $property->commission_percentage ?? '' }}" class="form-control" type="number" step="0.01" min="0" max="100" name="commission_percentage" placeholder="Ej: 5.00">
                            <small class="form-text text-muted">Porcentaje para agentes externos</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Exclusividad</label>
                            <select class="form-control" name="is_exclusive">
                                <option value="0" {{ !($property->is_exclusive ?? false) ? 'selected' : '' }}>No exclusiva (visible en Bolsa)</option>
                                <option value="1" {{ ($property->is_exclusive ?? false) ? 'selected' : '' }}>Exclusiva (solo mi empresa)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Notas de comisión</label>
                            <input value="{{ $property->commission_notes ?? '' }}" class="form-control" type="text" name="commission_notes" placeholder="Condiciones especiales">
                        </div>
                    </div>

                    {{-- Imagen principal --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-image"></i> Imagen principal</h6>
                    <div class="form-group">
                        <img class="card-img-top img-fluid w-50 mb-2" src="{{ isset($property->image) ? route('file', $property->image) : url('images/producto-sin-imagen.PNG') }}" style="max-height: 200px; object-fit: cover;">
                        <div class="custom-file">
                            <input type="file" class="form-control-file" name="image" accept="image/*">
                            <small class="form-text text-muted">Dejar vacío para mantener la imagen actual</small>
                        </div>
                    </div>

                    {{-- Galería de imágenes adicionales --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-images"></i> Galería adicional</h6>
                    @if($property->images && $property->images->count() > 0)
                        <div class="row mb-2">
                            @foreach($property->images as $img)
                                <div class="col-3 mb-2 text-center">
                                    <img src="{{ $img->url }}" class="img-thumbnail" style="height: 80px; object-fit: cover;">
                                    <form action="{{ url('/property/' . $property->id . '/image/' . $img->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger mt-1" title="Eliminar"><i class="fa fa-trash"></i></button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="form-group">
                        <input type="file" class="form-control-file" name="additional_images[]" accept="image/*" multiple>
                        <small class="form-text text-muted">Puede seleccionar múltiples imágenes</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
