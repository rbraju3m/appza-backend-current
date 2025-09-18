@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 50px !important;">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <h6>{{__('messages.Matrix')}}</h6>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <a href="{{route('license_logic_add')}}" title="" class="module_button_header">
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

                        {{-- Bootstrap Tabs --}}
                        <ul class="nav nav-tabs" id="eventTabs" role="tablist">
                            @foreach($events as $event)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                            id="{{ $event }}-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#tab-{{ $event }}"
                                            type="button" role="tab"
                                            aria-controls="tab-{{ $event }}"
                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        {{ ucfirst($event) }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content mt-3">
                            @foreach($events as $event)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $event }}" role="tabpanel" aria-labelledby="{{ $event }}-tab">

                                    <table class="table table-bordered datatable table-responsive mainTable text-center">
                                        <thead class="thead-dark">
                                        <tr>
                                            <th>SL</th>
                                            <th>Name</th>
                                            <th>Slug</th>
                                            <th>Event</th>
                                            @if($event === 'expiration' || $event === 'grace')
                                                <th>Direction</th>
                                                <th>From days</th>
                                                <th>To days</th>
                                            @endif
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $serial = ($licenseLogicsByEvent[$event]->currentPage() - 1) * $licenseLogicsByEvent[$event]->perPage() + 1;
                                        @endphp
                                        @foreach($licenseLogicsByEvent[$event] as $logics)
                                            <tr>
                                                <td>{{ $serial++ }}</td>
                                                <td>{{ $logics->name }}</td>
                                                <td>{{ $logics->slug }}</td>
                                                <td>{{ $logics->event }}</td>
                                                @if($event === 'expiration' || $event === 'grace')
                                                    <td>{{ $logics->direction }}</td>
                                                    <td>{{ $logics->from_days }}</td>
                                                    <td>{{ $logics->to_days }}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    {{-- Pagination --}}
                                    {{ $licenseLogicsByEvent[$event]->links('layouts.pagination') }}

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
{{--    <script src="{{Module::asset('appfiy:js/employee.js')}}"></script>--}}
@endsection
