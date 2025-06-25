@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container">
    <h2 class="mb-4">Order #{{ $order->id }}</h2>

    <!-- Buyer Info -->
    <div class="mb-4">
        <h5>Buyer Information</h5>
        <p><strong>Name:</strong> {{ $order->buyer->name }}</p>
        <p><strong>Role:</strong> {{ ucfirst($order->buyer->role) }}</p>
        <p><strong>Email:</strong> {{ $order->buyer->email }}</p>
    </div>

    <!-- Order Status -->
    <div class="mb-4">
        <h5>Status</h5>
        <p><strong>Order Status:</strong> {{ ucfirst($order->status) }}</p>
        <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
    </div>

    <!-- Items -->
    <div class="mb-4">
        <h5>Order Items</h5>
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>UGX {{ number_format($item->unit_price) }}</td>
                    <td>UGX {{ number_format($item->unit_price * $item->quantity) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p class="fw-bold">Total: UGX {{ number_format($order->total_amount) }}</p>
    </div>

    <!-- Actions (Supplier Role) -->
    <div class="mb-4">
        <h5>Actions</h5>

        @if($order->seller_id === auth()->id())
            {{-- Approve Order --}}
            @if($order->status === 'pending')
                <form method="POST" action="{{ route('orders.updateStatus', $order) }}" class="d-inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="btn btn-success">Approve Order</button>
                </form>
            @endif

            {{-- Mark as Shipped --}}
            @if($order->status === 'approved')
                <form method="POST" action="{{ route('orders.updateStatus', $order) }}" class="d-inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="shipped">
                    <button type="submit" class="btn btn-primary">Mark as Shipped</button>
                </form>
            @endif

            {{-- Verify Payment --}}
            @if($order->payment_status === 'pending_verification')
                <a href="{{ route('payments.verify.form', $order) }}" class="btn btn-outline-success">
                    Verify Payment
                </a>
            @endif
        @endif

        <a href="{{ route('supplier.orders') }}" class="btn btn-secondary">Back to Orders</a>
    </div>
</div>
@endsection
