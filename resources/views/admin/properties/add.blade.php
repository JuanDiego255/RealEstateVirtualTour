<div class="modal fade" id="addProperty">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar publicación</h5>
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
                        <div class="form-group col-md-4 add-field-realestate">
                            <label>Código <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="code">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tipo de publicación</label>
                            <select class="form-control" name="property_type" id="addPropertyType">
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
                            <label>Subcategoría</label>
                            <select class="form-control" name="subcategory_id">
                                <option value="">-- Sin subcategoría --</option>
                                @if(isset($subcategories))
                                    @foreach ($subcategories as $sub)
                                        <option value="{{ $sub->id }}">
                                            {{ $sub->category->sector->name ?? '' }} → {{ $sub->category->name ?? '' }} → {{ $sub->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Descripción detallada..."></textarea>
                    </div>

                    {{-- ============ CAMPOS DE PROPIEDAD (INMUEBLE) ============ --}}
                    <div id="addFieldsRealEstate">
                        <h6 class="text-muted mb-3 mt-3"><i class="fa fa-list"></i> Características</h6>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Dormitorios <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" name="rooms" min="0">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Baños <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" name="bathrooms" min="0">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Parqueos <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" name="garage" min="0">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Pisos <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" name="floor_levels" min="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Construcción Mt² <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" name="construction" min="0">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Terreno Mt² <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" name="land" min="0">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Año construcción <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="construction_year">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Mantenimiento <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="maintenance">
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
                    </div>

                    {{-- ============ CAMPOS DE VEHÍCULO ============ --}}
                    <div id="addFieldsVehicle" style="display:none;">
                        <h6 class="text-muted mb-3 mt-3"><i class="fa fa-car"></i> Datos del Vehículo</h6>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Marca <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="brand">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Modelo <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="model">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Año <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="year">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Color</label>
                                <input class="form-control" type="text" name="color">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Kilometraje</label>
                                <input class="form-control" type="text" name="mileage_km" placeholder="Ej: 50000">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Tipo combustible</label>
                                <select class="form-control" name="fuel_type">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Gasolina">Gasolina</option>
                                    <option value="Diésel">Diésel</option>
                                    <option value="Híbrido">Híbrido</option>
                                    <option value="Eléctrico">Eléctrico</option>
                                    <option value="GLP">GLP</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Transmisión</label>
                                <select class="form-control" name="transmission">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Automática">Automática</option>
                                    <option value="Manual">Manual</option>
                                    <option value="CVT">CVT</option>
                                    <option value="Semiautomática">Semiautomática</option>
                                </select>
                            </div>
                        </div>
                        <h6 class="text-muted mb-3 mt-3"><i class="fa fa-cog"></i> Especificaciones Técnicas</h6>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Cilindraje (CC)</label>
                                <input class="form-control" type="text" name="engine_cc">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Cap. tanque</label>
                                <input class="form-control" type="text" name="fuel_tank_capacity">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Tracción</label>
                                <select class="form-control" name="drivetrain">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="4x2">4x2</option>
                                    <option value="4x4">4x4</option>
                                    <option value="AWD">AWD</option>
                                    <option value="FWD">FWD</option>
                                    <option value="RWD">RWD</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Llantas</label>
                                <input class="form-control" type="text" name="tires">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Puertas</label>
                                <input class="form-control" type="text" name="doors">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Pasajeros</label>
                                <input class="form-control" type="text" name="passengers">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Condición</label>
                                <select class="form-control" name="condition">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Nuevo">Nuevo</option>
                                    <option value="Usado">Usado</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Placa</label>
                                <input class="form-control" type="text" name="plate">
                            </div>
                        </div>
                    </div>

                    {{-- Comisión y exclusividad (aplica a todos los tipos) --}}
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('addPropertyType');
    if (sel) {
        sel.addEventListener('change', function() {
            toggleAddFields(this.value);
        });
        toggleAddFields(sel.value);
    }
});
function toggleAddFields(type) {
    var realEstate = document.getElementById('addFieldsRealEstate');
    var vehicle = document.getElementById('addFieldsVehicle');
    var codeField = document.querySelector('#addProperty .add-field-realestate');
    if (type === 'vehicle') {
        realEstate.style.display = 'none';
        vehicle.style.display = 'block';
        if (codeField) codeField.style.display = 'none';
        realEstate.querySelectorAll('input, select').forEach(function(el) { el.removeAttribute('required'); });
    } else {
        realEstate.style.display = 'block';
        vehicle.style.display = 'none';
        if (codeField) codeField.style.display = 'block';
    }
}
</script>
