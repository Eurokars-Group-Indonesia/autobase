@extends('layouts.app')

@section('title', 'Roles Management')

@php
    $breadcrumbs = [
        ['title' => 'Roles', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-shield-check"></i> Roles Management</span>
                <a href="{{ route('roles.create') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-plus-circle"></i> Add Role
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form action="{{ route('roles.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by code, name, description..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Clear
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Permissions</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                                <tr>
                                    <td>{{ $role->role_id }}</td>
                                    <td><code>{{ $role->role_code }}</code></td>
                                    <td>{{ $role->role_name }}</td>
                                    <td>{{ $role->role_description }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $role->permissions->count() }} permissions</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $role->is_active == '1' ? 'success' : 'danger' }}">
                                            {{ $role->is_active == '1' ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('roles.edit', $role->role_id) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('roles.destroy', $role->role_id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No roles found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
