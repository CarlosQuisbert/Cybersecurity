<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{route('admin.home')}}"><i class="fas fa-shield-alt"></i> IncidentX</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{route('admin.home')}}">IX</a>
        </div>
        @php
            $current_url = url()->current();
            $base_url = env('APP_URL')
        @endphp
        <ul class="sidebar-menu">
            <li class="{{$base_url . '/admin/home' === $current_url ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.home')}}"><i class="fas fa-home"></i> <span>Inicio</span></a></li>

            <li class="menu-header">CIBERSEGURIDAD</li>
            <li class="{{$base_url . '/admin/incidents' === $current_url ? 'active' : ''}}"><a class="nav-link" href="{{route('incidents.index')}}"><i class="fas fa-bug"></i> <span>Incidentes</span></a></li>
            <li class="{{$base_url . '/admin/incident-types' === $current_url ? 'active' : ''}}"><a class="nav-link" href="{{route('incident-types.index')}}"><i class="fas fa-tags"></i> <span>Tipos de incidente</span></a></li>

            <li class="menu-header">GENERAL</li>
            <li class="{{$base_url . '/admin/users' === $current_url ? 'active' : ''}}"><a class="nav-link" href="{{route('users.index')}}"><i class="fas fa-user"></i> <span>Usuarios</span></a></li>

            <li class="menu-header">AUTORIZACIÓN</li>
            <li class="{{$base_url . '/admin/role' === $current_url ? 'active' : ''}}"><a class="nav-link" href="{{route('role.index')}}"><i class="fas fa-pencil-ruler"></i> <span>Roles</span></a></li>
            <li class="{{$base_url . '/admin/permission' === $current_url ? 'active' : ''}}"><a class="nav-link" href="{{route('permission.index')}}"><i class="fas fa-pencil-ruler"></i> <span>Permisos</span></a></li>
        </ul>
    </aside>
</div>
