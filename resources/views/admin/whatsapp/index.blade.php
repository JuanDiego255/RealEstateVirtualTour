@extends('admin.main')
@section('title', 'WhatsApp — Conversaciones')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fa fa-whatsapp" style="color:#25d366"></i> Conversaciones de WhatsApp</h4>
        <form method="GET" action="{{ route('admin.whatsapp.index') }}" class="form-inline">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Buscar por nombre o teléfono" value="{{ $search }}">
            <button class="btn btn-sm btn-primary ml-2"><i class="fa fa-search"></i></button>
        </form>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Estado del bot</th>
                        <th>Último mensaje</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($chats as $chat)
                    <tr>
                        <td>{{ $chat->contact_name ?: '—' }}</td>
                        <td>{{ $chat->phone }}</td>
                        <td>
                            @if($chat->bot_paused)
                                <span class="badge badge-warning">Pausado (equipo)</span>
                            @else
                                <span class="badge badge-success">Bot activo</span>
                            @endif
                            @if($chat->needs_human_at)
                                <span class="badge badge-danger" title="{{ $chat->needs_human_reason }}">Pidió persona</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:#666;">{{ $chat->last_message_at ? $chat->last_message_at->diffForHumans() : '—' }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.whatsapp.show', $chat) }}" class="btn btn-sm btn-outline-primary">Abrir chat</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aún no hay conversaciones.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $chats->links() }}</div>
</div>
@endsection
