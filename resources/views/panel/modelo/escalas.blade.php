@extends('panel.plantillas.base')

@section('titulo')
    Escalas de valoración
@endsection

@section('contenido')

    <div class="section-header">
        <h1>Escalas de valoración</h1>
    </div>

    <div class="section-body">
        @include('parciales.errores_formulario')
        <div class="alert alert-light">
            Cada escala tiene cinco niveles con su criterio de asignación documentado (apartado 3.3.1.4). Sin estos descriptores,
            la subjetividad volvería a la decisión de puntuación. Cada evaluación guarda el texto del descriptor elegido, por lo que
            editar una escala no altera los resultados anteriores.
        </div>

        @foreach($criterios as $criterio)
            <div class="card">
                <div class="card-header d-block">
                    <h4 class="mb-1">{{ $criterio->nombre }} <small class="text-muted">· {{ $criterio->bloque->nombre }}</small></h4>
                    <div class="texto-pequeno text-muted"><strong>Fundamento:</strong> {{ $criterio->fundamento }}</div>
                    @if($criterio->descripcion)<div class="texto-pequeno text-muted">{{ $criterio->descripcion }}</div>@endif
                    <div class="texto-pequeno">
                        <strong>Fuente del nivel:</strong>
                        {{ ['analista' => 'el analista', 'inventario' => 'el inventario de activos', 'tacticas' => 'la táctica ATT&CK más avanzada'][$criterio->fuente] ?? $criterio->fuente }}
                        @if($criterio->combinacion === 'maximo') · se toma el mayor de sus subescalas @endif
                        @if($criterio->solo_adversario) · solo aplica a incidentes de origen adversario @endif
                    </div>
                </div>
                <div class="card-body">
                    @foreach($criterio->escalas as $escala)
                        <form method="POST" action="{{ route('modelo.escalas.actualizar', $escala->id) }}" class="mb-4"
                              data-confirmar="¿Desea guardar los descriptores de «{{ $escala->nombre }}»?">
                            @csrf
                            @method('PUT')
                            @if($criterio->escalas->count() > 1)<h6>{{ $escala->nombre }}</h6>@endif
                            @foreach($escala->niveles as $nivel)
                                <div class="input-group input-group-sm mb-1">
                                    <div class="input-group-prepend"><span class="input-group-text" style="width:70px">Nivel {{ $nivel->nivel }}</span></div>
                                    <input name="niveles[{{ $nivel->nivel }}]" class="form-control" value="{{ $nivel->descriptor }}" required>
                                </div>
                            @endforeach
                            <div class="input-group input-group-sm mb-2">
                                <div class="input-group-prepend"><span class="input-group-text" style="width:70px">Nota</span></div>
                                <input name="nota" class="form-control" value="{{ $escala->nota }}" placeholder="Aclaración para el analista (opcional)">
                            </div>
                            <button class="btn btn-sm btn-primary">Guardar descriptores</button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

@endsection
