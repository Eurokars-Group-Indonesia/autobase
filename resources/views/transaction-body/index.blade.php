@extends('layouts.app')

@section('title', 'Transaction Body')

@php
    $breadcrumbs = [
        ['title' => 'Transaction Body', 'url' => '#']
    ];
@endphp

@push('styles')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-input {
        background-color: white !important;
    }
    [data-theme="dark"] .flatpickr-input {
        background-color: var(--bg-card) !important;
    }
    .table-nowrap th,
    .table-nowrap td {
        white-space: nowrap;
    }

    .table-nowrap th {
        vertical-align: middle;
        text-align: center;
    }

    .table-nowrap td {
        font-size: 0.90em;
        vertical-align: middle;
        text-align: center;
    }
    
    /* Table border radius */
    .table-responsive {
        border-radius: 10px;
        overflow-x: auto;
        overflow-y: visible;
    }
    
    .table-nowrap {
        margin-bottom: 0;
    }
    
    .table-nowrap thead th:first-child {
        border-top-left-radius: 6px;
    }
    
    .table-nowrap thead th:last-child {
        border-top-right-radius: 6px;
    }
    
    .table-nowrap tbody tr:last-child td:first-child {
        border-bottom-left-radius: 6px;
    }
    
    .table-nowrap tbody tr:last-child td:last-child {
        border-bottom-right-radius: 6px;
    }
    
    /* Prevent horizontal scroll on mobile */
    @media (max-width: 767.98px) {
        body {
            overflow-x: hidden;
        }
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }
        .card {
            margin-left: 0;
            margin-right: 0;
        }
        .table-responsive {
            margin-left: -10px;
            margin-right: -10px;
            width: calc(100% + 20px);
        }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul"></i> Transaction Body</span>
                @if(auth()->user()->hasPermission('transaction-body.import'))
                <a href="{{ route('transaction-body.import') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-upload"></i> Import Excel
                </a>
                @endif
            </div>
            <div class="card-body">
                <form action="{{ route('transaction-body.index') }}" method="GET" id="searchForm">
                    <div class="row mb-3">
                        <div class="col-md-1">
                            <label class="form-label">Per Page</label>
                            <select class="form-select" name="per_page" onchange="this.form.submit()">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Part No, Invoice No, WIP No..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="text" class="form-control" name="date_from" id="date_from" 
                                   placeholder="Select date from" value="{{ request('date_from') }}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="text" class="form-control" name="date_to" id="date_to" 
                                   placeholder="Select date to" value="{{ request('date_to') }}" readonly>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary me-2" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                            @if(request('search') || request('date_from') || request('date_to'))
                                <a href="{{ route('transaction-body.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>


                <div class="table-responsive">
                    <table class="table table-hover table-sm table-nowrap">
                        <thead>
                            <tr>
                                <th style="min-width: 120px;">Part No</th>
                                <th style="min-width: 120px;">Invoice No</th>
                                <th style="min-width: 120px;">WIP No</th>
                                <th style="min-width: 250px;">Description</th>
                                <th style="min-width: 100px;">Date Decard</th>
                                <th style="min-width: 80px;">Qty</th>
                                <th style="min-width: 80px;">Unit</th>
                                <th style="min-width: 120px;">Selling Price</th>
                                <th style="min-width: 100px;">Discount</th>
                                <th style="min-width: 130px;">Extended Price</th>
                                <th style="min-width: 100px;">Type</th>
                                <th style="min-width: 100px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->part_no }}</td>
                                    <td>{{ $transaction->invoice_no }}</td>
                                    <td>{{ $transaction->wip_no }}</td>
                                    <td>{{ $transaction->description ?? '-' }}</td>
                                    <td>{{ $transaction->date_decard ? $transaction->date_decard->format('d M Y') : '-' }}</td>
                                    <td class="text-end">{{ number_format($transaction->qty, 2) }}</td>
                                    <td>{{ $transaction->unit }}</td>
                                    <td class="text-end">{{ number_format($transaction->selling_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($transaction->discount, 2) }}%</td>
                                    <td class="text-end">{{ number_format($transaction->extended_price, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->part_or_labour === 'P' ? 'info' : 'warning' }}">
                                            {{ $transaction->getPartOrLabourLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->invoice_status === 'X' ? 'danger' : 'success' }}">
                                            {{ $transaction->getInvoiceStatusLabel() }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">No transaction body found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div class="text-center text-md-start">
                        Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
                    </div>
                    <div>
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date_from picker
        const dateFromPicker = flatpickr("#date_from", {
            dateFormat: "Y-m-d",
            allowInput: false,
            onChange: function(selectedDates, dateStr, instance) {
                // Update date_to minDate when date_from changes
                if (dateStr) {
                    dateToPicker.set('minDate', dateStr);
                    // Clear date_to if it's before the new date_from
                    const dateToValue = document.getElementById('date_to').value;
                    if (dateToValue && dateToValue < dateStr) {
                        dateToPicker.clear();
                    }
                } else {
                    dateToPicker.set('minDate', null);
                }
            }
        });

        // Initialize date_to picker
        const dateToPicker = flatpickr("#date_to", {
            dateFormat: "Y-m-d",
            allowInput: false,
            minDate: document.getElementById('date_from').value || null
        });
    });
</script>
@endpush
