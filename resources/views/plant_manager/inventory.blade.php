@extends('layouts.contentNavbarLayout')

@section('title', 'Plant Manager Inventory')

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

/* Production status indicators */
.production-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}

.status-raw { background-color: #fd7e14; }
.status-processing { background-color: #0d6efd; }
.status-finished { background-color: #198754; }
.status-expired { background-color: #dc3545; }
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
                            <i class="ri-archive-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Total Items</h5>
                            <small class="text-muted">{{ $stats['total_items'] }} inventory items</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-warning me-3">
                            <i class="ri-droplet-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Raw Materials</h5>
                            <small class="text-muted">{{ $stats['raw_materials_count'] }} types available</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-success me-3">
                            <i class="ri-product-hunt-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Finished Products</h5>
                            <small class="text-muted">{{ $stats['finished_products_count'] }} types ready</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-danger me-3">
                            <i class="ri-alert-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Low Stock</h5>
                            <small class="text-muted">{{ $stats['low_stock_items'] }} items below threshold</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Production Processing Section -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Production Processing</h5>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#productionModal">
                    <i class="ri-settings-line me-1"></i> Process Materials
                </button>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="mb-2">
                            <i class="ri-droplet-line text-warning" style="font-size: 2rem;"></i>
                        </div>
                        <h6>Raw Materials</h6>
                        <p class="text-muted">Input materials for processing</p>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-2">
                            <i class="ri-arrow-right-line text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h6>Processing</h6>
                        <p class="text-muted">Transform into finished products</p>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-2">
                            <i class="ri-product-hunt-line text-success" style="font-size: 2rem;"></i>
                        </div>
                        <h6>Finished Products</h6>
                        <p class="text-muted">Ready for distribution</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Management -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h5 class="card-title mb-0">Inventory Management</h5>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                        <i class="ri-add-line me-1"></i> Add Item
                    </button>
                </div>
            </div>

            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <form action="{{ route('plant_manager.inventory') }}" method="GET" class="row g-2">
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search items..." value="{{ request('search') }}">
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
                                <select class="form-select form-select-sm" name="product_type">
                                    <option value="">All Types</option>
                                    <option value="raw_materials" {{ request('product_type') == 'raw_materials' ? 'selected' : '' }}>Raw Materials</option>
                                    <option value="finished_products" {{ request('product_type') == 'finished_products' ? 'selected' : '' }}>Finished Products</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" name="stock_level">
                                    <option value="">All Stock Levels</option>
                                    <option value="out" {{ request('stock_level') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                                    <option value="low" {{ request('stock_level') == 'low' ? 'selected' : '' }}>Low Stock (≤100)</option>
                                    <option value="medium" {{ request('stock_level') == 'medium' ? 'selected' : '' }}>Medium Stock (101-500)</option>
                                    <option value="high" {{ request('stock_level') == 'high' ? 'selected' : '' }}>High Stock (>500)</option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-2">
                                <button type="submit" class="btn btn-outline-primary btn-sm">Filter</button>
                                <a href="{{ route('plant_manager.inventory') }}" class="btn btn-outline-secondary btn-sm ms-1">Clear</a>
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
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Batch/Expiry</th>
                                    <th>Unit Cost</th>
                                    <th>Status</th>
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
                                                            @if(in_array($item->product->category, ['Raw Milk', 'Additives', 'Packaging']))
                                                                <i class="ri-droplet-line"></i>
                                                            @else
                                                                <i class="ri-product-hunt-line"></i>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $item->product->name }}</h6>
                                                    <small class="text-muted">
                                                        @if(in_array($item->product->category, ['Raw Milk', 'Additives', 'Packaging']))
                                                            <span class="production-indicator status-raw"></span>Raw Material
                                                        @else
                                                            <span class="production-indicator status-finished"></span>Finished Product
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $item->product->category ?? 'Uncategorized' }}</td>
                                        <td>{{ number_format($item->quantity) }} {{ $item->product->unit ?? 'units' }}</td>
                                        <td>
                                            @if($item->batch_number)
                                                <small class="d-block">Batch: {{ $item->batch_number }}</small>
                                            @endif
                                            @if($item->expiry_date)
                                                <small class="d-block {{ $item->expiry_date->isPast() ? 'text-danger' : ($item->expiry_date->diffInDays() <= 7 ? 'text-warning' : 'text-muted') }}">
                                                    Exp: {{ $item->expiry_date->format('M d, Y') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>UGX {{ number_format($item->unit_cost, 0) }}</td>
                                        <td>
                                            @if($item->quantity <= 0)
                                                <span class="badge bg-label-danger">Out of Stock</span>
                                            @elseif($item->quantity <= 100)
                                                <span class="badge bg-label-warning">Low Stock</span>
                                            @elseif($item->expiry_date && $item->expiry_date->isPast())
                                                <span class="badge bg-label-danger">Expired</span>
                                            @elseif($item->expiry_date && $item->expiry_date->diffInDays() <= 7)
                                                <span class="badge bg-label-warning">Expiring Soon</span>
                                            @else
                                                <span class="badge bg-label-success">In Stock</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->updated_at->diffForHumans() }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">
                                                    <i class="ri-more-fill"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#" onclick="editInventory({{ $item->id }}, '{{ $item->product->name }}', {{ $item->quantity }}, '{{ $item->notes }}', '{{ $item->batch_number }}')">
                                                        <i class="ri-pencil-line me-1"></i> Edit
                                                    </a>
                                                    @if(in_array($item->product->category, ['Raw Milk', 'Additives']))
                                                        <a class="dropdown-item" href="#" onclick="processRawMaterial({{ $item->id }}, '{{ $item->product->name }}', {{ $item->quantity }})">
                                                            <i class="ri-settings-line me-1"></i> Process
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('plant_manager.inventory.destroy', $item->id) }}" method="POST" class="d-inline">
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
                                                <i class="ri-archive-line" style="font-size: 3rem; color: #ddd;"></i>
                                            </div>
                                            <h6>No inventory items found</h6>
                                            <p class="text-muted">Add materials and products to start managing inventory.</p>
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
                                                        @if(in_array($item->product->category, ['Raw Milk', 'Additives', 'Packaging']))
                                                            <i class="ri-droplet-line"></i>
                                                        @else
                                                            <i class="ri-product-hunt-line"></i>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-0">{{ $item->product->name }}</h5>
                                                <small class="text-muted">{{ $item->product->category ?? 'Uncategorized' }}</small>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <h6 class="mb-0">{{ number_format($item->quantity) }}</h6>
                                                    <small class="text-muted">{{ $item->product->unit ?? 'Units' }}</small>
                                                </div>
                                                <div class="col-6">
                                                    <h6 class="mb-0">UGX {{ number_format($item->unit_cost, 0) }}</h6>
                                                    <small class="text-muted">Unit Cost</small>
                                                </div>
                                            </div>
                                        </div>

                                        @if($item->batch_number || $item->expiry_date)
                                            <div class="mb-3">
                                                @if($item->batch_number)
                                                    <small class="d-block text-muted">Batch: {{ $item->batch_number }}</small>
                                                @endif
                                                @if($item->expiry_date)
                                                    <small class="d-block {{ $item->expiry_date->isPast() ? 'text-danger' : ($item->expiry_date->diffInDays() <= 7 ? 'text-warning' : 'text-muted') }}">
                                                        Expires: {{ $item->expiry_date->format('M d, Y') }}
                                                    </small>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            @if($item->quantity <= 0)
                                                <span class="badge bg-danger w-100">Out of Stock</span>
                                            @elseif($item->quantity <= 100)
                                                <span class="badge bg-warning w-100">Low Stock</span>
                                            @elseif($item->expiry_date && $item->expiry_date->isPast())
                                                <span class="badge bg-danger w-100">Expired</span>
                                            @elseif($item->expiry_date && $item->expiry_date->diffInDays() <= 7)
                                                <span class="badge bg-warning w-100">Expiring Soon</span>
                                            @else
                                                <span class="badge bg-success w-100">In Stock</span>
                                            @endif
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editInventory({{ $item->id }}, '{{ $item->product->name }}', {{ $item->quantity }}, '{{ $item->notes }}', '{{ $item->batch_number }}')">
                                                <i class="ri-pencil-line me-1"></i> Edit
                                            </button>
                                            @if(in_array($item->product->category, ['Raw Milk', 'Additives']))
                                                <button class="btn btn-outline-success btn-sm" onclick="processRawMaterial({{ $item->id }}, '{{ $item->product->name }}', {{ $item->quantity }})">
                                                    <i class="ri-settings-line"></i>
                                                </button>
                                            @endif
                                            <form action="{{ route('plant_manager.inventory.destroy', $item->id) }}" method="POST" class="d-inline">
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
                                    <i class="ri-archive-line" style="font-size: 4rem; color: #ddd;"></i>
                                    <h5 class="mt-3">No inventory items found</h5>
                                    <p class="text-muted">Add materials and products to start managing inventory.</p>
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
                <h5 class="modal-title">Add Item to Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('plant_manager.inventory.store') }}" method="POST">
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
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Batch Number</label>
                            <input type="text" class="form-control" name="batch_number" placeholder="e.g., BT2025-001">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" name="expiry_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Optional notes about this item..."></textarea>
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
                        <label class="form-label">Batch Number</label>
                        <input type="text" class="form-control" name="batch_number" id="editBatchNumber">
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

<!-- Production Processing Modal -->
<div class="modal fade" id="productionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Production Processing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('plant_manager.inventory.process') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Process raw materials into finished products. This will reduce raw material inventory and add to finished products.
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Raw Material <span class="text-danger">*</span></label>
                            <select class="form-select" name="raw_material_id" id="rawMaterialSelect" required>
                                <option value="">Select Raw Material</option>
                                <!-- Will be populated via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity to Use <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="raw_quantity" min="1" required>
                            <small class="text-muted">Available: <span id="availableQuantity">0</span></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Finished Product <span class="text-danger">*</span></label>
                            <select class="form-select" name="finished_product_id" required>
                                <option value="">Select Finished Product</option>
                                <!-- Will be populated via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Output Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="finished_quantity" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Batch Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="batch_number" value="BT{{ date('Y') }}-{{ str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) }}" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Production Notes</label>
                            <textarea class="form-control" name="production_notes" rows="3" placeholder="Production details, quality checks, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Process Production</button>
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
        loadAvailableProducts();
    });

    // Load production data when production modal opens
    $('#productionModal').on('show.bs.modal', function() {
        loadRawMaterials();
        loadFinishedProducts();
    });

    // Update available quantity when raw material is selected
    $('#rawMaterialSelect').change(function() {
        const selectedOption = $(this).find('option:selected');
        const quantity = selectedOption.data('quantity') || 0;
        $('#availableQuantity').text(quantity);
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

function loadAvailableProducts() {
    $.ajax({
        url: '{{ route("plant_manager.inventory.products") }}',
        method: 'GET',
        success: function(products) {
            const select = $('select[name="product_id"]');
            select.empty().append('<option value="">Select Product</option>');
            products.forEach(function(product) {
                select.append(`<option value="${product.id}">${product.name} - ${product.category} (${product.supplier_name})</option>`);
            });
        }
    });
}

function loadRawMaterials() {
    // This would typically load from an API endpoint
    // For now, we'll simulate with sample data
    const rawMaterials = [
        {id: 1, name: 'Raw Milk', quantity: 1500},
        {id: 2, name: 'Milk Additives', quantity: 500},
        {id: 3, name: 'Packaging Material', quantity: 200}
    ];
    
    const select = $('#rawMaterialSelect');
    select.empty().append('<option value="">Select Raw Material</option>');
    rawMaterials.forEach(function(material) {
        select.append(`<option value="${material.id}" data-quantity="${material.quantity}">${material.name} (${material.quantity} available)</option>`);
    });
}

function loadFinishedProducts() {
    // This would typically load from an API endpoint
    const finishedProducts = [
        {id: 4, name: 'Pasteurized Milk'},
        {id: 5, name: 'Yogurt'},
        {id: 6, name: 'Cheese'},
        {id: 7, name: 'Butter'}
    ];
    
    const select = $('select[name="finished_product_id"]');
    select.empty().append('<option value="">Select Finished Product</option>');
    finishedProducts.forEach(function(product) {
        select.append(`<option value="${product.id}">${product.name}</option>`);
    });
}

function editInventory(id, name, quantity, notes, batchNumber) {
    $('#editProductName').val(name);
    $('#editQuantity').val(quantity);
    $('#editNotes').val(notes || '');
    $('#editBatchNumber').val(batchNumber || '');
    $('#editInventoryForm').attr('action', `/plant_manager/inventory/${id}/update-quantity`);
    $('#editInventoryModal').modal('show');
}

function editThreshold(id, name, currentThreshold) {
    $('#thresholdProductName').val(name);
    $('#thresholdValue').val(currentThreshold);
    $('#thresholdForm').attr('action', `/plant_manager/inventory/${id}/threshold`);
    $('#thresholdModal').modal('show');
}

function processRawMaterial(id, name, quantity) {
    // Pre-select the raw material in production modal
    $('#productionModal').modal('show');
    setTimeout(() => {
        $('#rawMaterialSelect').val(id).trigger('change');
    }, 500);
}

function confirmDelete(button) {
    if (confirm('Are you sure you want to remove this item from inventory?')) {
        button.closest('form').submit();
    }
}
</script>
@endpush
@endsection
