@extends('layouts.contentNavbarLayout')
@section('title', 'Retailer Orders')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold py-3 mb-4">Retailer Dashboard</h4>

    @if($orders->isEmpty())
        <div class="alert alert-info">No incoming orders at the moment.</div>
    @else
        <div class="card">
            <h5 class="card-header">Incoming Orders</h5>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Buyer</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Items</th>
                            <th>Placed On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->buyer->name }}</td>
                            <td><span class="badge bg-label-primary">{{ ucfirst($order->status) }}</span></td>
                            <td><span class="badge bg-label-success">{{ ucfirst($order->payment_status) }}</span></td>
                            <td>
                                <ul class="list-unstyled mb-0">
                                    @foreach($order->items as $item)
                                        <li>{{ $item->product->name }} x {{ $item->quantity }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
