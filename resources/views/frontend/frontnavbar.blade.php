{{-- <div id="menuHolder" class="bg-menu-velvet sticky-top">
    <div role="navigation" class="border-bottom bg-menu-velvet" id="mainNavigation">
        <div class="flexMain">
            <div class="flex2">
                <button class="whiteLink siteLink" id="btnMenu" style="border-right:1px solid #eaeaea"
                    onclick="menuToggle()"><i class="fas fa-bars mr-2"></i> MENU</button>
            </div>
            <div class="flex3 text-center" id="siteBrand">
                <a class="text-title text-uppercase" href="{{ url('/') }}">VIRTUAL TOUR</a>
            </div>

            <div class="flex2 text-end d-block d-md-none">
                <a href="{{ url('/') }}"><button id="btnIngresar" class="whiteLink siteLink"><i
                            class="fa fa-building"></i></button></a>
            </div>

            <div class="flex2 text-end d-none d-md-block">

            </div>
        </div>
    </div>

    <div id="menuDrawer" class="bg-menu-d">

        <div>
            @guest
                <a class="nav-menu-item" href="javascript:void(0);" onclick="menuToggle()"><i
                        class="fa fa-arrow-circle-left mr-3"></i>CERRAR MENU</a>
                <a href="{{ url('/') }}" class="nav-menu-item"><i class="fas fa-home mr-3"></i>PROPIEDADES</a>

                <a href="{{ route('register') }}" class="nav-menu-item"><i class="fa fa-user-plus mr-3"></i>REGISTRARSE</a>
                <a href="{{ route('login') }}" class="nav-menu-item"><i class="fa fa-sign-in mr-3"></i>INGRESAR</a>
            @else
                <a class="nav-menu-item" href="javascript:void(0);" onclick="menuToggle()"><i
                        class="fa fa-arrow-circle-left mr-3"></i>CERRAR MENU</a>
                <a href="{{ url('/') }}" class="nav-menu-item"><i class="fas fa-home mr-3"></i>PROPIEDADES</a>
                <div class="nav-menu-item">
                    <a class="color-menu" href="javascript:void(0);" id="toggleLogout"><i
                            class="fas fa-user-minus mr-3"></i>{{ Auth::user()->name }} {{ Auth::user()->last_name }} <i
                            class="fa fa-arrow-circle-down mr-3"></i></a>
                    <div class="subLogout" id="logoutDropdown">
                        <ul>
                            <li class="item-submenu">
                                <a href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();"
                                    class="nav-submenu-item">
                                    <i class="fa fa-sign-out mr-3"></i>
                                    </span>Salir
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </a>
                            </li>

                        </ul>
                        <!-- Agrega más subcategorías si es necesario -->
                    </div>
                </div>
            @endguest

        </div>
    </div>
</div> --}}
<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">Synergy Real Estate</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
            aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>

        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a href="{{ url('/') }}" class="nav-link"><i class="fas fa-home mr-2"></i> Propiedades</a>
                </li>

                @guest
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="nav-link"><i class="fa fa-user-plus mr-2"></i> Registrarse</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link"><i class="fa fa-sign-in mr-2"></i> Ingresar</a>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user mr-2"></i> {{ Auth::user()->name }} {{ Auth::user()->last_name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa fa-sign-out mr-2"></i> Salir
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

