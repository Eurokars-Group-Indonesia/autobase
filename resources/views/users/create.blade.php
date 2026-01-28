@extends('layouts.app')

@section('title', 'Create User')

@php
    $breadcrumbs = [
        ['title' => 'Users', 'url' => route('users.index')],
        ['title' => 'Create', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <strong>Validation Error:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus"></i> Create New User
            </div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" required maxlength="150">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                   name="full_name" value="{{ old('full_name') }}" required maxlength="150">
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" required maxlength="150">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   name="phone" id="phone" value="{{ old('phone') }}" maxlength="20"
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
                                <option value="{{ $dealer->dealer_id }}" {{ old('dealer_id') == $dealer->dealer_id ? 'selected' : '' }}>
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
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" id="password" required maxlength="255">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-danger mt-1">
                                    <small><i class="bi bi-exclamation-circle"></i> {{ $message }}</small>
                                </div>
                            @enderror
                            
                            <!-- Password Strength Indicator -->
                            <div class="mt-2" id="password-requirements">
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
                                <small class="d-block password-req" id="req-sequential-alpha">
                                    <i class="bi bi-circle"></i> No sequential alphabet (abc, xyz, cba, etc)
                                </small>
                                <small class="d-block password-req" id="req-name">
                                    <i class="bi bi-circle"></i> Must not contain part of your name
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password_confirmation" id="passwordConfirmation" required maxlength="255">
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                                    <i class="bi bi-eye" id="passwordConfirmationIcon"></i>
                                </button>
                            </div>
                            <div id="passwordMatchError" class="text-danger mt-1" style="display: none;">
                                <small><i class="bi bi-exclamation-circle"></i> Password and confirm password not equal</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Roles</label>
                        <div class="row">
                            @foreach($roles as $role)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" 
                                               value="{{ $role->role_id }}" id="role{{ $role->role_id }}">
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
                                               value="{{ $brand->brand_id }}" id="brand{{ $brand->brand_id }}">
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
                        <button type="submit" class="btn btn-primary" id="submitButton">
                            <i class="bi bi-save"></i> Save User
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
    .password-req.invalid {
        color: #dc3545;
    }
    .password-req.invalid i {
        color: #dc3545;
    }
    .password-req i {
        font-size: 0.7rem;
    }
    
    /* Style for disabled submit button */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    /* Style for error message */
    .text-danger {
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .text-danger i {
        margin-right: 4px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Scroll to error message if exists
    @error('password')
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            if (passwordField) {
                passwordField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                passwordField.focus();
            }
        });
    @enderror

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
        const symbols = '!@#$%^&*(),.?":{}|<>_+=[]\/`-';
        let hasSymbol = false;
        for (let i = 0; i < password.length; i++) {
            if (symbols.includes(password[i])) {
                hasSymbol = true;
                break;
            }
        }
        updateRequirement('req-symbol', hasSymbol);
        
        // 5. At least 1 number
        const hasNumber = /[0-9]/.test(password);
        updateRequirement('req-number', hasNumber);
        
        // 6. No sequential numbers
        const hasNoSequential = !hasSequentialNumbers(password);
        updateRequirement('req-sequential', hasNoSequential);
        
        // 7. No sequential alphabet
        const hasNoSequentialAlpha = !hasSequentialAlphabet(password);
        updateRequirement('req-sequential-alpha', hasNoSequentialAlpha);
        
        // 8. Must not contain part of name
        const hasNoName = !containsNamePart(password, fullName);
        updateRequirement('req-name', hasNoName);
        
        // Check all requirements and update submit button
        checkAllRequirements();
    }
    
    function updateRequirement(elementId, isValid) {
        const element = document.getElementById(elementId);
        if (isValid) {
            element.classList.add('valid');
            element.classList.remove('invalid');
            element.querySelector('i').className = 'bi bi-check-circle-fill';
        } else {
            element.classList.remove('valid');
            element.classList.add('invalid');
            element.querySelector('i').className = 'bi bi-x-circle-fill';
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
    
    function hasSequentialAlphabet(password) {
        const passwordLower = password.toLowerCase();
        
        // Check ascending sequences (abc, bcd, cde, etc)
        for (let i = 0; i < passwordLower.length - 2; i++) {
            const char1 = passwordLower.charCodeAt(i);
            const char2 = passwordLower.charCodeAt(i + 1);
            const char3 = passwordLower.charCodeAt(i + 2);
            
            // Check if all are letters
            if ((char1 >= 97 && char1 <= 122) && 
                (char2 >= 97 && char2 <= 122) && 
                (char3 >= 97 && char3 <= 122)) {
                
                // Check ascending sequence
                if (char2 === char1 + 1 && char3 === char2 + 1) {
                    return true;
                }
                
                // Check descending sequence
                if (char2 === char1 - 1 && char3 === char2 - 1) {
                    return true;
                }
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
            const trimmedNamePart = namePart.trim();
            
            // Skip very short name parts (less than 3 characters)
            if (trimmedNamePart.length < 3) continue;
            
            // Check substrings of name (minimum 3 characters)
            for (let i = 0; i <= trimmedNamePart.length - 3; i++) {
                // Get substring with proper length (same as backend logic)
                const substringLength = Math.max(3, trimmedNamePart.length - i);
                const substring = trimmedNamePart.substring(i, i + substringLength);
                
                // Direct match
                if (passwordLower.includes(substring)) return true;
                
                // Leet speak match
                if (passwordNormalized.includes(substring)) return true;
                
                // Check similarity using Levenshtein distance
                if (isSimilar(passwordNormalized, substring)) return true;
            }
        }
        
        return false;
    }
    
    /**
     * Calculate Levenshtein distance between two strings
     */
    function levenshteinDistance(str1, str2) {
        const len1 = str1.length;
        const len2 = str2.length;
        const matrix = [];
        
        // Initialize matrix
        for (let i = 0; i <= len1; i++) {
            matrix[i] = [i];
        }
        for (let j = 0; j <= len2; j++) {
            matrix[0][j] = j;
        }
        
        // Fill matrix
        for (let i = 1; i <= len1; i++) {
            for (let j = 1; j <= len2; j++) {
                if (str1[i - 1] === str2[j - 1]) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1, // substitution
                        matrix[i][j - 1] + 1,     // insertion
                        matrix[i - 1][j] + 1      // deletion
                    );
                }
            }
        }
        
        return matrix[len1][len2];
    }
    
    /**
     * Check if a substring is similar to any part of the password
     * Using similarity threshold of 0.25 (25%) - more strict, need 75% similarity to match
     */
    function isSimilar(password, substring) {
        const similarityThreshold = 0.25; // Changed from 0.4 to 0.25 for stricter matching
        const subLen = substring.length;
        
        // Check all substrings of password with same length
        for (let i = 0; i <= password.length - subLen; i++) {
            const passwordPart = password.substring(i, i + subLen);
            
            // Calculate similarity
            const levenshtein = levenshteinDistance(passwordPart, substring);
            const maxLen = Math.max(passwordPart.length, substring.length);
            const similarity = 1 - (levenshtein / maxLen);
            
            // If similarity is above threshold, consider it too similar
            if (similarity >= (1 - similarityThreshold)) {
                return true;
            }
        }
        
        return false;
    }
    
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const passwordConfirmation = document.getElementById('passwordConfirmation').value;
        const errorElement = document.getElementById('passwordMatchError');
        const confirmPasswordField = document.getElementById('passwordConfirmation');
        
        if (passwordConfirmation.length > 0) {
            if (password !== passwordConfirmation) {
                errorElement.style.display = 'block';
                confirmPasswordField.classList.add('is-invalid');
                return false;
            } else {
                errorElement.style.display = 'none';
                confirmPasswordField.classList.remove('is-invalid');
                return true;
            }
        } else {
            errorElement.style.display = 'none';
            confirmPasswordField.classList.remove('is-invalid');
            return passwordConfirmation.length === 0; // Return true if empty (not required to match when empty)
        }
    }
    
    function checkAllRequirements() {
        const password = passwordInput.value;
        const fullName = document.querySelector('input[name="full_name"]').value;
        
        // Check all password requirements
        const hasLength = password.length >= 8;
        const hasUppercase = /[A-Z]/.test(password);
        const hasLowercase = /[a-z]/.test(password);
        const symbols = '!@#$%^&*(),.?":{}|<>_+=[]\/`-';
        let hasSymbol = false;
        for (let i = 0; i < password.length; i++) {
            if (symbols.includes(password[i])) {
                hasSymbol = true;
                break;
            }
        }
        const hasNumber = /[0-9]/.test(password);
        const hasNoSequential = !hasSequentialNumbers(password);
        const hasNoSequentialAlpha = !hasSequentialAlphabet(password);
        const hasNoName = !containsNamePart(password, fullName);
        
        // Check password match
        const passwordsMatch = checkPasswordMatch();
        
        // All requirements must be met
        const allValid = hasLength && hasUppercase && hasLowercase && hasSymbol && 
                        hasNumber && hasNoSequential && hasNoSequentialAlpha && hasNoName && passwordsMatch;
        
        // Enable/disable submit button
        const submitButton = document.getElementById('submitButton');
        if (password.length > 0) { // Only check if password is not empty
            if (allValid) {
                submitButton.disabled = false;
                submitButton.classList.remove('btn-secondary');
                submitButton.classList.add('btn-primary');
                submitButton.title = '';
            } else {
                submitButton.disabled = true;
                submitButton.classList.remove('btn-primary');
                submitButton.classList.add('btn-secondary');
                submitButton.title = 'Please meet all password requirements and ensure passwords match';
            }
        } else {
            // If password is empty, enable submit (for edit form where password is optional)
            submitButton.disabled = false;
            submitButton.classList.remove('btn-secondary');
            submitButton.classList.add('btn-primary');
            submitButton.title = '';
        }
        
        return allValid;
    }
    
    function convertLeetSpeak(text) {
        const leetMap = {
            '0': 'o', '1': 'i', '3': 'e', '4': 'a', '5': 's',
            '7': 't', '8': 'b', '@': 'a', '$': 's', '!': 'i', '+': 't'
        };
        
        return text.split('').map(char => leetMap[char] || char).join('');
    }
    
    // Add event listeners - hanya untuk password field dan password confirmation
    passwordInput.addEventListener('input', function() {
        checkPasswordStrength();
        checkAllRequirements();
    });
    passwordInput.addEventListener('keyup', function() {
        checkPasswordStrength();
        checkAllRequirements();
    });
    
    // Tambahkan event listener untuk password confirmation
    const passwordConfirmationInput = document.getElementById('passwordConfirmation');
    if (passwordConfirmationInput) {
        passwordConfirmationInput.addEventListener('input', function() {
            checkPasswordStrength();
            checkAllRequirements();
        });
        passwordConfirmationInput.addEventListener('keyup', function() {
            checkPasswordStrength();
            checkAllRequirements();
        });
    }

    // Password toggle functionality
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordField = document.getElementById('password');
        const passwordIcon = document.getElementById('passwordIcon');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            passwordIcon.classList.remove('bi-eye');
            passwordIcon.classList.add('bi-eye-slash');
        } else {
            passwordField.type = 'password';
            passwordIcon.classList.remove('bi-eye-slash');
            passwordIcon.classList.add('bi-eye');
        }
    });

    document.getElementById('togglePasswordConfirmation').addEventListener('click', function() {
        const passwordConfirmationField = document.getElementById('passwordConfirmation');
        const passwordConfirmationIcon = document.getElementById('passwordConfirmationIcon');
        
        if (passwordConfirmationField.type === 'password') {
            passwordConfirmationField.type = 'text';
            passwordConfirmationIcon.classList.remove('bi-eye');
            passwordConfirmationIcon.classList.add('bi-eye-slash');
        } else {
            passwordConfirmationField.type = 'password';
            passwordConfirmationIcon.classList.remove('bi-eye-slash');
            passwordConfirmationIcon.classList.add('bi-eye');
        }
    });
</script>
@endpush
