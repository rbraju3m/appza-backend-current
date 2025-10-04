@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-lg mb-4 border-0 rounded-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">🕐 License Duration Distribution</h6>
                    </div>
                    <div class="card-body">

                        {{-- Filter --}}
                        <form method="GET" action="{{ route('report_license_duration') }}" class="d-flex gap-2 mb-4">
                            <input type="hidden" name="type" value="{{ $type }}">

                            @if($type == 'daily')
                                <input type="date" name="search" value="{{ $search }}" class="form-control form-control-sm">
                            @elseif($type == 'monthly')
                                <input type="month" name="search" value="{{ $search }}" class="form-control form-control-sm">
                            @elseif($type == 'yearly')
                                <input type="number" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Enter year">
                            @endif

                            <select name="type" onchange="this.form.submit()" class="form-select form-select-sm">
                                @foreach($reportTypes as $key => $label)
                                    <option value="{{ $key }}" {{ $type == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-light btn-sm">Search</button>
                        </form>

                        {{-- Column Chart --}}
                        <div id="durationBarChart" style="height: 600px;"></div>
                        <hr>

                        {{-- Donut Chart --}}
                        <div id="durationDonutChart" style="height: 400px;"></div>

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
            // Column chart
            Highcharts.chart('durationBarChart', {
                chart: { type: 'column', backgroundColor: '#f8f9fa' },
                title: { text: '{{ ucfirst($type) }} License Duration Distribution' },
                xAxis: {
                    categories: @json($reportData->pluck('label')),
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: { text: 'License Count' }
                },
                tooltip: { shared: true, backgroundColor: '#fff', borderColor: '#ddd' },
                plotOptions: {
                    column: {
                        dataLabels: { enabled: true, style: { fontWeight: 'bold', color: '#000' } }
                    }
                },
                series: [{
                    name: 'Licenses',
                    data: @json($reportData->pluck('count')),
                    color: '#0d6efd'
                }],
                exporting: { enabled: true }
            });

            // Donut chart
            Highcharts.chart('durationDonutChart', {
                chart: { type: 'pie', backgroundColor: '#f8f9fa' },
                title: { text: 'Overall License Status' },
                plotOptions: {
                    pie: {
                        innerSize: '60%',
                        dataLabels: { enabled: true, format: '<b>{point.name}</b>: {point.y}', style: { fontWeight: 'bold', color: '#000' } }
                    }
                },
                series: [{
                    name: 'Licenses',
                    colorByPoint: true,
                    data: [
                        { name: 'Expired', y: {{ $summary['Expired'] }}, color: '#dc3545' },
                        { name: 'Active', y: {{ $summary['Active'] }}, color: '#198754' },
                        { name: 'Lifetime', y: {{ $summary['Lifetime'] }}, color: '#ffc107' }
                    ]
                }]
            });

        });
    </script>
@endsection
