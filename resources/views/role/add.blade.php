@extends('layouts.app')

@section('body')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.roleList')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <a href="{{route('role_list', app()->getLocale())}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus-circle"></i> {{__('messages.roleList')}}
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')

                        {!! Form::open(['route' => ['role_store', app()->getLocale()],'enctype'=>'multipart/form-data', 'autocomplete'=>'off', 'files'=> true, 'id'=>'basic-form', 'class' => '']) !!}

                        <div class="row">
                            <div class="form-group row">
                                <div class="col-sm-2">
                                    {!! Form::label(__('messages.RoleName'), __('messages.RoleName'), array('class' => 'col-form-label')) !!}
                                    <span class="textRed">*</span>
                                </div>

                                <div class="col-sm-10">
                                    {!! Form::text('name','',['id'=>'name','class' => 'form-control','Placeholder' => __('messages.PlcRoleName')]) !!}
                                    <span class="textRed">{!! $errors->first('name') !!}</span>
                                </div>
                            </div>

                            <div class="form-group row mg-top">
                                <div class="col-sm-2">
                                    {!! Form::label(__('messages.Permission'),__('messages.Permission'), array('class' => 'col-form-label')) !!}
                                    <span class="textRed">*</span>
                                </div>

                                <div class="col-sm-10 textjustify">
                                    @foreach($permission as $value)
                                        <ul class="untitle-list">
                                            <li class="listStyleNone">
                                                {{ Form::checkbox('permission[]', $value->id, false, array('class' => 'name','id'=>$value->name)) }}
                                                <label class="marginleft5" for={{$value->name}}>{{$value->name}}</label>
                                            </li>
                                        </ul>
                                    @endforeach
                                    <span class="textRed">{!! $errors->first('permission') !!}</span>
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
