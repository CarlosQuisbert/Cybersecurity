@extends('panel.plantillas.base')

@section('titulo')
    Pesos y umbrales
@endsection

@section('contenido')

    @php
        $num = fn ($v, $d = 3) => number_format((float) $v, $d, ',', '');
        $rango = collect($pesosGlobales)->sortDesc()->keys()->flip();
    @endphp

    <div class="section-header">
        <h1>Configuración del modelo de priorización</h1>
    </div>

    <div class="section-body">
        @include('parciales.errores_formulario')

        @if($vigente)
            <div class="row">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header"><h4>Versión vigente: {{ $vigente->version }}</h4></div>
                        <div class="card-body">
                            <p class="texto-pequeno text-muted">{{ $vigente->descripcion }}</p>
                            <table class="table table-sm table-striped" data-apilable>
                                <thead><tr><th>Bloque</th><th>Criterio</th><th>Peso del bloque</th><th>Peso local</th><th>Peso global</th><th>Orden</th></tr></thead>
                                <tbody>
                                @foreach($bloques as $bloque)
                                    @foreach($bloque->criterios as $criterio)
                                        <tr>
                                            <td>{{ $bloque->nombre }}</td>
                                            <td><strong>{{ $criterio->nombre }}</strong></td>
                                            <td>{{ $num($pesosBloque[$bloque->id] ?? 0) }}</td>
                                            <td>{{ $num($pesosCriterio[$criterio->id] ?? 0) }}</td>
                                            <td class="indice-riesgo">{{ $num($pesosGlobales[$criterio->codigo] ?? 0) }}</td>
                                            <td>{{ isset($rango[$criterio->codigo]) ? $rango[$criterio->codigo] + 1 : '-' }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                <tr><td></td><td><strong>Suma</strong></td><td></td><td></td><td class="indice-riesgo">{{ $num(array_sum($pesosGlobales)) }}</td><td></td></tr>
                                </tbody>
                            </table>
                            <p class="texto-pequeno text-muted mb-0">Los pesos de los bloques y los pesos locales de cada bloque se normalizan para que sumen 1 antes de multiplicarse.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header"><h4>Umbrales y parámetros</h4></div>
                        <div class="card-body">
                            <ul class="list-group mb-3">
                                <li class="list-group-item d-flex justify-content-between">@include('parciales.prioridad', ['nivel' => 'critico']) <span>índice ≥ {{ $num($vigente->umbral_critico, 2) }}</span></li>
                                <li class="list-group-item d-flex justify-content-between">@include('parciales.prioridad', ['nivel' => 'alto']) <span>{{ $num($vigente->umbral_alto, 2) }} ≤ índice &lt; {{ $num($vigente->umbral_critico, 2) }}</span></li>
                                <li class="list-group-item d-flex justify-content-between">@include('parciales.prioridad', ['nivel' => 'medio']) <span>{{ $num($vigente->umbral_medio, 2) }} ≤ índice &lt; {{ $num($vigente->umbral_alto, 2) }}</span></li>
                                <li class="list-group-item d-flex justify-content-between">@include('parciales.prioridad', ['nivel' => 'bajo']) <span>índice &lt; {{ $num($vigente->umbral_medio, 2) }}</span></li>
                            </ul>
                            <p class="mb-1"><strong>Capacidad:</strong> {{ $vigente->capacidad_criticos }} incidentes críticos en simultáneo</p>
                            <p class="mb-1"><strong>ATT&amp;CK:</strong> {{ $vigente->version_attack }}</p>
                            @if($vigente->origen_pesos)
                                <p class="mb-1"><strong>Origen de los pesos:</strong> {{ $vigente->origen_pesos['metodo'] ?? '-' }}
                                    @isset($vigente->origen_pesos['expertos']) · {{ $vigente->origen_pesos['expertos'] }} expertos @endisset</p>
                            @endif
                            <hr>
                            <p class="mb-2">Incidentes evaluados: <strong>{{ $evaluados }}</strong>@if($desactualizados) · <span class="text-warning">{{ $desactualizados }} calculados con una versión anterior</span>@endif</p>
                            <form method="POST" action="{{ route('modelo.recalcular') }}" data-confirmar="¿Desea recalcular todos los incidentes evaluados con la versión {{ $vigente->version }}?">
                                @csrf
                                <button class="btn btn-outline-primary btn-block"><i class="fas fa-sync mr-1"></i>Recalcular incidentes con la versión vigente</button>
                            </form>
                            <p class="texto-pequeno text-muted mt-2 mb-0">Se conservan los niveles elegidos por los analistas y las evaluaciones anteriores.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header"><h4>Publicar una nueva versión</h4></div>
            <div class="card-body">
                <p class="texto-pequeno text-muted">La versión actual se conserva: cada resultado guarda la versión con la que se calculó. Los pesos también pueden obtenerse en <a href="{{ route('modelo.ahp') }}">Cálculo AHP</a>.</p>
                <form method="POST" action="{{ route('modelo.configuraciones.guardar') }}" data-confirmar="¿Desea publicar una nueva versión de la configuración?">
                    @csrf
                    <div class="row">
                        @foreach($bloques as $bloque)
                            <div class="col-md-6 col-lg-3 mb-3">
                                <div class="border rounded p-2 h-100">
                                    <label class="font-weight-bold mb-1">{{ $bloque->nombre }}</label>
                                    <input type="number" step="0.001" min="0" max="1" class="form-control form-control-sm mb-2" name="pesos_bloque[{{ $bloque->id }}]"
                                           value="{{ old('pesos_bloque.' . $bloque->id, (float) ($pesosBloque[$bloque->id] ?? 0)) }}" required>
                                    @foreach($bloque->criterios as $criterio)
                                        <label class="texto-pequeno mb-0">{{ $criterio->nombre }} (local)</label>
                                        <input type="number" step="0.001" min="0" max="1" class="form-control form-control-sm mb-1" name="pesos_criterio[{{ $criterio->id }}]"
                                               value="{{ old('pesos_criterio.' . $criterio->id, (float) ($pesosCriterio[$criterio->id] ?? 0)) }}" required>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-2"><label>Umbral crítico</label><input type="number" step="0.01" name="umbral_critico" class="form-control" value="{{ old('umbral_critico', optional($vigente)->umbral_critico) }}" required></div>
                        <div class="form-group col-md-2"><label>Umbral alto</label><input type="number" step="0.01" name="umbral_alto" class="form-control" value="{{ old('umbral_alto', optional($vigente)->umbral_alto) }}" required></div>
                        <div class="form-group col-md-2"><label>Umbral medio</label><input type="number" step="0.01" name="umbral_medio" class="form-control" value="{{ old('umbral_medio', optional($vigente)->umbral_medio) }}" required></div>
                        <div class="form-group col-md-2"><label>Capacidad críticos</label><input type="number" name="capacidad_criticos" class="form-control" value="{{ old('capacidad_criticos', optional($vigente)->capacidad_criticos ?? 3) }}" required></div>
                        <div class="form-group col-md-4"><label>Versión de ATT&amp;CK</label><input name="version_attack" class="form-control" value="{{ old('version_attack', optional($vigente)->version_attack) }}" required></div>
                    </div>
                    <div class="form-group"><label>Descripción del cambio</label><textarea name="descripcion" rows="2" class="form-control" placeholder="Motivo: umbrales del apartado 3.3.1.6, nuevo panel de expertos, escenario de sensibilidad...">{{ old('descripcion') }}</textarea></div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="recalcular" value="1" id="casillaRecalcular" checked>
                        <label class="form-check-label" for="casillaRecalcular">Recalcular los incidentes evaluados con la nueva versión</label>
                    </div>
                    <button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Publicar versión</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4>Historial de versiones</h4></div>
            <div class="card-body">
                <table class="table table-sm table-striped" data-apilable>
                    <thead><tr><th>Versión</th><th>Fecha</th><th>Autor</th><th>Umbrales (crítico / alto / medio)</th><th>Descripción</th><th></th></tr></thead>
                    <tbody>
                    @foreach($versiones as $version)
                        <tr>
                            <td><strong>{{ $version->version }}</strong> @if($version->vigente)<span class="badge badge-success">vigente</span>@endif</td>
                            <td>{{ optional($version->creado_en)->format('d/m/Y H:i') }}</td>
                            <td>{{ optional($version->autor)->nombre ?? 'Carga inicial' }}</td>
                            <td>{{ $num($version->umbral_critico, 2) }} / {{ $num($version->umbral_alto, 2) }} / {{ $num($version->umbral_medio, 2) }}</td>
                            <td class="texto-pequeno">{{ $version->descripcion }}</td>
                            <td>
                                @unless($version->vigente)
                                    <form method="POST" action="{{ route('modelo.configuraciones.activar', $version->id) }}" data-confirmar="¿Desea volver a poner vigente la versión {{ $version->version }}?">
                                        @csrf
                                        <button class="btn btn-sm btn-light">Poner vigente</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
