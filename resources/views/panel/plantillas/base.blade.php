<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>@yield('titulo') &mdash; IncidentX</title>
    <link rel="icon" type="image/svg+xml" href="{{asset('favicon.svg')}}">

    <!-- CSS generales -->
    <link rel="stylesheet" href="{{asset('admin/css/bootstrap.min.css')}}" >
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <!-- CSS de la plantilla -->
    <link rel="stylesheet" href="{{asset('admin/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('admin/css/components.css')}}">
    <link rel="stylesheet" href="{{asset('admin/css/font-awesome.min.css')}}">
    <style>

        .fa-trash{

            color: red;
            cursor: pointer;
            font-size: 20px !important;

        }

        .fa-edit{

            color: #191d21;
            cursor: pointer;
            font-size: 20px !important;

        }
        .fa-eye{

            color: yellowgreen;

        }

        /* Tema IncidentX */
        .main-sidebar, body.sidebar-mini .main-sidebar:after {
            background: linear-gradient(160deg, #0b1c33 0%, #122b4d 45%, #1a3f6e 100%) !important;
        }
        .navbar-bg {
            background: linear-gradient(90deg, #1a3f6e, #388fff) !important;
        }
        .main-sidebar .sidebar-brand a {
            color: #fff !important;
            font-weight: 700;
            letter-spacing: .02em;
        }
        .main-sidebar .sidebar-menu li.menu-header {
            color: #6f8bb5 !important;
        }
        .main-sidebar .sidebar-menu li a {
            color: #cfe0f7 !important;
        }
        .main-sidebar .sidebar-menu li a i {
            color: #6fb1ff !important;
        }
        .main-sidebar .sidebar-menu li a:hover {
            background-color: rgba(111, 177, 255, .12) !important;
        }
        .main-sidebar .sidebar-menu li.active a {
            background-color: rgba(111, 177, 255, .16) !important;
            color: #fff !important;
            font-weight: 600;
        }
        .main-sidebar .sidebar-menu li.active a i {
            color: #fff !important;
        }
        .btn-primary, .badge-primary, .bg-primary {
            background: linear-gradient(135deg, #1a3f6e, #388fff) !important;
            border-color: #1a3f6e !important;
        }
        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(135deg, #163459, #2f7fe8) !important;
            border-color: #163459 !important;
        }
        .text-primary, a {
            color: #1a5fbf;
        }
        .card-icon.bg-primary {
            background: linear-gradient(135deg, #1a3f6e, #388fff) !important;
        }
        .page-item.active .page-link, .paginate_button.current {
            background: #1a3f6e !important;
            border-color: #1a3f6e !important;
            color: #fff !important;
        }
        .page-link, .paginate_button {
            color: #1a3f6e !important;
        }
        .custom-switch-input:checked ~ .custom-switch-indicator {
            background: #388fff !important;
        }

        /* Tablas ajustadas al ancho de la pantalla (sin barra horizontal) */
        table.tabla-apilable { width: 100% !important; table-layout: auto; }
        table.tabla-apilable td { white-space: normal; word-break: normal; overflow-wrap: break-word; vertical-align: middle; }
        table.tabla-apilable th { white-space: nowrap; vertical-align: middle; }
        .dataTables_wrapper .dataTables_paginate .pagination { flex-wrap: wrap; }
        .dataTables_wrapper > .row { margin-left: 0; margin-right: 0; }
        .dataTables_wrapper > .row > [class*="col-"] { padding-left: 0; padding-right: 0; }
        @media (min-width: 768px) {
            .dataTables_wrapper > .row > .col-md-6:first-child,
            .dataTables_wrapper > .row > .col-md-5:first-child { padding-right: 15px; }
        }

        @media (max-width: 767.98px) {
            .main-content .card[style*="padding"] { padding: 12px !important; }
            .section .section-header { flex-wrap: wrap; }
            .section .section-header h1 { font-size: 18px; }
            .btn-agregar { width: 100%; }
        }

        @media (max-width: 991.98px) {
            /* Celulares y tablets: cada fila se muestra como una tarjeta "Columna ..... valor" */
            table.tabla-apilable, table.tabla-apilable tbody,
            table.tabla-apilable tr, table.tabla-apilable td { display: block; width: 100% !important; }
            table.tabla-apilable { border: none !important; }
            table.tabla-apilable thead, table.tabla-apilable tfoot { display: none; }
            table.tabla-apilable tr {
                border: 1px solid #e4e6fc; border-radius: 6px; margin-bottom: 12px;
                padding: 4px 12px; background: #fff !important;
            }
            table.tabla-apilable td {
                display: flex; justify-content: space-between; align-items: center;
                height: auto !important; padding: 8px 0 !important; text-align: right;
                border: none !important; border-bottom: 1px solid #f2f2f2 !important;
            }
            table.tabla-apilable td:last-child { border-bottom: none !important; }
            table.tabla-apilable td::before {
                content: attr(data-label); font-weight: 600; color: #34395e;
                text-align: left; padding-right: 12px; flex-shrink: 0; max-width: 45%;
            }
            table.tabla-apilable td[data-label=""]::before { content: none; }
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate { text-align: left !important; margin-top: .5rem; }
            .dataTables_wrapper .dataTables_filter input { width: 100% !important; margin-left: 0 !important; display: block; }
            .dataTables_wrapper .dataTables_paginate .pagination { justify-content: flex-start !important; }
        }

    </style>
    @yield('estilos')
</head>

<body>
<div id="app">
    <div class="main-wrapper">
        <div class="navbar-bg"></div>

        @include('panel.plantillas.encabezado')

        @include('panel.plantillas.barra_lateral')

        <!-- Contenido principal -->
        <div class="main-content">
            <section class="section">
                @yield('contenido')
            </section>
        </div>

        @include('panel.plantillas.pie')

    </div>
</div>

<!-- Scripts JS generales -->
<script src="{{asset('admin/js/jquery-3.3.1.min.js')}}"></script>
<script src="{{asset('admin/js/bootstrap.min.js')}}"></script>

<script src="{{asset('js/app.js')}}"></script>
<script src="{{asset('admin/js/popper.min.js')}}"></script>
<script src="{{asset('admin/js/jquery.nicescroll.min.js')}}"></script>
<script src="{{asset('admin/js/moment.min.js')}}"></script>

<script src="{{asset('admin/js/stisla.js')}}"></script>

<!-- Librerías JS -->

<!-- JS de la plantilla -->
<script src="{{asset('admin/js/scripts.js')}}"></script>
<script src="{{asset('admin/js/custom.js')}}"></script>

<script>
    // Formularios con data-confirmar: piden confirmación (Sí / No) antes de enviarse.
    document.addEventListener('submit', function (e) {
        var formulario = e.target;
        if (!formulario.dataset || !formulario.dataset.confirmar || formulario.dataset.confirmado === '1') return;
        e.preventDefault();
        window.confirmarAccion(formulario.dataset.confirmar).then(function (si) {
            if (!si) return;
            formulario.dataset.confirmado = '1';
            formulario.submit();
        });
    });

    // Tablas sin paginador (p. ej. el inicio): también se ajustan a la pantalla.
    document.querySelectorAll('table[data-apilable]').forEach(function (t) { window.etiquetarCeldas(t); });

    // Mensajes temporales (arriba a la derecha) tras guardar, actualizar o eliminar.
    @if(session()->has('mensaje'))
        window.notificarExito(@json(session('mensaje')));
    @endif
    @if(session()->has('mensaje_actualizado'))
        window.notificarExito(@json(session('mensaje_actualizado')));
    @endif
    @if(session()->has('mensaje_eliminado'))
        window.notificarExito(@json(session('mensaje_eliminado')));
    @endif
    @if($errors->any())
        window.notificarError('No se pudo guardar: revise los datos del formulario.');
    @endif
</script>

<!-- JS específico de la página -->
@yield('scripts')
</body>
</html>
