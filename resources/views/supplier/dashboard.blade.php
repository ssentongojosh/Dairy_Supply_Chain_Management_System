@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Incoming Orders from Factories</h2>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Orders List --}}
    @forelse($incomingOrders as $order)
        <div class="card mb-3">
            <div class="card-header">
                <strong>Factory:</strong> {{ $order->buyer->name }}<br>
                <strong>Status:</strong> <span class="text-capitalize">{{ $order->status }}</span><br>
                <strong>Payment:</strong> {{ ucfirst($order->payment_status) }}<br>
                <strong>Total:</strong> ${{ number_format($order->total_amount, 2) }}<br>
                <strong>Placed At:</strong> {{ $order->created_at->format('d M Y, H:i') }}
            </div>
<<<<<<< HEAD

=======
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('supplier.orders') }}" class="btn btn-outline-primary">
                        <i class="ri-list-check me-2"></i>View Orders
                    </a>
                    <a href="{{ route('supplier.inventory') }}" class="btn btn-outline-info">
                        <i class="ri-box-3-line me-2"></i>Manage Inventory
                    </a>
                    <a href="{{ route('marketplace.index') }}" class="btn btn-outline-success">
                        <i class="ri-store-line me-2"></i>Browse Marketplace
                    </a>
                    <a href="{{ route('app-chat') }}" class="btn btn-outline-secondary">
                        <i class="ri-chat-3-line me-2"></i>Customer Support
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Recent Orders</h5>
                    <small class="text-muted">Your latest customer orders</small>
                </div>
                <a href="{{ route('supplier.orders.history') }}" class="btn btn-outline-primary btn-sm">
                    <i class="ri-external-link-line me-1"></i>View All
                </a>
            </div>
>>>>>>> parent of 1505f91 (Added cards for creating supplier and farmer orders)
            <div class="card-body">
                <h5>Items:</h5>
                <ul>
                    @foreach($order->items as $item)
                        <li>{{ $item->product->name }} × {{ $item->quantity }}</li>
                    @endforeach
                </ul>

                {{-- Conditional Buttons --}}
                @if($order->payment_status === 'paid' && $order->status === 'pending')
                    <form method="POST" action="{{ route('supplier.orders.approve', $order->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">Approve Order</button>
                    </form>
                @elseif($order->payment_status === 'paid' && $order->status === 'approved')
                    <form method="POST" action="{{ route('supplier.orders.ship', $order->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">Mark as Shipped</button>
                    </form>
                @elseif($order->status === 'shipped')
                    <button class="btn btn-secondary" disabled>Order Shipped</button>
                @else
                    <div class="text-muted">Waiting for payment...</div>
                @endif
            </div>
        </div>
    @empty
        <p>No orders at the moment.</p>
    @endforelse
</div>
@endsection
