@extends('layouts.contentNavbarLayout')

@section('title', 'Orders Dashboard - Farmer')

@section('content')
<div class="row">
    <!-- Order Statistics -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-warning me-3">
                            <i class="ri-time-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $orderStats['pending_orders'] ?? 0 }}</h5>
                            <small class="text-muted">Pending Orders</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-primary me-3">
                            <i class="ri-truck-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $orderStats['processing_orders'] ?? 0 }}</h5>
                            <small class="text-muted">Processing Orders</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-success me-3">
                            <i class="ri-check-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $orderStats['completed_orders'] ?? 0 }}</h5>
                            <small class="text-muted">Completed Orders</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-info me-3">
                            <i class="ri-money-dollar-circle-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">UGX {{ number_format($orderStats['total_revenue'] ?? 0, 0) }}</h5>
                            <small class="text-muted">Total Revenue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Recent Orders</h5>
                    <small class="text-muted">Orders from wholesalers, retailers, and factories</small>
                </div>
                <a href="{{ route('farmer.orders') }}" class="btn btn-outline-primary btn-sm">
                    <i class="ri-list-check me-1"></i> View All Orders
                </a>
            </div>

            <div class="card-body">
                @if($incomingOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Buyer</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Order Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incomingOrders as $order)
                                <tr>
                                    <td>
                                        <span class="fw-medium">#{{ $order->id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ substr($order->buyer->name ?? 'U', 0, 2) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="fw-medium">{{ $order->buyer->name ?? 'Unknown Buyer' }}</span>
                                                <small class="text-muted d-block">{{ ucfirst($order->buyer->role->value ?? 'Customer') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ $order->items->count() }} items</span>
                                        <small class="text-muted d-block">
                                            {{ $order->items->sum('quantity') }} units total
                                        </small>
                                    </td>
                                    <td>
                                        <span class="fw-medium">UGX {{ number_format($order->total_amount ?? 0, 0) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'approved' => 'info',
                                                'processing' => 'primary',
                                                'shipped' => 'success',
                                                'delivered' => 'success',
                                                'rejected' => 'danger',
                                                'cancelled' => 'secondary'
                                            ];
                                        @endphp
                                        <span class="badge bg-label-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                        @if($order->payment_status)
                                            <small class="text-muted d-block">
                                                Payment: {{ ucfirst($order->payment_status) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span>{{ $order->created_at->format('M d, Y') }}</span>
                                        <small class="text-muted d-block">{{ $order->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('farmer.orders.show', $order) }}">
                                                    <i class="ri-eye-line me-1"></i> View Details
                                                </a>

                                                @if($order->status == 'pending')
                                                    <form action="{{ route('farmer.orders.approve', $order) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success">
                                                            <i class="ri-check-line me-1"></i> Approve
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('farmer.orders.reject', $order) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to reject this order?')">
                                                            <i class="ri-close-line me-1"></i> Reject
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($order->status == 'approved')
                                                    <form action="{{ route('farmer.orders.ship', $order) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-primary">
                                                            <i class="ri-truck-line me-1"></i> Mark as Shipped
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="ri-shopping-cart-line" style="font-size: 3rem; color: #ddd;"></i>
                        </div>
                        <h6>No Orders Yet</h6>
                        <p class="text-muted">You haven't received any orders from buyers yet.</p>
                        <a href="{{ route('farmer.inventory') }}" class="btn btn-primary">
                            <i class="ri-droplet-line me-1"></i> Manage Inventory
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions for Pending Orders -->
@if($incomingOrders->where('status', 'pending')->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="ri-alert-line me-2"></i>
            <div>
                <strong>Action Required:</strong> You have {{ $incomingOrders->where('status', 'pending')->count() }} pending orders waiting for your approval.
            </div>
        </div>
    </div>
</div>
@endif
@endsection
                                                    </form>
                                                    <form action="{{ route('farmer.orders.reject', $order) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="ri-close-line me-1"></i> Reject
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($order->status == 'processing' && $order->payment_status == 'paid')
                                                    <form action="{{ route('farmer.orders.ship', $order) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-info">
                                                            <i class="ri-truck-line me-1"></i> Mark as Shipped
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('farmer.orders') }}" class="btn btn-primary">
                            <i class="ri-list-check me-1"></i> View All Orders
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
