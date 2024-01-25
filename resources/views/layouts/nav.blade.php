<!--sidebar wrapper -->
<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <h4 class="logo-text">{{ Auth::guard('admin')->user()->name }}</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
        </div>
    </div>
    <!--navigation-->
    <ul class="metismenu" id="menu">
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-home-circle'></i>
                </div>
                <div class="menu-title">Dashboard</div>
            </a>
            <ul>
                @if($admin->hasPerm('Dashboard - Inicio'))
                <li> 
                    <a href="{{ Asset(env('admin').'/home') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        Inicio
                        </a>
                </li>
                @endif
                @if($admin->hasPerm('Dashboard - Configuraciones'))
                <li>
                    <a href="{{ Asset(env('admin').'/setting') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        Configuraciones
                    </a>
                </li>
                @endif
                @if($admin->hasPerm('Paginas de la aplicacion'))
                <li>
                    <a href="{{ Asset(env('admin').'/page/add') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        Páginas de aplicaciones
                    </a>
                </li>
                @endif
              
            </ul>
        </li>
        
 
        @if($admin->hasPerm('Subaccount'))
        <li>
            <a href="{{ Asset(env('admin').'/adminUser') }}">
                <div class="parent-icon"><i class='lni lni-users'></i>
                </div>
                <div class="menu-title">Sub-Cuentas</div>
            </a>
        </li>
        @endif 

        @if($admin->hasPerm('Clanes'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='lni lni-popup'></i>
                </div>
                <div class="menu-title">Clanes</div>
            </a> 
            <ul>
                <li> 
                    <a href="{{ Asset(env('admin').'/clans') }}">
                    <i class="bx bx-right-arrow-alt"></i>
                    Listado de clanes
                    </a>
                </li>
                <li>
                    <a href="{{ Asset(env('admin').'/clan_requests') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        <div class="menu-title">Solicitudes</div>
                    </a>
                </li>               
            </ul>
        </li>
        @endif 
        @if($admin->hasPerm('Repartidores'))
        <li>
            <a href="{{ Asset(env('admin').'/bonuses') }}">
                <div class="parent-icon"><i class='bx bx-customize'></i>
                </div>
                <div class="menu-title">Servicios de mantenimiento</div>
            </a>
        </li>
        @endif

        @if($admin->hasPerm('Administrar Ciudades'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='lni lni-map-marker'></i>
                </div>
                <div class="menu-title">Ciudades y Zonas</div>
            </a> 
            <ul>
                <li> 
                    <a href="{{ Asset(env('admin').'/city') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        Administrar Ciudades
                        </a>
                </li>
                <li>
                    <a href="{{ Asset(env('admin').'/zones') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        Administrar Zonas
                    </a>
                </li>               
            </ul>
        </li>
        @endif

        @if($admin->hasPerm('Servicios'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='fadeIn animated bx bx-car'></i>
                </div>
                <div class="menu-title">Solicitudes de servicios</div>
            </a>
            <ul>
                <li> 
                    <a href="{{ Asset(env('admin').'/Services?status=0') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        Servicios Nuevos
                        </a>
                </li>
                <li>
                    <a href="{{ Asset(env('admin').'/Services?status=3') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        Servicios no asignados
                    </a>
                </li>
                <li>
                    <a href="{{ Asset(env('admin').'/Services?status=1') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        Servicios en curso
                    </a>
                </li>
                <li>
                    <a href="{{ Asset(env('admin').'/Services?status=6') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        Servicios Finalizados
                    </a>
                </li>
                <li>
                    <a href="{{ Asset(env('admin').'/Services?status=2') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        Servicios Cancelados
                    </a>
                </li>                
            </ul>
        </li>
        @endif

        @if($admin->hasPerm('Usuarios Registrados'))
        <li>
            <a href="{{ Asset(env('admin').'/appUser') }}">
                <div class="parent-icon">
                    <i class='fadeIn animated bx bx-user-plus'></i>
                </div>
                <div class="menu-title">Usuarios Registrados</div>
            </a>
        </li>
        @endif
        @if($admin->hasPerm('Ofertas de descuento'))
        <li>
            <a href="{{ Asset(env('admin').'/offer') }}">
                <div class="parent-icon"><i class='bx bx-calendar'></i>
                </div>
                <div class="menu-title">Cupones de descuento</div>
            </a>
        </li>
        @endif

        @if($admin->hasPerm('Conductores'))
        <li>
            <a href="{{ Asset(env('admin').'/type_delivery') }}">
                <div class="parent-icon"><i class='bx bx-taxi'></i>
                </div>
                <div class="menu-title">Tipo de vehiculos</div>
            </a>
        </li>
        @endif

        @if($admin->hasPerm('Conductores'))
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-taxi'></i>
                </div>
                <div class="menu-title">Conductores</div>
            </a>
            <ul>
                <li> 
                    <a href="{{ Asset(env('admin').'/delivery') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        Listado
                        </a>
                </li> 
                <li>
                    <a href="{{ Asset(env('admin').'/report_staff') }}">
                        <i class="bx bx-right-arrow-alt"></i>
                        Reportes
                    </a>
                </li>                
            </ul>
        </li>
        @endif

        @if($admin->hasPerm('Notificaciones push'))
        <!-- <li>
            <a href="{{ Asset(env('admin').'/push') }}">
                <div class="parent-icon"><i class='fadeIn animated bx bx-send'></i>
                </div>
                <div class="menu-title">Notificaciones</div>
            </a>
        </li> -->
        @endif

        <li>
            <a href="{{ Asset(env('admin').'/logout') }}">
                <div class="parent-icon"><i class='fadeIn animated bx bx-log-out-circle'></i>
                </div>
                <div class="menu-title">Cerrar Sesion</div>
            </a>
        </li>
    </ul>
    <!--end navigation-->
</div>
<!--end sidebar wrapper -->