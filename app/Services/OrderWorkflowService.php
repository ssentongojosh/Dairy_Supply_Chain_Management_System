<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderWorkflowService
{
    /**
     * Process a new order and determine if it can be auto-approved
     */
    public function processNewOrder(Order $order)
    {
        try {
            DB::beginTransaction();

            // Calculate total amount from order items
            $totalAmount = $this->calculateOrderTotal($order);
            $order->update(['total_amount' => $totalAmount]);

            // Check if order can be auto-approved based on stock
            if ($this->canAutoApprove($order)) {
                $this->autoApproveOrder($order);
            } else {
                $this->setOrderPendingReview($order);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order processing failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'error' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Check if order can be auto-approved based on wholesaler's inventory
     */
    protected function canAutoApprove(Order $order)
    {
        foreach ($order->items as $item) {
            $inventory = Inventory::where('user_id', $order->seller_id)
                ->where('product_id', $item->product_id)
                ->first();

            if (!$inventory || $inventory->quantity < $item->quantity) {
                return false;
            }
        }
        return true;
    }

    /**
     * Auto-approve the order and reserve inventory
     */
    protected function autoApproveOrder(Order $order)
    {
        // Update order status to approved
        $order->update([
            'status' => 'approved',
            'approved_at' => now(),
            'payment_due_date' => now()->addDays(3), // 3 days to pay
        ]);

        // Reserve inventory (reduce quantities)
        foreach ($order->items as $item) {
            $inventory = Inventory::where('user_id', $order->seller_id)
                ->where('product_id', $item->product_id)
                ->first();

            if ($inventory) {
                $inventory->decrement('quantity', $item->quantity);
            }
        }

        // Log approval
        Log::info('Order auto-approved', [
            'order_id' => $order->id,
            'buyer' => $order->buyer->name,
            'seller' => $order->seller->name,
            'total_amount' => $order->total_amount
        ]);
    }

    /**
     * Set order as pending review (manual approval needed)
     */
    protected function setOrderPendingReview(Order $order)
    {
        $order->update([
            'status' => 'pending_review',
        ]);

        Log::info('Order requires manual review', [
            'order_id' => $order->id,
            'reason' => 'Insufficient inventory'
        ]);
    }

    /**
     * Calculate total amount for the order
     */
    protected function calculateOrderTotal(Order $order)
    {
        $total = 0;

        foreach ($order->items as $item) {
            // Get the selling price from wholesaler's inventory
            $inventory = Inventory::where('user_id', $order->seller_id)
                ->where('product_id', $item->product_id)
                ->first();

            $unitPrice = $inventory ? $inventory->selling_price : $item->product->price;

            // Update the order item with the unit price
            $item->update(['unit_price' => $unitPrice]);

            $total += $item->quantity * $unitPrice;
        }

        return $total;
    }

    /**
     * Process payment for approved order
     */
    public function processPayment(Order $order, array $paymentData)
    {
        try {
            DB::beginTransaction();

            // Create payment record
            $payment = $order->payments()->create([
                'amount' => $order->total_amount,
                'method' => $paymentData['method'],
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'status' => 'pending',
            ]);

            // Update order payment status
            $order->update([
                'payment_status' => 'pending_verification'
            ]);

            DB::commit();
            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify payment and update order status
     */
    public function verifyPayment(Order $order, $verificationCode)
    {
        $payment = $order->latestPayment();

        if (!$payment) {
            return false;
        }

        // Simple verification logic (you can make this more sophisticated)
        if ($payment->status === 'pending' && !empty($verificationCode)) {
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'verification_code' => $verificationCode
            ]);

            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing'
            ]);

            return true;
        }

        return false;
    }

    /**
     * Mark order as shipped
     */
    public function shipOrder(Order $order)
    {
        if ($order->status === 'processing' && $order->payment_status === 'paid') {
            $order->update(['status' => 'shipped']);
            return true;
        }
        return false;
    }
}
