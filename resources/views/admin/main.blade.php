<!doctype html>
<html class="no-js" lang="en">

@include('admin.layouts.head')

<body>
    <div id="preloader">
        <div class="loader"></div>
    </div>

    <div class="page-container">
        @include('admin.layouts.sidebar')
        
        <div class="main-content">
            <div class="header-area">
                <div class="row align-items-center">
                    <div class="col-md-6 col-sm-8 clearfix">
                        <div class="nav-btn pull-left">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-4 clearfix">
                        <ul class="notification-area pull-right">
                            @auth
                            @php
                                $unreadNotifications = Auth::user()->unreadNotifications->take(5);
                                $unreadCount = Auth::user()->unreadNotifications->count();
                            @endphp
                            <li class="dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-bell"></i>
                                    @if($unreadCount > 0)
                                        <span class="badge badge-danger" style="position: absolute; top: 5px; right: 5px; font-size: 10px;">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                                    @endif
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" style="min-width: 320px; max-height: 400px; overflow-y: auto;">
                                    <h6 class="dropdown-header">Notificaciones</h6>
                                    @forelse($unreadNotifications as $notification)
                                        <a class="dropdown-item small py-2" href="#" style="white-space: normal;">
                                            <div>{{ $notification->data['message'] ?? 'Notificación' }}</div>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    @empty
                                        <span class="dropdown-item text-muted small">Sin notificaciones nuevas</span>
                                    @endforelse
                                    @if($unreadCount > 0)
                                        <form action="{{ url('/admin/notifications/mark-read') }}" method="POST" class="px-3 py-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary btn-block">Marcar todas como leídas</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                            @endauth
                            <li class="dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>{{Auth::user()->username}} <i class="ti-angle-down"></i> </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('profil') }}">
                                        Cambiar el perfil</a>
                                    <a class="dropdown-item" href="{{ route('ubahPassword') }}">
                                        Cambiar la contraseña</a>

                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Salir</a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="main-content-inner">
                <div class="sales-report-area mt-5 mb-5">
                    @yield('content')
                </div>
            </div>
        </div>
        
        <footer>
            <div class="footer-area">
                <p>© Copyright <span id="year"></span>. Safewor Solutions.</p>
            </div>
        </footer>
    </div>
    
    @include('admin.layouts.scripts')
    @stack('script')

    <script>
        document.getElementById("year").innerHTML = new Date().getFullYear();
    </script>
</body>
</html>