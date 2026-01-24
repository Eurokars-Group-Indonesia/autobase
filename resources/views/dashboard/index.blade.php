@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@push('styles')
<style>
    [data-theme="dark"] .text-primary {
        color: #FA891A !important;
    }
</style>
@endpush
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Dashboard</h2>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalUsers) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Transaction Master (Header)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalTransactionHeaders) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-receipt fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Transaction Detail (Body)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalTransactionBodies) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-list-ul fs-2 text-info"></i>
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
                <i class="bi bi-info-circle"></i> Welcome to AutoBase
            </div>
            <div class="card-body">
                <h5>Hello, {{ auth()->user()->full_name }}!</h5>
                <p>Welcome to your admin dashboard. Here you can manage transactions, users, and system settings.</p>
                <p class="mb-0"><strong>Last Login:</strong> {{ auth()->user()->last_login ? auth()->user()->last_login->format('d M Y H:i:s') : 'First time login' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
