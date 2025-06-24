<!-- @extends('layouts.contentNavbarLayout')

@section('title', 'Your Orders')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold py-3 mb-4">Your Orders</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="alert alert-info">You have not placed any orders yet.</div>
    @else
        <div class="card">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Seller</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Items</th>
                            <th>Placed On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach($orders as $order)
                        <tr>
                            <td><strong>#{{ $order->id }}</strong></td>
                            <td>{{ $order->seller->name }}</td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-label-warning',
                                        'processing' => 'bg-label-info',
                                        'approved' => 'bg-label-primary',
                                        'shipped' => 'bg-label-secondary',
                                        'delivered' => 'bg-label-success',
                                        'cancelled' => 'bg-label-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$order->status] ?? 'bg-label-secondary' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $order->payment_status === 'paid' ? 'bg-label-success' : 'bg-label-warning' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                            <td>
                                <ul class="list-unstyled mb-0">
                                    @foreach($order->items as $item)
                                        <li>{{ $item->product->name }} x {{ $item->quantity }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                            <td class="d-flex gap-2">
                                @if(in_array($order->status, ['pending', 'processing']))
                                    <form action="{{ route('retailer.orders.cancel', $order) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Cancel this order?')">
                                            Cancel
                                        </button>
                                    </form>
                                @endif

                                @if($order->payment_status === 'unpaid' && $order->status === 'approved')
                                    <a href="{{ route('payments.initiate', $order) }}" class="btn btn-sm btn-primary">
                                        Pay Now
                                    </a>
                                @endif

                                <a href="{{ route('retailer.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection -->


@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container">
    <h2>wholesaler Orders (As retailer)</h2>

    @forelse($orders as $order)
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Order #:</strong> {{ $order->id }}</p>
                <p><strong>From:</strong> {{ $order->buyer->name }} (wholesaler)</p>
                <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                <p><strong>Total:</strong> UGX {{ number_format($order->total_amount, 0) }}</p>

                @if($order->status === 'pending')
                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="approved">
                        <button class="btn btn-success btn-sm">Approve</button>
                    </form>
                @endif

                <a href="{{ route('payments.verify.form', $order) }}" class="btn btn-outline-primary btn-sm">
                    Verify Payment
                </a>
            </div>
        </div>
    @empty
        <p>No incoming orders from factories.</p>
    @endforelse
</div>
@endsection
