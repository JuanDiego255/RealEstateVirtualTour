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

                    @auth
                        @php
                            $u = Auth::user();
                            // company_admin y super_admin siempre tienen acceso a los módulos de su empresa
                            $hasAccess = $u->isSuperAdmin() || $u->isCompanyAdmin() || $u->hasActiveSubscription();
                        @endphp

                        {{-- ============================================= --}}
                        {{-- Sucursales: super_admin ve todas; company_admin ve "Mi Sucursal" + "Mis Categorías" --}}
                        {{-- ============================================= --}}
                        @if ($u->isSuperAdmin())
                            <li class="{{ Request::routeIs('admin.categories.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.categories.index') }}">
                                    <i class="fa fa-map-marker"></i><span>Sucursales</span>
                                </a>
                            </li>
                        @elseif ($u->isCompanyAdmin())
                            @php $myCat = \App\Category::where('company_id', $u->company_id)->first(); @endphp
                            <li class="{{ Request::routeIs('admin.categories.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.categories.index') }}">
                                    <i class="fa fa-map-marker"></i><span>Mi Sucursal</span>
                                </a>
                            </li>
                            @if ($myCat)
                            <li class="{{ Request::routeIs('admin.subcategories.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.subcategories.index', $myCat->id) }}">
                                    <i class="fa fa-tags"></i><span>Mis Categorías</span>
                                </a>
                            </li>
                            @endif
                        @endif

                        {{-- Publicaciones --}}
                        @if ($u->canAccessModule('properties'))
                        <li class="{{ Request::routeIs('config') || Request::routeIs('property') ? 'active' : '' }}">
                            <a href="{{ route('property') }}"><i class="fa fa-th-list"></i><span>Publicaciones</span></a>
                        </li>
                        @endif

                        {{-- Favoritos --}}
                        @if ($u->canAccessModule('favorites'))
                        <li class="{{ Request::routeIs('favorites.*') ? 'active' : '' }}">
                            <a href="{{ route('favorites.index') }}"><i class="fa fa-heart"></i><span>Mis Favoritos</span></a>
                        </li>
                        @endif

                        {{-- Mis Calificaciones --}}
                        @if ($u->canAccessModule('ratings'))
                        <li class="{{ Request::routeIs('reviews.my-reviews') ? 'active' : '' }}">
                            <a href="{{ route('reviews.my-reviews') }}"><i class="fa fa-star"></i><span>Mis Calificaciones</span></a>
                        </li>
                        @endif

                        {{-- Bolsa Inmobiliaria --}}
                        @if ($u->canAccessModule('bolsa') && $hasAccess)
                        <li class="{{ Request::routeIs('admin.bolsa.*') ? 'active' : '' }}">
                            <a class="has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <i class="fa fa-exchange"></i><span>Bolsa Inmobiliaria</span>
                            </a>
                            <ul>
                                <li><a href="{{ route('admin.bolsa.index') }}">Dashboard</a></li>
                                <li><a href="{{ route('admin.bolsa.available') }}">Disponibles</a></li>
                                <li><a href="{{ route('admin.bolsa.my-requests') }}">Mis Solicitudes</a></li>
                                <li><a href="{{ route('admin.bolsa.incoming') }}">Recibidas</a></li>
                            </ul>
                        </li>
                        @endif

                        {{-- Ventas --}}
                        @if ($u->canAccessModule('sales') && $hasAccess)
                            <li class="{{ Request::routeIs('admin.sales.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.sales.index') }}"><i
                                        class="fa fa-money"></i><span>Ventas</span></a>
                            </li>
                        @endif

                        {{-- Dashboard de Eventos:
                             super_admin y company_admin: siempre visible
                             agent: visible si tiene permiso y suscripción activa --}}
                        @if ($u->canAccessModule('event_dashboard') && $hasAccess)
                            <li class="{{ Request::routeIs('kiosk.dashboard') ? 'active' : '' }}">
                                <a href="{{ route('kiosk.dashboard') }}"><i class="fa fa-desktop"></i><span>Dashboard Eventos</span></a>
                            </li>
                        @endif

                        {{-- Kiosko:
                             super_admin y company_admin: siempre visible
                             agent: visible si tiene permiso y suscripción activa --}}
                        @if ($u->canAccessModule('kiosk') && $hasAccess)
                            <li class="{{ Request::routeIs('kiosk.index') ? 'active' : '' }}">
                                <a href="{{ route('kiosk.index') }}" target="_blank"><i class="fa fa-tablet"></i><span>Kiosko</span></a>
                            </li>
                        @endif

                        {{-- CRM + Agenda --}}
                        @if ($u->canAccessModule('crm') && $hasAccess)
                            <li class="{{ Request::routeIs('admin.crm.*') ? 'active' : '' }}">
                                <a class="has-arrow" href="javascript:void(0)" aria-expanded="false">
                                    <i class="fa fa-address-book"></i><span>CRM + Agenda</span>
                                    @php
                                        $dueReminders = \App\Reminder::byUser(Auth::id())->due()->count();
                                    @endphp
                                    @if ($dueReminders > 0)
                                        <span class="badge badge-warning float-right">{{ $dueReminders }}</span>
                                    @endif
                                </a>
                                <ul>
                                    <li class="{{ Request::routeIs('admin.crm.leads.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.crm.leads.index') }}"><i class="fa fa-users"></i> Leads</a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.crm.leads.pipeline') ? 'active' : '' }}">
                                        <a href="{{ route('admin.crm.leads.pipeline') }}"><i class="fa fa-columns"></i> Pipeline</a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.crm.appointments.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.crm.appointments.index') }}"><i class="fa fa-calendar"></i> Agenda</a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.crm.appointments.today') ? 'active' : '' }}">
                                        <a href="{{ route('admin.crm.appointments.today') }}"><i class="fa fa-sun-o"></i> Hoy</a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.crm.reminders.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.crm.reminders.index') }}">
                                            <i class="fa fa-bell"></i> Recordatorios
                                            @if ($dueReminders > 0)
                                                <span class="badge badge-warning">{{ $dueReminders }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.crm.leads.follow-ups') ? 'active' : '' }}">
                                        <a href="{{ route('admin.crm.leads.follow-ups') }}"><i class="fa fa-clock-o"></i> Seguimientos</a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.crm.tasks.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.crm.tasks.index') }}"><i class="fa fa-check-square-o"></i> Tareas</a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.crm.activity-templates.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.crm.activity-templates.index') }}"><i class="fa fa-list-alt"></i> Plantillas de Actividad</a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.crm.message-templates.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.crm.message-templates.index') }}"><i class="fa fa-comment"></i> Plantillas de Mensajes</a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.crm.forms.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.crm.forms.index') }}"><i class="fa fa-wpforms"></i> Formularios</a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.crm.pipeline-rules.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.crm.pipeline-rules.index') }}"><i class="fa fa-cogs"></i> Reglas de Pipeline</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        {{-- Métricas de Agentes (company_admin y super_admin) --}}
                        @if ($u->isCompanyAdmin() || $u->isSuperAdmin())
                            <li class="{{ Request::routeIs('admin.metrics.*') || Request::routeIs('admin.rewards.*') ? 'active' : '' }}">
                                <a class="has-arrow" href="javascript:void(0)" aria-expanded="false">
                                    <i class="fa fa-bar-chart"></i><span>Métricas</span>
                                </a>
                                <ul>
                                    <li class="{{ Request::routeIs('admin.metrics.index') ? 'active' : '' }}">
                                        <a href="{{ route('admin.metrics.index') }}"><i class="fa fa-tachometer"></i> Dashboard</a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.metrics.goals.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.metrics.goals.index') }}"><i class="fa fa-bullseye"></i> Metas</a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.rewards.grants.*') ? 'active' : '' }}">
                                        <a href="{{ route('admin.rewards.grants.index') }}"><i class="fa fa-gift"></i> Recompensas</a>
                                    </li>
                                    @if($u->isSuperAdmin())
                                    <li class="{{ Request::routeIs('admin.rewards.index') || Request::routeIs('admin.rewards.create') || Request::routeIs('admin.rewards.edit') ? 'active' : '' }}">
                                        <a href="{{ route('admin.rewards.index') }}"><i class="fa fa-trophy"></i> Reglas</a>
                                    </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- Gestión de Usuarios y Roles (para company_admin) --}}
                        @if ($u->role === 'company_admin')
                            <li class="{{ Request::routeIs('admin.users.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.users.index') }}"><i class="fa fa-users"></i><span>Mi Equipo</span></a>
                            </li>
                            <li class="{{ Request::routeIs('admin.roles.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.roles.index') }}"><i class="fa fa-shield"></i><span>Roles y Permisos</span></a>
                            </li>
                        @endif

                        {{-- Mi Suscripción (para usuarios no super_admin) --}}
                        @if (!$u->isSuperAdmin())
                            <li class="{{ Request::routeIs('admin.subscriptions.my') ? 'active' : '' }}">
                                <a href="{{ route('admin.subscriptions.my') }}"><i class="fa fa-credit-card"></i><span>Mi Suscripción</span></a>
                            </li>
                        @endif

                        {{-- ============================================= --}}
                        {{-- SECCIÓN SUPER ADMIN --}}
                        {{-- ============================================= --}}
                        @if ($u->isSuperAdmin())
                            <li class="menu-title mt-3">
                                <span style="color: #ffc107; font-size: 11px; text-transform: uppercase;">
                                    <i class="fa fa-cog"></i> Administración
                                </span>
                            </li>

                            <li class="{{ Request::routeIs('sectors') ? 'active' : '' }}">
                                <a href="{{ route('sectors') }}"><i class="fa fa-th-large"></i><span>Sectores</span></a>
                            </li>

                            <li class="{{ Request::routeIs('admin.companies.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.companies.index') }}"><i
                                        class="fa fa-building"></i><span>Empresas</span></a>
                            </li>

                            <li class="{{ Request::routeIs('admin.users.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.users.index') }}"><i
                                        class="fa fa-users"></i><span>Usuarios</span></a>
                            </li>

                            <li class="{{ Request::routeIs('admin.packages.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.packages.index') }}"><i
                                        class="fa fa-cube"></i><span>Paquetes</span></a>
                            </li>

                            <li class="{{ Request::routeIs('admin.subscriptions.*') && !Request::routeIs('admin.subscriptions.my') ? 'active' : '' }}">
                                <a class="has-arrow" href="javascript:void(0)" aria-expanded="false">
                                    <i class="fa fa-id-card"></i><span>Suscripciones</span>
                                    @php
                                        $pendingCount = \App\Subscription::where('status', 'pending')->count();
                                    @endphp
                                    @if ($pendingCount > 0)
                                        <span class="badge badge-danger float-right">{{ $pendingCount }}</span>
                                    @endif
                                </a>
                                <ul>
                                    <li><a href="{{ route('admin.subscriptions.index') }}">Todas</a></li>
                                    <li>
                                        <a href="{{ route('admin.subscriptions.pending') }}">
                                            Pendientes
                                            @if ($pendingCount > 0)
                                                <span class="badge badge-danger">{{ $pendingCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li><a href="{{ route('admin.subscriptions.expiring') }}">Por vencer</a></li>
                                    <li><a href="{{ route('admin.subscriptions.pending-payments') }}">Pagos pendientes</a></li>
                                    <li><a href="{{ route('admin.subscriptions.create') }}">Nueva suscripción</a></li>
                                </ul>
                            </li>
                        @endif
                    @endauth
                </ul>
            </nav>
        </div>
    </div>
</div>
<script>
    function cerrarMenu() {
        $(".close-button").click();
    }
</script>
