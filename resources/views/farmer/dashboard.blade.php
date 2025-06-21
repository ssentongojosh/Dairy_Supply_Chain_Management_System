@extends('layouts.contentNavbarLayout')

@section('title', 'Farmer Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Incoming Orders from Buyers</h5>
                <span class="badge bg-info">{{ $recentOrders->count() }} Recent Orders</span>
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Buyer</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <span class="fw-medium">#{{ $order->id }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ $order->customer_name }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">2 items</span>
                                        </td>
                                        <td>
                                            <span class="fw-medium">UGX {{ number_format($order->total_amount) }}</span>
                                        </td>
                                        <td>
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge bg-success">Approved</span>
                                                    @break
                                                @case('shipped')
                                                    <span class="badge bg-primary">Shipped</span>
                                                    @break
                                                @case('delivered')
                                                    <span class="badge bg-info">Delivered</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <small>{{ $order->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('farmer.orders.show', $order) }}">View Details</a></li>
                                                    @if($order->status === 'pending')
                                                        <li>
                                                            <form action="{{ route('farmer.orders.approve', $order) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button class="dropdown-item text-success" type="submit">
                                                                    <i class="ri-check-line me-2"></i>Approve Order
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('farmer.orders.reject', $order) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="ri-close-line me-2"></i>Reject Order
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    @if($order->status === 'processing')
                                                        <li>
                                                            <form action="{{ route('farmer.orders.ship', $order) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button class="dropdown-item text-primary" type="submit">
                                                                    <i class="ri-truck-line me-2"></i>Mark as Shipped
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('farmer.orders') }}" class="btn btn-outline-primary">
                            View Order History
                        </a>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ri-shopping-cart-line ri-48px text-muted mb-3"></i>
                        <h5 class="text-muted">No Orders Found</h5>
                        <p class="text-muted">
                            You haven't received any orders from buyers yet.
                        </p>
                        <a href="{{ route('farmer.orders') }}" class="btn btn-outline-primary">
                            View Order History
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
