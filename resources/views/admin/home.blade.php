@extends('admin.layouts.base')


@section('title')
    Inicio
@endsection

@section('content')

    <div class="section-header">
        <h1>Centro de operaciones de seguridad</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-shield-alt"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Total incidentes</h4></div>
                        <div class="card-body">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning"><i class="fas fa-folder-open"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Abiertos</h4></div>
                        <div class="card-body">{{ $stats['open'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Críticos abiertos</h4></div>
                        <div class="card-body">{{ $stats['critical'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success"><i class="fas fa-tasks"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>En proceso</h4></div>
                        <div class="card-body">{{ $stats['in_progress'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card"><div class="card-header"><h4>Por estado</h4></div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($byStatus as $status => $total)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $status }} <span class="badge badge-primary badge-pill">{{ $total }}</span>
                                </li>
                            @endforeach
                            @if(count($byStatus) === 0)<p class="text-muted">Sin datos.</p>@endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card"><div class="card-header"><h4>Por severidad</h4></div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($bySeverity as $sev => $total)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $sev }} <span class="badge badge-warning badge-pill">{{ $total }}</span>
                                </li>
                            @endforeach
                            @if(count($bySeverity) === 0)<p class="text-muted">Sin datos.</p>@endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card"><div class="card-header"><h4>Incidentes recientes</h4></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tr><th>Código</th><th>Título</th><th>Severidad</th><th>Estado</th><th>Fecha</th><th></th></tr>
                                @foreach($recent as $inc)
                                    <tr>
                                        <td><strong>{{ $inc->code }}</strong></td>
                                        <td>{{ $inc->title }}</td>
                                        <td><span class="badge badge-danger">{{ $inc->severity }}</span></td>
                                        <td><span class="badge badge-info">{{ $inc->status }}</span></td>
                                        <td>{{ $inc->created_at->format('d/m/Y H:i') }}</td>
                                        <td><a href="{{ route('incidents.show', $inc->id) }}" class="btn btn-sm btn-primary">Ver</a></td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
