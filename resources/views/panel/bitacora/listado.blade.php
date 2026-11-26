@extends('panel.plantillas.base')

@section('titulo')
    Bitácora
@endsection

@section('contenido')

    <div class="section-header">
        <h1>Bitácora de acciones</h1>
    </div>

    <div class="section-body">
        <div class="card" style="padding: 20px">
            <p class="text-muted texto-pequeno">Registro de las acciones de cada usuario: sesiones, registro y edición de incidentes, evaluaciones, seguimiento, evidencias y cambios en la configuración del modelo.</p>
            <table class="table table-striped table-sm" data-apilable>
                <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Entidad</th><th>Detalle</th><th>IP</th></tr></thead>
                <tbody>
                @forelse($registros as $registro)
                    <tr>
                        <td>{{ optional($registro->creado_en)->format('d/m/Y H:i:s') }}</td>
                        <td>{{ optional($registro->usuario)->nombre ?? '-' }}</td>
                        <td><code>{{ $registro->accion }}</code></td>
                        <td>{{ $registro->entidad ? $registro->entidad . ' #' . $registro->entidad_id : '-' }}</td>
                        <td class="texto-pequeno" style="max-width:420px">{{ $registro->detalle ? json_encode($registro->detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '' }}</td>
                        <td>{{ $registro->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Sin registros.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $registros->links() }}
        </div>
    </div>

@endsection
