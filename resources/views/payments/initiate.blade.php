@extends('layouts.contentNavbarLayout')

@section('title', 'Payment - Order #' . $order->id)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Process Payment for Order #{{ $order->id }}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <h6 class="mb-2">Payment Summary</h6>
                        <p class="mb-1"><strong>Order to:</strong> {{ $order->seller->name }}</p>
                        <p class="mb-1"><strong>Total Amount:</strong> ${{ number_format($order->total_amount, 2) }}</p>
                        @if($order->payment_due_date)
                            <p class="mb-0"><strong>Due Date:</strong> {{ $order->payment_due_date->format('M d, Y') }}</p>
                        @endif
                    </div>

                    <form action="{{ route('payments.process', $order) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="">Select Payment Method</option>
                                <option value="mpesa">M-Pesa</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="transaction_id" class="form-label">Transaction ID <small class="text-muted">(Optional)</small></label>
                            <input type="text" name="transaction_id" id="transaction_id"
                                   class="form-control"
                                   placeholder="Enter transaction reference number">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-secure-payment-line me-1"></i>Submit Payment
                            </button>
                            <a href="{{ route('retailer.orders.show', $order) }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i>Back to Order
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
