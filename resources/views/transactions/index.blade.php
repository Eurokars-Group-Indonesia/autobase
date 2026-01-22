@extends('layouts.app')

@section('title', 'Transaction Headers')

@php
    $breadcrumbs = [
        ['title' => 'Transactions', 'url' => '#']
    ];
@endphp

@push('styles')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-input {
        background-color: white !important;
    }
    .table-nowrap th,
    .table-nowrap td {
        white-space: nowrap;
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
                <span><i class="bi bi-receipt"></i> Transaction Headers</span>
                @if(auth()->user()->hasPermission('transactions.header.import'))
                <a href="{{ route('transactions.header.import') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-upload"></i> Import Excel
                </a>
                @endif
            </div>
            <div class="card-body">
                <form action="{{ route('transactions.index') }}" method="GET" id="searchForm">
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
                                   placeholder="Customer, Chassis, Invoice No, WIP No, Reg No, Date..." 
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
                                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
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
                                <th style="min-width: 120px;">Invoice No</th>
                                <th style="min-width: 120px;">WIP No</th>
                                <th style="min-width: 120px;">Invoice Date</th>
                                <th style="min-width: 150px;">Account</th>
                                <th style="min-width: 200px;">Customer Name</th>
                                <th style="min-width: 150px;">Registration No</th>
                                <th style="min-width: 180px;">Chassis</th>
                                <th style="min-width: 120px;">Document Type</th>
                                <th style="min-width: 120px;">Brand</th>
                                <th style="min-width: 130px;">Gross Value</th>
                                <th style="min-width: 130px;">Net Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td><code>{{ $transaction->invoice_no }}</code></td>
                                    <td><code>{{ $transaction->wip_no }}</code></td>
                                    <td>{{ $transaction->invoice_date->format('d M Y') }}</td>
                                    <td>{{ $transaction->account ?? '-' }}</td>
                                    <td>{{ $transaction->customer_name ?? '-' }}</td>
                                    <td>{{ $transaction->registration_no ?? '-' }}</td>
                                    <td>{{ $transaction->chassis ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->document_type === 'I' ? 'primary' : 'warning' }}">
                                            {{ $transaction->getDocumentTypeLabel() }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->brand->brand_name ?? '-' }}</td>
                                    <td class="text-end">{{ $transaction->currency_code }} {{ number_format($transaction->gross_value, 2) }}</td>
                                    <td class="text-end">{{ $transaction->currency_code }} {{ number_format($transaction->net_value, 2) }}</td>
                                   
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">No transactions found</td>
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
