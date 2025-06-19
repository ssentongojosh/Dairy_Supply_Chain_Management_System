@extends('layouts/contentNavbarLayout')

@section('title', 'Retailer Dashboard')

@section('content')
{{-- resources/views/retailer/dashboard.blade.php --}}

{{-- @extends('layouts.app') Extends your main layout file --}}

@section('content')
    <div class="row">
        {{-- Welcome Card --}}
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Welcome Back, Retailer! 🎉</h5>
                            <p class="mb-4">
                                You have <span class="fw-bold">{{ $pendingOrdersCount ?? 0 }}</span> pending orders and <span class="fw-bold">{{ $lowStockProductsCount ?? 0 }}</span> products running low on stock. Check your updates!
                            </p>
                            <a href="{{ url('/retailer/orders') }}" class="btn btn-sm btn-outline-primary">View Orders</a>
                            <a href="{{ url('/retailer/inventory') }}" class="btn btn-sm btn-outline-warning ms-2">Manage Inventory</a>

                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}"
                                height="140"
                                alt="View Badge"
                                data-app-light-img="illustrations/man-with-laptop-light.png"
                                data-app-dark-img="illustrations/man-with-laptop-dark.png" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sales & Order Metrics --}}
        <div class="col-lg-8 col-md-12 order-1">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between flex-sm-row flex-column gap-3">
                                <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                                    <div class="card-title">
                                        <h5 class="text-nowrap mb-2">Total Sales (This Month)</h5>
                                        <span class="badge bg-label-warning rounded-pill">Year 2025</span>
                                    </div>
                                    <div class="mt-sm-auto">
                                        <small class="text-success text-nowrap fw-semibold"><i class="ri-arrow-up-s-line me-1"></i>+15.8%</small>
                                        <h3 class="mb-0">UGX {{ number_format($totalSalesThisMonth ?? 0, 2) }}</h3>
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
                                        <h5 class="text-nowrap mb-2">New Orders</h5>
                                        <span class="badge bg-label-info rounded-pill">Today</span>
                                    </div>
                                    <div class="mt-sm-auto">
                                        <small class="text-danger text-nowrap fw-semibold"><i class="ri-arrow-down-s-line me-1"></i>-2.5%</small>
                                        <h3 class="mb-0">{{ $newOrdersToday ?? 0 }}</h3>
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
        <div class="col-lg-4 col-md-12 order-2">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Inventory Summary</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="transactionsID" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ri-more-2-line"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionsID">
                            <a class="dropdown-item" href="javascript:void(0);">Last 7 Days</a>
                            <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                            <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <i class="ri-stack-fill text-primary" style="font-size:1.5rem;"></i>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Total Stock Items</h6>
                                    <small class="text-muted">Unique products in stock</small>
                                </div>
                                <div class="user-progress">
                                    <small class="fw-semibold">{{ $totalUniqueProducts ?? 0 }}</small>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <i class="ri-alert-line text-warning" style="font-size:1.5rem;"></i>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Low Stock Alerts</h6>
                                    <small class="text-muted">Products below reorder point</small>
                                </div>
                                <div class="user-progress">
                                    <small class="fw-semibold text-danger">{{ $lowStockProductsCount ?? 0 }}</small>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <i class="ri-close-circle-line text-danger" style="font-size:1.5rem;"></i>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Out of Stock</h6>
                                    <small class="text-muted">Products with zero quantity</small>
                                </div>
                                <div class="user-progress">
                                    <small class="fw-semibold text-danger">{{ $outOfStockProductsCount ?? 0 }}</small>
                                </div>
                            </div>
                        </li>
                        {{-- Add more inventory metrics --}}
                    </ul>
                </div>
            </div>
        </div>

        {{-- Recent Orders Table --}}
        <div class="col-12 order-3 mt-4">
            <div class="card">
                <h5 class="card-header">Recent Orders</h5>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Order Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td><i class="ri-briefcase-line text-primary me-3"></i> <strong>#{{ $order->id }}</strong></td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>UGX {{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            @if ($order->status == 'pending')
                                                <span class="badge bg-label-warning me-1">Pending</span>
                                            @elseif ($order->status == 'shipped')
                                                <span class="badge bg-label-info me-1">Shipped</span>
                                            @elseif ($order->status == 'delivered')
                                                <span class="badge bg-label-success me-1">Delivered</span>
                                            @else
                                                <span class="badge bg-label-secondary me-1">{{ ucfirst($order->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="ri-more-2-line"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ url('/retailer/orders/' . $order->id) }}"><i class="ri-eye-line me-1"></i> View Details</a>
                                                    {{-- <a class="dropdown-item" href="javascript:void(0);"><i class="ri-edit-line me-1"></i> Edit</a> --}}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No recent orders found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products to Reorder --}}
        <div class="col-lg-6 col-md-12 order-4 mt-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Products to Reorder</h5>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        @forelse($productsToReorder as $product)
                            <li class="d-flex mb-4 pb-1">
                                <div class="avatar flex-shrink-0 me-3">
                                    <i class="ri-shopping-bag-line text-primary" style="font-size:1.5rem;"></i>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">{{ $product->name }}</h6>
                                        <small class="text-muted">{{ $product->sku }}</small>
                                    </div>
                                    <div class="user-progress d-flex align-items-center gap-1">
                                        <h6 class="mb-0">{{ $product->current_stock }} / {{ $product->reorder_point }}</h6>
                                        <i class="ri-arrow-down-line text-danger"></i>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center">No products currently need reordering.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Supplier Information (example) --}}
        <div class="col-lg-6 col-md-12 order-5 mt-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Key Suppliers</h5>
                    <a href="{{ url('/retailer/vendors') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        @forelse($keySuppliers as $supplier)
                            <li class="d-flex mb-4 pb-1">
                                <div class="avatar flex-shrink-0 me-3">
                                    <i class="ri-building-line text-success" style="font-size:1.5rem;"></i>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">{{ $supplier->name }}</h6>
                                        <small class="text-muted">{{ $supplier->contact_person }}</small>
                                    </div>
                                    <div class="user-progress">
                                        <span class="badge bg-label-primary">{{ $supplier->total_orders_this_month ?? 0 }} orders</span>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center">No key suppliers configured or found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

    </div>

    {{-- Include Chart.js Library --}}
    <style>
    .chart-container {
      position: relative;
      width: 150px;
      height: 80px;
    }
    .chart-container canvas {
      position: absolute;
      top: 0;
      left: 0;
      width: 100% !important;
      height: 100% !important;
    }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Sales Chart
        var salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
          type: 'line',
          data: {
            labels: @json($salesChartLabels),
            datasets: [{
              label: 'Sales',
              data: @json($salesChartData),
              borderColor: '#8c57ff',
              backgroundColor: 'rgba(140, 87, 255,0.2)',
              tension: 0.4,
              fill: true
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { display: false }, y: { display: false } }
          }
        });

        // Orders Chart
        var ordersCtx = document.getElementById('ordersChart').getContext('2d');
        new Chart(ordersCtx, {
          type: 'line',
          data: {
            labels: @json($ordersChartLabels),
            datasets: [{
              label: 'Orders',
              data: @json($ordersChartData),
              borderColor: '#1e88e5',
              backgroundColor: 'rgba(30, 136, 229,0.2)',
              tension: 0.4,
              fill: true
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { display: false }, y: { display: false } }
          }
        });
      });
    </script>
@endsection
@endsection
