<div class="modal fade" id="addProperty">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
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

                    {{-- Información básica --}}
                    <h6 class="text-muted mb-3"><i class="fa fa-info-circle"></i> Información básica</h6>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input class="form-control" required type="text" name="name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Código <span class="text-danger">*</span></label>
                            <input class="form-control" required type="text" name="code">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tipo de propiedad</label>
                            <select class="form-control" name="property_type">
                                <option value="house">Casa</option>
                                <option value="apartment">Apartamento</option>
                                <option value="land">Lote/Terreno</option>
                                <option value="vehicle">Vehículo</option>
                                <option value="commercial">Comercial</option>
                                <option value="other">Otro</option>
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
                                        <option value="{{ $cat->id }}">{{ $cat->sector->name ?? '' }} → {{ $cat->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Descripción detallada de la propiedad..."></textarea>
                    </div>

                    {{-- Características --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-list"></i> Características</h6>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Dormitorios <span class="text-danger">*</span></label>
                            <input class="form-control" required type="number" name="rooms" min="0">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Baños <span class="text-danger">*</span></label>
                            <input class="form-control" required type="number" name="bathrooms" min="0">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Parqueos <span class="text-danger">*</span></label>
                            <input class="form-control" required type="number" name="garage" min="0">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Pisos <span class="text-danger">*</span></label>
                            <input class="form-control" required type="number" name="floor_levels" min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Construcción Mt² <span class="text-danger">*</span></label>
                            <input class="form-control" required type="number" name="construction" min="0">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Terreno Mt² <span class="text-danger">*</span></label>
                            <input class="form-control" required type="number" name="land" min="0">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Año construcción <span class="text-danger">*</span></label>
                            <input class="form-control" required type="text" name="construction_year">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Mantenimiento <span class="text-danger">*</span></label>
                            <input class="form-control" required type="text" name="maintenance">
                        </div>
                    </div>

                    {{-- Precio y moneda --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-money"></i> Precio</h6>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Precio <span class="text-danger">*</span></label>
                            <input class="form-control" required type="text" name="price">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Moneda</label>
                            <select class="form-control" name="currency">
                                <option value="CRC">₡ Colones (CRC)</option>
                                <option value="USD">$ Dólares (USD)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Estado</label>
                            <select class="form-control" name="status">
                                <option value="available">Disponible</option>
                                <option value="reserved">Reservado</option>
                                <option value="negotiating">En negociación</option>
                                <option value="sold">Vendido</option>
                                <option value="rented">Alquilado</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    {{-- Ubicación --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-map-marker"></i> Ubicación</h6>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Ubicación / Zona</label>
                            <input class="form-control" type="text" name="location" placeholder="Ej: San José, Escazú">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Dirección</label>
                            <input class="form-control" type="text" name="address" placeholder="Dirección completa">
                        </div>
                    </div>

                    {{-- Comisión y exclusividad --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-handshake-o"></i> Comisión y Bolsa</h6>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>% Comisión</label>
                            <input class="form-control" type="number" step="0.01" min="0" max="100" name="commission_percentage" placeholder="Ej: 5.00">
                            <small class="form-text text-muted">Porcentaje para agentes externos</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Exclusividad</label>
                            <select class="form-control" name="is_exclusive">
                                <option value="0">No exclusiva (visible en Bolsa)</option>
                                <option value="1">Exclusiva (solo mi empresa)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Notas de comisión</label>
                            <input class="form-control" type="text" name="commission_notes" placeholder="Condiciones especiales">
                        </div>
                    </div>

                    {{-- Imagen principal --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-image"></i> Imagen principal</h6>
                    <div class="form-group">
                        <img class="card-img-top img-fluid" id="image-preview" alt="Image Preview" style="max-height: 200px; object-fit: cover;" />
                        <div class="custom-file mt-2">
                            <input type="file" class="form-control-file" id="image-upload" name="image"
                                required onchange="previewImage()" accept="image/*">
                        </div>
                    </div>

                    {{-- Galería adicional --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-images"></i> Galería adicional</h6>
                    <div class="form-group">
                        <input type="file" class="form-control-file" name="additional_images[]" accept="image/*" multiple>
                        <small class="form-text text-muted">Puede seleccionar múltiples imágenes (opcional)</small>
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
