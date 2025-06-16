@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Incoming Orders from Wholesalers</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @forelse($incomingOrders as $order)
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Wholesaler:</strong> {{ $order->buyer->name }}<br>
                    <strong>Status:</strong> <span class="text-capitalize">{{ $order->status }}</span><br>
                    <strong>Order Total:</strong> ${{ number_format($order->total_amount, 2) }}<br>
                    <strong>Order Placed At:</strong> {{ $order->created_at->format('d M Y, H:i') }}
                </div>
                <div class="card-body">
                    <h5>Order Items:</h5>
                    <ul>
                        @foreach($order->items as $item)
                            <li>
                                {{ $item->product->name }} &times; {{ $item->quantity }} @ 
                                ${{ number_format($item->price, 2) }} each
                            </li>
                        @endforeach
                    </ul>

                    @if($order->status === 'pending')
                        <form method="POST" action="{{ route('factory.orders.approve', $order->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">Approve Order</button>
                        </form>
                    @else
                        <button class="btn btn-secondary" disabled>Order {{ ucfirst($order->status) }}</button>
                    @endif
                </div>
            </div>
        @empty
            <p>No incoming orders at the moment.</p>
        @endforelse
    </div>
@endsection
