@extends('layouts/contentNavbarLayout')

@section('title', 'Farmer Dashboard')

@section('content')
    <div class="row">
        {{-- Welcome Card --}}
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Welcome {{ $user->name }}! 🥛</h5>
                            <p class="mb-4">
                                You have <span class="fw-bold">{{ $pendingOrdersCount }}</span> pending orders from factories and wholesalers.
                                Keep up the great dairy production!
                            </p>
                            <a href="{{ route('farmer.orders') }}" class="btn btn-sm btn-outline-primary">View Orders</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('assets/img/illustrations/dairy-farm-illustration.png') }}" 
                                 height="140" alt="Farmer Dashboard" 
                                 data-app-dark-img="illustrations/dairy-farm-illustration-dark.png"
                                 data-app-light-img="illustrations/dairy-farm-illustration.png">
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
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <i class="menu-icon tf-icons bx bx-money text-success"></i>
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Monthly Sales</span>
                            <h3 class="card-title mb-2">UGX {{ number_format($totalSalesThisMonth) }}</h3>
                            <div class="chart-container">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <i class="menu-icon tf-icons bx bx-package text-warning"></i>
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Orders Today</span>
                            <h3 class="card-title mb-2">{{ $newOrdersToday }}</h3>
                            <div class="chart-container">
                                <canvas id="ordersChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Milk Production Summary --}}
        <div class="col-lg-4 col-md-12 order-2">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Milk Production Status</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-droplet"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Total Milk Products</h6>
                                    <small class="text-muted">Available for sale</small>
                                </div>
                                <div class="user-progress">
                                    <span class="fw-semibold">{{ $totalMilkProducts }}</span>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-error-circle"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Low Stock Items</h6>
                                    <small class="text-muted">Need restocking</small>
                                </div>
                                <div class="user-progress">
                                    <span class="fw-semibold text-warning">{{ $lowStockProductsCount }}</span>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="bx bx-x-circle"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Out of Stock</h6>
                                    <small class="text-muted">Require immediate attention</small>
                                </div>
                                <div class="user-progress">
                                    <span class="fw-semibold text-danger">{{ $outOfStockProductsCount }}</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Recent Orders Table --}}
        <div class="col-12 order-3 mt-4">
            <div class="card">
                <h5 class="card-header">Recent Orders from Buyers</h5>
                <div class="card-body">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive text-nowrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Buyer</th>
                                        <th>Items</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td><strong>#{{ $order->id }}</strong></td>
                                            <td>{{ $order->buyer->name }}</td>
                                            <td>{{ $order->items->count() }} item(s)</td>
                                            <td>UGX {{ number_format($order->total_amount ?? 0) }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($order->status === 'pending') bg-label-warning
                                                    @elseif($order->status === 'approved') bg-label-success
                                                    @elseif($order->status === 'shipped') bg-label-info
                                                    @elseif($order->status === 'received') bg-label-primary
                                                    @else bg-label-secondary
                                                    @endif">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('farmer.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bx bx-show"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-package bx-lg text-muted"></i>
                            <p class="mt-2 text-muted">No recent orders found</p>
                            <a href="{{ route('farmer.products') }}" class="btn btn-primary">Add Products</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Products to Restock --}}
        <div class="col-lg-6 col-md-12 order-4 mt-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0">Products Need Attention</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($productsToRestock->count() > 0)
                        @foreach($productsToRestock as $item)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded-circle bg-label-warning">
                                            <i class="bx bx-droplet"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $item->product->name }}</h6>
                                        <small class="text-muted">Current: {{ $item->quantity }}L</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <small class="text-warning">Low Stock</small>
                                </div>
                            </div>
                        @endforeach
                        <div class="mt-3">
                            <a href="{{ route('farmer.inventory') }}" class="btn btn-sm btn-outline-warning w-100">
                                <i class="bx bx-list-ul me-1"></i> Manage Inventory
                            </a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bx bx-check-circle bx-lg text-success"></i>
                            <p class="mt-2 mb-0 text-muted">All products are well stocked!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Key Buyers Information --}}
        <div class="col-lg-6 col-md-12 order-5 mt-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0">Top Buyers</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($keyBuyers->count() > 0)
                        @foreach($keyBuyers as $buyer)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            {{ substr($buyer->buyer->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $buyer->buyer->name }}</h6>
                                        <small class="text-muted">{{ $buyer->order_count }} orders</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fw-semibold">UGX {{ number_format($buyer->total_spent) }}</span>
                                </div>
                            </div>
                        @endforeach
                        <div class="mt-3">
                            <a href="{{ route('farmer.orders') }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bx bx-list-ul me-1"></i> View All Orders
                            </a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bx bx-user bx-lg text-muted"></i>
                            <p class="mt-2 mb-0 text-muted">No buyers yet. Start selling your dairy products!</p>
                        </div>
                    @endif
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
              label: 'Sales (UGX)',
              data: @json($salesChartData),
              borderColor: 'rgba(75, 192, 192, 1)',
              backgroundColor: 'rgba(75, 192, 192, 0.2)',
              fill: true,
              tension: 0.4
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              x: {
                display: false
              },
              y: {
                display: false
              }
            }
          }
        });

        // Orders Chart
        var ordersCtx = document.getElementById('ordersChart').getContext('2d');
        new Chart(ordersCtx, {
          type: 'bar',
          data: {
            labels: @json($ordersChartLabels),
            datasets: [{
              label: 'Orders',
              data: @json($ordersChartData),
              backgroundColor: 'rgba(255, 159, 64, 0.8)',
              borderColor: 'rgba(255, 159, 64, 1)',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              x: {
                display: false
              },
              y: {
                display: false
              }
            }
          }
        });
      });
    </script>
@endsection
