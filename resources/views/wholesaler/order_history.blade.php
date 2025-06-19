@extends('layouts.contentNavbarLayout')

@section('title', 'Order History - Wholesaler')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Incoming Orders</h5>
                <small class="text-muted">Orders from retailers</small>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search Retailer</label>
                        <input type="text" name="search" class="form-control" placeholder="Retailer name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">Filter</button>
                    </div>
                </form>
            </div>

            <div class="card-body">
                @if($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Retailer</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Order Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <span class="fw-medium">#{{ $order->id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ substr($order->buyer->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="fw-medium">{{ $order->buyer->name }}</span>
                                                <small class="text-muted d-block">{{ $order->buyer->email }}</small>
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
                                        <span class="fw-medium">UGX {{ number_format($order->total_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'approved' => 'info',
                                                'processing' => 'primary',
                                                'shipped' => 'success',
                                                'received' => 'success',
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
                                                <a class="dropdown-item" href="{{ route('wholesaler.orders.show', $order) }}">
                                                    <i class="ri-eye-line me-1"></i> View Details
                                                </a>

                                                @if($order->status == 'pending' || $order->status == 'pending_review')
                                                    <form action="{{ route('wholesaler.orders.approve', $order) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success">
                                                            <i class="ri-check-line me-1"></i> Approve
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('wholesaler.orders.reject', $order) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="ri-close-line me-1"></i> Reject
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($order->status == 'processing' && $order->payment_status == 'paid')
                                                    <form action="{{ route('wholesaler.orders.ship', $order) }}" method="POST" class="d-inline">
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

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                        </small>
                        {{ $orders->withQueryString()->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ri-shopping-cart-line ri-48px text-muted mb-3"></i>
                        <h5 class="text-muted">No Orders Found</h5>
                        <p class="text-muted">
                            @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                                No orders match your current filters.
                            @else
                                You haven't received any orders from retailers yet.
                            @endif
                        </p>
                        @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                            <a href="{{ route('wholesaler.orders') }}" class="btn btn-outline-primary">
                                Clear Filters
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
