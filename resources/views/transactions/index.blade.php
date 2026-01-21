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
                <!-- Search Form -->
                <form action="{{ route('transactions.index') }}" method="GET" id="searchForm">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Invoice No, WIP No, Customer, Chassis, Reg No..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="text" class="form-control" name="date_from" id="date_from" 
                                   placeholder="Select date from" value="{{ request('date_from') }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="text" class="form-control" name="date_to" id="date_to" 
                                   placeholder="Select date to" value="{{ request('date_to') }}" readonly>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
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
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>WIP No</th>
                                <th>Invoice Date</th>
                                <th>Customer Name</th>
                                <th>Registration No</th>
                                <th>Chassis</th>
                                <th>Document Type</th>
                                <th>Brand</th>
                                <th>Gross Value</th>
                                <th>Net Value</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td><code>{{ $transaction->invoice_no }}</code></td>
                                    <td><code>{{ $transaction->wip_no }}</code></td>
                                    <td>{{ $transaction->invoice_date->format('d M Y') }}</td>
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
                                    <td>
                                        <span class="badge bg-{{ $transaction->is_active == '1' ? 'success' : 'danger' }}">
                                            {{ $transaction->is_active == '1' ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">No transactions found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $transactions->links() }}
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
