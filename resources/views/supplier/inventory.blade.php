<!-- layout -->
 @extends('layouts.contentNavbarLayout')

@section('content')
<div class="container my-5">
  <h2 class="mb-4 text-center font-bold text-xl">Raw Materials Purchased</h2>

  <!-- view their deliveries -->
   <div class="text-end mb-3">
    <a href="{{ route('delivery.mine') }}" class="btn btn-outline-primary">
        <i class="ri-truck-line"></i> View My Deliveries
    </a>
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

