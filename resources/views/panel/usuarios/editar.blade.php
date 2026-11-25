@extends('panel.plantillas.base')

@section('titulo')
    Editar usuario - {{$usuario->nombre}}
@endsection

@section('contenido')

<div class="section-header">
    <h1>Editar usuario - <b><i>{{$usuario->nombre}}</i></b></h1>
</div>

<div class="section-body" style="">


    <div class="row">
            <div class="col-12">

                <div class="card ">
                    <div class="col-lg-6 ">
                        @include('parciales.errores_formulario')
                    </div>
                    <!-- form start -->
                    <form role="form" method="post" action="{{route('usuarios.actualizar', $usuario->id)}}" data-confirmar="¿Desea guardar los cambios del usuario?" enctype="multipart/form-data">
                        {{csrf_field()}}
                        {{method_field('PATCH')}}
                        <div class="card-body">

                            <div class="col-lg-5">

                                <div class="form-group ">
                                    <label for="nombre">Nombre completo</label>
                                    <input type="text" value="{{ old('nombre', $usuario->nombre) }}" class="form-control" name="nombre" id="nombre" placeholder="Ingrese el nombre completo">
                                </div>
                                <div class="form-group ">
                                    <label for="usuario">Usuario</label>
                                    <input type="text" value="{{$usuario->usuario}}" class="form-control" id="usuario" readonly disabled>
                                    <small class="form-text text-muted">El usuario de acceso se asigna automáticamente al crear la cuenta y no puede modificarse.</small>
                                </div>
                                <div class="form-group ">
                                    <label for="identificacion">Identificación (carné de identidad)</label>
                                    <input type="text" value="{{ old('identificacion', $usuario->identificacion) }}" class="form-control" name="identificacion" id="identificacion" placeholder="Ingrese el número de identificación">
                                    <small class="form-text text-muted">Si cambia este número, la contraseña del usuario se actualizará con el nuevo valor.</small>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="rol">Rol</label>
                                    @php($rolActual = $usuario->roles->first())
                                    <select name="rol" id="rol" class="form-control">
                                        <option value="">Sin rol</option>
                                        @foreach($roles as $rol)
                                            <option value="{{$rol->id}}" @if(old('rol', $rolActual->id ?? null) == $rol->id)selected @endif>{{$rol->nombre}}</option>
                                        @endforeach
                                    </select>
                                </div>


                            </div>
                        </div>
                        <!-- /.card-body -->

                        <div class="card-footer">
                            <a href="{{route('usuarios.listar')}}" class="btn btn-danger">Volver</a>

                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>

            </div>

        </div>

    </div>

@endsection
