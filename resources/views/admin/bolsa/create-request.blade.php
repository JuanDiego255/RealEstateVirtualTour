@extends('admin.main')
@section('title', 'Solicitar Comisión')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-handshake-o"></i> Solicitar Comisión</h4>
            <a href="{{ route('admin.bolsa.available') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.bolsa.store-request', $property) }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label>Comisión propuesta (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="proposed_commission" class="form-control" required
                                           value="{{ old('proposed_commission', $property->commission_percentage) }}"
                                           step="0.5" min="0.5" max="100">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    La propiedad ofrece hasta {{ $property->commission_percentage }}% de comisión
                                </small>
                                @error('proposed_commission') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label>Mensaje al propietario <span class="text-danger">*</span></label>
                                <textarea name="initial_message" class="form-control" rows="4" required
                                          placeholder="Preséntese y explique por qué desea vender esta propiedad. Incluya su experiencia y plan de venta.">{{ old('initial_message') }}</textarea>
                                @error('initial_message') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <button type="submit" class="btn btn-success" onclick="return confirm('¿Enviar solicitud de comisión?')">
                                <i class="fa fa-paper-plane"></i> Enviar Solicitud
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- Info de la propiedad --}}
                <div class="card">
                    <div class="card-header"><strong>Propiedad</strong></div>
                    @if($property->image)
                        <img src="{{ $property->image_url }}" class="card-img-top" style="height: 150px; object-fit: cover;">
                    @endif
                    <div class="card-body">
                        <h5>{{ $property->name }}</h5>
                        <h4 class="text-primary">{{ $property->formatted_price }}</h4>

                        <hr>
                        <p class="small"><strong>Tipo:</strong> {{ $property->property_type_name }}</p>
                        @if($property->location)
                            <p class="small"><strong>Ubicación:</strong> {{ $property->location }}</p>
                        @endif
                        <p class="small"><strong>Comisión máxima:</strong>
                            <span class="badge badge-success">{{ $property->commission_percentage }}%</span>
                        </p>
                        @if($property->category && $property->category->company)
                            <p class="small"><strong>Empresa:</strong> {{ $property->category->company->name }}</p>
                        @endif

                        @if($property->commission_notes)
                            <hr>
                            <p class="small text-muted">{{ $property->commission_notes }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
