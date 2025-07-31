@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.ComponentGroupUpdate')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <a href="{{route('component_group_add')}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-list"></i> {{__('messages.componentGroup')}}
                                        </button>
                                    </a>

                                    <a href="{{route('component_group_list')}}" title="" class="module_button_header">
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
                                {{ html()->form('PATCH', route('component_group_update',$data->id))
                                    ->attribute('enctype', 'multipart/form-data')
                                    ->attribute('files', true)
                                    ->attribute('autocomplete', 'off')
                                    ->open() }}

                                <div class="row">

                                    <div class="form-group row">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.ComponentGroupName')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->text('name')
                                                ->value($data->name)
                                                ->class('form-control')
                                                ->placeholder(__('messages.ComponentGroupName'))
                                            }}
                                            <span class="textRed">{!! $errors->first('name') !!}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-4">
                                        <div class="col-sm-2">
                                            <label for="" class="form-label">{{__('messages.IconName')}}</label>
                                            <span class="textRed">*</span>
                                        </div>

                                        <div class="col-sm-10">
                                            {{html()
                                                ->text('icon')
                                                ->value($data->icon)
                                                ->class('form-control')
                                                ->placeholder(__('messages.IconName'))
                                            }}
                                            <span class="textRed">{!! $errors->first('icon') !!}</span>
                                        </div>
                                    </div>


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

    </script>

@endsection
