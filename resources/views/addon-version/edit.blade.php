@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>Plugin update</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <div class="btn-group me-2">
                                        <a href="{{route('plugin_add')}}" title="" class="module_button_header">
                                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-plus-circle"></i> {{__('messages.createPlugin')}}
                                            </button>
                                        </a>
                                    </div>

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
                                {{ html()->modelForm($data, 'PATCH', route('plugin_update', $data->id))
                                    ->attribute('enctype', 'multipart/form-data')
                                    ->attribute('files', true)
                                    ->attribute('autocomplete', 'off')
                                    ->open() }}

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
                                            <input class="form-control" name="image" type="file" id="imgInp" accept="image/*">
                                            @if(isset($data->image))
                                                <img src="{{ config('app.image_public_path').$data->image }}" alt="your image" width="25%" />
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

    <script type="text/javascript">
        imgInp.onchange = evt => {
            const [file] = imgInp.files
            if (file) {
                blah.src = URL.createObjectURL(file)
            }
        }
        $(document).delegate('.global-config-component','change',function(){
            let value = $(this).val();
            let globalConfigId = $('#global_config_id').val();
            let isChecked = 0
            if($(this).is(':checked')){isChecked = 1}
            let route = $('#global_config_assign_component').attr('data-href');
            $.ajax({
                url: route,
                method: "get",
                dataType: "json",
                data: {isChecked: isChecked,componentId:value,globalConfigId:globalConfigId},
                beforeSend: function( xhr ) {

                }
            }).done(function( response ) {
                console.log(response)
                if (response.status == 'deleted'){
                    $('#component_position_id_'+value).val(null)
                }
            }).fail(function( jqXHR, textStatus ) {

            });
            return false;
        });
        $(document).delegate('.component_position','change',function(){
            let value = $(this).val();
            let globalConfigId = $('#global_config_id').val();
            let componentId = $(this).attr('component_id');
            let route = $('#global_config_assign_component_position').attr('data-href');
            $.ajax({
                url: route,
                method: "get",
                dataType: "json",
                data: {componentId: componentId,value:value,globalConfigId:globalConfigId},
                beforeSend: function( xhr ) {

                }
            }).done(function( response ) {
                if (response.status == 'not-found'){
                    alert('Please assign component then added position.')
                    $('#component_position_id_'+componentId).val(null)
                }
            }).fail(function( jqXHR, textStatus ) {

            });
            return false;
        });

        $(document).delegate('.plugin_slug', 'change', function (event) {
            event.preventDefault(); // Prevent any default behavior
            let value = $(this).val();
            let id = $(this).attr('id');
            let route = $('.plugin_slug_update').attr('data-href');

            $.ajax({
                url: route,
                method: "post",
                dataType: "json",
                data: { id: id, value: value },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Attach CSRF token
                },
                beforeSend: function (xhr) {
                    // Optional: Add any loading indicator logic here
                }
            }).done(function (response) {
                if (response.status !== 'ok') {
                    // Reload the page when the response is successful
                    alert('Plugin slug not updated.')
                }
                location.reload();
                // console.log(response);
            }).fail(function (jqXHR, textStatus) {
                console.error('Request failed:', textStatus);
            });

            return false; // Prevent any additional default behavior
        });
    </script>

@endsection
