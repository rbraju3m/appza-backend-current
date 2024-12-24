@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.GlobalConfigUpdate')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <div class="btn-group me-2">
                                        <a href="{{route('global_config_add','appbar')}}" title="" class="module_button_header">
                                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-plus-circle"></i> {{__('messages.createAppbar')}}
                                            </button>
                                        </a>
                                    </div>
                                    <div class="btn-group me-2">
                                        <a href="{{route('global_config_add', 'navbar')}}" title="" class="module_button_header">
                                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-plus-circle"></i> {{__('messages.createNavbar')}}
                                            </button>
                                        </a>
                                    </div>
                                    <div class="btn-group me-2">
                                        <a href="{{route('global_config_add', 'drawer')}}" title="" class="module_button_header">
                                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-plus-circle"></i> {{__('messages.createDrawer')}}
                                            </button>
                                        </a>
                                    </div>

                                    <a href="{{route('global_config_list')}}" title="" class="module_button_header">
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
                                {{ html()->modelForm($data, 'PATCH', route('global_config_update', $data->id))
                                    ->attribute('enctype', 'multipart/form-data')
                                    ->attribute('files', true)
                                    ->attribute('autocomplete', 'off')
                                    ->open() }}

                                <div class="row">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.mode')}}</label>
                                            <span class="textRed">*</span>
                                        </div>
                                        @php
                                            $modeDropdownValue = [
                                                'appbar' => 'Appbar',
                                                'navbar' => 'Navbar',
                                                'drawer' => 'Drawer'
                                            ];
                                        @endphp

                                        <div class="col-sm-4">
                                            {{ html()->select('mode', $modeDropdownValue, $data['mode'])
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.chooseMode'))
                                                ->attribute('disabled',true)
                                            }}
                                            <span class="textRed">{!! $errors->first('mode') !!}</span>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.name')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('name',$data->name)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterName'))
                                            }}
                                            <span class="textRed">{!! $errors->first('name') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.Plugin')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()
                                                ->select('plugin_slug', $pluginDropdown, $data->plugin_slug)
                                                ->class('form-control plugin_slug form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.choosePlugin'))
                                                ->attribute('id',$data['id'])
                                            }}
                                            <span class="textRed">{!! $errors->first('plugin_slug') !!}</span>
                                            <a data-href="{{route('plugin_slug_update_config')}}" class="plugin_slug_update"></a>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.selectedColor')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()->input('color', 'selected_color', $data['selected_color'])
                                                ->class('form-control')
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.unselectedColor')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()->input('color', 'unselected_color', $data['unselected_color'])
                                                ->class('form-control')
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.backgroundColor')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{ html()->input('color', 'background_color', $data['background_color'])
                                                ->class('form-control')
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.Layout')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('layout',$data->layout)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterLayout'))
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.icon')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('icon',$data->icon)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterIconName'))
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.iconThemeSize')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('icon_theme_size',$data->icon_theme_size)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterIconThemeSize'))
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.iconThemeColor')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{ html()
                                                ->input('color', 'icon_theme_color', $data['icon_theme_color'])
                                                ->class('form-control')
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.shadow')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('shadow',$data->shadow)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterShadow'))
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.automaticallyImplyleading')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            @php
                                                $trueFalseDropdownValue = [
                                                    '0' => 'False',
                                                    '1' => 'True',
                                                ];
                                            @endphp
                                            {{ html()->select('automatically_imply_leading', $trueFalseDropdownValue, $data['automatically_imply_leading'])
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.chooseAutomaticallyImplyleading'))
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.centerTitle')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{ html()->select('center_title', $trueFalseDropdownValue, $data['center_title'])
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.chooseCenterTitle'))
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.flexibleSpace')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('flexible_space',$data->flexible_space)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterFlexibleSpace'))
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.toolbarOpacity')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('toolbar_opacity',$data->toolbar_opacity)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterToolbarOpacity'))
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.shapeType')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            @php
                                                $shapeTypeDropdownValue = [
                                                    'square' => 'Square',
                                                    'circle' => 'Circle',
                                                    'stadium' => 'Stadium',
                                                ];
                                            @endphp
                                            {{ html()->select('shape_type', $shapeTypeDropdownValue, $data['shape_type'])
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.chooseShapeType'))
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.shapeBorderRadius')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('shape_border_radius',$data->shape_border_radius)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterShapeBorderRadius'))
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.actionsIconThemeColor')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{ html()
                                                ->input('color', 'actions_icon_theme_color', $data['actions_icon_theme_color'])
                                                ->class('form-control')
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.actionsIconThemeSize')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('actions_icon_theme_size',$data->actions_icon_theme_size)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterActionsIconThemeSize'))
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.titleSpacing')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('title_spacing',$data->title_spacing)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterTitleSpacing'))
                                            }}
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.paddingX')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('padding_x',$data->padding_x)
                                                ->class('form-control')
                                                ->placeholder(__('messages.paddingX'))
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.paddingY')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('padding_y',$data->padding_y)
                                                ->class('form-control')
                                                ->placeholder(__('messages.paddingY'))
                                            }}
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.float')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{ html()
                                                ->select('float', $trueFalseDropdownValue, $data['float'])
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.chooseFloat'))
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.Currency')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{ html()
                                                ->select('currency_id', $currencyDropdown, $data['currency_id'])
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.ChooseCurrency'))
                                            }}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2 mg-top">
                                            <label for="" class="form-label">{{__('messages.marginX')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('margin_x',$data->margin_x)
                                                ->class('form-control')
                                                ->placeholder(__('messages.marginX'))
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.marginY')}}</label>
                                        </div>
                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('margin_y',$data->margin_y)
                                                ->class('form-control')
                                                ->placeholder(__('messages.marginY'))
                                            }}
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.properties')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            <div class="accordion" id="accordionPanelsStayOpenExample">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="panelsStayOpenTextProperties">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpenCollapseTwoTextProperties" aria-expanded="false" aria-controls="panelsStayOpenCollapseTwoTextProperties">
                                                            {{__('messages.textProperties')}}
                                                        </button>
                                                    </h2>
                                                    <div id="panelsStayOpenCollapseTwoTextProperties" class="accordion-collapse collapse" aria-labelledby="panelsStayOpenTextProperties">
                                                        <div class="accordion-body">
                                                            <div class="form-group row mg-top">
                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.Color')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{ html()
                                                                        ->input('color', 'text_properties_color', $data['text_properties_color'])
                                                                        ->class('form-control')
                                                                    }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="panelsStayOpenIconProperties">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpenCollapseIconProperties" aria-expanded="false" aria-controls="panelsStayOpenCollapseIconProperties">
                                                            {{__('messages.iconProperties')}}
                                                        </button>
                                                    </h2>
                                                    <div id="panelsStayOpenCollapseIconProperties" class="accordion-collapse collapse" aria-labelledby="panelsStayOpenIconProperties">
                                                        <div class="accordion-body">

                                                            <div class="form-group row mg-top">
                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.marginX')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('icon_properties_margin_x',$data->icon_properties_margin_x)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.marginX'))
                                                                    }}
                                                                </div>

                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.marginY')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('icon_properties_margin_y',$data->icon_properties_margin_y)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.marginY'))
                                                                    }}
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mg-top">
                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.paddingX')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('icon_properties_padding_x',$data->icon_properties_padding_x)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.paddingX'))
                                                                    }}
                                                                </div>

                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.paddingY')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('icon_properties_padding_y',$data->icon_properties_padding_y)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.paddingY'))
                                                                    }}
                                                                </div>
                                                            </div>


                                                            <div class="form-group row mg-top">
                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.size')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('icon_properties_size',$data->icon_properties_size)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.enterSize'))
                                                                    }}
                                                                </div>

                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.Color')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{ html()
                                                                        ->input('color', 'icon_properties_color', $data['icon_properties_color'])
                                                                        ->class('form-control')
                                                                    }}
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mg-top">
                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.shapeRadius')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('icon_properties_shape_radius',$data->icon_properties_shape_radius)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.enterShapeRadius'))
                                                                    }}
                                                                </div>

                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.backgroundColor')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{ html()
                                                                        ->input('color', 'icon_properties_background_color', $data['icon_properties_background_color'])
                                                                        ->class('form-control')
                                                                    }}
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="panelsStayOpenImageProperties">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpenCollapseImageProperties" aria-expanded="false" aria-controls="panelsStayOpenCollapseImageProperties">
                                                            {{__('messages.ImageProperties')}}
                                                        </button>
                                                    </h2>
                                                    <div id="panelsStayOpenCollapseImageProperties" class="accordion-collapse collapse" aria-labelledby="panelsStayOpenImageProperties">
                                                        <div class="accordion-body">

                                                            <div class="form-group row mg-top">
                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.marginX')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('image_properties_margin_x',$data->image_properties_margin_x)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.marginX'))
                                                                    }}
                                                                </div>

                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.marginY')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('image_properties_margin_y',$data->image_properties_margin_y)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.marginY'))
                                                                    }}
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mg-top">
                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.paddingX')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('image_properties_padding_x',$data->image_properties_padding_x)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.paddingX'))
                                                                    }}
                                                                </div>

                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.paddingY')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('image_properties_padding_y',$data->image_properties_padding_y)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.paddingY'))
                                                                    }}
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mg-top">
                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.height')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('image_properties_height',$data->image_properties_height)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.enterHeight'))
                                                                    }}
                                                                </div>

                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.width')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('image_properties_width',$data->image_properties_width)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.enterWidth'))
                                                                    }}
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mg-top">
                                                                <div class="col-sm-2">
                                                                    <label for="" class="form-label">{{__('messages.shapeRadius')}}</label>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    {{html()
                                                                        ->text('image_properties_shape_radius',$data->image_properties_shape_radius)
                                                                        ->class('form-control')
                                                                        ->placeholder(__('messages.enterShapeRadius'))
                                                                    }}
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.Component')}}</label>
                                        </div>

                                        <div class="col-sm-10 table-responsive">
                                            <table class="table table-striped table-bordered">
                                                <thead>
                                                    <th></th>
                                                    <th>{{__('messages.ComponentName')}}</th>
                                                    <th>{{__('messages.Plugin')}}</th>
                                                    <th>{{__('messages.Position')}}</th>
                                                </thead>
                                                <tbody>
                                                    @if(sizeof($getComponents)>0)
                                                        @foreach($getComponents as $com)
                                                            <tr>
                                                                <td class="textCenter">
                                                                    <div class="form-check form-check-inline">
                                                                        <input style="margin-top: 0px" class="form-check-input global-config-component" name="component_id[]" type="checkbox" id="{{$com->slug}}" value="{{$com->id}}"
                                                                        @if(count($assignComId)>0)
                                                                            {{in_array($com->id,$assignComId)?'checked':''}}
                                                                            @endif
                                                                        >
                                                                        <input type="hidden" id="global_config_id" value="{{$data->id}}">
                                                                        <a data-href="{{route('global_config_assign_component')}}" id="global_config_assign_component"></a>
                                                                        <a data-href="{{route('global_config_assign_component_position')}}" id="global_config_assign_component_position"></a>
                                                                    </div>
                                                                </td>
                                                                <td>{{$com->name}}</td>
                                                                <td>{{$com->plugin_name}}</td>
                                                                @if($data['mode'] === 'navbar')
                                                                    <td>
                                                                        {{ html()->text('position', isset($positions[$com->id]) ? $positions[$com->id] : null)
                                                                            ->class('form-control component_position')
                                                                            ->placeholder(__('messages.EnterPosition'))
                                                                            ->id('component_position_id_' . $com->id)
                                                                            ->attribute('component_id', $com->id)
                                                                        }}
                                                                    </td>
                                                                @else
                                                                    <td>
                                                                        @php
                                                                            $position['title_area'] = 'Title Area';
                                                                            $position['leading_area'] = 'Leading Area';
                                                                            $position['action_area'] = 'Action Area';
                                                                        @endphp
                                                                        {{ html()->select('position', $position, isset($positions[$com->id]) ? $positions[$com->id] : null)
                                                                            ->class('form-control component_position')
                                                                            ->id('component_position_id_' . $com->id)
                                                                            ->placeholder(__('messages.enterPosition'))
                                                                            ->attribute('component_id', $com->id)
                                                                        }}
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="formFile" class="form-label">{{__('messages.image')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            <input class="form-control" name="image" type="file" id="imgInp" accept="image/*">
                                            @if(isset($data->image))
                                                <img src="{{ config('app.image_public_path').$data->image }}" alt="your image" width="25%" />
                                            @endif
                                            <img id="blah" src="#" width="25%" />
                                        </div>
                                    </div>


                                    @if(!$alreadyUse)
                                        <div class="form-group row mg-top">
                                            <div class="col-sm-2">
                                                <label for="" class="form-label">{{__('messages.IsUpcoming')}}</label>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="from-group">
                                                        <?php
                                                        $upcoming = '';
                                                        $notUpcoming = '';
                                                        if (isset($data->is_upcoming)){
                                                            if ($data->is_upcoming == 1){
                                                                $upcoming = 'checked="checked"';
                                                            }else{
                                                                $notUpcoming = 'checked="checked"';
                                                            }
                                                        }else{
                                                            $notUpcoming = 'checked="checked"';
                                                        }
                                                        ?>
                                                    <div class="input-group mb-3">
                                                        <div class="form-check form-check-inline">
                                                            <input style="margin-top: 0px" class="form-check-input isChecked" type="radio" name="is_upcoming" id="is_upcoming1" value="1" {{$upcoming}}>
                                                            <label class="form-check-label" for="is_upcoming1">Yes</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input style="margin-top: 0px" class="form-check-input isChecked" type="radio" name="is_upcoming" id="is_upcoming2" value="0" {{$notUpcoming}}>
                                                            <label class="form-check-label" for="is_upcoming2">No</label>
                                                        </div>
                                                        <span class="textRed">{!! $errors->first('is_upcoming') !!}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.IsActive')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="from-group">
                                                <?php
                                                $Active = '';
                                                $Inactive = '';
                                                if (isset($data->is_active)){
                                                    if ($data->is_active == 1){
                                                        $Active = 'checked="checked"';
                                                    }else{
                                                        $Inactive = 'checked="checked"';
                                                    }
                                                }else{
                                                    $Inactive = 'checked="checked"';
                                                }
                                                ?>
                                                <div class="input-group mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input style="margin-top: 0px" class="form-check-input isChecked " type="radio" name="is_active" id="inlineRadioActive1" value="1" {{$Active}}>
                                                        <label class="form-check-label" for="inlineRadioActive1">Yes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input style="margin-top: 0px" class="form-check-input isChecked ayatFormEdit" type="radio" name="is_active" id="inlineRadioActive2" value="0" {{$Inactive}}>
                                                        <label class="form-check-label" for="inlineRadioActive2">No</label>
                                                    </div>
                                                    <span class="textRed">{!! $errors->first('is_multiple') !!}</span>
                                                </div>
                                            </div>
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
