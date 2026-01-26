@extends('layouts.app')

@section('title', 'Edit User')

@php
    $breadcrumbs = [
        ['title' => 'Users', 'url' => route('users.index')],
        ['title' => 'Edit', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil"></i> Edit User
            </div>
            <div class="card-body">
                <form action="{{ route('users.update', $user->unique_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name', $user->name) }}" required maxlength="150">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                   name="full_name" value="{{ old('full_name', $user->full_name) }}" required maxlength="150">
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email', $user->email) }}" required maxlength="150">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   name="phone" id="phone" value="{{ old('phone', $user->phone) }}" maxlength="20"
                                   placeholder="e.g. +628123456789">
                            <small class="text-muted">Only numbers and + symbol allowed</small>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dealer</label>
                        <select class="form-select @error('dealer_id') is-invalid @enderror" name="dealer_id">
                            <option value="">-- Select Dealer --</option>
                            @foreach($dealers as $dealer)
                                <option value="{{ $dealer->dealer_id }}" {{ old('dealer_id', $user->dealer_id) == $dealer->dealer_id ? 'selected' : '' }}>
                                    {{ $dealer->dealer_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('dealer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   name="password" id="password" maxlength="255">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Password Strength Indicator -->
                            <div class="mt-2" id="password-requirements" style="display: none;">
                                <small class="d-block mb-1 fw-bold text-muted">Password Requirements:</small>
                                <small class="d-block password-req" id="req-length">
                                    <i class="bi bi-circle"></i> Minimum 8 characters
                                </small>
                                <small class="d-block password-req" id="req-uppercase">
                                    <i class="bi bi-circle"></i> At least 1 uppercase letter
                                </small>
                                <small class="d-block password-req" id="req-lowercase">
                                    <i class="bi bi-circle"></i> At least 1 lowercase letter
                                </small>
                                <small class="d-block password-req" id="req-symbol">
                                    <i class="bi bi-circle"></i> At least 1 symbol (!@#$%^&*...)
                                </small>
                                <small class="d-block password-req" id="req-number">
                                    <i class="bi bi-circle"></i> At least 1 number
                                </small>
                                <small class="d-block password-req" id="req-sequential">
                                    <i class="bi bi-circle"></i> No sequential numbers (123, 234, 321, etc)
                                </small>
                                <small class="d-block password-req" id="req-name">
                                    <i class="bi bi-circle"></i> Must not contain part of your name
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="password_confirmation" maxlength="255">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Roles</label>
                        <div class="row">
                            @foreach($roles as $role)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" 
                                               value="{{ $role->role_id }}" id="role{{ $role->role_id }}"
                                               {{ in_array($role->role_id, $userRoles) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role{{ $role->role_id }}">
                                            {{ $role->role_name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Brands</label>
                        <div class="row">
                            @foreach($brands as $brand)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="brands[]" 
                                               value="{{ $brand->brand_id }}" id="brand{{ $brand->brand_id }}"
                                               {{ in_array($brand->brand_id, $userBrands) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="brand{{ $brand->brand_id }}">
                                            {{ $brand->brand_name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .password-req {
        color: #6c757d;
        transition: color 0.3s ease;
    }
    .password-req.valid {
        color: #198754;
    }
    .password-req.valid i {
        color: #198754;
    }
    .password-req i {
        font-size: 0.7rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Phone input validation - only allow numbers and + symbol
    document.getElementById('phone').addEventListener('input', function(e) {
        // Remove any character that is not a digit or +
        this.value = this.value.replace(/[^0-9+]/g, '');
    });

    // Prevent paste of invalid characters
    document.getElementById('phone').addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        const cleanedText = pastedText.replace(/[^0-9+]/g, '');
        
        // Insert cleaned text at cursor position
        const start = this.selectionStart;
        const end = this.selectionEnd;
        const currentValue = this.value;
        this.value = currentValue.substring(0, start) + cleanedText + currentValue.substring(end);
        
        // Set cursor position after inserted text
        const newPosition = start + cleanedText.length;
        this.setSelectionRange(newPosition, newPosition);
    });

    // Password strength validation
    const passwordInput = document.getElementById('password');
    const fullNameInput = document.querySelector('input[name="full_name"]');
    const requirementsDiv = document.getElementById('password-requirements');
    
    // Show requirements when user starts typing password
    passwordInput.addEventListener('focus', function() {
        if (this.value.length > 0) {
            requirementsDiv.style.display = 'block';
        }
    });
    
    passwordInput.addEventListener('input', function() {
        if (this.value.length > 0) {
            requirementsDiv.style.display = 'block';
            checkPasswordStrength();
        } else {
            requirementsDiv.style.display = 'none';
        }
    });
    
    function checkPasswordStrength() {
        const password = passwordInput.value;
        const fullName = fullNameInput.value;
        
        // 1. Minimum 8 characters
        const hasLength = password.length >= 8;
        updateRequirement('req-length', hasLength);
        
        // 2. At least 1 uppercase letter
        const hasUppercase = /[A-Z]/.test(password);
        updateRequirement('req-uppercase', hasUppercase);
        
        // 3. At least 1 lowercase letter
        const hasLowercase = /[a-z]/.test(password);
        updateRequirement('req-lowercase', hasLowercase);
        
        // 4. At least 1 symbol
        const hasSymbol = /[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/~`]/.test(password);
        updateRequirement('req-symbol', hasSymbol);
        
        // 5. At least 1 number
        const hasNumber = /[0-9]/.test(password);
        updateRequirement('req-number', hasNumber);
        
        // 6. No sequential numbers
        const hasNoSequential = !hasSequentialNumbers(password);
        updateRequirement('req-sequential', hasNoSequential);
        
        // 7. Must not contain part of name
        const hasNoName = !containsNamePart(password, fullName);
        updateRequirement('req-name', hasNoName);
    }
    
    function updateRequirement(elementId, isValid) {
        const element = document.getElementById(elementId);
        if (isValid) {
            element.classList.add('valid');
            element.querySelector('i').className = 'bi bi-check-circle-fill';
        } else {
            element.classList.remove('valid');
            element.querySelector('i').className = 'bi bi-circle';
        }
    }
    
    function hasSequentialNumbers(password) {
        const numbers = password.match(/\d/g);
        if (!numbers || numbers.length < 3) return false;
        
        // Check ascending sequences
        for (let i = 0; i < numbers.length - 2; i++) {
            if (parseInt(numbers[i + 1]) === parseInt(numbers[i]) + 1 &&
                parseInt(numbers[i + 2]) === parseInt(numbers[i]) + 2) {
                return true;
            }
        }
        
        // Check descending sequences
        for (let i = 0; i < numbers.length - 2; i++) {
            if (parseInt(numbers[i + 1]) === parseInt(numbers[i]) - 1 &&
                parseInt(numbers[i + 2]) === parseInt(numbers[i]) - 2) {
                return true;
            }
        }
        
        return false;
    }
    
    function containsNamePart(password, fullName) {
        if (!fullName || fullName.length < 3) return false;
        
        const passwordLower = password.toLowerCase();
        const passwordNormalized = convertLeetSpeak(passwordLower);
        const nameParts = fullName.toLowerCase().split(/\s+/);
        
        for (const namePart of nameParts) {
            if (namePart.length < 3) continue;
            
            // Check substrings of name (minimum 3 characters)
            for (let i = 0; i <= namePart.length - 3; i++) {
                const substring = namePart.substring(i);
                if (substring.length < 3) continue;
                
                // Direct match
                if (passwordLower.includes(substring)) return true;
                
                // Leet speak match
                if (passwordNormalized.includes(substring)) return true;
            }
        }
        
        return false;
    }
    
    function convertLeetSpeak(text) {
        const leetMap = {
            '0': 'o', '1': 'i', '3': 'e', '4': 'a', '5': 's',
            '7': 't', '8': 'b', '@': 'a', '$': 's', '!': 'i', '+': 't'
        };
        
        return text.split('').map(char => leetMap[char] || char).join('');
    }
    
    // Add event listeners
    passwordInput.addEventListener('keyup', checkPasswordStrength);
    fullNameInput.addEventListener('input', checkPasswordStrength);
    fullNameInput.addEventListener('keyup', checkPasswordStrength);
</script>
@endpush
