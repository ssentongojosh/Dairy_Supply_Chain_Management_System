@extends('layouts/contentNavbarLayout')

@section('title', 'Farmer Orders')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Farmer /</span> Orders
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Order Management</h5>
            <small class="text-muted float-end">Manage orders from factories and wholesalers</small>
        </div>
        <div class="card-body">
            <div class="text-center py-5">
                <i class="bx bx-package bx-lg text-muted mb-3"></i>
                <h6 class="mb-2">Order Management Coming Soon</h6>
                <p class="text-muted">
                    This section will allow you to manage orders from factories and wholesalers,
                    track delivery schedules, and manage your dairy product sales.
                </p>
                <a href="{{ route('farmer.dashboard') }}" class="btn btn-primary">
                    <i class="bx bx-arrow-back me-1"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
