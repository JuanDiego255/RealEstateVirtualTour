@extends('admin.main')
@section('title', 'Negociación')
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fa fa-comments"></i> Negociación</h4>
            <div>
                @if($isOwner)
                    <a href="{{ route('admin.bolsa.incoming') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Recibidas</a>
                @else
                    <a href="{{ route('admin.bolsa.my-requests') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Mis Solicitudes</a>
                @endif
            </div>
        </div>

        <div class="row">
            {{-- Chat / Negociación --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Conversación</strong>
                        <span class="badge badge-{{ $commissionRequest->status === 'accepted' ? 'success' : ($commissionRequest->status === 'negotiating' ? 'info' : 'warning') }}">
                            {{ ucfirst($commissionRequest->status) }}
                        </span>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;" id="chatContainer">
                        {{-- Mensaje inicial --}}
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $commissionRequest->requester->name ?? 'Solicitante' }}</strong>
                                <small class="text-muted">{{ $commissionRequest->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="mb-1">{{ $commissionRequest->initial_message }}</p>
                            <small class="text-primary"><strong>Comisión propuesta: {{ $commissionRequest->proposed_commission }}%</strong></small>
                        </div>

                        {{-- Mensajes de negociación --}}
                        @foreach($negotiations as $msg)
                            @php
                                $isMine = $msg->user_id === Auth::id();
                            @endphp
                            <div class="mb-3 p-3 rounded {{ $isMine ? 'bg-primary text-white ml-4' : 'bg-light mr-4' }}">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $msg->user->name ?? 'Usuario' }}</strong>
                                    <small class="{{ $isMine ? 'text-white-50' : 'text-muted' }}">{{ $msg->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <p class="mb-1">{{ $msg->message }}</p>
                                @if($msg->counter_offer_percentage)
                                    <span class="badge {{ $isMine ? 'badge-light' : 'badge-primary' }}">
                                        Contraoferta: {{ $msg->counter_offer_percentage }}%
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Formulario de mensaje --}}
                    @if(in_array($commissionRequest->status, ['pending', 'negotiating']))
                        <div class="card-footer">
                            <form action="{{ route('admin.bolsa.send-message', $commissionRequest) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <textarea name="message" class="form-control" rows="2" required placeholder="Escribe tu mensaje..."></textarea>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="form-inline">
                                        <label class="mr-2 small">Contraoferta (%):</label>
                                        <input type="number" name="counter_offer_percentage" class="form-control form-control-sm" style="width: 80px;"
                                               step="0.5" min="0" max="100" placeholder="Ej: 5">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fa fa-paper-plane"></i> Enviar
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Acciones del propietario --}}
                @if($isOwner && in_array($commissionRequest->status, ['pending', 'negotiating']))
                    <div class="card mt-3">
                        <div class="card-body d-flex justify-content-center">
                            <form action="{{ route('admin.bolsa.accept', $commissionRequest) }}" method="POST" class="d-inline mr-2">
                                @csrf
                                <button class="btn btn-success" onclick="return confirm('¿Aceptar esta solicitud de comisión?')">
                                    <i class="fa fa-check"></i> Aceptar Solicitud
                                </button>
                            </form>
                            <form action="{{ route('admin.bolsa.reject', $commissionRequest) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-danger" onclick="return confirm('¿Rechazar esta solicitud?')">
                                    <i class="fa fa-times"></i> Rechazar
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Cancelar (solicitante) --}}
                @if(!$isOwner && $commissionRequest->status === 'pending')
                    <div class="card mt-3">
                        <div class="card-body text-center">
                            <form action="{{ route('admin.bolsa.cancel', $commissionRequest) }}" method="POST">
                                @csrf
                                <button class="btn btn-outline-danger" onclick="return confirm('¿Cancelar tu solicitud?')">
                                    <i class="fa fa-times"></i> Cancelar Solicitud
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Info de la propiedad --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header"><strong>Propiedad</strong></div>
                    @if($commissionRequest->property->image)
                        <img src="{{ $commissionRequest->property->image_url }}" class="card-img-top" style="height: 150px; object-fit: cover;">
                    @endif
                    <div class="card-body">
                        <h5>{{ $commissionRequest->property->name }}</h5>
                        <h4 class="text-primary">{{ $commissionRequest->property->formatted_price }}</h4>
                        <hr>
                        <p class="small"><strong>Comisión ofrecida:</strong> {{ $commissionRequest->property->commission_percentage }}%</p>
                        <p class="small"><strong>Comisión propuesta:</strong> {{ $commissionRequest->proposed_commission }}%</p>
                        @if($commissionRequest->final_commission)
                            <p class="small"><strong>Comisión acordada:</strong>
                                <span class="badge badge-success">{{ $commissionRequest->final_commission }}%</span>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header"><strong>Participantes</strong></div>
                    <div class="card-body small">
                        <p><strong>Propietario:</strong><br>{{ $commissionRequest->ownerCompany->name ?? 'N/A' }}<br>{{ $commissionRequest->owner->name ?? '' }}</p>
                        <hr>
                        <p><strong>Solicitante:</strong><br>{{ $commissionRequest->requesterCompany->name ?? 'N/A' }}<br>{{ $commissionRequest->requester->name ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    // Auto-scroll al final del chat
    var chatContainer = document.getElementById('chatContainer');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
</script>
@endpush
