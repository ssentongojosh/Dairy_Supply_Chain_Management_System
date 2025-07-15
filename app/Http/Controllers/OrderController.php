<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\RawMaterial;
use Carbon\Carbon;
use App\Models\Delivery;
use App\Models\Inventory;
use Illuminate\Http\Request;
// use Illuminate\Http\Request;
use App\Services\OrderWorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected OrderWorkflowService $workflow;

    public function __construct(OrderWorkflowService $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * View all incoming orders for authenticated user based on role
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->role->value;

        // Redirect plant managers to their dedicated dashboard
        if ($role === 'wholesaler') {
            return redirect()->route('wholesaler.orders');
        }
        elseif ($role === 'plant_manager') {
            return redirect()->route('plant_manager.dashboard');
        }

        // Role configuration mapping
        $roleConfig = [
            'retailer' => [
                'dashboard' => 'retailer.dashboard',
                'order_filter' => ['buyer_id' => $user->id],
                'inventory_threshold' => 5,
            ],
            'wholesaler' => [
                'dashboard' => 'wholesaler.dashboard',
                'order_filter' => ['buyer_id' => $user->id],
                'inventory_threshold' => 10,
            ],
            'supplier' => [
                'dashboard' => 'supplier.dashboard',
                'order_filter' => ['seller_id' => $user->id],
                'inventory_threshold' => 10,
            ],
            'farmer' => [
                'dashboard' => 'farmer.dashboard',
                'order_filter' => ['seller_id' => $user->id],
                'inventory_threshold' => 10,
            ],
        ];

        if (!isset($roleConfig[$role])) {
            return view('dashboard.under_construction', ['message' => 'Dashboard for your role is under construction.']);
        }

        $config = $roleConfig[$role];

        // Orders
        $orders = Order::where($config['order_filter'])
            ->with(['seller', 'buyer', 'items.product'])
            ->get();

        $recentOrders = Order::where($config['order_filter'])
            ->with(['seller', 'buyer', 'items.product'])
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        $topProducts = DB::table('order_items')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->join('products', 'order_items.product_id', '=', 'products.id')
        ->where('orders.seller_id', $user->id)
        ->select(
            'products.id',
            'products.name',
            DB::raw('SUM(order_items.quantity) as total_sold'),
            DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_revenue')
        )
        ->groupBy('products.id', 'products.name')
        ->orderByDesc('total_sold')
        ->limit(5)
        ->get();

        

    // Example: Calculate monthly revenue for the last 6 months
    $monthlyRevenue = DB::table('order_items')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('orders.seller_id', $user->id)
        ->selectRaw('DATE_FORMAT(orders.created_at, "%Y-%m") as month, SUM(order_items.quantity * order_items.unit_price) as revenue')
        ->groupBy('month')
        ->orderBy('month')
        ->limit(6)
        ->get();

   $lowStockThreshold = 5;  

    $lowStockItems = Inventory::where('user_id', $user->id)
        ->where('quantity', '<=', $lowStockThreshold)
        ->get();

    //for counting total low stock
    $lowStockItems = Inventory::where('user_id', $user->id)
    ->where('quantity', '<=', $lowStockThreshold)
    ->get();

    //for the products
    $products = Product::all(); 
    //for raw materials 
    $rawMaterials = RawMaterial::all(); 
    //for low stock 
    $totalLowStock = $lowStockItems->count(); 
    //for deliveries
    $todayDeliveriesCount = Delivery::whereDate('created_at', Carbon::today())->count();

    return view("{$role}.dashboard", compact('orders', 'recentOrders', 'topProducts', 'monthlyRevenue', 'lowStockItems', 'products', 'rawMaterials', 'totalLowStock', 'todayDeliveriesCount'));
    
}


    /**
     * Store new order from buyer to seller
     */
    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'seller_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Calculate total amount
            $total = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $total += ($product ? $product->price : 0) * $item['quantity'];
            }

            $order = Order::create([
                'buyer_id' => Auth::id(),
                'seller_id' => $validated['seller_id'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'total_amount' => $total,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $product ? $product->price : 0,
                ]);
            }

            $this->workflow->processNewOrder($order);
            DB::commit();

            if (auth()->user()->role->value === 'retailer') {
                return redirect()->route('retailer.orders.history')->with('success', 'Order placed successfully!');
            }
            return back()->with('success', 'Order placed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to place order.');


        }
    }

    /**
     * Update order status by seller
     */
    public function updateStatus(Order $order, Request $request)
    {
        $user = Auth::user();

        if ($order->seller_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:approved,shipped,ready_for_delivery,delivered,received,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        if ($validated['status'] === 'received' && $user->role->value === 'retailer') {
            foreach ($order->items as $item) {
                $inventory = Inventory::firstOrNew([
                    'user_id' => $user->id,
                    'product_id' => $item->product_id,
                ]);

                $inventory->quantity += $item->quantity;
                $inventory->unit_cost = $item->unit_price;
                $inventory->selling_price = $item->unit_price * 1.2;
                $inventory->reorder_point = $inventory->reorder_point ?? 10;
                $inventory->save();
            }
        }

        return back()->with('success', 'Order status updated.');
    }

    /**
     * Show payment form
     */
    public function showPayment(Order $order)
    {
        $user = Auth::user();

        if ($order->buyer_id !== $user->id || !$order->requiresPayment()) {
            abort(403);
        }

        return view('payments.initiate', compact('order'));
    }

    /**
     * Process payment
     */
    public function processPayment(Order $order, Request $request)
    {
        $user = Auth::user();

        if ($order->buyer_id !== $user->id || !$order->requiresPayment()) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        $payment = $this->workflow->processPayment($order, [
            'method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
        ]);

        if ($payment) {
            return redirect()->route('retailer.orders.show', $order)
                ->with('success', 'Payment submitted! Awaiting verification.');
        }

        return redirect()->back()
            ->with('error', 'Payment processing failed. Please try again.');
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Order $order)
    {
        $user = Auth::user();

        if ($order->buyer_id !== $user->id || !in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', 'Cannot cancel this order.');
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Order cancelled.');
    }

    /**
     * View all orders placed by the user (as buyer)
     */
    public function outgoingOrders()
    {
        $user = Auth::user();
        $orders = Order::where('buyer_id', $user->id)
            ->where('status', '!=', 'cancelled') // Exclude cancelled orders
            ->with(['seller', 'items.product'])
            ->orderByDesc('created_at')
            ->paginate(10);

        // Return appropriate view based on user role
        $view = match($user->role->value) {
            'retailer' => 'retailer.orders',
            'wholesaler' => 'wholesaler.orders',
            default => 'orders.outgoing',
        };

        return view($view, [
            'orders' => $orders,
            'outgoingOrders' => $orders
        ]);
    }

    public function orderHistory()
    {
        $user = auth()->user();
        $orders = Order::where('seller_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        $view = match($user->role->value) {
            'retailer' => 'retailer.orders',
            'wholesaler' => 'wholesaler.order_history',
            'plant_manager' => 'plant_manager.order_history',
            default => 'orders.history',
        };
        

        return view($view, compact('orders'));
    }

    public function showOrder(Order $order)
    {
        $user = Auth::user();
        
        // Check if user has permission to view this order
        if ($order->seller_id !== $user->id && $order->buyer_id !== $user->id) {
            abort(403, 'You do not have permission to view this order.');
        }
        
        // Load the order with relationships
        $order->load(['buyer', 'seller', 'items.product']);
        
        $view = match($user->role->value) {
            'retailer' => 'retailer.order-show',
            'wholesaler' => 'wholesaler.order-show',
            'plant_manager' => 'plant_manager.order_show',
            'supplier' => 'supplier.order-show',
            'farmer' => 'farmer.order-show',
            default => 'orders.show',
        };
        
        return view($view, compact('order'));
    }

    public function getProductsForSeller($sellerId)
    {
        $products = \App\Models\Product::where('supplier_id', $sellerId)->get();
        return response()->json($products);
    }
}
