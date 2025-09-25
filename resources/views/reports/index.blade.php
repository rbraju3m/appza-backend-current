@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row">
            <!-- Growth Chart -->
            <div class="col-lg-12">
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
        </div>
    </div>
@endsection

@section('footer.scripts')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-3d.js"></script>
    <script src="https://code.highcharts.com/modules/funnel.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
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

        });
    </script>
@endsection
