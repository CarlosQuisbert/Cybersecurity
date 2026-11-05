@extends('admin.layouts.base')

@section('title')
    Editar usuario - {{$user->name}}
@endsection

@section('content')

<div class="section-header">
    <h1>Editar usuario - <b><i>{{$user->name}}</i></b></h1>
</div>

<div class="section-body" style="">


    <div class="row">
            <div class="col-12">

                <div class="card ">
                    <div class="col-lg-6 ">
                        @include('includes.form_error')
                    </div>
                    <!-- form start -->
                    <form role="form" method="post" action="{{route('users.update', $user->id)}}" enctype="multipart/form-data">
                        {{csrf_field()}}
                        {{method_field('PATCH')}}
                        <div class="card-body">

                            <div class="col-lg-5">

                                <div class="form-group ">
                                    <label for="name">Nombre completo</label>
                                    <input type="text" value="@if(old('name')) {{old('name')}} @else {{$user->name}} @endif" class="form-control" name="name" id="name" placeholder="Ingrese el nombre completo">
                                </div>
                                <div class="form-group ">
                                    <label for="usuario">Usuario</label>
                                    <input type="text" value="{{$user->usuario}}" class="form-control" id="usuario" readonly disabled>
                                    <small class="form-text text-muted">El usuario de acceso se asigna automáticamente al crear la cuenta y no puede modificarse.</small>
                                </div>
                                <div class="form-group ">
                                    <label for="identificacion">Identificación (carné de identidad)</label>
                                    <input type="text" value="@if(old('identificacion')) {{old('identificacion')}} @else {{$user->identificacion}} @endif" class="form-control" name="identificacion" id="identificacion" placeholder="Ingrese el número de identificación">
                                    <small class="form-text text-muted">Si cambia este número, la contraseña del usuario se actualizará con el nuevo valor.</small>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="role">Rol</label>
                                    @php($currentRole = $user->roles->first())
                                    <select name="role" id="role" class="form-control">
                                        <option value="">Sin rol</option>
                                        @foreach($roles as $role)
                                            <option value="{{$role->id}}" @if(old('role', $currentRole->id ?? null) == $role->id)selected @endif>{{$role->name}}</option>
                                        @endforeach
                                    </select>
                                </div>


                            </div>
                        </div>
                        <!-- /.card-body -->

                        <div class="card-footer">
                            <a href="{{route('users.index')}}" class="btn btn-danger">Volver</a>

                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>

            </div>

        </div>

    </div>

@endsection
