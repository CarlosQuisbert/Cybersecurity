<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>IncidentX &mdash; Iniciar sesión</title>

    <!-- General CSS Files -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{asset('admin/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('admin/css/components.css')}}">

    <style>
        .incidentx-panel {
            min-height: 100vh;
            background: linear-gradient(160deg, #0b1c33 0%, #122b4d 45%, #1a3f6e 100%);
            position: relative;
            overflow: hidden;
        }
        .incidentx-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: radial-gradient(circle at 30% 30%, rgba(0,0,0,.9), transparent 75%);
        }
        .incidentx-glow {
            position: absolute;
            width: 520px;
            height: 520px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56,143,255,.35) 0%, rgba(56,143,255,0) 70%);
            top: -120px;
            right: -120px;
        }
        .incidentx-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100vh;
            padding: 4rem;
            color: #eaf2ff;
        }
        .incidentx-badge {
            width: 68px;
            height: 68px;
            border-radius: 16px;
            background: rgba(56,143,255,.15);
            border: 1px solid rgba(56,143,255,.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: #6fb1ff;
            margin-bottom: 2rem;
        }
        .incidentx-content h1 {
            font-weight: 700;
            font-size: 2.4rem;
            margin-bottom: .75rem;
        }
        .incidentx-content p {
            color: #a9c3e6;
            font-size: 1.05rem;
            max-width: 480px;
            line-height: 1.6;
        }
        .incidentx-features {
            margin-top: 3rem;
            list-style: none;
            padding: 0;
        }
        .incidentx-features li {
            display: flex;
            align-items: center;
            color: #cfe0f7;
            margin-bottom: 1rem;
            font-size: .95rem;
        }
        .incidentx-features li i {
            color: #6fb1ff;
            margin-right: .75rem;
            width: 20px;
            text-align: center;
        }
        .incidentx-form-side {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .incidentx-form-wrap {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }
        .incidentx-logo-badge {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg, #1a3f6e, #388fff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 24px;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 20px rgba(56,143,255,.3);
        }
        .incidentx-form-wrap h4 {
            font-weight: 700;
        }
        .incidentx-form-wrap .input-group-text {
            background: #f4f7fb;
            border-right: none;
            color: #7a8aa0;
        }
        .incidentx-form-wrap .form-control {
            border-left: none;
            padding-left: 0;
        }
        .incidentx-form-wrap .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(56,143,255,.15);
            border-radius: .25rem;
        }
        .btn-incidentx {
            background: linear-gradient(135deg, #1a3f6e, #388fff);
            border: none;
            font-weight: 600;
            letter-spacing: .02em;
        }
        .btn-incidentx:hover {
            background: linear-gradient(135deg, #163459, #2f7fe8);
            color: #fff;
        }
        @media (max-width: 991px) {
            .incidentx-panel { min-height: 40vh; }
            .incidentx-content { padding: 2.5rem 1.75rem; min-height: 40vh; }
            .incidentx-features { display: none; }
        }
        html, body {
            margin: 0;
            padding: 0;
            background-color: #0b1c33;
        }
        .section > *:first-child {
            margin-top: 0 !important;
        }
    </style>
</head>

<body>
<div id="app">
    <section class="section">
        <div class="d-flex flex-wrap align-items-stretch">

            <div class="col-lg-8 col-12 order-lg-1 order-1 p-0">
                <div class="incidentx-panel">
                    <div class="incidentx-glow"></div>
                    <div class="incidentx-content">
                        <div class="incidentx-badge"><i class="fas fa-shield-alt"></i></div>
                        <h1>IncidentX</h1>
                        <p>Sistema de control de incidentes y control de riesgos de ciberseguridad. Gestiona, prioriza y da seguimiento a los incidentes de seguridad de tu organización en un solo lugar.</p>
                        <ul class="incidentx-features">
                            <li><i class="fas fa-bug"></i> Registro y seguimiento de incidentes</li>
                            <li><i class="fas fa-tasks"></i> Historial de estados y evidencias</li>
                            <li><i class="fas fa-users-cog"></i> Roles y permisos por usuario</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-12 order-lg-2 order-2 bg-white incidentx-form-side">
                <div class="incidentx-form-wrap">
                    <div class="incidentx-logo-badge"><i class="fas fa-lock"></i></div>
                    <h4 class="text-dark mb-1">Iniciar sesión</h4>
                    <p class="text-muted mb-4">Ingresa tu usuario y contraseña para acceder al panel.</p>

                    @include('includes.form_error')

                    <form method="POST" action="{{route('login')}}" class="needs-validation">
                        @csrf
                        <div class="form-group">
                            <label for="usuario">Usuario</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input id="usuario" type="text" class="form-control" name="usuario" tabindex="1" required autofocus placeholder="Ingrese su usuario">
                            </div>
                            <div class="invalid-feedback">
                                Por favor ingrese su usuario
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="control-label">Contraseña</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                </div>
                                <input id="password" type="password" class="form-control" name="password" tabindex="2" required placeholder="••••••••">
                            </div>
                            <div class="invalid-feedback">
                                Por favor ingrese su contraseña
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-incidentx btn-lg btn-block" tabindex="3">
                                Iniciar sesión
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- General JS Scripts -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="{{asset('admin/js/stisla.js')}}"></script>
<script src="{{asset('admin/js/scripts.js')}}"></script>
<script src="{{asset('admin/js/custom.js')}}"></script>
</body>
</html>
