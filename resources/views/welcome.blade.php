<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Virtual Tour | Su propiedad en occidente</title>

    {{-- Bootstrap --}}
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" media="all">

    {{-- Icon --}}
    <link rel="icon" href="{{ asset('img/UnsoedIcon.png') }}">

    <!-- Fonts -->
    <link href="//fonts.googleapis.com/css?family=Fahkwang:400,500,600,700" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">

    {{-- Css --}}
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">

    <!-- Jquery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>

    {{-- Script --}}
    <script src="{{ asset('js/modernizr.js') }}"></script>

    {{-- Pannellum --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>

    <!-- Fading Out Overlay -->
    <script>
        $(document).ready(function() {
            $("#hide").click(function() {
                $(".home-content-table").fadeOut(1000);
            });
        });
    </script>
</head>

<body id="top">

    <header>
        <nav id="menu-nav-wrap">
            <h3>Virtual Tour</h3>

            <ul class="nav-list">
                @foreach ($scenes as $scene)
                    <li>
                        <a class="smoothscroll" onclick="loadScene({{ $scene->id }})">{{ $scene->title }}
                            <center>
                                <img class="circular text-center"
                                    src= "{{ isset($scene->image_ref) ? route('file', $scene->image_ref) : url('images/producto-sin-imagen.PNG') }}" />
                            </center>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    <div class="modal fade" id="denahModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <img src="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- home-->

    <div class="home-content-table">
        <div class="home-content-tablecell">
            <div class="row">
                <div class="col-twelve">
                    <h1 class="animate-intro"> Virtual Tour | Descubre {{ $fscene->property_name }}</h1>

                    <div class="more animate-intro">
                        <a id="hide" class="button stroke"> Empezar Tour </a>
                        <a id="hide" href="{{ url('/') }}" class="button stroke"> Más Propiedades </a>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div id="pannellum">
        <div id="controls">
            <div class="ctrl btn-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" title="Menú"
                data-container="body" data-animation="true" id="menu-trigger-ctrl"><i class="fa fa-cubes"></i></div>
            <div data-bs-toggle="tooltip" data-bs-placement="top" title="Mapa" data-container="body"
                data-animation="true" class="ctrl" onclick="showModal()" id="menu-trigger-ctrl-map"><i
                    class="fa fa-map"></i></div>
        </div>
    </div>

    <div id="preloader">
        <div id="loader"></div>
    </div>


    <!-- Java Script -->
    <script src="{{ asset('js/plugins.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    <script>
        var viewer = pannellum.viewer('pannellum', {
            "default": {
                "firstScene": "{{ $fscene->id }}",
                "hfov": -1000,
                "autoLoad": true,
                "sceneFadeDuration": 2000,
                "autoRotate": -1,
                "resolution": 4096,
                "autoRotateInactivityDelay": 30000
            },

            "scenes": {
                @foreach ($scenes as $scene)

                    "{{ $scene->id }}": {
                        "title": "{{ $scene->title }}",
                        "hfov": {{ $scene->hfov }},
                        "pitch": {{ $scene->pitch }},
                        "yaw": {{ $scene->yaw }},
                        "type": "{{ $scene->type }}",
                        "panorama": "{{ isset($scene->image) ? route('file', $scene->image) : url('images/producto-sin-imagen.PNG') }}",

                        "hotSpots": [
                            @foreach ($hotspots->where('sourceScene', $scene->id) as $hotspot)
                                {
                                    "pitch": "{{ $hotspot->pitch }}",
                                    "yaw": "{{ $hotspot->yaw }}",
                                    "cssClass": "circular-hotspot",
                                    "type": "{{ $hotspot->type }}",
                                    "createTooltipFunc": hotspotTooltipFunction,
                                    "createTooltipArgs": "{{ isset($hotspot->image) ? route('file', $hotspot->image) : url('images/producto-sin-imagen.PNG') }}",
                                    "text": "{{ $hotspot->info }}",
                                    @if ($hotspot->type == 'scene')
                                        "sceneId": "{{ $hotspot->targetScene }}"
                                    @endif
                                },
                            @endforeach
                        ]
                    },
                @endforeach
            }
        });

        document.getElementById('menu-trigger-ctrl').addEventListener('click', function(e) {

        });

        function loadScene(clicked_id) {
            viewer.loadScene(clicked_id);
        }

        function hotspotTooltipFunction(hotSpotDiv, args) {
            // Puedes personalizar el contenido aquí, por ejemplo, mostrar una imagen
            var img = document.createElement('img');
            img.classList.add('circular-hotspot-img');
            img.src = args;
            hotSpotDiv.appendChild(img);
        }
    </script>

    <script>
        function showModal() {
            $('#denahModal').modal('show');
        };
    </script>

    <script>
        $("#menu-nav-wrap > ul > li > a").on('click', function() {
            $(".close-button").click();
        });
    </script>
</body>

</html>
