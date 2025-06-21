@extends('layouts.contentNavbarLayout')

@section('title', 'Farmer Inventory Management')

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
                            <i class="ri-plant-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Total Products</h5>
                            <small class="text-muted">{{ $stats['total_items'] }} dairy products</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-success me-3">
                            <i class="ri-droplet-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Total Units</h5>
                            <small class="text-muted">{{ $stats['total_quantity'] }} liters/units</small>
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
                            <small class="text-muted">{{ $stats['low_stock_items'] }} items below 50 units</small>
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
                <h5 class="card-title mb-0">Dairy Inventory Management</h5>
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
                        <form action="{{ route('farmer.inventory') }}" method="GET" class="row g-2">
                            <div class="col-md-4">
                                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search dairy products..." value="{{ request('search') }}">
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
                                    <option value="low" {{ request('stock_level') == 'low' ? 'selected' : '' }}>Low Stock (≤50)</option>
                                    <option value="medium" {{ request('stock_level') == 'medium' ? 'selected' : '' }}>Medium Stock (51-200)</option>
                                    <option value="high" {{ request('stock_level') == 'high' ? 'selected' : '' }}>High Stock (>200)</option>
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
                                                            <i class="ri-droplet-line"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $item->product->name }}</h6>
                                                    <small class="text-muted">{{ $item->product->category ?? 'Dairy' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>UGX {{ number_format($item->unit_cost, 0) }}</td>
                                        <td>UGX {{ number_format($item->selling_price, 0) }}</td>
                                        <td>{{ $item->quantity }} {{ $item->product->unit ?? 'L' }}</td>
                                        <td>
                                            @if($item->quantity <= 0)
                                                <span class="badge bg-label-danger">Out of Stock</span>
                                            @elseif($item->quantity <= 50)
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
                                                <a href="#" onclick="editThreshold({{ $item->id }}, '{{ $item->product->name }}', 20)" class="badge bg-label-primary">
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
                                                    <form action="{{ route('farmer.inventory.destroy', $item->id) }}" method="POST" class="d-inline">
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
                                                <i class="ri-droplet-line" style="font-size: 3rem; color: #ddd;"></i>
                                            </div>
                                            <h6>No dairy products in inventory</h6>
                                            <p class="text-muted">Add your first dairy product to get started with inventory tracking.</p>
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
                                                        <i class="ri-droplet-line"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-0">{{ $item->product->name }}</h5>
                                                <small class="text-muted">{{ $item->product->category ?? 'Dairy' }}</small>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <h6 class="mb-0">{{ $item->quantity }}</h6>
                                                    <small class="text-muted">Units</small>
                                                </div>
                                                <div class="col-4">
                                                    <h6 class="mb-0">UGX {{ number_format($item->unit_cost, 0) }}</h6>
                                                    <small class="text-muted">Cost</small>
                                                </div>
                                                <div class="col-4">
                                                    <h6 class="mb-0">UGX {{ number_format($item->selling_price, 0) }}</h6>
                                                    <small class="text-muted">Price</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            @if($item->quantity <= 0)
                                                <span class="badge bg-danger w-100">Out of Stock</span>
                                            @elseif($item->quantity <= 50)
                                                <span class="badge bg-warning w-100">Low Stock</span>
                                            @else
                                                <span class="badge bg-success w-100">In Stock</span>
                                            @endif
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editInventory({{ $item->id }}, '{{ $item->product->name }}', {{ $item->quantity }}, '{{ $item->notes }}')">
                                                <i class="ri-pencil-line me-1"></i> Edit
                                            </button>
                                            <form action="{{ route('farmer.inventory.destroy', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete(this)">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <i class="ri-droplet-line" style="font-size: 4rem; color: #ddd;"></i>
                                    <h5 class="mt-3">No dairy products in inventory</h5>
                                    <p class="text-muted">Add your first dairy product to get started with inventory tracking.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                @if($inventory->hasPages())
                    <div class="mt-4">
                        {{ $inventory->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Dairy Product to Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('farmer.inventory.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-select" name="product_id" required>
                                <option value="">Select Product</option>
                                <!-- Products will be loaded via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit Cost (UGX) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="unit_cost" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Selling Price (UGX) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="selling_price" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Optional notes about this product..."></textarea>
                        </div>
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
                <h5 class="modal-title">Update Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editInventoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" id="editProductName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="quantity" id="editQuantity" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" id="editNotes" rows="3"></textarea>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="thresholdForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" id="thresholdProductName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reorder Point <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="reorder_point" id="thresholdValue" min="1" required>
                        <small class="text-muted">Alert when quantity drops to or below this level</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Set Threshold</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Load available products when add modal opens
    $('#addInventoryModal').on('show.bs.modal', function() {
        $.ajax({
            url: '{{ route("farmer.inventory.products") }}',
            method: 'GET',
            success: function(products) {
                const select = $('select[name="product_id"]');
                select.empty().append('<option value="">Select Product</option>');
                products.forEach(function(product) {
                    select.append(`<option value="${product.id}">${product.name} - ${product.supplier_name}</option>`);
                });
            }
        });
    });

    // View toggle functionality
    $('input[name="viewMode"]').change(function() {
        if ($(this).attr('id') === 'tableView') {
            $('#tableViewContainer').removeClass('d-none');
            $('#cardViewContainer').addClass('d-none');
        } else {
            $('#tableViewContainer').addClass('d-none');
            $('#cardViewContainer').removeClass('d-none');
        }
    });
});

function editInventory(id, name, quantity, notes) {
    $('#editProductName').val(name);
    $('#editQuantity').val(quantity);
    $('#editNotes').val(notes || '');
    $('#editInventoryForm').attr('action', `/farmer/inventory/${id}/update-quantity`);
    $('#editInventoryModal').modal('show');
}

function editThreshold(id, name, currentThreshold) {
    $('#thresholdProductName').val(name);
    $('#thresholdValue').val(currentThreshold);
    $('#thresholdForm').attr('action', `/farmer/inventory/${id}/threshold`);
    $('#thresholdModal').modal('show');
}

function confirmDelete(button) {
    if (confirm('Are you sure you want to remove this product from inventory?')) {
        button.closest('form').submit();
    }
}
</script>
@endpush
@endsection
