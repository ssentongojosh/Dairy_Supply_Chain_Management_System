@extends('layouts.contentNavbarLayout')

@section('title', 'Inventory Management')

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
    border-color: var(--bs-primary);
    color: white;
}

/* Improve dropdown button in table */
.dropdown-toggle::after {
    display: none;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar-initial bg-primary rounded">
                                    <i class="ri-archive-line"></i>
                                </div>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Total Items</span>
                        <h3 class="card-title mb-2">{{ $stats['total_items'] }}</h3>
                        <small class="text-muted">Different products in stock</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar-initial bg-success rounded">
                                    <i class="ri-stack-line"></i>
                                </div>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Total Quantity</span>
                        <h3 class="card-title mb-2">{{ number_format($stats['total_quantity']) }}</h3>
                        <small class="text-muted">Units in inventory</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar-initial bg-warning rounded">
                                    <i class="ri-alert-line"></i>
                                </div>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Low Stock</span>
                        <h3 class="card-title mb-2">{{ $stats['low_stock_items'] }}</h3>
                        <small class="text-muted">Items with ≤10 units</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar-initial bg-danger rounded">
                                    <i class="ri-close-circle-line"></i>
                                </div>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Out of Stock</span>
                        <h3 class="card-title mb-2">{{ $stats['out_of_stock_items'] }}</h3>
                        <small class="text-muted">Items with 0 units</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Threshold Management Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Auto-Reorder Threshold Management</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bulkThresholdModal">
                        <i class="ri-settings-line me-1"></i>Bulk Set Threshold
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="triggerAutoReorder()">
                        <i class="ri-refresh-line me-1"></i>Check & Create Orders
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar me-2">
                                <div class="avatar-initial bg-info rounded">
                                    <i class="ri-alarm-warning-line"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0">Items Below Threshold</h6>
                                <small class="text-muted">{{ $stats['low_stock_items'] }} items need reordering</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar me-2">
                                <div class="avatar-initial bg-warning rounded">
                                    <i class="ri-shopping-cart-line"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0">Auto-Orders Today</h6>
                                <small class="text-muted">0 orders created automatically</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar me-2">
                                <div class="avatar-initial bg-success rounded">
                                    <i class="ri-check-line"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0">Well Stocked</h6>
                                <small class="text-muted">{{ $stats['total_items'] - $stats['low_stock_items'] }} items above threshold</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Inventory Management</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                    <i class="ri-add-line me-1"></i>Add Item
                </button>
            </div>

            <!-- Filters -->
            <div class="card-body">
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Search Product</label>
                        <input type="text" name="search" class="form-control" placeholder="Product name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                    {{ ucfirst($category) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock Level</label>
                        <select name="stock_level" class="form-select">
                            <option value="">All Levels</option>
                            <option value="out" {{ request('stock_level') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                            <option value="low" {{ request('stock_level') === 'low' ? 'selected' : '' }}>Low Stock (≤10)</option>
                            <option value="medium" {{ request('stock_level') === 'medium' ? 'selected' : '' }}>Medium (11-50)</option>
                            <option value="high" {{ request('stock_level') === 'high' ? 'selected' : '' }}>High Stock (>50)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-search-line"></i>
                            </button>
                            <a href="{{ route('retailer.inventory') }}" class="btn btn-outline-secondary">
                                <i class="ri-refresh-line"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- View Toggle -->
            <div class="card-body border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="viewMode" id="tableView" checked>
                        <label class="btn btn-outline-primary btn-sm" for="tableView">
                            <i class="ri-table-line me-1"></i>Table
                        </label>
                        <input type="radio" class="btn-check" name="viewMode" id="cardView">
                        <label class="btn btn-outline-primary btn-sm" for="cardView">
                            <i class="ri-grid-line me-1"></i>Cards
                        </label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="compactMode">
                        <label class="form-check-label" for="compactMode">Compact View</label>
                    </div>
                </div>
            </div>

            <!-- Table View -->
            <div id="tableViewContent" class="table-responsive">
                <table class="table table-striped" id="inventoryTable">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="d-none d-md-table-cell">Supplier</th>
                            <th>Quantity</th>
                            <th class="d-none d-lg-table-cell">Reorder Point</th>
                            <th class="d-none d-lg-table-cell">Unit Price</th>
                            <th class="d-none d-xl-table-cell">Total Value</th>
                            <th>Status</th>
                            <th class="d-none d-xl-table-cell">Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventory as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ substr($item->product->name ?? 'N/A', 0, 2) }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="fw-medium">{{ $item->product->name ?? 'N/A' }}</span>
                                            <div class="d-md-none">
                                                <small class="text-muted d-block">{{ $item->product->supplier->name ?? 'N/A' }}</small>
                                                <small class="text-muted">Reorder: {{ $item->reorder_point ?? 10 }} | ${{ number_format($item->product->price ?? 0, 2) }}</small>
                                            </div>
                                            @if($item->notes)
                                                <br><small class="text-muted">{{ $item->notes }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="text-muted">{{ $item->product->supplier->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ number_format($item->quantity) }}</span>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <div class="d-flex align-items-center">
                                        <span class="fw-medium me-2">{{ $item->reorder_point ?? 10 }}</span>
                                        @if($item->quantity <= ($item->reorder_point ?? 10))
                                            <span class="badge bg-warning badge-sm">
                                                <i class="ri-alarm-warning-line"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <span class="text-muted">${{ number_format($item->product->price ?? 0, 2) }}</span>
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    <span class="fw-medium">${{ number_format(($item->product->price ?? 0) * $item->quantity, 2) }}</span>
                                </td>
                                <td>
                                    @if($item->quantity == 0)
                                        <span class="badge bg-danger">Out</span>
                                    @elseif($item->quantity <= ($item->reorder_point ?? 10))
                                        @if(isset($item->has_pending_order) && $item->has_pending_order)
                                            <span class="badge bg-info">
                                                <i class="ri-shopping-cart-line me-1"></i>Ordered
                                            </span>
                                        @else
                                            <span class="badge bg-warning">Low</span>
                                        @endif
                                    @elseif($item->quantity <= (($item->reorder_point ?? 10) * 2))
                                        <span class="badge bg-info">Med</span>
                                    @else
                                        <span class="badge bg-success">Good</span>
                                    @endif
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    <span class="text-muted">{{ $item->updated_at->format('M d') }}</span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="ri-more-2-line"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="editInventory({{ $item->id }}, '{{ $item->product->name }}', {{ $item->quantity }}, '{{ $item->notes ?? '' }}')">
                                                    <i class="ri-edit-line me-2"></i>Update Quantity
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="editThreshold({{ $item->id }}, '{{ $item->product->name }}', {{ $item->reorder_point ?? 10 }})">
                                                    <i class="ri-alarm-warning-line me-2"></i>Set Threshold
                                                </a>
                                            </li>
                                            @if($item->quantity <= ($item->reorder_point ?? 10))
                                                @if(isset($item->has_pending_order) && $item->has_pending_order)
                                                    <li>
                                                        <span class="dropdown-item text-muted">
                                                            <i class="ri-shopping-cart-line me-2"></i>Order Pending
                                                        </span>
                                                    </li>
                                                @else
                                                    <li>
                                                        <a class="dropdown-item text-warning" href="#" onclick="createReorderNow({{ $item->id }}, '{{ $item->product->name }}')">
                                                            <i class="ri-shopping-cart-line me-2"></i>Reorder Now
                                                        </a>
                                                    </li>
                                                @endif
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('retailer.inventory.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirmDelete(event, '{{ $item->product->name ?? 'this item' }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="ri-delete-bin-line me-2"></i>Remove Item
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ri-archive-line mb-2" style="font-size: 3rem; color: #ccc;"></i>
                                        <h5 class="text-muted">No inventory items found</h5>
                                        <p class="text-muted">Start by adding some products to your inventory.</p>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                                            <i class="ri-add-line me-1"></i>Add First Item
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Card View -->
            <div id="cardViewContent" class="d-none">
                <div class="row">
                    @forelse($inventory as $item)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ substr($item->product->name ?? 'N/A', 0, 2) }}
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ $item->product->name ?? 'N/A' }}</h6>
                                                <small class="text-muted">{{ $item->product->supplier->name ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                <i class="ri-more-2-line"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="editInventory({{ $item->id }}, '{{ $item->product->name }}', {{ $item->quantity }}, '{{ $item->notes ?? '' }}')">
                                                        <i class="ri-edit-line me-2"></i>Update
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="editThreshold({{ $item->id }}, '{{ $item->product->name }}', {{ $item->reorder_point ?? 10 }})">
                                                        <i class="ri-alarm-warning-line me-2"></i>Threshold
                                                    </a>
                                                </li>
                                                @if($item->quantity <= ($item->reorder_point ?? 10))
                                                    @if(isset($item->has_pending_order) && $item->has_pending_order)
                                                        <li>
                                                            <span class="dropdown-item text-muted">
                                                                <i class="ri-shopping-cart-line me-2"></i>Order Pending
                                                            </span>
                                                        </li>
                                                    @else
                                                        <li>
                                                            <a class="dropdown-item text-warning" href="#" onclick="createReorderNow({{ $item->id }}, '{{ $item->product->name }}')">
                                                                <i class="ri-shopping-cart-line me-2"></i>Reorder
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endif
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="row text-center mb-3">
                                        <div class="col-4">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{{ number_format($item->quantity) }}</span>
                                                <small class="text-muted">In Stock</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{{ $item->reorder_point ?? 10 }}</span>
                                                <small class="text-muted">Reorder At</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">${{ number_format($item->product->price ?? 0, 2) }}</span>
                                                <small class="text-muted">Unit Price</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            @if($item->quantity == 0)
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @elseif($item->quantity <= ($item->reorder_point ?? 10))
                                                @if(isset($item->has_pending_order) && $item->has_pending_order)
                                                    <span class="badge bg-info">
                                                        <i class="ri-shopping-cart-line me-1"></i>Order Pending
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">Low Stock</span>
                                                @endif
                                            @elseif($item->quantity <= (($item->reorder_point ?? 10) * 2))
                                                <span class="badge bg-info">Medium Stock</span>
                                            @else
                                                <span class="badge bg-success">Well Stocked</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $item->updated_at->format('M d') }}</small>
                                    </div>

                                    @if($item->notes)
                                        <div class="mt-2">
                                            <small class="text-muted">{{ $item->notes }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-4">
                                <i class="ri-archive-line mb-2" style="font-size: 3rem; color: #ccc;"></i>
                                <h5 class="text-muted">No inventory items found</h5>
                                <p class="text-muted">Start by adding some products to your inventory.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            @if($inventory->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Showing {{ $inventory->firstItem() }} to {{ $inventory->lastItem() }} of {{ $inventory->total() }} items
                        </small>
                        {{ $inventory->withQueryString()->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('retailer.inventory.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product</label>
                        <select name="product_id" id="product_id" class="form-select" required>
                            <option value="">Select a product...</option>
                            <!-- Products will be loaded via JavaScript -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Any additional notes..."></textarea>
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editInventoryForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" id="edit_product_name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit_quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="edit_quantity" class="form-control" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Notes (Optional)</label>
                        <textarea name="notes" id="edit_notes" class="form-control" rows="3" placeholder="Any additional notes..."></textarea>
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
    <div class="modal-dialog modal-dialog-centered">
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
                        <label class="form-label">Product</label>
                        <input type="text" id="threshold_product_name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="threshold_reorder_point" class="form-label">Reorder Point</label>
                        <input type="number" name="reorder_point" id="threshold_reorder_point" class="form-control" min="1" required>
                        <div class="form-text">When stock reaches this level, an automatic order will be suggested.</div>
                    </div>
                    <div class="mb-3">
                        <label for="threshold_auto_order_qty" class="form-label">Auto Order Quantity (Optional)</label>
                        <input type="number" name="auto_order_quantity" id="threshold_auto_order_qty" class="form-control" min="1">
                        <div class="form-text">How many units to order automatically when threshold is reached.</div>
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

<!-- Bulk Threshold Modal -->
<div class="modal fade" id="bulkThresholdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Set Thresholds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('retailer.inventory.bulk-threshold') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        This will apply the same threshold to all your inventory items.
                    </div>
                    <div class="mb-3">
                        <label for="bulk_reorder_point" class="form-label">Reorder Point for All Items</label>
                        <input type="number" name="reorder_point" id="bulk_reorder_point" class="form-control" min="1" value="10" required>
                        <div class="form-text">This threshold will be applied to all inventory items.</div>
                    </div>
                    <div class="mb-3">
                        <label for="bulk_auto_order_qty" class="form-label">Default Auto Order Quantity</label>
                        <input type="number" name="auto_order_quantity" id="bulk_auto_order_qty" class="form-control" min="1" value="50">
                        <div class="form-text">Default quantity to order when threshold is reached.</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="overwrite_existing" id="overwrite_existing" value="1">
                            <label class="form-check-label" for="overwrite_existing">
                                Overwrite existing thresholds
                            </label>
                            <div class="form-text">Check this to replace existing thresholds, otherwise only items without thresholds will be updated.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply to All Items</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar me-3">
                        <div class="avatar-initial bg-warning rounded" id="confirmationIcon">
                            <i class="ri-question-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0" id="confirmationMessage">Are you sure you want to proceed?</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmationAction">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationTitle">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar me-3">
                        <div class="avatar-initial rounded" id="notificationIcon">
                            <i class="ri-information-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0" id="notificationMessage">Operation completed successfully!</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Reorder Confirmation Modal -->
<div class="modal fade" id="reorderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Reorder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar me-3">
                        <div class="avatar-initial bg-warning rounded">
                            <i class="ri-shopping-cart-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1" id="reorderProductName">Product Name</h6>
                        <p class="mb-0 text-muted">Create an automatic reorder for this product?</p>
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    This will create an order to your supplier based on the auto-order quantity settings.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmReorder">
                    <i class="ri-shopping-cart-line me-2"></i>Create Reorder
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Auto Reorder All Modal -->
<div class="modal fade" id="autoReorderAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Auto Reorder All Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar me-3">
                        <div class="avatar-initial bg-primary rounded">
                            <i class="ri-shopping-cart-2-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Bulk Auto Reorder</h6>
                        <p class="mb-0 text-muted">Create automatic orders for all items below their reorder threshold</p>
                    </div>
                </div>
                <div class="alert alert-warning">
                    <i class="ri-alert-line me-2"></i>
                    This will create multiple orders to your suppliers. Please review your thresholds before proceeding.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAutoReorderAll">
                    <i class="ri-shopping-cart-2-line me-2"></i>Create All Orders
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Load available products when add modal is opened
document.addEventListener('DOMContentLoaded', function() {
    const addModal = document.getElementById('addInventoryModal');
    const productSelect = document.getElementById('product_id');

    addModal.addEventListener('show.bs.modal', function() {
        // Load available products
        fetch('{{ route('retailer.inventory.products') }}')
            .then(response => response.json())
            .then(products => {
                productSelect.innerHTML = '<option value="">Select a product...</option>';
                products.forEach(product => {
                    productSelect.innerHTML += `<option value="${product.id}">${product.name} (${product.supplier_name}) - $${product.price}</option>`;
                });
            })
            .catch(error => {
                console.error('Error loading products:', error);
                productSelect.innerHTML = '<option value="">Error loading products</option>';
            });
    });
});

// Edit inventory function
function editInventory(id, productName, quantity, notes) {
    document.getElementById('edit_product_name').value = productName;
    document.getElementById('edit_quantity').value = quantity;
    document.getElementById('edit_notes').value = notes || '';
    document.getElementById('editInventoryForm').action = `/retailer/inventory/${id}`;

    new bootstrap.Modal(document.getElementById('editInventoryModal')).show();
}

// Edit threshold function
function editThreshold(id, productName, currentThreshold) {
    document.getElementById('threshold_product_name').value = productName;
    document.getElementById('threshold_reorder_point').value = currentThreshold;
    document.getElementById('thresholdForm').action = `/retailer/inventory/${id}/threshold`;

    new bootstrap.Modal(document.getElementById('thresholdModal')).show();
}

// Bulk threshold function
function openBulkThreshold() {
    new bootstrap.Modal(document.getElementById('bulkThresholdModal')).show();
}

// Create reorder now function
function createReorderNow(inventoryId, productName) {
    // Show reorder confirmation modal
    document.getElementById('reorderProductName').innerText = productName;
    const reorderModal = new bootstrap.Modal(document.getElementById('reorderModal'));

    // Confirm reorder action
    document.getElementById('confirmReorder').onclick = function() {
        fetch(`/retailer/inventory/${inventoryId}/reorder`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            reorderModal.hide();
            if (data.success) {
                // Show notification modal with success styling
                showNotification('Success!', 'Reorder created successfully!', 'success', 'ri-check-line');
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification('Error', 'Error creating reorder: ' + data.message, 'danger', 'ri-error-warning-line');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            reorderModal.hide();

            // Handle specific error messages
            if (error.message) {
                showNotification('Order Already Exists', error.message, 'warning', 'ri-information-line');
            } else {
                showNotification('Error', 'Error creating reorder. Please try again.', 'danger', 'ri-error-warning-line');
            }
        });
    };

    reorderModal.show();
}

// Trigger auto reorder for all items below threshold
function triggerAutoReorder() {
    // Show auto reorder confirmation modal
    const autoReorderModal = new bootstrap.Modal(document.getElementById('autoReorderAllModal'));

    // Confirm auto reorder action
    document.getElementById('confirmAutoReorderAll').onclick = function() {
        fetch('/retailer/inventory/auto-reorder', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            autoReorderModal.hide();
            if (data.success) {
                let message = '';
                if (data.orders_created > 0) {
                    message = `${data.orders_created} automatic orders created successfully!`;
                    if (data.skipped_items > 0) {
                        message += ` (${data.skipped_items} items already have pending orders)`;
                    }
                    showNotification('Success!', message, 'success', 'ri-check-line');
                } else {
                    message = data.message || 'No new orders were created.';
                    showNotification('No Orders Created', message, 'info', 'ri-information-line');
                }
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification('Error', 'Error creating auto orders: ' + data.message, 'danger', 'ri-error-warning-line');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            autoReorderModal.hide();
            showNotification('Error', 'Error creating auto orders. Please try again.', 'danger', 'ri-error-warning-line');
        });
    };

    autoReorderModal.show();
}

// View switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tableView = document.getElementById('tableView');
    const cardView = document.getElementById('cardView');
    const tableViewContent = document.getElementById('tableViewContent');
    const cardViewContent = document.getElementById('cardViewContent');
    const compactMode = document.getElementById('compactMode');
    const table = document.getElementById('inventoryTable');

    // Handle view switching
    tableView.addEventListener('change', function() {
        if (this.checked) {
            tableViewContent.classList.remove('d-none');
            cardViewContent.classList.add('d-none');
        }
    });

    cardView.addEventListener('change', function() {
        if (this.checked) {
            tableViewContent.classList.add('d-none');
            cardViewContent.classList.remove('d-none');
        }
    });

    // Handle compact mode
    compactMode.addEventListener('change', function() {
        if (this.checked) {
            table.classList.add('table-sm');
            // Hide some columns in compact mode
            const hideColumns = table.querySelectorAll('.d-xl-table-cell');
            hideColumns.forEach(col => {
                col.style.display = 'none';
            });
        } else {
            table.classList.remove('table-sm');
            // Show columns again
            const hideColumns = table.querySelectorAll('.d-xl-table-cell');
            hideColumns.forEach(col => {
                col.style.display = '';
            });
        }
    });

    // Save user preference in localStorage
    tableView.addEventListener('change', function() {
        localStorage.setItem('inventoryViewMode', 'table');
    });

    cardView.addEventListener('change', function() {
        localStorage.setItem('inventoryViewMode', 'card');
    });

    compactMode.addEventListener('change', function() {
        localStorage.setItem('inventoryCompactMode', this.checked);
    });

    // Load user preferences
    const savedViewMode = localStorage.getItem('inventoryViewMode');
    const savedCompactMode = localStorage.getItem('inventoryCompactMode');

    if (savedViewMode === 'card') {
        cardView.checked = true;
        cardView.dispatchEvent(new Event('change'));
    }

    if (savedCompactMode === 'true') {
        compactMode.checked = true;
        compactMode.dispatchEvent(new Event('change'));
    }
});

// Custom notification function
function showNotification(title, message, type = 'info', icon = 'ri-information-line') {
    const modal = document.getElementById('notificationModal');
    const titleEl = document.getElementById('notificationTitle');
    const messageEl = document.getElementById('notificationMessage');
    const iconEl = document.getElementById('notificationIcon');

    titleEl.textContent = title;
    messageEl.textContent = message;

    // Set icon and color based on type
    const bgClass = type === 'danger' ? 'bg-danger' :
                   type === 'success' ? 'bg-success' :
                   type === 'warning' ? 'bg-warning' : 'bg-info';

    iconEl.className = `avatar-initial rounded ${bgClass}`;
    iconEl.innerHTML = `<i class="${icon}"></i>`;

    const notificationModal = new bootstrap.Modal(modal);
    notificationModal.show();
}

// Custom confirmation function
function showConfirmation(title, message, onConfirm, confirmText = 'Confirm', confirmClass = 'btn-primary') {
    const modal = document.getElementById('confirmationModal');
    const titleEl = document.getElementById('confirmationTitle');
    const messageEl = document.getElementById('confirmationMessage');
    const actionBtn = document.getElementById('confirmationAction');

    titleEl.textContent = title;
    messageEl.textContent = message;
    actionBtn.textContent = confirmText;
    actionBtn.className = `btn ${confirmClass}`;

    const confirmModal = new bootstrap.Modal(modal);
    confirmModal.show();

    // Handle confirmation
    actionBtn.onclick = function() {
        confirmModal.hide();
        if (onConfirm) onConfirm();
    };
}

// Confirm delete function
function confirmDelete(event, itemName) {
    event.preventDefault();
    const form = event.target;

    showConfirmation(
        'Remove Item',
        `Are you sure you want to remove "${itemName}" from your inventory? This action cannot be undone.`,
        function() {
            form.submit();
        },
        'Remove Item',
        'btn-danger'
    );

    return false;
}
</script>
@endsection
