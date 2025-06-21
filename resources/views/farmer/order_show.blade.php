@extends('layouts.contentNavbarLayout')

@section('title', 'Order Details - Farmer')

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
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-border-bottom-0">
                                <tr>
                                    <th colspan="3" class="text-end">Total Amount:</th>
                                    <th class="fw-bold text-primary">UGX {{ number_format($order->total_amount, 0) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Buyer Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Buyer Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    {{ substr($order->buyer->name, 0, 2) }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $order->buyer->name }}</h6>
                                <small class="text-muted">{{ ucfirst($order->buyer->role->value ?? 'Customer') }}</small>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Email:</small>
                            <div>{{ $order->buyer->email }}</div>
                        </div>
                        @if($order->buyer->phone)
                            <div class="mb-2">
                                <small class="text-muted">Phone:</small>
                                <div>{{ $order->buyer->phone }}</div>
                            </div>
                        @endif
                        @if($order->delivery_address)
                            <div class="mb-2">
                                <small class="text-muted">Delivery Address:</small>
                                <div>{{ $order->delivery_address }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Order Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Order Date:</span>
                            <span>{{ $order->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Items:</span>
                            <span>{{ $order->items->count() }} products</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Quantity:</span>
                            <span>{{ $order->items->sum('quantity') }} units</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total Amount:</span>
                            <span>UGX {{ number_format($order->total_amount, 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Order Timeline</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar me-3">
                                <div class="avatar-initial bg-success rounded">
                                    <i class="ri-shopping-cart-line"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0">Order Received</h6>
                                <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                    </div>
                    @if(in_array($order->status, ['approved', 'processing', 'shipped', 'delivered']))
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar me-3">
                                    <div class="avatar-initial bg-info rounded">
                                        <i class="ri-check-line"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Approved</h6>
                                    <small class="text-muted">Order accepted</small>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(in_array($order->status, ['processing', 'shipped', 'delivered']))
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar me-3">
                                    <div class="avatar-initial bg-primary rounded">
                                        <i class="ri-settings-line"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Processing</h6>
                                    <small class="text-muted">Preparing order</small>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(in_array($order->status, ['shipped', 'delivered']))
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar me-3">
                                    <div class="avatar-initial bg-success rounded">
                                        <i class="ri-truck-line"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $order->status == 'delivered' ? 'Delivered' : 'Shipped' }}</h6>
                                    <small class="text-muted">{{ $order->updated_at->format('M d, Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if(in_array($order->status, ['pending', 'approved']))
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">Order Actions</h6>
                </div>
                <div class="card-body">
                    @if($order->status === 'pending')
                        <form action="{{ route('farmer.orders.approve', $order) }}" method="POST" class="d-inline me-2">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="ri-check-line me-1"></i>Approve Order
                            </button>
                        </form>
                        <form action="{{ route('farmer.orders.reject', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this order?')">
                                <i class="ri-close-line me-1"></i>Reject Order
                            </button>
                        </form>
                    @endif

                    @if($order->status === 'approved')
                        <form action="{{ route('farmer.orders.ship', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-truck-line me-1"></i>Mark as Shipped
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        <!-- Back to Orders -->
        <div class="mb-4">
            <a href="{{ route('farmer.orders') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i>Back to Orders
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        fetch(`/farmer/orders/${orderId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error cancelling order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the order.');
        });
    }
}
</script>
@endpush
@endsection
