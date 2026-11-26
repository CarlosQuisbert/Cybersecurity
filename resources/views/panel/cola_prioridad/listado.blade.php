@extends('panel.plantillas.base')

@section('titulo')
    Cola de prioridad
@endsection

@section('contenido')

    <div class="section-header">
        <h1>Cola de prioridad</h1>
    </div>

    <div class="section-body">
        @if($configuracion && $criticosAbiertos > $configuracion->capacidad_criticos)
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Hay <strong>{{ $criticosAbiertos }}</strong> incidentes críticos abiertos y la capacidad del equipo es de
                <strong>{{ $configuracion->capacidad_criticos }}</strong> en simultáneo.
            </div>
        @endif

        <div class="card" style="padding: 20px">
            <p class="text-muted texto-pequeno">
                Incidentes confirmados y no cerrados, ordenados por índice de riesgo. Ante un empate se atiende primero
                el de mayor progresión adversaria y, después, el más antiguo. Los incidentes sin evaluar van al final.
                @if($configuracion) Configuración vigente: versión {{ $configuracion->version }}
                (crítico ≥ {{ rtrim(rtrim(number_format($configuracion->umbral_critico, 2, ',', ''), '0'), ',') }},
                alto ≥ {{ rtrim(rtrim(number_format($configuracion->umbral_alto, 2, ',', ''), '0'), ',') }},
                medio ≥ {{ rtrim(rtrim(number_format($configuracion->umbral_medio, 2, ',', ''), '0'), ',') }}). @endif
            </p>
            <table class="table table-striped" data-apilable>
                <thead>
                <tr><th>#</th><th>Incidente</th><th>Categoría</th><th>Índice</th><th>Prioridad</th><th>Progresión</th><th>Estado</th><th>Detectado</th><th></th></tr>
                </thead>
                <tbody>
                @forelse($cola as $posicion => $incidente)
                    <tr>
                        <td><strong>{{ $posicion + 1 }}</strong></td>
                        <td><strong>{{ $incidente->codigo }}</strong><div class="texto-pequeno">{{ $incidente->titulo }}</div></td>
                        <td class="texto-pequeno">{{ optional($incidente->tipo)->nombre }}</td>
                        <td class="indice-riesgo">{{ $incidente->indice_riesgo !== null ? number_format($incidente->indice_riesgo, 2, ',', '') : '-' }}</td>
                        <td>@include('parciales.prioridad', ['nivel' => $incidente->nivel_prioridad])</td>
                        <td>{{ $incidente->progresion_nivel ?: ($incidente->origen === 'no_adversario' ? 'No aplica' : '-') }}</td>
                        <td><span class="badge badge-info">{{ str_replace('_', ' ', $incidente->estado) }}</span>
                            <div class="texto-pequeno">{{ optional($incidente->usuarioAsignado)->nombre ?? 'Sin asignar' }}</div></td>
                        <td class="texto-pequeno">{{ optional($incidente->detectado_en ?? $incidente->creado_en)->format('d/m/Y H:i') }}</td>
                        <td><a href="{{ route('incidentes.mostrar', $incidente->id) }}" class="btn btn-sm btn-primary">{{ $incidente->nivel_prioridad ? 'Ver' : 'Evaluar' }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">No hay incidentes en la cola.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
