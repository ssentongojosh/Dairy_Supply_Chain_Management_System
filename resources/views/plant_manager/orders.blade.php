@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container">
    <h2> Orders to farms and suppliers</h2>

    @forelse($orders as $order)
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Order #:</strong> {{ $order->id }}</p>
                <p><strong>To:</strong> {{ $order->seller->name }} </p>
                <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                <p><strong>Total:</strong> UGX {{ number_format($order->total_amount, 0) }}</p>

                <!-- @if($order->status === 'pending')
                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="d-inline"> -->
                        <!-- @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                        <button class="btn btn-success btn-sm">Approve</button>
                    </form>
                @endif -->

                <a href="{{ route('payments.verify.form', $order) }}" class="btn btn-outline-primary btn-sm">
                    Verify Payment
                </a>
            </div>
        </div>
    @empty
        <p>No incoming orders from wholesaler.</p>
    @endforelse
</div>
@endsection
