@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 50px !important;">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <h6>{{__('messages.GlobalConfigList')}}</h6>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <a href="{{route('global_config_add', 'appbar')}}" title="" class="module_button_header">
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
                                    <th>Plugin Slug</th>
                                    <th>{{__('messages.mode')}}</th>
                                    <th>{{__('messages.name')}}</th>
                                    <th>{{__('messages.slug')}}</th>
                                    <th>{{__('messages.selectedColor')}}</th>
                                    <th>{{__('messages.unselectedColor')}}</th>
                                    <th>{{__('messages.backgroundColor')}}</th>
                                    <th scope="col text-center" class="sorting_disabled" rowspan="1" colspan="1" aria-label style="width: 24px;">
                                        <i class="fas fa-cog"></i>
                                    </th>
                                </tr>
                                </thead>

                                @if(sizeof($globalConfig)>0)
                                    <tbody>
                                        @php
                                            $i=1;
                                            $currentPage = $globalConfig->currentPage();
                                            $perPage = $globalConfig->perPage();
                                            $serial = ($currentPage - 1) * $perPage + 1;
                                        @endphp
                                        @foreach($globalConfig as $config)
                                            <tr>
                                                <td>{{$serial++}}</td>
                                                <td>{{$config->plugin_name}}</td>
                                                <td>{{$config->mode}}</td>
                                                <td>{{$config->name}}</td>
                                                <td>{{$config->slug}}</td>
                                                <td>{{$config->selected_color}}</td>
                                                <td>{{$config->unselected_color}}</td>
                                                <td>{{$config->background_color}}</td>

                                                <td>
                                                    <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                            <a title="Edit" class="btn btn-outline-primary btn-sm" href="{{route('global_config_edit',$config->id)}}"><i class="fas fa-edit"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @php $i++; @endphp
                                        @endforeach
                                    </tbody>
                                @endif
                            </table>
                            @if(isset($globalConfig) && count($globalConfig)>0)
                                <div class=" justify-content-right">
                                    {{ $globalConfig->links('layouts.pagination') }}
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('footer.scripts')
{{--    <script src="{{Module::asset('appfiy:js/employee.js')}}"></script>--}}
@endsection
