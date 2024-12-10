@extends('layouts.app')

@section('body')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.userList')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">
                                    <a href="{{route('user_add', app()->getLocale())}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus-circle"></i> {{__('messages.Add Button')}}
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')
                        @if(isset($users) && !empty($users))
                        <table style="width: 100%!important;" id="leave_settings" class="table table-bordered datatable" id="table_id">

                            <thead class="thead-dark">
                            <tr>
                                <th>{{__('messages.SL')}}</th>
                                <th>{{__('messages.Name')}}</th>
                                <th>{{__('messages.Email')}}</th>
                                <th>{{__('messages.role')}}</th>
                                <th class="actionButton">
                                    <i class="fas fa-cog"></i>
                                </th>
                            </tr>
                            </thead>

                            <tbody>

                            @php $i = 1; @endphp
                            @foreach ($users as $key => $user)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->getRoleNames() }}</td>
                                    <td>
                                        <a href="{{route('user_edit',[app()->getLocale(),$user->id])}}" title="Edit User" class="text-primary"><i class="fas fa-pencil-alt"></i></a>
                                        <a href="{{route('user_delete',[app()->getLocale(),$user->id])}}" title="Permanent Delete" onclick="return confirm('Are you sure to Permanent Delete?')" class="text-danger"><i class="fas fa-trash-alt"></i></a>
{{--                                        <a href="{{route('role_delete',[app()->getLocale(),$role->id])}}" title="Permanent Delete" onclick="return confirm('Are you sure to Permanent Delete?')" class="text-danger"><i class="fas fa-trash-alt"></i></a>--}}
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endforeach
                            </tbody>
                        </table>
                        <div class=" justify-content-right paginationIndex">
                            {{ $users->appends(request()->query())->links('layouts.pagination') }}
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('footer.scripts')
    @push('CustomStyle')
        <style>
            .actionButton{
                text-align: center;
                width: 5%;
            }
            .paginationIndex{
                margin-top: 20px;
                text-align: end;
                display: inline;
            }
        </style>
    @endpush

@endsection
