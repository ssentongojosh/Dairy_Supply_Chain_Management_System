@extends('layouts.contentNavbarLayout')

@section('title', 'Order Details - Plant Manager')

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
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    @if($item->product->image_url)
                                                        <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="rounded">
                                                    @else
                                                        <div class="avatar-initial rounded bg-label-primary">
                                                            <i class="ri-droplet-line"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="fw-medium">{{ $item->product->name }}</span>
                                                    <small class="text-muted d-block">{{ $item->product->category ?? 'Dairy' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ number_format($item->quantity) }} {{ $item->product->unit ?? 'L' }}</td>
                                        <td>UGX {{ number_format($item->unit_price, 0) }}</td>
                                        <td class="fw-medium">UGX {{ number_format($item->unit_price * $item->quantity, 0) }}</td>
                                        <td>
                                            @if($order->status === 'processing')
                                                <span class="badge bg-label-primary">In Production</span>
                                            @elseif($order->status === 'shipped')
                                                <span class="badge bg-label-success">Ready</span>
                                            @elseif($order->status === 'delivered')
                                                <span class="badge bg-label-success">Delivered</span>
                                            @else
                                                <span class="badge bg-label-secondary">{{ ucfirst($order->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-border-bottom-0">
                                <tr>
                                    <th colspan="3" class="text-end">Total Amount:</th>
                                    <th class="fw-bold text-primary">UGX {{ number_format($order->total_amount, 0) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Production Information -->
                @if($order->status === 'processing' || $order->status === 'shipped' || $order->status === 'delivered')
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Production Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Production Schedule</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="ri-calendar-line text-success me-2"></i>
                                        <strong>Started:</strong> {{ $order->production_started_at ? $order->production_started_at->format('M d, Y h:i A') : 'Not started' }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="ri-time-line text-primary me-2"></i>
                                        <strong>Est. Completion:</strong> 
                                        @if($order->production_started_at)
                                            {{ $order->production_started_at->addHours(8)->format('M d, Y h:i A') }}
                                        @else
                                            TBD
                                        @endif
                                    </li>
                                    @if($order->status === 'shipped' && $order->shipped_at)
                                    <li class="mb-2">
                                        <i class="ri-truck-line text-success me-2"></i>
                                        <strong>Shipped:</strong> {{ $order->shipped_at->format('M d, Y h:i A') }}
                                    </li>
                                    @endif
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Quality Control</h6>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ri-shield-check-line text-success me-2"></i>
                                    <span>Quality Approved</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ri-thermometer-line text-info me-2"></i>
                                    <span>Temperature Controlled</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="ri-time-line text-warning me-2"></i>
                                    <span>Fresh Product Guarantee</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Order Timeline -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Order Timeline</h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-point timeline-point-primary"></div>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="timeline-title">Order Placed</h6>
                                        <small class="text-muted">{{ $order->created_at->format('M d, Y h:i A') }}</small>
                                    </div>
                                    <p class="mb-0">Order was placed by {{ $order->buyer->name }}</p>
                                </div>
                            </div>

                            @if($order->status !== 'pending')
                            <div class="timeline-item">
                                <div class="timeline-point timeline-point-info"></div>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="timeline-title">Order {{ $order->status === 'rejected' ? 'Rejected' : 'Approved' }}</h6>
                                        <small class="text-muted">{{ $order->approved_at ? $order->approved_at->format('M d, Y h:i A') : $order->updated_at->format('M d, Y h:i A') }}</small>
                                    </div>
                                    <p class="mb-0">
                                        @if($order->status === 'rejected')
                                            Order was rejected
                                            @if($order->rejection_reason)
                                                <br><em>Reason: {{ $order->rejection_reason }}</em>
                                            @endif
                                        @else
                                            Order was approved and ready for production
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @endif

                            @if($order->status === 'processing' || $order->status === 'shipped' || $order->status === 'delivered')
                            <div class="timeline-item">
                                <div class="timeline-point timeline-point-warning"></div>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="timeline-title">Production Started</h6>
                                        <small class="text-muted">{{ $order->production_started_at ? $order->production_started_at->format('M d, Y h:i A') : 'In Progress' }}</small>
                                    </div>
                                    <p class="mb-0">Order items entered production phase</p>
                                </div>
                            </div>
                            @endif

                            @if($order->status === 'shipped' || $order->status === 'delivered')
                            <div class="timeline-item">
                                <div class="timeline-point timeline-point-success"></div>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="timeline-title">Order Shipped</h6>
                                        <small class="text-muted">{{ $order->shipped_at ? $order->shipped_at->format('M d, Y h:i A') : 'Recently' }}</small>
                                    </div>
                                    <p class="mb-0">Order dispatched for delivery</p>
                                </div>
                            </div>
                            @endif

                            @if($order->status === 'delivered')
                            <div class="timeline-item">
                                <div class="timeline-point timeline-point-success"></div>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="timeline-title">Order Delivered</h6>
                                        <small class="text-muted">{{ $order->delivered_at ? $order->delivered_at->format('M d, Y h:i A') : 'Recently' }}</small>
                                    </div>
                                    <p class="mb-0">Order successfully delivered to customer</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Customer Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Customer Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary fs-4">
                                    {{ substr($order->buyer->name ?? 'U', 0, 2) }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $order->buyer->name ?? 'Unknown Customer' }}</h6>
                                <small class="text-muted">{{ ucfirst($order->buyer->role->value ?? 'Customer') }}</small>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="ri-mail-line text-primary me-2"></i>
                                <span>{{ $order->buyer->email ?? 'N/A' }}</span>
                            </li>
                            <li class="mb-2">
                                <i class="ri-phone-line text-primary me-2"></i>
                                <span>{{ $order->buyer->phone ?? 'N/A' }}</span>
                            </li>
                            @if($order->delivery_address)
                            <li class="mb-2">
                                <i class="ri-map-pin-line text-primary me-2"></i>
                                <span>{{ $order->delivery_address }}</span>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Payment Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>UGX {{ number_format($order->total_amount - ($order->tax_amount ?? 0), 0) }}</span>
                        </div>
                        @if($order->tax_amount)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax ({{ $order->tax_rate ?? 18 }}%):</span>
                            <span>UGX {{ number_format($order->tax_amount, 0) }}</span>
                        </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span class="text-primary">UGX {{ number_format($order->total_amount, 0) }}</span>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-label-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }} w-100">
                                Payment {{ ucfirst($order->payment_status ?? 'pending') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Order Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($order->status === 'pending')
                                <button type="button" class="btn btn-success" onclick="approveOrder({{ $order->id }})">
                                    <i class="ri-check-line me-2"></i>Approve Order
                                </button>
                                <button type="button" class="btn btn-danger" onclick="rejectOrder({{ $order->id }})">
                                    <i class="ri-close-line me-2"></i>Reject Order
                                </button>
                            @elseif($order->status === 'approved')
                                <button type="button" class="btn btn-primary" onclick="startProduction({{ $order->id }})">
                                    <i class="ri-settings-2-line me-2"></i>Start Production
                                </button>
                            @elseif($order->status === 'processing')
                                <button type="button" class="btn btn-success" onclick="shipOrder({{ $order->id }})">
                                    <i class="ri-truck-line me-2"></i>Ship Order
                                </button>
                            @endif
                            
                            @if(in_array($order->status, ['shipped', 'delivered']))
                                <button type="button" class="btn btn-outline-primary" onclick="downloadInvoice({{ $order->id }})">
                                    <i class="ri-download-line me-2"></i>Download Invoice
                                </button>
                            @endif
                            
                            <a href="{{ route('plant_manager.orders.history') }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-2"></i>Back to Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
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

// Auto-refresh for processing orders
@if($order->status === 'processing')
setInterval(() => {
    // Check if order status has changed
    fetch(`/plant-manager/orders/{{ $order->id }}/status`)
        .then(response => response.json())
        .then(data => {
            if (data.status !== '{{ $order->status }}') {
                location.reload();
            }
        })
        .catch(error => console.error('Status check error:', error));
}, 30000); // Check every 30 seconds
@endif
</script>

<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -1.75rem;
    top: 1.5rem;
    height: calc(100% + 1rem);
    width: 2px;
    background-color: #e7eef7;
}

.timeline-point {
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    background-color: #fff;
    border: 2px solid #e7eef7;
}

.timeline-point-primary {
    border-color: #696cff;
    background-color: #696cff;
}

.timeline-point-info {
    border-color: #03c3ec;
    background-color: #03c3ec;
}

.timeline-point-warning {
    border-color: #ffab00;
    background-color: #ffab00;
}

.timeline-point-success {
    border-color: #71dd37;
    background-color: #71dd37;
}

.timeline-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.timeline-title {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
}
</style>
@endsection
