@extends('layouts/contentNavbarLayout')

@section('title', 'Wholesaler Products')

@section('content')
<div class="row">
  <div class="col-12">
    <h4 class="mb-4">Products from {{ $wholesaler->name }}</h4>
    <a href="{{ route('retailer.vendors') }}" class="btn btn-secondary mb-3">
      <i class="ri-arrow-go-back-line me-1"></i> Back to Wholesalers
    </a>
    @if($products->isEmpty())
      <div class="alert alert-info">No active products found for this wholesaler.</div>
    @else
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th>SKU</th>
              <th>Price</th>
              <th>Available Quantity</th>
            </tr>
          </thead>
          <tbody>
            @foreach($products as $product)
              <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->sku }}</td>
                <td>${{ number_format($product->price, 2) }}</td>
                <td>
                  {{ optional($product->inventory->first())->quantity ?? 0 }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
