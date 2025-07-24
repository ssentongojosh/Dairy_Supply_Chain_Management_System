@extends('layouts.contentNavbarLayout')

@section('title', 'Order History - Wholesaler')

@section('content')
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Order History</h4>
                        <p class="text-muted mb-0">Track your sales orders to plant managers and other customers</p>
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
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                                <label class="form-label">Search Customer</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Customer name..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                            <div class="mb-3">
                                <a href="{{ route('wholesaler.orders.history') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="ri-close-line"></i> Clear Filters
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Statistics Summary -->
                    <div class="card-body border-top">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-3 bg-label-primary">
                                        <i class="ri-shopping-cart-line"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $stats['total_orders'] ?? 0 }}</h6>
                                        <small class="text-muted">Total Orders</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-3 bg-label-warning">
                                        <i class="ri-time-line"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $stats['pending_orders'] ?? 0 }}</h6>
                                        <small class="text-muted">Pending Orders</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-3 bg-label-success">
                                        <i class="ri-check-line"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $stats['completed_orders'] ?? 0 }}</h6>
                                        <small class="text-muted">Completed</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-3 bg-label-info">
                                        <i class="ri-money-dollar-circle-line"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">UGX {{ number_format($stats['total_revenue'] ?? 0, 2) }}</h6>
                                        <small class="text-muted">Total Revenue</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Table -->
                    <div class="card-body border-top">
                        @if($orders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total Amount</th>
                                            <th>Status</th>
                                            <th>Payment Status</th>
                                            <th>Address</th>
                                            <th>Payment Method</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('wholesaler.orders.show', $order) }}" class="fw-semibold text-primary">
                                                    #{{ $order->id }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if(isset($order->buyer) && $order->buyer->avatar && Storage::disk('public')->exists($order->buyer->avatar))
  <img src="{{ Storage::url($order->buyer->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
@elseif(isset($order->buyer) && $order->buyer->name)
  <span class="avatar-initial rounded-circle bg-label-primary" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
    {{ strtoupper(substr($order->buyer->name, 0, 1)) }}{{ strtoupper(substr(strstr($order->buyer->name, ' '), 1, 1)) }}
  </span>
@else
  <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
@endif
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
                                                <div class="mt-1">
                                                    @foreach($order->items->take(2) as $item)
                                                        <small class="text-muted d-block">{{ $item->product->name ?? 'Unknown Product' }}</small>
                                                    @endforeach
                                                    @if($order->items->count() > 2)
                                                        <small class="text-muted">+{{ $order->items->count() - 2 }} more</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">UGX {{ number_format($order->total_amount, 2) }}</span>
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
                                                        'cancelled' => 'bg-label-secondary',
                                                        default => 'bg-label-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                                                @if($order->shipped_at)
                                                    <small class="text-muted d-block">
                                                        Shipped: {{ $order->shipped_at->format('M d') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $paymentClass = match($order->payment_status ?? 'pending') {
                                                        'paid' => 'bg-label-success',
                                                        'pending' => 'bg-label-warning',
                                                        'failed' => 'bg-label-danger',
                                                        'refunded' => 'bg-label-info',
                                                        default => 'bg-label-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $paymentClass }}">
                                                    {{ ucfirst($order->payment_status ?? 'pending') }}
                                                </span>
                                            </td>
                                            <td>{{ $order->address ?? 'N/A' }}</td>
                                            <td>{{ ucfirst($order->payment_method ?? 'N/A') }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                        Actions
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('wholesaler.orders.show', $order) }}">
                                                            <i class="ri-eye-line me-2"></i>View Details
                                                        </a>
                                                        @if($order->status === 'pending' || $order->status === 'pending_review')
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
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="{{ route('wholesaler.orders.show', $order) }}?print=true" target="_blank">
                                                            <i class="ri-printer-line me-2"></i>Print Invoice
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('wholesaler.orders.show', $order) }}?download=true">
                                                            <i class="ri-download-line me-2"></i>Download PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $orders->withQueryString()->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="ri-inbox-line fs-1 text-muted"></i>
                                <h6 class="mt-2">No orders found</h6>
                                <p class="text-muted">
                                    @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                                        No orders match your current filters. Try adjusting your search criteria.
                                    @else
                                        You haven't received any orders yet. Orders will appear here once customers start placing them.
                                    @endif
                                </p>
                                @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                                    <a href="{{ route('wholesaler.orders.history') }}" class="btn btn-outline-primary">
                                        <i class="ri-refresh-line me-2"></i>Clear Filters
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
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
                        <div class="form-text">This reason will be shared with the customer.</div>
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
        fetch(`/wholesaler/orders/${orderId}/approve`, {
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

    fetch(`/wholesaler/orders/${orderId}/reject`, {
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
        fetch(`/wholesaler/orders/${orderId}/ship`, {
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
</script>
@endsection
