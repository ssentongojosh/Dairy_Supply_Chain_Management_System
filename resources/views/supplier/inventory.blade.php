<!-- layout -->
 @extends('layouts.contentNavbarLayout')

@section('content')
<div class="container my-5">
  <h2 class="mb-4 text-center font-bold text-xl">Raw Materials Purchased</h2>

  <div class="row">
    @forelse ($rawMaterials as $item)
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
          <!-- Placeholder image only -->
          <!-- <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="{{ $item->name }}"> -->

          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ $item->name }}</h5>
            <p class="card-text text-muted">{{ Str::limit($item->description, 80) }}</p>
            <p class="card-text fw-bold">Price: UGX {{ number_format($item->price) }} / {{ $item->unit_type }}</p>
            <a href="{{ route('delivery.create', ['product_id' => $product->id]) }}" class="btn btn-primary mt-auto">Order Now</a>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <p class="text-center text-muted">No Raw Materials are currently available for Purchase.</p>
      </div>
    @endforelse
  </div>
</div>
@endsection
