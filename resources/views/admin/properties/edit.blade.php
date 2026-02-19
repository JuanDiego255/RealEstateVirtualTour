<div class="modal fade" id="editProperty{{ $property['id'] }}" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-normal" id="exampleModalLabel">Editar: {{ $property->name }}</h5>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="updateProperty{{ $property->id }}"
                    action="{{ url('property/update/' . $property->id) }}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}

                    {{-- Información básica --}}
                    <h6 class="text-muted mb-3"><i class="fa fa-info-circle"></i> Información básica</h6>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input value="{{ $property->name }}" class="form-control" required type="text"
                                name="name">
                        </div>
                        <div class="form-group col-md-4 edit-field-realestate-{{ $property->id }}">
                            <label>Código <span class="text-danger">*</span></label>
                            <input value="{{ $property->code }}" class="form-control" type="text" name="code">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tipo de publicación</label>
                            <select class="form-control editPropertyType" name="property_type"
                                data-id="{{ $property->id }}">
                                <option value="house"
                                    {{ ($property->property_type ?? '') == 'house' ? 'selected' : '' }}>Casa</option>
                                <option value="apartment"
                                    {{ ($property->property_type ?? '') == 'apartment' ? 'selected' : '' }}>Apartamento
                                </option>
                                <option value="land"
                                    {{ ($property->property_type ?? '') == 'land' ? 'selected' : '' }}>Lote/Terreno
                                </option>
                                <option value="vehicle"
                                    {{ ($property->property_type ?? '') == 'vehicle' ? 'selected' : '' }}>Vehículo
                                </option>
                                <option value="commercial"
                                    {{ ($property->property_type ?? '') == 'commercial' ? 'selected' : '' }}>Comercial
                                </option>
                                <option value="other"
                                    {{ ($property->property_type ?? '') == 'other' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <label>Subcategoría</label>
                            <select class="form-control" name="subcategory_id">
                                <option value="">-- Sin subcategoría --</option>
                                @if (isset($subcategories))
                                    @foreach ($subcategories as $sub)
                                        <option value="{{ $sub->id }}"
                                            {{ $property->subcategory_id == $sub->id ? 'selected' : '' }}>
                                            {{ $sub->category->sector->name ?? '' }} →
                                            {{ $sub->category->name ?? '' }} → {{ $sub->name }}
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

                    {{-- ============ CAMPOS DE PROPIEDAD (INMUEBLE) ============ --}}
                    <div id="editFieldsRealEstate{{ $property->id }}"
                        style="{{ ($property->property_type ?? '') == 'vehicle' ? 'display:none;' : '' }}">
                        <h6 class="text-muted mb-3 mt-3"><i class="fa fa-list"></i> Características</h6>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Dormitorios <span class="text-danger">*</span></label>
                                <input value="{{ $property->rooms }}" class="form-control" type="number"
                                    name="rooms" min="0">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Baños <span class="text-danger">*</span></label>
                                <input value="{{ $property->bathrooms }}" class="form-control" type="number"
                                    name="bathrooms" min="0">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Parqueos <span class="text-danger">*</span></label>
                                <input value="{{ $property->garage }}" class="form-control" type="number"
                                    name="garage" min="0">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Pisos <span class="text-danger">*</span></label>
                                <input value="{{ $property->floor_levels }}" class="form-control" type="number"
                                    name="floor_levels" min="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Construcción Mt² <span class="text-danger">*</span></label>
                                <input value="{{ $property->construction }}" class="form-control" type="number"
                                    name="construction" min="0">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Terreno Mt² <span class="text-danger">*</span></label>
                                <input value="{{ $property->land }}" class="form-control" type="number"
                                    name="land" min="0">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Año construcción <span class="text-danger">*</span></label>
                                <input value="{{ $property->construction_year }}" class="form-control"
                                    type="text" name="construction_year">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Mantenimiento <span class="text-danger">*</span></label>
                                <input value="{{ $property->maintenance }}" class="form-control" type="text"
                                    name="maintenance">
                            </div>
                        </div>

                        {{-- Ubicación --}}
                        <h6 class="text-muted mb-3 mt-3"><i class="fa fa-map-marker"></i> Ubicación</h6>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Ubicación / Zona</label>
                                <input value="{{ $property->location ?? '' }}" class="form-control" type="text"
                                    name="location" placeholder="Ej: San José, Escazú">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Dirección</label>
                                <input value="{{ $property->address ?? '' }}" class="form-control" type="text"
                                    name="address" placeholder="Dirección completa">
                            </div>
                        </div>

                        {{-- Comisión y exclusividad --}}
                        <h6 class="text-muted mb-3 mt-3"><i class="fa fa-handshake-o"></i> Comisión y Bolsa</h6>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>% Comisión</label>
                                <input value="{{ $property->commission_percentage ?? '' }}" class="form-control"
                                    type="number" step="0.01" min="0" max="100"
                                    name="commission_percentage" placeholder="Ej: 5.00">
                                <small class="form-text text-muted">Porcentaje para agentes externos</small>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Exclusividad</label>
                                <select class="form-control" name="is_exclusive">
                                    <option value="0"
                                        {{ !($property->is_exclusive ?? false) ? 'selected' : '' }}>No exclusiva
                                        (visible en Bolsa)</option>
                                    <option value="1" {{ $property->is_exclusive ?? false ? 'selected' : '' }}>
                                        Exclusiva (solo mi empresa)</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Notas de comisión</label>
                                <input value="{{ $property->commission_notes ?? '' }}" class="form-control"
                                    type="text" name="commission_notes" placeholder="Condiciones especiales">
                            </div>
                        </div>
                    </div>

                    {{-- ============ CAMPOS DE VEHÍCULO ============ --}}
                    <div id="editFieldsVehicle{{ $property->id }}"
                        style="{{ ($property->property_type ?? '') != 'vehicle' ? 'display:none;' : '' }}">
                        <h6 class="text-muted mb-3 mt-3"><i class="fa fa-car"></i> Datos del Vehículo</h6>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Marca <span class="text-danger">*</span></label>
                                <input value="{{ $property->brand ?? '' }}" class="form-control" type="text"
                                    name="brand">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Modelo <span class="text-danger">*</span></label>
                                <input value="{{ $property->model ?? '' }}" class="form-control" type="text"
                                    name="model">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Año <span class="text-danger">*</span></label>
                                <input value="{{ $property->year ?? '' }}" class="form-control" type="text"
                                    name="year">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Color</label>
                                <input value="{{ $property->color ?? '' }}" class="form-control" type="text"
                                    name="color">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Kilometraje</label>
                                <input value="{{ $property->mileage_km ?? '' }}" class="form-control" type="text"
                                    name="mileage_km">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Tipo combustible</label>
                                <select class="form-control" name="fuel_type">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Gasolina"
                                        {{ ($property->fuel_type ?? '') == 'Gasolina' ? 'selected' : '' }}>Gasolina
                                    </option>
                                    <option value="Diésel"
                                        {{ ($property->fuel_type ?? '') == 'Diésel' ? 'selected' : '' }}>Diésel
                                    </option>
                                    <option value="Híbrido"
                                        {{ ($property->fuel_type ?? '') == 'Híbrido' ? 'selected' : '' }}>Híbrido
                                    </option>
                                    <option value="Eléctrico"
                                        {{ ($property->fuel_type ?? '') == 'Eléctrico' ? 'selected' : '' }}>Eléctrico
                                    </option>
                                    <option value="GLP"
                                        {{ ($property->fuel_type ?? '') == 'GLP' ? 'selected' : '' }}>GLP</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Transmisión</label>
                                <select class="form-control" name="transmission">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Automática"
                                        {{ ($property->transmission ?? '') == 'Automática' ? 'selected' : '' }}>
                                        Automática</option>
                                    <option value="Manual"
                                        {{ ($property->transmission ?? '') == 'Manual' ? 'selected' : '' }}>Manual
                                    </option>
                                    <option value="CVT"
                                        {{ ($property->transmission ?? '') == 'CVT' ? 'selected' : '' }}>CVT</option>
                                    <option value="Semiautomática"
                                        {{ ($property->transmission ?? '') == 'Semiautomática' ? 'selected' : '' }}>
                                        Semiautomática</option>
                                </select>
                            </div>
                        </div>
                        <h6 class="text-muted mb-3 mt-3"><i class="fa fa-cog"></i> Especificaciones Técnicas</h6>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Cilindraje (CC)</label>
                                <input value="{{ $property->engine_cc ?? '' }}" class="form-control" type="text"
                                    name="engine_cc">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Cap. tanque</label>
                                <input value="{{ $property->fuel_tank_capacity ?? '' }}" class="form-control"
                                    type="text" name="fuel_tank_capacity">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Tracción</label>
                                <select class="form-control" name="drivetrain">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="4x2"
                                        {{ ($property->drivetrain ?? '') == '4x2' ? 'selected' : '' }}>4x2</option>
                                    <option value="4x4"
                                        {{ ($property->drivetrain ?? '') == '4x4' ? 'selected' : '' }}>4x4</option>
                                    <option value="AWD"
                                        {{ ($property->drivetrain ?? '') == 'AWD' ? 'selected' : '' }}>AWD</option>
                                    <option value="FWD"
                                        {{ ($property->drivetrain ?? '') == 'FWD' ? 'selected' : '' }}>FWD</option>
                                    <option value="RWD"
                                        {{ ($property->drivetrain ?? '') == 'RWD' ? 'selected' : '' }}>RWD</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Llantas</label>
                                <input value="{{ $property->tires ?? '' }}" class="form-control" type="text"
                                    name="tires">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Puertas</label>
                                <input value="{{ $property->doors ?? '' }}" class="form-control" type="text"
                                    name="doors">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Pasajeros</label>
                                <input value="{{ $property->passengers ?? '' }}" class="form-control" type="text"
                                    name="passengers">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Condición</label>
                                <select class="form-control" name="condition">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Nuevo"
                                        {{ ($property->condition ?? '') == 'Nuevo' ? 'selected' : '' }}>Nuevo</option>
                                    <option value="Usado"
                                        {{ ($property->condition ?? '') == 'Usado' ? 'selected' : '' }}>Usado</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Placa</label>
                                <input value="{{ $property->plate ?? '' }}" class="form-control" type="text"
                                    name="plate">
                            </div>
                        </div>
                    </div>

                    {{-- Precio y moneda --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-money"></i> Precio</h6>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Precio <span class="text-danger">*</span></label>
                            <input value="{{ $property->price }}" class="form-control" required type="text"
                                name="price">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Moneda</label>
                            <select class="form-control" name="currency">
                                <option value="CRC"
                                    {{ ($property->currency ?? 'CRC') == 'CRC' ? 'selected' : '' }}>₡ Colones (CRC)
                                </option>
                                <option value="USD" {{ ($property->currency ?? '') == 'USD' ? 'selected' : '' }}>$
                                    Dólares (USD)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Estado</label>
                            <select class="form-control" name="status">
                                <option value="available"
                                    {{ ($property->status ?? 'available') == 'available' ? 'selected' : '' }}>
                                    Disponible</option>
                                <option value="reserved"
                                    {{ ($property->status ?? '') == 'reserved' ? 'selected' : '' }}>Reservado</option>
                                <option value="negotiating"
                                    {{ ($property->status ?? '') == 'negotiating' ? 'selected' : '' }}>En negociación
                                </option>
                                <option value="sold" {{ ($property->status ?? '') == 'sold' ? 'selected' : '' }}>
                                    Vendido</option>
                                <option value="rented" {{ ($property->status ?? '') == 'rented' ? 'selected' : '' }}>
                                    Alquilado</option>
                                <option value="inactive"
                                    {{ ($property->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    {{-- Imagen principal --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-image"></i> Imagen principal</h6>
                    <div class="form-group">
                        <img class="card-img-top img-fluid w-50 mb-2"
                            src="{{ isset($property->image) ? route('file', $property->image) : url('images/producto-sin-imagen.PNG') }}"
                            style="max-height: 200px; object-fit: cover;">
                        <div class="custom-file">
                            <input type="file" class="form-control-file" name="image" accept="image/*">
                            <small class="form-text text-muted">Dejar vacío para mantener la imagen actual</small>
                        </div>
                    </div>

                    {{-- Galería de imágenes adicionales --}}
                    <h6 class="text-muted mb-3 mt-3"><i class="fa fa-images"></i> Galería adicional</h6>

                    @if ($property->images && $property->images->count() > 0)
                        <div class="row mb-2">
                            @foreach ($property->images as $img)
                                <div class="col-3 mb-2 text-center">
                                    <img src="{{ route('file', $img->image) }}" class="img-thumbnail"
                                        style="height: 80px; width: 100%; object-fit: cover;">

                                    {{-- Botón que dispara un form externo (NO anidado) --}}
                                    <button type="submit" class="btn btn-sm btn-outline-danger mt-1"
                                        title="Eliminar" form="delete-img-{{ $property->id }}-{{ $img->id }}"
                                        onclick="return confirm('¿Eliminar esta imagen?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="form-group">
                        <input type="file" class="form-control-file" name="additional_images[]" accept="image/*"
                            multiple>
                        <small class="form-text text-muted">Puede seleccionar múltiples imágenes</small>
                    </div>


                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
                {{-- Forms de delete (FUERA del form principal) --}}
                @if ($property->images && $property->images->count() > 0)
                    @foreach ($property->images as $img)
                        <form id="delete-img-{{ $property->id }}-{{ $img->id }}"
                            action="{{ url('/property/' . $property->id . '/image/' . $img->id) }}" method="POST"
                            style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                @endif

            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var sel = document.querySelector('#editProperty{{ $property->id }} .editPropertyType');
        if (sel) {
            sel.addEventListener('change', function() {
                toggleEditFields{{ $property->id }}(this.value);
            });
            toggleEditFields{{ $property->id }}(sel.value);
        }

        function toggleEditFields{{ $property->id }}(type) {
            var re = document.getElementById('editFieldsRealEstate{{ $property->id }}');
            var ve = document.getElementById('editFieldsVehicle{{ $property->id }}');
            var code = document.querySelector('.edit-field-realestate-{{ $property->id }}');
            if (type === 'vehicle') {
                re.style.display = 'none';
                ve.style.display = 'block';
                if (code) code.style.display = 'none';
                re.querySelectorAll('input, select').forEach(function(el) {
                    el.removeAttribute('required');
                });
            } else {
                re.style.display = 'block';
                ve.style.display = 'none';
                if (code) code.style.display = 'block';
            }
        }
    })();
</script>
