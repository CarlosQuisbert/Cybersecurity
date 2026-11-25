@extends('panel.plantillas.base')


@section('titulo')
    Detalle incidente {{ $incidente->codigo }}
@endsection

@section('contenido')

    <div class="section-header">
        <h1>{{ $incidente->codigo }} - {{ $incidente->titulo }}</h1>
        <div class="section-header-breadcrumb">
            <a href="{{ route('incidentes.listar') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>

    <div class="section-body">
        <detalle-incidente datos_incidente="{{ $incidente->toJson() }}" datos_tipos="{{ $tipos->toJson() }}" datos_usuarios="{{ $usuarios->toJson() }}"></detalle-incidente>
    </div>

@endsection