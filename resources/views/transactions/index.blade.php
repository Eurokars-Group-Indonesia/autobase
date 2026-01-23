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
        font-size: 12px;
        vertical-align: middle;
        text-align: center;
    }
    
    /* Table border radius */
    .table-responsive {
        border-radius: 8px;
        overflow-x: auto;
        overflow-y: visible;
        box-shadow: 1rem 1rem 1rem 1rem rgba(0, 0, 0, 0.075);
    }
    
    .table-nowrap {
        margin-bottom: 0;
    }
    
    /* Border box for each header-body group */
    .transaction-group {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 1rem;
        overflow-x: auto;
        overflow-y: visible;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .transaction-group:hover {
        border-color: #002856;
        box-shadow: 0 4px 8px rgba(0, 40, 86, 0.15);
    }
    
    .transaction-group .header-row td {
        background-color: #f8f9fa;
        font-weight: 500;
    }
    
    .transaction-group .body-details-row td {
        background-color: #ffffff;
    }

    .table thead th {
        font-size: 12px;
    }

    .form-label, label {
        font-size: 13px;
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
                            <select class="form-select form-select-sm" name="per_page" onchange="this.form.submit()">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control form-control-sm" name="search" 
                                   placeholder="Customer, Chassis, Invoice No, WIP No, Reg No, Date..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="text" class="form-control form-control-sm" id="date_from_display" 
                                   placeholder="Select date from" readonly>
                            <input type="hidden" name="date_from" id="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="text" class="form-control form-control-sm" id="date_to_display" 
                                   placeholder="Select date to" readonly>
                            <input type="hidden" name="date_to" id="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary btn-sm me-2" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                            @if(request('search') || request('date_from') || request('date_to'))
                                <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>


                <div class="table-responsive">
                    @if($hasFilter)
                        {{-- When filtering, show header labels first --}}
                        <!-- <div class="table-header-labels mb-2 p-2 bg-primary text-white rounded">
                            <div class="row g-0">
                                <div class="col" style="min-width: 80px;">Actions</div>
                                <div class="col" style="min-width: 120px;">Invoice No</div>
                                <div class="col" style="min-width: 120px;">WIP No</div>
                                <div class="col" style="min-width: 120px;">Invoice Date</div>
                                <div class="col" style="min-width: 150px;">Account</div>
                                <div class="col" style="min-width: 200px;">Customer Name</div>
                                <div class="col" style="min-width: 150px;">Registration No</div>
                                <div class="col" style="min-width: 180px;">Chassis</div>
                                <div class="col" style="min-width: 120px;">Document Type</div>
                                <div class="col" style="min-width: 120px;">Brand</div>
                                <div class="col text-end" style="min-width: 130px;">Gross Value</div>
                                <div class="col text-end" style="min-width: 130px;">Net Value</div>
                            </div>
                        </div> -->
                        
                        @forelse($transactions as $transaction)
                            <div class="transaction-group">
                                <table class="table table-hover table-sm table-nowrap mb-0">
                                    <thead class="table-light">
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
                                        <!-- Header Row -->
                                        <tr class="header-row">
                                            <td>
                                                @if(isset($transaction->bodies) && count($transaction->bodies) > 0)
                                                <span class="badge bg-success">{{ count($transaction->bodies) }} items</span>
                                                @else
                                                <span class="badge bg-secondary">No items</span>
                                                @endif
                                            </td>
                                            <td>{{ $transaction->invoice_no }}</td>
                                            <td>{{ $transaction->wip_no }}</td>
                                            <td>{{ $transaction->invoice_date ? $transaction->invoice_date->format('d M Y') : '-' }}</td>
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
                                        
                                        <!-- Body Details Row -->
                                        @if(isset($transaction->bodies) && count($transaction->bodies) > 0)
                                        <tr class="body-details-row">
                                            <td colspan="12" class="p-3">
                                                <h6 class="mb-3 text-primary">
                                                    <!-- <i class="bi bi-list-ul"></i> Transaction Body Details  -->
                                                    <!-- <small class="text-muted">({{ count($transaction->bodies) }} items)</small> -->
                                                </h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead class="table-secondary">
                                                            <tr>
                                                                <th class="text-center" style="width: 50px;">No</th>
                                                                <th class="text-center">Part No</th>
                                                                <th class="text-center">Description</th>
                                                                <th class="text-center">Date Decard</th>
                                                                <th class="text-center">Qty</th>
                                                                <th class="text-center">Selling Price</th>
                                                                <th class="text-center">Discount %</th>
                                                                <th class="text-center">Extended Price</th>
                                                                <th class="text-center">Type</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php $totalExtPrice = 0; @endphp
                                                            @foreach($transaction->bodies as $index => $body)
                                                                @php $totalExtPrice += $body->extended_price; @endphp
                                                                <tr>
                                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                                    <td>{{ $body->part_no }}</td>
                                                                    <td>{{ $body->description ?? '-' }}</td>
                                                                    <td class="text-center">
                                                                        @if($body->date_decard)
                                                                            {{ \Carbon\Carbon::parse($body->date_decard)->format('d M Y') }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-end">{{ number_format($body->qty, 2) }}</td>
                                                                    <td class="text-end">{{ number_format($body->selling_price, 2) }}</td>
                                                                    <td class="text-end">{{ number_format($body->discount, 2) }}%</td>
                                                                    <td class="text-end">{{ number_format($body->extended_price, 2) }}</td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-{{ $body->part_or_labour === 'P' ? 'primary' : 'success' }}">
                                                                            {{ $body->part_or_labour === 'P' ? 'Part' : 'Labour' }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot class="table-secondary">
                                                            <tr>
                                                                <th colspan="7" class="text-end">Total:</th>
                                                                <th class="text-end">{{ number_format($totalExtPrice, 2) }}</th>
                                                                <th></th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                        @else
                                        <tr class="body-details-row">
                                            <td colspan="12" class="p-3">
                                                <h6 class="mb-3 text-primary">
                                                    <!-- <i class="bi bi-list-ul"></i> Transaction Body Details  -->
                                                    <small class="text-muted">No Details</small>
                                                </h6>
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        @empty
                            <div class="text-center py-4">No transactions found</div>
                        @endforelse
                    @else
                        {{-- When not filtering, show normal table --}}
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
                                        <td>{{ $transaction->invoice_no }}</td>
                                        <td>{{ $transaction->wip_no }}</td>
                                        <td>{{ $transaction->invoice_date ? $transaction->invoice_date->format('d M Y') : '-' }}</td>
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
                    @endif
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

<!-- Modal for Transaction Body Details (when not filtering) -->
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
                                    <th class="text-center">Date Decard</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Selling Price</th>
                                    <th class="text-center">Discount %</th>
                                    <th class="text-center">Extended Price</th>
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
                                    <th colspan="7" class="text-end">Total:</th>
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

@push('scripts')
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Transaction page loaded');
        
        // Function to convert Y-m-d to d-m-Y
        function formatDateForDisplay(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                return parts[2] + '-' + parts[1] + '-' + parts[0];
            }
            return dateStr;
        }

        // Set initial display values if dates exist
        const initialDateFrom = document.getElementById('date_from').value;
        const initialDateTo = document.getElementById('date_to').value;
        if (initialDateFrom) {
            document.getElementById('date_from_display').value = formatDateForDisplay(initialDateFrom);
        }
        if (initialDateTo) {
            document.getElementById('date_to_display').value = formatDateForDisplay(initialDateTo);
        }

        // Initialize date_from picker
        const dateFromPicker = flatpickr("#date_from_display", {
            dateFormat: "d-m-Y",
            allowInput: false,
            onChange: function(selectedDates, dateStr, instance) {
                // Convert d-m-Y to Y-m-d for hidden input
                if (dateStr) {
                    const parts = dateStr.split('-');
                    const ymdFormat = parts[2] + '-' + parts[1] + '-' + parts[0];
                    document.getElementById('date_from').value = ymdFormat;
                    
                    // Update date_to minDate
                    dateToPicker.set('minDate', dateStr);
                    
                    // Clear date_to if it's before the new date_from
                    const dateToValue = document.getElementById('date_to_display').value;
                    if (dateToValue && new Date(dateToValue.split('-').reverse().join('-')) < new Date(ymdFormat)) {
                        dateToPicker.clear();
                        document.getElementById('date_to').value = '';
                    }
                } else {
                    document.getElementById('date_from').value = '';
                    dateToPicker.set('minDate', null);
                }
            }
        });

        // Initialize date_to picker
        const dateToPicker = flatpickr("#date_to_display", {
            dateFormat: "d-m-Y",
            allowInput: false,
            minDate: initialDateFrom ? formatDateForDisplay(initialDateFrom) : null,
            onChange: function(selectedDates, dateStr, instance) {
                // Convert d-m-Y to Y-m-d for hidden input
                if (dateStr) {
                    const parts = dateStr.split('-');
                    const ymdFormat = parts[2] + '-' + parts[1] + '-' + parts[0];
                    document.getElementById('date_to').value = ymdFormat;
                } else {
                    document.getElementById('date_to').value = '';
                }
            }
        });

        // Handle toggle details button click (when filtering) - Using vanilla JS
        document.body.addEventListener('click', function(e) {
            const toggleButton = e.target.closest('.toggle-details');
            if (toggleButton) {
                e.preventDefault();
                const headerId = toggleButton.getAttribute('data-header-id');
                const detailsRow = document.getElementById('details-' + headerId);
                const icon = toggleButton.querySelector('i');
                
                console.log('Toggle clicked for header:', headerId);
                
                if (detailsRow) {
                    // Toggle visibility
                    if (detailsRow.style.display === 'none' || detailsRow.style.display === '') {
                        detailsRow.style.display = 'table-row';
                        if (icon) {
                            icon.classList.remove('bi-chevron-down');
                            icon.classList.add('bi-chevron-up');
                        }
                        console.log('Details shown');
                    } else {
                        detailsRow.style.display = 'none';
                        if (icon) {
                            icon.classList.remove('bi-chevron-up');
                            icon.classList.add('bi-chevron-down');
                        }
                        console.log('Details hidden');
                    }
                }
            }
        });

        // Handle view details button click (when not filtering - use modal) - Using jQuery
        $(document).on('click', '.view-details', function(e) {
            e.preventDefault();
            const wipNo = $(this).data('wipno');
            const invNo = $(this).data('invno');
            const brandId = $(this).data('brandid');
            
            console.log('View details clicked:', wipNo, invNo);
            
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
                            
                            const dateDecard = item.date_decard ? new Date(item.date_decard).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'}) : '-';
                            
                            html += `
                                <tr>
                                    <td class="text-center">${index + 1}</td>
                                    <td>${item.part_no || '-'}</td>
                                    <td>${item.description || '-'}</td>
                                    <td class="text-center">${dateDecard}</td>
                                    <td class="text-end">${parseFloat(item.qty || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td class="text-end">${parseFloat(item.selling_price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td class="text-end">${parseFloat(item.discount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}%</td>
                                    <td class="text-end">${parseFloat(item.extended_price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td>${item.vat || '-'}</td>
                                    <td>${item.analysis_code || '-'}</td>
                                    <td class="text-center">
                                        <span class="badge bg-${item.part_or_labour === 'P' ? 'primary' : 'success'}">
                                            ${item.part_or_labour === 'P' ? 'Part' : 'Labour'}
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
