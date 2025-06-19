@extends('layouts.contentNavbarLayout')

@section('title', 'Order Details')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Order Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Order #{{ $order->id }}</h5>
                    <small class="text-muted">to {{ $order->seller->name }}</small>
                </div>
                <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'shipped' ? 'primary' : 'success') }} fs-6">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Order Items</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ substr($item->product->name, 0, 2) }}
                                            </span>
                                        </div>
                                        <span class="fw-medium">{{ $item->product->name }}</span>
                                    </div>
                                </td>
                                <td>{{ number_format($item->quantity) }}</td>
                                <td>${{ number_format($item->unit_price, 2) }}</td>
                                <td class="fw-medium">${{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-border-bottom-0">
                        <tr>
                            <th colspan="3" class="text-end">Total Amount:</th>
                            <th class="fw-bold text-primary">${{ number_format($order->total_amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Order Timeline</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar me-3">
                                <div class="avatar-initial bg-success rounded">
                                    <i class="ri-shopping-cart-line"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0">Order Placed</h6>
                                <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @if($order->status === 'shipped' || $order->status === 'received')
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar me-3">
                                    <div class="avatar-initial bg-primary rounded">
                                        <i class="ri-truck-line"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Shipped</h6>
                                    <small class="text-muted">{{ $order->updated_at->format('M d, Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($order->status === 'received')
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar me-3">
                                    <div class="avatar-initial bg-success rounded">
                                        <i class="ri-check-line"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Received</h6>
                                    <small class="text-muted">{{ $order->updated_at->format('M d, Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if(in_array($order->status, ['pending', 'pending_review', 'approved', 'processing', 'shipped']))
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">Order Actions</h6>
                </div>
                <div class="card-body">
                    @if(in_array($order->status, ['pending', 'pending_review']))
                        <button type="button" class="btn btn-danger me-2" onclick="cancelOrder({{ $order->id }})">
                            <i class="ri-close-line me-1"></i>Cancel Order
                        </button>
                    @endif

                    @if($order->status === 'approved' && $order->payment_status === 'unpaid')
                        <a href="{{ route('retailer.orders.payment', $order) }}" class="btn btn-success me-2">
                            <i class="ri-secure-payment-line me-1"></i>Make Payment
                        </a>
                        <small class="text-muted d-block mt-2">
                            <i class="ri-information-line me-1"></i>
                            Payment required to proceed with order processing
                            @if($order->payment_due_date)
                                (Due: {{ $order->payment_due_date->format('M d, Y') }})
                            @endif
                        </small>
                    @endif

                    @if($order->payment_status === 'pending_verification')
                        <div class="alert alert-warning mb-2">
                            <i class="ri-time-line me-2"></i>Payment submitted and awaiting verification
                        </div>
                    @endif
                    @if($order->status === 'shipped')
                        <form action="{{ route('retailer.orders.received', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="ri-check-line me-1"></i>Mark as Received
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        <!-- Payment Information -->
        @if($order->payment_status === 'unpaid' && $order->latestPayment?->status === 'pending')
            <div class="card mb-4">
                <div class="card-body">
                    <div class="alert alert-warning d-flex align-items-center">
                        <div class="avatar me-3">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-alert-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Payment Verification Required</h6>
                            <p class="mb-2">Awaiting payment verification for this order.</p>
                            <a href="{{ route('payments.verify.form', $order) }}"
                               class="btn btn-sm btn-primary">
                                <i class="ri-check-circle-line me-1"></i>Verify Payment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Navigation -->
        <div class="d-flex justify-content-between">
            <a href="{{ route('retailer.orders') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i>Back to Orders
            </a>
            @if($order->notes)
                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#notesModal">
                    <i class="ri-file-text-line me-1"></i>View Notes
                </button>
            @endif
        </div>
    </div>
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

<!-- Notes Modal -->
@if($order->notes)
<div class="modal fade" id="notesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">{{ $order->notes }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

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
                        window.location.href = '{{ route("retailer.orders") }}';
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
