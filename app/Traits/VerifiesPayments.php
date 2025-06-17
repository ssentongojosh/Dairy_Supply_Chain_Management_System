<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

trait VerifiesPayments
{
    /**
     * Verify a payment and update order status
     */
    protected function verifyPayment(Payment $payment, string $transactionId, ?string $notes = null): bool
    {
        try {
            $payment->update([
                'status' => 'verified',
                'transaction_id' => $transactionId,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
                'verification_notes' => $notes,
            ]);

            $payment->order->update(['payment_status' => 'paid']);
            
            Log::info("Payment {$payment->id} verified by user " . auth()->id());
            return true;

        } catch (\Exception $e) {
            Log::error("Payment verification failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Role-specific verification permissions
     */
    protected function canVerifyPayment(Order $order): bool
    {
        $user = auth()->user();
        
        return match($user->role) {
            'supplier'   => $order->seller_id === $user->id && $order->buyer->role === 'factory',
            'factory'    => $order->seller_id === $user->id && $order->buyer->role === 'wholesaler',
            'wholesaler' => $order->seller_id === $user->id && $order->buyer->role === 'retailer',
            default      => false,
        };
    }

    /**
     * Payment method-specific verification logic
     */
    protected function verifyByMethod(Payment $payment, string $transactionId): bool
    {
        return match($payment->method) {
            'mpesa' => $this->verifyMpesaPayment($transactionId),
            'bank'  => $this->verifyBankPayment($transactionId),
            'cash'  => true, // Auto-verify cash payments
            default => false,
        };
    }

    protected function verifyMpesaPayment(string $code): bool
    {
        // TODO: Replace with actual M-Pesa API call
        return preg_match('/^[A-Z0-9]{10}$/', $code); // Sample validation
    }

    protected function verifyBankPayment(string $reference): bool
    {
        // TODO: Replace with bank API validation
        return strlen($reference) >= 8;
    }
}