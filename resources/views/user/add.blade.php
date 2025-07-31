@extends('layouts.app')

@section('body')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.newUser')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

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

                        {!! Form::open(['route' => ['user_store', app()->getLocale()],'enctype'=>'multipart/form-data', 'autocomplete'=>'off', 'files'=> true, 'id'=>'basic-form', 'class' => '']) !!}

                        <div class="row">
                            <div class="form-group row">
                                <div class="col-sm-2">
                                    {!! Form::label(__('messages.userName'), __('messages.userName'), array('class' => 'col-form-label')) !!}
                                    <span class="textRed">*</span>
                                </div>

                                <div class="col-sm-10">
                                    {!! Form::text('name','',['id'=>'name','class' => 'form-control','Placeholder' => __('messages.userNamePlc')]) !!}
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
                                    {!! Form::email('email','',['class' => 'form-control','Placeholder' => __('messages.emailPlc')]) !!}
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
                                    {!! Form::select('roles[]',$roles,[],['id'=>'roles','multiple'=>'multiple','class' => 'form-select js-example-basic-multiple form-control']) !!}
                                    <span class="textRed">{!! $errors->first('roles') !!}</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mg-top">
                            <div class="form-group row">
                                <div class="col-sm-2">
                                    {!! Form::label(__('messages.password'), __('messages.password'), array('class' => 'col-form-label')) !!}
                                    <span class="textRed">*</span>
                                </div>

                                <div class="col-sm-10">
                                    {!! Form::password('password',['class' => 'form-control','Placeholder' => __('messages.passwordPlc')]) !!}
                                    <span class="textRed">{!! $errors->first('password') !!}</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mg-top">
                            <div class="form-group row">
                                <div class="col-sm-2">
                                    {!! Form::label(__('messages.passwordConfirm'), __('messages.passwordConfirm'), array('class' => 'col-form-label')) !!}
                                    <span class="textRed">*</span>
                                </div>

                                <div class="col-sm-10">
                                    {!! Form::password('password_confirmation',['class' => 'form-control','Placeholder' => __('messages.passwordConfirmPlc')]) !!}
                                    <span class="textRed">{!! $errors->first('password_confirmation') !!}</span>
                                </div>
                            </div>
                        </div>


                        {{--<div class="col-md-6">
                            {!! Form::label('User Roles', 'User Roles', array('class' => 'form-label')) !!}
                            <span style="color: red">*</span>
                            <div class="input-group mb-3">
                                @if(isset($data))
                                    --}}{{--                        {!! Form::select('roles[]', $roles,$userRole, array('class' => 'form-control','multiple')) !!}--}}{{--
                                    {!! Form::select('roles[]',$roles,$userRole,['id'=>'roles','multiple'=>'multiple','class' => 'form-select js-example-basic-multiple form-control','aria-label' =>'name','aria-describedby'=>'basic-addon2']) !!}
                                @else
                                    {!! Form::select('roles[]',$roles,[],['id'=>'roles','multiple'=>'multiple','class' => 'form-select js-example-basic-multiple form-control','aria-label' =>'name','aria-describedby'=>'basic-addon2','required'=>true]) !!}
                                @endif

                                <span style="color: #ff0000">{!! $errors->first('roles') !!}</span>
                            </div>
                        </div>--}}


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
