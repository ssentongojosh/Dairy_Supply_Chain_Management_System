<!-- layout -->
 @extends('layouts.contentNavbarLayout')

@section('content')
<div class="container my-5">
  <!-- heading -->
  <h2 class="mb-4 text-center font-bold text-xl">Raw Materials Purchased</h2>
  <div class="d-flex justify-content-between align-items-center mb-4">
  <form method="GET" action="{{ route('inventory.raw_materials') }}" class="mb-4">
                <input type="text" name="search" placeholder="Search product by name..." value="{{ request('search') }}"class="border px-3 py-1 rounded w-1/2">
                <button type="submit" class="bg-blue-500 text-black px-3 py-1 rounded">Search</button>

                <!-- clear search -->
                 @if(request('search'))
                   <a href="{{ route('inventory.raw_materials') }}" class="btn btn-outline-secondary">
                   <i class="ri-refresh-line"></i> Clear
                   </a>
                @endif
            </form>

      <!-- create a delivery -->
       <span><a href="{{ route('delivery.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-1"></i> Send Delivery
        </a>      

  <!-- view their deliveries -->
   <!--<div class="text-end mb-3">-->
    <a href="{{ route('delivery.mine') }}" class="btn btn-outline-primary">
        <i class="ri-truck-line"></i> View My Deliveries
    </a>
   </span>
  </div>

  <div class="row">
    @forelse ($rawMaterials as $item)
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
          <!-- Placeholder image only -->
          <img src="{{ asset('images/raw_materials/' . $item->image) }}" class="card-img-top" alt="{{ $item->name }}" style="height: 100px; object-fit: cover;">

          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ $item->name }}</h5>
            <p class="card-text text-muted">{{ Str::limit($item->description, 80) }}</p>
            <p class="card-text fw-bold">Stock: {{ number_format($item->quantity) }}  {{ $item->unit }}</p>
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
