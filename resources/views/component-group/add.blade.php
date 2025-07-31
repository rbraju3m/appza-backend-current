@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.createNew')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <a href="{{route('component_group_list')}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-list"></i> {{__('messages.componentGroupList')}}
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
                                {{ html()->form('POST', route('component_group_create'))
                                    ->attribute('enctype', 'multipart/form-data')
                                    ->attribute('files', true)
                                    ->attribute('autocomplete', 'off')
                                    ->open() }}

                                <div class="row">
                                    <div class="form-group row">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.ComponentGroupName')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->text('name')
                                                ->class('form-control')
                                                ->placeholder(__('messages.ComponentGroupName'))
                                            }}
                                            <span class="textRed">{!! $errors->first('name') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-4">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.IconName')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->text('icon')
                                                ->class('form-control')
                                                ->placeholder(__('messages.IconName'))
                                            }}
                                            <span class="textRed">{!! $errors->first('icon') !!}</span>
                                        </div>
                                    </div>

                                    <div class="row mg-top">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-10">
                                            <div class="from-group">
                                                <button type="submit" class="btn btn-primary " id="UserFormSubmit">
                                                    Submit
                                                </button>
                                                <button type="reset" class="btn submit-button">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{ html()->form()->close() }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('CustomStyle')
    <style>
        .customButton {
            color: #000;
            background-color: #fff;
            border-color: #6c757d;
        }

        .textRed {
            color: #ff0000;
        }

        .height29 {
            height: 29px;
        }

        .textCenter {
            text-align: center;
        }

        .displayNone {
            display: none;
        }

    </style>
@endpush

@section('footer.scripts')

@endsection
