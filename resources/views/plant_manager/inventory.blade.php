{{-- Use the main layout file so the page gets the navbar, footer, etc. --}}
@extends('layouts.contentNavbarLayout')

{{-- Start of the content section --}}
@section('content')
<div class="container py-4">

    {{-- Page Title --}}
    <h1 class="mb-4">🐮 Manager Inventory Dashboard</h1>

    <!-- ========================
         ✅ INVENTORY SUMMARY CARDS
         These are small boxes showing quick totals
         ======================== -->
    <div class="row mb-4">
        <!-- 📦 Card: Total Finished Products -->
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">🥛 Finished Products</h5>
                    {{-- Show total number of finished products --}}
                   <p class="card-text h3">{{ $products->count() }}</p>
                </div>
            </div>
        </div>

        <!-- 🧪 Card: Total Raw Materials -->
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">🐄 Raw Materials</h5>
                    {{-- Show total number of raw materials --}}
                    <p class="card-text h3">{{ $rawMaterials->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Card: Low Stock Alert -->
        <div class="col-md-3">
            <div class="card text-white bg-warning text-dark mb-3">
                <div class="card-body">
                    <h5 class="card-title">⚠️ Below Threshold</h5>
                    {{-- Show number of items below threshold --}}
                    <p class="card-text h3">{{ $rawMaterials->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Card: Expected Deliveries -->
        <div class="col-md-3">
            <div class="card text-white bg-secondary mb-4">
                <div class="card-body">
                    <h5 class="card-title">🚚  Delivery Activity</h5>

                    <div class="d-flex justify-content-between">
                       <div>
                           <p class="mb-1">📥Incoming</p>
                           <h4>{{ $stats['incoming_deliveries'] ?? 0 }}</h4>
                        </div>
                        <div>
                           <p class="mb-1">📤Outgoing</p>
                           <h4>{{ $stats['outgoing_deliveries'] ?? 0 }}</h4>
                        </div>
                    </div>

                </div>
           </div>
        </div>
        
    </div>

    <!-- ========================
         🔍 SEARCH BAR (Optional Feature)
         Just a placeholder here for now
         ======================== -->
    <div class="mb-4">
        <input type="text" class="form-control" placeholder="🔍 Search inventory... ">
    </div>

    <!-- ========================
         ✅ FINISHED PRODUCTS TABLE
         Shows a full list of all finished products
         ======================== -->
    <div class="card mb-5">

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h5 class="card-title mb-0">Inventory Management</h5>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                        <i class="ri-add-line me-1"></i> Add Product
                    </button>
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
                        <th>Status</th>
                        <th>Added On</th>
                        <th>Actions</th> {{-- For buttons like "View" --}}
                    </tr>
                </thead>
                {{-- Loop through each product and display in the table --}}
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>{{ $product->status ?? 'Available' }}</td>
                            <td>{{ $product->created_at->format('Y-m-d') }}</td>
                            <td>
                                {{-- Button to view product details --}}
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================
         ✅ RAW MATERIALS TABLE
         Shows a full list of all raw materials
         ======================== -->
    <div class="card">

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h5 class="card-title mb-0">Inventory Management</h5>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                        <i class="ri-add-line me-1"></i> Add Raw Material
                    </button>
                </div>
        </div>

        {{-- Table title / header --}}
        <div class="card-header bg-success text-white">
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
                            <td>{{ $material->expiry_date ?? 'N/A' }}</td>
                            <td>{{ $material->status ?? 'Available' }}</td>
                            <td>
                                {{-- Button to view raw material details --}}
                                <a href="{{ route('raw_materials.show', $material->id) }}" class="btn btn-sm btn-info">
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
@endsection
