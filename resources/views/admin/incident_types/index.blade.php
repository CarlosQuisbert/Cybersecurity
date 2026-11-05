@extends('admin.layouts.base')


@section('title')
    Tipos de incidente
@endsection

@section('styles')

    <link rel="stylesheet" href="{{asset('admin/datatables/dataTables.bootstrap4.css')}}">


@endsection

@section('content')

    <div class="section-header">
        <h1>Tipos de incidente</h1>
    </div>

    <div class="section-body" style="">

        <div class="row">

            <div class="col-12" >

                <div class="card" style="padding: 20px">

                    <incident-types raw_types="{{ $types->toJson() }}"></incident-types>

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