@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@push('styles')
<style>
    [data-theme="dark"] .text-primary {
        color: #FA891A !important;
    }
</style>
@endpush
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Dashboard</h2>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Welcome to AutoBase
            </div>
            <div class="card-body">
                <h5>Hello, {{ auth()->user()->full_name }}!</h5>
                <p>Welcome to AutoBase, Autoline DataBase Here you can search the Transactions.</p>
                <p class="mb-0"><strong>Last Login:</strong> {{ auth()->user()->last_login ? auth()->user()->last_login->format('d M Y H:i:s') : 'First time login' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @if(auth()->user()->hasRole('ADMIN'))
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalUsers) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Transaction Master (Header)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalTransactionHeaders) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-receipt fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Transaction Detail (Body)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalTransactionBodies) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-list-ul fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Charts -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Transaction Statistics</h5>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('dashboard') }}" class="d-flex justify-content-md-end">
                            <label class="me-2 align-self-center mb-0">Select Year:</label>
                            <select name="year" class="form-select form-select-sm" style="width: 120px;" onchange="this.form.submit()">
                                @for($year = date('Y'); $year >= 2007; $year--)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Transaction Header Chart -->
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header text-white" style="background-color: #002856;">
                <i class="bi bi-receipt"></i> Transaction Header by Invoice Date - {{ $selectedYear }}
            </div>
            <div class="card-body">
                <canvas id="transactionHeaderChart" style="max-height: 400px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Transaction Body Chart -->
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header text-white" style="background-color: #002856;">
                <i class="bi bi-list-ul"></i> Transaction Body by Decard Date - {{ $selectedYear }}
            </div>
            <div class="card-body">
                <canvas id="transactionBodyChart" style="max-height: 400px;"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Transaction Header Chart
    const ctxHeader = document.getElementById('transactionHeaderChart').getContext('2d');
    
    const headerChartData = {
        labels: @json($chartLabels),
        datasets: [
            {
                label: 'Transaction Count',
                data: @json($chartHeaderData),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#28a745',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }
        ]
    };

    const headerConfig = {
        type: 'line',
        data: headerChartData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        title: function(context) {
                            return context[0].label + ' {{ $selectedYear }}';
                        },
                        label: function(context) {
                            return 'Total: ' + context.parsed.y.toLocaleString() + ' transactions';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return Number.isInteger(value) ? value.toLocaleString() : '';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    };

    const transactionHeaderChart = new Chart(ctxHeader, headerConfig);

    // Transaction Body Chart
    const ctxBody = document.getElementById('transactionBodyChart').getContext('2d');
    
    const bodyChartData = {
        labels: @json($chartLabels),
        datasets: [
            {
                label: 'Transaction Count',
                data: @json($chartBodyData),
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.2)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#17a2b8',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }
        ]
    };

    const bodyConfig = {
        type: 'line',
        data: bodyChartData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        title: function(context) {
                            return context[0].label + ' {{ $selectedYear }}';
                        },
                        label: function(context) {
                            return 'Total: ' + context.parsed.y.toLocaleString() + ' transactions';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return Number.isInteger(value) ? value.toLocaleString() : '';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    };

    const transactionBodyChart = new Chart(ctxBody, bodyConfig);
</script>
@endpush
@endsection
