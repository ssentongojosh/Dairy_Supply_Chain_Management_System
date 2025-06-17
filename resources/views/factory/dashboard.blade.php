@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Incoming Wholesaler Orders -->
        <div class="col-md-8">
            <h2>Wholesaler Orders</h2>
            
            @foreach($incomingOrders as $order)
                <div class="card mb-3">
                    <div class="card-header">
                        Order #{{ $order->id }} from {{ $order->buyer->name }}
                        <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'approved' ? 'primary' : 'success') }}">
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
                        
                        <div class="d-flex gap-2">
                            @if($order->status === 'pending')
                                <form action="{{ route('factory.orders.approve', $order) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Approve Order</button>
                                </form>
                            @endif
                            
                            @if($order->status === 'approved')
                                <form action="{{ route('factory.orders.ship', $order) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success">Mark as Shipped</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Create Order to Supplier -->
        <div class="col-md-4">
            <h2>Order Raw Materials</h2>
            <form action="{{ route('factory.supplier.orders.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Select Supplier</label>
                    <select name="supplier_id" class="form-select" required>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div id="order-items">
                    <div class="order-item mb-3">
                        <select name="items[0][product_id]" class="form-select mb-2" required>
                            <option value="">Select Raw Material</option>
                            @foreach($rawMaterials as $material)
                                <option value="{{ $material->id }}">{{ $material->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="items[0][quantity]" class="form-control" min="1" value="1" required>
                    </div>
                </div>
                
                <button type="button" id="add-item" class="btn btn-secondary mb-3">Add Material</button>
                <button type="submit" class="btn btn-primary">Place Order</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('add-item').addEventListener('click', function() {
        const container = document.getElementById('order-items');
        const index = container.children.length;
        
        const div = document.createElement('div');
        div.className = 'order-item mb-3';
        div.innerHTML = `
            <select name="items[${index}][product_id]" class="form-select mb-2" required>
                <option value="">Select Raw Material</option>
                @foreach($rawMaterials as $material)
                    <option value="{{ $material->id }}">{{ $material->name }}</option>
                @endforeach
            </select>
            <input type="number" name="items[${index}][quantity]" class="form-control" min="1" value="1" required>
        `;
        
        container.appendChild(div);
    });
</script>
@endsection