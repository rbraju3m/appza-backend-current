@if($message = Session::get('message'))
    <div class="container-fluid mt-3">
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
            <strong>{{ $message }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

@if($validate = Session::get('validate'))
    <div class="container-fluid mt-3">
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>{{ $validate }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

@if($delete = Session::get('delete'))
    <div class="container-fluid mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>{{ $delete }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif


