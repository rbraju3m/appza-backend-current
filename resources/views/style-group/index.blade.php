@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 50px !important;">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.style-group')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">
                                    <a href="{{route('style_group_create')}}" title="" class="module_button_header">
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
                        <form method="post" role="form" id="search-form">
                            <table id="leave_settings"
                                   class="table table-bordered datatable table-responsive mainTable text-center">

                                <thead class="thead-dark">
                                <tr>
                                    <th>{{__('messages.SL')}}</th>
                                    <th>Plugin Name</th>
                                    <th>{{__('messages.name')}}</th>
                                    <th>{{__('messages.slug')}}</th>
                                    <th>{{__('messages.totalProperties')}}</th>
                                    <th>{{__('messages.Properties')}}</th>
                                    <th scope="col text-center" class="sorting_disabled" rowspan="1" colspan="1"
                                        aria-label style="width: 24px;">
                                        <i class="fas fa-cog"></i>
                                    </th>
                                </tr>
                                </thead>

                                @if(count($styleGroups)>0)
                                    <tbody>
                                    @php
                                        $i=1;
                                        $currentPage = $styleGroups->currentPage();
                                        $perPage = $styleGroups->perPage();
                                        $serial = ($currentPage - 1) * $perPage + 1;
                                    @endphp
                                    @foreach($styleGroups as $styleGroup)

                                        <tr>
                                            <td>{{$serial++}}</td>
                                            <td>
                                                @if (!empty($styleGroup->plugin_slug) && is_array($styleGroup->plugin_slug))
                                                    <ul style="text-align: left; font-weight: bold; margin: 0; padding-left: 20px;">
                                                        @foreach ($styleGroup->plugin_slug as $plugin)
                                                            <li style="margin: 0;">{{ $plugin }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p style="color: darkred; font-style: italic;">No plugins assigned.</p>
                                                @endif
                                            </td>

                                            <td>{{$styleGroup->name}}</td>
                                            <td>{{$styleGroup->slug}}</td>
                                            <td>{{count($styleGroup->groupProperties->toArray())}}</td>
                                            <td>
                                                    <?php echo \App\Models\StyleGroup::getPropertiesNameArray($styleGroup->id); ?>
                                            </td>

                                            <td>
                                                <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                        <a title="Edit" class="btn btn-outline-primary btn-sm" href="{{route('style_group_edit',$styleGroup->id)}}"><i class="fas fa-edit"></i></a>
                                                    <a title="Assign Properties" class="btn btn-outline-danger btn-sm" href="{{route('style_group_assign_properties',$styleGroup->id)}}"><i class="fas fa-font"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        @php $i++; @endphp
                                    @endforeach
                                    </tbody>
                                @endif
                            </table>

                            @if(count($styleGroups)>0)
                                <div class=" justify-content-right">
                                    {{ $styleGroups->links('layouts.pagination') }}
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
@endsection
