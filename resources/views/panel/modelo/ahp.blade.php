@extends('panel.plantillas.base')

@section('titulo')
    Cálculo AHP
@endsection

@section('contenido')

    <div class="section-header">
        <h1>Cálculo de pesos por proceso analítico jerárquico</h1>
    </div>

    <div class="section-body">
        <calculo-ahp datos_estructura="{{ json_encode($estructura) }}"
                     datos_vigente="{{ json_encode($vigente ? $vigente->only(['version', 'umbral_critico', 'umbral_alto', 'umbral_medio', 'capacidad_criticos', 'version_attack']) : null) }}"></calculo-ahp>
    </div>

@endsection
