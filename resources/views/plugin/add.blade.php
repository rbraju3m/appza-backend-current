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

                                    <a href="{{route('plugin_list')}}" title="" class="module_button_header">
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
                                    ->form('POST', route('plugin_store'))
                                    ->attribute('enctype', 'multipart/form-data')
                                    ->attribute('files', true)
                                    ->attribute('autocomplete', 'off')
                                    ->open()
                                }}
                                <div class="row">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="layout_type_id" class="form-label">{{__('messages.name')}}</label>
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
                                            <label for="transparent" class="form-label">{{__('messages.slug')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('slug')
                                                ->class('form-control')
                                                ->placeholder(__('messages.slug'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('slug') !!}</span>
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="prefix" class="form-label">{{__('messages.prefix')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('prefix')
                                                ->class('form-control')
                                                ->placeholder(__('messages.prefix'))
                                                ->required()

                                            }}
                                            <span class="textRed">{!! $errors->first('prefix') !!}</span>
                                        </div>
                                    </div>

                                    <hr style="margin-top: 20px">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="title" class="form-label">{{__('messages.title')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('title')
                                                ->class('form-control')
                                                ->placeholder(__('messages.title'))
                                            }}
                                            <span class="textRed">{!! $errors->first('title') !!}</span>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="description" class="form-label">{{__('messages.description')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('description')
                                                ->class('form-control')
                                                ->placeholder(__('messages.description'))
                                            }}
                                            <span class="textRed">{!! $errors->first('description') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="others" class="form-label">{{__('messages.others')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('others')
                                                ->class('form-control')
                                                ->placeholder(__('messages.others'))
                                            }}
                                            <span class="textRed">{!! $errors->first('others') !!}</span>
                                        </div>
                                    </div>
                                    <hr style="margin-top: 20px">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="formFile" class="form-label">{{__('messages.image')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            <input class="form-control" name="image" type="file" id="imgInp" accept="image/*" required>
                                            @if(isset($theme->image))
                                                <img src="{{ config('app.image_public_path').$theme->image }}" alt="your image" width="25%" />
                                            @endif
                                            <span class="textRed">{!! $errors->first('image') !!}</span>
                                            <img id="blah" src="#" width="25%" />
                                        </div>
                                    </div>

                                    <hr style="margin-top: 20px">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="active" class="form-label">{{__('messages.active')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()->checkbox('status',true)}}
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="disable" class="form-label">{{__('messages.IsDisable')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()->checkbox('is_disable')}}
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
        imgInp.onchange = evt => {
            const [file] = imgInp.files
            if (file) {
                blah.src = URL.createObjectURL(file)
            }
        }
    </script>

@endsection
