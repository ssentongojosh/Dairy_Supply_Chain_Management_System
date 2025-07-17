@extends('layouts/contentNavbarLayout')

@section('title', 'Farmer Orders')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Farmer /</span> Orders
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Received Orders</h5>
            <small class="text-muted float-end">Orders from Suppliers</small>
        </div>

        <div class="card-body">
            @if ($orders->isEmpty())
                <div class="text-center py-5">
                    <i class="bx bx-package bx-lg text-muted mb-3"></i>
                    <h6 class="mb-2">No Orders Found</h6>
                    <p class="text-muted">
                        You haven’t received any orders from suppliers yet.
                    </p>
                    <a href="{{ route('farmer.dashboard') }}" class="btn btn-primary">
                        <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#Order ID</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->buyer->name }}<br><small>{{ $order->buyer->email }}</small></td>
                                    <td>
                                        <span class="badge bg-info text-dark text-capitalize">{{ $order->status }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary text-capitalize">{{ $order->payment_status }}</span>
                                    </td>
                                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('farmer.orders', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <a href="{{ route('farmer.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
