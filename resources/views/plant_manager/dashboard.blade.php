@extends('layouts.contentNavbarLayout')

@section('title', 'Plant Manager Dashboard')

@push('styles')
<style>
.production-line-card {
    transition: transform 0.2s;
}

.production-line-card:hover {
    transform: translateY(-2px);
}

.status-active {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.status-maintenance {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.status-offline {
    background: linear-gradient(135deg, #6c757d, #495057);
    color: white;
}

.efficiency-bar {
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
}

.alert-card {
    border-left: 4px solid #dc3545;
}
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-light">Plant Manager /</span> Dashboard
            </h4>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Welcome back, {{ $user->name }}!</h5>
                    <small class="text-muted">{{ $user->business_name ?? 'Plant Operations' }}</small>
                </div>                <div class="d-flex gap-2">
                    <a href="{{ route('plant_manager.orders.dashboard') }}" class="btn btn-primary">
                        <i class="ri-shopping-cart-line me-1"></i> Orders
                    </a>
                    <a href="{{ route('plant_manager.inventory') }}" class="btn btn-outline-primary">
                        <i class="ri-archive-line me-1"></i> Inventory
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Production Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-primary rounded">
                                <i class="ri-droplet-line"></i>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn p-0" type="button" id="productionDropdown" data-bs-toggle="dropdown">
                                <i class="ri-more-2-line"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">View Details</a>
                                <a class="dropdown-item" href="#">Production Report</a>
                            </div>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Daily Production</span>
                    <h3 class="card-title mb-2">{{ number_format($productionStats['current_production']) }}L</h3>
                    <small class="text-muted">
                        {{ number_format(($productionStats['current_production'] / $productionStats['daily_capacity']) * 100, 1) }}% of capacity
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-speed-up-line"></i>
                            </div>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Efficiency Rate</span>
                    <h3 class="card-title mb-2">{{ $productionStats['efficiency_rate'] }}%</h3>
                    <small class="text-success">
                        <i class="ri-arrow-up-line"></i> +2.3% from yesterday
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-info rounded">
                                <i class="ri-shield-check-line"></i>
                            </div>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Quality Score</span>
                    <h3 class="card-title mb-2">{{ $productionStats['quality_score'] }}%</h3>
                    <small class="text-info">
                        <i class="ri-checkbox-circle-line"></i> All tests passed
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-alert-line"></i>
                            </div>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Active Alerts</span>
                    <h3 class="card-title mb-2">{{ array_sum($qualityAlerts) }}</h3>
                    <small class="text-warning">
                        <i class="ri-time-line"></i> Requires attention
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Production Lines Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Production Lines Status</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm">
                        <i class="ri-refresh-line me-1"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($productionLines as $line)
                            <div class="col-md-4 mb-3">
                                <div class="card production-line-card status-{{ $line['status'] }}">
                                    <div class="card-body text-white">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="text-white mb-0">{{ $line['name'] }}</h6>
                                            <span class="badge bg-light text-dark">
                                                {{ ucfirst($line['status']) }}
                                            </span>
                                        </div>
                                        @if($line['current_batch'])
                                            <small class="d-block mb-1">Batch: {{ $line['current_batch'] }}</small>
                                        @endif
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Efficiency: {{ $line['efficiency'] }}%</span>
                                            <span>{{ number_format($line['output_today']) }}L today</span>
                                        </div>
                                        <div class="efficiency-bar bg-light mt-2">
                                            <div class="bg-white" style="width: {{ $line['efficiency'] }}%; height: 100%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders and Inventory Status -->
    <div class="row mb-4">
        <!-- Recent Orders -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Orders</h5>
                    <a href="{{ route('plant_manager.orders.dashboard') }}" class="btn btn-outline-primary btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td>
                                                <span class="fw-medium">#{{ $order->id }}</span>
                                                <small class="text-muted d-block">{{ $order->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ substr($order->buyer->name ?? 'U', 0, 2) }}
                                                        </span>
                                                    </div>
                                                    <span>{{ $order->buyer->name ?? 'Unknown' }}</span>
                                                </div>
                                            </td>
                                            <td>UGX {{ number_format($order->total_amount, 0) }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'approved' => 'info',
                                                        'processing' => 'primary',
                                                        'shipped' => 'success',
                                                        'delivered' => 'success'
                                                    ];
                                                @endphp
                                                <span class="badge bg-label-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="ri-shopping-cart-line" style="font-size: 2rem; color: #ddd;"></i>
                            <p class="text-muted mt-2">No recent orders</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Inventory Summary -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Inventory Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">Raw Materials</span>
                            <span class="fw-medium">{{ number_format($inventoryStats['raw_milk_stock']) }}L</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: 75%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">Finished Products</span>
                            <span class="fw-medium">{{ $inventoryStats['finished_products'] }} types</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 60%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">Low Stock Items</span>
                            <span class="fw-medium text-warning">{{ $inventoryStats['low_stock_items'] }} items</span>
                        </div>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('plant_manager.inventory') }}" class="btn btn-outline-primary btn-sm">
                            Manage Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Control Alerts -->
    @if(array_sum($qualityAlerts) > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card alert-card">
                    <div class="card-header bg-light">
                        <h6 class="card-title text-danger mb-0">
                            <i class="ri-alert-line me-2"></i>Quality Control Alerts
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($qualityAlerts['temperature_alerts'] > 0)
                                <div class="col-md-3 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-temp-hot-line text-danger me-2"></i>
                                        <span>{{ $qualityAlerts['temperature_alerts'] }} Temperature Alerts</span>
                                    </div>
                                </div>
                            @endif
                            @if($qualityAlerts['batch_tests_pending'] > 0)
                                <div class="col-md-3 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-test-tube-line text-warning me-2"></i>
                                        <span>{{ $qualityAlerts['batch_tests_pending'] }} Pending Tests</span>
                                    </div>
                                </div>
                            @endif
                            @if($qualityAlerts['expired_products'] > 0)
                                <div class="col-md-3 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-time-line text-danger me-2"></i>
                                        <span>{{ $qualityAlerts['expired_products'] }} Expired Products</span>
                                    </div>
                                </div>
                            @endif
                            @if($qualityAlerts['compliance_checks'] > 0)
                                <div class="col-md-3 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-shield-check-line text-info me-2"></i>
                                        <span>{{ $qualityAlerts['compliance_checks'] }} Compliance Checks</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Order Statistics -->
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-time-line"></i>
                            </div>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Pending Orders</span>
                    <h3 class="card-title mb-2">{{ $orderStats['pending_orders'] }}</h3>
                    <small class="text-muted">Awaiting approval</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-primary rounded">
                                <i class="ri-settings-line"></i>
                            </div>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">In Production</span>
                    <h3 class="card-title mb-2">{{ $orderStats['processing_orders'] }}</h3>
                    <small class="text-muted">Being processed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-check-line"></i>
                            </div>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Completed Today</span>
                    <h3 class="card-title mb-2">{{ $orderStats['completed_today'] }}</h3>
                    <small class="text-muted">Orders delivered</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-info rounded">
                                <i class="ri-money-dollar-circle-line"></i>
                            </div>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Revenue (Month)</span>
                    <h3 class="card-title mb-2">UGX {{ number_format($orderStats['total_revenue_month'], 0) }}</h3>
                    <small class="text-muted">This month's earnings</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
