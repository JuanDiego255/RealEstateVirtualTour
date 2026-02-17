@extends('admin.main')
@section('title', $category->name)
@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show"><strong>{{ Session::get('success') }}</strong><button type="button" class="close" data-dismiss="alert"><span class="fa fa-times"></span></button></div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>
                @if($category->logo_url)
                    <img src="{{ $category->logo_url }}" style="height: 30px;" class="mr-2">
                @endif
                {{ $category->name }}
                @if(!$category->is_active) <span class="badge badge-secondary">Inactiva</span> @endif
            </h4>
            <div>
                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Editar</a>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
            </div>
        </div>

        <div class="row">
            {{-- Info Principal --}}
            <div class="col-md-8">
                @if($category->cover_url)
                    <img src="{{ $category->cover_url }}" class="img-fluid rounded mb-3" style="max-height: 250px; width: 100%; object-fit: cover;">
                @endif

                <div class="card mb-3">
                    <div class="card-header"><strong>Información General</strong></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Sector:</strong>
                                    @if($category->sector)
                                        <span class="badge badge-info">
                                            @if($category->sector->icon)<i class="{{ $category->sector->icon }}"></i> @endif
                                            {{ $category->sector->name }}
                                        </span>
                                    @else N/A @endif
                                </p>
                                <p><strong>Empresa:</strong> {{ $category->company->name ?? 'N/A' }}</p>
                                <p><strong>Vistas:</strong> {{ $category->views_count ?? 0 }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Creada:</strong> {{ $category->created_at->format('d/m/Y') }}</p>
                                <p><strong>Slug:</strong> <code>{{ $category->slug }}</code></p>
                                <p><strong>Estado:</strong>
                                    <span class="badge badge-{{ $category->is_active ? 'success' : 'secondary' }}">
                                        {{ $category->is_active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        @if($category->description)
                            <hr>
                            <p>{{ $category->description }}</p>
                        @endif
                    </div>
                </div>

                {{-- Propiedades recientes --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <strong>Propiedades ({{ $category->properties->count() }})</strong>
                    </div>
                    <div class="card-body table-responsive">
                        @if($category->properties->count())
                            <table class="table table-sm table-striped">
                                <thead><tr><th>Nombre</th><th>Precio</th><th>Estado</th><th>Tour</th></tr></thead>
                                <tbody>
                                    @foreach($category->properties as $prop)
                                        <tr>
                                            <td>{{ $prop->name }}</td>
                                            <td>{{ $prop->formatted_price ?? $prop->price }}</td>
                                            <td><span class="badge badge-{{ $prop->status_color ?? 'secondary' }}">{{ $prop->status_name ?? $prop->status ?? 'N/A' }}</span></td>
                                            <td>
                                                @if($prop->has_virtual_tour)
                                                    <span class="text-success"><i class="fa fa-check"></i></span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted text-center">No hay propiedades en esta categoría</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Panel lateral --}}
            <div class="col-md-4">
                {{-- Enlace compartible --}}
                <div class="card mb-3 border-success">
                    <div class="card-header bg-success text-white"><i class="fa fa-share-alt"></i> Enlace Compartible</div>
                    <div class="card-body">
                        <div class="input-group mb-2">
                            <input type="text" class="form-control form-control-sm" value="{{ $category->share_url }}" id="shareUrl" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-outline-success" onclick="copyShareLink()"><i class="fa fa-copy"></i></button>
                            </div>
                        </div>
                        <form action="{{ route('admin.categories.regenerate-token', $category) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-warning btn-block" onclick="return confirm('¿Regenerar enlace? El anterior dejará de funcionar.')">
                                <i class="fa fa-refresh"></i> Regenerar enlace
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Contacto --}}
                <div class="card mb-3">
                    <div class="card-header"><i class="fa fa-phone"></i> Contacto</div>
                    <div class="card-body small">
                        @if($category->contact_name)<p><strong>Nombre:</strong> {{ $category->contact_name }}</p>@endif
                        @if($category->contact_email)<p><strong>Email:</strong> <a href="mailto:{{ $category->contact_email }}">{{ $category->contact_email }}</a></p>@endif
                        @if($category->contact_phone)<p><strong>Tel:</strong> {{ $category->contact_phone }}</p>@endif
                        @if($category->contact_whatsapp)
                            <p><strong>WhatsApp:</strong> <a href="{{ $category->whatsapp_link }}" target="_blank">{{ $category->contact_whatsapp }}</a></p>
                        @endif
                        @if(!$category->contact_name && !$category->contact_email && !$category->contact_phone)
                            <p class="text-muted">Sin información de contacto</p>
                        @endif
                    </div>
                </div>

                {{-- Ubicación --}}
                @if($category->address || $category->latitude)
                <div class="card mb-3">
                    <div class="card-header"><i class="fa fa-map-marker"></i> Ubicación</div>
                    <div class="card-body small">
                        @if($category->address)<p>{{ $category->address }}</p>@endif
                        @if($category->latitude && $category->longitude)
                            <p class="text-muted">{{ $category->latitude }}, {{ $category->longitude }}</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Redes --}}
                @if($category->website || $category->social_facebook || $category->social_instagram)
                <div class="card mb-3">
                    <div class="card-header"><i class="fa fa-globe"></i> Web y Redes</div>
                    <div class="card-body small">
                        @if($category->website)<p><i class="fa fa-globe"></i> <a href="{{ $category->website }}" target="_blank">{{ $category->website }}</a></p>@endif
                        @if($category->social_facebook)<p><i class="fa fa-facebook"></i> {{ $category->social_facebook }}</p>@endif
                        @if($category->social_instagram)<p><i class="fa fa-instagram"></i> {{ $category->social_instagram }}</p>@endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
function copyShareLink() {
    var input = document.getElementById('shareUrl');
    input.select();
    if (navigator.clipboard) {
        navigator.clipboard.writeText(input.value).then(function() { alert('Enlace copiado'); });
    } else {
        document.execCommand('copy');
        alert('Enlace copiado');
    }
}
</script>
@endpush
