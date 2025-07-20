{{-- filepath: c:\xampp\htdocs\DSCMS\resources\views\farmer\dashboard.blade.php --}}
@extends('layouts/contentNavbarLayout')

@section('title', 'Farmer Dashboard')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/dashboards-analytics.js')}}"></script>
@endsection

@section('content')
<div class="row">
  <!-- Welcome Section -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="card-title mb-1">Welcome back, {{ $user->name }}! 👋</h4>
            <p class="mb-0">Here's what's happening with your dairy supply business today.</p>
          </div>
          <div class="text-end">
            <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
  <!-- Statistics Cards -->
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <a href="{{ route('farmer.orders', ['status' => 'pending']) }}">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="card-info">
            <p class="card-text">Pending Orders</p>
            <div class="d-flex align-items-end mb-2">
              <h4 class="card-title mb-0 me-2">{{ $pendingOrdersCount }}</h4>
              <small class="text-warning">Awaiting Action</small>
            </div>
          </div>
          <div class="card-icon">
            <span class="badge bg-label-warning rounded p-2">
              <i class="bx bx-time-five bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    </a>
  </div>
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <a href="{{ route('farmer.orders', ['status' => 'pending']) }}">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="card-info">
            <p class="card-text">New Orders Today</p>
            <div class="d-flex align-items-end mb-2">
              <h4 class="card-title mb-0 me-2">{{ $newOrdersToday }}</h4>
              <small class="text-success">Fresh Orders</small>
            </div>
          </div>
          <div class="card-icon">
            <span class="badge bg-label-success rounded p-2">
              <i class="bx bx-cart bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    </a>
  </div>
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="card-info">
            <p class="card-text">Total Products</p>
            <div class="d-flex align-items-end mb-2">
              <h4 class="card-title mb-0 me-2">{{ $inventoryStats['total_products'] ?? 0 }} </h4>
              <small class="text-info">In Inventory</small>
            </div>
          </div>
          <div class="card-icon">
            <span class="badge bg-label-info rounded p-2">
              <i class="bx bx-package bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="card-info">
            <p class="card-text">Monthly Revenue</p>
            <div class="d-flex align-items-end mb-2">
              <h4 class="card-title mb-0 me-2">${{ number_format($totalSalesThisMonth, 2) }}</h4>
              @if($salesGrowth = 0)
                <small class="text-success">+{{ number_format($salesGrowth, 1) }}%</small>
              @elseif($salesGrowth < 0)
                <small class="text-danger">{{ number_format($salesGrowth, 1) }}%</small>
              @else
                <small class="text-muted">0%</small>
              @endif
            </div>
          </div>
          <div class="card-icon">
            <span class="badge bg-label-primary rounded p-2">
              <i class="bx bx-dollar bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
</div>

<div class="row">
  <!-- Sales Chart -->
  <div class="col-lg-8 col-12 mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Weekly Sales Overview</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="salesChart" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="bx bx-dots-vertical-rounded"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesChart">
            <a class="dropdown-item" href="{{ route('farmer.orders.history') }}">View All Orders</a>
            <a class="dropdown-item" href="{{ route('farmer.inventory') }}">Manage Inventory</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="salesChart"></div>
      </div>
    </div>
  </div>

  <!-- Inventory Status -->
  <div class="col-lg-4 col-12 mb-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">Inventory Status</h5>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-success">
                <i class="bx bx-check"></i>
              </span>
            </div>
            <div>
              <p class="mb-0">In Stock</p>
              <h6 class="mb-0">{{ $inventoryStats['total_products'] ?? 0  - $outOfStockProductsCount - $lowStockProductsCount }}</h6>
            </div>
          </div>
          <div class="user-progress">

            <small class="fw-semibold">
                {{ (
                    ($inventoryStats['total_products'] ?? 0) > 0
                        ? round((($inventoryStats['total_products'] - $outOfStockProductsCount - $lowStockProductsCount) / $inventoryStats['total_products']) * 100, 1)
                        : 0
                ) }}%
            </small>

          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="bx bx-error"></i>
              </span>
            </div>
            <div>
              <p class="mb-0">Low Stock</p>
              <h6 class="mb-0">{{ $lowStockProductsCount }}</h6>
            </div>
          </div>
          <div class="user-progress">
          <small class="fw-semibold">
                {{ (
                    ($inventoryStats['total_products'] ?? 0) > 0
                        ? round((($inventoryStats['total_products'] - $outOfStockProductsCount - $lowStockProductsCount) / $inventoryStats['total_products']) * 100, 1)
                        : 0
                ) }}%
            </small>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="bx bx-x"></i>
              </span>
            </div>
            <div>
              <p class="mb-0">Out of Stock</p>
              <h6 class="mb-0">{{ $outOfStockProductsCount }}</h6>
            </div>
          </div>
          <div class="user-progress">
          <small class="fw-semibold">
                {{ (
                    ($inventoryStats['total_products'] ?? 0) > 0
                        ? round((($inventoryStats['total_products'] - $outOfStockProductsCount - $lowStockProductsCount) / $inventoryStats['total_products']) * 100, 1)
                        : 0
                ) }}%
            </small>
          </div>
        </div>

        @if($lowStockProductsCount > 0 || $outOfStockProductsCount > 0)
        <div class="mt-3">
          <a href="{{ route('farmer.inventory') }}" class="btn btn-warning btn-sm w-100">
            <i class="bx bx-plus me-1"></i>Restock Items
          </a>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Recent Orders -->
  <div class="col-lg-8 col-12 mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Recent Orders</h5>
        <a href="{{ route('farmer.orders') }}" class="btn btn-primary btn-sm">View All</a>
      </div>
      <div class="card-body">
        @forelse($recentOrders as $order)
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded-circle
                @switch($order->status)
                  @case('pending') bg-label-warning @break
                  @case('approved') bg-label-info @break
                  @case('shipped') bg-label-primary @break
                  @case('delivered') bg-label-success @break
                  @default bg-label-secondary
                @endswitch
              ">
                {{ substr($order->buyer->name, 0, 2) }}
              </span>
            </div>
            <div>
              <h6 class="mb-0">{{ $order->buyer->name }}</h6>
              <small class="text-muted">
                Order #{{ $order->id }} • {{ $order->created_at->diffForHumans() }}
              </small>
            </div>
          </div>
          <div class="text-end">
            <h6 class="mb-0">${{ number_format($order->total_amount ?? 0, 2) }}</h6>
            <span class="badge
              @switch($order->status)
                @case('pending') bg-label-warning @break
                @case('approved') bg-label-info @break
                @case('shipped') bg-label-primary @break
                @case('delivered') bg-label-success @break
                @default bg-label-secondary
              @endswitch
            ">
              {{ ucfirst($order->status) }}
            </span>
          </div>
        </div>
        @empty
        <div class="text-center py-4">
          <i class="bx bx-cart bx-lg text-muted"></i>
          <p class="text-muted mt-2">No recent orders found</p>
          <a href="{{ route('farmer.inventory') }}" class="btn btn-primary">Add Products to Sell</a>
        </div>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Top Customers -->
  <div class="col-lg-4 col-12 mb-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">Top Customers</h5>
      </div>
      <div class="card-body">
        @forelse($keyBuyers as $buyer)
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded-circle bg-label-primary">
                {{ substr($buyer->buyer->name, 0, 2) }}
              </span>
            </div>
            <div>
              <h6 class="mb-0">{{ $buyer->buyer->name }}</h6>
              <small class="text-muted">{{ $buyer->order_count }} orders</small>
            </div>
          </div>
          <div class="text-end">
            <small class="text-success">${{ number_format($buyer->total_spent, 0) }}</small>
          </div>
        </div>
        @empty
        <div class="text-center py-4">
          <i class="bx bx-user bx-lg text-muted"></i>
          <p class="text-muted mt-2">No customers yet</p>
        </div>
        @endforelse
      </div>
    </div>
  </div>
</div>

@if($productsToRestock->isNotEmpty())
<div class="row">
  <!-- Products to Restock -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">
          <i class="bx bx-error text-warning me-2"></i>Products Needing Attention
        </h5>
        <a href="{{ route('farmer.inventory') }}" class="btn btn-warning btn-sm">Manage Inventory</a>
      </div>
      <div class="card-body">
        <div class="row">
          @foreach($productsToRestock as $item)
          <div class="col-lg-3 col-md-4 col-6 mb-3">
            <div class="card border {{ $item->quantity == 0 ? 'border-danger' : 'border-warning' }}">
              <div class="card-body text-center">
                <div class="mb-2">
                  @if($item->product && $item->product->image)
                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="rounded" width="50" height="50">
                  @else
                    <div class="avatar avatar-lg mx-auto">
                      <span class="avatar-initial rounded bg-label-secondary">
                        <i class="bx bx-package"></i>
                      </span>
                    </div>
                  @endif
                </div>
                <h6 class="mb-1">{{ $item->product->name ?? 'Unknown Product' }}</h6>
                <small class="text-muted">Stock: {{ $item->quantity }}</small>
                @if($item->quantity == 0)
                  <div class="mt-1">
                    <span class="badge bg-danger">Out of Stock</span>
                  </div>
                @else
                  <div class="mt-1">
                    <span class="badge bg-warning">Low Stock</span>
                  </div>
                @endif
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Sales Chart
  const salesChartEl = document.querySelector('#salesChart');
  if (salesChartEl) {
    const salesChartConfig = {
      chart: {
        height: 300,
        type: 'area',
        toolbar: {
          show: false
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 3
      },
      series: [
        {
          name: 'Sales ($)',
          data: {!! json_encode($salesChartData) !!}
        },
        {
          name: 'Orders',
          data: {!! json_encode($ordersChartData) !!}
        }
      ],
      xaxis: {
        categories: {!! json_encode($salesChartLabels) !!}
      },
      colors: ['#696cff', '#8592a3'],
      fill: {
        type: 'gradient',
        gradient: {
          shade: 'dark',
          gradientToColors: ['#696cff', '#8592a3'],
          shadeIntensity: 1,
          type: 'horizontal',
          opacityFrom: 1,
          opacityTo: 1,
          stops: [0, 100, 100, 100]
        }
      }
    };

    const salesChart = new ApexCharts(salesChartEl, salesChartConfig);
    salesChart.render();
  }
});
</script>
@endsection
