@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Dashboard</h2>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Roles</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalRoles }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-shield-check fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Permissions</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPermissions }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-key fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Menus</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalMenus }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-menu-button-wide fs-2 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Welcome to Admin Panel
            </div>
            <div class="card-body">
                <h5>Hello, {{ auth()->user()->full_name }}!</h5>
                <p>Welcome to your admin dashboard. Here you can manage users, roles, permissions, and menus.</p>
                <p class="mb-0"><strong>Last Login:</strong> {{ auth()->user()->last_login ? auth()->user()->last_login->format('d M Y H:i:s') : 'First time login' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
