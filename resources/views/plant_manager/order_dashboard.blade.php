@extends('layouts.contentNavbarLayout')

@section('title', 'Orders Dashboard - Plant Manager')

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
                            <i class="ri-settings-2-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $orderStats['in_production'] ?? 0 }}</h5>
                            <small class="text-muted">In Production</small>
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
                            <i class="ri-truck-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $orderStats['shipped_orders'] ?? 0 }}</h5>
                            <small class="text-muted">Shipped Orders</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Production Overview -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Production Queue</h5>
                    <small class="text-muted">Orders waiting for production</small>
                </div>
                <a href="{{ route('plant_manager.inventory') }}" class="btn btn-outline-primary btn-sm">
                    <i class="ri-factory-line me-1"></i> Manage Production
                </a>
            </div>
            <div class="card-body">
                @if($productionQueue->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($productionQueue->take(5) as $order)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-0">Order #{{ $order->id }}</h6>
                                    <small class="text-muted">{{ $order->items->count() }} items • {{ $order->buyer->name }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-label-warning">{{ ucfirst($order->status) }}</span>
                                    <small class="text-muted d-block">{{ $order->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="ri-inbox-line fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No orders in production queue</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Recent Orders</h5>
                    <small class="text-muted">Latest incoming orders</small>
                </div>
                <a href="{{ route('plant_manager.orders.history') }}" class="btn btn-outline-primary btn-sm">
                    <i class="ri-list-check me-1"></i> View All Orders
                </a>
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentOrders->take(5) as $order)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-0">Order #{{ $order->id }}</h6>
                                    <small class="text-muted">{{ $order->buyer->name }} • UGX {{ number_format($order->total_amount, 0) }}</small>
                                </div>
                                <div class="text-end">
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
                                    <small class="text-muted d-block">{{ $order->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="ri-inbox-line fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No recent orders</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Active Orders</h5>
                    <small class="text-muted">Orders requiring action</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="ri-filter-line me-1"></i> Filter
                    </button>
                    <a href="{{ route('plant_manager.orders.history') }}" class="btn btn-primary btn-sm">
                        <i class="ri-history-line me-1"></i> View History
                    </a>
                </div>
            </div>

            <div class="card-body">
                @if($activeOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Order Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeOrders as $order)
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
                                                <span class="fw-medium">{{ $order->buyer->name ?? 'Unknown Customer' }}</span>
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
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($activeOrders->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $activeOrders->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="ri-inbox-line fs-1 text-muted"></i>
                        <h5 class="text-muted mt-3">No active orders</h5>
                        <p class="text-muted">All orders are up to date!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Type</label>
                            <select name="customer_type" class="form-select">
                                <option value="">All Customers</option>
                                <option value="retailer" {{ request('customer_type') === 'retailer' ? 'selected' : '' }}>Retailers</option>
                                <option value="wholesaler" {{ request('customer_type') === 'wholesaler' ? 'selected' : '' }}>Wholesalers</option>
                                <option value="farmer" {{ request('customer_type') === 'farmer' ? 'selected' : '' }}>Farmers</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
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
                location.reload();
            } else {
                alert('Error approving order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while approving the order');
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
                location.reload();
            } else {
                alert('Error rejecting order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while rejecting the order');
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
                location.reload();
            } else {
                alert('Error starting production: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while starting production');
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
                location.reload();
            } else {
                alert('Error shipping order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while shipping the order');
        });
    }
}
</script>
@endsection
