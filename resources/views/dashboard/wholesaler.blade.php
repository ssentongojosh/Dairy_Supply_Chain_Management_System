@extends('layouts/contentNavbarLayout')

@section('title', 'Wholesaler Dashboard')

@section('content')
    <div class="row">
        {{-- Welcome Card --}}
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Welcome Back, Wholesaler! 🎉</h5>
                            <p class="mb-4">
                                You have <span class="fw-bold">{{ $pendingOrdersCount }}</span> pending orders and <span class="fw-bold">{{ $lowStockProductsCount }}</span> products running low on stock.
                            </p>
                            <a href="{{ url('/wholesaler/orders') }}" class="btn btn-sm btn-outline-primary">View Orders</a>
                            <a href="{{ url('/wholesaler/inventory') }}" class="btn btn-sm btn-outline-warning ms-2">Manage Inventory</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" height="140" alt="Illustration"
                                data-app-light-img="illustrations/man-with-laptop-light.png"
                                data-app-dark-img="illustrations/man-with-laptop-dark.png" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sales & Orders Metrics --}}
        <div class="col-lg-8 col-md-12">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between flex-sm-row flex-column gap-3">
                                <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                                    <div class="card-title">
                                        <h5 class="mb-2">Total Sales (This Month)</h5>
                                        <span class="badge bg-label-warning rounded-pill">Year {{ now()->year }}</span>
                                    </div>
                                    <div class="mt-sm-auto">
                                        <small class="text-success fw-semibold"><i class="ri-arrow-up-s-line me-1"></i>+12.3%</small>
                                        <h3 class="mb-0">UGX {{ number_format($totalSalesThisMonth, 2) }}</h3>
                                    </div>
                                </div>
                                <div class="chart-container" style="width:150px; height:80px;">
                                    <canvas id="salesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between flex-sm-row flex-column gap-3">
                                <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                                    <div class="card-title">
                                        <h5 class="mb-2">New Orders Today</h5>
                                        <span class="badge bg-label-info rounded-pill">Today</span>
                                    </div>
                                    <div class="mt-sm-auto">
                                        <small class="text-danger fw-semibold"><i class="ri-arrow-down-s-line me-1"></i>-4.7%</small>
                                        <h3 class="mb-0">{{ $newOrdersToday }}</h3>
                                    </div>
                                </div>
                                <div class="chart-container" style="width:150px; height:80px;">
                                    <canvas id="ordersChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Inventory Summary --}}
        <div class="col-lg-4 col-md-12">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Inventory Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3"><i class="ri-stack-fill text-primary" style="font-size:1.5rem;"></i></div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div><h6 class="mb-0">Total Stock Items</h6><small class="text-muted">Unique products in stock</small></div>
                                <div><small class="fw-semibold">{{ $totalUniqueProducts }}</small></div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3"><i class="ri-alert-line text-warning" style="font-size:1.5rem;"></i></div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div><h6 class="mb-0">Low Stock Alerts</h6><small class="text-muted">Products below reorder point</small></div>
                                <div><small class="fw-semibold text-danger">{{ $lowStockProductsCount }}</small></div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3"><i class="ri-close-circle-line text-danger" style="font-size:1.5rem;"></i></div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div><h6 class="mb-0">Out of Stock</h6><small class="text-muted">Products with zero quantity</small></div>
                                <div><small class="fw-semibold text-danger">{{ $outOfStockProductsCount }}</small></div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="col-12 mt-4">
            <div class="card">
                <h5 class="card-header">Recent Orders</h5>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td><i class="ri-briefcase-line text-primary me-3"></i> <strong>#{{ $order->id }}</strong></td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>UGX {{ number_format($order->total_amount,2) }}</td>
                                        <td>
                                            @if($order->status=='pending')<span class="badge bg-label-warning">Pending</span>
                                            @elseif($order->status=='shipped')<span class="badge bg-label-info">Shipped</span>
                                            @else<span class="badge bg-label-success">Delivered</span>@endif
                                        </td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ri-more-2-line"></i></button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ url('/wholesaler/orders/'.$order->id) }}"><i class="ri-eye-line me-1"></i> View</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center">No recent orders found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products to Reorder --}}
        <div class="col-lg-6 col-md-12 mt-4">
            <div class="card h-100">
                <div class="card-header"><h5 class="m-0">Products to Reorder</h5></div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        @forelse($productsToReorder as $p)
                            <li class="d-flex mb-4 pb-1">
                                <div class="avatar flex-shrink-0 me-3"><i class="ri-shopping-bag-line text-primary" style="font-size:1.5rem;"></i></div>
                                <div class="d-flex w-100 align-items-center justify-content-between">
                                    <div><h6 class="mb-0">{{ $p->name }}</h6><small class="text-muted">{{ $p->sku }}</small></div>
                                    <div class="d-flex align-items-center gap-1"><h6 class="mb-0">{{ $p->current_stock }}/{{ $p->reorder_point }}</h6><i class="ri-arrow-down-line text-danger"></i></div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center">Nothing to reorder.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Key Suppliers --}}
        <div class="col-lg-6 col-md-12 mt-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Key Suppliers</h5>
                    <a href="{{ url('/wholesaler/vendors') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        @forelse($keySuppliers as $s)
                            <li class="d-flex mb-4 pb-1">
                                <div class="avatar flex-shrink-0 me-3"><i class="ri-building-line text-success" style="font-size:1.5rem;"></i></div>
                                <div class="d-flex w-100 align-items-center justify-content-between">
                                    <div><h6 class="mb-0">{{ $s->name }}</h6><small class="text-muted">{{ $s->contact_person }}</small></div>
                                    <div><span class="badge bg-label-primary">{{ $s->total_orders_this_month }} orders</span></div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center">No key suppliers found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js Scripts --}}
    <style>.chart-container{position:relative;width:150px;height:80px;}.chart-container canvas{position:absolute;top:0;left:0;width:100% !important;height:100% !important;}</style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
      new Chart(document.getElementById('salesChart'),{type:'line',data:{labels:@json($salesChartLabels),datasets:[{label:'Sales',data:@json($salesChartData),borderColor:'rgba(75, 192, 192, 1)',fill:false}]},options:{responsive:true,maintainAspectRatio:false}});
      new Chart(document.getElementById('ordersChart'),{type:'line',data:{labels:@json($ordersChartLabels),datasets:[{label:'Orders',data:@json($ordersChartData),borderColor:'rgba(153, 102, 255, 1)',fill:false}]},options:{responsive:true,maintainAspectRatio:false}});
    });
    </script>
@endsection
