@extends('admin.main')
@section('title', 'WhatsApp — Configuración del bot')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fa fa-whatsapp" style="color:#25d366"></i> Configuración del bot</h4>
        <a href="{{ route('admin.whatsapp.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-comments"></i> Ver conversaciones
        </a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row">
        {{-- ── Columna principal: tono, reglas, cierre, relevo ── --}}
        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.whatsapp.settings.update') }}">
                @csrf
                @method('PUT')

                <div class="card mb-4">
                    <div class="card-header"><strong>Datos del negocio</strong></div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Nombre de la tienda</label>
                                <input type="text" name="store_name" class="form-control" value="{{ old('store_name', $settings->store_name) }}" placeholder="Ej: Autos del Valle">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Correo para avisos de relevo</label>
                                <input type="email" name="notify_email" class="form-control" value="{{ old('notify_email', $settings->notify_email) }}" placeholder="ventas@tunegocio.com">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><strong>Tono del bot</strong>
                        <small class="text-muted d-block">Cómo habla tu negocio. Podés escribirlo a mano o generarlo con capturas (panel de la derecha).</small>
                    </div>
                    <div class="card-body">
                        <textarea name="training_profile" rows="8" class="form-control" placeholder="Ej: Tuteamos, saludamos con '¡Hola! 👋', somos cercanos pero profesionales...">{{ old('training_profile', $settings->training_profile) }}</textarea>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><strong>Reglas del negocio</strong>
                        <small class="text-muted d-block">Lo que el bot siempre debe (o nunca debe) hacer.</small>
                    </div>
                    <div class="card-body">
                        <textarea name="custom_rules" rows="5" class="form-control" placeholder="Ej: No damos descuentos por WhatsApp. Los precios no incluyen traspaso...">{{ old('custom_rules', $settings->custom_rules) }}</textarea>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><strong>Cómo se cierra una compra</strong>
                        <small class="text-muted d-block">Los pasos para concretar. El bot los sigue y, si toca acción humana, hace relevo.</small>
                    </div>
                    <div class="card-body">
                        <textarea name="order_instructions" rows="5" class="form-control" placeholder="Ej: 1) Confirmar interés y datos. 2) Coordinar visita al local. 3) Un asesor cierra el trato...">{{ old('order_instructions', $settings->order_instructions) }}</textarea>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><strong>Cuándo entra una persona</strong>
                        <small class="text-muted d-block">El bot pasa el chat a un humano en estos casos.</small>
                    </div>
                    <div class="card-body">
                        @php
                            $switches = [
                                'asks_for_human'   => 'Si piden hablar con una persona',
                                'complaint'        => 'Si hay un reclamo o queja',
                                'asks_past_order'  => 'Si preguntan por algo ya comprado',
                                'sends_receipt'    => 'Si mandan un comprobante de pago',
                                'sends_voice_note' => 'Si mandan una nota de voz',
                                'not_found'        => 'Si no encuentra lo que piden',
                            ];
                        @endphp
                        <div class="form-row">
                            @foreach($switches as $key => $label)
                                <div class="form-group col-md-6">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="handoff[{{ $key }}]" value="0">
                                        <input type="checkbox" class="custom-control-input" id="ho_{{ $key }}" name="handoff[{{ $key }}]" value="1" {{ !empty($handoff[$key]) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="ho_{{ $key }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-group">
                            <label>Palabras clave que fuerzan el relevo (separadas por coma)</label>
                            <input type="text" name="handoff[keywords]" class="form-control" value="{{ $handoff['keywords'] ?? '' }}" placeholder="reclamo, factura, devolución">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Reanudar el bot tras (horas sin respuesta humana)</label>
                                <input type="number" min="0" name="handoff[resume_after_h]" class="form-control" value="{{ $handoff['resume_after_h'] ?? 2 }}">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>Mensaje al pasar a una persona</label>
                            <textarea name="handoff[handoff_message]" rows="2" class="form-control">{{ $handoff['handoff_message'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary"><i class="fa fa-save"></i> Guardar configuración</button>
            </form>
        </div>

        {{-- ── Columna lateral: consumo + entrenamiento por capturas + promociones ── --}}
        <div class="col-lg-4">
            @if($billing)
                <div class="card mb-4">
                    <div class="card-header"><strong>Consumo del mes</strong></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span>Conversaciones</span>
                            <strong>{{ number_format($billing['used']) }}@if($billing['included'] > 0) / {{ number_format($billing['included']) }}@endif</strong>
                        </div>
                        @if($billing['included'] > 0)
                            @php $pct = min(100, round($billing['used'] / max(1, $billing['included']) * 100)); @endphp
                            <div class="progress my-2" style="height:8px;">
                                <div class="progress-bar {{ $pct >= 100 ? 'bg-danger' : ($pct >= 80 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $pct }}%"></div>
                            </div>
                        @endif
                        @if($billing['extras'] > 0)
                            <div class="d-flex justify-content-between small text-muted">
                                <span>Extras</span><span>{{ number_format($billing['extras']) }} (${{ number_format($billing['extrasCost'], 2) }})</span>
                            </div>
                        @endif
                        @if($billing['exceeded'])
                            <div class="alert alert-warning py-2 px-2 mt-2 mb-0 small">
                                <i class="fa fa-exclamation-triangle"></i>
                                El bot está pausado este mes por {{ $billing['capReached'] ? 'alcanzar el tope de gasto' : 'agotar el cupo del plan' }}.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header"><strong>Entrenar por capturas</strong>
                    <small class="text-muted d-block">Subí capturas de chats reales; la IA aprende el tono. Las imágenes se borran tras analizarlas.</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.whatsapp.settings.train') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <input type="file" name="screenshots[]" class="form-control-file" accept="image/*" multiple required>
                            <small class="text-muted">Hasta 8 imágenes, máx 5 MB c/u.</small>
                        </div>
                        <div class="form-group">
                            <label>Indicaciones (opcional)</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Ej: enfocate en cómo cerramos ventas"></textarea>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="replace" name="replace" value="1">
                                <label class="custom-control-label" for="replace">Reemplazar el perfil actual (en vez de mejorarlo)</label>
                            </div>
                        </div>
                        <button class="btn btn-success btn-block"><i class="fa fa-magic"></i> Generar perfil de tono</button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><strong>Promociones</strong>
                    <small class="text-muted d-block">El bot solo menciona las vigentes.</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.whatsapp.promotions.store') }}" class="mb-3">
                        @csrf
                        <div class="form-group">
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Título (ej: Bono de traspaso)" required>
                        </div>
                        <div class="form-group">
                            <textarea name="description" rows="2" class="form-control form-control-sm" placeholder="Detalle de la promo" required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label class="small mb-0">Desde</label>
                                <input type="date" name="starts_at" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-6">
                                <label class="small mb-0">Hasta</label>
                                <input type="date" name="ends_at" class="form-control form-control-sm">
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary btn-block"><i class="fa fa-plus"></i> Agregar promoción</button>
                    </form>

                    @forelse($promotions as $promo)
                        <div class="border rounded p-2 mb-2 {{ $promo->active ? '' : 'bg-light text-muted' }}">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $promo->title }}</strong>
                                <span class="badge badge-{{ $promo->active ? 'success' : 'secondary' }}">{{ $promo->active ? 'Activa' : 'Inactiva' }}</span>
                            </div>
                            <div class="small">{{ $promo->description }}</div>
                            @if($promo->starts_at || $promo->ends_at)
                                <div class="small text-muted">
                                    {{ optional($promo->starts_at)->format('d/m/Y') ?: '—' }} → {{ optional($promo->ends_at)->format('d/m/Y') ?: '—' }}
                                </div>
                            @endif
                            <div class="mt-1">
                                <form method="POST" action="{{ route('admin.whatsapp.promotions.toggle', $promo) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-xs btn-link p-0 mr-2">{{ $promo->active ? 'Desactivar' : 'Activar' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.whatsapp.promotions.destroy', $promo) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta promoción?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-link text-danger p-0">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Todavía no hay promociones.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
