@extends('admin.main')
@section('title', 'Nueva Suscripción')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-id-card"></i> Nueva Suscripción</h4>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @if (Session::has('error'))
            <div class="alert alert-danger">{{ Session::get('error') }}</div>
        @endif

        <div class="card"><div class="card-body">
            <form action="{{ route('admin.subscriptions.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Empresa <span class="text-danger">*</span></label>
                        <select name="company_id" class="form-control" required>
                            <option value="">Seleccione empresa...</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Paquete <span class="text-danger">*</span></label>
                        <select name="package_id" class="form-control" required id="package-select">
                            <option value="">Seleccione paquete...</option>
                            @foreach($packages as $p)
                                <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-currency="{{ $p->currency }}"
                                        data-period="{{ $p->billing_period }}" data-trial="{{ $p->trial_days }}"
                                        {{ old('package_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} - {{ $p->formatted_price }}/{{ $p->billing_period_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Fecha de inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="starts_at" value="{{ old('starts_at', date('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Períodos a facturar <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="billing_periods" value="{{ old('billing_periods', 1) }}" min="1" max="12" required>
                        <small class="text-muted" id="period-info">Se calculará automáticamente la fecha de vencimiento</small>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Método de pago <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-control" required>
                            <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transferencia</option>
                            <option value="sinpe" {{ old('payment_method') == 'sinpe' ? 'selected' : '' }}>SINPE Móvil</option>
                            <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Tarjeta</option>
                        </select>
                    </div>

                    <div class="form-group col-md-12">
                        <label>Notas</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Notas opcionales">{{ old('notes') }}</textarea>
                    </div>

                    <div class="form-group col-md-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="auto_approve" name="auto_approve" value="1" checked>
                            <label class="custom-control-label" for="auto_approve">Aprobar automáticamente</label>
                        </div>
                    </div>

                    <div class="form-group col-md-4" id="trial-option" style="display:none;">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="start_trial" name="start_trial" value="1">
                            <label class="custom-control-label" for="start_trial">Iniciar con período de prueba</label>
                        </div>
                        <small class="text-muted" id="trial-days-info"></small>
                    </div>

                    <div class="form-group col-md-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="auto_renew" name="auto_renew" value="1">
                            <label class="custom-control-label" for="auto_renew">Auto-renovar</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Crear Suscripción</button>
            </form>
        </div></div>
    </div>
@endsection

@push('script')
<script>
    $('#package-select').on('change', function() {
        var opt = $(this).find(':selected');
        var trial = parseInt(opt.data('trial')) || 0;
        if (trial > 0) {
            $('#trial-option').show();
            $('#trial-days-info').text(trial + ' días de prueba');
        } else {
            $('#trial-option').hide();
            $('#start_trial').prop('checked', false);
        }
    }).trigger('change');
</script>
@endpush
