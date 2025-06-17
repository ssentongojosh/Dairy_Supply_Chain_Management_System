@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Available Products from Wholesalers -->
        <div class="col-md-6">
            <h2>Available Products</h2>
            
            @foreach($wholesalers as $wholesaler)
                <div class="card mb-3">
                    <div class="card-header">
                        {{ $wholesaler->name }}
                    </div>
                    <div class="card-body">
                        <form action="{{ route('retailer.orders.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="wholesaler_id" value="{{ $wholesaler->id }}">
                            
                            <div id="order-items-{{ $wholesaler->id }}">
                                <div class="order-item mb-3">
                                    <select name="items[0][product_id]" class="form-select mb-2" required>
                                        <option value="">Select Product</option>
                                        @foreach($wholesaler->products as $product)
                                            <option value="{{ $product->id }}">
                                                {{ $product->name }} - Ksh {{ number_format($product->price, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="items[0][quantity]" class="form-control" min="1" value="1" required>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-secondary btn-sm mb-3 btn-add-item" 
                                    data-wholesaler="{{ $wholesaler->id }}">
                                Add Item
                            </button>
                            
                            <button type="submit" class="btn btn-primary">Place Order</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- My Orders -->
        <div class="col-md-6">
            <h2>My Orders</h2>
            
            @foreach($outgoingOrders as $order)
                <div class="card mb-3">
                    <div class="card-header">
                        Order #{{ $order->id }} to {{ $order->seller->name }}
                        <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'shipped' ? 'primary' : 'success') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <h5>Items:</h5>
                        <ul>
                            @foreach($order->items as $item)
                                <li>{{ $item->quantity }} x {{ $item->product->name }}</li>
                            @endforeach
                        </ul>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('retailer.orders.show', $order) }}" class="btn btn-sm btn-info">
                                View Details
                            </a>
                            
                            @if($order->status === 'shipped')
                                <form action="{{ route('retailer.orders.receive', $order) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        Mark as Received
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-add-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const wholesalerId = this.dataset.wholesaler;
            const container = document.getElementById(`order-items-${wholesalerId}`);
            const index = container.children.length;
            
            const div = document.createElement('div');
            div.className = 'order-item mb-3';
            div.innerHTML = `
                <select name="items[${index}][product_id]" class="form-select mb-2" required>
                    <option value="">Select Product</option>
                    @foreach($wholesalers as $wholesaler)
                        @foreach($wholesaler->products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} - Ksh {{ number_format($product->price, 2) }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
                <input type="number" name="items[${index}][quantity]" class="form-control" min="1" value="1" required>
            `;
            
            container.appendChild(div);
        });
    });
</script>
@endsection