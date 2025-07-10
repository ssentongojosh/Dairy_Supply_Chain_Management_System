<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class SupplierDashboard extends Controller
{
    /**
     * Display the supplier dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Get current date and month boundaries
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Dashboard metrics for suppliers
        $pendingOrdersCount = Order::where('seller_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $newOrdersToday = Order::where('seller_id', $user->id)
            ->whereDate('created_at', $today)
            ->count();

        // Get incoming orders from factories/buyers
        $incomingOrders = Order::where('seller_id', $user->id)
            ->with(['buyer', 'items.product'])
            ->whereIn('status', ['pending', 'approved', 'shipped'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get supplier's inventory data
        $inventory = Inventory::where('user_id', $user->id)->get();

        $totalProducts = $inventory->count();

        $lowStockProductsCount = $inventory->filter(function ($item) {
            return $item->quantity > 0 && $item->quantity <= ($item->low_stock_threshold ?? 50);
        })->count();

        $outOfStockProductsCount = $inventory->where('quantity', 0)->count();

        // Calculate total sales this month
        $totalSalesThisMonth = Order::where('seller_id', $user->id)
            ->where('status', 'received')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with('items')
            ->get()
            ->reduce(function ($carry, $order) {
                return $carry + $order->items->sum(function ($item) {
                    return $item->quantity * $item->unit_price;
                });
            }, 0);

        // Calculate total revenue this month
        $totalRevenueThisMonth = Order::where('seller_id', $user->id)
            ->whereIn('status', ['shipped', 'delivered', 'received'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with('items')
            ->get()
            ->reduce(function ($carry, $order) {
                return $carry + $order->items->sum(function ($item) {
                    return $item->quantity * $item->unit_price;
                });
            }, 0);

        // Chart data for the last 4 weeks
        $salesChartData = [];
        $ordersChartData = [];
        $salesChartLabels = [];
        $ordersChartLabels = [];

        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();

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

        // Get recent orders
        $recentOrders = Order::where('seller_id', $user->id)
            ->with(['buyer', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get products that need restocking
        $productsToRestock = Inventory::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('quantity', 0)
                      ->orWhereRaw('quantity <= low_stock_threshold');
            })
            ->with('product')
            ->orderBy('quantity', 'asc')
            ->take(5)
            ->get();

        // Get key buyers (customers who order most frequently)
        $keyBuyers = Order::where('seller_id', $user->id)
            ->select('buyer_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total_amount) as total_spent'))
            ->groupBy('buyer_id')
            ->with('buyer')
            ->orderBy('order_count', 'desc')
            ->take(5)
            ->get();

        // Get monthly comparison data
        $lastMonth = Carbon::now()->subMonth();
        $lastMonthStart = $lastMonth->startOfMonth();
        $lastMonthEnd = $lastMonth->endOfMonth();

        $lastMonthSales = Order::where('seller_id', $user->id)
            ->whereIn('status', ['shipped', 'delivered', 'received'])
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->with('items')
            ->get()
            ->reduce(function ($carry, $order) {
                return $carry + $order->items->sum(function ($item) {
                return $item->quantity * $item->unit_price;
                });
            }, 0);

        $salesGrowth = $lastMonthSales > 0
            ? (($totalSalesThisMonth - $lastMonthSales) / $lastMonthSales) * 100
            : 0;

        // Top selling products this month
        $topSellingProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.seller_id', $user->id)
            ->whereIn('orders.status', ['shipped', 'delivered', 'received'])
            ->whereBetween('orders.created_at', [$startOfMonth, $endOfMonth])
            ->select(
                'products.name',
                'products.id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        return view('supplier.dashboard', [
            'user' => $user,
            'incomingOrders' => $incomingOrders, // Add this line
            'pendingOrdersCount' => $pendingOrdersCount,
            'newOrdersToday' => $newOrdersToday,
            'totalProducts' => $totalProducts,
            'lowStockProductsCount' => $lowStockProductsCount,
            'outOfStockProductsCount' => $outOfStockProductsCount,
            'totalSalesThisMonth' => $totalSalesThisMonth,
            'totalRevenueThisMonth' => $totalRevenueThisMonth,
            'salesChartData' => $salesChartData,
            'ordersChartData' => $ordersChartData,
            'salesChartLabels' => $salesChartLabels,
            'ordersChartLabels' => $ordersChartLabels,
            'recentOrders' => $recentOrders,
            'productsToRestock' => $productsToRestock,
            'keyBuyers' => $keyBuyers,
            'salesGrowth' => $salesGrowth,
            'topSellingProducts' => $topSellingProducts,
        ]);
    }

    /**
     * Get dashboard statistics for AJAX requests
     */
    public function getStats()
    {
        $user = Auth::user();

        return response()->json([
            'pending_orders' => Order::where('seller_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'low_stock_items' => Inventory::where('user_id', $user->id)
                ->whereRaw('quantity <= low_stock_threshold')
                ->count(),
            'total_products' => Inventory::where('user_id', $user->id)->count(),
        ]);
    }
}
