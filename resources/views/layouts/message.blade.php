@if($message = Session::get('message'))
    <div class="alert-primary alert-dismissible fade show" role="alert" style="padding: 10px 7px">
        <strong>{{$message }}</strong>
{{--        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
    </div>
@endif

@if($validate = Session::get('validate'))
    <div class="alert-warning alert-dismissible fade show" role="alert"  style="padding: 10px 7px">
        <strong>{{$validate }}</strong>
{{--        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
    </div>
@endif

@if($delete = Session::get('delete'))
    <div class="alert-danger alert-dismissible fade show" role="alert"  style="padding: 10px 7px">
        <strong>{{$delete }}</strong>
{{--        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
    </div>
@endif


