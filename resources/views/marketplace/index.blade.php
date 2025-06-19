@extends('layouts.contentNavbarLayout')

@section('title', 'Marketplace')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ri-store-line me-2"></i>Marketplace
                    <small class="text-muted ms-2">Browse products from different sellers</small>
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('marketplace.add-form') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i>Add to My Inventory
                    </a>
                    <a href="{{ route('marketplace.create-product') }}" class="btn btn-outline-primary">
                        <i class="ri-add-circle-line me-1"></i>Create New Product
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('marketplace.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search Products</label>
                        <input type="text" class="form-control" name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by name or description">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}"
                                        {{ request('category') === $category ? 'selected' : '' }}>
                                    {{ ucfirst($category) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Seller</label>
                        <select class="form-select" name="seller">
                            <option value="">All Sellers</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}"
                                        {{ request('seller') == $seller->id ? 'selected' : '' }}>
                                    {{ $seller->name }} ({{ ucfirst($seller->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Min Price</label>
                        <input type="number" class="form-control" name="min_price"
                               value="{{ request('min_price') }}"
                               placeholder="0" step="0.01">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Max Price</label>
                        <input type="number" class="form-control" name="max_price"
                               value="{{ request('max_price') }}"
                               placeholder="1000" step="0.01">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sort By</label>
                        <select class="form-select" name="sort">
                            <option value="product_name" {{ request('sort') === 'product_name' ? 'selected' : '' }}>Product Name</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="seller" {{ request('sort') === 'seller' ? 'selected' : '' }}>Seller</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-search-line"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row">
            @forelse($inventoryItems as $inventory)
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="card h-100">
                        @if($inventory->product->image_url)
                            <img src="{{ $inventory->product->image_url }}" class="card-img-top" alt="{{ $inventory->product->name }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                <i class="ri-image-line display-4 text-muted"></i>
                            </div>
                        @endif

                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">{{ $inventory->product->name }}</h6>
                            <p class="card-text text-muted small flex-grow-1">
                                {{ Str::limit($inventory->product->description, 100) }}
                            </p>

                            <!-- Seller Info -->
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-xs me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        {{ substr($inventory->user->name, 0, 2) }}
                                    </span>
                                </div>
                                <small class="text-muted">
                                    {{ $inventory->user->name }}
                                    <span class="badge bg-label-secondary ms-1">{{ ucfirst($inventory->user->role) }}</span>
                                </small>
                            </div>

                            <!-- Price and Stock -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="text-primary mb-0">${{ number_format($inventory->selling_price, 2) }}</h5>
                                    <small class="text-muted">per unit</small>
                                </div>
                                <div class="text-end">
                                    <small class="text-success">{{ $inventory->quantity }} in stock</small>
                                    <br>
                                    @if($inventory->quantity <= $inventory->reorder_point)
                                        <span class="badge bg-warning">Low Stock</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Category -->
                            <span class="badge bg-label-info mb-3">{{ ucfirst($inventory->product->category) }}</span>

                            <!-- Actions -->
                            <div class="mt-auto">
                                <div class="btn-group w-100">
                                    <a href="{{ route('marketplace.product', $inventory->product) }}"
                                       class="btn btn-outline-primary">
                                        <i class="ri-eye-line me-1"></i>View Details
                                    </a>
                                    <button type="button" class="btn btn-primary"
                                            onclick="orderProduct({{ $inventory->id }}, '{{ $inventory->product->name }}', {{ $inventory->selling_price }})">
                                        <i class="ri-shopping-cart-line me-1"></i>Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <div class="avatar-initial bg-label-secondary rounded">
                                <i class="ri-store-3-line display-4"></i>
                            </div>
                        </div>
                        <h6 class="mb-1">No Products Found</h6>
                        <p class="text-muted">No products match your current filters. Try adjusting your search criteria.</p>
                        <a href="{{ route('marketplace.index') }}" class="btn btn-primary">
                            <i class="ri-refresh-line me-1"></i>Clear Filters
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($inventoryItems->hasPages())
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Showing {{ $inventoryItems->firstItem() }} to {{ $inventoryItems->lastItem() }} of {{ $inventoryItems->total() }} products
                        </small>
                        {{ $inventoryItems->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('retailer.orders.store') }}" method="POST">
                @csrf
                <div class="modal-body">                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" id="orderProductName" readonly>
                        <input type="hidden" name="wholesaler_id" id="orderSellerId">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Price</label>
                        <input type="text" class="form-control" id="orderUnitPrice" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="orderQuantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="orderQuantity" name="quantity" min="1" value="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="text" class="form-control" id="orderTotal" readonly>
                    </div>
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

function orderProduct(inventoryId, productName, unitPrice) {
    // Get inventory data via AJAX to get seller_id and product_id
    fetch(`/api/inventory/${inventoryId}`)
        .then(response => response.json())
        .then(data => {
            currentInventory = data;

            document.getElementById('orderProductName').value = productName;            document.getElementById('orderUnitPrice').value = '$' + unitPrice.toFixed(2);
            document.getElementById('orderSellerId').value = data.seller.id;

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
