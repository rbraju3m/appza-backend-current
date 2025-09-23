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

                        <ul class="nav nav-tabs" id="eventTabs" role="tablist">
                            @foreach($events as $event)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == $event ? 'active' : '' }}"
                                       href="{{ route('license_logic_list', ['tab' => $event]) }}">
                                        {{ ucfirst($event) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content mt-3">
                            @foreach($events as $event)
                                <div class="tab-pane fade {{ $activeTab == $event ? 'show active' : '' }}">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>SL</th><th>Name</th><th>Slug</th><th>Event</th>
                                            @if($event === 'expiration' || $event === 'grace')
                                                <th>Direction</th><th>From</th><th>To</th>
                                            @endif
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $serial = ($licenseLogicsByEvent[$event]->currentPage() - 1) * $licenseLogicsByEvent[$event]->perPage() + 1;
                                        @endphp
                                        @foreach($licenseLogicsByEvent[$event] as $logic)
                                            <tr>
                                                <td>{{ $serial++ }}</td>
                                                <td>{{ $logic->name }}</td>
                                                <td>{{ $logic->slug }}</td>
                                                <td>{{ $logic->event }}</td>
                                                @if($event === 'expiration' || $event === 'grace')
                                                    <td>{{ $logic->direction }}</td>
                                                    <td>{{ $logic->from_days }}</td>
                                                    <td>{{ $logic->to_days }}</td>
                                                @endif
                                                <td><a href="{{ route('license_logic_edit', $logic->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    {{-- Pagination --}}
                                    <div class="d-flex justify-content-end">
                                        {{ $licenseLogicsByEvent[$event]->withQueryString()->links('layouts.pagination') }}
                                    </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = '{{ $activeTab }}';
            if(activeTab) {
                const tabEl = document.getElementById(`tab-${activeTab}-tab`);
                if(tabEl) new bootstrap.Tab(tabEl).show();
            }
        });
    </script>
@endsection

