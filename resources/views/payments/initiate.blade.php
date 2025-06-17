@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Process Payment for Order #{{ $order->id }}</h2>
    
    <div class="card">
        <div class="card-body">
            <p><strong>Total Amount:</strong> UGX {{ number_format($order->total_amount, 2) }}</p>
            
            <form action="{{ route('payments.process', $order) }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="method">Payment Method</label>
                    <select name="method" id="method" class="form-control" required>
                        <option value="">Select method</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="cash">Cash on Delivery</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount</label>
                    <input type="number" name="amount" id="amount" 
                           class="form-control" 
                           value="{{ $order->total_amount }}" 
                           step="0.01" min="0.01" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Submit Payment</button>
            </form>
        </div>
    </div>
</div>
@endsection