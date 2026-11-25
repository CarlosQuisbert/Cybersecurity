@extends('panel.plantillas.base')

@section('titulo')
    Usuarios
@endsection

@section('estilos')

    <link rel="stylesheet" href="{{asset('admin/datatables/dataTables.bootstrap4.css')}}">

@endsection

@section('contenido')

    <div class="section-header">
        <h1>Usuarios</h1>
    </div>

    <div class="section-body" style="">

        <div class="row">
            <div class="col-12">

                <div class="card" style="padding: 20px">

                    <div>
                        <div class="box-header"  >
                            @include('parciales.errores_formulario')
                            <br>
                        </div>
                        <div class="d-flex justify-content-end mb-3">
                            <a href="{{route('usuarios.crear')}}" class="btn btn-primary btn-agregar">
                                <i class="fas fa-user-plus mr-2"></i>Agregar usuario
                            </a>
                        </div>

                        <!-- /.card-header -->
                        <div class="box-body">

                            <table id="tabla-datos" class="table table-bordered table-striped w-100">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nombre</th>
                                    <th>Usuario</th>
                                    <th>Rol asignado</th>
                                    <th>Editar</th>
                                    <th>Eliminar</th>

                                </tr>
                                </thead>
                                <tbody>

                                @if($usuarios)
                                    @foreach($usuarios as $usuario)
                                        <tr>
                                            <td>{{$usuario->id}}</td>
                                            <td>{{$usuario->nombre}}</td>
                                            <td>{{$usuario->usuario}}</td>
                                            <td> {{$usuario->roles->pluck('nombre')->implode(' | ')}}</td>

                                            <td><a href="{{route('usuarios.editar', $usuario->id)}}"><span class="fas fa-edit"></span></a></td>
                                            <td>

                                                <form method="post" id="form-eliminar-{{$usuario->id}}" action="{{route('usuarios.eliminar', $usuario->id)}}">
                                                    {{csrf_field()}}
                                                    {{method_field('DELETE')}}

                                                </form>

                                                <a href="" onclick="
                                                    if(confirm('¿Está seguro de que desea eliminar esto?'))
                                                    {
                                                    event.preventDefault();document.getElementById('form-eliminar-{{$usuario->id}}').submit();}
                                                    else
                                                    {event.preventDefault();}">
                                                    <span class="text-center fas fa-trash" ></span></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Nombre</th>
                                    <th>Usuario</th>
                                    <th>Rol asignado</th>
                                    <th>Editar</th>
                                    <th>Eliminar</th>

                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>

            </div>
        </div>

    </div>


@endsection

@section('scripts')

    <script src="{{asset('admin/datatables/jquery.dataTables.js')}}"></script>
    <script src="{{asset('admin/datatables/dataTables.bootstrap4.js')}}"></script>

    <script>
        $(function () {
            $("#tabla-datos").DataTable(window.opcionesTablaDatos());
        });
    </script>

@endsection
