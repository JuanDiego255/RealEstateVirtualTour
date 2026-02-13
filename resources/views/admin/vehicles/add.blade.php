<div class="modal fade" id="addVehicle">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-car mr-2"></i>Agregar Vehículo</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('addVehicle') }}" method="POST" enctype="multipart/form-data">
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

                    <h6 class="text-muted mb-3 border-bottom pb-2">
                        <i class="fa fa-info-circle mr-1"></i>Información General
                    </h6>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Categoría <span class="text-danger">*</span></label>
                            <select class="form-control form-control-lg input-rounded mb-2" name="category_id" required>
                                <option value="">-- Seleccionar Categoría --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nombre / Título <span class="text-danger">*</span></label>
                            <input class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="name" placeholder="Ej: Toyota Corolla 2024 Cross">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Marca <span class="text-danger">*</span></label>
                            <input class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="brand" placeholder="Ej: Toyota">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Modelo <span class="text-danger">*</span></label>
                            <input class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="model" placeholder="Ej: Corolla Cross">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Año <span class="text-danger">*</span></label>
                            <input class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="year" placeholder="Ej: 2024">
                        </div>
                    </div>

                    <h6 class="text-muted mb-3 mt-2 border-bottom pb-2">
                        <i class="fa fa-cogs mr-1"></i>Especificaciones Técnicas
                    </h6>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Color</label>
                            <input class="form-control form-control-lg input-rounded mb-2" type="text" name="color"
                                placeholder="Ej: Blanco Perla">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Kilometraje</label>
                            <input class="form-control form-control-lg input-rounded mb-2" type="text"
                                name="mileage_km" placeholder="Ej: 15000">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Cap. Tanque (L)</label>
                            <input class="form-control form-control-lg input-rounded mb-2" type="text"
                                name="fuel_tank_capacity" placeholder="Ej: 50">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tipo de Combustible</label>
                            <select class="form-control form-control-lg input-rounded mb-2" name="fuel_type">
                                <option value="">-- Seleccionar --</option>
                                <option value="Gasolina">Gasolina</option>
                                <option value="Diésel">Diésel</option>
                                <option value="Híbrido">Híbrido</option>
                                <option value="Eléctrico">Eléctrico</option>
                                <option value="GLP">GLP</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Motor CC</label>
                            <input class="form-control form-control-lg input-rounded mb-2" type="text"
                                name="engine_cc" placeholder="Ej: 1800">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Transmisión</label>
                            <select class="form-control form-control-lg input-rounded mb-2" name="transmission">
                                <option value="">-- Seleccionar --</option>
                                <option value="Manual">Manual</option>
                                <option value="Automática">Automática</option>
                                <option value="CVT">CVT</option>
                                <option value="Semi-automática">Semi-automática</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Puertas</label>
                            <input class="form-control form-control-lg input-rounded mb-2" type="number" name="doors"
                                placeholder="Ej: 4">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Pasajeros</label>
                            <input class="form-control form-control-lg input-rounded mb-2" type="number"
                                name="passengers" placeholder="Ej: 5">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Llantas</label>
                            <input class="form-control form-control-lg input-rounded mb-2" type="text" name="tires"
                                placeholder="Ej: 205/55 R16">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Tracción</label>
                            <select class="form-control form-control-lg input-rounded mb-2" name="drivetrain">
                                <option value="">-- Seleccionar --</option>
                                <option value="2WD">2WD</option>
                                <option value="4WD">4WD</option>
                                <option value="AWD">AWD</option>
                                <option value="FWD">FWD</option>
                                <option value="RWD">RWD</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="text-muted mb-3 mt-2 border-bottom pb-2">
                        <i class="fa fa-money-bill mr-1"></i>Datos Comerciales
                    </h6>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Precio <span class="text-danger">*</span></label>
                            <input class="form-control form-control-lg input-rounded mb-2" required type="text"
                                name="price" placeholder="Ej: 25000000">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Condición</label>
                            <select class="form-control form-control-lg input-rounded mb-2" name="condition">
                                <option value="">-- Seleccionar --</option>
                                <option value="Nuevo">Nuevo</option>
                                <option value="Usado">Usado</option>
                                <option value="Semi-nuevo">Semi-nuevo</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Placa</label>
                            <input class="form-control form-control-lg input-rounded mb-2" type="text" name="plate"
                                placeholder="Ej: ABC-123">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <label>Imagen del Vehículo</label>
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
