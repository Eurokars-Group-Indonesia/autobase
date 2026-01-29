<div class="table-responsive">
@if($hasFilter)
    {{-- When filtering, show header labels first --}}
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
                            <button class="btn btn-sm btn-info view-details-inline" 
                                    data-wipno="{{ $transaction->wip_no }}" 
                                    data-invno="{{ $transaction->invoice_no }}" 
                                    data-brandcode="{{ $transaction->brand_code }}"
                                    data-magicid="{{ $transaction->magic_id }}"
                                    data-headerid="{{ $transaction->header_id }}"
                                    title="View Details">
                                <i class="bi bi-chevron-down"></i>
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
                    
                    <!-- Body Details Row (will be loaded via AJAX) -->
                    <tr class="body-details-row" id="details-{{ $transaction->header_id }}" style="display: none;">
                        <td colspan="12" class="p-3">
                            <div class="body-details-content">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Loading details...</p>
                                </div>
                            </div>
                        </td>
                    </tr>
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
                                data-brandcode="{{ $transaction->brand_code }}"
                                data-magicid="{{ $transaction->magic_id }}"
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
