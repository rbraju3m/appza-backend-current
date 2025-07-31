@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.themeSearchCustomize')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    {{--<a href="{{route('component_add', app()->getLocale())}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus-circle"></i> {{__('messages.createNew')}}
                                        </button>
                                    </a>

                                    <a href="{{route('component_list', app()->getLocale())}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-list"></i> {{__('messages.list')}}
                                        </button>
                                    </a>--}}

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')
                        <div class="row">
                            <div class="col-md-12">
                                {!! Form::model($searchFeildCommon, ['method' => 'PATCH','autocomplete'=>'off', 'files'=> true, 'route'=> ['search_field_update',app()->getLocale(), $id],'enctype'=>'multipart/form-data']) !!}

                                <div class="row">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.fillColor')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {!! Form::color('fill_color', $searchFeildCommon->fill_color, array('class' => 'form-control')) !!}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.pageTitleText')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {!! Form::text('page_title_text', $searchFeildCommon->page_title_text, array('class' => 'form-control ','placeholder'=>__('messages.pageTitleText'))) !!}
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.pageTitleColor')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {!! Form::color('page_title_color', $searchFeildCommon->page_title_color, array('class' => 'form-control')) !!}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.pageTitleFontSize')}}</label>
                                        </div>

                                        <div class="col-sm-4">
                                            {!! Form::text('page_title_font_size', $searchFeildCommon->page_title_font_size, array('class' => 'form-control ','placeholder'=>__('messages.pageTitleFontSize'))) !!}
                                        </div>
                                    </div>


                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.styleGroup')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">

    <div class="accordion" id="accordionExample">
        <br><span class="textRed">{!! $errors->first('style_group') !!}</span>
        @if(sizeof($styleGroups)>0)
            @foreach($styleGroups as $key => $styleGroup)
                <div class="accordion-item" @if($key!=0) style="margin-top: 10px" @endif>
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne{{$styleGroup['id']}}" aria-expanded="true" aria-controls="collapseOne{{$styleGroup['id']}}">
                            <div class="form-check form-check-inline">
                                <input style="margin-top: 0px" class="form-check-input" name="style_group[]" type="checkbox" id="{{$styleGroup['slug']}}" value="{{$styleGroup['id']}}"
                                {{--@if(count($componentStyleIdArray)>0)
                                    {{in_array($styleGroup['id'],$componentStyleIdArray)?'checked':''}}
                                    @endif--}}
                                >
                                <label class="form-check-label" for="{{$styleGroup['slug']}}">{{$styleGroup['name']}}</label>
                            </div>
                        </button>
                    </h2>
                    <div id="collapseOne{{$styleGroup['id']}}" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            {{--@if(sizeof($styleGroup['properties'])>0)
                                <table class="table table-striped">
                                    <a data-href="{{route('component_properties_inline_update',app()->getLocale())}}" id="component_properties_inline_update"></a>
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

                                                @if($pro['input_type'] == 'number')
                                                    {!! Form::text('value[]', $pro['value'], array('class' => 'form-control inline_update','component_properties_id'=>$pro['id'])) !!}
                                                @endif
                                                @if($pro['input_type'] == 'double')
                                                    {!! Form::text('value[]', $pro['value'], array('class' => 'form-control inline_update','component_properties_id'=>$pro['id'])) !!}
                                                @endif

                                                @if($pro['input_type'] == 'color')
                                                    {!! Form::color('value[]', $pro['value'], array('class' => 'form-control inline_update','component_properties_id'=>$pro['id'])) !!}
                                                @endif

                                                @if($pro['input_type'] == 'select')
                                                    {!! Form::select('value[]',$dropdownValue, $pro['value'], array('class' => 'form-control inline_update form-select js-example-basic-single','style'=>'width:100% !important','component_properties_id'=>$pro['id'])) !!}
                                                @endif

                                                @if($pro['input_type'] == 'boolean')
                                                    {!! Form::select('value[]',$dropdownValue, $pro['value'], array('class' => 'form-control inline_update form-select js-example-basic-single','style'=>'width:100% !important','component_properties_id'=>$pro['id'])) !!}
                                                @endif
                                            </td>
                                        </tr>
                                @endforeach
                                </table>
                            @endif--}}
                        </div>
                    </div>
                </div>
            @endforeach
            @endif
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

                                {!! Form::close() !!}
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
                    <button type="button" class="btn customButton" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>                    <button type="button" class="btn btn-primary modelDataInsert">Save changes</button>
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


        image_url.onchange = evt => {
            const [file] = image_url.files
            if (file) {
                blahurl.src = URL.createObjectURL(file)
            }
        }

        $(document).delegate('.inline_update','change',function(){
            let value = $(this).val();
            let component_properties_id = $(this).attr('component_properties_id');
            let route = $('#component_properties_inline_update').attr('data-href');
            // console.log(value,component_properties_id)

            $.ajax({
                url: route,
                method: "get",
                dataType: "json",
                data: {component_properties_id: component_properties_id,value:value},
                beforeSend: function( xhr ) {

                }
            }).done(function( response ) {
                console.log(response)
                /*if(response.status=='ok') {
                    isChecked == 1 ? $('.checked_id_' + id).prop('checked', true) : $('.checked_id_' + id).prop('checked', false)
                }*/
            }).fail(function( jqXHR, textStatus ) {

            });
            return false;
            /*let isChecked = 0
            if($(this).is(':checked')){isChecked = 1}
            let id = $(this).attr('value')
            let route = $('#theme_component_update').attr('data-href');
            */
        });
    </script>

@endsection
