@extends('layouts.contentNavbarLayout')

@section('title', 'Order Details - Wholesaler')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Order #{{ $order->id }}</h5>
                    <small class="text-muted">From {{ $order->buyer->name }}</small>
                </div>
                <a href="{{ route('wholesaler.orders') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> Back to Orders
                </a>
            </div>

            <div class="card-body">
                <div class="row">
                    <!-- Order Info -->
                    <div class="col-md-6">
                        <h6>Order Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Order ID:</strong></td>
                                <td>#{{ $order->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'approved' => 'info',
                                            'processing' => 'primary',
                                            'shipped' => 'success',
                                            'received' => 'success',
                                            'rejected' => 'danger',
                                            'cancelled' => 'secondary'
                                        ];
                                    @endphp
                                    <span class="badge bg-label-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Payment Status:</strong></td>
                                <td>
                                    @if($order->payment_status)
                                        <span class="badge bg-label-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Order Date:</strong></td>
                                <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                            @if($order->approved_at)
                            <tr>
                                <td><strong>Approved At:</strong></td>
                                <td>{{ $order->approved_at->format('M d, Y h:i A') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>

                    <!-- Retailer Info -->
                    <div class="col-md-6">
                        <h6>Retailer Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $order->buyer->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $order->buyer->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Role:</strong></td>
                                <td>
                                    <span class="badge bg-label-primary">{{ ucfirst($order->buyer->role) }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <!-- Order Items -->
                <h6>Order Items</h6>
                <div class="table-responsive">
                    <table class="table">
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
                                    <div>
                                        <span class="fw-medium">{{ $item->product->name }}</span>
                                        @if($item->product->sku)
                                            <small class="text-muted d-block">SKU: {{ $item->product->sku }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                                <td>UGX {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Total Amount:</th>
                                <th>UGX {{ number_format($order->items->sum(fn($item) => $item->quantity * $item->unit_price), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <hr>

                <!-- Actions -->
                <div class="d-flex gap-2">
                    @if($order->status == 'pending' || $order->status == 'pending_review')
                        <form action="{{ route('wholesaler.orders.approve', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="ri-check-line me-1"></i> Approve Order
                            </button>
                        </form>
                        <form action="{{ route('wholesaler.orders.reject', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="ri-close-line me-1"></i> Reject Order
                            </button>
                        </form>
                    @endif

                    @if($order->status == 'processing' && $order->payment_status == 'paid')
                        <form action="{{ route('wholesaler.orders.ship', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-truck-line me-1"></i> Mark as Shipped
                            </button>
                        </form>
                    @endif
                </div>

                @if($order->latestPayment)
                <hr>
                <h6>Payment Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Payment Method:</strong></td>
                                <td>{{ ucfirst($order->latestPayment->method) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Amount:</strong></td>
                                <td>UGX {{ number_format($order->latestPayment->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge bg-label-{{ $order->latestPayment->status == 'verified' ? 'success' : 'warning' }}">
                                        {{ ucfirst($order->latestPayment->status) }}
                                    </span>
                                </td>
                            </tr>
                            @if($order->latestPayment->transaction_id)
                            <tr>
                                <td><strong>Transaction ID:</strong></td>
                                <td>{{ $order->latestPayment->transaction_id }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
