@extends('admin.main')

@section('title', 'Publicaciones')

@section('content')
    @if ($message = Session::has('success'))
        <div class="alert-dismiss">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>{{ Session::get('success') }}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span class="fa fa-times"></span>
                </button>
            </div>
        </div>
    @endif
    @include('admin.properties.add')

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-dark"><i class="fa fa-folder-open mr-2"></i>Gestión de Publicaciones</h4>
            @if(Auth::user()->canAccessModule('properties', 'create'))
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProperty">
                <i class="fa fa-plus mr-1"></i> Nueva Publicación
            </button>
            @endif
        </div>

        <div class="row mt-5">

            @foreach ($properties as $property)
                @if(Auth::user()->canAccessModule('properties', 'edit'))
                    @include('admin.properties.edit')
                @endif
                <div class="col-md-3 col-sm-6">
                    <div class="item">
                        <div class="product-grid product_data">
                            <div class="product-image">
                                <img
                                    src="{{ isset($property->image) ? route('file', $property->image) : url('images/producto-sin-imagen.PNG') }}">
                                @if(Auth::user()->canAccessModule('properties', 'delete'))
                                <a onclick="if (confirm('¿Deseas borrar esta publicación?')) {
                                    document.getElementById('deleteProperty' + {{ $property->id }}).submit();
                                }"
                                    href="#"> <span class="product-discount-label"><i
                                            class="fa fa-trash"></i></span></a>
                                <form class="form-property" id="deleteProperty{{ $property->id }}" method="post"
                                    action="{{ url('/delete/property/' . $property->id) }}">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}
                                </form>
                                @endif
                                {{-- Status badge --}}
                                @if ($property->status && $property->status !== 'available')
                                    <span
                                        class="product-new-label badge badge-{{ $property->status_color ?? 'secondary' }}">
                                        {{ $property->status_name ?? ucfirst($property->status) }}
                                    </span>
                                @endif

                                @if (($property->property_type ?? '') !== 'vehicle')
                                    <ul class="product-links">
                                        <li><a href="#"><i class="fa fa-bed mr-1"></i>{{ $property->rooms }}</a></li>
                                        <li><a href="#"><i
                                                    class="fa fa-bath mr-1"></i>{{ $property->bathrooms }}</a></li>
                                        <li><a href="#"><i class="fa fa-car mr-1"></i>{{ $property->garage }}</a>
                                        </li>
                                    </ul>
                                @else
                                    <ul class="product-links">
                                        @if ($property->transmission)
                                            <li><a href="#"><i
                                                        class="fa fa-cog mr-1"></i>{{ $property->transmission }}</a></li>
                                        @endif
                                        @if ($property->fuel_type)
                                            <li><a href="#"><i
                                                        class="fa fa-tint mr-1"></i>{{ $property->fuel_type }}</a></li>
                                        @endif
                                        @if ($property->year)
                                            <li><a href="#"><i
                                                        class="fa fa-calendar mr-1"></i>{{ $property->year }}</a></li>
                                        @endif
                                    </ul>
                                @endif

                                @if(Auth::user()->canAccessModule('properties', 'edit'))
                                <a href="#" data-toggle="modal" data-target="#editProperty{{ $property['id'] }}"
                                    class="add-to-cart">Editar</a>
                                @endif
                            </div>
                            <div class="product-content">

                                <h3 class="title"><a
                                        href="{{ route('config', $property->id) }}">{{ $property->name }}</a>
                                </h3>

                                @if ($property->property_type)
                                    <div class="mb-1"><span
                                            class="badge badge-light">{{ $property->property_type_name }}</span></div>
                                @endif

                                @if (($property->property_type ?? '') === 'vehicle')
                                    @if ($property->brand)
                                        <div class="price"><i class="fa fa-car mr-1"></i>{{ $property->brand }}
                                            {{ $property->model }} {{ $property->year }}</div>
                                    @endif
                                    @if ($property->mileage_km)
                                        <div class="price"><i
                                                class="fa fa-road mr-1"></i>{{ number_format($property->mileage_km) }} km
                                        </div>
                                    @endif
                                @else
                                    <div class="price"><i class="fa fa-home mr-1"></i>Construcción:
                                        {{ $property->construction }} Mt2
                                    </div>
                                    <div class="price"><i class="fa fa-border-none mr-1"></i>Terreno:
                                        {{ $property->land }} Mt2
                                    </div>
                                    <div class="price"><i class="fa fa-calendar-alt mr-1"></i>Se construyó:
                                        {{ $property->construction_year }}
                                    </div>
                                @endif

                                <div class="price"><i class="fa fa-money-bill mr-1"></i>Precio:
                                    @if ($property->currency === 'USD')
                                        ${{ number_format($property->price) }} USD
                                    @else
                                        ₡{{ number_format($property->price) }}
                                    @endif
                                </div>

                                @if (($property->property_type ?? '') !== 'vehicle')
                                    <div class="price"><i class="fa fa-money-bill mr-1"></i>Mantenimiento:
                                        ₡{{ number_format($property->maintenance) }}
                                    </div>
                                @endif

                                @if ($property->location)
                                    <div class="price"><i class="fa fa-map-marker-alt mr-1"></i>{{ $property->location }}
                                    </div>
                                @endif
                                @if ($property->commission_percentage > 0 && !$property->is_exclusive)
                                    <div class="price text-success"><i class="fa fa-handshake mr-1"></i>Comisión:
                                        {{ $property->commission_percentage }}%</div>
                                @endif
                                @if ($property->is_exclusive)
                                    <div class="price text-warning"><i class="fa fa-lock mr-1"></i>Exclusiva</div>
                                @endif

                                @if($property->share_token)
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-outline-success" onclick="copyPropertyLink('{{ $property->share_url }}')" title="Copiar enlace compartible">
                                            <i class="fa fa-share-alt mr-1"></i> Compartir
                                        </button>
                                    </div>
                                @endif

                                @if(($property->property_type ?? '') === 'vehicle')
                                    <div class="mt-2">
                                        <a href="{{ route('admin.test-drive.index', $property->id) }}"
                                           class="btn btn-sm btn-warning btn-block" title="Configurar Test Drive">
                                            <i class="fa fa-car mr-1"></i> Test Drive Virtual
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
    function copyPropertyLink(url) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function() {
                alert('Enlace copiado al portapapeles');
            });
        } else {
            var input = document.createElement('input');
            input.value = url;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            alert('Enlace copiado al portapapeles');
        }
    }
    </script>

@endsection
