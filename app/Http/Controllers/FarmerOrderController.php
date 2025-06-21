<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FarmerOrderController extends Controller
{
    // View incoming orders from wholesalers and factories
    public function index()
    {
        $incomingOrders = Order::where('seller_id', Auth::id())
                              ->with(['buyer', 'items.product', 'latestPayment'])
                              ->orderBy('created_at', 'desc')
                              ->take(10)
                              ->get();

        return view('farmer.order_dashboard', compact('incomingOrders'));
    }

    // Manual approval for orders that couldn't be auto-approved
    public function approveOrder(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403);
        }

        // Allow approval for orders that are pending or pending_review
        if (!in_array($order->status, ['pending', 'pending_review'])) {
            return redirect()->back()
                            ->with('error', 'Order cannot be approved at this stage.');
        }

        $order->update([
            'status' => 'approved',
            'approved_at' => now(),
            'payment_due_date' => now()->addDays(3), // 3 days to pay
        ]);

        return redirect()->back()
                        ->with('success', 'Order approved successfully!');
    }

    // Reject order
    public function rejectOrder(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($order->status, ['pending', 'pending_review'])) {
            return redirect()->back()
                            ->with('error', 'Order cannot be rejected at this stage.');
        }

        $order->update(['status' => 'rejected']);

        return redirect()->back()
                        ->with('success', 'Order rejected.');
    }

    // Mark order as shipped
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

    // Show order history page for farmer
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

        return view('farmer.order_history', compact('orders'));
    }

    // Show order details for farmer
    public function showOrder(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'buyer', 'latestPayment']);
        return view('farmer.order_show', compact('order'));
    }
}
