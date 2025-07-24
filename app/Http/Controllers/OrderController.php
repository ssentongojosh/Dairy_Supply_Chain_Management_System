<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\RawMaterialOrder;
use Carbon\Carbon;
use App\Models\Delivery;
use App\Models\Inventory;
use Illuminate\Http\Request;
// use Illuminate\Http\Request;
use App\Services\OrderWorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\OrderApproved;

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
            return redirect()->route('plant_manager.orders');
        }
        elseif ($role === 'supplier') {
            return redirect()->route('supplier.orders');
        }
        elseif ($role === 'retailer') {
            return redirect()->route('retailer.orders');
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

        // Always define orderStats for all roles
        $orderStats = [
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'completed_orders' => $orders->whereIn('status', ['delivered', 'received'])->count(),
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->whereIn('status', ['delivered', 'received'])->sum('total_amount'),
        ];

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
        // Ensure $incomingOrders and $outgoingOrders are sent for wholesaler dashboard
        if ($role === 'wholesaler') {
            $viewData['incomingOrders'] = $orders;
            $viewData['outgoingOrders'] = $orders;
        }
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

        $seller_id = $validated['seller_id'];
        $seller_role = $seller_id ? User::find($seller_id)->role->value : null;

Log::info("current user role: " . Auth::user()->role->value, [
            'seller_id' => $seller_id,
            'seller_role' => $seller_role,
            'items' => $validated['items'],
        ]);

        if ($seller_role === 'wholesaler' || $seller_role === 'plant_manager'){

        try {
          DB::beginTransaction();
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

            if (Auth::user()->role->value === 'retailer') {
                return redirect()->route('retailer.dashboard')->with('success', 'Order placed successfully🎉');
            }
             elseif (Auth::user()->role->value === 'wholesaler') {
    return redirect()->route('wholesaler.dashboard')->with('success', 'Order placed successfully🎉');
}
            return back()->with('success', 'Order placed successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to place order.');


        }
      }
      elseif ($seller_role === 'supplier' || $seller_role === 'farmer'){
        DB::beginTransaction();
        Log::info('Creating raw material order', [
            'seller_id' => $seller_id,
            'items' => $validated['items'],
        ]);

        try {
            // Calculate total amount
            $total = 0;
            foreach ($validated['items'] as $item) {
                $rawMaterial = RawMaterial::find($item['product_id']);
                $total += ($rawMaterial ? $rawMaterial->price : 0) * $item['quantity'];
            }

            Log::info('Total amount calculated for raw material order', [
                'total' => $total,
                'items' => $validated['items']
            ]);

            $rawMaterialOrder = RawMaterialOrder::create([
                'buyer_id' => Auth::id(),
                'seller_id' => $validated['seller_id'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'total_amount' => $total,
            ]);

            Log::info('Raw material order created', [
                'order_id' => $rawMaterialOrder->id,
                'buyer_id' => Auth::id(),
                'seller_id' => $validated['seller_id'],
                'total_amount' => $total
            ]);

            foreach ($validated['items'] as $item) {
                $rawMaterial = RawMaterial::find($item['product_id']);
                $rawMaterialOrder->items()->create([
                    'raw_material_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $rawMaterial ? $rawMaterial->price : 0,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Order placed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Raw material order creation failed: ' . $e->getMessage(), [
        'exception' => $e,
        'trace' => $e->getTraceAsString(),
        'buyer_id' => Auth::id(),
        'seller_id' => $validated['seller_id'],
        'total' => $total,
        'items' => $validated['items']
    ]);
            return back()->with('error', 'Failed to place order.');
        }


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

        // Handle plant managers viewing outgoing raw material orders
        if ($user->role->value === 'plant_manager') {
            $query = RawMaterialOrder::where('buyer_id', $user->id)
                ->where('status', '!=', 'cancelled');

            $status = request('status');
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            $orders = $query->with(['seller', 'items.rawMaterial'])
                ->orderByDesc('created_at')
                ->paginate(10);

            $view = 'plant_manager.orders';

            return view($view, [
                'orders' => $orders,
                'outgoingOrders' => $orders
            ]);
        }

        // Handle regular orders for other roles
        $query = Order::where('buyer_id', $user->id)
            ->where('status', '!=', 'cancelled');
        $status = request('status');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->with(['seller', 'items.product'])
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

    public function incomingOrders()
    {
        $user = Auth::user();

        // Handle suppliers viewing incoming raw material orders
        if ($user->role->value === 'supplier') {
            $orders = RawMaterialOrder::where('seller_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->with(['buyer', 'items.rawMaterial'])
                ->orderByDesc('created_at')
                ->paginate(10);

            $view = 'supplier.orders';

            return view($view, [
                'orders' => $orders,
                'incomingOrders' => $orders
            ]);
        }

        // Handle regular orders for other roles
        $orders = Order::where('seller_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->with(['seller', 'items.product'])
            ->orderByDesc('created_at')
            ->paginate(10);

        // Return appropriate view based on user role
        $view = match($user->role->value) {
            'wholesaler' => 'wholesaler.orders',
            'plant_manager' => 'plant_manager.orders',
            'farmer'=>'farmer.orders',
            default => 'orders.incoming',
        };

        return view($view, [
            'orders' => $orders,
            'incomingOrders' => $orders
        ]);
    }

    public function orderHistory()
    {
        $user = Auth::user();
        if ($user->role->value === 'farmer' || $user->role->value === 'supplier') {
            // For suppliers, get raw material orders where they are the seller
            $receivedOrders = \App\Models\RawMaterialOrder::where('seller_id', $user->id)
                ->with(['buyer', 'items.rawMaterial'])
                ->orderBy('created_at', 'desc')
                ->get();
            $placedOrders = $user->role->value === 'farmer'
                ? \App\Models\Order::where('buyer_id', $user->id)
                    ->with(['seller', 'items.product'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();
            return view('orders.history', compact('placedOrders', 'receivedOrders'));
        }elseif($user->role->value === 'retailer' || $user->role->value ==='wholesaler'){
        $orders = Order::where('seller_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        $view = match($user->role->value) {
            'retailer' => 'retailer.orders',
            'wholesaler' => 'wholesaler.order_history',
            default => 'orders.history',
        };

        if($user->role->value === 'plant_manager'){
          $orders = RawMaterialOrder::where('seller_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->paginate(10);
          $view = 'plant_manager.order_history';
        }

        return view($view, compact('orders'));
    }
  }
    /**
     * View order history (orders where user is the seller)
     */
    public function history()
    {
        $user = Auth::user();

        // Apply filters if provided
        $query = Order::where('seller_id', $user->id);
        $rawMaterialsQuery = RawMaterialOrder::where('seller_id', $user->id);

        if (request('status')) {
            $query->where('status', request('status'));
            $rawMaterialsQuery->where('status', request('status'));
        }

        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
            $rawMaterialsQuery->whereDate('created_at', '>=', request('date_from'));
        }

        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
            $rawMaterialsQuery->whereDate('created_at', '<=', request('date_to'));
        }

        if (request('search')) {
            $query->whereHas('buyer', function($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            });
            $rawMaterialsQuery->whereHas('buyer', function($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            });
        }

        $regularOrders = $query->with(['buyer', 'items.product'])
            ->orderByDesc('created_at')
            ->paginate(10);
        $rawMaterialOrders = $rawMaterialsQuery->with(['buyer', 'items.rawMaterial'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $orders = $regularOrders->concat($rawMaterialOrders)
        ->sortByDesc('created_at')
        ->paginate(10);

        // Calculate order statistics
        $stats = [
            'total_orders' => Order::where('seller_id', $user->id)->count() + RawMaterialOrder::where('seller_id', $user->id)->count(),
            'pending_orders' => Order::where('seller_id', $user->id)->where('status', 'pending')->count() + RawMaterialOrder::where('seller_id', $user->id)->where('status', 'pending')->count(),
            'completed_orders' => Order::where('seller_id', $user->id)->whereIn('status', ['delivered', 'received'])->count() + RawMaterialOrder::where('seller_id', $user->id)->whereIn('status', ['delivered', 'received'])->count(),
            'total_revenue' => Order::where('seller_id', $user->id)->whereIn('status', ['delivered', 'received'])->sum('total_amount') + RawMaterialOrder::where('seller_id', $user->id)->whereIn('status', ['delivered', 'received'])->sum('total_amount'),
        ];

        $view = match($user->role->value) {
            'retailer' => 'retailer.orders',
            'wholesaler' => 'wholesaler.order_history',
            'plant_manager' => 'plant_manager.order_history',
            default => 'orders.history',
        };

        return view($view, compact('orders', 'stats'));
    }

    public function getProductsForSeller($sellerId)
{
  $user = Auth::user();
  if ($user && $user->role && $user->role->value === 'plant_manager') {
    Log::info('User is a plant manager', ['user_id' => $user->id]);

    $products = DB::table('supplier_raw_material as srm')
    ->join('raw_materials as rm', 'srm.raw_material_id', '=', 'rm.id')
    ->where('srm.supplier_id', $sellerId)
    ->select('rm.id', 'rm.name', 'rm.quantity') // 👈 assuming 'quantity' exists in raw_materials
    ->get(); // returns an array of objects

    Log::info('Fetched products for seller', [
        'seller_id' => $sellerId,
        'product_count' => $products->count()
    ]);
return response()->json([
    'sellerId' => $sellerId,
    'products' => $products
]);}
else {
    $products = \App\Models\Product::where('supplier_id', $sellerId)->get();

    Log::info('Fetched products for seller', [
        'seller_id' => $sellerId,
        'product_count' => $products->count()
    ]);
        return response()->json($products);
  }

}




    /**
     * Show order details
     */
    public function showOrder(Request $request, $orderId)
    {
        $user = Auth::user();

        Log::info("showOrder called", [
            'orderId' => $orderId,
            'user_id' => $user->id,
            'user_role' => $user->role->value
        ]);

        // First try to find it as a regular order
        $order = Order::find($orderId);
        Log::info('current user role', [
            'user_role' => $user->role->value,
            'order_id' => $orderId
        ]);
        
        if ($order && ($user->role->value !== 'supplier' && $user->role->value !== 'farmer')) {
            Log::info("Found regular order", ['order_id' => $order->id]);
            
            // Check if user is authorized to view this order
            if ($order->seller_id !== $user->id && $order->buyer_id !== $user->id) {
                abort(403, 'Unauthorized access to this order.');
            }

            $order->load(['seller', 'buyer', 'items.product']);

        $view = match($user->role->value) {
            'retailer' => 'retailer.order-show',
            'wholesaler' => 'wholesaler.order-show',
            'plant_manager' => 'plant_manager.order-show',
            default => 'orders.show',
        };

            return view($view, compact('order'));
        } else {
            Log::info("Regular order not found, trying raw material order");
            
            // If not found as regular order, try to find it as a raw material order
            $rawMaterialOrder = RawMaterialOrder::find($orderId);
            
            if ($rawMaterialOrder) {
                Log::info("Found raw material order", [
                    'order_id' => $rawMaterialOrder->id,
                    'seller_id' => $rawMaterialOrder->seller_id,
                    'buyer_id' => $rawMaterialOrder->buyer_id
                ]);
                
                // Check if user is authorized to view this raw material order
                if ($rawMaterialOrder->seller_id !== $user->id && $rawMaterialOrder->buyer_id !== $user->id) {
                    abort(403, 'Unauthorized access to this order.');
                }

                $rawMaterialOrder->load(['seller', 'buyer', 'items.rawMaterial']);

                // Use the existing showRawMaterialOrder method for raw material orders
                return $this->showRawMaterialOrder($rawMaterialOrder);
            } else {
                Log::error("Neither regular nor raw material order found", ['orderId' => $orderId]);
                abort(404, 'Order not found.');
            }
        }
    }    /**
     * Show raw material order details
     */
    public function showRawMaterialOrder( $orderId)
    {
        $order = $orderId instanceof RawMaterialOrder ? $orderId : RawMaterialOrder::find($orderId);

        // Log the user accessing the raw material order
        Log::info("Accessing raw material order", [
            'order_id' => $order->id ?? null,
            'user_id' => Auth::id(),
            'timestamp' => now()
        ]);

        // Get the authenticated user
    {
        $user = Auth::user();

        // Check if order exists
        if (!$order) {
            Log::error("RawMaterialOrder is null in showRawMaterialOrder method");
            abort(404, 'Raw material order not found.');
        }

        Log::info("Show Raw Materials method hit", [
            'order' => $order
        ]);

        // Check if user is authorized to view this order
        // if ($order->seller_id !== $user->id && $order->buyer_id !== $user->id) {
        //     abort(403, 'Unauthorized access to this order.');
        // }
        

        $order->load(['seller', 'buyer', 'items.rawMaterial']);

        Log::info("Showing raw material order details", [
            'order_id' => $order->id,
            'seller_id' => $order->seller,
            'buyer_id' => $order->buyer,
        ]);

        $view = match($user->role->value) {
            'supplier' => 'supplier.order-show',
            'plant_manager' => 'plant_manager.order_show',
            default => 'orders.show',
        };

        return view($view, compact('order'));
    }
  }
    /**
     * Approve an order
     */
    public function approveOrder(Order $order)
    {
        $user = Auth::user();

        try {
            // Check if user is authorized to approve this order
            if ($order->seller_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the seller can approve this order.'
                ], 403);
            }

            if ($order->status !== 'pending' ) {
              if ($order->status !== 'pending_review'){
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending orders can be approved.'
                ], 400);
            }}

            // Check inventory availability for each order item
            $inventoryIssues = [];
            foreach ($order->items as $item) {
                $inventory = Inventory::where('user_id', $user->id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if (!$inventory || $inventory->quantity < $item->quantity) {
                    $availableQty = $inventory ? $inventory->quantity : 0;
                    $inventoryIssues[] = "Insufficient stock for {$item->product->name}. Available: {$availableQty}, Required: {$item->quantity}";
                }
            }

            if (!empty($inventoryIssues)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot approve order due to insufficient inventory: ' . implode('; ', $inventoryIssues)
                ], 400);
            }

            // Update order status and reserve inventory
            DB::beginTransaction();

            $order->update(['status' => 'approved']);

            // Reserve inventory quantities
            foreach ($order->items as $item) {
                $inventory = Inventory::where('user_id', $user->id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($inventory) {
                    $inventory->decrement('quantity', $item->quantity);
                }
            }

            DB::commit();
            OrderApproved::dispatch($order);

            // Log the approval
            Log::info("Order {$order->id} approved by user {$user->id}");

            // Trigger events or notifications here if needed
            // Event::dispatch(new OrderApproved($order));

            return response()->json([
                'success' => true,
                'message' => 'Order has been approved successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error approving order {$order->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while approving the order. Please try again.'
            ], 500);
        }
    }

    /**
     * Approve a raw material order
     */
    public function approveRawMaterialOrder(RawMaterialOrder $order)
    {
        $user = Auth::user();

        try {
            // Check if user is authorized to approve this order
            if ($order->seller_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the seller can approve this order.'
                ], 403);
            }

            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending orders can be approved.'
                ], 400);
            }

            // Update order status
            DB::beginTransaction();
            $order->update(['status' => 'approved']);
            DB::commit();

            // Log the approval
            Log::info("Raw material order {$order->id} approved by user {$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Raw material order has been approved successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error approving raw material order {$order->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while approving the order. Please try again.'
            ], 500);
        }
    }

    /**
     * Reject an order
     */
    public function rejectOrder(Order $order, Request $request)
    {
        $user = Auth::user();

        try {
            // Check if user is authorized to reject this order
            if ($order->seller_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the seller can reject this order.'
                ], 403);
            }

            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending orders can be rejected.'
                ], 400);
            }

            // Validate rejection reason if provided
            $reason = $request->input('reason');
            if (!$reason) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a reason for rejection.'
                ], 400);
            }

            // Update order status
            $order->update([
                'status' => 'rejected'
            ]);

            // Log the rejection
            Log::info("Order {$order->id} rejected by user {$user->id} with reason: {$reason}");

            return response()->json([
                'success' => true,
                'message' => 'Order has been rejected successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error("Error rejecting order {$order->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while rejecting the order. Please try again.'
            ], 500);
        }
    }

    /**
     * Mark an order as shipped
     */
    public function markShipped(Order $order)
    {
        $user = Auth::user();

        try {
            // Check if user is authorized to mark this order as shipped
            if ($order->seller_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the seller can mark this order as shipped.'
                ], 403);
            }

            if ($order->status !== 'approved' && $order->status !== 'processing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only approved or processing orders can be marked as shipped.'
                ], 400);
            }

            $order->update([
                'status' => 'shipped'
            ]);

            // Log the shipping
            Log::info("Order {$order->id} marked as shipped by user {$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Order has been marked as shipped successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error("Error marking order {$order->id} as shipped: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the order. Please try again.'
            ], 500);
        }
    }
}


