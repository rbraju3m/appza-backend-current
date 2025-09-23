@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.createNewMessage')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <a href="{{route('license_message_list')}}" title="" class="module_button_header">
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
                                {{ html()->modelForm($licenseMessage, 'PATCH', route('license_message_update', $licenseMessage->id))
                                    ->attribute('enctype', 'multipart/form-data')
                                    ->attribute('files', true)
                                    ->attribute('autocomplete', 'off')
                                    ->open() }}
                                <div class="row">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="product_id" class="form-label">{{__('messages.Product')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()
                                                ->select('product_id', $products, $licenseMessage->product_id)
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.ChooseProduct'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('product_id') !!}</span>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="license_logic_id" class="form-label">{{__('messages.Matrix')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()
                                                ->select('license_logic_id', $matrixs, $licenseMessage->license_logic_id)
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.ChooseMatrix'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('license_logic_id') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="license_type" class="form-label">{{__('messages.LicenseType')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()
                                                ->select('license_type', $licenseType , $licenseMessage->license_type)
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.ChooseLicenseType'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('license_type') !!}</span>
                                        </div>
                                    </div>

                                    <hr style="margin-top: 20px">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="message_user" class="form-label">{{__('messages.UserMessage')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->textarea('message_user')
                                                ->class('form-control')
                                                ->placeholder(__('messages.UserMessage'))
                                                ->value(old('message_user', $licenseMessage->user_message))
                                                ->attribute('rows',2)
                                            }}
                                            <span class="textRed">{!! $errors->first('message_user') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="message_admin" class="form-label">{{__('messages.AdminMessage')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->textarea('message_admin')
                                                ->class('form-control')
                                                ->value(old('message_admin', $licenseMessage->admin_message))
                                                ->placeholder(__('messages.AdminMessage'))
                                                ->attribute('rows',2)
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="message_special" class="form-label">{{__('messages.SpecialMessage')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->textarea('message_special')
                                                ->class('form-control')
                                                ->value($licenseMessage->special_message)
                                                ->value(old('message_special', $licenseMessage->special_message))
                                                ->placeholder(__('messages.SpecialMessage'))
                                                ->attribute('rows',2)
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
