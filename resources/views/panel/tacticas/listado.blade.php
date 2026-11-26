@extends('panel.plantillas.base')

@section('titulo')
    Tácticas ATT&CK
@endsection

@section('estilos')
    <link rel="stylesheet" href="{{asset('admin/datatables/dataTables.bootstrap4.css')}}">
@endsection

@section('contenido')

    <div class="section-header">
        <h1>Catálogo de tácticas ATT&amp;CK</h1>
    </div>

    <div class="section-body">
        <div class="card" style="padding: 20px">
            <p class="text-muted texto-pequeno">
                Escala de progresión adversaria (escala j): las tácticas se agrupan por la profundidad de la intrusión.
                El catálogo es un parámetro: cuando se publique una nueva versión de ATT&amp;CK se actualiza aquí, sin modificar el código.
                Versión de referencia: {{ optional(\App\Models\ConfiguracionModelo::vigente())->version_attack ?? '-' }}.
            </p>
            <tacticas datos_tacticas="{{ $tacticas->toJson() }}"></tacticas>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{asset('admin/datatables/jquery.dataTables.js')}}"></script>
    <script src="{{asset('admin/datatables/dataTables.bootstrap4.js')}}"></script>
@endsection
