@extends('admin.main')
@section('title', 'Registrar Venta')
@section('content')
    @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show"><strong>{{ Session::get('error') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-check-circle"></i> Registrar Venta</h4>
            <a href="{{ route('admin.bolsa.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Dashboard</a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.bolsa.register-sale', $property) }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Precio de Venta <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <select name="currency" class="form-control">
                                                <option value="CRC" {{ old('currency', $property->currency) == 'CRC' ? 'selected' : '' }}>₡</option>
                                                <option value="USD" {{ old('currency', $property->currency) == 'USD' ? 'selected' : '' }}>$</option>
                                            </select>
                                        </div>
                                        <input type="number" name="sale_price" class="form-control" required
                                               value="{{ old('sale_price', $property->price) }}" step="0.01" min="0">
                                    </div>
                                    @error('sale_price') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Solicitud de comisión asociada</label>
                                    <select name="commission_request_id" class="form-control" id="commissionRequestSelect">
                                        <option value="">Sin solicitud (venta directa)</option>
                                        @foreach($commissionRequests ?? [] as $req)
                                            <option value="{{ $req->id }}" data-commission="{{ $req->final_commission ?? $req->proposed_commission }}"
                                                {{ old('commission_request_id') == $req->id ? 'selected' : '' }}>
                                                {{ $req->requesterCompany->name ?? 'N/A' }} — {{ $req->final_commission ?? $req->proposed_commission }}%
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>% Comisión Total</label>
                                    <div class="input-group">
                                        <input type="number" name="commission_percentage" class="form-control" id="commissionPct"
                                               value="{{ old('commission_percentage', $property->commission_percentage) }}"
                                               step="0.5" min="0" max="100">
                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Nombre del Comprador</label>
                                    <input type="text" name="buyer_name" class="form-control" value="{{ old('buyer_name') }}" placeholder="Nombre del comprador">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Notas de la venta</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Observaciones, detalles de la transacción...">{{ old('notes') }}</textarea>
                            </div>

                            {{-- Preview del cálculo --}}
                            <div class="card bg-light mb-3" id="commissionPreview">
                                <div class="card-body">
                                    <h6>Resumen de Comisiones</h6>
                                    <div class="row text-center" id="commissionSummary">
                                        <div class="col-md-4">
                                            <p class="mb-0 text-muted small">Comisión total</p>
                                            <h5 id="totalCommission">—</h5>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-0 text-muted small">Tu parte (propietario)</p>
                                            <h5 id="ownerCommission" class="text-success">—</h5>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-0 text-muted small">Parte agente externo</p>
                                            <h5 id="agentCommission" class="text-info">—</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('¿Registrar esta venta? Esta acción no se puede deshacer.')">
                                <i class="fa fa-check-circle"></i> Registrar Venta
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
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
                        <p class="small"><strong>Comisión configurada:</strong> {{ $property->commission_percentage }}%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    function updatePreview() {
        var price = parseFloat($('input[name="sale_price"]').val()) || 0;
        var pct = parseFloat($('#commissionPct').val()) || 0;
        var total = price * (pct / 100);

        var select = document.getElementById('commissionRequestSelect');
        var agentPct = 0;
        if (select.value) {
            var option = select.options[select.selectedIndex];
            agentPct = parseFloat(option.getAttribute('data-commission')) || 0;
        }

        var agentAmount = price * (agentPct / 100);
        var ownerAmount = total - agentAmount;
        if (ownerAmount < 0) ownerAmount = 0;

        var currency = $('select[name="currency"]').val() === 'USD' ? '$' : '₡';

        $('#totalCommission').text(currency + total.toLocaleString('es-CR', {maximumFractionDigits: 0}));
        $('#ownerCommission').text(currency + ownerAmount.toLocaleString('es-CR', {maximumFractionDigits: 0}));
        $('#agentCommission').text(agentPct > 0 ? currency + agentAmount.toLocaleString('es-CR', {maximumFractionDigits: 0}) : '—');
    }

    $('input[name="sale_price"], #commissionPct, #commissionRequestSelect, select[name="currency"]').on('change input', updatePreview);
    updatePreview();
</script>
@endpush
