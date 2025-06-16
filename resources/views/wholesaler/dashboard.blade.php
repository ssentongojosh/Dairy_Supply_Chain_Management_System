<h2>Incoming Orders from Retailers</h2>

@foreach($incomingOrders as $order)
    <div>
        <strong>Retailer:</strong> {{ $order->buyer->name }}<br>
        <strong>Status:</strong> {{ $order->status }}<br>
        <ul>
            @foreach($order->items as $item)
                <li>{{ $item->product->name }} x {{ $item->quantity }}</li>
            @endforeach
        </ul>

        @if($order->status == 'pending')
            <form method="POST" action="{{ route('wholesaler.orders.approve', $order->id) }}">
                @csrf
                <button type="submit">Approve Order</button>
            </form>
        @endif
    </div>
@endforeach
