@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            Order #{{ $order->id }} to {{ $order->seller->name }}
            <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'shipped' ? 'primary' : 'success') }}">
                {{ ucfirst($order->status) }}
            </span>
        </div>
        
        <div class="card-body">
            <h5>Order Details</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Ksh {{ number_format($item->product->price, 2) }}</td>
                            <td>Ksh {{ number_format($item->product->price * $item->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Subtotal</th>
                        <th>Ksh {{ number_format($order->items->sum(function($item) { return $item->product->price * $item->quantity; }), 2) }}</th>
                    </tr>
                </tfoot>
            </table>
            
            <div class="mt-3">
                <h5>Order Timeline</h5>
                <ul class="list-group">
                    <li class="list-group-item">
                        <strong>Order Placed:</strong> {{ $order->created_at->format('M d, Y H:i') }}
                    </li>
                    @if($order->status === 'shipped' || $order->status === 'received')
                        <li class="list-group-item">
                            <strong>Shipped:</strong> {{ $order->updated_at->format('M d, Y H:i') }}
                        </li>
                    @endif
                    @if($order->status === 'received')
                        <li class="list-group-item">
                            <strong>Received:</strong> {{ $order->updated_at->format('M d, Y H:i') }}
                        </li>
                    @endif
                </ul>
            </div>
            
            @if($order->status === 'shipped')
                <div class="mt-3">
                    <form action="{{ route('retailer.orders.receive', $order) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            Mark as Received
                        </button>
                    </form>
                </div>
            @endif
            @if($order->payment_status === 'unpaid' && $order->latestPayment?->status === 'pending')
    <div class="alert alert-warning mt-3">
        <p class="mb-2">Awaiting payment verification.</p>
        <a href="{{ route('retailer.payments.verify.form', $order) }}" 
           class="btn btn-sm btn-primary">
            <i class="fas fa-check-circle me-1"></i> Verify Payment
        </a>
    </div>
@endif
        </div>
    </div>
</div>
@endsection