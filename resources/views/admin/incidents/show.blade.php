@extends('admin.layouts.base')


@section('title')
    Detalle incidente {{ $incident->code }}
@endsection

@section('content')

    <div class="section-header">
        <h1>{{ $incident->code }} - {{ $incident->title }}</h1>
        <div class="section-header-breadcrumb">
            <a href="{{ route('incidents.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>

    <div class="section-body">
        <incident-detail raw_incident="{{ $incident->toJson() }}" raw_types="{{ $types->toJson() }}" raw_admins="{{ $admins->toJson() }}"></incident-detail>
    </div>

@endsection