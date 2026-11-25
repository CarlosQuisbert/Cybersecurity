@extends('panel.plantillas.base')


@section('titulo')
    Tipos de incidente
@endsection

@section('estilos')

    <link rel="stylesheet" href="{{asset('admin/datatables/dataTables.bootstrap4.css')}}">


@endsection

@section('contenido')

    <div class="section-header">
        <h1>Tipos de incidente</h1>
    </div>

    <div class="section-body" style="">

        <div class="row">

            <div class="col-12" >

                <div class="card" style="padding: 20px">

                    <tipos-incidente datos_tipos="{{ $tipos->toJson() }}"></tipos-incidente>

                </div>


            </div>

        </div>

    </div>

@endsection

@section('scripts')


    <script src="{{asset('admin/datatables/jquery.dataTables.js')}}"></script>
    <script src="{{asset('admin/datatables/dataTables.bootstrap4.js')}}"></script>


@endsection