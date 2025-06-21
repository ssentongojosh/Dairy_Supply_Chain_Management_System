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

class FarmerDashboard extends Controller
{
    /**
     * Display the farmer dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Get current date and month boundaries
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Dashboard metrics for farmers
        $pendingOrdersCount = Order::where('seller_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $newOrdersToday = Order::where('seller_id', $user->id)
            ->whereDate('created_at', $today)
            ->count();

        // Get farmer's inventory data (dairy products/raw milk)
        $inventory = Inventory::where('user_id', $user->id)->get();

        $totalMilkProducts = $inventory->count();

        $lowStockProductsCount = $inventory->where('quantity', '>', 0)
            ->where('quantity', '<=', 100) // Milk measured in liters
            ->count();

        $outOfStockProductsCount = $inventory->where('quantity', 0)->count();        // Calculate total sales this month (orders from factories/wholesalers)
        $totalSalesThisMonth = Order::where('seller_id', $user->id)
            ->where('status', 'received')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with('items')
            ->get()
            ->reduce(function ($carry, $order) {
                return $carry + $order->items->reduce(function ($itemCarry, $item) {
                    return $itemCarry + ($item->quantity * $item->unit_price);
                }, 0);
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
            $ordersChartLabels[] = $weekLabel;            // Weekly sales
            $weeklySales = Order::where('seller_id', $user->id)
                ->where('status', 'received')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->with('items')
                ->get()
                ->reduce(function ($carry, $order) {
                    return $carry + $order->items->reduce(function ($itemCarry, $item) {
                        return $itemCarry + ($item->quantity * $item->unit_price);
                    }, 0);
                }, 0);
            $salesChartData[] = $weeklySales;

            // Weekly orders count
            $weeklyOrders = Order::where('seller_id', $user->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
            $ordersChartData[] = $weeklyOrders;
        }

        // Get recent orders from factories/wholesalers
        $recentOrders = Order::where('seller_id', $user->id)
            ->with(['buyer', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get products that need attention (low milk production)
        $productsToRestock = Inventory::where('user_id', $user->id)
            ->whereColumn('quantity', '<=', 'reorder_point')
            ->with('product')
            ->orderBy('quantity', 'asc')
            ->take(5)
            ->get();

        // Get key buyers (factories/wholesalers who order most frequently)
        $keyBuyers = Order::where('seller_id', $user->id)
            ->select('buyer_id', DB::raw('count(*) as order_count'), DB::raw('sum(total_amount) as total_spent'))
            ->groupBy('buyer_id')
            ->with('buyer')
            ->orderBy('order_count', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.farmer', [
            'user' => $user,
            'pendingOrdersCount' => $pendingOrdersCount,
            'lowStockProductsCount' => $lowStockProductsCount,
            'totalSalesThisMonth' => $totalSalesThisMonth,
            'newOrdersToday' => $newOrdersToday,
            'totalMilkProducts' => $totalMilkProducts,
            'outOfStockProductsCount' => $outOfStockProductsCount,
            'salesChartLabels' => $salesChartLabels,
            'salesChartData' => $salesChartData,
            'ordersChartLabels' => $ordersChartLabels,
            'ordersChartData' => $ordersChartData,
            'recentOrders' => $recentOrders,
            'productsToRestock' => $productsToRestock,
            'keyBuyers' => $keyBuyers,
        ]);
    }
}
