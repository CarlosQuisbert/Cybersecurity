@extends('panel.plantillas.base')


@section('titulo')
    Inicio
@endsection

@section('contenido')

    @php($etiquetas = ['critico' => 'Crítico', 'alto' => 'Alto', 'medio' => 'Medio', 'bajo' => 'Bajo'])

    <div class="section-header">
        <h1>Centro de operaciones de seguridad</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-folder-open"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>En la cola</h4></div>
                        <div class="card-body">{{ $estadisticas['abiertos'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Críticos abiertos / capacidad</h4></div>
                        <div class="card-body {{ $estadisticas['criticos'] > $estadisticas['capacidad'] ? 'text-danger' : '' }}">{{ $estadisticas['criticos'] }} / {{ $estadisticas['capacidad'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning"><i class="fas fa-calculator"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Sin evaluar</h4></div>
                        <div class="card-body">{{ $estadisticas['sin_evaluar'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success"><i class="fas fa-inbox"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Eventos por calificar</h4></div>
                        <div class="card-body">{{ $estadisticas['por_calificar'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($estadisticas['criticos'] > $estadisticas['capacidad'])
            <div class="alert alert-danger">Los incidentes críticos abiertos superan la capacidad de atención simultánea del equipo ({{ $estadisticas['capacidad'] }}).</div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="card"><div class="card-header"><h4>En la cola por nivel de prioridad</h4></div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($porPrioridad as $nivel => $total)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    @include('parciales.prioridad', ['nivel' => $nivel]) <span class="badge badge-light badge-pill">{{ $total }}</span>
                                </li>
                            @endforeach
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                @include('parciales.prioridad', ['nivel' => null]) <span class="badge badge-light badge-pill">{{ $estadisticas['sin_evaluar'] }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card"><div class="card-header"><h4>Todos los registros por estado</h4></div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($porEstado as $estado => $total)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ str_replace('_', ' ', $estado) }} <span class="badge badge-primary badge-pill">{{ $total }}</span>
                                </li>
                            @endforeach
                            @if(count($porEstado) === 0)<p class="text-muted">Sin datos.</p>@endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card"><div class="card-header d-flex justify-content-between"><h4>Primeros en la cola de prioridad</h4>
                        <a href="{{ route('cola.listar') }}" class="btn btn-sm btn-light">Ver cola completa</a></div>
                    <div class="card-body p-0">
                        <table class="table table-striped" data-apilable>
                            <thead><tr><th>#</th><th>Código</th><th>Título</th><th>Índice</th><th>Prioridad</th><th>Estado</th><th></th></tr></thead>
                            <tbody>
                            @forelse($cola as $posicion => $incidente)
                                <tr>
                                    <td>{{ $posicion + 1 }}</td>
                                    <td><strong>{{ $incidente->codigo }}</strong></td>
                                    <td>{{ $incidente->titulo }}</td>
                                    <td class="indice-riesgo">{{ $incidente->indice_riesgo !== null ? number_format($incidente->indice_riesgo, 2, ',', '') : '-' }}</td>
                                    <td>@include('parciales.prioridad', ['nivel' => $incidente->nivel_prioridad])</td>
                                    <td><span class="badge badge-info">{{ str_replace('_', ' ', $incidente->estado) }}</span></td>
                                    <td><a href="{{ route('incidentes.mostrar', $incidente->id) }}" class="btn btn-sm btn-primary">Ver</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">No hay incidentes en la cola.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
