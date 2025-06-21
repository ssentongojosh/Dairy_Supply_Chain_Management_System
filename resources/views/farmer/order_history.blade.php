@extends('layouts.contentNavbarLayout')

@section('title', 'Order History - Farmer')

@section('content')
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Order History</h4>
                        <p class="text-muted mb-0">Track your sales orders to wholesalers and retailers</p>
                    </div>

                    <!-- Filters -->
                    <div class="card-body">
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Search Buyer</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Buyer name..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                            <div class="mb-3">
                                <a href="{{ route('farmer.orders') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="ri-close-line"></i> Clear Filters
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Orders Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Buyer</th>
                                    <th>Products</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Order Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>
                                            <span class="fw-medium">#{{ $order->id }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        {{ substr($order->buyer->name ?? 'N/A', 0, 2) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="fw-medium">{{ $order->buyer->name ?? 'N/A' }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ ucfirst($order->buyer->role->value ?? 'Customer') }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                @foreach($order->items->take(2) as $item)
                                                    <small class="text-muted">
                                                        {{ $item->product->name ?? 'Product' }} ({{ $item->quantity }} {{ $item->product->unit ?? 'L' }})
                                                    </small>
                                                @endforeach
                                                @if($order->items->count() > 2)
                                                    <small class="text-primary">
                                                        +{{ $order->items->count() - 2 }} more items
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium">UGX {{ number_format($order->total_amount, 0) }}</span>
                                            <br>
                                            <small class="text-muted">{{ $order->items->sum('quantity') }} total units</small>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'approved' => 'info',
                                                    'processing' => 'primary',
                                                    'shipped' => 'success',
                                                    'delivered' => 'success',
                                                    'cancelled' => 'danger',
                                                    'rejected' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-label-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                            @if($order->payment_status)
                                                <br>
                                                <small class="text-muted">
                                                    Payment: {{ ucfirst($order->payment_status) }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <span>{{ $order->created_at->format('M d, Y') }}</span>
                                            <br>
                                            <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
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
                                                                <i class="ri-check-line me-1"></i> Approve Order
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('farmer.orders.reject', $order) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to reject this order?')">
                                                                <i class="ri-close-line me-1"></i> Reject Order
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
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="ri-shopping-cart-line" style="font-size: 3rem; color: #ddd;"></i>
                                            </div>
                                            <h6>No Orders Found</h6>
                                            <p class="text-muted">
                                                @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                                                    No orders match your current filters.
                                                @else
                                                    You haven't received any orders yet.
                                                @endif
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($orders->hasPages())
                        <div class="card-body">
                            {{ $orders->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Statistics Cards -->
        <div class="row mt-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar-initial bg-primary rounded">
                                    <i class="ri-shopping-cart-line"></i>
                                </div>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Total Orders</span>
                        <h3 class="card-title mb-2">{{ $orderStats['total_orders'] ?? 0 }}</h3>
                        <small class="text-muted">All time orders</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar-initial bg-warning rounded">
                                    <i class="ri-time-line"></i>
                                </div>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Pending Orders</span>
                        <h3 class="card-title mb-2">{{ $orderStats['pending_orders'] ?? 0 }}</h3>
                        <small class="text-muted">Awaiting your approval</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar-initial bg-success rounded">
                                    <i class="ri-truck-line"></i>
                                </div>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Completed Orders</span>
                        <h3 class="card-title mb-2">{{ $orderStats['completed_orders'] ?? 0 }}</h3>
                        <small class="text-muted">Successfully delivered</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar-initial bg-info rounded">
                                    <i class="ri-money-dollar-circle-line"></i>
                                </div>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Total Revenue</span>
                        <h3 class="card-title mb-2">UGX {{ number_format($orderStats['total_revenue'] ?? 0, 0) }}</h3>
                        <small class="text-muted">From all orders</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
