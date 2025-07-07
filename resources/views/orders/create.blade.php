@extends('layouts.contentNavbarLayout')

@section('content')
<h2 class="text-xl font-bold mb-4">Place New Order</h2>

<form action="{{ route('orders.store') }}" method="POST">
        @csrf

    <label>Choose Seller</label>
    <select name="seller_id" required>
                @foreach($sellers as $seller)
            <option value="{{ $seller->id }}">{{ $seller->name }} ({{ $seller->role }})</option>
                @endforeach
            </select>

    <h3 class="mt-4 font-semibold">Select Products</h3>

    @foreach($products as $product)
        <div class="mb-2">
            <label>
                <input type="checkbox" name="products[{{ $product->id }}][id]" value="{{ $product->id }}">
                {{ $product->name }} - {{ $product->price }} UGX ({{ $product->type }})
                    </label>
            <input type="number" name="products[{{ $product->id }}][quantity]" min="1" placeholder="Qty">
            </div>
@endforeach

    <button type="submit" class="btn btn-primary mt-4">Place Order</button>
</form>
@endsection