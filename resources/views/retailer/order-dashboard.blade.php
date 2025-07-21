@extends('layouts.contentNavbarLayout')

@section('title', 'Orders My Orders ')

@section('content')
<!-- Recent Orders -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Recent Orders</h5>
                    <small class="text-muted">Your latest customer orders</small>
                </div>
                <a href="{{ route('retailer.orders') }}" class="btn btn-outline-primary btn-sm">
                    <i class="ri-external-link-line me-1"></i>View All
                </a>
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentOrders as $order)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-0">Order #{{ $order->id }}</h6>
                                    <small class="text-muted">{{ $order->buyer->name ?? 'Unknown Customer' }}</small>
                                </div>
                                <div class="text-end">
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'approved' => 'info',
                                            'processing' => 'primary',
                                            'shipped' => 'success',
                                            'delivered' => 'success',
                                            'rejected' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                    <small class="text-muted d-block">UGX {{ number_format($order->total_amount, 0) }}</small>
                                    @if ($order->status === 'approved' && $order->payment_status === 'unpaid')
                                        <a href="{{ route('retailer.orders.payment.show', $order->id) }}" class="btn btn-sm btn-primary mt-1">Pay</a>
                                    @endif
                                    @if ($order->payment_status === 'unpaid')
                                        <form action="{{ route('retailer.orders.cancel', $order->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger mt-1" onclick="return confirm('Are you sure you want to cancel this order?');">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="ri-inbox-line fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No recent orders</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endsection