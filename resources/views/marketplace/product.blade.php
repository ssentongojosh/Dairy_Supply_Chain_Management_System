@extends('layouts.contentNavbarLayout')

@section('title', $product->name . ' - Marketplace')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('marketplace.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i>Back to Marketplace
            </a>
        </div>

        <!-- Product Details -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" class="img-fluid rounded" alt="{{ $product->name }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-light rounded" style="height: 300px;">
                                <i class="ri-image-line display-1 text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h3 class="card-title">{{ $product->name }}</h3>
                        <p class="text-muted mb-3">SKU: {{ $product->sku }}</p>

                        <div class="mb-3">
                            <span class="badge bg-label-info">{{ ucfirst($product->category) }}</span>
                        </div>

                        @if($product->description)
                            <div class="mb-4">
                                <h6>Description</h6>
                                <p class="text-muted">{{ $product->description }}</p>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <h6>Price Range</h6>
                                <p class="h5 text-primary">
                                    ${{ number_format($sellers->min('selling_price'), 2) }} -
                                    ${{ number_format($sellers->max('selling_price'), 2) }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6>Available from {{ $sellers->count() }} {{ $sellers->count() === 1 ? 'Seller' : 'Sellers' }}</h6>
                                <p class="text-muted">Compare prices below</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sellers List -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Available from Sellers</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($sellers as $inventory)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border">
                                <div class="card-body">
                                    <!-- Seller Info -->
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar me-3">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ substr($inventory->user->name, 0, 2) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $inventory->user->name }}</h6>
                                            <small class="text-muted">
                                                <span class="badge bg-label-secondary">{{ ucfirst($inventory->user->role) }}</span>
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Price and Stock -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h4 class="text-primary mb-0">${{ number_format($inventory->selling_price, 2) }}</h4>
                                                <small class="text-muted">per unit</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-success fw-medium">{{ $inventory->quantity }} available</span>
                                                @if($inventory->quantity <= $inventory->reorder_point)
                                                    <br><span class="badge bg-warning">Low Stock</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Info -->
                                    @if($inventory->location)
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="ri-map-pin-line me-1"></i>{{ $inventory->location }}
                                            </small>
                                        </div>
                                    @endif

                                    <!-- Order Button -->
                                    <button type="button" class="btn btn-primary w-100"
                                            onclick="orderFromSeller({{ $inventory->id }}, '{{ $inventory->user->name }}', {{ $inventory->selling_price }}, {{ $inventory->quantity }})">
                                        <i class="ri-shopping-cart-line me-1"></i>Order from {{ $inventory->user->name }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order from <span id="sellerName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('retailer.orders.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6 class="mb-2">{{ $product->name }}</h6>
                        <p class="mb-0">{{ $product->description }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Unit Price</label>
                            <input type="text" class="form-control" id="orderUnitPrice" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Available</label>
                            <input type="text" class="form-control" id="orderAvailable" readonly>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="orderQuantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="orderQuantity" name="quantity" min="1" value="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Amount</label>
                            <input type="text" class="form-control" id="orderTotal" readonly>
                        </div>
                    </div>

                    <input type="hidden" name="wholesaler_id" id="orderSellerId">
                    <input type="hidden" name="items" id="orderItems">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Place Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentInventory = null;

function orderFromSeller(inventoryId, sellerName, unitPrice, available) {
    // Get inventory data via AJAX to get seller_id and product_id
    fetch(`/api/inventory/${inventoryId}`)
        .then(response => response.json())
        .then(data => {
            currentInventory = data;

            document.getElementById('sellerName').textContent = sellerName;
            document.getElementById('orderUnitPrice').value = '$' + unitPrice.toFixed(2);
            document.getElementById('orderAvailable').value = available + ' units';
            document.getElementById('orderSellerId').value = data.seller.id;
            document.getElementById('orderQuantity').max = available;

            updateOrderTotal();

            const modal = new bootstrap.Modal(document.getElementById('orderModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load product details');
        });
}

function updateOrderTotal() {
    const quantity = parseInt(document.getElementById('orderQuantity').value) || 1;
    const unitPrice = currentInventory ? currentInventory.selling_price : 0;
    const total = quantity * unitPrice;

    document.getElementById('orderTotal').value = '$' + total.toFixed(2);
      // Update hidden items field for form submission
    if (currentInventory) {
        const items = [{
            product_id: currentInventory.product.id,
            quantity: quantity
        }];
        document.getElementById('orderItems').value = JSON.stringify(items);
    }
}

// Update total when quantity changes
document.getElementById('orderQuantity').addEventListener('input', updateOrderTotal);
</script>
@endsection
