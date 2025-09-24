{{--
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

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="">
                                <tr>
                                    <th>SL</th>
                                    <th>Lead Info</th>
                                    <th class="text-center">Free Trial Status</th>
                                    <th class="text-center">Premium Status</th>
                                    <th>Progress</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($leads as $lead)
                                    <tr
                                        @if($lead->is_fluent_license_check == 1)
                                            class="table-success" --}}
{{-- Premium --}}{{--

                                        @elseif($lead->product_slug)
                                            class="table-info" --}}
{{-- Free Trial --}}{{--

                                        @endif
                                    >
                                        <td>{{ $loop->iteration + ($leads->currentPage()-1)*$leads->perPage() }}</td>
                                        <td>
                                            <strong>Name:</strong> {{ $lead->first_name }} {{ $lead->last_name }} <br>
                                            <strong>Email:</strong> {{ $lead->email }} <br>
                                            <strong>Domain:</strong> {{ $lead->domain }} <br>
                                        </td>

                                        <td class="text-center">
                                            @if($lead->product_slug)
                                                <strong>Status:</strong>
                                                <span class="badge {{ $lead->status == 'valid' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($lead->status) }}
                                                </span><br>
                                                <strong>Created:</strong> {{ \Carbon\Carbon::parse($lead->created_at)->format('d-M-Y h:i A') }}<br>
                                                <strong>Expires:</strong> {{ \Carbon\Carbon::parse($lead->expiration_date)->format('d-M-Y h:i A') }}<br>
                                                <strong>Grace End:</strong> {{ \Carbon\Carbon::parse($lead->grace_period_date)->format('d-M-Y h:i A') }}
                                            @else
                                                <span class="badge bg-warning">No Free Trial</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($lead->is_fluent_license_check == 1)
                                                <strong>License Key:</strong> {{ $lead->license_key ?? 'N/A' }} <br>
                                                <strong>Activated:</strong> {{ $lead->activations_count }}/{{ $lead->activation_limit }} <br>
                                                <span class="badge bg-success">Premium Active</span>
                                            @else
                                                <span class="badge bg-secondary">Not Upgraded</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="status-step active">
                                                    <div class="status-circle bg-success">
                                                        <i class="fas fa-user-plus"></i>
                                                    </div>
                                                </div>

                                                <div class="status-line {{ $lead->product_slug ? 'bg-success' : 'bg-secondary' }}"></div>

                                                <div class="status-step {{ $lead->product_slug ? 'active' : '' }}">
                                                    <div class="status-circle {{ $lead->product_slug ? 'bg-success' : 'bg-secondary' }}">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </div>
                                                </div>

                                                <div class="status-line {{ $lead->is_fluent_license_check == 1 ? 'bg-success' : 'bg-secondary' }}"></div>

                                                <div class="status-step {{ $lead->is_fluent_license_check == 1 ? 'active' : '' }}">
                                                    <div class="status-circle {{ $lead->is_fluent_license_check == 1 ? 'bg-success' : 'bg-secondary' }}">
                                                        <i class="fas fa-lock-open"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No leads found</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
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
        .status-step {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .status-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .status-line {
            height: 4px;
            flex-grow: 1;
            margin: 0 5px;
            border-radius: 2px;
        }

        .bg-success {
            background-color: #28a745 !important;
        }

        .bg-secondary {
            background-color: #6c757d !important;
        }
    </style>
@endsection
--}}


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

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                <tr>
                                    <th>SL</th>
                                    <th width="25%">Lead</th>
                                    <th class="text-center"  width="20%">Free Trial</th>
                                    <th class="text-center"  width="20%">Premium</th>
                                    <th  width="35%">Progress</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($leads as $lead)
                                    <tr class="
                                        @if($lead->is_fluent_license_check == 1)
                                            bg-premium
                                        @elseif($lead->product_slug)
                                            bg-free-trial
                                        @else
                                            bg-lead
                                        @endif
                                    ">
                                        <td>{{ $loop->iteration + ($leads->currentPage()-1)*$leads->perPage() }}</td>
                                        <td>
                                            <strong>Name:</strong> {{ $lead->first_name }} {{ $lead->last_name }} <br>
                                            <strong>Email:</strong> {{ $lead->email }} <br>
                                            <strong>Domain:</strong> {{ $lead->domain }} <br>
                                        </td>

                                        <td class="text-center">
                                            @if($lead->product_slug)
                                                <strong>Status:</strong>
                                                <span class="badge {{ $lead->status == 'valid' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($lead->status) }}
                                                </span><br>
                                                <strong>Created:</strong> {{ \Carbon\Carbon::parse($lead->created_at)->format('d-M-Y h:i A') }}<br>
                                                <strong>Expires:</strong> {{ \Carbon\Carbon::parse($lead->expiration_date)->format('d-M-Y h:i A') }}<br>
                                                <strong>Grace End:</strong> {{ \Carbon\Carbon::parse($lead->grace_period_date)->format('d-M-Y h:i A') }}
                                            @else
                                                <span class="badge bg-warning">No Free Trial</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($lead->is_fluent_license_check == 1)
                                                <strong>License Key:</strong> {{ $lead->license_key ?? 'N/A' }} <br>
                                                <strong>Activated:</strong> {{ $lead->activations_count }}/{{ $lead->activation_limit }} <br>
                                                <span class="badge bg-success">Premium Active</span>
                                            @else
                                                <span class="badge bg-secondary">Not Upgraded</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="status-step active">
                                                    <div class="status-circle bg-success">
                                                        <i class="fas fa-user-plus"></i>
                                                    </div>
                                                    <div class="status-info">
                                                        <div class="status-title">Lead</div>
                                                        <div class="status-date">{{ \Carbon\Carbon::parse($lead->created_at)->format('d-M-Y') }}</div>
                                                    </div>
                                                </div>

                                                <div class="status-line {{ $lead->product_slug ? 'bg-success' : 'bg-secondary' }}"></div>

                                                <div class="status-step {{ $lead->product_slug ? 'active' : '' }}">
                                                    <div class="status-circle {{ $lead->product_slug ? 'bg-success' : 'bg-secondary' }}">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </div>
                                                    <div class="status-info">
                                                        <div class="status-title">Trial</div>
                                                        @if($lead->product_slug)
                                                            <div class="status-date">{{ \Carbon\Carbon::parse($lead->created_at)->format('d-M-Y') }}</div>
                                                        @else
                                                            <div class="status-date">N/A</div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="status-line {{ $lead->is_fluent_license_check == 1 ? 'bg-success' : 'bg-secondary' }}"></div>

                                                <div class="status-step {{ $lead->is_fluent_license_check == 1 ? 'active' : '' }}">
                                                    <div class="status-circle {{ $lead->is_fluent_license_check == 1 ? 'bg-success' : 'bg-secondary' }}">
                                                        <i class="fas fa-lock-open"></i>
                                                    </div>
                                                    <div class="status-info">
                                                        <div class="status-title">Premium</div>
                                                        @if($lead->is_fluent_license_check == 1)
                                                            <div class="status-date">{{ \Carbon\Carbon::parse($lead->updated_at)->format('d-M-Y') }}</div>
                                                        @else
                                                            <div class="status-date">N/A</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No leads found</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
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
        /* New background colors for the rows */
        .bg-lead {
            background-color: #f8f9fa; /* Light gray for new leads */
        }
        .bg-free-trial {
            background-color: #e2f4ff; /* Light blue for free trials */
        }
        .bg-premium {
            background-color: #d4edda; /* Light green for premium */
        }

        /* Progress column styling */
        .status-step {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .status-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .status-line {
            height: 4px;
            flex-grow: 1;
            margin: 0 5px;
            border-radius: 2px;
        }

        .status-info {
            margin-top: 5px;
            font-size: 0.7rem;
            line-height: 1.2;
        }

        .status-title {
            font-weight: bold;
        }

        .status-date {
            color: #6c757d;
        }

        /* Bootstrap colors for styling */
        .bg-success {
            background-color: #28a745 !important;
        }

        .bg-secondary {
            background-color: #6c757d !important;
        }

        .bg-warning {
            background-color: #ffc107 !important;
        }
    </style>
@endsection
