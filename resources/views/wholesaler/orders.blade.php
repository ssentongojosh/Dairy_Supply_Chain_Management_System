@extends('layouts.contentNavbarLayout')

@section('title', 'My Orders to retailer')

@section('content')
<div class="container">
    <h2 class="mb-4">Orders to Wholesaler</h2>

    @forelse($orders as $order)
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Order #:</strong> {{ $order->id }}</p>
                <p><strong>To:</strong> {{ $order->seller->name ?? 'Unknown Wholesaler' }} (Wholesaler)</p>
                <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                <p><strong>Total:</strong> UGX {{ number_format($order->total_amount, 0) }}</p>
                <p><strong>Created:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>

                {{-- Payment action if needed --}}
                @if($order->payment_status === 'pending' || $order->payment_status === 'pending_verification')
                    <a href="{{ route('payments.verify.form', $order) }}" class="btn btn-outline-primary btn-sm">
                        Verify Payment
                    </a>
                @endif

                {{-- Cancel order if still pending --}}
                @if($order->status === 'pending')
                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="d-inline ms-2">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="cancelled">
                        <button class="btn btn-outline-danger btn-sm">Cancel Order</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <p>You haven't placed any orders to the wholesaler yet.</p>
    @endforelse
</div>
@endsection
