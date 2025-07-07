@extends('layouts.contentNavbarLayout')

@section('content')
<h2 class="text-xl font-bold mb-4">Order History</h2>

<!-- Placed Orders -->
<h3 class="text-lg font-semibold mt-6 mb-2">Your Orders (Placed)</h3>
@forelse($placedOrders as $order)
    <div class="border p-4 rounded mb-3 bg-gray-100">
        <p><strong>To:</strong> {{ $order->seller->name }} ({{ $order->seller->role }})</p>
        <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
        <p><strong>Total:</strong> {{ $order->total_price }} UGX</p>
        <ul class="mt-2">
            @foreach($order->items as $item)
                <li>📦 {{ $item->product->name }} — Qty: {{ $item->quantity }}</li>
            @endforeach
        </ul>
    </div>
@empty
    <p class="text-gray-500">No placed orders yet.</p>
@endforelse

<!-- Received Orders -->
<h3 class="text-lg font-semibold mt-6 mb-2">Orders You Received</h3>
@forelse($receivedOrders as $order)
    <div class="border p-4 rounded mb-3 bg-green-50">
        <p><strong>From:</strong> {{ $order->buyer->name }} ({{ $order->buyer->role }})</p>
        <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
        <p><strong>Total:</strong> {{ $order->total_price }} UGX</p>
        <ul class="mt-2">
            @foreach($order->items as $item)
                <li>📦 {{ $item->product->name }} — Qty: {{ $item->quantity }}</li>
            @endforeach
        </ul>
    </div>
@empty
    <p class="text-gray-500">No received orders yet.</p>
@endforelse
@if(!$order->payment && $order->status === 'pending')
    <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="mt-2">
        @csrf
        <button type="submit" class="text-red-600 hover:underline">
            ❌ Cancel Order
        </button>
    </form>
@endif
@endsection