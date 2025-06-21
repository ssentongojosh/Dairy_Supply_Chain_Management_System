@extends('layouts/contentNavbarLayout')

@section('title', 'Farmer Products')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Farmer /</span> Products
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Catalog</h5>
            <small class="text-muted float-end">Manage your dairy product offerings</small>
        </div>
        <div class="card-body">
            <div class="text-center py-5">
                <i class="bx bx-cube bx-lg text-muted mb-3"></i>
                <h6 class="mb-2">Product Management Coming Soon</h6>
                <p class="text-muted">
                    This section will allow you to add and manage your dairy products,
                    set pricing, and create product descriptions for buyers.
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
