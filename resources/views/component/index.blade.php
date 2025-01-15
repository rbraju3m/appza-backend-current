
@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 50px !important;">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.componentList')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">
                                    <a href="{{route('component_add')}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus-circle"></i> {{__('messages.createNew')}}
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')

                        <!-- Search Form -->
                        <form method="GET" action="{{ route('component_list') }}" id="search-form" class="mb-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" name="search" class="form-control" placeholder="{{__('messages.searchPlaceholder')}}" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{__('messages.search')}}
                                    </button>
                                    <a href="{{ route('component_list') }}" class="btn btn-secondary">
                                        {{__('messages.clear')}}
                                    </a>
                                </div>
                            </div>
                        </form>

                        <table id="leave_settings" class="table table-bordered datatable table-responsive mainTable text-center">
                            <thead class="thead-dark">
                            <tr>
                                <th>{{__('messages.SL')}}</th>
                                <th>Plugin Name</th>
                                <th>Component Name</th>
                                <th>{{__('messages.slug')}}</th>
                                <th>{{__('messages.label')}}</th>
                                <th>{{__('messages.scope')}}</th>
                                <th scope="col text-center" class="sorting_disabled" rowspan="1" colspan="1" aria-label style="width: 24px;">
                                    <i class="fas fa-cog"></i>
                                </th>
                            </tr>
                            </thead>
                            @php
                                $currentPage = $components->currentPage();
                                $perPage = $components->perPage();
                                $serial = ($currentPage - 1) * $perPage + 1;
                            @endphp

                            @if(isset($components) && count($components)>0)
                                <tbody>
                                @foreach($components as $component)
                                    <tr>
                                        <td>{{$serial++}}</td>
                                        <td>{{$component->plugin_name}}</td>
                                        <td>{{$component->name}}</td>
                                        <td>{{$component->slug}}</td>
                                        <td>{{$component->label}}</td>
                                        <td>{{$component->scope ? implode(', ', json_decode($component->scope)) : null}}</td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                <a title="Edit" class="btn btn-outline-primary btn-sm" href="{{ route('component_edit', $component->id) }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a title="Delete" onclick="return confirm('Are you sure?');" class="btn btn-outline-danger btn-sm" href="{{ route('component_delete', $component->id) }}">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            @endif
                        </table>
                        @if(isset($components) && count($components) > 0)
                            <div class="justify-content-right">
                                {{ $components->appends(request()->query())->links('layouts.pagination') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('CustomStyle')

@endpush

@section('footer.scripts')
@endsection
