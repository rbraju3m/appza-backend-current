
@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row">
            <!-- Report Card -->
            <div class="col-lg-12">
                <div class="card shadow border-0 rounded-3">
                    <div class="card-header bg-gradient bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">📊 Product Wise Report</h5>
                    </div>

                    <div class="card-body">
                        {{-- Product Tabs --}}
                        <ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
                            @foreach($products as $slug => $title)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == $slug ? 'active' : '' }}"
                                       href="{{ route('report_total_overview_table', [
                                        'tab' => $slug,
                                        'type' => $type,
                                        'search' => $search
                                   ]) }}">
                                        {{ $title->product_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Filter Form --}}
                        <form method="GET" action="{{ route('report_total_overview_table') }}" class="row g-2 mb-4">
                            <input type="hidden" name="tab" value="{{ $activeTab }}">

                            <div class="col-md-5">
                                @if($type == 'daily')
                                    <input type="date" name="search" value="{{ $search }}" class="form-control form-control-sm">
                                @elseif($type == 'monthly')
                                    <input type="month" name="search" value="{{ $search }}" class="form-control form-control-sm">
                                @elseif($type == 'yearly')
                                    <input type="number" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Enter year">
                                @endif
                            </div>

                            <div class="col-md-5">
                                <select name="type" onchange="this.form.submit()" class="form-select form-select-sm">
                                    @foreach($reportTypes as $key => $label)
                                        <option value="{{ $key }}" {{ $type == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-outline-dark w-100">
                                    🔍 Search
                                </button>
                            </div>
                        </form>

                        {{-- Export Buttons --}}
                        <div class="d-flex justify-content-end mb-3 gap-2">
                            <button id="downloadPng" class="btn btn-sm btn-outline-primary">
                                📸 Export PNG
                            </button>
                            <button id="downloadXlsx" class="btn btn-sm btn-outline-success">
                                📑 Export XLSX
                            </button>
                        </div>

                        {{-- Report Section --}}
                        <div class="png-body">
                            <h5 class="fw-bold text-center mb-4 border-bottom pb-2">
                                Overview : {{ ucfirst($type) }} - {{ $products[$activeTab]->product_name ?? 'All Products' }} ({{ $search }})
                            </h5>

                            <div class="table-responsive">
                                <table id="reportTable" class="table table-striped table-hover align-middle text-center">
                                    <thead class="table-dark sticky-top">
                                    <tr>
                                        <th>Period</th>
                                        <th>Leads</th>
                                        <th>Free Trials</th>
                                        <th>Premium</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $totalLeads = 0;
                                        $totalFree = 0;
                                        $totalPremium = 0;
                                    @endphp
                                    @forelse($reportData as $row)
                                        @php
                                            $totalLeads += $row['leads'];
                                            $totalFree += $row['free_trials'];
                                            $totalPremium += $row['premium'];
                                        @endphp
                                        <tr>
                                            <td>{{ $row['period'] }}</td>
                                            <td>{{ number_format($row['leads']) }}</td>
                                            <td>{{ number_format($row['free_trials']) }}</td>
                                            <td>{{ number_format($row['premium']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No data available</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                    <tfoot class="fw-bold bg-light">
                                    <tr>
                                        <td>Total</td>
                                        <td>{{ number_format($totalLeads) }}</td>
                                        <td>{{ number_format($totalFree) }}</td>
                                        <td>{{ number_format($totalPremium) }}</td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('footer.scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        document.getElementById("downloadXlsx").addEventListener("click", function () {
            let wb = XLSX.utils.book_new();
            let ws = XLSX.utils.table_to_sheet(document.getElementById("reportTable"), { origin: "A3" });

            // Report Name
            let reportName = "Overview : {{ ucfirst($type) }} - {{ $products[$activeTab]->product_name ?? 'All Products' }} ({{ $search }})";
            XLSX.utils.sheet_add_aoa(ws, [[reportName]], { origin: "A1" });

            // Style: Merge A1:D1 and center text
            if(!ws['!merges']) ws['!merges'] = [];
            ws['!merges'].push({ s: {r:0,c:0}, e: {r:0,c:3} });
            ws['A1'].s = { font: { bold: true, sz: 14 }, alignment: { horizontal: "center" } };

            wb.Props = {
                Title: reportName,
                Author: "YourApp",
                CreatedDate: new Date()
            };

            XLSX.utils.book_append_sheet(wb, ws, "Report");
            XLSX.writeFile(wb, "overview.xlsx");
        });

        document.getElementById("downloadPng").addEventListener("click", function () {
            html2canvas(document.querySelector(".png-body")).then(canvas => {
                let link = document.createElement("a");
                link.download = "overview.png";
                link.href = canvas.toDataURL("image/png");
                link.click();
            });
        });
    </script>
@endsection

