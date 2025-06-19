@extends('layouts/contentNavbarLayout')

@section('title', 'Order History')

@section('content')
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Order History</h4>

                    </div>

                    <!-- Filters -->
                    <div class="card-body">
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
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
                                <label class="form-label">Search Supplier</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Supplier name..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                            <div class="mb-3">
                                <a href="{{ route('app-order') }}" class="btn btn-outline-secondary btn-sm">
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
                                    <th>Supplier</th>
                                    <th>Items</th>
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
                                                        {{ substr($order->seller->name ?? 'N/A', 0, 2) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="fw-medium">{{ $order->seller->name ?? 'N/A' }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $order->seller->email ?? '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                @foreach($order->items->take(2) as $item)
                                                    <small class="text-muted">
                                                        {{ $item->product->name ?? 'Product' }} ({{ $item->quantity }})
                                                    </small>
                                                @endforeach
                                                @if($order->items->count() > 2)
                                                    <small class="text-muted">+{{ $order->items->count() - 2 }} more items</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium">
                                                ${{ number_format($order->total_amount ?? 0, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                    @break
                                                @case('pending_review')
                                                    <span class="badge bg-warning">Pending Review</span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge bg-success">Approved</span>
                                                    @break
                                                @case('processing')
                                                    <span class="badge bg-info">Processing</span>
                                                    @break
                                                @case('shipped')
                                                    <span class="badge bg-primary">Shipped</span>
                                                    @break
                                                @case('delivered')
                                                @case('received')
                                                    <span class="badge bg-success">Received</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Cancelled</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                            @endswitch

                                            <!-- Payment Status -->
                                            @if(isset($order->payment_status) && $order->payment_status !== 'unpaid')
                                                <br><small class="text-muted">
                                                    @switch($order->payment_status)
                                                        @case('pending_verification')
                                                            <i class="ri-time-line"></i> Payment Pending
                                                            @break
                                                        @case('paid')
                                                            <i class="ri-check-line text-success"></i> Paid
                                                            @break
                                                        @case('failed')
                                                            <i class="ri-close-line text-danger"></i> Payment Failed
                                                            @break
                                                    @endswitch
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $order->created_at->format('M d, Y') }}</span>
                                            <br>
                                            <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('retailer.orders.show', $order->id) }}">
                                                            <i class="ri-eye-line me-2"></i>View Details
                                                        </a>
                                                    </li>
                                                    @if($order->status === 'shipped')
                                                        <li>
                                                            <form action="{{ route('retailer.orders.received', $order->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-success">
                                                                    <i class="ri-check-line me-2"></i>Mark as Received
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    @if(in_array($order->status, ['pending', 'processing']))
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" onclick="cancelOrder({{ $order->id }})">
                                                                <i class="ri-close-line me-2"></i>Cancel Order
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ri-shopping-cart-line mb-2" style="font-size: 3rem; color: #ccc;"></i>
                                                <h5 class="text-muted">No orders found</h5>
                                                <p class="text-muted">You haven't placed any orders yet.</p>
                                                <a href="{{ route('retailer.dashboard') }}" class="btn btn-primary">
                                                    Browse Products
                                                </a>
                                            </div>
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
                                {{ $orders->withQueryString()->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- / Content -->
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar me-3">
                        <div class="avatar-initial bg-warning rounded" id="confirmationIcon">
                            <i class="ri-question-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0" id="confirmationMessage">Are you sure you want to proceed?</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmationAction">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationTitle">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar me-3">
                        <div class="avatar-initial rounded" id="notificationIcon">
                            <i class="ri-information-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0" id="notificationMessage">Operation completed successfully!</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
// Custom notification function
function showNotification(title, message, type = 'info', icon = 'ri-information-line') {
    const modal = document.getElementById('notificationModal');
    const titleEl = document.getElementById('notificationTitle');
    const messageEl = document.getElementById('notificationMessage');
    const iconEl = document.getElementById('notificationIcon');

    titleEl.textContent = title;
    messageEl.textContent = message;

    // Set icon and color based on type
    const bgClass = type === 'danger' ? 'bg-danger' :
                   type === 'success' ? 'bg-success' :
                   type === 'warning' ? 'bg-warning' : 'bg-info';

    iconEl.className = `avatar-initial rounded ${bgClass}`;
    iconEl.innerHTML = `<i class="${icon}"></i>`;

    const notificationModal = new bootstrap.Modal(modal);
    notificationModal.show();
}

// Custom confirmation function
function showConfirmation(title, message, onConfirm, confirmText = 'Confirm', confirmClass = 'btn-primary') {
    const modal = document.getElementById('confirmationModal');
    const titleEl = document.getElementById('confirmationTitle');
    const messageEl = document.getElementById('confirmationMessage');
    const actionBtn = document.getElementById('confirmationAction');
    const iconEl = document.getElementById('confirmationIcon');

    titleEl.textContent = title;
    messageEl.textContent = message;
    actionBtn.textContent = confirmText;
    actionBtn.className = `btn ${confirmClass}`;

    // Update icon based on action type
    if (confirmClass === 'btn-danger') {
        iconEl.className = 'avatar-initial bg-danger rounded';
        iconEl.innerHTML = '<i class="ri-error-warning-line"></i>';
    } else {
        iconEl.className = 'avatar-initial bg-warning rounded';
        iconEl.innerHTML = '<i class="ri-question-line"></i>';
    }

    const confirmModal = new bootstrap.Modal(modal);
    confirmModal.show();

    // Handle confirmation
    actionBtn.onclick = function() {
        confirmModal.hide();
        if (onConfirm) onConfirm();
    };
}

function cancelOrder(orderId) {
    showConfirmation(
        'Cancel Order',
        'Are you sure you want to cancel this order? This action cannot be undone.',
        function() {
            fetch(`/retailer/order/${orderId}/cancel`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Success!', 'Order cancelled successfully!', 'success', 'ri-check-line');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification('Error', 'Error cancelling order: ' + data.message, 'danger', 'ri-error-warning-line');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error', 'Error cancelling order. Please try again.', 'danger', 'ri-error-warning-line');
            });
        },
        'Cancel Order',
        'btn-danger'
    );
}
</script>
@endsection
