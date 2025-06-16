@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Create Order to Supplier</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('factory.orders.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="seller_id" class="form-label">Select Supplier</label>
                <select name="seller_id" id="seller_id" class="form-select" required>
                    <option value="" disabled selected>Choose a supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('seller_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <h4>Order Items</h4>
            <div id="order-items">
                <div class="order-item row mb-3">
                    <div class="col-md-6">
                        <label>Product</label>
                        <select name="items[0][product_id]" class="form-select" required>
                            <option value="" disabled selected>Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('items.0.product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} (${{ number_format($product->price, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Quantity</label>
                        <input type="number" name="items[0][quantity]" class="form-control" min="1" value="{{ old('items.0.quantity', 1) }}" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-remove-item">Remove</button>
                    </div>
                </div>
            </div>

            <button type="button" id="btn-add-item" class="btn btn-secondary mb-3">Add Another Item</button>

            <div>
                <button type="submit" class="btn btn-primary">Send Order</button>
                <a href="{{ route('factory.dashboard') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let itemIndex = {{ old('items') ? count(old('items')) : 1 }};
            const orderItemsContainer = document.getElementById('order-items');
            const btnAddItem = document.getElementById('btn-add-item');

            btnAddItem.addEventListener('click', () => {
                const newItem = document.createElement('div');
                newItem.classList.add('order-item', 'row', 'mb-3');

                newItem.innerHTML = `
                    <div class="col-md-6">
                        <label>Product</label>
                        <select name="items[${itemIndex}][product_id]" class="form-select" required>
                            <option value="" disabled selected>Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} (${{ number_format($product->price, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Quantity</label>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-remove-item">Remove</button>
                    </div>
                `;

                orderItemsContainer.appendChild(newItem);
                itemIndex++;
            });

            orderItemsContainer.addEventListener('click', e => {
                if (e.target.classList.contains('btn-remove-item')) {
                    const item = e.target.closest('.order-item');
                    item.remove();
                }
            });
        });
    </script>
@endsection
