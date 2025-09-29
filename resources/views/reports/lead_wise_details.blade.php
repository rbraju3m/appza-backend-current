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

                        {{-- Search Form --}}
                        <form method="GET" action="{{ route('report_lead_wise_details') }}" class="mb-3">
                            <input type="hidden" name="tab" value="{{ $activeTab }}">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Search..." value="{{ $search }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <a href="{{ route('report_lead_wise_details', ['tab' => $activeTab]) }}"
                                       class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>

                        {{-- Export Buttons --}}
                        {{--<div class="d-flex justify-content-end mb-3 gap-2">
                            <button id="downloadPng" class="btn btn-sm btn-outline-primary">
                                📸 Export PNG
                            </button>
                            <button id="downloadXlsx" class="btn btn-sm btn-outline-success">
                                📑 Export XLSX
                            </button>
                        </div>--}}

                        {{-- Product Tabs --}}
                        <ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
                            @foreach($products as $slug => $title)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == $slug ? 'active' : '' }}"
                                       href="{{ route('report_lead_wise_details', ['tab' => $slug, 'search' => $search]) }}">
                                        {{ $title->product_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Leads Table --}}
                        <div class="table-responsive png-body" id="reportTable">
                            <table class="table table-bordered align-middle">
                                <thead>
                                <tr>
                                    <th>SL</th>
                                    <th width="25%">Lead</th>
                                    <th class="text-center" width="20%">Free Trial</th>
                                    <th class="text-center" width="20%">Premium</th>
                                    <th width="35%">Progress</th>
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
                                            <strong>Created:</strong> {{ \Carbon\Carbon::parse($lead->created_at)->format('d-M-Y h:i A') }}
                                        </td>

                                        {{-- Free Trial Info --}}
                                        <td class="text-center">
                                            @if($lead->product_slug)
                                                @php
                                                    $now = \Carbon\Carbon::now();
                                                    $expiration = \Carbon\Carbon::parse($lead->expiration_date);
                                                    $grace = \Carbon\Carbon::parse($lead->grace_period_date);

                                                    if ($now->lt($expiration)) {
                                                        // Trial still valid
                                                        $statusClass = 'bg-success';
                                                        $statusText = 'Active';
                                                    } elseif ($now->lt($grace)) {
                                                        // Expired but still in grace period
                                                        $statusClass = 'bg-warning';
                                                        $statusText = 'Grace Period';
                                                    } else {
                                                        // Expired completely
                                                        $statusClass = 'bg-danger';
                                                        $statusText = 'Expired';
                                                    }
                                                @endphp

                                                <span class="badge {{ $statusClass }}">
            {{ $statusText }}
        </span><br>

                                                <strong>Expires:</strong> {{ $expiration->format('d-M-Y h:i A') }}<br>
                                                <strong>Grace End:</strong> {{ $grace->format('d-M-Y h:i A') }}
                                            @else
                                                <span class="badge bg-secondary">No Free Trial</span>
                                            @endif
                                        </td>


                                        {{-- Premium Info --}}
                                        <td class="text-center">
                                            @if($lead->is_fluent_license_check == 1)
                                                {{--<strong>License Key:</strong>--}} {{ $lead->p_license_key ?? 'N/A' }} <br>
                                                <strong>Activated: </strong> {{ $lead->p_activations_count }} /{{ $lead->p_activation_limit == 0 ? ' Unlimited' : $lead->p_activation_limit }} <br>
                                                <span class="badge bg-success">Premium Active</span>
                                            @else
                                                <span class="badge bg-secondary">Not Upgraded</span>
                                            @endif
                                        </td>

                                        {{-- Progress Tracker --}}
                                        <td class="text-center">
                                            <div class="progress-flow d-flex align-items-center justify-content-center">

                                                {{-- Lead --}}
                                                <div class="status-step active" data-bs-toggle="tooltip" data-bs-placement="top"
                                                     title="Lead Created: {{ \Carbon\Carbon::parse($lead->created_at)->format('d M Y') }}">
                                                    <div class="status-circle lead-step">
                                                        <i class="fas fa-user-plus"></i>
                                                    </div>
                                                    <div class="status-title">Lead</div>
                                                    <p>{{\Carbon\Carbon::parse($lead->created_at)->format('d M Y') }}</p>
                                                </div>

                                                {{-- Line to Trial --}}
                                                <div class="status-line {{ $lead->product_slug ? 'line-active' : 'line-inactive' }}"></div>

                                                {{-- Free Trial --}}
                                                <div class="status-step {{ $lead->product_slug ? 'active' : '' }}"
                                                     data-bs-toggle="tooltip" data-bs-placement="top"
                                                     title="{{ $lead->product_slug ? 'Trial Expiry: ' . \Carbon\Carbon::parse($lead->expiration_date)->format('d M Y') : 'No Trial' }}">
                                                    <div class="status-circle trial-step {{ $lead->product_slug ? 'active' : '' }}">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </div>
                                                    <div class="status-title">Trial</div>
                                                    <p>{{$lead->product_slug ? \Carbon\Carbon::parse($lead->expiration_date)->format('d M Y') : null }}</p>
                                                </div>

                                                {{-- Line to Premium --}}
                                                <div class="status-line {{ $lead->is_fluent_license_check == 1 ? 'line-active' : 'line-inactive' }}"></div>

                                                {{-- Premium --}}
                                                <div class="status-step {{ $lead->is_fluent_license_check == 1 ? 'active' : '' }}"
                                                     data-bs-toggle="tooltip" data-bs-placement="top"
                                                     title="{{ $lead->is_fluent_license_check == 1 ? 'Premium Expiry: ' . ($lead->p_expiration_date == 'lifetime' ? 'Lifetime' : \Carbon\Carbon::parse($lead->p_expiration_date)->format('d M Y')) : 'Not Upgraded' }}">
                                                    <div class="status-circle premium-step {{ $lead->is_fluent_license_check == 1 ? 'active' : '' }}">
                                                        <i class="fas fa-lock-open"></i>
                                                    </div>
                                                    <div class="status-title">Premium</div>
                                                    <p>{{$lead->is_fluent_license_check == 1?($lead->p_expiration_date == 'lifetime' ? 'Lifetime' : \Carbon\Carbon::parse($lead->p_expiration_date)->format('d M Y')):null }}</p>
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
        /* Row backgrounds */
        .bg-lead { background-color: #f8f9fa; }
        .bg-free-trial { background-color: #e2f4ff; }
        .bg-premium { background-color: #d4edda; }

        /* Progress flow */
        .progress-flow { gap: 20px; }

        .status-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .status-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        .status-circle:hover { transform: scale(1.15); }

        /* Step colors */
        .lead-step { background: linear-gradient(135deg,#007bff,#0056b3); }
        .trial-step.active { background: linear-gradient(135deg,#17a2b8,#138496); }
        .premium-step.active { background: linear-gradient(135deg,#28a745,#1e7e34); }

        .status-title {
            margin-top: 6px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        /* Lines */
        .status-line {
            flex-grow: 1;
            height: 6px;
            border-radius: 4px;
        }
        .line-active {
            background: linear-gradient(90deg,#17a2b8,#28a745);
            box-shadow: 0 0 6px rgba(23,162,184,0.7);
        }
        .line-inactive { background: #d6d6d6; }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        // Enable bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // XLSX download
        document.getElementById("downloadXlsx").addEventListener("click", function () {
            let wb = XLSX.utils.book_new();
            let ws = XLSX.utils.table_to_sheet(document.getElementById("reportTable"), { origin: "A3" });

            // Report Name
            let reportName = "Lead wise details report";
            XLSX.utils.sheet_add_aoa(ws, [[reportName]], { origin: "A1" });

            // Merge A1:E1 (5 columns) and make bold, centered
            if(!ws['!merges']) ws['!merges'] = [];
            ws['!merges'].push({ s: {r:0,c:0}, e: {r:0,c:4} });
            ws['A1'].s = { font: { bold: true, sz: 14 }, alignment: { horizontal: "center" } };

            // Make headers bold & centered
            const headers = ['A3','B3','C3','D3','E3'];
            headers.forEach(h => {
                if(!ws[h]) return;
                ws[h].s = { font: { bold: true }, alignment: { horizontal: "center", vertical: "center", wrapText: true } };
            });

            // Auto column width based on content length
            const cols = ['A','B','C','D','E'];
            ws['!cols'] = cols.map((col, i) => {
                let maxLength = 10; // minimum width
                for (let row = 4; row <= ws['!ref'].split(':')[1].replace(/\D+/g,''); row++) {
                    const cell = ws[`${col}${row}`];
                    if(cell && cell.v) {
                        const length = cell.v.toString().length;
                        if(length > maxLength) maxLength = length;
                    }
                }
                return { wch: maxLength + 5 }; // add some padding
            });

            wb.Props = {
                Title: reportName,
                Author: "YourApp",
                CreatedDate: new Date()
            };

            XLSX.utils.book_append_sheet(wb, ws, "Report");
            XLSX.writeFile(wb, "lead-details.xlsx");
        });


        // PNG download
        document.getElementById("downloadPng").addEventListener("click", function () {
            html2canvas(document.querySelector(".png-body")).then(canvas => {
                let link = document.createElement("a");
                link.download = "lead-details.png";
                link.href = canvas.toDataURL("image/png");
                link.click();
            });
        });
    </script>
@endsection

