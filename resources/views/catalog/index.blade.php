<!-- layout -->
 @extends('layouts.contentNavbarLayout')

@section('content')
<div class="container my-5">
  <h2 class="mb-4 text-center font-bold text-xl">Products Available for Purchase</h2>

  <div class="row">
    @forelse ($products as $product)
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
          <!-- Placeholder image only -->
          <!-- <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="{{ $product->name }}"> -->

          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ $product->name }}</h5>
            <p class="card-text text-muted">{{ Str::limit($product->description, 80) }}</p>
            <p class="card-text fw-bold">Price: UGX {{ number_format($product->price) }} / {{ $product->unit_type }}</p>
            <a href="{{ route('delivery.create', ['product_id' => $product->id]) }}" class="btn btn-primary mt-auto">Order Now</a>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <p class="text-center text-muted">No products are currently available for order.</p>
      </div>
    @endforelse
  </div>
</div>
@endsection
