@extends('layouts.contentNavbarLayout')

@section('title', 'Order History - Plant Manager')

@section('content')
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Order History</h4>
                        <p class="text-muted mb-0">Track production orders and deliveries</p>
                    </div>

                    <!-- Filters -->
                    <div class="card-body">
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Customer Type</label>
                                <select name="customer_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="retailer" {{ request('customer_type') === 'retailer' ? 'selected' : '' }}>Retailers</option>
                                    <option value="wholesaler" {{ request('customer_type') === 'wholesaler' ? 'selected' : '' }}>Wholesalers</option>
                                    <option value="farmer" {{ request('customer_type') === 'farmer' ? 'selected' : '' }}>Farmers</option>
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
                                <label class="form-label">Search Customer</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Customer name..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                    <i class="ri-refresh-line"></i>
                                </button>
                            </div>
                        </form>

                        @if(request()->hasAny(['status', 'customer_type', 'date_from', 'date_to', 'search']))
                            <div class="mb-3">
                                <a href="{{ route('plant_manager.orders.history') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="ri-close-line"></i> Clear Filters
                                </a>
                                <span class="text-muted ms-2">Showing filtered results</span>
                            </div>
                        @endif

                        <!-- Quick Stats -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center">
                                    <h5 class="mb-0 text-primary">{{ $stats['total_orders'] ?? 0 }}</h5>
                                    <small class="text-muted">Total Orders</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center">
                                    <h5 class="mb-0 text-success">{{ $stats['completed_orders'] ?? 0 }}</h5>
                                    <small class="text-muted">Completed</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center">
                                    <h5 class="mb-0 text-warning">{{ $stats['pending_orders'] ?? 0 }}</h5>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center">
                                    <h5 class="mb-0 text-info">UGX {{ number_format($stats['total_revenue'] ?? 0, 0) }}</h5>
                                    <small class="text-muted">Total Revenue</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
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
                                                    <small class="text-muted">
                                                        +{{ $order->items->count() - 2 }} more items
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium">UGX {{ number_format($order->total_amount ?? 0, 0) }}</span>
                                            @if($order->payment_status)
                                                <br>
                                                <small class="text-muted">
                                                    Payment: 
                                                    <span class="badge bg-label-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }} fs-6">
                                                        {{ ucfirst($order->payment_status) }}
                                                    </span>
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'approved' => 'info',
                                                    'processing' => 'primary',
                                                    'shipped' => 'success',
                                                    'delivered' => 'success',
                                                    'cancelled' => 'secondary',
                                                    'rejected' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-label-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                            @if($order->status === 'processing')
                                                <br>
                                                <small class="text-muted">In Production</small>
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
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('plant_manager.orders.show', $order->id) }}">
                                                            <i class="ri-eye-line me-2"></i>View Details
                                                        </a>
                                                    </li>
                                                    @if($order->status === 'pending')
                                                        <li>
                                                            <button type="button" class="dropdown-item" onclick="approveOrder({{ $order->id }})">
                                                                <i class="ri-check-line me-2"></i>Approve Order
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item text-danger" onclick="rejectOrder({{ $order->id }})">
                                                                <i class="ri-close-line me-2"></i>Reject Order
                                                            </button>
                                                        </li>
                                                    @elseif($order->status === 'approved')
                                                        <li>
                                                            <button type="button" class="dropdown-item" onclick="startProduction({{ $order->id }})">
                                                                <i class="ri-settings-2-line me-2"></i>Start Production
                                                            </button>
                                                        </li>
                                                    @elseif($order->status === 'processing')
                                                        <li>
                                                            <button type="button" class="dropdown-item" onclick="shipOrder({{ $order->id }})">
                                                                <i class="ri-truck-line me-2"></i>Ship Order
                                                            </button>
                                                        </li>
                                                    @endif
                                                    @if(in_array($order->status, ['shipped', 'delivered']))
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="downloadInvoice({{ $order->id }})">
                                                                <i class="ri-download-line me-2"></i>Download Invoice
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="ri-inbox-line fs-1 text-muted"></i>
                                            <h5 class="text-muted mt-3">No orders found</h5>
                                            <p class="text-muted">
                                                @if(request()->hasAny(['status', 'customer_type', 'date_from', 'date_to', 'search']))
                                                    Try adjusting your filters or 
                                                    <a href="{{ route('plant_manager.orders.history') }}">clear all filters</a>
                                                @else
                                                    No orders have been placed yet.
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
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                                </small>
                                {{ $orders->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
function clearFilters() {
    window.location.href = "{{ route('plant_manager.orders.history') }}";
}

function approveOrder(orderId) {
    if (confirm('Are you sure you want to approve this order?')) {
        fetch(`/plant-manager/orders/${orderId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Order approved successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('error', 'Error approving order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'An error occurred while approving the order');
        });
    }
}

function rejectOrder(orderId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason && reason.trim()) {
        fetch(`/plant-manager/orders/${orderId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason.trim() })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Order rejected successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('error', 'Error rejecting order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'An error occurred while rejecting the order');
        });
    }
}

function startProduction(orderId) {
    if (confirm('Are you sure you want to start production for this order?')) {
        fetch(`/plant-manager/orders/${orderId}/start-production`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Production started successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('error', 'Error starting production: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'An error occurred while starting production');
        });
    }
}

function shipOrder(orderId) {
    if (confirm('Are you sure you want to ship this order?')) {
        fetch(`/plant-manager/orders/${orderId}/ship`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Order shipped successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('error', 'Error shipping order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'An error occurred while shipping the order');
        });
    }
}

function downloadInvoice(orderId) {
    window.open(`/plant-manager/orders/${orderId}/invoice`, '_blank');
}

function showNotification(type, message) {
    // You can implement a toast notification here
    // For now, using a simple alert
    if (type === 'success') {
        alert('✓ ' + message);
    } else {
        alert('✗ ' + message);
    }
}
</script>
@endsection
