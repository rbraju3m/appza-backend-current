@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.themeUpdate')}}</h6>
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
                                    ->form('PATCH', route('theme_update',$theme->id))
                                    ->attribute('enctype', 'multipart/form-data')
                                    ->attribute('files', true)
                                    ->attribute('autocomplete', 'off')
                                    ->open()
                                }}

                                <div class="row">


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.Plugin')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            <input type="hidden" name="plugin_slug" value="{{$theme->plugin_slug}}">
                                            {{ html()
                                                ->select('plugin_slug', $pluginDropdown, $theme->plugin_slug)
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.choosePlugin'))
                                                ->attribute('disabled',true)
                                            }}
                                            <span class="textRed">{!! $errors->first('plugin_slug') !!}</span>
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.DefaultPage')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{ html()
                                                ->select('default_page', $pages, $theme->default_page)
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.chooseDefaultPage'))
                                            }}
                                            <span class="textRed">{!! $errors->first('default_page') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.name')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->text('name',$theme->name)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterThemeName'))
                                            }}
                                            <span class="textRed">{!! $errors->first('name') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.appbar')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{ html()
                                                ->select('appbar_id', $appbars, $theme->appbar_id)
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.chooseAppbar'))
                                            }}
                                            <span class="textRed">{!! $errors->first('appbar_id') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.navbar')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{ html()
                                                ->select('navbar_id', $navbars, $theme->navbar_id)
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.chooseNavbar'))
                                            }}
                                            <span class="textRed">{!! $errors->first('navbar_id') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.drawer')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{ html()
                                                ->select('drawer_id', $drawers, $theme->drawer_id)
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.chooseDrawer'))
                                            }}
                                            <span class="textRed">{!! $errors->first('drawer_id') !!}</span>
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.dashboardPage')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->text('dashboard_page',$theme->dashboard_page)
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
                                                ->text('login_page',$theme->login_page)
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
                                                ->text('login_modal',$theme->login_modal)
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
                                                ->input('color', 'background_color',$theme->background_color)
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
                                                ->select('font_family', $fontFamilyDropdownValue, $theme->font_family)
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
                                                ->input('color', 'text_color',$theme->text_color)
                                                ->class('form-control inline_update')
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="transparent" class="form-label">{{__('messages.fontSize')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('font_size',$theme->font_size)
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
                                                ->select('transparent', $transparentDropdown, $theme->transparent)
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
                                            @if(isset($theme->image))
                                                <img src="{{ config('app.image_public_path').$theme->image }}" alt="your image" width="25%" />
                                            @endif
                                            <img id="blah" src="#" width="25%" />
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="formFile" class="form-label">{{__('messages.gallery')}}</label>
                                        </div>

                                        <div class="col-sm-10">

                                                    <table class="table table-striped table-bordered table-hover">
                                                        <thead>
                                                        {{--<tr>
                                                            <td>Image</td>
                                                            <td>Caption</td>
                                                            <td>Action</td>
                                                        </tr>--}}
                                                        <tr>
                                                            <td>
                                                                <input type="file" id="file" name="file[]" class="form-control" multiple />
{{--                                                                <span style="font-size: 10px;">( Greater than or equal to width 1280px & height 850px. )</span>--}}
                                                            </td>
                                                            <td>
                                                                {{html()
                                                                    ->text('caption')
                                                                    ->class('form-control')
                                                                    ->id('caption')
                                                                    ->placeholder('Caption')
                                                                }}
                                                            </td>
                                                            <td>
                                                                <button id="photo_gallery_image_add" type="button" data-action="" data-entity-id="{{$theme->id}}" class="btn btn-primary btn-sm">Add</button>
                                                            </td>
                                                        </tr>
                                                        </thead>
                                                        <tbody class="photo_gallery_images">
                                                        @include('theme/image-gallery')
                                                        </tbody>
                                                    </table>

                                        </div>
                                    </div>




                                    <div class="row mg-top">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-10" >
                                            <div class="from-group">
                                                <button type="submit" class="btn btn-primary " id="UserFormSubmit">Next</button>
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


    <div class="modal fade" id="allModalShow" tabindex="-1" aria-labelledby="allModalShowModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appfiypleModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" id="modelForm">

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn customButton" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>
                    <button type="button" class="btn btn-primary modelDataInsert">Save changes</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('CustomStyle')

    <!-- CSS -->

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

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#photo_gallery_image_add').on('click',function(e) {
            var fd = new FormData();
            var caption=$('#caption').val();
            var id=$(this).attr('data-entity-id');

            let TotalFiles = $('#file')[0].files.length;
            let files = $('#file')[0];
            for (let i = 0; i < TotalFiles; i++) {
                fd.append('files' + i, files.files[i]);
            }
            fd.append('TotalFiles', TotalFiles);

            if(TotalFiles > 0 ){
                fd.append('id',id);
                fd.append('caption',caption);
                fd.append("_token", '{{csrf_token()}}');
                $.ajax({
                    url: '{!! route('store_photo_gallery_for_theme') !!}',
                    headers:{'X-CSRF-Token':$('meta[name=csrf_token]').attr('content')},
                    async:true,
                    type:"post",
                    contentType:false,
                    data:fd,
                    processData:false,
                    success: function(response){
                        jQuery(".photo_gallery_images").html(response.html);
                        $('#caption').val('');
                        $('#file').val('');
                    },
                });
            }else{
                alert("Please select a file.");
            }
        });

        $(document).on('click','.record_delete',function(e) {
            var element= $(this);
            var entityId= $(this).attr('data-id');
            if(entityId==''){
                alert('This record are not available');
                return false;
            }
            if(confirm('Are you sure delete?')){
                jQuery.ajax({
                    type: 'GET',
                    dataType: 'json',
                    url: '/appza/theme/gallery/image/' + entityId,
                    data: {},
                    success: function (data) {

                        if (data.status == 200) {
                            jQuery('.alert').addClass('alert-success').show().html(data.message);
                        }
                        $(element).closest('tr').remove();
                    }

                });
            }
        });
    </script>

@endsection
