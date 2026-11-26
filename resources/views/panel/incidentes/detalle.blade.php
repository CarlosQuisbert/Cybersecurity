@extends('panel.plantillas.base')


@section('titulo')
    Detalle incidente {{ $incidente->codigo }}
@endsection

@section('contenido')

    <div class="section-header">
        <h1>{{ $incidente->codigo }} - {{ $incidente->titulo }}</h1>
        <div class="section-header-breadcrumb">
            <a href="{{ route('cola.listar') }}" class="btn btn-light mr-2">Cola de prioridad</a>
            <a href="{{ route('incidentes.listar') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>

    <div class="section-body">
        <detalle-incidente
            datos_incidente="{{ $incidente->toJson() }}"
            datos_estructura="{{ $estructura->toJson() }}"
            datos_configuracion="{{ json_encode($configuracion ? [
                'id' => $configuracion->id,
                'version' => $configuracion->version,
                'umbrales' => $configuracion->umbrales(),
                'capacidad_criticos' => $configuracion->capacidad_criticos,
                'pesos' => $configuracion->pesosGlobales(),
            ] : null) }}"
            datos_tipos="{{ $tipos->toJson() }}"
            datos_usuarios="{{ $usuarios->toJson() }}"
            datos_tacticas="{{ $tacticas->toJson() }}"
            datos_activos="{{ $activos->toJson() }}"></detalle-incidente>
    </div>

@endsection
