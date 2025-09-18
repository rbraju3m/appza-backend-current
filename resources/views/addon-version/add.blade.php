@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.createAddon')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <a href="{{route('addon_version_list')}}" title="" class="module_button_header">
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
                                    ->form('POST', route('addon_version_store'))
                                    ->attribute('enctype', 'multipart/form-data')
                                    ->attribute('files', true)
                                    ->attribute('autocomplete', 'off')
                                    ->open()
                                }}
                                <div class="row">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="layout_type_id" class="form-label">{{__('messages.ProductName')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{ html()
                                                ->select('product_id', $products, old('product_id'))
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.ChooseProduct'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('product_id') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="addon_name" class="form-label">{{__('messages.AddonName')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('addon_name')
                                                ->class('form-control')
                                                ->placeholder(__('messages.AddonName'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('addon_name') !!}</span>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="addon_slug" class="form-label">{{__('messages.AddonSlug')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('addon_slug')
                                                ->class('form-control')
                                                ->placeholder(__('messages.AddonSlug'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('addon_slug') !!}</span>
                                        </div>
                                    </div>

                                    <hr style="margin-top: 20px">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="version" class="form-label">{{__('messages.version')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('version')
                                                ->class('form-control')
                                                ->placeholder(__('messages.version'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('version') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="addon_json_info" class="form-label">{{__('messages.VersionJson')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->textarea('addon_json_info')
                                                ->class('form-control')
                                                ->placeholder(__('messages.VersionJson'))
                                                ->attribute('rows',5)
                                            }}
                                            <span class="textRed">{!! $errors->first('addon_json_info') !!}</span>
                                        </div>
                                    </div>

                                    <hr style="margin-top: 20px">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="formFile" class="form-label">{{__('messages.AddonFile')}}</label>
                                            <span class="textRed">*</span>
                                        </div>
                                        <div class="col-sm-10">
                                            <input class="form-control" name="addon_file" type="file" id="zipInp" accept=".zip" required>
                                            <span class="textRed">{!! $errors->first('addon_file') !!}</span>
                                            <p id="fileName" class="mt-2 text-info">Ex: appza-plugin-1.0.0.zip</p>
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
        .textRed{
            color: #ff0000;
        }

    </style>
@endpush

@section('footer.scripts')
    <script type="text/javascript">
        const zipInp = document.getElementById("zipInp");
        const fileName = document.getElementById("fileName");

        zipInp.onchange = () => {
            if (zipInp.files.length > 0) {
                fileName.textContent = "Selected file: " + zipInp.files[0].name;
            } else {
                fileName.textContent = "";
            }
        };
    </script>

@endsection
