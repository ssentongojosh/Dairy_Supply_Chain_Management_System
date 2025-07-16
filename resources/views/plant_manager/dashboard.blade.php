@extends('layouts.contentNavbarLayout')

@section('title', 'Plant Manager Dashboard')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-12 mb-4">
        <div class="row">
            <!-- Products Statistics -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 bg-label-primary me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-box-3-line fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">{{ $products->count() ?? 0 }}</h5>
                                <small class="text-muted">Finished Products</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- 🧪 Card: Total Raw Materials -->
        <div onclick="document.getElementById('rawMaterialsTable').scrollIntoView({ behavior: 'smooth' })" style="cursor: pointer;" class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100 border-start border-4 border-success">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-info me-3">
                            <i class="ri-cup-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Raw Materials</h5>
                            <h6 class="fw-bold">{{ $rawMaterials->count() }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 bg-label-warning me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-error-warning-line fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">{{ $totalLowStock ?? 0 }}</h5>
                                <small class="text-muted">Low Stock Items</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Activity -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 bg-label-info me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-truck-line fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">{{ ($stats['incoming_deliveries'] ?? 0) + ($stats['outgoing_deliveries'] ?? 0) }}</h5>
                                <small class="text-muted">Active Deliveries</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Production Overview -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Production Overview</h5>
                    <small class="text-muted">Your production and inventory status</small>
                </div>
                <a href="{{ route('plant_manager.inventory') }}" class="btn btn-primary">
                    <i class="ri-factory-line me-1"></i> Manage Production
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3 bg-label-primary d-flex align-items-center justify-content-center">
                                <i class="ri-box-3-line"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $products->count() ?? 0 }}</h6>
                                <small class="text-muted">Products</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3 bg-label-success d-flex align-items-center justify-content-center">
                                <i class="ri-drop-line"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $rawMaterials->count() ?? 0 }}</h6>
                                <small class="text-muted">Raw Materials</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3 bg-label-warning d-flex align-items-center justify-content-center">
                                <i class="ri-error-warning-line"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $totalLowStock ?? 0 }}</h6>
                                <small class="text-muted">Low Stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3 bg-label-info d-flex align-items-center justify-content-center">
                                <i class="ri-truck-line"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ ($stats['incoming_deliveries'] ?? 0) + ($stats['outgoing_deliveries'] ?? 0) }}</h6>
                                <small class="text-muted">Deliveries</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3 bg-label-dark d-flex align-items-center justify-content-center">
                                <i class="ri-time-line"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $orderStats['pending_orders'] ?? 0 }}</h6>
                                <small class="text-muted">Pending Orders</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3 bg-label-secondary d-flex align-items-center justify-content-center">
                                <i class="ri-shield-check-line"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ ($qualityAlerts['temperature_alerts'] ?? 0) + ($qualityAlerts['batch_tests_pending'] ?? 0) }}</h6>
                                <small class="text-muted">Quality Alerts</small>
                            </div>
                        </div>
                    </div>
                </div>
                @if($totalLowStock > 0)
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">Low Stock Alert!</h6>
                        <p class="mb-0">You have {{ $totalLowStock }} items running low on stock.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="ri-add-line me-1"></i>Add Product
                        </button>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#addRawMaterialModal">
                            <i class="ri-add-line me-1"></i>Add Material
                        </button>
                    </div>

                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                       <a href="{{ route('plant_manager.orders.create') }}" class="btn btn-success w-100">
    <i class="ri-add-line me-1"></i>Create Order
</a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <a href="{{ route('plant_manager.inventory') }}" class="btn btn-outline-warning w-100">
                            <i class="ri-shopping-cart-line me-1"></i>View inventory
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <a href="{{ route('plant_manager.inventory') }}" class="btn btn-outline-warning w-100">
                            <i class="ri-shopping-cart-line me-1"></i>View inventory
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <a href="{{ route('plant_manager.inventory') }}" class="btn btn-outline-info w-100">
                            <i class="ri-search-line me-1"></i>Search
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <a href="{{ route('app-chat') }}" class="btn btn-outline-secondary w-100">
                            <i class="ri-chat-3-line me-1"></i>Support
                        </a>
                    </div>
                </div>
        </div>

        {{-- Table title / header --}}
        <div class="card-header bg-primary text-white">
            📦 Finished Products
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                {{-- Table column titles --}}
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Added on</th>
                        <th>Actions</th> {{-- For buttons like "View" --}}
                    </tr>
                </thead>
                {{-- Loop through each product and display in the table --}}
                <tbody>
                    @foreach($products as $product)grace.nakato@modernmilk.com
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>{{ $product->price }}</td>
                            <td>
                                @if ($product->quantity <= 150)
                                   <span class="text-danger">Out of Stock</span>
                                @elseif ($product->quantity <= 350)
                                   <span class="text-warning">Limited</span>
                                @else
                                   <span class="text-success">Available</span>
                                @endif
                            </td>
                            <td>{{ $product->manufacture_date }}</td>
                            <td>
                                {{-- Button to view product details --}}
                                <a href="{{ route('plant_manager.inventory') }}" class="btn btn-sm btn-info">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div><br><br></div>

    <!-- RAW MATERIALS TABLE -->
    <div class="card">
grace.nakato@modernmilk.com
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h5 class="card-title mb-0">Raw Material Management</h5>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRawMaterialModal">
                        <i class="ri-add-line me-1"></i> Add Raw Material
                    </button>

                    <!-- button for product deliveries -->
                    <a href="{{ route('delivery.index') }}" class="btn btn-outline-primary btn-sm ms-2">
                       <i class="ri-truck-line"></i> Deliveries
                    </a>
                </div>
            </div>

        {{-- Table title / header --}}
        <div id = "rawMaterialTable" class="card-header bg-success text-white">
            🧪 Raw Materials
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                {{-- Table column titles --}}
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th>Actions</th> {{-- For buttons like "View" --}}
                    </tr>
                </thead>
                {{-- Loop through each raw material and display it --}}
                <tbody>
                    @foreach($rawMaterials as $material)
                        <tr>
                            <td>{{ $material->name }}</td>
                            <td>{{ $material->quantity }}</td>
                            <td>{{ $material->expiry ?? 'N/A' }}</td>
                            <td>
                                @if ($material->quantity <= 150)
                                   <span class="text-danger">Out of Stock</span>
                                @elseif ($material->quantity <= 350)
                                   <span class="text-warning">Limited</span>
                                @else
                                   <span class="text-success">Available</span>
                                @endif
                            </td>
                            <td>
                                {{-- Button to view raw material details --}}
                                <a href="{{ route('plant_manager.inventory') }}" class="btn btn-sm btn-info">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductLabel">🧀 Add Finished Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('plant_manager.inventory.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" name="price" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Added on</label>
                        <input type="date" name="manufacture_date" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Raw Material Modal -->
<div class="modal fade" id="addRawMaterialModal" tabindex="-1" aria-labelledby="addRawLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRawLabel">🐄 Add Raw Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('raw_materials.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Material Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expiry</label>
                        <input type="date" name="expiry" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Material</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
