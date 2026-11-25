@extends('panel.plantillas.base')

@section('titulo')
    Crear usuario
@endsection

@section('contenido')

<div class="section-header">
    <h1>Crear usuario</h1>
</div>

<div class="section-body" style="">

    <div class="row">
        <div class="col-12">

            <div class="card ">
                <!-- form start -->
                <form role="form" method="post" action="{{route('usuarios.guardar')}}" data-confirmar="¿Desea guardar el usuario?" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <div class="card-body">

                        <div class="col-md-5">

                            @include('parciales.errores_formulario')

                            <div class="form-group">
                                <label for="nombre">Nombre completo</label>
                                <input type="text" name="nombre" value="{{old('nombre')}}" required class="form-control" id="nombre" placeholder="Ingrese el nombre completo">
                            </div>

                            <div class="form-group">
                                <label for="usuario">Usuario</label>
                                <input type="text" name="usuario" value="{{old('usuario')}}" required class="form-control" id="usuario" placeholder="Se sugiere a partir del nombre, puede modificarlo">
                                <small class="form-text text-muted">
                                    Se sugiere automáticamente como primer nombre.primer apellido, pero puede escribir el que prefiera.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="identificacion">Identificación (carné de identidad)</label>
                                <input type="text" name="identificacion" value="{{old('identificacion')}}" required class="form-control" id="identificacion" placeholder="Ingrese el número de identificación">
                                <small class="form-text text-muted">
                                    La contraseña de acceso será este número de identificación.
                                </small>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="rol">Rol</label>
                                @if($roles->count() > 0)
                                    <select name="rol" id="rol" class="form-control">
                                        <option value="">Seleccione un rol...</option>
                                        @foreach($roles as $rol)
                                            <option value="{{$rol->id}}" @if(old('rol') == $rol->id)selected @endif>{{$rol->nombre}}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <p><em>No hay roles registrados... Agregue uno <a href="{{route('roles.listar')}}">aquí <i class="fas fa-plus-circle"></i></a></em></p>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <a href="{{route('usuarios.listar')}}" class="btn btn-danger">Volver</a>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </div>

                    </div>


                    <!-- /.card-body -->

                </form>
            </div>

        </div>

    </div>

</div>

@endsection

@section('scripts')

    <script>
        (function () {
            var nombreInput = document.getElementById('nombre');
            var usuarioInput = document.getElementById('usuario');
            var usuarioEditadoManualmente = usuarioInput.value.length > 0;

            function normalizar(valor) {
                return (valor || '')
                    .toLowerCase()
                    .normalize('NFD').replace(/[̀-ͯ]/g, '')
                    .replace(/[^a-z0-9]/g, '');
            }

            usuarioInput.addEventListener('input', function () {
                usuarioEditadoManualmente = true;
            });

            nombreInput.addEventListener('input', function () {
                if (usuarioEditadoManualmente) return;

                var partes = this.value.trim().split(/\s+/).filter(Boolean);
                var sugerido = normalizar(partes[0] || '');
                if (partes[1]) sugerido += '.' + normalizar(partes[1]);

                usuarioInput.value = sugerido;
            });
        })();
    </script>

@endsection
