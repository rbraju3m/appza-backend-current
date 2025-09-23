@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 50px !important;">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{ __('messages.FreeTrial') }}</h6>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')

                        <!-- Search Form -->
                        <form method="GET" action="{{ route('free_trial_list') }}" id="search-form" class="mb-3">
                            <input type="hidden" name="tab" class="tab_search_field" value="{{ $activeTab }}">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Search..." value="{{ $search }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <a href="{{ route('free_trial_list', ['tab' => $activeTab]) }}"
                                       class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>

                        {{-- Tabs --}}
                        <ul class="nav nav-tabs" id="eventTabs" role="tablist">
                            @foreach($products as $slug => $name)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tabChange {{ $activeTab == $slug ? 'active' : '' }}"
                                            id="{{ $slug }}-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#tab-{{ $slug }}"
                                            type="button" role="tab"
                                            tab-name="{{ $slug }}"
                                            aria-controls="tab-{{ $slug }}"
                                            aria-selected="{{ $activeTab == $slug ? 'true' : 'false' }}">
                                        {{ $name }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content mt-3" id="eventTabsContent">
                            @foreach($products as $slug => $name)
                                <div class="tab-pane fade {{ $activeTab == $slug ? 'show active' : '' }}"
                                     id="tab-{{ $slug }}"
                                     role="tabpanel"
                                     aria-labelledby="{{ $slug }}-tab">

                                    <table class="table table-bordered text-center">
                                        <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Information</th>
                                            <th>SiteUrl</th>
                                            <th>Expiration Date</th>
                                            <th>Grace Period Date</th>
                                            <th>License Type</th>
                                            <th><i class="fas fa-cog"></i></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($freeTrials[$slug] as $freeTrial)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="text-start meta-info">
                                                    <div><strong>Date:</strong> {{ $freeTrial->created_at->timezone('Asia/Dhaka')->format('d-M-Y h:i:s A') }}</div>
                                                    <div><strong>Name:</strong> {{ $freeTrial->name }}</div>
                                                    <div><strong>Email:</strong> {{ $freeTrial->email }}</div>
                                                </td>
                                                <td>{{ $freeTrial->site_url }}</td>
                                                <td>{{ $freeTrial->expiration_date->timezone('Asia/Dhaka')->format('d-M-Y h:i:s A') }}</td>
                                                <td>{{ $freeTrial->grace_period_date->timezone('Asia/Dhaka')->format('d-M-Y h:i:s A') }}</td>
                                                <td>{{ $freeTrial->is_fluent_license_check == 0 ? "Free Trial" : "Premium" }}</td>
                                                <td>
                                                    @if(auth()->user()->user_type === 'DEVELOPER' && $freeTrial->is_fluent_license_check == 0)
                                                        <a class="btn btn-outline-danger btn-sm"
                                                           onclick="return confirm('Are you sure?')"
                                                           href="{{ route('free_trial_delete', $freeTrial->id) }}">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7">No free trial found</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>

                                    <div class="d-flex justify-content-end">
                                        {{ $freeTrials[$slug]->links('layouts.pagination') }}
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.tabChange');

            tabs.forEach(tab => {
                tab.addEventListener('shown.bs.tab', function () {
                    const tabName = this.getAttribute('tab-name');

                    // Update hidden input in search form
                    document.querySelector('.tab_search_field').value = tabName;

                    // Update URL without reloading
                    const url = new URL(window.location);
                    url.searchParams.set('tab', tabName);
                    window.history.replaceState({}, '', url);
                });
            });
        });
    </script>
@endsection
