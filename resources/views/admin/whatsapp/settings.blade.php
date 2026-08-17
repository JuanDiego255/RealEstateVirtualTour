@extends('admin.main')
@section('title', 'WhatsApp — Configuración del bot')
@section('content')
@include('admin.crm._ui')

<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-whatsapp" style="color:#25d366;"></i> Configuración del bot</h2>
            <p class="sub">Tono, reglas, cierre, relevo y promociones</p>
        </div>
        <a href="{{ route('admin.whatsapp.index') }}" class="action-btn secondary"><i class="fa fa-comments"></i> Ver conversaciones</a>
    </div>

    @if(session('success'))<div class="crm-alert success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="crm-alert danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="crm-alert danger"><ul style="margin:0; padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="crm-two-col">
        {{-- ── Columna principal ── --}}
        <div>
            <form method="POST" action="{{ route('admin.whatsapp.settings.update') }}">
                @csrf @method('PUT')

                <div class="crm-section">
                    <div class="crm-section-header"><h5><i class="fa fa-building-o"></i> Datos del negocio</h5></div>
                    <div class="crm-section-pad">
                        <div class="crm-form-row">
                            <div class="crm-form-group">
                                <label class="crm-label">Nombre de la tienda</label>
                                <input type="text" name="store_name" class="crm-input" value="{{ old('store_name', $settings->store_name) }}" placeholder="Ej: Autos del Valle">
                            </div>
                            <div class="crm-form-group">
                                <label class="crm-label">Correo para avisos de relevo</label>
                                <input type="email" name="notify_email" class="crm-input" value="{{ old('notify_email', $settings->notify_email) }}" placeholder="ventas@tunegocio.com">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="crm-section">
                    <div class="crm-section-header"><h5><i class="fa fa-comment-o"></i> Tono del bot</h5><span class="hint">cómo habla tu negocio</span></div>
                    <div class="crm-section-pad">
                        <textarea name="training_profile" rows="8" class="crm-textarea" placeholder="Ej: Tuteamos, saludamos con '¡Hola! 👋', somos cercanos pero profesionales...">{{ old('training_profile', $settings->training_profile) }}</textarea>
                    </div>
                </div>

                <div class="crm-section">
                    <div class="crm-section-header"><h5><i class="fa fa-list-ul"></i> Reglas del negocio</h5></div>
                    <div class="crm-section-pad">
                        <textarea name="custom_rules" rows="5" class="crm-textarea" placeholder="Ej: No damos descuentos por WhatsApp. Los precios no incluyen traspaso...">{{ old('custom_rules', $settings->custom_rules) }}</textarea>
                    </div>
                </div>

                <div class="crm-section">
                    <div class="crm-section-header"><h5><i class="fa fa-handshake-o"></i> Cómo se cierra una compra</h5></div>
                    <div class="crm-section-pad">
                        <textarea name="order_instructions" rows="5" class="crm-textarea" placeholder="Ej: 1) Confirmar interés y datos. 2) Coordinar visita al local. 3) Un asesor cierra el trato...">{{ old('order_instructions', $settings->order_instructions) }}</textarea>
                    </div>
                </div>

                <div class="crm-section">
                    <div class="crm-section-header"><h5><i class="fa fa-user-o"></i> Cuándo entra una persona</h5></div>
                    <div class="crm-section-pad">
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
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
                            @foreach($switches as $key => $label)
                                <label class="crm-toggle">
                                    <input type="hidden" name="handoff[{{ $key }}]" value="0">
                                    <input type="checkbox" name="handoff[{{ $key }}]" value="1" {{ !empty($handoff[$key]) ? 'checked' : '' }}>
                                    <span class="track"></span> {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <div class="crm-form-group">
                            <label class="crm-label">Palabras clave que fuerzan el relevo (separadas por coma)</label>
                            <input type="text" name="handoff[keywords]" class="crm-input" value="{{ $handoff['keywords'] ?? '' }}" placeholder="reclamo, factura, devolución">
                        </div>
                        <div class="crm-form-row">
                            <div class="crm-form-group" style="max-width:280px;">
                                <label class="crm-label">Reanudar el bot tras (horas sin respuesta humana)</label>
                                <input type="number" min="0" name="handoff[resume_after_h]" class="crm-input" value="{{ $handoff['resume_after_h'] ?? 2 }}">
                            </div>
                        </div>
                        <div class="crm-form-group" style="margin-bottom:0;">
                            <label class="crm-label">Mensaje al pasar a una persona</label>
                            <textarea name="handoff[handoff_message]" rows="2" class="crm-textarea">{{ $handoff['handoff_message'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <button class="action-btn primary"><i class="fa fa-save"></i> Guardar configuración</button>
            </form>
        </div>

        {{-- ── Columna lateral ── --}}
        <div>
            @if($billing)
                <div class="crm-section">
                    <div class="crm-section-header"><h5><i class="fa fa-bar-chart"></i> Consumo del mes</h5></div>
                    <div class="crm-section-pad">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="font-size:13px; color:#475569;">Conversaciones</span>
                            <strong>{{ number_format($billing['used']) }}@if($billing['included'] > 0) / {{ number_format($billing['included']) }}@endif</strong>
                        </div>
                        @if($billing['included'] > 0)
                            @php $pct = min(100, round($billing['used'] / max(1, $billing['included']) * 100)); @endphp
                            <div class="crm-progress"><span class="{{ $pct >= 100 ? 'pg-red' : ($pct >= 80 ? 'pg-amber' : 'pg-green') }}" style="width: {{ $pct }}%"></span></div>
                        @endif
                        @if($billing['extras'] > 0)
                            <div style="display:flex; justify-content:space-between; font-size:12px; color:#94a3b8; margin-top:8px;">
                                <span>Extras</span><span>{{ number_format($billing['extras']) }} (${{ number_format($billing['extrasCost'], 2) }})</span>
                            </div>
                        @endif
                        @if($billing['exceeded'])
                            <div class="crm-alert warning" style="margin:12px 0 0; font-size:12px;">
                                <i class="fa fa-exclamation-triangle"></i>
                                El bot está pausado este mes por {{ $billing['capReached'] ? 'alcanzar el tope de gasto' : 'agotar el cupo del plan' }}.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="crm-section">
                <div class="crm-section-header"><h5><i class="fa fa-magic"></i> Entrenar por capturas</h5></div>
                <div class="crm-section-pad">
                    <p class="crm-help" style="margin-bottom:12px;">Subí capturas de chats reales; la IA aprende el tono. Las imágenes se borran tras analizarlas.</p>
                    <form method="POST" action="{{ route('admin.whatsapp.settings.train') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="crm-form-group">
                            <input type="file" name="screenshots[]" accept="image/*" multiple required>
                            <div class="crm-help">Hasta 8 imágenes, máx 5 MB c/u.</div>
                        </div>
                        <div class="crm-form-group">
                            <label class="crm-label">Indicaciones (opcional)</label>
                            <textarea name="notes" rows="2" class="crm-textarea" placeholder="Ej: enfocate en cómo cerramos ventas"></textarea>
                        </div>
                        <label class="crm-toggle" style="margin-bottom:12px;">
                            <input type="checkbox" name="replace" value="1"><span class="track"></span> Reemplazar el perfil actual
                        </label>
                        <button class="action-btn success" style="width:100%; justify-content:center;"><i class="fa fa-magic"></i> Generar perfil de tono</button>
                    </form>
                </div>
            </div>

            <div class="crm-section">
                <div class="crm-section-header"><h5><i class="fa fa-tag"></i> Promociones</h5><span class="hint">solo las vigentes</span></div>
                <div class="crm-section-pad">
                    <form method="POST" action="{{ route('admin.whatsapp.promotions.store') }}" style="margin-bottom:16px;">
                        @csrf
                        <div class="crm-form-group">
                            <input type="text" name="title" class="crm-input" placeholder="Título (ej: Bono de traspaso)" required>
                        </div>
                        <div class="crm-form-group">
                            <textarea name="description" rows="2" class="crm-textarea" placeholder="Detalle de la promo" required></textarea>
                        </div>
                        <div class="crm-form-row">
                            <div class="crm-form-group">
                                <label class="crm-label">Desde</label>
                                <input type="date" name="starts_at" class="crm-input">
                            </div>
                            <div class="crm-form-group">
                                <label class="crm-label">Hasta</label>
                                <input type="date" name="ends_at" class="crm-input">
                            </div>
                        </div>
                        <button class="action-btn secondary" style="width:100%; justify-content:center;"><i class="fa fa-plus"></i> Agregar promoción</button>
                    </form>

                    @forelse($promotions as $promo)
                        <div style="border:1px solid #eef0f3; border-radius:12px; padding:12px; margin-bottom:10px; {{ $promo->active ? '' : 'opacity:.6;' }}">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <strong style="font-size:13px;">{{ $promo->title }}</strong>
                                <span class="crm-badge {{ $promo->active ? 'green' : 'slate' }}">{{ $promo->active ? 'Activa' : 'Inactiva' }}</span>
                            </div>
                            <div style="font-size:12px; color:#475569; margin-top:4px;">{{ $promo->description }}</div>
                            @if($promo->starts_at || $promo->ends_at)
                                <div class="crm-help">{{ optional($promo->starts_at)->format('d/m/Y') ?: '—' }} → {{ optional($promo->ends_at)->format('d/m/Y') ?: '—' }}</div>
                            @endif
                            <div style="margin-top:8px; display:flex; gap:6px;">
                                <form method="POST" action="{{ route('admin.whatsapp.promotions.toggle', $promo) }}">@csrf
                                    <button class="action-btn secondary xs">{{ $promo->active ? 'Desactivar' : 'Activar' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.whatsapp.promotions.destroy', $promo) }}" onsubmit="return confirm('¿Eliminar esta promoción?')">@csrf @method('DELETE')
                                    <button class="action-btn danger xs">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="crm-help">Todavía no hay promociones.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
