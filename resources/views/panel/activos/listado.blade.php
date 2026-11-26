@extends('panel.plantillas.base')

@section('titulo')
    Inventario de activos
@endsection

@section('estilos')
    <link rel="stylesheet" href="{{asset('admin/datatables/dataTables.bootstrap4.css')}}">
@endsection

@section('contenido')

    <div class="section-header">
        <h1>Inventario de activos</h1>
    </div>

    <div class="section-body">
        <div class="card" style="padding: 20px">
            <p class="text-muted texto-pequeno">
                La criticidad de cada activo alimenta el criterio «criticidad del activo»: el analista no la estima.
                Si el activo afectado no figura aquí, el incidente se valora con criticidad 1 y la omisión queda registrada abajo.
            </p>
            <activos datos_activos="{{ $activos->toJson() }}"
                     datos_descriptores="{{ json_encode(optional(optional(\App\Models\Escala::where('codigo', 'criticidad_activo')->first())->niveles)->pluck('descriptor') ?? []) }}"></activos>
        </div>

        <div class="card">
            <div class="card-header"><h4>Omisiones del inventario</h4></div>
            <div class="card-body">
                <p class="text-muted texto-pequeno">Incidentes evaluados cuyo activo no figura en el inventario: son requerimientos de actualización del inventario.</p>
                <table class="table table-sm table-striped" data-apilable>
                    <thead><tr><th>Incidente</th><th>Título</th><th>Activo indicado</th><th>Evaluado</th><th></th></tr></thead>
                    <tbody>
                    @forelse($omisiones as $incidente)
                        <tr>
                            <td><strong>{{ $incidente->codigo }}</strong></td>
                            <td>{{ $incidente->titulo }}</td>
                            <td>{{ $incidente->activos_afectados ?: '-' }}</td>
                            <td>{{ optional($incidente->evaluado_en)->format('d/m/Y H:i') }}</td>
                            <td><a href="{{ route('incidentes.mostrar', $incidente->id) }}" class="btn btn-sm btn-light">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Sin omisiones.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{asset('admin/datatables/jquery.dataTables.js')}}"></script>
    <script src="{{asset('admin/datatables/dataTables.bootstrap4.js')}}"></script>
@endsection
