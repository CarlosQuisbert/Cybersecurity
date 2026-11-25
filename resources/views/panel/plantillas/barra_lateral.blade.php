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

            <li class="menu-header">CIBERSEGURIDAD</li>
            <li class="{{ request()->routeIs('incidentes.listar') ? 'active' : '' }}"><a class="nav-link" href="{{route('incidentes.listar')}}"><i class="fas fa-bug"></i> <span>Incidentes</span></a></li>
            <li class="{{ request()->routeIs('tipos-incidente.listar') ? 'active' : '' }}"><a class="nav-link" href="{{route('tipos-incidente.listar')}}"><i class="fas fa-tags"></i> <span>Tipos de incidente</span></a></li>

            <li class="menu-header">GENERAL</li>
            <li class="{{ request()->routeIs('usuarios.listar') ? 'active' : '' }}"><a class="nav-link" href="{{route('usuarios.listar')}}"><i class="fas fa-user"></i> <span>Usuarios</span></a></li>

            <li class="menu-header">AUTORIZACIÓN</li>
            <li class="{{ request()->routeIs('roles.listar') ? 'active' : '' }}"><a class="nav-link" href="{{route('roles.listar')}}"><i class="fas fa-pencil-ruler"></i> <span>Roles</span></a></li>
            <li class="{{ request()->routeIs('permisos.listar') ? 'active' : '' }}"><a class="nav-link" href="{{route('permisos.listar')}}"><i class="fas fa-pencil-ruler"></i> <span>Permisos</span></a></li>
        </ul>
    </aside>
</div>
