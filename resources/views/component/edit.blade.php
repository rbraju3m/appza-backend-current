@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.componentUpdate')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <a href="{{route('component_add')}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus-circle"></i> {{__('messages.createNew')}}
                                        </button>
                                    </a>

                                    <a href="{{route('component_list')}}" title="" class="module_button_header">
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
                                {{ html()->modelForm($data, 'PATCH', route('component_update', $data->id))
                                    ->attribute('enctype', 'multipart/form-data')
                                    ->attribute('files', true)
                                    ->attribute('autocomplete', 'off')
                                    ->open() }}

                                <div class="row">
                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="transparent"
                                                   class="form-label">{{__('messages.Plugin')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            <input type="hidden" name="plugin_slug" value="{{$data['plugin_slug']}}">
                                            {{--{{ html()
                                                ->select('plugin_slug', $pluginDropdown, $theme->plugin_slug)
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.choosePlugin'))
                                                ->attribute('disabled',true)
                                            }}--}}
                                            {{ html()->select('plugin_slug', $pluginDropdown, $data['plugin_slug'])
                                                ->class('form-control form-select js-example-basic-single plugin_slug')
                                                ->placeholder(__('messages.choosePlugin'))
                                                ->attribute('id',$data['id'])
                                                ->attribute('disabled',true)
                                            }}
                                            <br><span class="textRed">{!! $errors->first('plugin_slug') !!}</span>
                                            <a data-href="{{route('plugin_slug_update_component')}}" class="plugin_slug_update"></a>
                                        </div>
                                    </div>
                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.name')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('name')
                                                ->value($data->name)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterComponentName'))
                                            }}
                                            <span class="textRed">{!! $errors->first('name') !!}</span>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.label')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('label')
                                                ->value($data->label)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterComponentLabel'))
                                            }}
                                            <span class="textRed">{!! $errors->first('label') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for=""
                                                   class="form-label">{{__('messages.iconName')}}</label>
                                            {{--                                            <span class="textRed">*</span>--}}
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('icon_code')
                                                ->value($data->icon_code)
                                                ->class('form-control')
                                                ->placeholder(__('messages.enterIconName'))
                                            }}
                                            <span class="textRed">{!! $errors->first('icon_code') !!}</span>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.event')}}</label>
                                            {{--                                            <span class="textRed">*</span>--}}
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('event')
                                                ->value($data->event)
                                                ->class('form-control')
                                                ->placeholder(__('messages.EnterEvent'))
                                            }}
                                            <span class="textRed">{!! $errors->first('event') !!}</span>
                                        </div>
                                    </div>

                                    {{--<div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.classType')}}</label>
                                            <span class="textRed">*</span>
                                        </div>
                                        <div class="col-sm-10">
                                            {!! Form::text('class_type', null, array('class' => 'form-control ','placeholder'=>__('messages.EnterClassType'))) !!}
                                            <span class="textRed">{!! $errors->first('class_type') !!}</span>
                                        </div>
                                    </div>--}}

                                    <div class="form-group row mg-top">

                                        <div class="col-sm-2">
                                            <label for=""
                                                   class="form-label">{{__('messages.productType')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()->select('product_type', $classTypesDropdown, $data['product_type'])
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.chooseProductType'))
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="formFile"
                                                   class="form-label">{{__('messages.webIcon')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{html()
                                                ->text('web_icon')
                                                ->value($data->web_icon)
                                                ->class('form-control')
                                                ->placeholder(__('messages.EnterWebIcon'))
                                            }}
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="layout_type_id"
                                                   class="form-label">{{__('messages.layoutType')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()->select('layout_type_id', $layoutTypes, $data['layout_type_id'])
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.chooseLayoutType'))
                                                ->attribute('id','layout_type')
                                            }}
                                            <br><span class="textRed">{!! $errors->first('layout_type_id') !!}</span>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="transparent"
                                                   class="form-label">{{__('messages.Transparent')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            @php
                                                $transparentDropdown['True'] = 'True';
                                                $transparentDropdown['False'] = 'False';
                                            @endphp
                                            {{ html()->select('transparent', $transparentDropdown, $data['transparent'])
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.chooseTransparent'))
                                            }}
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="layout_type_id"
                                                   class="form-label">{{__('messages.selectedDesign')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            @php
                                                $selectedDesignDropdown=[];
                                                $pageDetailsDropdown=[];
                                                for ($i=1;$i<=10;$i++){
                                                    $selectedDesignDropdown[$i] = $i;
                                                    $pageDetailsDropdown[$i] = $i;
                                                }
                                            @endphp
                                            {{ html()->select('selected_design', $selectedDesignDropdown, $data['selected_design'])
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.chooseSelectedDesign'))
                                            }}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="transparent"
                                                   class="form-label">{{__('messages.pageDetails')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {{ html()->select('details_page', $pageDetailsDropdown, $data['details_page'])
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.choosePageDetails'))
                                            }}
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">

                                        <div class="col-sm-2">
                                            <label for="transparent"
                                                   class="form-label">{{__('messages.componentType')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-4">
                                            @php
                                                $componentTypeDropdown=[];
                                                foreach ($componentType as $type){
                                                    $componentTypeDropdown[$type['id']] = $type['name'];
                                                }
                                            @endphp
                                            {{ html()->select('component_type_id', $componentTypeDropdown, $data['component_type_id'])
                                                ->class('form-control form-select js-example-basic-single')
                                                ->placeholder(__('messages.chooseComponentType'))
                                            }}
                                            <br><span class="textRed">{!! $errors->first('component_type_id') !!}</span>
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.Items')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            <span style="color: red" id="items_message"></span>
                                            {{html()
                                                ->textarea('items')
                                                ->value($data->items?json_encode(json_decode($data->items, false), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES):null)
                                                ->class('form-control')
                                                ->placeholder(__('messages.EnterItemsJson'))
                                                ->attribute('rows',5)
                                            }}
                                            <br><span class="textRed">{!! $errors->first('items') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.DevData')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->textarea('dev_data')
                                                ->value($data->dev_data?json_encode(json_decode($data->dev_data, false), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES):null)
                                                ->class('form-control')
                                                ->placeholder(__('messages.EnterDevData'))
                                                ->attribute('rows',5)
                                            }}
                                            <br><span class="textRed">{!! $errors->first('dev_data') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.Filters')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->textarea('filters')
                                                ->value($data->filters ? json_encode(json_decode($data->filters, false),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES): null )
                                                ->class('form-control')
                                                ->placeholder(__('messages.EnterFiltersJson'))
                                                ->attribute('rows',5)
                                            }}
                                            <br><span class="textRed">{!! $errors->first('filters') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.pagination')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            {{ html()
                                                ->textarea('pagination')
                                                ->value($data->pagination ? json_encode(json_decode($data->pagination, false),JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES): null )
                                                ->class('form-control')
                                                ->placeholder(__('messages.EnterPaginationData'))
                                                ->attribute('rows',5)
                                                }}
                                            <br><span class="textRed">{!! $errors->first('pagination') !!}</span>
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for=""
                                                   class="form-label">{{__('messages.styleGroup')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">

                                            <div class="accordion" id="accordionExample">
                                                <br><span class="textRed">{!! $errors->first('style_group') !!}</span>
                                                @if(sizeof($componentStyleGroup)>0)
                                                    @foreach($componentStyleGroup as $key => $styleGroup)
                                                        <div class="accordion-item"
                                                             @if($key!=0) style="margin-top: 10px" @endif>
                                                            <h2 class="accordion-header" id="headingOne">
                                                                <button class="accordion-button" type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#collapseOne{{$styleGroup['id']}}"
                                                                        aria-expanded="true"
                                                                        aria-controls="collapseOne{{$styleGroup['id']}}">
                                                                    <div class="form-check form-check-inline">
                                                                        <input style="margin-top: 0px"
                                                                               class="form-check-input"
                                                                               name="style_group[]" type="checkbox"
                                                                               id="{{$styleGroup['style_group']['slug']}}"
                                                                               value="{{$styleGroup['style_group_id']}}"
                                                                        @if(count($componentStyleIdArray)>0)
                                                                            {{in_array($styleGroup['style_group_id'],$componentStyleIdArray)?'checked':''}}
                                                                            @endif
                                                                        >
                                                                        <label class="form-check-label"
                                                                               for="{{$styleGroup['style_group']['slug']}}">{{$styleGroup['style_group']['name']}}</label>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapseOne{{$styleGroup['id']}}"
                                                                 class="accordion-collapse collapse"
                                                                 aria-labelledby="headingOne"
                                                                 data-bs-parent="#accordionExample">
                                                                <div class="accordion-body">
                                                                    @if(sizeof($styleGroup['properties'])>0)
                                                                        <table class="table table-striped">
                                                                            <a data-href="{{route('component_properties_inline_update')}}"
                                                                               id="component_properties_inline_update"></a>
                                                                            @foreach($styleGroup['properties'] as $pro)
                                                                                <tr>
                                                                                    <th>{{$pro['name']}}</th>
                                                                                    <td>
                                                                                        @php
                                                                                            $dropdownValue = [];
                                                                                            if ($pro['name'] == 'font_family'){
                                                                                                $dropdownValue = [
                                                                                                      'Lato'=>'Lato',
                                                                                                      'Poppins'=>'Poppins',
                                                                                                      'Roboto'=>'Roboto',
                                                                                                      'Open Sans'=>'Open Sans',
                                                                                                      'Inter'=>'Inter'
                                                                                                ];
                                                                                            }
                                                                                            if ($pro['name'] == 'font_weight'){
                                                                                                $dropdownValue = [
                                                                                                      'normal'=>'Normal',
                                                                                                      'bold'=>'Bold',
                                                                                                      '100'=>'100',
                                                                                                      '200'=>'200',
                                                                                                      '300'=>'300',
                                                                                                      '400'=>'400',
                                                                                                      '500'=>'500',
                                                                                                      '600'=>'600',
                                                                                                      '700'=>'700',
                                                                                                      '800'=>'800',
                                                                                                      '900'=>'900'
                                                                                                ];
                                                                                            }
                                                                                            if ($pro['name'] == 'font_style'){
                                                                                                $dropdownValue = [
                                                                                                      'Lato'=>'Lato',
                                                                                                      'Poppins'=>'Poppins',
                                                                                                      'Roboto'=>'Roboto',
                                                                                                      'Open Sans'=>'Open Sans',
                                                                                                      'normal'=>'Normal',
                                                                                                ];
                                                                                            }
                                                                                            if ($pro['name'] == 'text_overflow'){
                                                                                                $dropdownValue = [
                                                                                                      'ellipsis'=>'ellipsis',
                                                                                                      'visible'=>'visible',
                                                                                                ];
                                                                                            }
                                                                                            if ($pro['name'] == 'text_font'){
                                                                                                $dropdownValue = [
                                                                                                      'Lato'=>'Lato',
                                                                                                      'Poppins'=>'Poppins',
                                                                                                      'Roboto'=>'Roboto',
                                                                                                      'Open Sans'=>'Open Sans',
                                                                                                ];
                                                                                            }
                                                                                            if ($pro['name'] == 'boxfit'){
                                                                                                $dropdownValue = [
                                                                                                      'cover'=>'cover',
                                                                                                      'fill'=>'fill',
                                                                                                      'contain'=>'contain',
                                                                                                ];
                                                                                            }
                                                                                            if ($pro['name'] == 'alignment'){
                                                                                                $dropdownValue = [
                                                                                                      'top'=>'top',
                                                                                                      'topCenter'=>'topCenter',
                                                                                                      'topRight'=>'topRight',
                                                                                                      'topLeft'=>'topLeft',
                                                                                                      'center'=>'center',
                                                                                                      'centerRight'=>'centerRight',
                                                                                                      'centerLeft'=>'centerLeft',
                                                                                                      'bottom'=>'bottom',
                                                                                                      'bottomLeft'=>'bottomLeft',
                                                                                                      'bottomRight'=>'bottomRight',
                                                                                                      'start'=>'start',
                                                                                                      'end'=>'end',
                                                                                                ];
                                                                                            }
                                                                                            if ($pro['name'] == 'text_decoration'){
                                                                                                $dropdownValue = [
                                                                                                      'lineThrough'=>'lineThrough',
                                                                                                      'none'=>'none'
                                                                                                ];
                                                                                            }
                                                                                            if ($pro['name'] == 'auto_play'){
                                                                                                $dropdownValue = [
                                                                                                      'true'=>'true',
                                                                                                      'false'=>'false'
                                                                                                ];
                                                                                            }
                                                                                            if ($pro['name'] == 'indicator_visibility'){
                                                                                                $dropdownValue = [
                                                                                                      'true'=>'true',
                                                                                                      'false'=>'false'
                                                                                                ];
                                                                                            }
                                                                                            if ($pro['name'] == 'shape'){
                                                                                                $dropdownValue = [
                                                                                                      'square'=>'Square',
                                                                                                      'circle'=>'Circle',
                                                                                                      'width'=>'Width'
                                                                                                ];
                                                                                            }
                                                                                            if ($pro['name'] == 'text_align'){
                                                                                                $dropdownValue = [
                                                                                                      'start'=>'start',
                                                                                                      'center'=>'center',
                                                                                                      'end'=>'end'
                                                                                                ];
                                                                                            }
                                                                                        @endphp


                                                                                        @if(isset($pro['input_type'], $pro['id'], $pro['value']))
                                                                                            @switch($pro['input_type'])
                                                                                                @case('number')
                                                                                                @case('double')
                                                                                                    {{ html()->text('value[]', $pro['value'])
                                                                                                        ->class('form-control inline_update')
                                                                                                        ->attribute('component_properties_id', $pro['id']) }}
                                                                                                    @break

                                                                                                @case('color')
                                                                                                    {{ html()->input('color', 'value[]', $pro['value'])
                                                                                                        ->class('form-control inline_update')
                                                                                                        ->attribute('component_properties_id', $pro['id']) }}
                                                                                                    @break

                                                                                                @case('select')
                                                                                                @case('boolean')
                                                                                                    {{ html()->select('value[]', $dropdownValue, $pro['value'])
                                                                                                        ->class('form-control inline_update form-select js-example-basic-single')
                                                                                                        ->style('width:100% !important')
                                                                                                        ->attribute('component_properties_id', $pro['id']) }}
                                                                                                    @break
                                                                                            @endswitch
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </table>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>

                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for=""
                                                   class="form-label">{{__('messages.globalScope')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            @php
                                                $scopeArray = json_decode($data['scope']);
                                            @endphp
                                            @if(count($scopeArrayData['global-scope'])>0)
                                                @foreach($scopeArrayData['global-scope'] as $scopeGlobal)
                                                    <div class="form-check form-check-inline">
                                                        <input style="margin-top: 0px" class="form-check-input"
                                                               name="scope[]" type="checkbox"
                                                               id="{{$scopeGlobal['slug']}}"
                                                               value="{{$scopeGlobal['slug']}}"
                                                        @if(isset($scopeArray) && count($scopeArray)>0)
                                                            {{in_array($scopeGlobal['slug'],$scopeArray)?'checked':''}}
                                                            @endif
                                                        >
                                                        <label class="form-check-label"
                                                               for="{{$scopeGlobal['slug']}}">{{$scopeGlobal['name']}}</label>
                                                    </div>
                                                @endforeach
                                                <br><span class="textRed">{!! $errors->first('scope') !!}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for=""
                                                   class="form-label">{{__('messages.pageScope')}}</label>
                                            {{--                                            <span class="textRed">*</span>--}}
                                        </div>

                                        <div class="col-sm-10">
                                            @php
                                                $scopeArray = json_decode($data['scope']);
                                            @endphp
                                            @if(isset($scopeArrayData['page-scope']) && count($scopeArrayData['page-scope'])>0)
                                                @foreach($scopeArrayData['page-scope'] as $scopePage)
                                                    <div class="form-check form-check-inline">
                                                        <input style="margin-top: 0px" class="form-check-input"
                                                               name="scope[]" type="checkbox"
                                                               id="{{$scopePage['slug']}}"
                                                               value="{{$scopePage['slug']}}"
                                                        @if(isset($scopeArray) && count($scopeArray)>0)
                                                            {{in_array($scopePage['slug'],$scopeArray)?'checked':''}}
                                                            @endif
                                                        >
                                                        <label class="form-check-label"
                                                               for="{{$scopePage['slug']}}">{{$scopePage['name']}}</label>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.isMultiple')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="from-group">
                                                <?php
                                                $Active = '';
                                                $Inactive = '';
                                                if (isset($data->is_multiple)) {
                                                    if ($data->is_multiple == 1) {
                                                        $Active = 'checked="checked"';
                                                    } else {
                                                        $Inactive = 'checked="checked"';
                                                    }
                                                } else {
                                                    $Inactive = 'checked="checked"';
                                                }
                                                ?>
                                                <div class="input-group mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input style="margin-top: 0px"
                                                               class="form-check-input isChecked ayatFormEdit"
                                                               type="radio" name="is_multiple" id="is_multiple1"
                                                               value="1" {{$Active}}>
                                                        <label class="form-check-label" for="is_multiple1">Yes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input style="margin-top: 0px"
                                                               class="form-check-input isChecked ayatFormEdit"
                                                               type="radio" name="is_multiple" id="is_multiple2"
                                                               value="0" {{$Inactive}}>
                                                        <label class="form-check-label" for="is_multiple2">No</label>
                                                    </div>
                                                    <span class="textRed">{!! $errors->first('is_multiple') !!}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.showNoDataView')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="from-group">
                                                <?php
                                                $showNoDataViewTrue = '';
                                                $showNoDataViewFalse = '';
                                                if (isset($data->show_no_data_view)) {
                                                    if ($data->show_no_data_view == 1) {
                                                        $showNoDataViewTrue = 'checked="checked"';
                                                    } else {
                                                        $showNoDataViewFalse = 'checked="checked"';
                                                    }
                                                } else {
                                                    $showNoDataViewFalse = 'checked="checked"';
                                                }
                                                ?>
                                                <div class="input-group mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input style="margin-top: 0px"
                                                               class="form-check-input isChecked ayatFormEdit"
                                                               type="radio" name="show_no_data_view" id="showNoDataViewTrue"
                                                               value="1" {{$showNoDataViewTrue}}>
                                                        <label class="form-check-label" for="showNoDataViewTrue">True</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input style="margin-top: 0px"
                                                               class="form-check-input isChecked ayatFormEdit"
                                                               type="radio" name="show_no_data_view" id="showNoDataViewFalse"
                                                               value="0" {{$showNoDataViewFalse}}>
                                                        <label class="form-check-label" for="showNoDataViewFalse">False</label>
                                                    </div>
                                                    <span class="textRed">{!! $errors->first('show_no_data_view') !!}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if(!$alreadyUse)
                                        <div class="form-group row mg-top">
                                            <div class="col-sm-2">
                                                <label for=""
                                                       class="form-label">{{__('messages.IsUpcoming')}}</label>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="from-group">
                                                        <?php
                                                        $upcoming = '';
                                                        $notUpcoming = '';
                                                        if (isset($data->is_upcoming)) {
                                                            if ($data->is_upcoming == 1) {
                                                                $upcoming = 'checked="checked"';
                                                            } else {
                                                                $notUpcoming = 'checked="checked"';
                                                            }
                                                        } else {
                                                            $notUpcoming = 'checked="checked"';
                                                        }
                                                        ?>
                                                    <div class="input-group mb-3">
                                                        <div class="form-check form-check-inline">
                                                            <input style="margin-top: 0px"
                                                                   class="form-check-input isChecked" type="radio"
                                                                   name="is_upcoming" id="is_upcoming1"
                                                                   value="1" {{$upcoming}}>
                                                            <label class="form-check-label"
                                                                   for="is_upcoming1">Yes</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input style="margin-top: 0px"
                                                                   class="form-check-input isChecked" type="radio"
                                                                   name="is_upcoming" id="is_upcoming2"
                                                                   value="0" {{$notUpcoming}}>
                                                            <label class="form-check-label"
                                                                   for="is_upcoming2">No</label>
                                                        </div>
                                                        <span
                                                            class="textRed">{!! $errors->first('is_upcoming') !!}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for=""
                                                   class="form-label">{{__('messages.IsActive')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="from-group">
                                                <?php
                                                $Active = '';
                                                $Inactive = '';
                                                if (isset($data->is_active)) {
                                                    if ($data->is_active == 1) {
                                                        $Active = 'checked="checked"';
                                                    } else {
                                                        $Inactive = 'checked="checked"';
                                                    }
                                                } else {
                                                    $Inactive = 'checked="checked"';
                                                }
                                                ?>
                                                <div class="input-group mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input style="margin-top: 0px"
                                                               class="form-check-input isChecked " type="radio"
                                                               name="is_active" id="inlineRadioActive1"
                                                               value="1" {{$Active}}>
                                                        <label class="form-check-label"
                                                               for="inlineRadioActive1">Yes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input style="margin-top: 0px"
                                                               class="form-check-input isChecked ayatFormEdit"
                                                               type="radio" name="is_active" id="inlineRadioActive2"
                                                               value="0" {{$Inactive}}>
                                                        <label class="form-check-label"
                                                               for="inlineRadioActive2">No</label>
                                                    </div>
                                                    <span class="textRed">{!! $errors->first('is_multiple') !!}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="formFile"
                                                   class="form-label">{{__('messages.image')}}</label>
                                        </div>

                                        <div class="col-sm-10">
                                            <input class="form-control" name="image" type="file" id="imgInp"
                                                   accept="image/*">
                                            @if(isset($data->image))
                                                <img src="{{ config('app.image_public_path').$data->image }}" alt="your image" width="25%" />
                                            @endif
                                            <img id="blah" src="#" width="25%"/>
                                        </div>
                                    </div>


                                    {{--<div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="imageUrl"
                                                   class="form-label">{{__('messages.imageUrl')}}</label>

                                        </div>

                                        <div class="col-sm-10">
                                            <input class="form-control" name="image_url" type="file" id="image_url"
                                                   accept="image/*">
                                            @if(isset($data->image_url))
                                                <img src="{{ config('app.image_public_path').$data->image_url }}" alt="your image" width="25%" />
                                            @endif
                                            <img id="blahurl" src="#" width="25%"/>
                                        </div>
                                    </div>--}}


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
                    <button type="button" class="btn customButton" data-bs-dismiss="modal" aria-label="Close"><i
                            class="fas fa-times"></i></button>
                    <button type="button" class="btn btn-primary modelDataInsert">Save changes</button>
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

        .imageText {
            background: blue;
            color: #fff;
            padding: 5px 5px;
            display: block;
            margin-top: 2px;
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

    <script type="text/javascript">
        imgInp.onchange = evt => {
            const [file] = imgInp.files
            if (file) {
                blah.src = URL.createObjectURL(file)
            }
        }


        /*image_url.onchange = evt => {
            const [file] = image_url.files
            if (file) {
                blahurl.src = URL.createObjectURL(file)
            }
        }*/

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


        $(document).delegate('.inline_update', 'change', function () {
            let value = $(this).val();
            let component_properties_id = $(this).attr('component_properties_id');
            let route = $('#component_properties_inline_update').attr('data-href');
            // console.log(value,component_properties_id,route)

            if(value && component_properties_id && route) {
                $.ajax({
                    url: route,
                    method: "get",
                    dataType: "json",
                    data: {component_properties_id: component_properties_id, value: value},
                    beforeSend: function (xhr) {

                    }
                }).done(function (response) {
                    console.log(response)
                    /*if(response.status=='ok') {
                        isChecked == 1 ? $('.checked_id_' + id).prop('checked', true) : $('.checked_id_' + id).prop('checked', false)
                    }*/
                }).fail(function (jqXHR, textStatus) {

                });
                return false;
            }else {
                alert('Field value missing.')
            }
            /*let isChecked = 0
            if($(this).is(':checked')){isChecked = 1}
            let id = $(this).attr('value')
            let route = $('#theme_component_update').attr('data-href');
            */
        });

        $(document).delegate('#layout_type', 'change', function () {
            let value = $(this).val();
            if (value==8){
                let span = document.getElementById('items_message')
                span.textContent = "If you populate the items JSON, it will replace the banner items data."
            }

        });
    </script>

@endsection
