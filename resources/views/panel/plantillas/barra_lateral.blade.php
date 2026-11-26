@php($usuarioActual = auth('admin')->user())
<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{route('panel.inicio')}}"><i class="fas fa-shield-alt"></i> IncidentX</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{route('panel.inicio')}}">IX</a>
        </div>
        <ul class="sidebar-menu">
            <li class="{{ request()->routeIs('panel.inicio') ? 'active' : '' }}"><a class="nav-link" href="{{route('panel.inicio')}}"><i class="fas fa-home"></i> <span>Inicio</span></a></li>

            @if($usuarioActual->can('incidentes.ver'))
                <li class="menu-header">CIBERSEGURIDAD</li>
                <li class="{{ request()->routeIs('cola.listar') ? 'active' : '' }}"><a class="nav-link" href="{{route('cola.listar')}}"><i class="fas fa-sort-amount-down"></i> <span>Cola de prioridad</span></a></li>
                <li class="{{ request()->routeIs('incidentes.*') ? 'active' : '' }}"><a class="nav-link" href="{{route('incidentes.listar')}}"><i class="fas fa-bug"></i> <span>Incidentes</span></a></li>
            @endif
            @if($usuarioActual->can('activos.gestionar'))
                <li class="{{ request()->routeIs('activos.*') ? 'active' : '' }}"><a class="nav-link" href="{{route('activos.listar')}}"><i class="fas fa-server"></i> <span>Inventario de activos</span></a></li>
            @endif
            @if($usuarioActual->can('tipos-incidente.gestionar'))
                <li class="{{ request()->routeIs('tipos-incidente.*') ? 'active' : '' }}"><a class="nav-link" href="{{route('tipos-incidente.listar')}}"><i class="fas fa-tags"></i> <span>Categorías de incidente</span></a></li>
            @endif

            @if($usuarioActual->can('modelo.configurar'))
                <li class="menu-header">MODELO DE PRIORIZACIÓN</li>
                <li class="{{ request()->routeIs('modelo.configuracion') ? 'active' : '' }}"><a class="nav-link" href="{{route('modelo.configuracion')}}"><i class="fas fa-sliders-h"></i> <span>Pesos y umbrales</span></a></li>
                <li class="{{ request()->routeIs('modelo.escalas') ? 'active' : '' }}"><a class="nav-link" href="{{route('modelo.escalas')}}"><i class="fas fa-list-ol"></i> <span>Escalas de valoración</span></a></li>
                <li class="{{ request()->routeIs('tacticas.*') ? 'active' : '' }}"><a class="nav-link" href="{{route('tacticas.listar')}}"><i class="fas fa-chess"></i> <span>Tácticas ATT&amp;CK</span></a></li>
                <li class="{{ request()->routeIs('modelo.ahp') ? 'active' : '' }}"><a class="nav-link" href="{{route('modelo.ahp')}}"><i class="fas fa-balance-scale"></i> <span>Cálculo AHP</span></a></li>
                <li class="{{ request()->routeIs('modelo.sensibilidad') ? 'active' : '' }}"><a class="nav-link" href="{{route('modelo.sensibilidad')}}"><i class="fas fa-chart-line"></i> <span>Sensibilidad</span></a></li>
            @endif

            @if($usuarioActual->can('usuarios.gestionar') || $usuarioActual->can('roles.gestionar') || $usuarioActual->can('bitacora.ver'))
                <li class="menu-header">ADMINISTRACIÓN</li>
            @endif
            @if($usuarioActual->can('usuarios.gestionar'))
                <li class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}"><a class="nav-link" href="{{route('usuarios.listar')}}"><i class="fas fa-user"></i> <span>Usuarios</span></a></li>
            @endif
            @if($usuarioActual->can('roles.gestionar'))
                <li class="{{ request()->routeIs('roles.*') ? 'active' : '' }}"><a class="nav-link" href="{{route('roles.listar')}}"><i class="fas fa-user-shield"></i> <span>Roles</span></a></li>
                <li class="{{ request()->routeIs('permisos.*') ? 'active' : '' }}"><a class="nav-link" href="{{route('permisos.listar')}}"><i class="fas fa-key"></i> <span>Permisos</span></a></li>
            @endif
            @if($usuarioActual->can('bitacora.ver'))
                <li class="{{ request()->routeIs('bitacora.*') ? 'active' : '' }}"><a class="nav-link" href="{{route('bitacora.listar')}}"><i class="fas fa-history"></i> <span>Bitácora</span></a></li>
            @endif
        </ul>
    </aside>
</div>
