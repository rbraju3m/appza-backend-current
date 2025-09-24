@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        {{--<div class="row">
            <!-- Growth Chart -->
            <div class="col-lg-8">
                <div class="card shadow-lg mb-4 border-0 rounded-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">📈 Growth Tracking</h6>
                        <form method="GET" action="{{ route('report_free_trial') }}">
                            <select name="type" onchange="this.form.submit()" class="form-select form-select-sm">
                                <option value="daily" {{ $type=='daily'?'selected':'' }}>Daily</option>
                                <option value="monthly" {{ $type=='monthly'?'selected':'' }}>Monthly</option>
                                <option value="yearly" {{ $type=='yearly'?'selected':'' }}>Yearly</option>
                            </select>
                        </form>
                    </div>
                    <div class="card-body">
                        <div id="growthChart" style="height: 600px;"></div>
                    </div>
                </div>
            </div>

            <!-- Conversion Funnel -->
            <div class="col-lg-4">
                <div class="card shadow-lg mb-4 border-0 rounded-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">🔻 Conversion Funnel</h6>
                    </div>
                    <div class="card-body">
                        <div id="funnelChart" style="height: 600px;"></div>
                    </div>
                </div>
            </div>
        </div>--}}

        <div class="row">
            <!-- Comparison Chart -->
            <div class="col-lg-12">
                <div class="card shadow-lg mb-4 border-0 rounded-3">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0">📊 Free Trials vs Leads vs Premium Comparison</h6>
                    </div>
                    <div class="card-body">
                        <div id="comparisonChart" style="height: 600px;"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('footer.scripts')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-3d.js"></script>
    <script src="https://code.highcharts.com/modules/funnel.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

    <script>
        /*document.addEventListener("DOMContentLoaded", function () {
            // --- Growth Chart ---
            Highcharts.chart('growthChart', {
                chart: { type: 'spline', backgroundColor: '#f8f9fa' },
                title: { text: 'Free Trial ➝ Lead ➝ Premium ({{ ucfirst($type) }})' },
                subtitle: { text: 'Conversion performance over time' },
                xAxis: {
                    categories: @json($freeTrials->pluck('period')),
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: { text: 'Count' },
                    gridLineColor: '#e9ecef'
                },
                tooltip: { shared: true, backgroundColor: '#fff', borderColor: '#ddd' },
                legend: { layout: 'horizontal', align: 'center', verticalAlign: 'bottom' },
                plotOptions: {
                    spline: {
                        marker: { enabled: true, radius: 4, symbol: 'circle' }
                    }
                },
                series: [
                    { name: 'Free Trials', data: @json($freeTrials->pluck('total')), color: '#0d6efd' },
                    { name: 'Leads', data: @json($leads->pluck('total')), color: '#198754' },
                    { name: 'Premium Licenses', data: @json($premium->pluck('total')), color: '#dc3545' }
                ],
                exporting: { enabled: true }
            });

            // --- Conversion Funnel ---
            Highcharts.chart('funnelChart', {
                chart: { type: 'funnel', backgroundColor: '#f8f9fa' },
                title: { text: 'Overall Conversion Funnel' },
                plotOptions: {
                    series: {
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.y}',
                            softConnector: true
                        },
                        neckWidth: '30%',
                        neckHeight: '25%',
                        width: '80%'
                    }
                },
                tooltip: { pointFormat: '<b>{point.y}</b>' },
                series: [{
                    name: 'Users',
                    data: [
                        ['Free Trials', {{ $funnelData['free_trials'] }}],
                        ['Leads', {{ $funnelData['leads'] }}],
                        ['Premium Licenses', {{ $funnelData['premium'] }}]
                    ]
                }]
            });
        });*/
        // --- Comparison Chart ---
        Highcharts.chart('comparisonChart', {
            chart: { type: 'column', backgroundColor: '#f8f9fa' },
            title: { text: 'Comparison Across Periods ({{ ucfirst($type) }})' },
            xAxis: {
                categories: @json($freeTrials->pluck('period')),
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: { text: 'Count' },
                gridLineColor: '#e9ecef'
            },
            tooltip: {
                shared: true,
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat:
                    '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y}</b></td></tr>',
                footerFormat: '</table>',
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [
                { name: 'Free Trials', data: @json($freeTrials->pluck('total')), color: '#0d6efd' },
                { name: 'Leads', data: @json($leads->pluck('total')), color: '#198754' },
                { name: 'Premium Licenses', data: @json($premium->pluck('total')), color: '#dc3545' }
            ],
            exporting: { enabled: true }
        });

    </script>
@endsection
