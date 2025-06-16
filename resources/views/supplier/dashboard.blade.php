@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Incoming Orders from Factories</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @forelse($incomingOrders as $order)
        <div class="card mb-3">
            <div class="card-header">
                <strong>Factory:</strong> {{ $order->buyer->name }}<br>
                <strong>Status:</strong> <span class="text-capitalize">{{ $order->status }}</span><br>
                <strong>Total:</strong> ${{ number_format($order->total_amount, 2) }}<br>
                <strong>Placed At:</strong> {{ $order->created_at->format('d M Y, H:i') }}
            </div>

            <div class="card-body">
                <h5>Items:</h5>
                <ul>
                    @foreach($order->items as $item)
                        <li>{{ $item->product->name }} × {{ $item->quantity }}</li>
                    @endforeach
                </ul>

                @if($order->status === 'pending')
                    <form method="POST" action="{{ route('supplier.orders.approve', $order->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Approve Order</button>
                    </form>
                @elseif($order->status === 'approved')
                    <form method="POST" action="{{ route('supplier.orders.ship', $order->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Mark as Shipped</button>
                    </form>
                @elseif($order->status === 'shipped')
                    <button class="btn btn-secondary" disabled>Order Shipped</button>
                @endif
            </div>
        </div>
    @empty
        <p>No orders at the moment.</p>
    @endforelse
</div>
@endsection
