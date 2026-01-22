@extends('layouts.app')

@section('title', 'Import Transaction Headers')

@push('styles')
<style>
    .drop-zone {
        border: 2px dashed #cbd5e0;
        border-radius: 8px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }
    
    .drop-zone:hover {
        border-color: #4299e1;
        background-color: #ebf8ff;
    }
    
    .drop-zone.dragover {
        border-color: #3182ce;
        background-color: #bee3f8;
        transform: scale(1.02);
    }
    
    .drop-zone-icon {
        font-size: 36px;
        color: #a0aec0;
        margin-bottom: 12px;
    }
    
    .drop-zone.dragover .drop-zone-icon {
        color: #3182ce;
    }
    
    .file-info {
        margin-top: 12px;
        padding: 10px;
        background-color: #e6fffa;
        border: 1px solid #81e6d9;
        border-radius: 6px;
        display: none;
    }
    
    .file-info.show {
        display: block;
    }
    
    .file-info-icon {
        color: #38b2ac;
        margin-right: 8px;
    }
    
    .error-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .error-item {
        border-left: 4px solid #dc3545;
        padding: 12px;
        margin-bottom: 10px;
        background-color: #fff5f5;
        border-radius: 4px;
    }
    
    .error-row-number {
        font-weight: bold;
        color: #dc3545;
        font-size: 14px;
        margin-bottom: 8px;
    }
    
    .error-message {
        color: #721c24;
        font-size: 13px;
        line-height: 1.6;
    }
    
    .error-message code {
        background-color: #f8d7da;
        padding: 2px 6px;
        border-radius: 3px;
        color: #721c24;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="h3 mb-0">Import Transaction Headers</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error') && !session('import_errors') && !session('sql_error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('sql_error'))
        <div class="alert alert-danger" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Import Errors ({{ count(session('sql_error')) }} error(s))
            </h5>
            <p class="mb-3">The following errors occurred during import:</p>
            
            <div class="error-list">
                @foreach(session('sql_error') as $error)
                    <div class="error-item">
                        <div class="error-row-number">
                            <i class="bi bi-arrow-right-circle-fill me-1"></i>
                            @if($error['row'] === 'Unknown')
                                Row: Unknown
                            @else
                                Excel Row {{ $error['row'] }}
                            @endif
                        </div>
                        <div class="error-message">
                            <strong>Field:</strong> {{ $error['field'] }}<br>
                            <strong>Value:</strong> <code>{{ is_array($error['value']) ? json_encode($error['value']) : $error['value'] }}</code><br>
                            <strong>Error:</strong> {{ $error['error'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(session('import_errors'))
        <div class="alert alert-danger" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Import Errors ({{ count(session('import_errors')) }} error(s))
            </h5>
            @if(session('success_count'))
                <p class="mb-2">
                    <span class="badge bg-success">{{ session('success_count') }} records imported successfully</span>
                </p>
            @endif
            <p class="mb-3">The following rows could not be imported:</p>
            
            <div class="error-list">
                @foreach(session('import_errors') as $error)
                    <div class="error-item">
                        <div class="error-row-number">
                            <i class="bi bi-arrow-right-circle-fill me-1"></i>Excel Row {{ $error['row'] }}
                        </div>
                        <div class="error-message">
                            <strong>Field:</strong> {{ $error['field'] }}<br>
                            <strong>Value:</strong> <code>{{ is_array($error['value']) ? json_encode($error['value']) : $error['value'] }}</code><br>
                            <strong>Error:</strong> {{ $error['error'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upload Excel File</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('transactions.header.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="brand_id" class="form-label">Brand <span class="text-danger">*</span></label>
                            <select name="brand_id" id="brand_id" class="form-select @error('brand_id') is-invalid @enderror" required>
                                <option value="">Select Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->brand_id }}" {{ old('brand_id') == $brand->brand_id ? 'selected' : '' }}>
                                        {{ $brand->brand_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Excel File <span class="text-danger">*</span></label>
                            
                            <div class="drop-zone" id="dropZone">
                                <div class="drop-zone-icon">
                                    <i class="bi bi-cloud-upload"></i>
                                </div>
                                <p class="mb-1"><strong>Drag & Drop your file here</strong></p>
                                <p class="text-muted small mb-2">or click to browse</p>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('file').click()">
                                    <i class="bi bi-folder2-open"></i> Browse Files
                                </button>
                                <input type="file" name="file" id="file" class="d-none" accept=".xlsx,.xls,.csv" required>
                            </div>
                            
                            <div class="file-info" id="fileInfo">
                                <i class="bi bi-file-earmark-spreadsheet file-info-icon"></i>
                                <strong>Selected file:</strong> <span id="fileName"></span>
                                <span class="text-muted">(<span id="fileSize"></span>)</span>
                                <button type="button" class="btn btn-sm btn-link text-danger float-end" onclick="clearFile()">
                                    <i class="bi bi-x-circle"></i> Remove
                                </button>
                            </div>
                            
                            <div class="form-text">Supported formats: .xlsx, .xls, .csv (Max: 10MB)</div>
                            @error('file')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-upload"></i> Import
                            </button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Template & Instructions</h5>
                </div>
                <div class="card-body">
                    <h6>Download Template</h6>
                    <p class="text-muted small">Download the Excel template with the correct column headers.</p>
                    <a href="{{ route('transactions.header.import.template') }}" class="btn btn-success btn-sm mb-3">
                        <i class="bi bi-download"></i> Download Template
                    </a>

                    <h6>Required Columns</h6>
                    <ul class="small text-muted">
                        <li><strong>WIPNO</strong> - Work In Progress Number (Required)</li>
                        <li><strong>Account</strong> - Account Code</li>
                        <li><strong>CustName</strong> - Customer Name</li>
                        <li><strong>Add1-Add5</strong> - Address Lines</li>
                        <li><strong>Dept</strong> - Department</li>
                        <li><strong>InvNo</strong> - Invoice Number</li>
                        <li><strong>InvDate</strong> - Invoice Date</li>
                        <li><strong>MAGICH</strong> - Vehicle ID</li>
                        <li><strong>DocType</strong> - Document Type</li>
                        <li><strong>ExchangeRate</strong> - Exchange Rate</li>
                        <li><strong>RegNo</strong> - Registration Number</li>
                        <li><strong>Chassis</strong> - Chassis Number</li>
                        <li><strong>Mileage</strong> - Mileage</li>
                        <li><strong>CurrCode</strong> - Currency Code</li>
                        <li><strong>GrossValue</strong> - Gross Value</li>
                        <li><strong>NetValue</strong> - Net Value</li>
                        <li><strong>CustDisc</strong> - Customer Discount</li>
                        <li><strong>SvcCode</strong> - Service Code</li>
                        <li><strong>RegDate</strong> - Registration Date</li>
                        <li><strong>Description</strong> - Description</li>
                        <li><strong>EngineNo</strong> - Engine Number</li>
                        <li><strong>AcctCompany</strong> - Account Company</li>
                    </ul>

                    <h6 class="mt-3">Notes</h6>
                    <ul class="small text-muted">
                        <li>The import uses <strong>update data</strong> based on WIPNO and Brand</li>
                        <li>Existing records will be updated</li>
                        <li>New records will be created</li>
                        <li>Date format: YYYY-MM-DD or Excel date</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('file');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const submitBtn = document.getElementById('submitBtn');
    
    // Allowed file types
    const allowedTypes = [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'text/plain'
    ];
    const allowedExtensions = ['.xlsx', '.xls', '.csv'];
    
    // Click to browse
    dropZone.addEventListener('click', function(e) {
        if (e.target.tagName !== 'BUTTON') {
            fileInput.click();
        }
    });
    
    // File input change
    fileInput.addEventListener('change', function(e) {
        handleFiles(this.files);
    });
    
    // Drag and drop events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, function() {
            dropZone.classList.add('dragover');
        });
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, function() {
            dropZone.classList.remove('dragover');
        });
    });
    
    dropZone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    });
    
    function handleFiles(files) {
        if (files.length === 0) return;
        
        const file = files[0];
        
        // Validate file type
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        const isValidType = allowedTypes.includes(file.type) || allowedExtensions.includes(fileExtension);
        
        if (!isValidType) {
            alert('Invalid file type. Please upload Excel (.xlsx, .xls) or CSV (.csv) files only.');
            return;
        }
        
        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size exceeds 10MB. Please upload a smaller file.');
            return;
        }
        
        // Create a new FileList with the file
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;
        
        // Display file info
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileInfo.classList.add('show');
        
        // Enable submit button
        submitBtn.disabled = false;
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    window.clearFile = function() {
        fileInput.value = '';
        fileInfo.classList.remove('show');
        submitBtn.disabled = false;
    };
});
</script>
@endpush
