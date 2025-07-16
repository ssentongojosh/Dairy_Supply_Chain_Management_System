


@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Orders to Wholesalers</h5>
                    <a href="{{ route('retailer.orders.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-2"></i>Place New Order
                    </a>
                </div>
                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>To</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Payment Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">#{{ $order->id }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <img src="{{ $order->seller->profile_photo ?? asset('assets/img/avatars/default.png') }}" 
                                                             alt="Avatar" class="rounded-circle">
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold">{{ $order->seller->name ?? 'Unknown' }}</span>
                                                        <small class="text-muted d-block">{{ ucfirst($order->seller->role?->value ?? '') }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match($order->status) {
                                                        'pending' => 'bg-label-warning',
                                                        'approved' => 'bg-label-primary',
                                                        'processing' => 'bg-label-info',
                                                        'shipped' => 'bg-label-success',
                                                        'delivered' => 'bg-label-success',
                                                        'received' => 'bg-label-success',
                                                        'rejected' => 'bg-label-danger',
                                                        default => 'bg-label-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">UGX {{ number_format($order->total_amount, 0) }}</span>
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
                                            <td>
                                                <span class="fw-semibold">{{ $order->created_at->format('M d, Y') }}</span>
                                                <small class="text-muted d-block">{{ $order->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                            data-bs-toggle="dropdown">
                                                        Actions
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('retailer.orders.show', $order) }}">
                                                            <i class="ri-eye-line me-2"></i>View Details
                                                        </a>
                                                        @if($order->status === 'pending')
                                                            <button class="dropdown-item text-danger" 
                                                                    onclick="cancelOrder({{ $order->id }})">
                                                                <i class="ri-close-line me-2"></i>Cancel Order
                                                            </button>
                                                        @endif
                                                        @if($order->status === 'delivered' && $order->payment_status !== 'paid')
                                                            <a class="dropdown-item text-success" 
                                                               href="{{ route('retailer.orders.payment.show', $order) }}">
                                                                <i class="ri-money-dollar-circle-line me-2"></i>Make Payment
                                                            </a>
                                                        @endif
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="{{ route('retailer.orders.show', $order) }}?print=true" target="_blank">
                                                            <i class="ri-printer-line me-2"></i>Print Invoice
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
                            <p class="text-muted">You haven't placed any orders yet. Start by creating your first order.</p>
                            <a href="{{ route('retailer.orders.create') }}" class="btn btn-primary">
                                <i class="ri-add-line me-2"></i>Place Your First Order
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this order? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep Order</button>
                <form id="cancelForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">Cancel Order</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
        const form = document.getElementById('cancelForm');
        form.action = `/retailer/orders/${orderId}/cancel`;
        form.submit();
    }
}
</script>
@endsection
