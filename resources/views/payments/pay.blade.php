@extends('layouts.contentNavbarLayout')

@section('content')
<h2 class="text-xl font-bold mb-4">Pay for Order #{{ $order->id }}</h2>

<p><strong>Total Amount:</strong> {{ $order->total_price }} UGX</p>

<form action="{{ route('payments.process', $order->id) }}" method="POST">
    @csrf

    <label>Currency</label>
    <select name="currency" required>
        <option value="UGX">UGX</option>
        <option value="USD">USD</option>
    </select>

    <label class="block mt-4">Payment Method</label>
    <select name="method" required>
        <option value="bank">Bank Account</option>
        <option value="mpesa">M-Pesa</option>
        <option value="cash">Cash</option>
    </select>

    <label class="block mt-4">Transaction Reference (optional for cash)</label>
    <input type="text" name="transaction_reference" placeholder="Txn ID or Bank Ref">

    <button type="submit" class="btn btn-primary mt-4">Submit Payment</button>
</form>
@endsection