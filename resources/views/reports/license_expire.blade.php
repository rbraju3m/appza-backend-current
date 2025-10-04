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
                        <hr>
                        <div id="licenseDonutChart" style="height: 400px;"></div>

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

            // --- Column Chart ---
            /*Highcharts.chart('licenseBarChart', {
                chart: { type: 'column', backgroundColor: '#f8f9fa' },
                title: { text: '{{ ucfirst($type) }} License Expiry Report' },
                subtitle: { text: 'Active vs Expired licenses grouped by {{ $type }}' },
                xAxis: {
                    categories: @json($reportData->pluck('period')),
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: { text: 'License Count' },
                    gridLineColor: '#e9ecef'
                },
                tooltip: { shared: true, backgroundColor: '#fff', borderColor: '#ddd' },
                legend: { layout: 'horizontal', align: 'center', verticalAlign: 'bottom' },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,
                            style: { fontWeight: 'bold', color: '#000' }
                        }
                    }
                },
                series: [
                    { name: 'Active', data: @json($reportData->pluck('active')), color: '#198754' },
                    { name: 'Expired', data: @json($reportData->pluck('expired')), color: '#dc3545' },
                ],
                exporting: { enabled: true }
            });*/

            // --- Donut Chart (Summary) ---
            Highcharts.chart('licenseDonutChart', {
                chart: { type: 'pie', backgroundColor: '#f8f9fa' },
                title: { text: 'Overall License Status' },
                plotOptions: {
                    pie: {
                        innerSize: '60%',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.y}',
                            style: { fontWeight: 'bold', color: '#000' }
                        }
                    }
                },
                series: [{
                    name: 'Licenses',
                    colorByPoint: true,
                    data: [
                        { name: 'Active', y: {{ $summary->active }}, color: '#198754' },
                        { name: 'Expired', y: {{ $summary->expired }}, color: '#dc3545' }
                    ]
                }]
            });

        });
    </script>
@endsection
