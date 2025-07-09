{{-- @extends('layouts.contentNavbarLayout')
@section('title', 'Retailer Orders') --}}

{{-- @section('content')
<div class="container-fluid">
    <h4 class="fw-bold py-3 mb-4">Retailer Dashboard</h4>

    @if($orders->isEmpty())
        <div class="alert alert-info">No incoming orders at the moment.</div>
    @else
        <div class="card">
            <h5 class="card-header">Incoming Orders</h5>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Buyer</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Items</th>
                            <th>Placed On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->buyer->name }}</td>
                            <td><span class="badge bg-label-primary">{{ ucfirst($order->status) }}</span></td>
                            <td><span class="badge bg-label-success">{{ ucfirst($order->payment_status) }}</span></td>
                            <td>
                                <ul class="list-unstyled mb-0">
                                    @foreach($order->items as $item)
                                        <li>{{ $item->product->name }} x {{ $item->quantity }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection --}}
 @extends('layouts.contentNavbarLayout')

@section('title', 'Retailer Dashboard')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-12 mb-4">
        <div class="row">
            <!-- Order Statistics -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 bg-label-primary me-3">
                                <i class="ri-shopping-cart-line fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">{{ $orderStats['total_orders'] ?? 0 }}</h5>
                                <small class="text-muted">Total Orders</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 bg-label-warning me-3">
                                <i class="ri-time-line fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">{{ $orderStats['pending_orders'] ?? 0 }}</h5>
                                <small class="text-muted">Pending Orders</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 bg-label-success me-3">
                                <i class="ri-check-line fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">{{ $orderStats['completed_orders'] ?? 0 }}</h5>
                                <small class="text-muted">Completed Orders</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 bg-label-info me-3">
                                <i class="ri-money-dollar-circle-line fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">UGX {{ number_format($orderStats['total_revenue'] ?? 0, 0) }}</h5>
                                <small class="text-muted">Total Revenue</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Overview -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Inventory Overview</h5>
                    <small class="text-muted">Your product inventory status</small>
                </div>
                {{-- <a href="{{ route('retailer.inventory') }}" class="btn btn-primary"> --}}
                    <i class="ri-box-3-line me-1"></i> Manage Inventory
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2 bg-label-primary">
                                <i class="ri-droplet-line"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $inventoryStats['total_products'] ?? 0 }}</h6>
                                <small class="text-muted">Products</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2 bg-label-warning">
                                <i class="ri-alert-line"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $inventoryStats['low_stock_items'] ?? 0 }}</h6>
                                <small class="text-muted">Low Stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2 bg-label-danger">
                                <i class="ri-close-circle-line"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $inventoryStats['out_of_stock'] ?? 0 }}</h6>
                                <small class="text-muted">Out of Stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2 bg-label-success">
                                <i class="ri-money-dollar-circle-line"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">UGX {{ number_format($inventoryStats['total_value'] ?? 0, 0) }}</h6>
                                <small class="text-muted">Total Value</small>
                            </div>
                        </div>
                    </div>
                </div>
@if(isset($lowStockItems) && $lowStockItems->count() > 0)
    <div class="alert alert-warning">
        <h6 class="alert-heading">Low Stock Alert!</h6>
        <p class="mb-0">You have {{ $lowStockItems->count() }} items running low on stock.</p>
    </div>

                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('retailer.orders') }}" class="btn btn-outline-primary">
                        <i class="ri-list-check me-2"></i>View Orders
                    </a>
                    <a href="{{ route('retailer.orders.create') }}" class="btn btn-outline-primary">
    <i class="ri-add-line me-2"></i> Create Order
</a>

                    {{-- <a href="{{ route('retailer.inventory') }}" class="btn btn-outline-info"> --}}
                        <i class="ri-box-3-line me-2"></i>Manage Inventory
                    </a>
                    {{-- <a href="{{ route('marketplace.index') }}" class="btn btn-outline-success"> --}}
                        <i class="ri-store-line me-2"></i>Browse Marketplace
                    </a>
                    <a href="{{ route('app-chat') }}" class="btn btn-outline-secondary">
                        <i class="ri-chat-3-line me-2"></i>Customer Support
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Recent Orders</h5>
                    <small class="text-muted">Your latest customer orders</small>
                </div>
                <a href="{{ route('retailer.orders') }}" class="btn btn-outline-primary btn-sm">
                    <i class="ri-external-link-line me-1"></i>View All
                </a>
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentOrders as $order)
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
                                    @if ($order->status === 'approved' && $order->payment_status === 'unpaid')
                                        <a href="{{ route('retailer.orders.payment.show', $order->id) }}" class="btn btn-sm btn-primary mt-1">Pay</a>
                                    @endif
                                    @if ($order->payment_status === 'unpaid')
                                        <form action="{{ route('retailer.orders.cancel', $order->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger mt-1" onclick="return confirm('Are you sure you want to cancel this order?');">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="ri-inbox-line fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No recent orders</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Selling Products</h5>
                <small class="text-muted">This month's best performers</small>
            </div>
            <div class="card-body">
                @if($topProducts->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($topProducts as $product)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    <small class="text-muted">{{ $product->total_sold }} units sold</small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold">UGX {{ number_format($product->total_revenue, 0) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="ri-bar-chart-line fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No sales data this month</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Customer Insights -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Customer Insights</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Customers</span>
                            <span class="fw-bold">{{ $customerStats['total_customers'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Repeat Customers</span>
                            <span class="fw-bold text-success">{{ $customerStats['repeat_customers'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <span>New This Month</span>
                            <span class="fw-bold text-primary">{{ $customerStats['new_customers_this_month'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Revenue Trend</h5>
                <small class="text-muted">Last 6 months</small>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

{{-- @section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
//const monthlyData = @json($monthlyRevenue);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthlyData.map(item => item.month),
        datasets: [{
            label: 'Revenue (UGX)',
            data: monthlyData.map(item => item.revenue),
            borderColor: '#696cff',
            backgroundColor: 'rgba(105, 108, 255, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'UGX ' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Revenue: UGX ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});

// Auto-refresh dashboard every 5 minutes
setInterval(() => {
    location.reload();
}, 300000);
</script>
@endsection --> --}}
