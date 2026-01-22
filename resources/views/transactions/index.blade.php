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
                                <th style="min-width: 80px;">Actions</th>
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
                                    <td>
                                        <button class="btn btn-sm btn-info view-details" 
                                                data-wipno="{{ $transaction->wip_no }}" 
                                                data-invno="{{ $transaction->invoice_no }}" 
                                                data-brandid="{{ $transaction->brand_id }}"
                                                title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                    <td><code>{{ $transaction->invoice_no }}</code></td>
                                    <td><code>{{ $transaction->wip_no }}</code></td>
                                    <td>{{ $transaction->invoice_date->format('d M Y') }}</td>
                                    <td>{{ $transaction->account_code ?? '-' }}</td>
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

<!-- Modal for Transaction Body Details -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="detailsModalLabel">Transaction Body Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading transaction details...</p>
                </div>
                <div id="modalContent" style="display: none;">
                    <div class="mb-3">
                        <strong>WIP No:</strong> <span id="modalWipNo"></span> | 
                        <strong>Invoice No:</strong> <span id="modalInvNo"></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th class="text-center">Part No</th>
                                    <th class="text-center">Description</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Selling Price</th>
                                    <th class="text-end">Discount %</th>
                                    <th class="text-end">Extended Price</th>
                                    <th class="text-center">VAT</th>
                                    <th class="text-center">Analysis Code</th>
                                    <th class="text-center">Parts/Labour</th>
                                </tr>
                            </thead>
                            <tbody id="detailsTableBody">
                                <!-- Data will be loaded here via AJAX -->
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="6" class="text-end">Total:</th>
                                    <th class="text-end" id="totalExtPrice">0.00</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div id="modalError" style="display: none;" class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <span id="errorMessage"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

        // Handle view details button click
        $(document).on('click', '.view-details', function() {
            const wipNo = $(this).data('wipno');
            const invNo = $(this).data('invno');
            const brandId = $(this).data('brandid');
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
            
            // Reset modal state
            $('#modalLoading').show();
            $('#modalContent').hide();
            $('#modalError').hide();
            $('#detailsTableBody').empty();
            
            // Set header info
            $('#modalWipNo').text(wipNo);
            $('#modalInvNo').text(invNo);
            
            // Fetch data via AJAX
            $.ajax({
                url: '{{ route("transactions.body.details") }}',
                method: 'GET',
                data: {
                    wip_no: wipNo,
                    invoice_no: invNo,
                    brand_id: brandId
                },
                success: function(response) {
                    $('#modalLoading').hide();
                    
                    if (response.success && response.data.length > 0) {
                        let totalExtPrice = 0;
                        let html = '';
                        
                        response.data.forEach(function(item, index) {
                            totalExtPrice += parseFloat(item.extended_price || 0);
                            
                            html += `
                                <tr>
                                    <td class="text-center">${index + 1}</td>
                                    <td><code>${item.part_no || '-'}</code></td>
                                    <td>${item.description || '-'}</td>
                                    <td class="text-end">${parseFloat(item.qty || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td class="text-end">${parseFloat(item.selling_price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td class="text-end">${parseFloat(item.discount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}%</td>
                                    <td class="text-end">${parseFloat(item.extended_price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td>${item.vat || '-'}</td>
                                    <td>${item.analysis_code || '-'}</td>
                                    <td>
                                        <span class="badge bg-${item.part_or_labour === 'P' ? 'primary' : 'success'}">
                                            ${item.part_or_labour === 'P' ? 'Parts' : 'Labour'}
                                        </span>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        $('#detailsTableBody').html(html);
                        $('#totalExtPrice').text(totalExtPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#modalContent').show();
                    } else {
                        $('#modalError').show();
                        $('#errorMessage').text('No transaction body details found for this transaction.');
                    }
                },
                error: function(xhr) {
                    $('#modalLoading').hide();
                    $('#modalError').show();
                    $('#errorMessage').text('Failed to load transaction details. Please try again.');
                    console.error('AJAX Error:', xhr);
                }
            });
        });
    });
</script>
@endpush
