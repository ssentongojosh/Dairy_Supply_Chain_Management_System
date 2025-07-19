<!-- layout -->
 @extends('layouts.contentNavbarLayout')

 @section('head')
 <style>
.card.hover-lift {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card.hover-lift:hover {
  transform: translateY(-10px) scale(1.02); /* lift up */
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); /* soft shadow */
  cursor: pointer;
}
</style>
@endsection

@section('content')
<div class="container my-5">
    <div>
        <h2 class="mb-4 text-center font-bold text-xl">Our Dairy Products</h2>
            <form method="GET" action="{{ route('catalog.retailer') }}" class="mb-4">
                <input type="text" name="search" placeholder="Search product by name..." value="{{ request('search') }}"class="border px-3 py-1 rounded w-1/2">
                <button type="submit" class="bg-blue-500 text-black px-3 py-1 rounded">Search</button>

                <!-- clear search -->
                 @if(request('search'))
                   <a href="{{ route('catalog.retailer') }}" class="btn btn-outline-secondary">
                   <i class="ri-refresh-line"></i> Clear
                   </a>
                @endif
            </form>




  <div class="row"> 
    @forelse ($products as $product)
      <div class="col-md-3 mb-4">
        <div class="card hover-lift h-100 shadow-sm" style="max-height: 350px; ">
          <div style="overflow: hidden;">
          <!-- Placeholder image only -->
          <img src="{{ asset('images/products/' . $product->image) }}" alt="{{ $product->name }}" class="card-img-top" style="height: 100px; object-fit: cover;">
          </div>

          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ $product->name }}</h5>
            <a href="{{ route('retailer.orders.create', ['product_id' => $product->id]) }}" class="btn btn-primary mt-auto">Order Now</a>
            
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
