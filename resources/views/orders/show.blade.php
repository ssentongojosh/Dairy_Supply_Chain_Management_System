@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Order #{{ $order->id }}</h4>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                    <p><strong>Seller:</strong> {{ $order->seller->name ?? '-' }}</p>
                    <p><strong>Buyer:</strong> {{ $order->buyer->name ?? '-' }}</p>
                    <h5>Items</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? '-' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->unit_price, 0) }} UGX</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 