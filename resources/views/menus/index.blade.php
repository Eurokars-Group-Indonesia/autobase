@extends('layouts.app')

@section('title', 'Menus Management')

@php
    $breadcrumbs = [
        ['title' => 'Menus', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-menu-button-wide"></i> Menus Management</span>
                <a href="{{ route('menus.create') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-plus-circle"></i> Add Menu
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>URL</th>
                                <th>Icon</th>
                                <th>Parent</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($menus as $menu)
                                <tr>
                                    <td>{{ $menu->menu_id }}</td>
                                    <td><code>{{ $menu->menu_code }}</code></td>
                                    <td>
                                        <i class="bi {{ $menu->menu_icon }}"></i> {{ $menu->menu_name }}
                                    </td>
                                    <td>{{ $menu->menu_url ?? '-' }}</td>
                                    <td><i class="bi {{ $menu->menu_icon }}"></i></td>
                                    <td>{{ $menu->parent ? $menu->parent->menu_name : '-' }}</td>
                                    <td>{{ $menu->menu_order }}</td>
                                    <td>
                                        <span class="badge bg-{{ $menu->is_active == '1' ? 'success' : 'danger' }}">
                                            {{ $menu->is_active == '1' ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('menus.edit', $menu->menu_id) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('menus.destroy', $menu->menu_id) }}" method="POST" class="d-inline">
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
                                    <td colspan="9" class="text-center">No menus found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $menus->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
