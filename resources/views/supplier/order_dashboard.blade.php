@extends('layouts.contentNavbarLayout')

@section('title', 'Orders Dashboard - Supplier')

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
                            <h5 class="card-title mb-0">₱{{ number_format($orderStats['total_revenue'] ?? 0, 2) }}</h5>
                            <small class="text-muted">Total Revenue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('supplier.orders.history') }}" class="btn btn-outline-primary w-100">
                            <i class="ri-history-line me-2"></i>View All Orders
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('supplier.inventory') }}" class="btn btn-outline-success w-100">
                            <i class="ri-archive-line me-2"></i>Manage Inventory
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('supplier.dashboard') }}" class="btn btn-outline-info w-100">
                            <i class="ri-dashboard-line me-2"></i>Main Dashboard
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <button class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="ri-download-line me-2"></i>Export Orders
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Orders Requiring Action -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Orders Requiring Action</h5>
                <small class="text-muted">{{ $activeOrders->count() }} orders</small>
            </div>
            <div class="card-body">
                @if($activeOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>                                @foreach($activeOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('supplier.orders.show', $order) }}" class="fw-semibold">
                                            #{{ $order->id }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <img src="{{ $order->buyer->profile_photo ?? asset('assets/img/avatars/default.png') }}" 
                                                     alt="Avatar" class="rounded-circle">
                                            </div>
                                            <div>
                                                <span class="fw-semibold">{{ $order->buyer->name ?? 'Unknown' }}</span>
                                                <small class="text-muted d-block">{{ $order->buyer->email ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $order->created_at->format('M d, Y') }}</span>
                                        <small class="text-muted d-block">{{ $order->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ $order->items->count() }} items</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">₱{{ number_format($order->total_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($order->status) {
                                                'pending' => 'bg-label-warning',
                                                'approved' => 'bg-label-primary',
                                                'processing' => 'bg-label-info',
                                                'shipped' => 'bg-label-success',
                                                'delivered' => 'bg-label-success',
                                                'rejected' => 'bg-label-danger',
                                                default => 'bg-label-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                    data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('supplier.orders.show', $order) }}">
                                                    <i class="ri-eye-line me-2"></i>View Details
                                                </a>
                                                @if($order->status === 'pending')
                                                    <button class="dropdown-item text-success" 
                                                            onclick="approveOrder({{ $order->id }})">
                                                        <i class="ri-check-line me-2"></i>Approve
                                                    </button>
                                                    <button class="dropdown-item text-danger" 
                                                            onclick="rejectOrder({{ $order->id }})">
                                                        <i class="ri-close-line me-2"></i>Reject
                                                    </button>
                                                @endif
                                                @if(in_array($order->status, ['approved', 'processing']))
                                                    <button class="dropdown-item text-primary" 
                                                            onclick="markShipped({{ $order->id }})">
                                                        <i class="ri-truck-line me-2"></i>Mark as Shipped
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $activeOrders->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ri-inbox-line fs-1 text-muted"></i>
                        <h6 class="mt-2">No orders requiring action</h6>
                        <p class="text-muted">All your orders are up to date!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Orders</h5>
                <a href="{{ route('supplier.orders.history') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('supplier.orders.show', $order) }}" class="fw-semibold">
                                            #{{ $order->id }}
                                        </a>
                                    </td>
                                    <td>{{ $order->buyer->name ?? 'Unknown' }}</td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-label-info">{{ $order->items->count() }} items</span>
                                    </td>
                                    <td>₱{{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        @php
                                            $statusClass = match($order->status) {
                                                'pending' => 'bg-label-warning',
                                                'approved' => 'bg-label-primary',
                                                'processing' => 'bg-label-info',
                                                'shipped' => 'bg-label-success',
                                                'delivered' => 'bg-label-success',
                                                'rejected' => 'bg-label-danger',
                                                default => 'bg-label-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                                    </td>
                                    <td>                                        <a href="{{ route('supplier.orders.show', $order) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ri-inbox-line fs-1 text-muted"></i>
                        <h6 class="mt-2">No orders yet</h6>
                        <p class="text-muted">Orders will appear here once customers start placing them.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <div class="mb-3">
                        <label class="form-label">Date Range</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="date" class="form-control" name="from_date" placeholder="From">
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control" name="to_date" placeholder="To">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select class="form-select" name="format">
                            <option value="csv">CSV</option>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="exportOrders()">Export</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Order Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <input type="hidden" id="rejectOrderId" name="order_id">
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection</label>
                        <textarea class="form-control" name="reason" rows="3" 
                                  placeholder="Please provide a reason for rejecting this order..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmRejectOrder()">Reject Order</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
function approveOrder(orderId) {
    if (confirm('Are you sure you want to approve this order?')) {
        fetch(`/supplier/orders/${orderId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to approve order');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while approving the order');
        });
    }
}

function rejectOrder(orderId) {
    document.getElementById('rejectOrderId').value = orderId;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmRejectOrder() {
    const form = document.getElementById('rejectForm');
    const formData = new FormData(form);
    const orderId = formData.get('order_id');
    const reason = formData.get('reason');

    if (!reason.trim()) {
        alert('Please provide a reason for rejection');
        return;
    }

    fetch(`/supplier/orders/${orderId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Failed to reject order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while rejecting the order');
    });
}

function markShipped(orderId) {
    if (confirm('Are you sure you want to mark this order as shipped?')) {
        fetch(`/supplier/orders/${orderId}/ship`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to mark order as shipped');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the order');
        });
    }
}

function exportOrders() {
    const form = document.getElementById('exportForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    window.open(`/supplier/orders/export?${params.toString()}`, '_blank');
    bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
}
</script>
@endsection
