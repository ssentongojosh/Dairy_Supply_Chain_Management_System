@extends('layouts/contentNavbarLayout')

@section('title', 'DSCMS - Analytics Dashboard')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
@vite('resources/assets/js/dashboards-analytics.js')
@endsection

@section('content')
<div class="row gy-6">
  <!-- Welcome card -->
  <div class="col-md-12 col-lg-4">
    <div class="card">
      <div class="card-body text-nowrap">
        <h5 class="card-title mb-0 flex-wrap text-nowrap">Welcome to DSCMS! 🥛</h5>
        <p class="mb-2">Dairy Supply Chain Analytics</p>
        <h4 class="text-primary mb-0">{{ number_format(rand(150000, 250000)) }} L</h4>
        <p class="mb-2">Daily milk processed 📈</p>
        <a href="javascript:;" class="btn btn-sm btn-primary">View Details</a>
      </div>
      <img src="{{asset('assets/img/illustrations/trophy.png')}}" class="position-absolute bottom-0 end-0 me-5 mb-5" width="83" alt="view details">
    </div>
  </div>
  <!--/ Welcome card -->

  <!-- Supply Chain Metrics -->
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Supply Chain Overview</h5>
          <div class="dropdown">
            <button class="btn text-muted p-0" type="button" id="supplyChainID" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="ri-more-2-line ri-24px"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="supplyChainID">
              <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
              <a class="dropdown-item" href="javascript:void(0);">Export</a>
              <a class="dropdown-item" href="javascript:void(0);">Settings</a>
            </div>
          </div>
        </div>
        <p class="small mb-0"><span class="h6 mb-0">{{ rand(85, 95) }}% efficiency</span> 🎯 this month</p>
      </div>
      <div class="card-body pt-lg-10">
        <div class="row g-6">
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center">
              <div class="avatar">
                <div class="avatar-initial bg-primary rounded shadow-xs">
                  <i class="ri-plant-line ri-24px"></i>
                </div>
              </div>
              <div class="ms-3">
                <p class="mb-0">Farmers</p>
                <h5 class="mb-0">{{ rand(45, 78) }}</h5>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center">
              <div class="avatar">
                <div class="avatar-initial bg-success rounded shadow-xs">
                  <i class="ri-truck-line ri-24px"></i>
                </div>
              </div>
              <div class="ms-3">
                <p class="mb-0">Deliveries</p>
                <h5 class="mb-0">{{ rand(120, 180) }}</h5>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center">
              <div class="avatar">
                <div class="avatar-initial bg-warning rounded shadow-xs">
                  <i class="ri-store-2-line ri-24px"></i>
                </div>
              </div>
              <div class="ms-3">
                <p class="mb-0">Retailers</p>
                <h5 class="mb-0">{{ rand(25, 45) }}</h5>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center">
              <div class="avatar">
                <div class="avatar-initial bg-info rounded shadow-xs">
                  <i class="ri-drop-line ri-24px"></i>
                </div>
              </div>
              <div class="ms-3">
                <p class="mb-0">Quality Score</p>
                <h5 class="mb-0">{{ rand(92, 98) }}%</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Supply Chain Metrics -->

  <!-- Weekly Production Chart -->
  <div class="col-xl-4 col-md-6">
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between">
          <h5 class="mb-1">Weekly Production</h5>
          <div class="dropdown">
            <button class="btn text-muted p-0" type="button" id="weeklyProductionDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="ri-more-2-line ri-24px"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="weeklyProductionDropdown">
              <a class="dropdown-item" href="javascript:void(0);">View Report</a>
              <a class="dropdown-item" href="javascript:void(0);">Export Data</a>
              <a class="dropdown-item" href="javascript:void(0);">Settings</a>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body pt-lg-2">
        <div id="weeklyOverviewChart"></div>
        <div class="mt-1 mt-md-3">
          <div class="d-flex align-items-center gap-4">
            <h4 class="mb-0">+12%</h4>
            <p class="mb-0">Production is up 12% 📈 compared to last week</p>
          </div>
          <div class="d-grid mt-3 mt-md-4">
            <button class="btn btn-primary" type="button">View Details</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Weekly Production Chart -->

  <!-- Revenue Overview -->
  <div class="col-xl-4 col-md-6">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0 me-2">Monthly Revenue</h5>
        <div class="dropdown">
          <button class="btn text-muted p-0" type="button" id="monthlyRevenue" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ri-more-2-line ri-24px"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="monthlyRevenue">
            <a class="dropdown-item" href="javascript:void(0);">Last 30 Days</a>
            <a class="dropdown-item" href="javascript:void(0);">This Quarter</a>
            <a class="dropdown-item" href="javascript:void(0);">This Year</a>
          </div>
        </div>
      </div>
      <div class="card-body pt-lg-8">
        <div class="mb-5 mb-lg-12">
          <div class="d-flex align-items-center">
            <h3 class="mb-0">${{ number_format(rand(180000, 320000)) }}</h3>
            <span class="text-success ms-2">
              <i class="ri-arrow-up-s-line"></i>
              <span>{{ rand(8, 15) }}%</span>
            </span>
          </div>
          <p class="mb-0">Compared to last month</p>
        </div>
        <ul class="p-0 m-0">
          <li class="d-flex mb-6">
            <div class="avatar flex-shrink-0 bg-lightest rounded me-3">
              <i class="ri-drop-line text-primary ri-24px"></i>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Fresh Milk</h6>
                <p class="mb-0">Direct from farmers</p>
              </div>
              <div>
                <h6 class="mb-2">${{ number_format(rand(80000, 120000)) }}</h6>
                <div class="progress bg-label-primary" style="height: 4px;">
                  <div class="progress-bar bg-primary" style="width: {{ rand(65, 85) }}%" role="progressbar"></div>
                </div>
              </div>
            </div>
          </li>
          <li class="d-flex mb-6">
            <div class="avatar flex-shrink-0 bg-lightest rounded me-3">
              <i class="ri-gift-line text-info ri-24px"></i>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Processed Products</h6>
                <p class="mb-0">Cheese, Yogurt, Butter</p>
              </div>
              <div>
                <h6 class="mb-2">${{ number_format(rand(40000, 80000)) }}</h6>
                <div class="progress bg-label-info" style="height: 4px;">
                  <div class="progress-bar bg-info" style="width: {{ rand(55, 75) }}%" role="progressbar"></div>
                </div>
              </div>
            </div>
          </li>
          <li class="d-flex">
            <div class="avatar flex-shrink-0 bg-lightest rounded me-3">
              <i class="ri-shopping-cart-line text-secondary ri-24px"></i>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Retail Sales</h6>
                <p class="mb-0">Direct to consumer</p>
              </div>
              <div>
                <h6 class="mb-2">${{ number_format(rand(30000, 60000)) }}</h6>
                <div class="progress bg-label-secondary" style="height: 4px;">
                  <div class="progress-bar bg-secondary" style="width: {{ rand(45, 65) }}%" role="progressbar"></div>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <!--/ Revenue Overview -->

  <!-- Quality Metrics -->
  <div class="col-xl-4 col-md-6">
    <div class="row gy-6">
      <!-- Milk Quality Score -->
      <div class="col-sm-6">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h4 class="mb-0">{{ rand(92, 98) }}%</h4>
          </div>
          <div class="card-body">
            <div id="totalProfitLineChart" class="mb-3"></div>
            <h6 class="text-center mb-0">Quality Score</h6>
          </div>
        </div>
      </div>
      <!--/ Milk Quality Score -->
      <!-- Supply Chain Efficiency -->
      <div class="col-sm-6">
        <div class="card h-100">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div class="avatar">
              <div class="avatar-initial bg-success rounded-circle shadow-xs">
                <i class="ri-truck-line ri-24px"></i>
              </div>
            </div>
            <div class="dropdown">
              <button class="btn text-muted p-0" type="button" id="efficiencyID" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="ri-more-2-line ri-24px"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="efficiencyID">
                <a class="dropdown-item" href="javascript:void(0);">View Report</a>
                <a class="dropdown-item" href="javascript:void(0);">Export</a>
                <a class="dropdown-item" href="javascript:void(0);">Settings</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <h6 class="mb-1">Delivery Efficiency</h6>
            <div class="d-flex flex-wrap mb-1 align-items-center">
              <h4 class="mb-0 me-2">{{ rand(85, 95) }}%</h4>
              <p class="text-success mb-0">+{{ rand(5, 12) }}%</p>
            </div>
            <small>On-time deliveries</small>
          </div>
        </div>
      </div>
      <!--/ Supply Chain Efficiency -->
    </div>
  </div>
  <!--/ Quality Metrics -->
</div>
@endsection
