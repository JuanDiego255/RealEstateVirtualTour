<div class="sidebar-menu">
    <div class="sidebar-header">
        <div class="logo">
            <h5><a href="{{ url('/admin') }}" class="text-white">MENU PRINCIPAL</a></h5>
        </div>
    </div>
    <div class="main-menu">
        <div class="menu-inner">
            <nav>
                <ul class="metismenu" id="menu">
                  {{--   <li class="">
                        <a href="#" onclick="cerrarMenu();"><i class="fa fa-close"></i><span>Cerrar Menú</span></a>
                    </li> --}}
                    <li class="{{ Request::routeIs('home') ? 'active' : '' }}">
                        <a href="{{ route('home') }}"><i class="fa fa-home"></i><span>Inicio</span></a>
                    </li>

                    <li
                        class="{{ (Request::routeIs('config') ? 'active' : '' || Request::routeIs('property')) ? 'active' : '' }}">
                        <a href="{{ route('property') }}"><i class="fa fa-building"></i> <span>
                                Propiedades</span></a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
<script>
    function cerrarMenu(){
        $(".close-button").click();
    }
</script>
