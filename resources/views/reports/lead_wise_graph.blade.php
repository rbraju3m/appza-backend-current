@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Free Trial Status</h5>
                <canvas id="trialChart" height="300"></canvas>
            </div>
            <div class="col-md-6">
                <h5>Premium Status</h5>
                <canvas id="premiumChart" height="300"></canvas>
            </div>
        </div>
    </div>
@endsection

@section('footer.scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Chart Highlight & Tooltip Setup
        const trialCtx = document.getElementById('trialChart').getContext('2d');
        const trialChart = new Chart(trialCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Grace Period', 'Expired', 'No Trial'],
                datasets: [{
                    data: [{{ $trialActive }}, {{ $trialGrace }}, {{ $trialExpired }}, {{ $noTrial }}],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d'],
                    hoverOffset: 15,   // Highlight on hover
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                return `${label}: ${value} Lead${value > 1 ? 's' : ''}`;
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });

        const premiumCtx = document.getElementById('premiumChart').getContext('2d');
        const premiumChart = new Chart(premiumCtx, {
            type: 'doughnut',
            data: {
                labels: ['Premium Active', 'Not Upgraded'],
                datasets: [{
                    data: [{{ $premiumActive }}, {{ $notUpgraded }}],
                    backgroundColor: ['#28a745', '#6c757d'],
                    hoverOffset: 15,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                return `${label}: ${value} Lead${value > 1 ? 's' : ''}`;
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });
    </script>
@endsection
