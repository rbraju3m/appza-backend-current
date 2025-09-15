@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 50px !important;">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <h6>{{__('messages.AddonVersionList')}}</h6>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <a href="{{route('addon_version_add')}}" title="" class="module_button_header">
                                    <button type="button" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-plus-circle"></i> {{__('messages.createAddon')}}
                                    </button>
                                </a>
                            </div>
                        </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')

                        <form method="post" role="form" id="search-form">
                            <table id="leave_settings" class="table table-bordered datatable table-responsive mainTable text-center">

                                <thead class="thead-dark">
                                <tr>
                                    <th>{{__('messages.SL')}}</th>
                                    <th>{{__('messages.ProductName')}}</th>
                                    <th>{{__('messages.name')}}</th>
                                    <th>{{__('messages.slug')}}</th>
                                    <th>{{__('messages.version')}}</th>
                                    {{--                                    <th>{{__('messages.prefix')}}</th>--}}
                                    {{--                                    <th>{{__('messages.Disable')}}</th>--}}
                                    {{--                                    <th>{{__('messages.image')}}</th>--}}
                                    <th scope="col text-center" class="sorting_disabled" rowspan="1" colspan="1" aria-label style="width: 24px;">
                                        <i class="fas fa-cog"></i>
                                    </th>
                                </tr>
                                </thead>

                                @if(sizeof($versions)>0)
                                    <tbody>
                                    @php
                                        $i=1;
                                        $currentPage = $versions->currentPage();
                                        $perPage = $versions->perPage();
                                        $serial = ($currentPage - 1) * $perPage + 1;
                                    @endphp
                                    @foreach($versions as $addon)
                                        <tr>
                                            <td>{{$serial++}}</td>
                                            <td>{{$addon->product_name}}</td>
                                            <td>{{$addon->addon_name}}</td>
                                            <td>{{$addon->addon_slug}}</td>
                                            <td>{{$addon->version}}</td>
                                            <td>
                                                <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                    {{--                                                        <a title="Added Version" class="btn btn-outline-danger btn-sm" href="{{route('addon_version_added',[$addon->id,$addon->addon_id])}}"><i class="fas fa-font"></i></a>--}}
                                                    {{--@if((auth()->user()->user_type === 'DEVELOPER' || auth()->user()->user_type === 'ADMIN' || auth()->user()->user_type === 'PLUGIN') && $addon->is_edited==1 )
                                                        <a title="Edit" class="btn btn-outline-primary btn-sm" href="{{route('addon_version_edit',$addon->id)}}"><i class="fas fa-edit"></i></a>
                                                    @endif--}}
                                                    {{--                                                        <a title="Delete" onclick="return confirm('Are you sure?');" class="btn btn-outline-danger btn-sm" href="{{route('page_delete',$page->id)}}"><i class="fas fa-trash"></i></a>--}}
                                                </div>
                                            </td>
                                        </tr>
                                        @php $i++; @endphp
                                    @endforeach
                                    </tbody>
                                @endif
                            </table>
                            @if(isset($versions) && count($versions)>0)
                                <div class=" justify-content-right">
                                    {{ $versions->links('layouts.pagination') }}
                                </div>
                            @endif
                        </form>

                        <hr style="margin-top: 20px">

                        <div class="row">
                                <div class="col-md-12">
                                    {{ html()
                                        ->form('POST', route('added_version_store',$addonId))
                                        ->attribute('enctype', 'multipart/form-data')
                                        ->attribute('files', true)
                                        ->attribute('autocomplete', 'off')
                                        ->open()
                                    }}
                                    <div class="row">

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

                                            <div class="col-sm-2">
                                                <label for="formFile" class="form-label">{{__('messages.AddonFile')}}</label>
                                                <span class="textRed">*</span>
                                            </div>
                                            <div class="col-sm-4">
                                                <input class="form-control" name="addon_file" type="file" id="zipInp" accept=".zip" required>
                                                <span class="textRed">{!! $errors->first('addon_file') !!}</span>
                                                <p id="fileName" class="mt-2 text-info"></p>
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
