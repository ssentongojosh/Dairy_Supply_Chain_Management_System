@extends('layouts.contentNavbarLayout')

@section('content')
<h2 class="text-xl font-bold mb-4">Incoming Orders</h2>

@foreach($orders as $order)
<div class="border p-4 rounded mb-4">
    <p><strong>From:</strong> {{ $order->buyer->name }}</p>
    <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
    <p><strong>Total:</strong> {{ $order->total_price }} UGX</p>

    <h4 class="mt-2 font-semibold">Items:</h4>
    <ul>
        @foreach($order->items as $item)
        <li>{{ $item->product->name }} — Qty: {{ $item->quantity }}</li>
        @endforeach
    </ul>

    <form method="POST" action="{{ route('orders.updateStatus', $order->id) }}" class="mt-3">
        @csrf
        <select name="status" required>
            <option value="">-- Change Status --</option>
            <option value="approved">Approve</option>
            <option value="processing">Processing</option>
            <option value="completed">Completed</option>
        </select>
        <button type="submit" class="ml-2 btn btn-sm btn-success">Update</button>
    </form>
</div>
@endforeach
@endsection