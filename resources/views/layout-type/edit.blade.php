@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.employeeEdit')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <a href="{{route('employee_add', app()->getLocale())}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus-circle"></i> {{__('messages.Add Button')}}
                                        </button>
                                    </a>

                                    <a href="{{route('employee_list', app()->getLocale())}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-list"></i> {{__('messages.list Button')}}
                                        </button>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')
                        <div class="row">
                            <div class="col-md-12">
                                {!! Form::model($employee, ['method' => 'PATCH','autocomplete'=>'off', 'files'=> true, 'route'=> ['employee_update',app()->getLocale(), $employee->id],'enctype'=>'multipart/form-data']) !!}

                                <div class="row">

                                    <div class="form-group row">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.name')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {!! Form::text('name', null, array('class' => 'form-control ','placeholder'=>__('messages.namePlc'))) !!}
                                            <span class="textRed">{!! $errors->first('name') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.employeeId')}}</label>
{{--                                            <span class="textRed">*</span>--}}
                                        </div>

                                        <div class="col-sm-10">
                                            {!! Form::text('employee_id', null, array('class' => 'form-control ','placeholder'=>__('messages.employeeIdpLC'))) !!}
                                            <span class="textRed">{!! $errors->first('employee_id') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.mobile')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {!! Form::text('mobile', null, array('class' => 'form-control ','placeholder'=>__('messages.mobilePlc'))) !!}
                                            <span class="textRed">{!! $errors->first('mobile') !!}</span>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.country')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {!! Form::select('country_id',$countries,$employee->country_id,['id'=>'country_id','class' => 'form-control form-select js-example-basic-single','placeholder'=>__('messages.selectCountry')]) !!}
                                            <span class="textRed">{!! $errors->first('country_id') !!}</span>
                                        </div>
                                    </div>

                                    {{--<div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.email')}}</label>
--}}{{--                                            <span class="textRed">*</span>--}}{{--
                                        </div>

                                        <div class="col-sm-10">
                                            {!! Form::text('email', null, array('class' => 'form-control ','placeholder'=>__('messages.emailPlc'))) !!}
                                            <span class="textRed">{!! $errors->first('email') !!}</span>
                                        </div>
                                    </div>--}}


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.presentAdd')}}</label>
                                            {{--<span class="textRed">*</span>--}}
                                        </div>

                                        <div class="col-sm-10">
                                            {!! Form::textarea('present_address', null, array('class' => 'form-control ','placeholder'=>__('messages.presentAddPlc'),'rows'=>3)) !!}
                                            {{--<span class="textRed">{!! $errors->first('present_address') !!}</span>--}}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.permanentAdd')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            {!! Form::textarea('permanent_address', null, array('class' => 'form-control ','placeholder'=>__('messages.permanentAddPlc'),'rows'=>3)) !!}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.education')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            {!! Form::textarea('education', null, array('class' => 'form-control ','placeholder'=>__('messages.educationPlc'),'rows'=>3)) !!}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.designation')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-3">
                                            <a class="displayNone" id="createRouteforauthor"
                                               data-href="{{ route('create_desig_dept_pbs',app()->getLocale()) }}">
                                            </a>
                                            {!! Form::select('designation_id',$designation,$employee->designation_id,['id'=>'designation_id','class' => 'form-control form-select js-example-basic-single','placeholder'=>__('messages.selectDesignation')]) !!}
                                            <span class="textRed">{!! $errors->first('designation_id') !!}</span>
                                        </div>

                                        <div class="col-sm-1">
                                            <button type="button" class="btn btn-sm btn-primary height29 modelShow" modelName="designation" >
                                                <i class="fas fa-plus-circle"></i>
                                            </button>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.location')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-3">
                                            {!! Form::select('location_id',$location,$employee->location_id,['id'=>'location_id','class' => 'form-control form-select js-example-basic-single','placeholder'=>__('messages.selectLocation')]) !!}
                                            <span class="textRed">{!! $errors->first('location_id') !!}</span>
                                        </div>

                                        <div class="col-sm-1">
                                            <button type="button" class="btn btn-sm btn-primary height29 modelShow" modelName="location">
                                                <i class="fas fa-plus-circle"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.department')}}</label>
{{--                                            <span class="textRed">*</span>--}}
                                        </div>

                                        <div class="col-sm-3">
                                            {!! Form::select('department_id',$department,$employee->department_id,['id'=>'department_id','class' => 'form-control form-select js-example-basic-single','placeholder'=>__('messages.selectDepartment')]) !!}
                                            <span class="textRed">{!! $errors->first('department_id') !!}</span>
                                        </div>

                                        <div class="col-sm-1">
                                            <button type="button" class="btn btn-sm btn-primary height29 modelShow" modelName="department">
                                                <i class="fas fa-plus-circle"></i>
                                            </button>
                                        </div>

                                        <div class="col-sm-2">
                                            {!! Form::label(__('messages.joinDate'),__('messages.joinDate'), array('class' => 'form-label','for'=>'formFile')) !!}
                                        </div>

                                        <div class="col-sm-4">
                                            {{ Form::date('join_date',$employee->join_date,['id'=>'join_date','class' => 'form-control height28']) }}
                                            <span class="textRed">{!! $errors->first('join_date') !!}</span>
                                        </div>
                                    </div>




                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            {!! Form::label(__('messages.image'), __('messages.image'), array('class' => 'form-label','for'=>'formFile')) !!}
                                        </div>

                                        <div class="col-sm-10">
                                            <input class="form-control" name="image" type="file" id="image" accept="image/*">
                                            @if(isset($employee->image))
                                                <span class="imageText">{{$employee->image}}</span>
                                            @endif
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            {!! Form::label(__('messages.signature'), __('messages.image'), array('class' => 'form-label','for'=>'formFile')) !!}
                                        </div>

                                        <div class="col-sm-10">
                                            <input class="form-control" name="signature" type="file" id="image" accept="image/*">
                                            @if(isset($employee->signature))
                                                <span class="imageText">{{$employee->signature}}</span>
                                            @endif
                                        </div>
                                    </div>


                                    {{--<div class="row mg-top">
                                        <div class="form-group row">
                                            <div class="col-sm-2">
                                                {!! Form::label(__('messages.userRole'), __('messages.userRole'), array('class' => 'col-form-label')) !!}
                                                <span class="textRed">*</span>
                                            </div>

                                            <div class="col-sm-10">
                                                {!! Form::select('roles[]',$roles,$userRole,['id'=>'roles','multiple'=>'multiple','class' => 'form-select js-example-basic-multiple form-control']) !!}
                                                <span class="textRed">{!! $errors->first('roles') !!}</span>
                                            </div>
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

                                </div>

                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="allModalShow" tabindex="-1" aria-labelledby="allModalShowModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" id="modelForm">

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn customButton" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>                    <button type="button" class="btn btn-primary modelDataInsert">Save changes</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('CustomStyle')
    <style>
        .customButton{
            color: #000;
            background-color: #fff;
            border-color: #6c757d;
        }
        .imageText{
            background: blue;
            color: #fff;
            padding: 5px 5px;
            display: block;
            margin-top: 2px;
        }
        .textRed{
            color: #ff0000;
        }

        .height29{
            height: 29px;
        }
        .textCenter{
            text-align: center;
        }
        .displayNone{
            display: none;
        }

    </style>
@endpush

@section('footer.scripts')

    <script src="{{Module::asset('quran:js/sura-datatable.js')}}"></script>
    <script type="text/javascript">
        $(function () {
            $(document).on("change", ".isChecked", function (e) {
                e.preventDefault();
            });
            /* DESIGNATION ADD */
            $(document).delegate('.modelShow','click',function(){
                var modelName = $(this).attr('modelname');
                if(modelName == 'designation'){
                    $('.modal-title').text('New Designation');
                    $('#modelForm').html('<input type="text" name="name" fieldName="designation" class="form-control fieldValue" placeholder="{{__('messages.modelEnterDesig')}}" id="newDesignation">');
                }
                if (modelName == 'location'){
                    $('.modal-title').text('New Location');
                    $('#modelForm').html('<input type="text" name="name" fieldName="location" class="form-control fieldValue" placeholder="{{__('messages.modelLocation')}}" id="newLocation">');
                }
                if (modelName == 'department'){
                    $('.modal-title').text('New Department');
                    $('#modelForm').html('<input type="text" name="name" fieldName="department" class="form-control fieldValue" placeholder="{{__('messages.modelDepartment')}}" id="newDepartment">');
                }
                $("#allModalShow").modal('show');

            });

            /*Data insert from model designation/location/department*/
            $(document).delegate('.modelDataInsert','click',function(){
                var value = $('.fieldValue').val();
                var type = $('.fieldValue').attr('fieldname');
                var route = $('#createRouteforauthor').attr('data-href');
                var validation = true;

                if (type == 'designation'){
                    if (value == ''){
                        Swal.fire(
                            'Enter designation',
                            '',
                        )
                        return false
                        validation = false;
                    }
                }

                if (type == 'location'){
                    if (value == ''){
                        Swal.fire(
                            'Enter location',
                            '',
                        )
                        return false
                        validation = false;
                    }
                }

                if (type == 'department'){
                    if (value == ''){
                        Swal.fire(
                            'Enter department',
                            '',
                        )
                        return false
                        validation = false;
                    }
                }
                if (validation){
                    $.ajax({
                        url: route,
                        method: "get",
                        dataType: "json",
                        data: {value: value,type:type},
                        beforeSend: function( xhr ) {

                        }
                    }).done(function( response ) {
                        if (!response.exists) {
                            var allItems = response.dropdown;
                            var dropdownOption = '';
                            if (response.type == '1') {
                                var dropdownOption = '<option value="">{{__('messages.selectDesignation')}}</option>';
                            }

                            if (response.type == '2') {
                                var dropdownOption = '<option value="">{{__('messages.selectDepartment')}}</option>';
                            }

                            if (response.type == '3') {
                                var dropdownOption = '<option value="">{{__('messages.selectLocation')}}</option>';
                            }

                            jQuery.each(allItems, function (i, item) {
                                dropdownOption += '<option value="' + i + '">' + item + '</option>';
                            });
                            if (response.type == '1') {
                                jQuery('#designation_id').html(dropdownOption);
                            }
                            if (response.type == '2') {
                                jQuery('#department_id').html(dropdownOption);
                            }
                            if (response.type == '3') {
                                jQuery('#location_id').html(dropdownOption);
                            }

                            $("#allModalShow").modal('hide');
                        }else{
                            Swal.fire(
                                response.exists,
                                '',
                            )
                        }
                    }).fail(function( jqXHR, textStatus ) {

                    });
                    return false;
                }
            });
        });
    </script>

@endsection
