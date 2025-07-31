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

                                    <a href="{{route('theme_list')}}" title="" class="module_button_header">
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
                                    ->form('POST', route('theme_store'))
                                    ->attribute('enctype', 'multipart/form-data')
                                    ->attribute('files', true)
                                    ->attribute('autocomplete', 'off')
                                    ->open()
                                }}
                                <div class="row">
                                    <div class="form-group row">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.name')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->text('name')
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterThemeName'))
                                            }}
                                            <span class="textRed">{!! $errors->first('name') !!}</span>
                                        </div>
                                    </div>

                                    {{--<div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.appbar')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{ html()
                                                ->select('appbar_id', $appbars, '')
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.chooseAppbar'))
                                            }}
                                            <span class="textRed">{!! $errors->first('appbar_id') !!}</span>
                                        </div>
                                    </div>--}}

                                    {{--<div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.navbar')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{ html()
                                                ->select('navbar_id', $navbars, '')
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.chooseNavbar'))
                                            }}
                                            <span class="textRed">{!! $errors->first('navbar_id') !!}</span>
                                        </div>
                                    </div>--}}

                                    {{--<div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.drawer')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{ html()
                                                ->select('drawer_id', $drawers, '')
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.chooseNavbar'))
                                            }}
                                            <span class="textRed">{!! $errors->first('drawer_id') !!}</span>
                                        </div>
                                    </div>--}}

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.Plugin')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{ html()
                                                ->select('plugin_slug', $withHomePage, '')
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.choosePlugin'))
                                            }}
                                            <span class="textRed">{!! $errors->first('plugin_slug') !!}</span>
                                        </div>
                                    </div>

                                    @if (!empty($withoutHomePage))
                                        <div class="form-group row mg-top">
                                            <div class="col-sm-2">
                                            </div>

                                            <div class="col-sm-10" style="background-color: #FFF2E8; padding: 10px">
                                                <h3 class="text-center text-danger mb-3 font-weight-bold">
                                                    <i class="fas fa-exclamation-circle"></i> Missing Home Page
                                                </h3>
                                                <div class="alert alert-warning text-center" role="alert">
                                                    The following plugins are missing a home page:
                                                </div>
                                                <ul class="list-group list-group-flush">
                                                    @foreach ($withoutHomePage as $key => $value)
{{--                                                        <li>Plugin Name: {{ $value }}</li>--}}
                                                        <li class="list-group-item d-flex justify-content-between align-items-center text-wrap">
                                                            <span class="font-weight-bold">{{ $value }}</span>
                                                            <span class="badge bg-danger text-white">Missing</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                {{--<ul>
                                                    @foreach ($withoutHomePage as $key => $value)
                                                        <li>Plugin Name: {{ $value }}</li>
                                                    @endforeach
                                                </ul>--}}
                                            </div>
                                        </div>
                                    @endif


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.dashboardPage')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->text('dashboard_page')
                                                ->class('form-control')
                                                ->placeholder(__('messages.dashboardPage'))
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.loginPage')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->text('login_page')
                                                ->class('form-control')
                                                ->placeholder(__('messages.loginPage'))
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.loginModel')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->text('login_modal')
                                                ->class('form-control')
                                                ->placeholder(__('messages.loginModel'))
                                            }}
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="layout_type_id" class="form-label">{{__('messages.BackgroundColor')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()
                                                ->input('color', 'background_color')
                                                ->class('form-control inline_update')
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="transparent" class="form-label">{{__('messages.fontFamily')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            @php
                                                $fontFamilyDropdownValue = [
                                                              'Lato'=>'Lato',
                                                              'Poppins'=>'Poppins',
                                                              'Roboto'=>'Roboto',
                                                              'Open Sans'=>'Open Sans',
                                                              'Inter'=>'Inter'
                                                        ];
                                            @endphp
                                            {{ html()
                                                ->select('font_family', $fontFamilyDropdownValue, '')
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.chooseFontFamily'))
                                            }}
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="layout_type_id" class="form-label">{{__('messages.TextColor')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()
                                                ->input('color', 'text_color')
                                                ->class('form-control inline_update')
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="transparent" class="form-label">{{__('messages.fontSize')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('font_size')
                                                ->class('form-control')
                                                ->placeholder(__('messages.EnterFontSize'))
                                            }}
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="transparent" class="form-label">{{__('messages.Transparent')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            @php
                                                $transparentDropdown['0x00FFFFFF'] = '0x00FFFFFF';
                                            @endphp
                                            {{ html()
                                                ->select('transparent', $transparentDropdown, '')
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.chooseTransparent'))
                                            }}
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="formFile" class="form-label">{{__('messages.image')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            <input class="form-control" name="image" type="file" id="imgInp" accept="image/*">
                                            <img id="blah" src="#" width="25%" />
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
        imgInp.onchange = evt => {
            const [file] = imgInp.files
            if (file) {
                blah.src = URL.createObjectURL(file)
            }
        }
    </script>

@endsection
