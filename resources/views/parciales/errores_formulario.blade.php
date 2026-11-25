<div class=" ">
    @if(count($errors) > 0)
        <div class="alert alert-danger" style="margin-top: 20px;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>



@if(session()->has('mensaje'))

{{--    <p class="alert alert-success" style="margin-top: 20px;">{{session('mensaje')}}</p>--}}
    <div class="alert alert-light alert-dismissible col-lg-8 col-lg-offset-2" style="margin-left: 15px; margin-top: 15px">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="icon fas fa-check"></i> ¡Aviso!</h4>
        {{session('mensaje')}}
    </div>
@endif
@if(session()->has('mensaje_eliminado'))

    {{--    <p class="alert alert-success" style="margin-top: 20px;">{{session('mensaje')}}</p>--}}
    <div class="alert alert-light alert-dismissible col-lg-8 col-lg-offset-2" style="margin-left: 15px; margin-top: 15px">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="icon fas fa-check"></i> ¡Aviso!</h4>
        {{session('mensaje_eliminado')}}
    </div>
@endif
@if(session()->has('mensaje_actualizado'))

    {{--    <p class="alert alert-success" style="margin-top: 20px;">{{session('mensaje')}}</p>--}}
    <div class="alert alert-light alert-dismissible col-lg-8col-lg-8 col-lg-offset-2" style="margin-left: 15px; margin-top: 15px">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="icon fas fa-check"></i> ¡Aviso!</h4>
        {{session('mensaje_actualizado')}}
    </div>
@endif