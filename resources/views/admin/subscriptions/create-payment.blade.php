@extends('admin.main')
@section('title', 'Registrar Pago')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-money"></i> Registrar Pago</h4>
            <a href="{{ route('admin.subscriptions.payments', $subscription) }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.subscriptions.store-payment', $subscription) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Método de Pago <span class="text-danger">*</span></label>
                                    <select name="payment_method" class="form-control" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="sinpe" {{ old('payment_method') == 'sinpe' ? 'selected' : '' }}>SINPE Móvil</option>
                                        <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transferencia Bancaria</option>
                                    </select>
                                    @error('payment_method') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Monto <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <select name="currency" class="form-control">
                                                <option value="CRC" {{ old('currency', 'CRC') == 'CRC' ? 'selected' : '' }}>₡</option>
                                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>$</option>
                                            </select>
                                        </div>
                                        <input type="number" name="amount" class="form-control" required
                                               value="{{ old('amount', $subscription->package->price ?? '') }}"
                                               step="0.01" min="0">
                                    </div>
                                    @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Número de Referencia</label>
                                <input type="text" name="reference_number" class="form-control"
                                       value="{{ old('reference_number') }}"
                                       placeholder="Ej: SINPE-123456 o Ref. transferencia">
                                @error('reference_number') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label>Comprobante de Pago <span class="text-danger">*</span></label>
                                <input type="file" name="proof_image" class="form-control-file" required accept="image/*,.pdf">
                                <small class="form-text text-muted">Sube una imagen o PDF del comprobante (SINPE, voucher, recibo)</small>
                                @error('proof_image') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label>Notas adicionales</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Notas opcionales sobre el pago">{{ old('notes') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-paper-plane"></i> Enviar Comprobante
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-header"><strong>Datos de Pago</strong></div>
                    <div class="card-body">
                        <p><strong>Paquete:</strong> {{ $subscription->package->name ?? 'N/A' }}</p>
                        <p><strong>Precio:</strong> {{ $subscription->package->formatted_price ?? 'N/A' }}</p>
                        <hr>
                        <p class="small text-muted"><i class="fa fa-info-circle"></i>
                            El pago será revisado por un administrador. Recibirás confirmación cuando sea aprobado.
                        </p>
                        <hr>
                        <p class="small"><strong>SINPE Móvil:</strong></p>
                        <p class="small text-muted">Número: Consultar con administrador</p>
                        <p class="small"><strong>Transferencia:</strong></p>
                        <p class="small text-muted">Cuenta: Consultar con administrador</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
