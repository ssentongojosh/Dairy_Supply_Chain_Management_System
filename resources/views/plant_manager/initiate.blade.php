@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container">
    <h2>Pay for Order #{{ $order->id }}</h2>

    <form action="{{ route('plantmanager.orders.payment.process', $order) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select name="payment_method" id="payment_method" class="form-select" required>
                <option value="">Choose method</option>
                <option value="mpesa">M-Pesa</option>
                <option value="card">Card</option>
                <option value="bank">Bank Transfer</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="transaction_id" class="form-label">Transaction ID (if available)</label>
            <input type="text" name="transaction_id" id="transaction_id" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Submit Payment</button>
        <a href="{{ route('plantmanager.orders') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
