@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 50px !important;">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <h6>{{__('messages.pageList')}}</h6>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <a href="{{route('page_add')}}" title="" class="module_button_header">
                                    <button type="button" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-plus-circle"></i> {{__('messages.createPage')}}
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
                                    <th>{{__('messages.name')}}</th>
                                    <th>{{__('messages.slug')}}</th>
                                    <th>{{__('messages.Plugin')}}</th>
                                    {{--<th scope="col text-center" class="sorting_disabled" rowspan="1" colspan="1" aria-label style="width: 24px;">
                                        <i class="fas fa-cog"></i>
                                    </th>--}}
                                </tr>
                                </thead>

                                @if(sizeof($scopes)>0)
                                    <tbody>
                                        @php
                                            $i=1;
                                            $currentPage = $scopes->currentPage();
                                            $perPage = $scopes->perPage();
                                            $serial = ($currentPage - 1) * $perPage + 1;
                                        @endphp
                                        @foreach($scopes as $scope)
                                            <tr>
                                                <td>{{$serial++}}</td>
                                                <td>{{$scope->name}}</td>
                                                <td>{{$scope->slug}}</td>
                                                <td>{{$scope->plugin_name}}</td>

                                                {{--<td>
                                                    <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                            <a title="Edit" class="btn btn-outline-primary btn-sm" href="{{route('page_edit',$scope->id)}}"><i class="fas fa-edit"></i></a>
                                                        <a title="Delete" onclick="return confirm('Are you sure?');" class="btn btn-outline-danger btn-sm" href="{{route('page_delete',$scope->id)}}"><i class="fas fa-trash"></i></a>
                                                    </div>
                                                </td>--}}
                                            </tr>
                                            @php $i++; @endphp
                                        @endforeach
                                    </tbody>
                                @endif
                            </table>
                            @if(isset($scopes) && count($scopes)>0)
                                <div class=" justify-content-right">
                                    {{ $scopes->links('layouts.pagination') }}
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
