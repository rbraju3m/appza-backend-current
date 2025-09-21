@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 50px !important;">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <h6>{{__('messages.LeadList')}}</h6>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                {{--<a href="{{route('setup_add')}}" title="" class="module_button_header">
                                    <button type="button" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-plus-circle"></i> {{__('messages.createSetup')}}
                                    </button>
                                </a>--}}
                            </div>
                        </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')

                        {{-- Tabs --}}
                        <ul class="nav nav-tabs" id="eventTabs" role="tablist">
                            @foreach($products as $slug => $name)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                            id="{{ $slug }}-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#tab-{{ $slug }}"
                                            type="button" role="tab"
                                            aria-controls="tab-{{ $slug }}"
                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        {{ $name }} {{-- show product_name --}}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content mt-3" id="eventTabsContent">
                            @foreach($products as $slug => $name)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                     id="tab-{{ $slug }}"
                                     role="tabpanel"
                                     aria-labelledby="{{ $slug }}-tab">

                                    <table class="table table-bordered text-center">
                                        <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Information</th>
                                            <th>SiteUrl</th>
                                            <th>Note</th>
                                            <th><i class="fas fa-cog"></i></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($leads[$slug] as $lead)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="text-start meta-info">
                                                    <div><strong>Date:</strong> {{ $lead->created_at->timezone('Asia/Dhaka')->format('d-M-Y h:i:s A') }}</div>
                                                    <div><strong>First Name:</strong> {{ $lead->first_name }}</div>
                                                    <div><strong>Last Name:</strong> {{ $lead->last_name }}</div>
                                                    <div><strong>Email:</strong> {{ $lead->email }}</div>
                                                </td>
                                                <td>{{ $lead->domain }}</td>
                                                <td>{{ $lead->note }}</td>
                                                <td>
                                                    @if(auth()->user()->user_type === 'DEVELOPER')
                                                        <a class="btn btn-outline-danger btn-sm"
                                                           onclick="return confirm('Are you sure?')"
                                                           href="{{ route('lead_delete', $lead->id) }}">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6">No leads found</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>

                                    {{-- pagination --}}
                                    {{ $leads[$slug]->links('layouts.pagination') }}
                                </div>
                            @endforeach
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('footer.scripts')
    <style>
        .meta-info div {
            margin-bottom: 3px;
            font-size: 14px;
        }
        .meta-info strong {
            color: #0d6efd; /* Bootstrap primary color */
            font-weight: 600;
            margin-right: 4px;
        }
    </style>
@endsection
