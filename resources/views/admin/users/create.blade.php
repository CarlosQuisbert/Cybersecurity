@extends('admin.layouts.base')

@section('title')
    Crear usuario
@endsection

@section('content')

<div class="section-header">
    <h1>Crear usuario</h1>
</div>

<div class="section-body" style="">

    <div class="row">
        <div class="col-12">

            <div class="card ">
                <!-- form start -->
                <form role="form" method="post" action="{{route('users.store')}}" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <div class="card-body">

                        <div class="col-md-5">

                            @include('includes.form_error')

                            <div class="form-group">
                                <label for="name">Nombre completo</label>
                                <input type="text" name="name" value="{{old('name')}}" required class="form-control" id="name" placeholder="Ingrese el nombre completo">
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
                                <label class="form-label" for="role">Rol</label>
                                @if($roles->count() > 0)
                                    <select name="role" id="role" class="form-control">
                                        <option value="">Seleccione un rol...</option>
                                        @foreach($roles as $role)
                                            <option value="{{$role->id}}" @if(old('role') == $role->id)selected @endif>{{$role->name}}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <p><em>No hay roles registrados... Agregue uno <a href="{{route('role.index')}}">aquí <i class="fas fa-plus-circle"></i></a></em></p>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <a href="{{route('users.index')}}" class="btn btn-danger">Volver</a>
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
            var nameInput = document.getElementById('name');
            var usuarioInput = document.getElementById('usuario');
            var usuarioEditedManually = usuarioInput.value.length > 0;

            function slugify(value) {
                return (value || '')
                    .toLowerCase()
                    .normalize('NFD').replace(/[̀-ͯ]/g, '')
                    .replace(/[^a-z0-9]/g, '');
            }

            usuarioInput.addEventListener('input', function () {
                usuarioEditedManually = true;
            });

            nameInput.addEventListener('input', function () {
                if (usuarioEditedManually) return;

                var partes = this.value.trim().split(/\s+/).filter(Boolean);
                var sugerido = slugify(partes[0] || '');
                if (partes[1]) sugerido += '.' + slugify(partes[1]);

                usuarioInput.value = sugerido;
            });
        })();
    </script>

@endsection
