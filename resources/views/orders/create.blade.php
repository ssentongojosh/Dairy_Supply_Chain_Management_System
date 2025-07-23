@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Place New Order</h4>
                </div>
                <div class="card-body">
                    @php
                        $role = auth()->user()->role->value;
                        $storeRoute = match($role) {
                            'retailer' => 'retailer.orders.store',
                            'wholesaler' => 'wholesaler.orders.store',
                            'plant_manager' => 'plant_manager.orders.store',
                            default => '#',
                        };
                    @endphp
                    <form action="{{ $storeRoute !== '#' ? route($storeRoute) : '#' }}" method="POST" id="orderForm">
                        @csrf

                        <div class="mb-3">
                            <label for="seller_id" class="form-label">Choose Seller</label>
                            <select name="seller_id" id="seller_id" class="form-select" required>
                                <option value="">Select a seller</option>
                                @foreach($allowedSellers as $seller)
                                    <option value="{{ $seller->id }}">{{ $seller->name }} ({{ $seller->role }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <h5 class="mb-3">Select Products</h5>
                            <div id="products-container">

                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Choose Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="">Select a payment method</option>
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Delivery Address</label>
                            <textarea name="address" id="address" class="form-control" rows="2" required placeholder="Enter delivery address"></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Place Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('seller_id').addEventListener('change', function() {
        var sellerId = this.value;
        var productsContainer = document.getElementById('products-container');
        productsContainer.innerHTML = '';

        if (sellerId) {
            fetch('/seller/' + sellerId + '/products')
                .then(response => response.json())
                .then(products => {
                    if (products.length === 0  || products.products.length === 0) {
                        productsContainer.innerHTML = '<p>No products available for this seller.</p>';
                    } else {
                        let html = '';
                        const productList = products.products || products; // Handle both cases
                        productList.forEach(product => {
                            html += `
                                <div class="product-row mb-3 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <input type="checkbox" name="items[${product.id}][product_id]" value="${product.id}" class="form-check-input me-2">
                                                ${product.name}
                                            </label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="items[${product.id}][quantity]" min="1" placeholder="Quantity" class="form-control" disabled>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="text-muted">Available: ${product.quantity}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        productsContainer.innerHTML = html;

                        // Enable/disable quantity input based on checkbox
                        const checkboxes = productsContainer.querySelectorAll('input[type="checkbox"]');
                        const quantityInputs = productsContainer.querySelectorAll('input[type="number"]');
                        checkboxes.forEach((checkbox, index) => {
                            checkbox.addEventListener('change', function() {
                                const quantityInput = quantityInputs[index];
                                if (this.checked) {
                                    quantityInput.disabled = false;
                                    quantityInput.required = true;
                                } else {
                                    quantityInput.disabled = true;
                                    quantityInput.required = false;
                                    quantityInput.value = '';
                                }
                            });
                        });
                    }
                });
        }
    });
});
</script>
@endsection
