@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{$styleGroup->name}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    {{-- <a href="{{route('component_add', app()->getLocale())}}" title="" class="module_button_header">
                                         <button type="button" class="btn btn-sm btn-outline-secondary">
                                             <i class="fas fa-plus-circle"></i> {{__('messages.createNew')}}
                                         </button>
                                     </a>--}}

                                    <a href="{{route('style_group_list')}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-list"></i> {{__('messages.styleGroupList')}}
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

                                {{ html()->form('PATCH', route('style_group_properties_update', $styleGroup->id))->open() }}
                                <div class="row">

                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="formFile" class="form-label">{{__('messages.styleProperties')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            @if(count($styleProperties)>0)
                                                @foreach($styleProperties as $properties)
                                                    <div class="form-check form-check-inline">
                                                        <input style="margin-top: 0px" class="form-check-input" name="properties_id[]" type="checkbox" id="{{$properties->name}}" value="{{$properties->id}}"
                                                        @if(count($existsPropertiesArray)>0)
                                                            {{in_array($properties->id,$existsPropertiesArray)?'checked':''}}
                                                            @endif
                                                        >
                                                        <label class="form-check-label" for="{{$properties->name}}">{{$properties->name}}</label>
                                                    </div>
                                                @endforeach
                                            @endif
                                            <br><span class="textRed">{!! $errors->first('properties_id') !!}</span>
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

    </script>

@endsection
