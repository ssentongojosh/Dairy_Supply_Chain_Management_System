<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
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
        if ($role === 'plant_manager') {
            return redirect()->route('plant-manager.dashboard');
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
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $orderStats = [
            'total_orders' => Order::where($config['order_filter'])->count(),
            'pending_orders' => Order::where($config['order_filter'])->where('status', 'pending')->count(),
            'completed_orders' => Order::where($config['order_filter'])->where('status', 'completed')->count(),
            'total_revenue' => Order::where($config['order_filter'])->sum('total_amount'),
        ];

        // Inventory
        $inventories = Inventory::where('user_id', $user->id)->get();
        $totalProducts = $inventories->count();
        $lowStockThreshold = $config['inventory_threshold'];
        $inventoryStats = [
            'total_products' => $inventories->count(),
            'low_stock_items' => $inventories->where('quantity', '<=', $lowStockThreshold)->count(),
            'out_of_stock' => $inventories->where('quantity', '<=', 0)->count(),
            'total_value' => $inventories->sum(function($inv) { return $inv->quantity * ($inv->selling_price ?? 0); }),
        ];
        $lowStockItems = $inventories->where('quantity', '<=', $lowStockThreshold);

        $topProducts = collect();
        $monthlyRevenue = collect();
        $pendingOrdersCount = $orderStats['pending_orders'];
        $newOrdersToday = 0;
        $totalRevenueThisMonth = 0;
        $salesGrowth = 0; // Default value for all roles
        $outOfStockProductsCount = 0;
        $lowStockProductsCount = 0;
        $keyBuyers = collect();
        if ($role === 'supplier') {
            $today = now()->startOfDay();
            $newOrdersToday = \App\Models\Order::where('seller_id', $user->id)
                ->whereDate('created_at', $today)
                ->count();
            $totalRevenueThisMonth = Order::where('seller_id', $user->id)
                ->whereIn('status', ['shipped', 'delivered', 'received'])
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->with('items')
                ->get()
                ->reduce(function ($carry, $order) {
                    return $carry + $order->items->sum(function ($item) {
                        return $item->quantity * $item->unit_price;
                    });
                }, 0);
            // Calculate last month's revenue
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastMonthRevenue = Order::where('seller_id', $user->id)
                ->whereIn('status', ['shipped', 'delivered', 'received'])
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->with('items')
                ->get()
                ->reduce(function ($carry, $order) {
                    return $carry + $order->items->sum(function ($item) {
                        return $item->quantity * $item->unit_price;
                    });
                }, 0);
            if ($lastMonthRevenue > 0) {
                $salesGrowth = (($totalRevenueThisMonth - $lastMonthRevenue) / $lastMonthRevenue) * 100;
            } else {
                $salesGrowth = 0;
            }
            // Calculate out of stock and low stock products count
            $outOfStockProductsCount = $inventories->where('quantity', '<=', 0)->count();
            $lowStockProductsCount = $inventories->where('quantity', '>', 0)->where('quantity', '<=', $lowStockThreshold)->count();
            // Calculate top buyers (keyBuyers)
            $keyBuyers = Order::select('buyer_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total_amount) as total_spent'))
                ->where('seller_id', $user->id)
                ->whereNotNull('buyer_id')
                ->groupBy('buyer_id')
                ->orderByDesc('total_spent')
                ->with('buyer')
                ->limit(5)
                ->get();
            // Add productsToRestock for supplier dashboard
            $productsToRestock = Inventory::where('user_id', $user->id)
                ->where(function ($query) {
                    $query->where('quantity', 0)
                          ->orWhereRaw('quantity <= low_stock_threshold');
                })
                ->with('product')
                ->orderBy('quantity', 'asc')
                ->take(5)
                ->get();
            // Add chart data for the last 4 weeks
            $salesChartData = [];
            $ordersChartData = [];
            $salesChartLabels = [];
            $ordersChartLabels = [];
            for ($i = 3; $i >= 0; $i--) {
                $weekStart = now()->subWeeks($i)->startOfWeek();
                $weekEnd = now()->subWeeks($i)->endOfWeek();
                $weekLabel = 'Week ' . (4 - $i);
                $salesChartLabels[] = $weekLabel;
                $ordersChartLabels[] = $weekLabel;
                // Weekly sales
                $weeklySales = Order::where('seller_id', $user->id)
                    ->whereIn('status', ['shipped', 'delivered', 'received'])
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->with('items')
                    ->get()
                    ->reduce(function ($carry, $order) {
                        return $carry + $order->items->sum(function ($item) {
                            return $item->quantity * $item->unit_price;
                        });
                    }, 0);
                $salesChartData[] = $weeklySales;
                // Weekly orders count
                $weeklyOrders = Order::where('seller_id', $user->id)
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->count();
                $ordersChartData[] = $weeklyOrders;
            }
        }

        $viewData = compact(
            'orders',
            'recentOrders',
            'orderStats',
            'inventoryStats',
            'lowStockItems',
            'topProducts',
            'monthlyRevenue',
            'user',
            'pendingOrdersCount',
            'newOrdersToday',
            'totalProducts',
            'totalRevenueThisMonth',
            'salesGrowth',
            'outOfStockProductsCount',
            'lowStockProductsCount',
            'keyBuyers'
        );
        if (isset($productsToRestock)) {
            $viewData['productsToRestock'] = $productsToRestock;
        }
        if ($role === 'supplier') {
            $viewData['salesChartData'] = $salesChartData;
            $viewData['ordersChartData'] = $ordersChartData;
            $viewData['salesChartLabels'] = $salesChartLabels;
            $viewData['ordersChartLabels'] = $ordersChartLabels;
        }
        return view($config['dashboard'], $viewData);
    }


    /**
     * Show order creation form
     */
    public function createOrder()
    {
        $user = Auth::user();
        
        // Get allowed sellers based on user role
        $allowedSellers = [];
        
        switch ($user->role->value) {
            case 'retailer':
                // Retailers can order from wholesalers
                $allowedSellers = User::where('role', 'wholesaler')->get();
                break;
            case 'wholesaler':
                // Wholesalers can order from plant managers
                $allowedSellers = User::where('role', 'plant_manager')->get();
                break;
            case 'plant_manager':
                // Plant managers can order from suppliers and farmers
                $allowedSellers = User::whereIn('role', ['supplier', 'farmer'])->get();
                break;
            default:
                abort(403, 'Order creation not allowed for this role.');
        }
        
        // Get available products from allowed sellers
        $products = Product::whereHas('inventory', function($query) use ($allowedSellers) {
            $query->whereIn('user_id', $allowedSellers->pluck('id'));
        })->with(['inventory' => function($query) use ($allowedSellers) {
            $query->whereIn('user_id', $allowedSellers->pluck('id'));
        }])->get();
        
        return view('orders.create', compact('allowedSellers', 'products'));
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
            $order = Order::create([
                'buyer_id' => Auth::id(),
                'seller_id' => $validated['seller_id'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
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
            ->with(['seller', 'items.product'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('wholesaler.orders', compact('orders'));
    }

    public function orderHistory()
    {
        $user = auth()->user();
        $orders = Order::where('seller_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $view = match($user->role->value) {
            'retailer' => 'retailer.orders',
            'wholesaler' => 'wholesaler.order_history',
            'plant_manager' => 'plant_manager.orders_history',
            default => 'orders.history',
        };
        

        return view($view, compact('orders'));
    }

    public function showOrder($orderId)
    {
        $order = \App\Models\Order::with(['seller', 'buyer', 'items.product'])->findOrFail($orderId);
        return view('orders.show', compact('order'));
    }
}
