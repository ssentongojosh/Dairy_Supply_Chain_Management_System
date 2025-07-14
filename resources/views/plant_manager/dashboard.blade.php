{{-- Use the main layout file so the page gets the navbar, footer, etc. --}}
@extends('layouts.contentNavbarLayout')

{{-- Start of the content section --}}
@section('content')
<div class="container py-4">

    {{-- Page Title --}}
    <h1 class="mb-4">🐮 Manager Inventory Dashboard</h1>

    <!-- INVENTORY SUMMARY CARDS -->
    <div class="row mb-4">
        <!-- 📦 Card: Total Finished Products -->
        <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100 border-start border-4 border-primary">
                    <div class="card-body d-flex align-items-center ">
                       
                        <div class="avatar flex-shrink-0 bg-label-primary me-3">
                            <i class="ri-archive-line fs-3"></i> 
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Products</h5>
                            <small class="fw-bold">{{ $products->count() }}</small>
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

        <!-- Card: Low Stock Alert -->
        <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100 border-start border-4 border-warning">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar flex-shrink-0 bg-label-danger me-3">
                            <i class="ri-alert-line fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Low Stock</h5>
                            <small class="text-muted fw-bold">{{ $totalLowStock }}</small>
                        </div>
                    </div>
                </div>
        </div>           

        <!-- Card: Expected Deliveries -->
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100 border-start border-4 border-secondary">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar flex-shrink-0 bg-label-secondary me-3">
                       <i class="ri-truck-line fs-3"></i>
                    </div>
                    <div>
                       <h5 class="card-title mb-0">Today's Deliveries</h5>
                       <small class="text-muted fw-bold">{{ $todayDeliveriesCount }}</small>
                    </div>
                </div>
            </div>
        </div>
     
    </div>

    <!-- 🔍 SEARCH BAR (Optional Feature) -->
    <div class="mb-4">
        <input type="text" class="form-control" placeholder="🔍 Search inventory... ">
    </div>

    <!-- ✅ FINISHED PRODUCTS TABLE -->
    <div class="card mb-5">

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h5 class="card-title mb-0">Product Management</h5>
                <div class="d-flex align-items-center">

                    <!-- button to add an item -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="ri-add-line me-2"></i> Add Product
                    </button>

                    <!-- button for product deliveries -->
                    <a href="{{ route('inventory.search') }}" class="btn btn-outline-primary btn-sm ms-2">
                       <i class="ri-shopping-cart-line"></i> Orders
                    </a>
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
                    @foreach($products as $product)
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

<!--Add product modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      {{-- Modal Header --}}
      <div class="modal-header">
        <h5 class="modal-title" id="addProductLabel">🧀 Add Finished Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      {{-- Modal Body --}}
      <div class="modal-body">
        <form action="{{ route('product.store') }}" method="POST">
          @csrf

          {{-- Item Name --}}
          <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>

          {{-- Quantity --}}
          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" name="quantity" class="form-control" min="1" required>
          </div>

          {{-- Price --}}
          <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="number" name="price" class="form-control" min="1" required>
          </div>

          {{-- Expiry Date --}}
          <div class="mb-3">
            <label class="form-label">Added on</label>
            <input type="date" name="manufacture_date" class="form-control" required>
          </div>

          {{-- Buttons --}}
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Product</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<!-- modal for add raw material -->
 <div class="modal fade" id="addRawMaterialModal" tabindex="-1" aria-labelledby="addRawLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      {{-- Modal Header --}}
      <div class="modal-header">
        <h5 class="modal-title" id="addRawLabel">🐄 Add Raw Material</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      {{-- Modal Body --}}
      <div class="modal-body">
        <form action="{{ route('raw_materials.store') }}" method="POST">
          @csrf

          {{-- Material Name --}}
          <div class="mb-3">
            <label class="form-label">Material Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>

          {{-- Quantity --}}
          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" min="1" required>
          </div>

          <!-- expiry -->
          <div class="mb-3">
            <label class="form-label">Expiry</label>
            <input type="date" name="expiry" class="form-control" required>
          </div>

          <!-- Buttons -->
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
