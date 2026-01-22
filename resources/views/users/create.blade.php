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
                                   name="phone" value="{{ old('phone') }}" maxlength="20">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand</label>
                            <select class="form-select @error('brand_id') is-invalid @enderror" name="brand_id">
                                <option value="">-- Select Brand --</option>
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
                        <div class="col-md-6 mb-3">
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
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   name="password" required maxlength="255">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password_confirmation" required maxlength="255">
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

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
