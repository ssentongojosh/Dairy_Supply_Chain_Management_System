@extends('layouts.contentNavbarLayout')

@section('title', 'Wholesalers')

@section('content')
<div class="row mb-4">
  <div class="col-12">
    <h4>All Wholesalers</h4>
    <p class="text-muted">Browse and select from available suppliers</p>
  </div>
</div>

<div class="row">
  @foreach($wholesalers as $wholesaler)
    <div class="col-md-4 mb-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">{{ $wholesaler->name }}</h5>
          <p class="card-text"><i class="ri-mail-line me-1"></i>{{ $wholesaler->email }}</p>
          <p class="card-text"><small class="text-muted">Joined {{ $wholesaler->created_at->format('M d, Y') }}</small></p>
          <a href="{{ route('retailer.orders') }}?supplier={{ $wholesaler->id }}" class="btn btn-primary btn-sm">
            View Products
          </a>
        </div>
      </div>
    </div>
  @endforeach
</div>
@endsection
