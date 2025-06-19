@extends('layouts.contentNavbarLayout')

@section('title', 'Wholesaler Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Incoming Orders from Retailers</h5>
                <span class="badge bg-info">{{ $incomingOrders->count() }} Orders</span>
            </div>
            <div class="card-body">
                @if($incomingOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Retailer</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incomingOrders as $order)
                                    <tr>
                                        <td><strong>#{{ $order->id }}</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        {{ substr($order->buyer->name, 0, 2) }}
                                                    </span>
                                                </div>
                                                <span>{{ $order->buyer->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <small>
                                                @foreach($order->items as $item)
                                                    {{ $item->product->name }} ({{ $item->quantity }}x)
                                                    @if(!$loop->last), @endif
                                                @endforeach
                                            </small>
                                        </td>
                                        <td>
                                            <strong>${{ number_format($order->total_amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                    @break
                                                @case('pending_review')
                                                    <span class="badge bg-secondary">Needs Review</span>
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
                                                @case('rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light">{{ ucfirst($order->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @switch($order->payment_status)
                                                @case('unpaid')
                                                    <span class="badge bg-secondary">Unpaid</span>
                                                    @break
                                                @case('pending_verification')
                                                    <span class="badge bg-warning">Pending Verification</span>
                                                    @break
                                                @case('paid')
                                                    <span class="badge bg-success">Paid</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light">{{ ucfirst($order->payment_status ?? 'unpaid') }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <small>{{ $order->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if($order->status === 'pending_review')
                                                        <li>
                                                            <form action="{{ route('wholesaler.orders.approve', $order) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button class="dropdown-item text-success" type="submit">
                                                                    <i class="ri-check-line me-2"></i>Approve Order
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('wholesaler.orders.reject', $order) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="ri-close-line me-2"></i>Reject Order
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    @if($order->status === 'processing' && $order->payment_status === 'paid')
                                                        <li>
                                                            <form action="{{ route('wholesaler.orders.ship', $order) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button class="dropdown-item text-primary" type="submit">
                                                                    <i class="ri-truck-line me-2"></i>Mark as Shipped
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    @if($order->payment_status === 'pending_verification')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('payments.verify.form', $order) }}">
                                                                <i class="ri-shield-check-line me-2"></i>Verify Payment
                                                            </a>
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
                @else
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <div class="avatar-initial bg-label-secondary rounded">
                                <i class="ri-shopping-cart-2-line display-4"></i>
                            </div>
                        </div>
                        <h6 class="mb-1">No Orders Yet</h6>
                        <p class="text-muted">You haven't received any orders from retailers yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
