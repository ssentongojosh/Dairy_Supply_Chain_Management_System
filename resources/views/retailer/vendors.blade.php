@extends('layouts/contentNavbarLayout')

@section('title', 'Browse Wholesalers')

@section('content')
<div class="row">
  <div class="col-12">
    <h4 class="mb-4">Available Wholesalers</h4>
    <div class="list-group">
      @foreach($wholesalers as $w)
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h5 class="mb-1">{{ $w->name }}</h5>
            <p class="mb-1">{{ $w->email }}</p>
          </div>
          <div>
            <!-- Add as Key Supplier -->
            <form method="POST" action="{{ route('retailer.vendors.addKey', $w) }}" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-sm btn-success me-2">
                <i class="ri-store-3-line me-1"></i> Add Key Supplier
              </button>
            </form>
            <!-- View Vendor Products -->
            <a href="{{ route('retailer.vendors.products', $w) }}" class="btn btn-sm btn-info">
              <i class="ri-eye-line me-1"></i> View Products
            </a>
          </div>
        </div>
        <hr>
      @endforeach
    </div>
  </div>
</div>
@endsection
