<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function initiatePayment(Order $order)
    {
        // Authorization - only buyer can pay for order
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        // Check if order is approved for payment
        if ($order->status !== 'approved') {
            return back()->with('error', 'Order must be approved before payment');
        }

        // Check if already paid
        if ($order->payment_status === 'paid') {
            return back()->with('error', 'Order already paid');
        }

        return view('payments.initiate', compact('order'));
    }

    public function processPayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'method' => 'required|in:mpesa,bank,cash',
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Create payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'status' => 'pending',
        ]);

        // Process payment based on method
        switch ($validated['method']) {
            case 'mpesa':
                return $this->processMpesaPayment($payment);
            case 'bank':
                return $this->processBankPayment($payment);
            case 'cash':
                return $this->processCashPayment($payment);
        }
    }

    protected function processMpesaPayment(Payment $payment)
    {
        // Implement M-Pesa API integration here
        // This is a placeholder for actual implementation
        
        $payment->update([
            'status' => 'completed',
            'transaction_id' => 'MPESA' . now()->timestamp,
            'paid_at' => now(),
        ]);
        
        $payment->order->update([
            'payment_status' => 'paid'
        ]);
        
        return redirect()->route('orders.show', $payment->order)
            ->with('success', 'M-Pesa payment processed successfully');
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'seller_id' => 'required|exists:users,id',
        'payment_method' => 'required|in:mpesa,bank,credit',
        'mpesa_number' => 'required_if:payment_method,mpesa',
        'bank_reference' => 'required_if:payment_method,bank',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'notes' => 'nullable|string',
    ]);

    // Calculate total amount
    $total = 0;
    foreach ($validated['items'] as $item) {
        $product = Product::find($item['product_id']);
        $total += $product->price * $item['quantity'];
    }
    $total *= 1.16; // Add 16% tax

    // Create the order
    $order = Order::create([
        'buyer_id' => auth()->id(),
        'seller_id' => $validated['seller_id'],
        'total_amount' => $total,
        'status' => 'pending',
        'payment_status' => $validated['payment_method'] === 'credit' ? 'pending' : 'unpaid',
        'notes' => $validated['notes'] ?? null,
    ]);

    // Add order items
    foreach ($validated['items'] as $item) {
        $order->items()->create([
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
        ]);
    }

    // Process payment if not credit
    if ($validated['payment_method'] !== 'credit') {
        $payment = $order->payments()->create([
            'method' => $validated['payment_method'],
            'amount' => $total,
            'status' => 'pending',
            'details' => $validated['payment_method'] === 'mpesa' 
                ? ['phone' => $validated['mpesa_number']]
                : ($validated['payment_method'] === 'bank' 
                    ? ['reference' => $validated['bank_reference']]
                    : null),
        ]);

        // Process payment based on method
        if ($validated['payment_method'] === 'mpesa') {
            // Initiate M-Pesa payment
            $response = $this->initiateMpesaPayment($payment);
            // Handle response
        }
    }

    return redirect()->route('factory.orders.show', $order)
        ->with('success', 'Order created successfully!');
}

// Payment method-specific verification
protected function verifyMpesaPayment(string $transactionId): bool
{
    // TODO: Replace with actual M-Pesa API call
    // For testing, assume valid if starts with "MP" and has 10 chars
    return Str::startsWith($transactionId, 'MP') && strlen($transactionId) === 10;
}

protected function verifyBankPayment(string $reference): bool
{
    // TODO: Replace with bank API validation
    return strlen($reference) >= 8;
}

/**
 * Show verification form (for all roles)
 */
public function showVerificationForm(Order $order)
{
    // Check if user can verify (retailer/wholesaler/factory/supplier)
    if (!$this->canVerifyPayment($order)) {
        abort(403, 'Unauthorized action');
    }

    $payment = $order->latestPayment;
    return view('payments.verify', compact('order', 'payment'));
}

/**
 * Unified verification processing
 */
public function verifyPayment(Request $request, Order $order)
{
    $request->validate(['transaction_id' => 'required|string|min:5']);

    if (!$this->canVerifyPayment($order)) {
        abort(403);
    }

    $payment = $order->latestPayment;

    if ($this->verifyPaymentForRole($payment, $request->transaction_id)) {
        return redirect()
            ->route($this->getRoleDashboardRoute(), $order)
            ->with('success', 'Payment verified!');
    }

    return back()->with('error', 'Verification failed');
}

// --- Helper Methods (Add these as protected) ---

protected function canVerifyPayment(Order $order): bool
{
    $user = auth()->user();
    return match($user->role) {
        'retailer'   => $order->buyer_id === $user->id,
        'wholesaler' => $order->seller_id === $user->id && $order->buyer->role === 'retailer',
        'factory'    => $order->seller_id === $user->id && $order->buyer->role === 'wholesaler',
        'supplier'   => $order->seller_id === $user->id && $order->buyer->role === 'factory',
        default      => false
    };
}

protected function verifyPaymentForRole(Payment $payment, string $transactionId): bool
{
    $isValid = match($payment->method) {
        'mpesa' => $this->verifyMpesaPayment($transactionId),
        'bank'  => $this->verifyBankPayment($transactionId),
        'cash'  => true,
        default => false
    };

    if ($isValid) {
        $payment->update([
            'status' => 'verified',
            'transaction_id' => $transactionId,
            'verified_at' => now(),
            'verified_by' => auth()->id()
        ]);
        $payment->order->update(['payment_status' => 'paid']);
    }

    return $isValid;
}

protected function getRoleDashboardRoute(): string
{
    return match(auth()->user()->role) {
        'retailer'   => 'retailer.dashboard',
        'wholesaler' => 'wholesaler.orders.show',
        'factory'    => 'factory.orders.show',
        'supplier'   => 'supplier.orders.show',
        default      => 'home'
    };
}
}