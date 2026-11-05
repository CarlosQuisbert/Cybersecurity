@extends('admin.layouts.base')

@section('title')
    Usuarios
@endsection

@section('styles')

    <link rel="stylesheet" href="{{asset('admin/datatables/dataTables.bootstrap4.css')}}">

@endsection

@section('content')

    <div class="section-header">
        <h1>Usuarios</h1>
    </div>

    <div class="section-body" style="">

        <div class="row">
            <div class="col-12">

                <div class="card" style="padding: 20px">

                    <div>
                        <div class="box-header"  >
                            @include('includes.form_error')
                            <br>
                        </div>
                        <div class="float-right mb-4">
                            <a style="padding: 10px" href="{{route('users.create')}}" class=" pull-right btn btn-primary">
                                <i class="fas fa-user-plus" style="padding-right: 10px"></i>Agregar usuario
                            </a>
                        </div>
                        <br>

                        <!-- /.card-header -->
                        <div class="box-body">

                            <table id="example1" class="table table-bordered table-striped">
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

                                @if($users)
                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{$user->id}}</td>
                                            <td>{{$user->name}}</td>
                                            <td>{{$user->usuario}}</td>
                                            <td> {{$user->roles->pluck('name')->implode(' | ')}}</td>

                                            <td><a href="{{route('users.edit', $user->id)}}"><span class="fas fa-edit"></span></a></td>
                                            <td>

                                                <form method="post" id="delete-form-{{$user->id}}" action="{{route('users.destroy', $user->id)}}">
                                                    {{csrf_field()}}
                                                    {{method_field('DELETE')}}

                                                </form>

                                                <a href="" onclick="
                                                    if(confirm('¿Está seguro de que desea eliminar esto?'))
                                                    {
                                                    event.preventDefault();document.getElementById('delete-form-{{$user->id}}').submit();}
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
            $("#example1").DataTable();
        });
    </script>

@endsection
