@extends('layouts.contentNavbarLayout')

@section('title', 'Add Product to Inventory')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Add Product to Your Inventory</h5>
                <p class="text-muted mb-0">Add existing products to your inventory with your own pricing</p>
            </div>
            <div class="card-body">
                <form action="{{ route('marketplace.add-inventory') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_id" class="form-label">Select Product <span class="text-danger">*</span></label>
                                <select class="form-select" id="product_id" name="product_id" required>
                                    <option value="">Choose a product...</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-category="{{ $product->category }}">
                                            {{ $product->name }} ({{ $product->category }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Select from existing products in the system</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Initial Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                                <small class="text-muted">How many units do you have in stock?</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="unit_cost" class="form-label">Unit Cost <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="unit_cost" name="unit_cost"
                                           step="0.01" min="0" required>
                                </div>
                                <small class="text-muted">Your cost per unit (for profit calculation)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="selling_price" class="form-label">Selling Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="selling_price" name="selling_price"
                                           step="0.01" min="0.01" required>
                                </div>
                                <small class="text-muted">Price you want to sell at</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reorder_point" class="form-label">Reorder Point</label>
                                <input type="number" class="form-control" id="reorder_point" name="reorder_point"
                                       min="0" value="10">
                                <small class="text-muted">Alert when stock reaches this level</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Storage Location</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       placeholder="e.g., Warehouse A, Shelf 12">
                                <small class="text-muted">Where you store this product</small>
                            </div>
                        </div>
                    </div>

                    <!-- Profit Calculation -->
                    <div class="alert alert-info" id="profit-info" style="display: none;">
                        <h6>Profit Analysis</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Profit per Unit:</strong> <span id="profit-per-unit">$0.00</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Profit Margin:</strong> <span id="profit-margin">0%</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Total Potential Profit:</strong> <span id="total-profit">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('marketplace.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i>Back to Marketplace
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i>Add to Inventory
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create New Product Link -->
        <div class="card mt-4">
            <div class="card-body text-center">
                <h6 class="mb-2">Product not in the list?</h6>
                <p class="text-muted mb-3">If the product you want to sell is not available, you can create a new product.</p>
                <a href="{{ route('marketplace.create-product') }}" class="btn btn-outline-primary">
                    <i class="ri-add-circle-line me-1"></i>Create New Product
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function calculateProfit() {
    const unitCost = parseFloat(document.getElementById('unit_cost').value) || 0;
    const sellingPrice = parseFloat(document.getElementById('selling_price').value) || 0;
    const quantity = parseInt(document.getElementById('quantity').value) || 0;

    if (unitCost > 0 && sellingPrice > 0) {
        const profitPerUnit = sellingPrice - unitCost;
        const profitMargin = ((profitPerUnit / sellingPrice) * 100).toFixed(1);
        const totalProfit = profitPerUnit * quantity;

        document.getElementById('profit-per-unit').textContent = '$' + profitPerUnit.toFixed(2);
        document.getElementById('profit-margin').textContent = profitMargin + '%';
        document.getElementById('total-profit').textContent = '$' + totalProfit.toFixed(2);

        document.getElementById('profit-info').style.display = 'block';
    } else {
        document.getElementById('profit-info').style.display = 'none';
    }
}

// Calculate profit when inputs change
document.getElementById('unit_cost').addEventListener('input', calculateProfit);
document.getElementById('selling_price').addEventListener('input', calculateProfit);
document.getElementById('quantity').addEventListener('input', calculateProfit);
</script>
@endsection
