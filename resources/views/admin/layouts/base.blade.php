<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>@yield('title') &mdash; IncidentX</title>
    <link rel="icon" type="image/svg+xml" href="{{asset('favicon.svg')}}">

    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{asset('admin/css/bootstrap.min.css')}}" >
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <!-- Template CSS -->
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

        /* IncidentX theme */
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

    </style>
    @yield('styles')
</head>

<body>
<div id="app">
    <div class="main-wrapper">
        <div class="navbar-bg"></div>

        @include('admin.layouts.header')

        @include('admin.layouts.sidebar')

        <!-- Main Content -->
        <div class="main-content">
            <section class="section">
                @yield('content')
            </section>
        </div>

        @include('admin.layouts.footer')

    </div>
</div>

<!-- General JS Scripts -->
<script src="{{asset('admin/js/jquery-3.3.1.min.js')}}"></script>
<script src="{{asset('admin/js/bootstrap.min.js')}}"></script>

<script src="{{asset('js/app.js')}}"></script>
<script src="{{asset('admin/js/popper.min.js')}}"></script>
<script src="{{asset('admin/js/jquery.nicescroll.min.js')}}"></script>
<script src="{{asset('admin/js/moment.min.js')}}"></script>

<script src="{{asset('admin/js/stisla.js')}}"></script>

<!-- JS Libraies -->

<!-- Template JS File -->
<script src="{{asset('admin/js/scripts.js')}}"></script>
<script src="{{asset('admin/js/custom.js')}}"></script>

<!-- Page Specific JS File -->
@yield('scripts')
</body>
</html>
