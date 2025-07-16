<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
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
        // Only the buyer of an approved order can pay
        if ($order->buyer_id !== auth()->id() || $order->status !== 'approved') {
            abort(403);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        // Create a pending payment
        $payment = Payment::create([
            'order_id'       => $order->id,
            'amount'         => $order->total_amount,
            'method'         => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'status'         => 'pending',
        ]);

        // Mark order as awaiting verification
        $order->update(['payment_status' => 'pending_verification']);

        return redirect()->route('retailer.orders.show', $order)
                         ->with('success', 'Payment submitted! Awaiting verification.');
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
    // Only the buyer of the order can verify/pay
    return $order->buyer_id === $user->id;
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