@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row">
            <!-- Report Card -->
            <div class="col-lg-12">
                <div class="card shadow-lg mb-4 border-0 rounded-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">📊 Product Wise Report</h6>
                    </div>
                    <div class="card-body">

                        {{-- Product Tabs --}}
                        <ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
                            @foreach($products as $slug => $title)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == $slug ? 'active' : '' }}"
                                       href="{{ route('report_free_trial', [
                                        'tab' => $slug,
                                        'type' => $type,
                                        'search' => $search
                                   ]) }}">
                                        {{ $title->product_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <form method="GET" action="{{ route('report_free_trial') }}" class="d-flex gap-2 mb-4">
                            <input type="hidden" name="tab" value="{{ $activeTab }}">

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

                        <div id="barChart" style="height: 600px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer.scripts')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

{{--
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            Highcharts.chart('barChart', {
                chart: { type: 'column', backgroundColor: '#f8f9fa' },
                title: { text: '{{ ucfirst($type) }} Report: Leads, Free Trials, Premium' },
                subtitle: { text: 'Product: {{ $products[$activeTab]->product_name ?? "All" }}' },
                xAxis: {
                    categories: @json($reportData->pluck('period')), // daily/monthly/yearly periods
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
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,        // always show data labels
                            inside: true,         // show inside the bar
                            style: {
                                fontWeight: 'bold',
                                color: '#fff'
                            }
                        }
                    }
                },
                series: [
                    { name: 'Leads', data: @json($reportData->pluck('leads')), color: '#198754' },
                    { name: 'Free Trials', data: @json($reportData->pluck('free_trials')), color: '#0d6efd' },
                    { name: 'Premium', data: @json($reportData->pluck('premium')), color: '#dc3545' }
                ],
                exporting: { enabled: true }
            });
        });
    </script>
--}}

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            Highcharts.chart('barChart', {
                chart: {
                    type: 'column',
                    backgroundColor: '#f8f9fa',
                    borderRadius: 8,
                    zoomType: 'x',
                    panning: true,
                    panKey: 'shift'
                },
                title: {
                    text: '{{ ucfirst($type) }} Report: Leads, Free Trials, Premium'
                },
                subtitle: {
                    text: 'Product: {{ $products[$activeTab]->product_name ?? "All" }}'
                },
                xAxis: {
                    categories: @json($reportData->pluck('period')),
                    crosshair: true,
                    labels: { rotation: -45 } // angled labels for readability
                },
                yAxis: {
                    min: 0,
                    title: { text: 'Count' },
                    gridLineColor: '#e9ecef'
                },
                tooltip: {
                    shared: true,
                    backgroundColor: '#fff',
                    borderColor: '#ddd',
                    borderRadius: 6,
                    shadow: true,
                    formatter: function() {
                        return `<b>${this.x}</b><br>
                    <span style="color:#198754;">●</span> Leads: <b>${this.points[0].y}</b><br>
                    <span style="color:#0d6efd;">●</span> Free Trials: <b>${this.points[1].y}</b><br>
                    <span style="color:#dc3545;">●</span> Premium: <b>${this.points[2].y}</b>`;
                    }
                },
                legend: {
                    layout: 'horizontal',
                    align: 'center',
                    verticalAlign: 'bottom',
                    itemStyle: { fontWeight: '600' }
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0,
                        borderRadius: 5,
                        dataLabels: {
                            enabled: true,
                            inside: true,
                            style: { fontWeight: 'bold', color: '#fff' }
                        },
                        states: {
                            hover: { brightness: 0.1 }
                        }
                    },
                    series: {
                        animation: { duration: 1200 }
                    }
                },
                series: [
                    {
                        name: 'Leads',
                        data: @json($reportData->pluck('leads')),
                        color: { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#28a745'], [1, '#198754']] }
                    },
                    {
                        name: 'Free Trials',
                        data: @json($reportData->pluck('free_trials')),
                        color: { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#0dcaf0'], [1, '#0d6efd']] }
                    },
                    {
                        name: 'Premium',
                        data: @json($reportData->pluck('premium')),
                        color: { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#fd6b75'], [1, '#dc3545']] }
                    }
                ],
                exporting: {
                    enabled: true,
                    buttons: {
                        contextButton: {
                            symbolStroke: '#6c757d',
                            theme: { fill: '#f8f9fa', stroke: '#dee2e6' }
                        }
                    }
                },
                responsive: {
                    rules: [{
                        condition: { maxWidth: 768 },
                        chartOptions: { xAxis: { labels: { rotation: -90 } } }
                    }]
                }
            });
        });
    </script>


@endsection
