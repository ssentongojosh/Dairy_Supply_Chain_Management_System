@extends('layouts.contentNavbarLayout')

@section('title', 'Payment - Order #' . $order->id)

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Order Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-1">Order Summary</h5>
                <small class="text-muted">Order #{{ $order->id }} to {{ $order->seller->name }}</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-2">Order Items</h6>
                        @foreach($order->items as $item)
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ $item->product->name }} ({{ $item->quantity }}x)</span>
                                <span>${{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6 class="mb-2">Payment Due</h6>
                            <p class="mb-1">Total Amount: <strong>${{ number_format($order->total_amount, 2) }}</strong></p>
                            @if($order->payment_due_date)
                                <p class="mb-0">Due Date: {{ $order->payment_due_date->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('retailer.orders.payment.process', $order) }}" method="POST" id="paymentForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="transaction_id" class="form-label">Transaction ID <small class="text-muted">(Optional)</small></label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id"
                                       placeholder="Enter transaction reference number">
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Specific Instructions -->
                    <div id="payment-instructions" class="mb-3" style="display: none;">
                        <div class="alert alert-info">
                            <div id="instructions-content"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri-secure-payment-line me-1"></i>Submit Payment
                            </button>
                            <a href="{{ route('retailer.orders.show', $order) }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i>Back to Order
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('payment_method').addEventListener('change', function() {
    const method = this.value;
    const instructionsDiv = document.getElementById('payment-instructions');
    const contentDiv = document.getElementById('instructions-content');

    if (method) {
        let instructions = '';

        switch(method) {
            case 'mpesa':
                instructions = `
                    <h6><i class="ri-smartphone-line me-2"></i>M-Pesa Payment Instructions</h6>
                    <p class="mb-2">1. Go to M-Pesa menu on your phone</p>
                    <p class="mb-2">2. Select "Lipa na M-Pesa" → "Buy Goods and Services"</p>
                    <p class="mb-2">3. Enter Till Number: <strong>123456</strong></p>
                    <p class="mb-2">4. Enter Amount: <strong>KES {{ number_format($order->total_amount, 2) }}</strong></p>
                    <p class="mb-0">5. Enter the M-Pesa confirmation code in the Transaction ID field above</p>
                `;
                break;
            case 'bank_transfer':
                instructions = `
                    <h6><i class="ri-bank-line me-2"></i>Bank Transfer Instructions</h6>
                    <p class="mb-2">Bank: ABC Bank</p>
                    <p class="mb-2">Account Name: {{ $order->seller->name }}</p>
                    <p class="mb-2">Account Number: 1234567890</p>
                    <p class="mb-2">Amount: <strong>KES {{ number_format($order->total_amount, 2) }}</strong></p>
                    <p class="mb-0">Reference: Order #{{ $order->id }}</p>
                `;
                break;
            case 'cheque':
                instructions = `
                    <h6><i class="ri-file-text-line me-2"></i>Cheque Payment Instructions</h6>
                    <p class="mb-2">Make cheque payable to: <strong>{{ $order->seller->name }}</strong></p>
                    <p class="mb-2">Amount: <strong>KES {{ number_format($order->total_amount, 2) }}</strong></p>
                    <p class="mb-0">Write "Order #{{ $order->id }}" on the back of the cheque</p>
                `;
                break;
            case 'cash':
                instructions = `
                    <h6><i class="ri-money-dollar-circle-line me-2"></i>Cash Payment Instructions</h6>
                    <p class="mb-2">Amount: <strong>KES {{ number_format($order->total_amount, 2) }}</strong></p>
                    <p class="mb-0">Please arrange with {{ $order->seller->name }} for cash collection</p>
                `;
                break;
        }

        contentDiv.innerHTML = instructions;
        instructionsDiv.style.display = 'block';
    } else {
        instructionsDiv.style.display = 'none';
    }
});

// Form validation
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const method = document.getElementById('payment_method').value;

    if (!method) {
        e.preventDefault();
        alert('Please select a payment method');
        return;
    }

    // Show confirmation
    if (!confirm(`Are you sure you want to submit payment of KES {{ number_format($order->total_amount, 2) }} via ${method}?`)) {
        e.preventDefault();
    }
});
</script>
@endsection
