<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WholesalerOrderController extends Controller
{
    // View incoming orders from retailers (used for dashboard)
    public function index()
    {
        // For the wholesaler dashboard page, we'll show recent orders
        $incomingOrders = Order::where('seller_id', Auth::id())
                              ->with(['buyer', 'items.product', 'latestPayment'])
                              ->orderBy('created_at', 'desc')
                              ->take(10)
                              ->get();

        return view('wholesaler.order_dashboard', compact('incomingOrders'));
    }

    // Manual approval for orders that couldn't be auto-approved
    public function approveOrder(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'pending_review') {
            return redirect()->back()
                            ->with('error', 'This order cannot be approved.');
        }

        $workflowService = new OrderWorkflowService();

        // Force approve the order (manual approval)
        $order->update([
            'status' => 'approved',
            'approved_at' => now(),
            'payment_due_date' => now()->addDays(3),
        ]);

        return redirect()->back()
                        ->with('success', 'Order approved successfully! Retailer can now make payment.');
    }

    // Reject order
    public function rejectOrder(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($order->status, ['pending', 'pending_review'])) {
            return redirect()->back()
                            ->with('error', 'This order cannot be rejected.');
        }

        $order->update(['status' => 'rejected']);

        return redirect()->back()
                        ->with('success', 'Order rejected successfully.');
    }

    // Mark order as shipped (after payment is verified)
    public function markShipped(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'processing' || $order->payment_status !== 'paid') {
            return redirect()->back()
                            ->with('error', 'Order cannot be shipped until payment is verified.');
        }

        $workflowService = new OrderWorkflowService();
        if ($workflowService->shipOrder($order)) {
            return redirect()->back()
                            ->with('success', 'Order marked as shipped.');
        }

        return redirect()->back()
                        ->with('error', 'Failed to ship order.');
    }

    // Show order history page for wholesaler
    public function orderHistory(Request $request)
    {
        $query = Order::where('seller_id', Auth::id())
                     ->with(['buyer', 'items.product']);

        // Apply filters if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->whereHas('buyer', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate total amount for each order
        $orders->transform(function ($order) {
            $order->total_amount = $order->items->sum(function ($item) {
                return $item->quantity * $item->unit_price;
            });
            return $order;
        });

        return view('wholesaler.order_history', compact('orders'));
    }

    // Show order details for wholesaler
    public function showOrder(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'buyer', 'latestPayment']);
        return view('wholesaler.order_show', compact('order'));
    }
}

