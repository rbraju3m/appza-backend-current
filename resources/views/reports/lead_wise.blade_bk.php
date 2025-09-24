@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <div class="card mb-4 shadow-sm">

                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>{{ __('Lead Wise Free Trial & Premium') }}</h6>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')

                        <!-- Search Form -->
                        <form method="GET" action="{{ route('report_lead_wise') }}" class="mb-3">
                            <input type="hidden" name="tab" value="{{ $activeTab }}">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Search..." value="{{ $search }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <a href="{{ route('report_lead_wise', ['tab' => $activeTab]) }}"
                                       class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>

                        <!-- Product Tabs -->
                        <ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
                            @foreach($products as $slug => $title)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == $slug ? 'active' : '' }}"
                                       href="{{ route('report_lead_wise', ['tab' => $slug, 'search' => $search]) }}">
                                        {{ $title->product_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <!-- Leads Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="">
                                <tr>
                                    <th>SL</th>
                                    <th>Lead Info</th>
                                    <th>Free Trial Info</th>
                                    <th>Premium Info</th>
                                </tr>
                                </thead>
                                {{--<tbody>
                                @forelse($leads as $lead)
                                    <tr>
                                        <td>{{ $loop->iteration + ($leads->currentPage()-1)*$leads->perPage() }}</td>
                                        <td>
                                            <strong>Name:</strong> {{ $lead->first_name }} {{ $lead->last_name }} <br>
                                            <strong>Email:</strong> {{ $lead->email }} <br>
                                            <strong>Domain:</strong> {{ $lead->domain }} <br>
                                            <strong>Created:</strong> {{ \Carbon\Carbon::parse($lead->created_at)->format('d-M-Y H:i') }}
                                        </td>
                                        <td>
                                            @if($lead->product_slug)
                                                <strong>Product:</strong> {{ $lead->product_title }} <br>
                                                <strong>Site:</strong> {{ $lead->site_url }} <br>
                                                <strong>Status:</strong>
                                                <span class="badge {{ $lead->status == 'valid' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($lead->status) }}
                                                </span> <br>
                                                <strong>Expiration:</strong> {{ \Carbon\Carbon::parse($lead->expiration_date)->format('d-M-Y') }}
                                            @else
                                                <span class="badge bg-warning">No Free Trial</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($lead->is_fluent_license_check == 1)
                                                <strong>License Key:</strong> {{ $lead->license_key ?? 'N/A' }} <br>
                                                <strong>Activated:</strong> {{ $lead->activations_count }}/{{ $lead->activation_limit }} <br>
                                                <span class="badge bg-success">Premium Active</span>
                                            @else
                                                <span class="badge bg-secondary">Not Upgraded</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No leads found</td>
                                    </tr>
                                @endforelse
                                </tbody>--}}


                                {{--<tbody>
                                @forelse($leads as $lead)
                                    <tr
                                        @if($lead->is_fluent_license_check == 1)
                                            class="table-success" --}}{{-- Premium --}}{{--
                                        @elseif($lead->product_slug)
                                            class="table-info" --}}{{-- Free Trial --}}{{--
                                        @endif
                                    >
                                        <td>{{ $loop->iteration + ($leads->currentPage()-1)*$leads->perPage() }}</td>

                                        <!-- Lead Info -->
                                        <td>
                                            <strong>Name:</strong> {{ $lead->first_name }} {{ $lead->last_name }} <br>
                                            <strong>Email:</strong> {{ $lead->email }} <br>
                                            <strong>Domain:</strong> {{ $lead->domain }} <br>
                                            <strong>Created:</strong> {{ \Carbon\Carbon::parse($lead->created_at)->format('d-M-Y H:i') }}
                                        </td>

                                        <!-- Free Trial Info -->
                                        <td>
                                            @if($lead->product_slug)
                                                <strong>Product:</strong> {{ $lead->product_title }} <br>
                                                <strong>Site:</strong> {{ $lead->site_url }} <br>
                                                <strong>Status:</strong>
                                                <span class="badge {{ $lead->status == 'valid' ? 'bg-success' : 'bg-danger' }}">
                    {{ ucfirst($lead->status) }}
                </span> <br>
                                                <strong>Expiration:</strong> {{ \Carbon\Carbon::parse($lead->expiration_date)->format('d-M-Y') }}
                                            @else
                                                <span class="badge bg-warning">No Free Trial</span>
                                            @endif
                                        </td>

                                        <!-- Premium Info -->
                                        <td>
                                            @if($lead->is_fluent_license_check == 1)
                                                <strong>License Key:</strong> {{ $lead->license_key ?? 'N/A' }} <br>
                                                <strong>Activated:</strong> {{ $lead->activations_count }}/{{ $lead->activation_limit }} <br>
                                                <span class="badge bg-success">Premium Active</span>
                                            @else
                                                <span class="badge bg-secondary">Not Upgraded</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No leads found</td>
                                    </tr>
                                @endforelse
                                </tbody>--}}

                                <tbody>
                                @forelse($leads as $lead)
                                    <tr>
                                        <td>{{ $loop->iteration + ($leads->currentPage()-1)*$leads->perPage() }}</td>

                                        <td>
                                            <div class="lead-status-progress">
                                                <div class="status-step active">
                                                    <div class="status-circle bg-success"><i class="fas fa-check"></i></div>
                                                    <div class="status-label">Account Created</div>
                                                </div>

                                                @if($lead->product_slug)
                                                    <div class="status-line bg-success"></div>
                                                @else
                                                    <div class="status-line bg-secondary"></div>
                                                @endif

                                                <div class="status-step @if($lead->product_slug) active @endif">
                                                    <div class="status-circle @if($lead->product_slug) bg-success @else bg-secondary @endif">
                                                        <i class="fas fa-envelope"></i>
                                                    </div>
                                                    <div class="status-label">Free Trial</div>
                                                </div>

                                                @if($lead->is_fluent_license_check == 1)
                                                    <div class="status-line bg-success"></div>
                                                @else
                                                    <div class="status-line bg-secondary"></div>
                                                @endif

                                                <div class="status-step @if($lead->is_fluent_license_check == 1) active @endif">
                                                    <div class="status-circle @if($lead->is_fluent_license_check == 1) bg-success @else bg-secondary @endif">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </div>
                                                    <div class="status-label">Premium</div>
                                                </div>
                                            </div>

                                            <hr class="my-2">

                                            <strong>Name:</strong> {{ $lead->first_name }} {{ $lead->last_name }} <br>
                                            <strong>Email:</strong> {{ $lead->email }} <br>
                                            <strong>Domain:</strong> {{ $lead->domain }} <br>
                                            <strong>Created:</strong> {{ \Carbon\Carbon::parse($lead->created_at)->format('d-M-Y H:i') }}
                                        </td>

                                        <td>
                                            @if($lead->product_slug)
                                                <strong>Product:</strong> {{ $lead->product_title }} <br>
                                                <strong>Site:</strong> {{ $lead->site_url }} <br>
                                                <strong>Status:</strong>
                                                <span class="badge {{ $lead->status == 'valid' ? 'bg-success' : 'bg-danger' }}">
                        {{ ucfirst($lead->status) }}
                    </span> <br>
                                                <strong>Expiration:</strong> {{ \Carbon\Carbon::parse($lead->expiration_date)->format('d-M-Y') }}
                                            @else
                                                <span class="badge bg-warning">No Free Trial</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($lead->is_fluent_license_check == 1)
                                                <strong>License Key:</strong> {{ $lead->license_key ?? 'N/A' }} <br>
                                                <strong>Activated:</strong> {{ $lead->activations_count }}/{{ $lead->activation_limit }} <br>
                                                <span class="badge bg-success">Premium Active</span>
                                            @else
                                                <span class="badge bg-secondary">Not Upgraded</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No leads found</td>
                                    </tr>
                                @endforelse
                                </tbody>

                            </table>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-end">
                                {{ $leads->appends(['tab' => $activeTab, 'search' => $search])->links('layouts.pagination') }}
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection


@section('footer.scripts')
    <style>
        .lead-status-progress {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .status-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .status-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .status-label {
            font-size: 10px;
            margin-top: 5px;
            color: #6c757d;
        }

        .status-line {
            height: 3px;
            flex-grow: 1;
            margin: 0 10px;
            position: relative;
            top: -5px; /* Adjust to align with circles */
            z-index: 0;
        }

        /* Bootstrap colors for styling */
        .bg-success {
            background-color: #28a745 !important;
        }

        .bg-secondary {
            background-color: #6c757d !important;
        }
    </style>

@endsection
