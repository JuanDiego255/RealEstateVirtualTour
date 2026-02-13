<div class="modal fade" id="editVehicle{{ $vehicle->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-edit mr-2"></i>Editar Vehículo</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" action="{{ url('vehicle/update/' . $vehicle->id) }}" method="post"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}

                    <h6 class="text-muted mb-3 border-bottom pb-2">
                        <i class="fa fa-info-circle mr-1"></i>Información General
                    </h6>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Categoría <span class="text-danger">*</span></label>
                            <select class="form-control form-control-lg input-rounded mb-2" name="category_id" required>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ $vehicle->category_id == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nombre / Título <span class="text-danger">*</span></label>
                            <input value="{{ $vehicle->name }}"
                                class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Marca <span class="text-danger">*</span></label>
                            <input value="{{ $vehicle->brand }}"
                                class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="brand">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Modelo <span class="text-danger">*</span></label>
                            <input value="{{ $vehicle->model }}"
                                class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="model">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Año <span class="text-danger">*</span></label>
                            <input value="{{ $vehicle->year }}"
                                class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="year">
                        </div>
                    </div>

                    <h6 class="text-muted mb-3 mt-2 border-bottom pb-2">
                        <i class="fa fa-cogs mr-1"></i>Especificaciones Técnicas
                    </h6>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Color</label>
                            <input value="{{ $vehicle->color }}"
                                class="form-control form-control-lg input-rounded mb-2" type="text" name="color">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Kilometraje</label>
                            <input value="{{ $vehicle->mileage_km }}"
                                class="form-control form-control-lg input-rounded mb-2" type="text"
                                name="mileage_km">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Cap. Tanque (L)</label>
                            <input value="{{ $vehicle->fuel_tank_capacity }}"
                                class="form-control form-control-lg input-rounded mb-2" type="text"
                                name="fuel_tank_capacity">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tipo de Combustible</label>
                            <select class="form-control form-control-lg input-rounded mb-2" name="fuel_type">
                                <option value="">-- Seleccionar --</option>
                                <option value="Gasolina" {{ $vehicle->fuel_type == 'Gasolina' ? 'selected' : '' }}>
                                    Gasolina</option>
                                <option value="Diésel" {{ $vehicle->fuel_type == 'Diésel' ? 'selected' : '' }}>
                                    Diésel</option>
                                <option value="Híbrido" {{ $vehicle->fuel_type == 'Híbrido' ? 'selected' : '' }}>
                                    Híbrido</option>
                                <option value="Eléctrico" {{ $vehicle->fuel_type == 'Eléctrico' ? 'selected' : '' }}>
                                    Eléctrico</option>
                                <option value="GLP" {{ $vehicle->fuel_type == 'GLP' ? 'selected' : '' }}>GLP</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Motor CC</label>
                            <input value="{{ $vehicle->engine_cc }}"
                                class="form-control form-control-lg input-rounded mb-2" type="text"
                                name="engine_cc">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Transmisión</label>
                            <select class="form-control form-control-lg input-rounded mb-2" name="transmission">
                                <option value="">-- Seleccionar --</option>
                                <option value="Manual" {{ $vehicle->transmission == 'Manual' ? 'selected' : '' }}>
                                    Manual</option>
                                <option value="Automática"
                                    {{ $vehicle->transmission == 'Automática' ? 'selected' : '' }}>
                                    Automática</option>
                                <option value="CVT" {{ $vehicle->transmission == 'CVT' ? 'selected' : '' }}>CVT
                                </option>
                                <option value="Semi-automática"
                                    {{ $vehicle->transmission == 'Semi-automática' ? 'selected' : '' }}>
                                    Semi-automática</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Puertas</label>
                            <input value="{{ $vehicle->doors }}"
                                class="form-control form-control-lg input-rounded mb-2" type="number" name="doors">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Pasajeros</label>
                            <input value="{{ $vehicle->passengers }}"
                                class="form-control form-control-lg input-rounded mb-2" type="number"
                                name="passengers">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Llantas</label>
                            <input value="{{ $vehicle->tires }}"
                                class="form-control form-control-lg input-rounded mb-2" type="text" name="tires">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Tracción</label>
                            <select class="form-control form-control-lg input-rounded mb-2" name="drivetrain">
                                <option value="">-- Seleccionar --</option>
                                <option value="2WD" {{ $vehicle->drivetrain == '2WD' ? 'selected' : '' }}>2WD
                                </option>
                                <option value="4WD" {{ $vehicle->drivetrain == '4WD' ? 'selected' : '' }}>4WD
                                </option>
                                <option value="AWD" {{ $vehicle->drivetrain == 'AWD' ? 'selected' : '' }}>AWD
                                </option>
                                <option value="FWD" {{ $vehicle->drivetrain == 'FWD' ? 'selected' : '' }}>FWD
                                </option>
                                <option value="RWD" {{ $vehicle->drivetrain == 'RWD' ? 'selected' : '' }}>RWD
                                </option>
                            </select>
                        </div>
                    </div>

                    <h6 class="text-muted mb-3 mt-2 border-bottom pb-2">
                        <i class="fa fa-money-bill mr-1"></i>Datos Comerciales
                    </h6>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Precio <span class="text-danger">*</span></label>
                            <input value="{{ $vehicle->price }}"
                                class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="price">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Condición</label>
                            <select class="form-control form-control-lg input-rounded mb-2" name="condition">
                                <option value="">-- Seleccionar --</option>
                                <option value="Nuevo" {{ $vehicle->condition == 'Nuevo' ? 'selected' : '' }}>Nuevo
                                </option>
                                <option value="Usado" {{ $vehicle->condition == 'Usado' ? 'selected' : '' }}>Usado
                                </option>
                                <option value="Semi-nuevo" {{ $vehicle->condition == 'Semi-nuevo' ? 'selected' : '' }}>
                                    Semi-nuevo</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Placa</label>
                            <input value="{{ $vehicle->plate }}"
                                class="form-control form-control-lg input-rounded mb-2" type="text" name="plate">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <label>Imagen</label>
                            @if ($vehicle->image)
                                <img class="card-img-top img-fluid w-50 mb-2"
                                    src="{{ route('file', $vehicle->image) }}">
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
