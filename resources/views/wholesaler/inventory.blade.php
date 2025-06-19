@extends('layouts.contentNavbarLayout')

@section('title', 'Wholesaler Inventory Management')

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
</style>
@endpush

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-primary me-3">
                            <i class="ri-stack-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Total Products</h5>
                            <small class="text-muted">{{ $stats['total_items'] }} unique products</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-success me-3">
                            <i class="ri-shopping-basket-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Total Units</h5>
                            <small class="text-muted">{{ $stats['total_quantity'] }} units in stock</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-warning me-3">
                            <i class="ri-alert-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Low Stock</h5>
                            <small class="text-muted">{{ $stats['low_stock_items'] }} items below threshold</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-danger me-3">
                            <i class="ri-ban-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Out of Stock</h5>
                            <small class="text-muted">{{ $stats['out_of_stock'] }} products unavailable</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory List -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h5 class="card-title mb-0">Inventory Management</h5>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                        <i class="ri-add-line me-1"></i> Add Product
                    </button>
                   
                </div>
            </div>

            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <form action="{{ route('wholesaler.inventory') }}" method="GET" class="row g-2">
                            <div class="col-md-4">
                                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search products..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" name="category">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" name="stock_level">
                                    <option value="">All Stock Levels</option>
                                    <option value="out" {{ request('stock_level') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                                    <option value="low" {{ request('stock_level') == 'low' ? 'selected' : '' }}>Low Stock</option>
                                    <option value="medium" {{ request('stock_level') == 'medium' ? 'selected' : '' }}>Medium Stock</option>
                                    <option value="high" {{ request('stock_level') == 'high' ? 'selected' : '' }}>High Stock</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="btn-group" role="group" aria-label="View options">
                            <input type="radio" class="btn-check" name="viewMode" id="tableView" checked>
                            <label class="btn btn-outline-primary btn-sm" for="tableView">
                                <i class="ri-list-check me-1"></i> Table View
                            </label>

                            <input type="radio" class="btn-check" name="viewMode" id="cardView">
                            <label class="btn btn-outline-primary btn-sm" for="cardView">
                                <i class="ri-layout-grid-line me-1"></i> Card View
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div id="tableViewContainer">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Unit Cost</th>
                                    <th>Selling Price</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>Reorder Point</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventory as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">
                                                    @if($item->product->image_url)
                                                        <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="rounded">
                                                    @else
                                                        <div class="avatar-initial rounded bg-label-primary">
                                                            <i class="ri-box-3-line"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $item->product->name }}</h6>
                                                    <small class="text-muted">{{ $item->product->category }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>${{ number_format($item->unit_cost, 2) }}</td>
                                        <td>${{ number_format($item->selling_price, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>
                                            @if($item->quantity <= 0)
                                                <span class="badge bg-label-danger">Out of Stock</span>
                                            @elseif($item->reorder_point && $item->quantity <= $item->reorder_point)
                                                <span class="badge bg-label-warning">Low Stock</span>
                                            @else
                                                <span class="badge bg-label-success">In Stock</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->reorder_point)
                                                {{ $item->reorder_point }}
                                                <a href="#" onclick="editThreshold({{ $item->id }}, '{{ $item->product->name }}', {{ $item->reorder_point }})" class="text-muted ms-1">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                            @else
                                                <a href="#" onclick="editThreshold({{ $item->id }}, '{{ $item->product->name }}', 10)" class="badge bg-label-primary">
                                                    Set Threshold
                                                </a>
                                            @endif
                                        </td>
                                        <td>{{ $item->updated_at->diffForHumans() }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">
                                                    <i class="ri-more-fill"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#" onclick="editInventory({{ $item->id }}, '{{ $item->product->name }}', {{ $item->quantity }}, '{{ $item->notes }}')">
                                                        <i class="ri-pencil-line me-1"></i> Edit
                                                    </a>
                                                    <form action="{{ route('wholesaler.inventory.destroy', $item->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="dropdown-item" onclick="confirmDelete(this)">
                                                            <i class="ri-delete-bin-line me-1"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-3">
                                            <div class="text-center mb-3">
                                                <i class="ri-inbox-line" style="font-size: 3rem;"></i>
                                            </div>
                                            <h6>No inventory items found</h6>
                                            <p class="text-muted">Add products to your inventory to get started.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Card View -->
                <div id="cardViewContainer" class="d-none">
                    <div class="row">
                        @forelse($inventory as $item)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card card-view-item h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar me-2">
                                                @if($item->product->image_url)
                                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="rounded">
                                                @else
                                                    <div class="avatar-initial rounded bg-label-primary">
                                                        <i class="ri-box-3-line"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-0">{{ $item->product->name }}</h5>
                                                <small class="text-muted">{{ $item->product->category }}</small>
                                            </div>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="d-flex flex-column">
                                                    <small class="text-muted">Unit Cost</small>
                                                    <span>${{ number_format($item->unit_cost, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex flex-column">
                                                    <small class="text-muted">Selling Price</small>
                                                    <span>${{ number_format($item->selling_price, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex flex-column">
                                                    <small class="text-muted">Quantity</small>
                                                    <span>{{ $item->quantity }} units</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex flex-column">
                                                    <small class="text-muted">Status</small>
                                                    <span>
                                                        @if($item->quantity <= 0)
                                                            <span class="badge bg-label-danger">Out of Stock</span>
                                                        @elseif($item->reorder_point && $item->quantity <= $item->reorder_point)
                                                            <span class="badge bg-label-warning">Low Stock</span>
                                                        @else
                                                            <span class="badge bg-label-success">In Stock</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between mt-3">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editInventory({{ $item->id }}, '{{ $item->product->name }}', {{ $item->quantity }}, '{{ $item->notes }}')">
                                                <i class="ri-pencil-line me-1"></i> Edit
                                            </button>

                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" onclick="editThreshold({{ $item->id }}, '{{ $item->product->name }}', {{ $item->reorder_point ?? 10 }})">
                                                    <i class="ri-alarm-line me-1"></i> Threshold
                                                </button>
                                                <form action="{{ route('wholesaler.inventory.destroy', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(this)">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <i class="ri-inbox-line" style="font-size: 3rem;"></i>
                                    <h6 class="mt-3">No inventory items found</h6>
                                    <p class="text-muted">Add products to your inventory to get started.</p>
                                    <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                                        <i class="ri-add-line me-1"></i> Add Product
                                    </button>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $inventory->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product to Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('wholesaler.inventory.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">Select a product</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                        </div>
                        <div class="col-6">
                            <label for="unit_cost" class="form-label">Unit Cost</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="unit_cost" name="unit_cost" min="0.01" step="0.01" value="0.00" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="selling_price" class="form-label">Selling Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="selling_price" name="selling_price" min="0.01" step="0.01" value="0.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add to Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Inventory Modal -->
<div class="modal fade" id="editInventoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editInventoryForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_product_name" class="form-label">Product</label>
                        <input type="text" class="form-control" id="edit_product_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit_quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="edit_quantity" name="quantity" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Threshold Modal -->
<div class="modal fade" id="thresholdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Reorder Threshold</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="thresholdForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="threshold_product_name" class="form-label">Product</label>
                        <input type="text" class="form-control" id="threshold_product_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="reorder_point" class="form-label">Reorder Point (Low Stock Threshold)</label>
                        <input type="number" class="form-control" id="reorder_point" name="reorder_point" min="1" required>
                        <small class="text-muted">You'll be alerted when stock falls below this level</small>
                    </div>
                    <div class="mb-3">
                        <label for="auto_order_quantity" class="form-label">Auto-Order Quantity (Optional)</label>
                        <input type="number" class="form-control" id="auto_order_quantity" name="auto_order_quantity" min="1">
                        <small class="text-muted">Default quantity to order when restocking</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Threshold</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmationMessage">Are you sure you want to delete this item?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmActionBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationTitle">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="notificationMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
// Load available products when add modal is opened
document.addEventListener('DOMContentLoaded', function() {
    const addModal = document.getElementById('addInventoryModal');
    if (addModal) {
        addModal.addEventListener('show.bs.modal', function() {
            const productSelect = document.getElementById('product_id');
            if (productSelect.options.length <= 1) {
                fetch('{{ route("wholesaler.inventory.products") }}')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Clear existing options except the first one
                        while (productSelect.options.length > 1) {
                            productSelect.remove(1);
                        }

                        // Add new options
                        data.forEach(product => {
                            const option = document.createElement('option');
                            option.value = product.id;
                            option.textContent = `${product.name} (Supplier: ${product.supplier_name})`;
                            option.dataset.price = product.price;
                            productSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading products:', error);
                        showNotification('Error', 'Failed to load available products. Please try again.');
                    });
            }
        });
    }

    // Set price when product is selected
    document.getElementById('product_id')?.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.price) {
            const price = parseFloat(selectedOption.dataset.price);
            document.getElementById('unit_cost').value = price.toFixed(2);
            document.getElementById('selling_price').value = (price * 1.2).toFixed(2); // 20% markup
        }
    });

    // View switching functionality
    document.getElementById('tableView')?.addEventListener('change', function() {
        document.getElementById('tableViewContainer').classList.remove('d-none');
        document.getElementById('cardViewContainer').classList.add('d-none');
    });

    document.getElementById('cardView')?.addEventListener('change', function() {
        document.getElementById('tableViewContainer').classList.add('d-none');
        document.getElementById('cardViewContainer').classList.remove('d-none');
    });
});

// Edit inventory function
function editInventory(id, productName, quantity, notes) {
    document.getElementById('edit_product_name').value = productName;
    document.getElementById('edit_quantity').value = quantity;
    document.getElementById('edit_notes').value = notes || '';
    document.getElementById('editInventoryForm').action = `/wholesaler/inventory/${id}`;

    const editModal = new bootstrap.Modal(document.getElementById('editInventoryModal'));
    editModal.show();
}

// Edit threshold function
function editThreshold(id, productName, currentThreshold) {
    document.getElementById('threshold_product_name').value = productName;
    document.getElementById('reorder_point').value = currentThreshold;
    document.getElementById('thresholdForm').action = `/wholesaler/inventory/${id}/threshold`;

    const thresholdModal = new bootstrap.Modal(document.getElementById('thresholdModal'));
    thresholdModal.show();
}

// Confirmation function for delete
function confirmDelete(button) {
    const form = button.closest('form');
    document.getElementById('confirmActionBtn').onclick = function() {
        form.submit();
    };

    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    modal.show();
}

// Custom notification function
function showNotification(title, message) {
    document.getElementById('notificationTitle').textContent = title;
    document.getElementById('notificationMessage').textContent = message;

    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    modal.show();
}
</script>
@endsection
