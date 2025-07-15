@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container">
    <h2>Order Details</h2>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Order ID:</strong> {{ $order->id }}</p>
            <p><strong>Buyer:</strong> {{ $order->buyer->name }} ({{ $order->buyer->email }})</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
            <p><strong>Placed on:</strong> {{ $order->created_at->format('F j, Y') }}</p>
        </div>
    </div>

    <h4>Order Items</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('farmer.orders.history') }}" class="btn btn-secondary mt-3">Back to Orders</a>
</div>
@endsection
