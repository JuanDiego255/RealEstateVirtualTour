@foreach($messages as $m)
    @php $out = $m->direction === 'outbound'; @endphp
    <div class="wa-row {{ $out ? 'out' : 'in' }}" data-id="{{ $m->id }}">
        <div class="wa-bubble {{ $out ? ($m->is_human ? 'human' : 'bot') : 'client' }}">
            <div class="wa-text">{{ $m->message }}</div>
            <div class="wa-meta">
                @if($out){{ $m->is_human ? 'Equipo' : 'Bot' }} · @endif{{ $m->created_at->format('d/m H:i') }}
            </div>
        </div>
    </div>
@endforeach
