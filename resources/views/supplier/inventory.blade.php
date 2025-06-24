@extends('layouts.contentNavbarLayout')

@section('title', 'Supplier Inventory Management')

@push('styles')
<style>
/* Responsive table improvements */
.table-responsive {
    border-radius: 0.375rem;
}

/* Compact mode styles */
.table-sm th,
.table-sm td {
    padding: 0.3rem 0.5rem;
    font-size: 0.875rem;
}

/* Badge improvements for mobile */
.badge {
    font-size: 0.75rem;
}

/* Card view improvements */
.card-view-item {
    transition: transform 0.2s;
}

.card-view-item:hover {
    transform: translateY(-2px);
}

/* Mobile-specific improvements */
@media (max-width: 768px) {
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .dropdown-toggle {
        padding: 0.25rem 0.5rem;
    }

    .table th,
    .table td {
        padding: 0.5rem 0.25rem;
        font-size: 0.875rem;
    }
}

/* View toggle styles */
.btn-check:checked + .btn-outline-primary {
    background-color: var(--bs-primary);
    color: white;
}

/* Improve dropdown button in table */
.dropdown-toggle::after {
    vertical-align: middle;
}

.low-stock {
    background-color: #fff3cd !important;
    border-left: 4px solid #ffc107;
}

.out-of-stock {
    background-color: #f8d7da !important;
    border-left: 4px solid #dc3545;
}
</style>
@endpush

@section('content')
<div class="row">
    <!-- Page Header -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-1">Inventory Management</h4>
                    <p class="text-muted mb-0">Manage your product inventory and stock levels</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                        <i class="ri-add-line me-2"></i>Add Product
                    </button>
                    <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
                        <i class="ri-upload-line me-2"></i>Bulk Import
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Statistics -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-primary me-3">
                            <i class="ri-archive-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $inventoryStats['total_products'] ?? 0 }}</h5>
                            <small class="text-muted">Total Products</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-warning me-3">
                            <i class="ri-error-warning-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $inventoryStats['low_stock_products'] ?? 0 }}</h5>
                            <small class="text-muted">Low Stock</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-danger me-3">
                            <i class="ri-close-circle-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $inventoryStats['out_of_stock_products'] ?? 0 }}</h5>
                            <small class="text-muted">Out of Stock</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-info me-3">
                            <i class="ri-money-dollar-circle-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">₱{{ number_format($inventoryStats['total_value'] ?? 0, 2) }}</h5>
                            <small class="text-muted">Total Value</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search Product</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Product name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Stock Status</label>
                        <select name="stock_status" class="form-select">
                            <option value="">All Status</option>
                            <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                            <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sort By</label>
                        <select name="sort_by" class="form-select">
                            <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Name</option>
                            <option value="quantity" {{ request('sort_by') === 'quantity' ? 'selected' : '' }}>Quantity</option>
                            <option value="price" {{ request('sort_by') === 'price' ? 'selected' : '' }}>Price</option>
                            <option value="updated_at" {{ request('sort_by') === 'updated_at' ? 'selected' : '' }}>Last Updated</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-search-line me-2"></i>Filter
                        </button>
                        @if(request()->hasAny(['search', 'category', 'stock_status', 'sort_by']))
                            <a href="{{ route('supplier.inventory') }}" class="btn btn-outline-secondary">
                                <i class="ri-close-line me-2"></i>Clear
                            </a>
                        @endif
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="view" id="tableView" checked>
                            <label class="btn btn-outline-primary btn-sm" for="tableView">
                                <i class="ri-list-unordered"></i>
                            </label>
                            <input type="radio" class="btn-check" name="view" id="cardView">
                            <label class="btn btn-outline-primary btn-sm" for="cardView">
                                <i class="ri-grid-line"></i>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Inventory Table View -->
    <div class="col-12" id="inventoryTableView">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Inventory Items</h5>
                <span class="text-muted">{{ $inventory->total() ?? 0 }} items total</span>
            </div>
            <div class="card-body">
                @if(isset($inventory) && $inventory->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Value</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventory as $item)
                                    @php
                                        $stockClass = '';
                                        if ($item->quantity <= 0) {
                                            $stockClass = 'out-of-stock';
                                        } elseif ($item->quantity <= $item->threshold) {
                                            $stockClass = 'low-stock';
                                        }
                                    @endphp
                                    <tr class="{{ $stockClass }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($item->product->image)
                                                    <img src="{{ asset('storage/' . $item->product->image) }}" 
                                                         alt="{{ $item->product->name }}"
                                                         class="rounded me-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @endif
                                                <div>
                                                    <h6 class="mb-0">{{ $item->product->name ?? 'Unknown Product' }}</h6>
                                                    <small class="text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $item->product->category ?? 'N/A' }}</td>
                                        <td>
                                            <span class="fw-semibold">{{ $item->quantity }}</span>
                                            <small class="text-muted">{{ $item->product->unit ?? 'pcs' }}</small>
                                            @if($item->quantity <= $item->threshold)
                                                <br><small class="text-warning">Threshold: {{ $item->threshold }}</small>
                                            @endif
                                        </td>
                                        <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                        <td>₱{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                        <td>
                                            @if($item->quantity <= 0)
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @elseif($item->quantity <= $item->threshold)
                                                <span class="badge bg-warning">Low Stock</span>
                                            @else
                                                <span class="badge bg-success">In Stock</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->updated_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                        data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu">
                                                    <button class="dropdown-item" 
                                                            onclick="editInventory({{ $item->id }})">
                                                        <i class="ri-edit-line me-2"></i>Edit
                                                    </button>
                                                    <button class="dropdown-item" 
                                                            onclick="adjustStock({{ $item->id }})">
                                                        <i class="ri-refresh-line me-2"></i>Adjust Stock
                                                    </button>
                                                    <div class="dropdown-divider"></div>
                                                    <button class="dropdown-item text-danger" 
                                                            onclick="deleteInventory({{ $item->id }})">
                                                        <i class="ri-delete-bin-line me-2"></i>Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $inventory->withQueryString()->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ri-archive-line fs-1 text-muted"></i>
                        <h6 class="mt-2">No inventory items found</h6>
                        <p class="text-muted">
                            @if(request()->hasAny(['search', 'category', 'stock_status']))
                                No items match your current filters. Try adjusting your search criteria.
                            @else
                                Start by adding products to your inventory.
                            @endif
                        </p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                            <i class="ri-add-line me-2"></i>Add First Product
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product to Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="inventoryForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="product_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="">Select Category</option>
                                <option value="Seeds">Seeds</option>
                                <option value="Fertilizers">Fertilizers</option>
                                <option value="Tools">Tools</option>
                                <option value="Pesticides">Pesticides</option>
                                <option value="Equipment">Equipment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" class="form-control" name="sku" placeholder="Product SKU">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit</label>
                            <select class="form-select" name="unit">
                                <option value="pcs">Pieces</option>
                                <option value="kg">Kilograms</option>
                                <option value="lbs">Pounds</option>
                                <option value="bags">Bags</option>
                                <option value="liters">Liters</option>
                                <option value="boxes">Boxes</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="unit_price" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Low Stock Threshold</label>
                            <input type="number" class="form-control" name="threshold" min="0" step="0.01" value="10">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Product description..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-2"></i>Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockAdjustmentForm">
                <div class="modal-body">
                    <input type="hidden" id="adjustInventoryId" name="inventory_id">
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" class="form-control" id="currentStock" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <select class="form-select" name="adjustment_type" id="adjustmentType">
                            <option value="add">Add Stock</option>
                            <option value="subtract">Subtract Stock</option>
                            <option value="set">Set Exact Amount</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" name="reason" rows="2" placeholder="Reason for adjustment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Import Modal -->
<div class="modal fade" id="bulkImportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Import Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkImportForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">CSV File</label>
                        <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                        <div class="form-text">Upload a CSV file with columns: name, category, sku, unit, quantity, unit_price, threshold, description</div>
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('supplier.inventory.template') }}" class="btn btn-outline-info btn-sm">
                            <i class="ri-download-line me-2"></i>Download Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-upload-line me-2"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
// Form submission
document.getElementById('inventoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = this.dataset.action || '{{ route("supplier.inventory.store") }}';
    const method = this.dataset.method || 'POST';
    
    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addInventoryModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Failed to save product');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the product');
    });
});

// Stock adjustment form submission
document.getElementById('stockAdjustmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const inventoryId = formData.get('inventory_id');
    
    fetch(`/supplier/inventory/${inventoryId}/adjust`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('stockAdjustmentModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Failed to adjust stock');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adjusting stock');
    });
});

// Bulk import form submission
document.getElementById('bulkImportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("supplier.inventory.bulk-import") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('bulkImportModal')).hide();
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Failed to import products');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during import');
    });
});

// Edit inventory function
function editInventory(inventoryId) {
    fetch(`/supplier/inventory/${inventoryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('inventoryForm');
                const modal = document.getElementById('addInventoryModal');
                
                // Populate form fields
                form.elements['product_name'].value = data.inventory.product.name;
                form.elements['category'].value = data.inventory.product.category || '';
                form.elements['sku'].value = data.inventory.product.sku || '';
                form.elements['unit'].value = data.inventory.product.unit || 'pcs';
                form.elements['quantity'].value = data.inventory.quantity;
                form.elements['unit_price'].value = data.inventory.unit_price;
                form.elements['threshold'].value = data.inventory.threshold;
                form.elements['description'].value = data.inventory.product.description || '';
                
                // Update modal title and form action
                modal.querySelector('.modal-title').textContent = 'Edit Product';
                form.dataset.action = `/supplier/inventory/${inventoryId}`;
                form.dataset.method = 'PUT';
                
                new bootstrap.Modal(modal).show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load inventory data');
        });
}

// Adjust stock function
function adjustStock(inventoryId) {
    fetch(`/supplier/inventory/${inventoryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('adjustInventoryId').value = inventoryId;
                document.getElementById('currentStock').value = `${data.inventory.quantity} ${data.inventory.product.unit || 'pcs'}`;
                
                new bootstrap.Modal(document.getElementById('stockAdjustmentModal')).show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load inventory data');
        });
}

// Delete inventory function
function deleteInventory(inventoryId) {
    if (confirm('Are you sure you want to delete this product from inventory?')) {
        fetch(`/supplier/inventory/${inventoryId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to delete product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the product');
        });
    }
}

// Reset form when modal is hidden
document.getElementById('addInventoryModal').addEventListener('hidden.bs.modal', function() {
    const form = document.getElementById('inventoryForm');
    form.reset();
    form.removeAttribute('data-action');
    form.removeAttribute('data-method');
    this.querySelector('.modal-title').textContent = 'Add New Product to Inventory';
});
</script>
@endsection
