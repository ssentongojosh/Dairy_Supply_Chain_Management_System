<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlantManagerOrderController extends Controller
{
    protected $workflowService;

    public function __construct(OrderWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }    /**
     * Display order dashboard for plant manager
     */
    public function index()
    {
        // Get production queue (approved orders waiting for production)
        $productionQueue = Order::where('seller_id', Auth::id())
                                ->with(['buyer', 'items.product'])
                                ->where('status', 'approved')
                                ->orderBy('created_at', 'asc')
                                ->get();

        // Get recent orders (last 10 orders)
        $recentOrders = Order::where('seller_id', Auth::id())
                            ->with(['buyer', 'items.product'])
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get();

        // Get active orders (orders requiring action)
        $activeOrders = Order::where('seller_id', Auth::id())
                            ->with(['buyer', 'items.product'])
                            ->whereIn('status', ['pending', 'approved', 'processing'])
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        // Order statistics
        $orderStats = [
            'total_orders' => Order::where('seller_id', Auth::id())->count(),
            'pending_orders' => Order::where('seller_id', Auth::id())
                                    ->where('status', 'pending')
                                    ->count(),
            'in_production' => Order::where('seller_id', Auth::id())
                                   ->where('status', 'processing')
                                   ->count(),
            'completed_orders' => Order::where('seller_id', Auth::id())
                                      ->whereIn('status', ['shipped', 'delivered'])
                                      ->count(),
            'shipped_orders' => Order::where('seller_id', Auth::id())
                                    ->where('status', 'shipped')
                                    ->count(),
        ];

        return view('plant_manager.order_dashboard', compact(
            'productionQueue', 
            'recentOrders', 
            'activeOrders', 
            'orderStats'
        ));
    }

    /**
     * Show order history for plant manager
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
        }        if ($request->filled('customer_type')) {
            $query->whereHas('buyer', function($q) use ($request) {
                $q->where('role', $request->customer_type);
            });
        }

        if ($request->filled('search')) {
            $query->whereHas('buyer', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        // Order statistics for the view
        $stats = [
            'total_orders' => Order::where('seller_id', Auth::id())->count(),
            'pending_orders' => Order::where('seller_id', Auth::id())
                                    ->where('status', 'pending')
                                    ->count(),
            'completed_orders' => Order::where('seller_id', Auth::id())
                                      ->whereIn('status', ['shipped', 'delivered'])
                                      ->count(),
            'total_revenue' => Order::where('seller_id', Auth::id())
                                   ->where('status', 'delivered')
                                   ->sum('total_amount'),
        ];

        return view('plant_manager.order_history', compact('orders', 'stats'));
    }

    /**
     * Show specific order details
     */
    public function showOrder(Order $order)
    {
        // Check if this order belongs to the current user
        if ($order->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized access to order');
        }

        $order->load(['buyer', 'items.product', 'payment']);

        return view('plant_manager.order_show', compact('order'));
    }    /**
     * Approve an order
     */
    public function approveOrder(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized action');
        }

        if ($order->status === 'pending') {
            $order->update(['status' => 'approved']);
            
            return redirect()->back()
                            ->with('success', 'Order approved successfully.');
        }

        return redirect()->back()
                        ->with('error', 'Order cannot be approved at this time.');
    }

    /**
     * Reject an order
     */
    public function rejectOrder(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized action');
        }

        if (in_array($order->status, ['pending', 'approved'])) {
            $order->update(['status' => 'rejected']);
            
            return redirect()->back()
                            ->with('success', 'Order rejected.');
        }

        return redirect()->back()
                        ->with('error', 'Order cannot be rejected at this time.');
    }

    /**
     * Mark order as shipped
     */
    public function markShipped(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized action');
        }

        if ($order->status === 'processing') {
            $order->update(['status' => 'shipped']);
            
            return redirect()->back()
                            ->with('success', 'Order marked as shipped.');
        }

        return redirect()->back()
                        ->with('error', 'Order cannot be shipped at this time.');
    }

    /**
     * Start production for an order
     */
    public function startProduction(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized action');
        }

        if ($order->status === 'approved') {
            $order->update(['status' => 'processing']);
            
            return redirect()->back()
                            ->with('success', 'Production started for order #' . $order->id);
        }

        return redirect()->back()
                        ->with('error', 'Order cannot be moved to production at this time.');
    }
}
