@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4>Verify Payment for Order #{{ $order->id }}</h4>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <strong>Amount Paid:</strong> Ksh {{ number_format($payment->amount, 2) }}<br>
                <strong>Method:</strong> {{ Str::upper($payment->method) }}
            </div>

            <form method="POST" action="{{ route('retailer.payments.verify', $order) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">
                        {{ $payment->method === 'mpesa' ? 'M-Pesa Transaction ID' : 'Bank Reference' }}
                    </label>
                    <input
                        type="text"
                        name="transaction_id"
                        class="form-control"
                        placeholder="{{ $payment->method === 'mpesa' ? 'e.g., MP123456789' : 'Bank transaction reference' }}"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-check-circle me-2"></i> Confirm Verification
                </button>
            </form>
        </div>
    </div>
</div>
@endsection