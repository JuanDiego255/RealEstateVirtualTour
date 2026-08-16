@extends('admin.main')
@section('title', 'Chat — ' . ($chat->contact_name ?: $chat->phone))
@section('content')
<style>
    .wa-thread { background:#e9edef; border-radius:12px; padding:16px; height:60vh; overflow-y:auto; }
    .wa-row { display:flex; margin-bottom:8px; }
    .wa-row.in { justify-content:flex-start; }
    .wa-row.out { justify-content:flex-end; }
    .wa-bubble { max-width:70%; padding:8px 12px; border-radius:10px; box-shadow:0 1px 1px rgba(0,0,0,.08); font-size:14px; }
    .wa-bubble.client { background:#fff; }
    .wa-bubble.bot    { background:#d9fdd3; }
    .wa-bubble.human  { background:#cfe9ff; }
    .wa-text { white-space:pre-wrap; word-break:break-word; }
    .wa-meta { font-size:10px; color:#667; margin-top:3px; text-align:right; }
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('admin.whatsapp.index') }}" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i></a>
            <strong class="ml-2">{{ $chat->contact_name ?: 'Sin nombre' }}</strong>
            <span class="text-muted">· {{ $chat->phone }}</span>
            @if($chat->bot_paused)
                <span class="badge badge-warning ml-2">Bot pausado</span>
            @else
                <span class="badge badge-success ml-2">Bot activo</span>
            @endif
        </div>
        <div>
            @if($chat->bot_paused)
                <form method="POST" action="{{ route('admin.whatsapp.resume', $chat) }}" class="d-inline">@csrf
                    <button class="btn btn-sm btn-outline-success"><i class="fa fa-robot"></i> Devolver al bot</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.whatsapp.pause', $chat) }}" class="d-inline">@csrf
                    <button class="btn btn-sm btn-outline-warning"><i class="fa fa-hand-paper-o"></i> Tomar control</button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="wa-thread" id="wa-thread">
        @include('admin.whatsapp._bubbles', ['messages' => $messages])
    </div>

    <form method="POST" action="{{ route('admin.whatsapp.reply', $chat) }}" class="mt-3">
        @csrf
        <div class="input-group">
            <textarea name="message" class="form-control" rows="2" placeholder="Escribí una respuesta..." required></textarea>
            <div class="input-group-append">
                <button class="btn btn-success"><i class="fa fa-paper-plane"></i> Enviar</button>
            </div>
        </div>
        <small class="text-muted">Al enviar, tomás el control: el bot deja de responder este chat hasta que lo devuelvas.</small>
    </form>
</div>

@push('script')
<script>
(function () {
    var thread = document.getElementById('wa-thread');
    var lastId = {{ $messages->max('id') ?: 0 }};
    var url = '{{ route('admin.whatsapp.messages', $chat) }}';
    thread.scrollTop = thread.scrollHeight;

    setInterval(function () {
        fetch(url + '?since=' + lastId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.html && data.html.trim() !== '') {
                    thread.insertAdjacentHTML('beforeend', data.html);
                    thread.scrollTop = thread.scrollHeight;
                }
                if (data.last_id) lastId = data.last_id;
            })
            .catch(function () {});
    }, 5000);
})();
</script>
@endpush
@endsection
