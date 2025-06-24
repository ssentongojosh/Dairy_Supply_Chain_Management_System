@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container">
    <h2>Order #{{ $order->id }}</h2>

    <div class="card mb-3">
        <div class="card-header">
            Supplier: {{ $order->seller->name }}
        </div>
        <div class="card-body">
            <p>Status: <strong>{{ ucfirst($order->status) }}</strong></p>
            <p>Payment Status: <strong>{{ ucfirst($order->payment_status) }}</strong></p>

            <h5>Items</h5>
            <ul>
                @foreach($order->items as $item)
                    <li>{{ $item->quantity }} x {{ $item->product->name }} (Ksh {{ number_format($item->unit_price, 2) }})</li>
                @endforeach
            </ul>

            <div class="mt-3">
                @if($order->status === 'shipped')
                    <form action="{{ route('retailer.orders.updateStatus', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="received">
                        <button type="submit" class="btn btn-success">Mark as Received</button>
                    </form>
                @endif

                @if(in_array($order->status, ['pending', 'processing']))
                    <form action="{{ route('retailer.orders.cancel', $order) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-danger">Cancel Order</button>
                    </form>
                @endif

                @if($order->payment_status === 'unpaid')
                    <a href="{{ route('retailer.orders.payment.show', $order) }}" class="btn btn-primary mt-2">
                        Pay Now
                    </a>
                @endif
            </div>
        </div>
    </div>

    <a href="{{ route('retailer.orders') }}" class="btn btn-secondary">Back to Orders</a>
</div>
@endsection
