@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.createNewMatrix')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <a href="{{route('license_logic_list')}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-list"></i> {{__('messages.list')}}
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
                                {{ html()
                                    ->form('POST', route('license_logic_store'))
                                    ->attribute('enctype', 'multipart/form-data')
                                    ->attribute('files', true)
                                    ->attribute('autocomplete', 'off')
                                    ->open()
                                }}
                                <div class="row">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="name" class="form-label">{{__('messages.name')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('name')
                                                ->class('form-control')
                                                ->placeholder(__('messages.name'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('name') !!}</span>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="Identifier" class="form-label">{{__('messages.Identifier')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('slug')
                                                ->class('form-control')
                                                ->placeholder(__('messages.Identifier'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('slug') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="event" class="form-label">{{__('messages.event')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()
                                                ->select('event', $eventDropdown, '')
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.ChooseEvent'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('event') !!}</span>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="direction" class="form-label">{{__('messages.Direction')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()
                                                ->select('direction', $directionDropdown, '')
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.ChooseDirection'))
                                                ->required()
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="from_days" class="form-label">{{__('messages.FromDays')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()
                                                ->select('from_days', $toDaysDropdown, '')
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.ChooseFromDays'))
                                                ->required()
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="to_days" class="form-label">{{__('messages.ToDays')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()
                                                ->select('to_days', $toDaysDropdown, '')
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.ChooseToDays'))
                                                ->required()
                                            }}
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
        .customButton{
            color: #000;
            background-color: #fff;
            border-color: #6c757d;
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

    <script type="text/javascript">

    </script>

@endsection
