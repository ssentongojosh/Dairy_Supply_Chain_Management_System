<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierOrderController extends Controller
{
    protected $workflowService;

    public function __construct(OrderWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display order dashboard for supplier
     */
    public function index()
    {
        // Get recent orders
        $recentOrders = Order::where('seller_id', Auth::id())
                           ->with(['buyer', 'items.product'])
                           ->orderBy('created_at', 'desc')
                           ->limit(10)
                           ->get();

        // Get active orders (requiring action)
        $activeOrders = Order::where('seller_id', Auth::id())
                           ->with(['buyer', 'items.product'])
                           ->whereIn('status', ['pending', 'approved'])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);

        // Order statistics
        $orderStats = [
            'pending_orders' => Order::where('seller_id', Auth::id())
                                   ->where('status', 'pending')
                                   ->count(),
            'processing_orders' => Order::where('seller_id', Auth::id())
                                       ->whereIn('status', ['approved', 'processing'])
                                       ->count(),
            'completed_orders' => Order::where('seller_id', Auth::id())
                                     ->whereIn('status', ['shipped', 'delivered'])
                                     ->count(),
            'total_revenue' => Order::where('seller_id', Auth::id())
                                  ->whereIn('status', ['shipped', 'delivered'])
                                  ->sum('total_amount'),
        ];

        return view('supplier.order_dashboard', compact(
            'recentOrders',
            'activeOrders',
            'orderStats'
        ));
    }

    /**
     * Show order history for supplier
     */
    public function orderHistory(Request $request)
    {
        $query = Order::where('seller_id', Auth::id())
                     ->with(['buyer', 'items.product']);

        // Apply filters
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

        // Statistics for the view
        $stats = [
            'total_orders' => Order::where('seller_id', Auth::id())->count(),
            'pending_orders' => Order::where('seller_id', Auth::id())
                                   ->where('status', 'pending')
                                   ->count(),
            'completed_orders' => Order::where('seller_id', Auth::id())
                                     ->whereIn('status', ['shipped', 'delivered'])
                                     ->count(),
            'total_revenue' => Order::where('seller_id', Auth::id())
                                  ->whereIn('status', ['shipped', 'delivered'])
                                  ->sum('total_amount'),
        ];

        return view('supplier.order_history', compact('orders', 'stats'));
    }

    /**
     * Show specific order details
     */
    public function showOrder(Order $order)
    {
        // Ensure the order belongs to this supplier
        if ($order->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['buyer', 'items.product', 'payment']);

        return view('supplier.order_show', compact('order'));
    }

    /**
     * Approve an order
     */
    public function approveOrder(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Order cannot be approved at this stage'], 400);
        }

        try {
            $order->update([
                'status' => 'approved',
                'approved_at' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Order approved successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to approve order'], 500);
        }
    }

    /**
     * Reject an order
     */
    public function rejectOrder(Order $order, Request $request)
    {
        if ($order->seller_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Order cannot be rejected at this stage'], 400);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $order->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason,
                'rejected_at' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Order rejected successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to reject order'], 500);
        }
    }

    /**
     * Mark order as shipped
     */
    public function markShipped(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!in_array($order->status, ['approved', 'processing'])) {
            return response()->json(['success' => false, 'message' => 'Order cannot be shipped at this stage'], 400);
        }

        try {
            $order->update([
                'status' => 'shipped',
                'shipped_at' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Order marked as shipped successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to ship order'], 500);
        }
    }

    /**
     * Legacy methods for backward compatibility
     */
    public function approve(Order $order)
    {
        return $this->approveOrder($order);
    }

    public function ship(Order $order)
    {
        return $this->markShipped($order);
    }
}

