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
                    <li class="{{ Request::routeIs('home') ? 'active' : '' }}">
                        <a href="{{ route('home') }}"><i class="fa fa-home"></i><span>Inicio</span></a>
                    </li>

                    <li class="{{ Request::routeIs('sectors') ? 'active' : '' }}">
                        <a href="{{ route('sectors') }}"><i class="fa fa-th-large"></i><span>Sectores</span></a>
                    </li>

                    <li class="{{ Request::routeIs('categories') ? 'active' : '' }}">
                        <a href="{{ route('categories') }}"><i class="fa fa-folder-open"></i><span>Categorías</span></a>
                    </li>

                    <li
                        class="{{ (Request::routeIs('config') ? 'active' : '' || Request::routeIs('property')) ? 'active' : '' }}">
                        <a href="{{ route('property') }}"><i class="fa fa-building"></i> <span>
                                Propiedades</span></a>
                    </li>

                    <li class="{{ Request::routeIs('vehicles') ? 'active' : '' }}">
                        <a href="{{ route('vehicles') }}"><i class="fa fa-car"></i><span>Vehículos</span></a>
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
