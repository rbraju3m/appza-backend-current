@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-lg mb-4 border-0 rounded-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">📅 License Expiry & Status Report</h6>
                    </div>
                    <div class="card-body">

                        {{-- Filter --}}
                        <form method="GET" action="{{ route('report_license_expiry') }}" class="d-flex gap-2 mb-4">
                            @if($type == 'daily')
                                <input type="date" name="search" value="{{ $search }}" class="form-control form-control-sm">
                            @elseif($type == 'monthly')
                                <input type="month" name="search" value="{{ $search }}" class="form-control form-control-sm">
                            @elseif($type == 'yearly')
                                <input type="number" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Enter year">
                            @endif

                            <select name="type" onchange="this.form.submit()" class="form-select form-select-sm">
                                @foreach($reportTypes as $key => $label)
                                    <option value="{{ $key }}" {{ $type == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-light btn-sm">Search</button>
                        </form>

                        {{-- Charts --}}
{{--                        <div id="licenseBarChart" style="height: 600px;"></div>--}}
{{--                        <hr>--}}
{{--                        <div id="licenseDonutChart" style="height: 400px;"></div>--}}

                        <div class="row">
                            <div class="col-md-6">
                                <div id="freeLicenseDonutChart" style="height: 400px;"></div>
                            </div>
                            <div class="col-md-6">
                                <div id="premiumLicenseDonutChart" style="height: 400px;"></div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer.scripts')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // --- Free Trial Donut ---
            Highcharts.chart('freeLicenseDonutChart', {
                chart: { type: 'pie', backgroundColor: '#f8f9fa' },
                title: { text: 'Free Trial License Status' },
                plotOptions: {
                    pie: {
                        innerSize: '60%',
                        dataLabels: { enabled: true, format: '<b>{point.name}</b>: {point.y}' }
                    }
                },
                series: [{
                    name: 'Licenses',
                    colorByPoint: true,
                    data: [
                        { name: 'Active', y: {{ $freeSummary['active'] ?? 0 }}, color: '#198754' },
                        { name: 'Expired', y: {{ $freeSummary['expired'] ?? 0 }}, color: '#dc3545' },
                        { name: 'Grace', y: {{ $freeSummary['grace_period'] ?? 0 }}, color: '#ffc107' }
                    ]
                }]
            });

            // --- Premium License Donut ---
            Highcharts.chart('premiumLicenseDonutChart', {
                chart: { type: 'pie', backgroundColor: '#f8f9fa' },
                title: { text: 'Premium License Status' },
                plotOptions: {
                    pie: {
                        innerSize: '60%',
                        dataLabels: { enabled: true, format: '<b>{point.name}</b>: {point.y}' }
                    }
                },
                series: [{
                    name: 'Licenses',
                    colorByPoint: true,
                    data: [
                        { name: 'Active', y: {{ $premiumSummary['active'] ?? 0 }}, color: '#198754' },
                        { name: 'Expired', y: {{ $premiumSummary['expired'] ?? 0 }}, color: '#dc3545' },
                        { name: 'Grace', y: {{ $premiumSummary['grace_period'] ?? 0 }}, color: '#ffc107' }
                    ]
                }]
            });

        });
    </script>

@endsection
