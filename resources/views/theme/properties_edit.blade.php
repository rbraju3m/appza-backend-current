@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 0px">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.componentInformation')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <a href="{{route('component_add', app()->getLocale())}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus-circle"></i> {{__('messages.createNew')}}
                                        </button>
                                    </a>

                                    <a href="{{route('component_list', app()->getLocale())}}" title="" class="module_button_header">
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
                                <div class="row">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2 text-end">
                                            <label for="" class="form-label">{{__('messages.name')}} &nbsp;&nbsp; : </label>
                                        </div>
                                        <div class="col-sm-3"><label for="" class="form-label">{{$component->name}}</label></div>

                                        <div class="col-sm-2 text-end">
                                            <label for="" class="form-label">{{__('messages.label')}} &nbsp;&nbsp; : </label>
                                        </div>

                                        <div class="col-sm-3"><label for="" class="form-label">{{$component->label}}</label></div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2 text-end">
                                            <label for="" class="form-label">{{__('messages.iconCode')}} &nbsp;&nbsp; : </label>
                                        </div>
                                        <div class="col-sm-3"><label for="" class="form-label">{{$component->icon_code}}</label></div>

                                        <div class="col-sm-2 text-end">
                                            <label for="" class="form-label">{{__('messages.event')}} &nbsp;&nbsp; : </label>
                                        </div>

                                        <div class="col-sm-3"><label for="" class="form-label">{{$component->event}}</label></div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2 text-end">
                                            <label for="" class="form-label">{{__('messages.scope')}} &nbsp;&nbsp; : </label>
                                        </div>
                                        <div class="col-sm-3"><label for="" class="form-label">{{$component->scope}}</label></div>

                                        <div class="col-sm-2 text-end">
                                            <label for="" class="form-label">{{__('messages.classType')}} &nbsp;&nbsp; : </label>
                                        </div>

                                        <div class="col-sm-3"><label for="" class="form-label">{{$component->class_type}}</label></div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2 text-end">
                                            <label for="" class="form-label">{{__('messages.layoutType')}} &nbsp;&nbsp; : </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <label for="" class="form-label">
                                        @if(isset($componentLayout) && count($componentLayout)>0)
                                            @php $i=1 @endphp
                                            @foreach($componentLayout as $comLay)
                                                {{($i==1?'':', ').$comLay['name']}}
                                                        @php $i++ @endphp
                                            @endforeach
                                        @endif
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2 text-end">
                                            <label for="" class="form-label">{{__('messages.productType')}} &nbsp;&nbsp; : </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <label for="" class="form-label">
                                                {{$component->product_type}}
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card" >

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.componentPropertiesUpdate')}}</h6>
                            {{--<div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <a href="{{route('component_add', app()->getLocale())}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus-circle"></i> {{__('messages.createNew')}}
                                        </button>
                                    </a>

                                    <a href="{{route('component_list', app()->getLocale())}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-list"></i> {{__('messages.list')}}
                                        </button>
                                    </a>

                                </div>
                            </div>--}}
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')
                        <div class="row">
                            <div class="col-md-12">
                                {!! Form::model($records, ['method' => 'PATCH','autocomplete'=>'off', 'files'=> true, 'route'=> ['component_properties_update',app()->getLocale(), $component->id],'enctype'=>'multipart/form-data']) !!}

                                <div class="row">

                                    <div class="form-group row mg-top">
                                        <table class="table table-striped table-responsive table-bordered">
                                            <thead>
                                                <th>{{__('messages.layoutType')}}</th>
                                                <th>{{__('messages.propertiesName')}}</th>
                                                <th>{{__('messages.value')}}</th>
{{--                                                <th>{{__('messages.defaultValue')}}</th>--}}
                                            </thead>
                                            @if(count($records)>0)
                                                <tbody>
                                                    @foreach($records as $comPro)
                                                        @php $i=1 @endphp
                                                        @foreach($comPro as $val)
                                                        <tr>
                                                            @if($i==1)
                                                            <td rowspan="{{count($comPro)}}" class="textCenter">
                                                                {{$val['layout_type_name']}}
{{--                                                                {!! Form::text('layout_type_name', $val['layout_type_name'], array('class' => 'form-control','readonly'=>true)) !!}--}}
                                                            </td>
                                                            @endif
                                                            <td>
                                                                {!! Form::text('name', $val['name'], array('class' => 'form-control','readonly'=>true)) !!}
                                                                <input type="hidden" name="component_properties_id[]" value="{{$val['id']}}">
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $dropdownValue = [];
                                                                    if ($val['name'] == 'font_family'){
                                                                        $dropdownValue = [
                                                                              'Arial'=>'Arial',
                                                                              'Verdana'=>'Verdana',
                                                                              'Calibri'=>'Calibri',
                                                                              'Noto'=>'Noto',
                                                                              'Inter'=>'Inter'
                                                                        ];
                                                                    }
                                                                    if ($val['name'] == 'font_weight'){
                                                                        $dropdownValue = [
                                                                              'normal'=>'normal',
                                                                              'bold'=>'bold',
                                                                              'bolder'=>'bolder',
                                                                              'lighter'=>'lighter',
                                                                        ];
                                                                    }
                                                                    if ($val['name'] == 'font_style'){
                                                                        $dropdownValue = [
                                                                              'normal'=>'normal',
                                                                              'Verdana'=>'Verdana',
                                                                              'Calibri'=>'Calibri',
                                                                              'Noto'=>'Noto',
                                                                        ];
                                                                    }
                                                                    if ($val['name'] == 'text_overflow'){
                                                                        $dropdownValue = [
                                                                              'clip'=>'clip',
                                                                              'ellipsis'=>'ellipsis',
                                                                              'string'=>'string',
                                                                              'initial'=>'initial',
                                                                              'inherit'=>'inherit',
                                                                        ];
                                                                    }
                                                                    if ($val['name'] == 'text_font'){
                                                                        $dropdownValue = [
                                                                              'Arial'=>'Arial',
                                                                              'Verdana'=>'Verdana',
                                                                              'Calibri'=>'Calibri',
                                                                              'Noto'=>'Noto',
                                                                        ];
                                                                    }
                                                                @endphp

                                                                @if($val['input_type'] == 'number')
                                                                    {!! Form::number('value[]', $val['value'], array('class' => 'form-control')) !!}
                                                                @endif

                                                                @if($val['input_type'] == 'color')
                                                                    {!! Form::color('value[]', $val['value'], array('class' => 'form-control')) !!}
                                                                @endif

                                                                @if($val['input_type'] == 'select')
{{--                                                                    {!! Form::text('rrr[]', $val->value, array('class' => 'form-control')) !!}--}}
                                                                    {!! Form::select('value[]',$dropdownValue, $val['value'], array('class' => 'form-control')) !!}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @php $i++ @endphp
                                                    @endforeach
                                                    @endforeach
                                                </tbody>
                                            @endif
                                        </table>
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

                                                                {!! Form::close() !!}
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
        $(function () {
        });
    </script>

@endsection
