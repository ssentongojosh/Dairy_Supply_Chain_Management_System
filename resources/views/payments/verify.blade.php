@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            Verify Payment for Order #{{ $order->id }}
        </div>
        <div class="card-body">
            @if ($payment)
                <p><strong>Amount:</strong> Ksh {{ number_format($payment->amount, 2) }}</p>
                <p><strong>Method:</strong> {{ strtoupper($payment->method) }}</p>
                <form method="POST" action="{{ route('payments.verify', $order) }}">
                    @csrf
                    <div class="mb-3">
                        <label>Transaction ID</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="MPESA12345 or Bank Reference" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Confirm Verification</button>
                </form>
            @else
                <div class="alert alert-warning">
                    No payment has been initiated for this order yet.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection