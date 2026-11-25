<nav class="navbar navbar-expand-lg main-navbar">
    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
        </ul>
    </form>
    <ul class="navbar-nav navbar-right">
        <li class="">
            <a href="#" data-toggle="" class="nav-link nav-link-lg nav-link-user">
                <div class="d-sm-none d-lg-inline-block">Bienvenido,  {{ mb_convert_case(auth('admin')->user()->nombre, MB_CASE_TITLE, 'UTF-8') }}</div></a>
        </li>

        <li style="margin-right: 20px;">
            <a href="{{ route('salir') }}" class="dropdown-item has-icon text-center btn btn-danger "><i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </li>
    </ul>
</nav>
