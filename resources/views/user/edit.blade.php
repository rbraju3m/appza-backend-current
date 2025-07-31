
@extends('layouts.app')

@section('body')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.userUpdate')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">
                                    <a href="{{route('user_add', app()->getLocale())}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus-circle"></i> {{__('messages.Add Button')}}
                                        </button>
                                    </a>

                                    <a href="{{route('user_list', app()->getLocale())}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus-circle"></i> {{__('messages.userList')}}
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')
                        {!! Form::model($user, ['method' => 'PATCH','autocomplete'=>'off', 'files'=> true, 'route'=> ['user_update', app()->getLocale(), $user->id],"class"=>"",'enctype'=>'multipart/form-data', 'id' => 'basic-form']) !!}

                        <div class="row">
                            <div class="form-group row">
                                <div class="col-sm-2">
                                    {!! Form::label(__('messages.userName'), __('messages.userName'), array('class' => 'col-form-label')) !!}
                                    <span class="textRed">*</span>
                                </div>

                                <div class="col-sm-10">
                                    {!! Form::text('name',$user->name,['id'=>'name','class' => 'form-control','Placeholder' => __('messages.userNamePlc')]) !!}
                                    <span class="textRed">{!! $errors->first('name') !!}</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mg-top">
                            <div class="form-group row">
                                <div class="col-sm-2">
                                    {!! Form::label(__('messages.Email'), __('messages.Email'), array('class' => 'col-form-label')) !!}
                                    <span class="textRed">*</span>
                                </div>

                                <div class="col-sm-10">
                                    {!! Form::email('email',$user->email,['class' => 'form-control','Placeholder' => __('messages.emailPlc')]) !!}
                                    <span class="textRed">{!! $errors->first('email') !!}</span>
                                </div>
                            </div>
                        </div>


                        <div class="row mg-top">
                            <div class="form-group row">
                                <div class="col-sm-2">
                                    {!! Form::label(__('messages.userRole'), __('messages.userRole'), array('class' => 'col-form-label')) !!}
                                    <span class="textRed">*</span>
                                </div>

                                <div class="col-sm-10">
                                    {!! Form::select('roles[]',$roles,$userRole,['multiple'=>'multiple','class' => 'form-select js-example-basic-multiple form-control']) !!}
                                    <span class="textRed">{!! $errors->first('roles') !!}</span>
                                </div>
                            </div>
                        </div>


                        <div class="row mg-top">
                            <div class="col-md-2"></div>
                            <div class="col-md-10" >
                                <div class="from-group">
                                    <button type="submit" class="btn btn-primary " id="UserFormSubmit">Submit</button>
                                    <button type="reset" class="btn submit-button">Reset</button>
                                </div>
                            </div>

                        </div>

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('footer.scripts')

@endsection

@push('CustomStyle')
    <style>
        .untitle-list{
            float: left;
            width: 30%;
            padding-left: 0px;
        }
        .textRed{
            color: #ff0000;
        }
        .textjustify{
            text-align: justify;
        }
        .listStyleNone{
            list-style: none;
        }
        .marginleft5{
            margin-left: 5px;
        }
    </style>
@endpush


