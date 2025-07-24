@extends('layouts.contentNavbarLayout')

@section('title', 'Order Details - Wholesaler')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Order Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Order #{{ $order->id }}</h5>
                    <small class="text-muted">from {{ $order->buyer->name }} ({{ ucfirst($order->buyer->role->value ?? 'Customer') }})</small>
                </div>
                <div class="d-flex align-items-center gap-2">
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
                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }} fs-6">
                        {{ ucfirst($order->status) }}
                    </span>
                    @if($order->payment_status)
                        <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }} fs-6">
                            Payment: {{ ucfirst($order->payment_status) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="row">
            <div class="col-md-8">
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
                                                @if($item->product->image)
                                                    <img src="{{ asset('storage/' . $item->product->image) }}" 
                                                         alt="{{ $item->product->name }}"
                                                         class="rounded me-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @endif
                                                <div>
                                                    <h6 class="mb-0">{{ $item->product->name ?? 'Unknown Product' }}</h6>
                                                    <small class="text-muted">{{ $item->product->category ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $item->quantity }} {{ $item->product->unit ?? 'pcs' }}</td>
                                        <td>UGX{{ number_format($item->unit_price, 2) }}</td>
                                        <td>UGX{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Subtotal:</th>
                                    <th>UGX{{ number_format($order->items->sum(fn($item) => $item->quantity * $item->unit_price), 2) }}</th>
                                </tr>
                                @if($order->delivery_fee > 0)
                                <tr>
                                    <th colspan="3" class="text-end">Delivery Fee:</th>
                                    <th>UGX{{ number_format($order->delivery_fee, 2) }}</th>
                                </tr>
                                @endif
                                @if($order->tax_amount > 0)
                                <tr>
                                    <th colspan="3" class="text-end">Tax:</th>
                                    <th>UGX{{ number_format($order->tax_amount, 2) }}</th>
                                </tr>
                                @endif
                                <tr class="table-active">
                                    <th colspan="3" class="text-end">Grand Total:</th>
                                    <th>UGX{{ number_format($order->total_amount, 2) }}</th>



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
                        <div class="timeline">
                            <div class="timeline-item {{ $order->created_at ? 'active' : '' }}">
                                <div class="timeline-indicator">
                                    <i class="ri-shopping-cart-line"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Order Placed</h6>
                                    <p class="text-muted mb-0">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>

                            @if($order->approved_at)
                            <div class="timeline-item active">
                                <div class="timeline-indicator">
                                    <i class="ri-check-line"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Order Approved</h6>
                                    <p class="text-muted mb-0">{{ $order->approved_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                            @endif

                            @if($order->shipped_at)
                            <div class="timeline-item active">
                                <div class="timeline-indicator">
                                    <i class="ri-truck-line"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Order Shipped</h6>
                                    <p class="text-muted mb-0">{{ $order->shipped_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                            @endif

                            @if($order->delivered_at)
                            <div class="timeline-item active">
                                <div class="timeline-indicator">
                                    <i class="ri-check-double-line"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Order Delivered</h6>
                                    <p class="text-muted mb-0">{{ $order->delivered_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                            @endif

                            @if($order->status === 'rejected' && $order->rejected_at)
                            <div class="timeline-item active">
                                <div class="timeline-indicator bg-danger">
                                    <i class="ri-close-line"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Order Rejected</h6>
                                    <p class="text-muted mb-1">{{ $order->rejected_at->format('M d, Y h:i A') }}</p>
                                    @if($order->rejection_reason)
                                        <div class="alert alert-danger alert-sm">
                                            <strong>Reason:</strong> {{ $order->rejection_reason }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                @if($order->notes || $order->special_instructions)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Order Notes</h6>
                    </div>
                    <div class="card-body">
                        @if($order->special_instructions)
                            <div class="mb-3">
                                <strong>Special Instructions:</strong>
                                <p class="text-muted">{{ $order->special_instructions }}</p>
                            </div>
                        @endif
                        @if($order->notes)
                            <div>
                                <strong>Internal Notes:</strong>
                                <p class="text-muted">{{ $order->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-4">
                <!-- Order Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Order Actions</h6>
                    </div>
                    <div class="card-body">
                        @if($order->status === 'pending')
                            <button class="btn btn-success w-100 mb-2" onclick="approveOrder({{ $order->id }})">
                                <i class="ri-check-line me-2"></i>Approve Order
                            </button>
                            <button class="btn btn-danger w-100 mb-2" onclick="rejectOrder({{ $order->id }})">
                                <i class="ri-close-line me-2"></i>Reject Order
                            </button>
                        @endif

                        @if(in_array($order->status, ['approved', 'processing']))
                            <button class="btn btn-primary w-100 mb-2" onclick="markShipped({{ $order->id }})">
                                <i class="ri-truck-line me-2"></i>Mark as Shipped
                            </button>
                        @endif

                        <div class="dropdown w-100">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100" data-bs-toggle="dropdown">
                                <i class="ri-more-2-line me-2"></i>More Actions
                            </button>
                            <ul class="dropdown-menu w-100">
                                <li><a class="dropdown-item" href="{{ route('wholesaler.orders-show', $order) }}?print=true" target="_blank">
                                    <i class="ri-printer-line me-2"></i>Print Order
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('wholesaler.orders-show', $order) }}?download=true">
                                    <i class="ri-download-line me-2"></i>Download PDF
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('wholesaler.orders.history') }}">
                                    <i class="ri-arrow-left-line me-2"></i>Back to Orders
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Customer Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar me-3">
                                <img src="{{ $order->buyer->profile_photo ?? asset('assets/img/avatars/default.png') }}" 
                                     alt="Customer" class="rounded-circle" style="width: 50px; height: 50px;">
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $order->buyer->name }}</h6>
                                <small class="text-muted">{{ ucfirst($order->buyer->role->value ?? 'Customer') }}</small>
                            </div>
                        </div>
                        
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="ri-mail-line me-2 text-muted"></i>
                                <a href="mailto:{{ $order->buyer->email }}">{{ $order->buyer->email }}</a>
                            </li>
                            @if($order->buyer->phone)
                            <li class="mb-2">
                                <i class="ri-phone-line me-2 text-muted"></i>
                                <a href="tel:{{ $order->buyer->phone }}">{{ $order->buyer->phone }}</a>
                            </li>
                            @endif
                            @if($order->buyer->address)
                            <li class="mb-2">
                                <i class="ri-map-pin-line me-2 text-muted"></i>
                                {{ $order->buyer->address }}
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Delivery Information -->
                @if($order->delivery_address || $order->delivery_date)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Delivery Information</h6>
                    </div>
                    <div class="card-body">
                        @if($order->delivery_address)
                            <div class="mb-3">
                                <label class="form-label text-muted">Delivery Address:</label>
                                <p>{{ $order->delivery_address }}</p>
                            </div>
                        @endif
                        @if($order->delivery_date)
                            <div class="mb-3">
                                <label class="form-label text-muted">Requested Delivery Date:</label>
                                <p>{{ \Carbon\Carbon::parse($order->delivery_date)->format('M d, Y') }}</p>
                            </div>
                        @endif
                        @if($order->delivery_fee > 0)
                            <div class="mb-0">
                                <label class="form-label text-muted">Delivery Fee:</label>
                                <p>UGX{{ number_format($order->delivery_fee, 2) }}</p>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Payment Information -->
                @if($order->payment)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Payment Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">Payment Method:</label>
                            <p>{{ ucfirst($order->payment->payment_method ?? 'N/A') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Payment Status:</label>
                            <p>
                                <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($order->payment_status ?? 'pending') }}
                                </span>
                            </p>
                        </div>
                        @if($order->payment->paid_at)
                            <div class="mb-0">
                                <label class="form-label text-muted">Paid At:</label>
                                <p>{{ $order->payment->paid_at->format('M d, Y h:i A') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reject Order Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Order #{{ $order->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <input type="hidden" id="rejectOrderId" name="order_id" value="{{ $order->id }}">
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="3" 
                                  placeholder="Please provide a detailed reason for rejecting this order..." required></textarea>
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

@section('page-style')
<style>
.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -29px;
    top: 30px;
    width: 2px;
    height: calc(100% + 10px);
    background-color: #e5e7eb;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-indicator {
    position: absolute;
    left: -40px;
    top: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background-color: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: #6b7280;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e5e7eb;
}

.timeline-item.active .timeline-indicator {
    background-color: #3b82f6;
    color: white;
    box-shadow: 0 0 0 2px #3b82f6;
}

.timeline-item.active .timeline-indicator.bg-danger {
    background-color: #ef4444;
    box-shadow: 0 0 0 2px #ef4444;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
}
</style>
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
