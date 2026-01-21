@extends('layouts.app')

@section('title', 'Create Role')

@php
    $breadcrumbs = [
        ['title' => 'Roles', 'url' => route('roles.index')],
        ['title' => 'Create', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-plus"></i> Create New Role
            </div>
            <div class="card-body">
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Role Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('role_code') is-invalid @enderror" 
                                   name="role_code" value="{{ old('role_code') }}" required>
                            @error('role_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('role_name') is-invalid @enderror" 
                                   name="role_name" value="{{ old('role_name') }}" required>
                            @error('role_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('is_active') is-invalid @enderror" name="is_active" required>
                                <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('role_description') is-invalid @enderror" 
                                  name="role_description" rows="3" required>{{ old('role_description') }}</textarea>
                        @error('role_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    @foreach($permissions as $permission)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                       value="{{ $permission->permission_id }}" id="perm{{ $permission->permission_id }}">
                                                <label class="form-check-label" for="perm{{ $permission->permission_id }}">
                                                    {{ $permission->permission_name }}
                                                    <br><small class="text-muted">{{ $permission->permission_code }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Menus</label>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    @foreach($menus as $menu)
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="menus[]" 
                                                       value="{{ $menu->menu_id }}" id="menu{{ $menu->menu_id }}">
                                                <label class="form-check-label fw-bold" for="menu{{ $menu->menu_id }}">
                                                    <i class="bi {{ $menu->menu_icon }}"></i> {{ $menu->menu_name }}
                                                </label>
                                            </div>
                                            @if($menu->children->count() > 0)
                                                <div class="ms-4">
                                                    @foreach($menu->children as $child)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="menus[]" 
                                                                   value="{{ $child->menu_id }}" id="menu{{ $child->menu_id }}">
                                                            <label class="form-check-label" for="menu{{ $child->menu_id }}">
                                                                <i class="bi {{ $child->menu_icon }}"></i> {{ $child->menu_name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
