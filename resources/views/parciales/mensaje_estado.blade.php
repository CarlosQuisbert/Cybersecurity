@if(session()->has('mensaje'))

    {{--    <p class="alert alert-success" style="margin-top: 20px;">{{session('mensaje')}}</p>--}}
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="icon fa fa-check"></i> ¡Aviso!</h4>
        {{session('mensaje')}}
    </div>
@endif
@if(session()->has('mensaje_eliminado'))

    {{--    <p class="alert alert-success" style="margin-top: 20px;">{{session('mensaje')}}</p>--}}
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="icon fa fa-check"></i> ¡Aviso!</h4>
        {{session('mensaje_eliminado')}}
    </div>
@endif
@if(session()->has('mensaje_actualizado'))

    {{--    <p class="alert alert-success" style="margin-top: 20px;">{{session('mensaje')}}</p>--}}
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="icon fa fa-check"></i> ¡Aviso!</h4>
        {{session('mensaje_actualizado')}}
    </div>
@endif