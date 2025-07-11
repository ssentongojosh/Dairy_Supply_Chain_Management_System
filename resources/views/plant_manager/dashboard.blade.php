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

            <!-- Raw Materials Statistics -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 bg-label-success me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-drop-line fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">{{ $rawMaterials->count() ?? 0 }}</h5>
                                <small class="text-muted">Raw Materials</small>
                            </div>
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
                <a href="{{ route('inventory.search') }}" class="btn btn-primary">
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
                        <a href="{{ route('inventory.search') }}" class="btn btn-outline-info w-100">
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
        </div>
    </div>

    <!-- Orders Row -->
    <div class="col-12 mb-4">
        <div class="row">
            <!-- Recent Incoming Orders (as Seller) -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Recent Incoming Orders</h5>
                            <small class="text-muted">Orders from your customers</small>
                        </div>
                        <a href="{{ route('plant_manager.orders.history') }}" class="btn btn-outline-primary btn-sm">
                            <i class="ri-external-link-line me-1"></i>View All
                        </a>
                    </div>
                    <div class="card-body">
                        @if(isset($incomingOrders) && $incomingOrders->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($incomingOrders as $order)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <h6 class="mb-0">Order #{{ $order->id }}</h6>
                                            <small class="text-muted">{{ $order->buyer->name ?? 'Unknown Customer' }}</small>
                                        </div>
                                        <div class="text-end">
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'approved' => 'info',
                                                    'processing' => 'primary',
                                                    'shipped' => 'success',
                                                    'delivered' => 'success',
                                                    'rejected' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                            <small class="text-muted d-block">UGX {{ number_format($order->total_amount, 0) }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="ri-inbox-line fs-1 text-muted"></i>
                                <p class="text-muted mb-0">No recent incoming orders</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Outgoing Orders (as Buyer) -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Recent Outgoing Orders</h5>
                            <small class="text-muted">Orders you have placed</small>
                        </div>
                        <a href="{{ route('plant_manager.orders.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="ri-external-link-line me-1"></i>View All
                        </a>
                    </div>
                    <div class="card-body">
                        @if(isset($outgoingOrders) && $outgoingOrders->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($outgoingOrders as $order)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <h6 class="mb-0">Order #{{ $order->id }}</h6>
                                            <small class="text-muted">To: {{ $order->seller->name ?? 'Unknown Supplier' }}</small>
                                        </div>
                                        <div class="text-end">
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'approved' => 'info',
                                                    'processing' => 'primary',
                                                    'shipped' => 'success',
                                                    'delivered' => 'success',
                                                    'rejected' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                            <small class="text-muted d-block">UGX {{ number_format($order->total_amount, 0) }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="ri-inbox-line fs-1 text-muted"></i>
                                <p class="text-muted mb-0">No recent outgoing orders</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Production Status Row -->
    <div class="col-12 mb-4">
        <div class="row">
            <!-- Production Line Status -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Production Line Status</h5>
                        <small class="text-muted">Current production activities</small>
                    </div>
                    <div class="card-body">
                        @if(isset($productionLines) && count($productionLines) > 0)
                            <div class="list-group list-group-flush">
                                @foreach($productionLines as $line)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <h6 class="mb-0">{{ $line['name'] }}</h6>
                                            <small class="text-muted">{{ $line['current_batch'] ?? 'No active batch' }}</small>
                                        </div>
                                        <div class="text-end">
                                            @if($line['status'] === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif($line['status'] === 'maintenance')
                                                <span class="badge bg-warning">Maintenance</span>
                                            @else
                                                <span class="badge bg-secondary">Idle</span>
                                            @endif
                                            <small class="text-muted d-block">{{ $line['efficiency'] }}% efficiency</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="ri-settings-line fs-1 text-muted"></i>
                                <p class="text-muted mb-0">No production lines configured</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quality Control Alerts -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Quality Control Alerts</h5>
                        <small class="text-muted">System alerts and checks</small>
                    </div>
                    <div class="card-body">
                        @if(isset($qualityAlerts))
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Temperature Alerts</span>
                                        <span class="fw-bold text-warning">{{ $qualityAlerts['temperature_alerts'] ?? 0 }}</span>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Batch Tests Pending</span>
                                        <span class="fw-bold text-info">{{ $qualityAlerts['batch_tests_pending'] ?? 0 }}</span>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Expired Products</span>
                                        <span class="fw-bold text-danger">{{ $qualityAlerts['expired_products'] ?? 0 }}</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex justify-content-between">
                                        <span>Compliance Checks</span>
                                        <span class="fw-bold text-primary">{{ $qualityAlerts['compliance_checks'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="ri-shield-check-line fs-1 text-success"></i>
                                <p class="text-muted mb-0">All quality checks passed</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
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
                <form action="{{ route('product.store') }}" method="POST">
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
