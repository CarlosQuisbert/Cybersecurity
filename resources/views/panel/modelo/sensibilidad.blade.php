@extends('panel.plantillas.base')

@section('titulo')
    Análisis de sensibilidad
@endsection

@section('contenido')

    @php($num = fn ($v, $d = 3) => $v === null ? '-' : number_format((float) $v, $d, ',', ''))

    <div class="section-header">
        <h1>Análisis de sensibilidad de los pesos</h1>
    </div>

    <div class="section-body">
        <div class="alert alert-light">
            Se varía el peso de cada bloque en −20 %, −10 %, +10 % y +20 %, redistribuyendo la diferencia de forma proporcional entre los
            demás bloques, y se recalcula el banco de casos (incidentes evaluados, con los niveles de su última evaluación).
            La estabilidad se mide con la proporción de casos que cambian de nivel de prioridad y con la correlación de rangos de
            Spearman entre el ordenamiento original y el modificado (apartado 3.5.2). Configuración base: versión {{ $vigente->version }}.
        </div>

        @if(! $analisis)
            <div class="card"><div class="card-body text-muted">
                Se necesitan al menos dos incidentes evaluados para el análisis (hay {{ count($casos) }}).
            </div></div>
        @else
            <div class="card">
                <div class="card-header"><h4>Resultados por bloque y escenario · {{ count($casos) }} casos</h4></div>
                <div class="card-body">
                    <table class="table table-sm table-striped" data-apilable>
                        <thead><tr><th>Bloque</th><th>Variación</th><th>Peso original</th><th>Peso modificado</th><th>Casos que cambian de nivel</th><th>Proporción</th><th>Spearman (ρ)</th></tr></thead>
                        <tbody>
                        @foreach($analisis['escenarios'] as $fila)
                            <tr>
                                <td>{{ $nombresBloque[$fila['bloque']] ?? $fila['bloque'] }}</td>
                                <td>{{ $fila['variacion'] > 0 ? '+' : '' }}{{ (int) round($fila['variacion'] * 100) }} %</td>
                                <td>{{ $num($fila['peso_original']) }}</td>
                                <td>{{ $num($fila['peso_variado']) }}</td>
                                <td>{{ $fila['cambios_nivel'] }} de {{ $fila['casos'] }}</td>
                                <td class="{{ $fila['proporcion_cambios'] > 0.2 ? 'text-danger' : '' }}">{{ $num($fila['proporcion_cambios'] * 100, 1) }} %</td>
                                <td class="indice-riesgo">{{ $num($fila['spearman']) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4>Ordenamiento base</h4></div>
                <div class="card-body">
                    <table class="table table-sm" data-apilable>
                        <thead><tr><th>Incidente</th><th>Índice</th><th>Prioridad</th></tr></thead>
                        <tbody>
                        @foreach(collect($analisis['base'])->sortByDesc('indice') as $codigo => $r)
                            <tr><td>{{ $codigo }}</td><td class="indice-riesgo">{{ $num($r['indice'], 2) }}</td><td>@include('parciales.prioridad', ['nivel' => $r['nivel']])</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

@endsection
