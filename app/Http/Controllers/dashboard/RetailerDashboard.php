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

class RetailerDashboard extends Controller
{
    /**
     * Display the retailer dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Get current date and month boundaries
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Dashboard metrics from database
        $pendingOrdersCount = Order::where('buyer_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $newOrdersToday = Order::where('buyer_id', $user->id)
            ->whereDate('created_at', $today)
            ->count();

        // Get retailer's inventory data
        $inventory = Inventory::where('user_id', $user->id)->get();

        $totalUniqueProducts = $inventory->count();

        $lowStockProductsCount = $inventory->where('quantity', '>', 0)
            ->where('quantity', '<=', 10) // Assuming reorder point is 10
            ->count();

        $outOfStockProductsCount = $inventory->where('quantity', 0)->count();

        // Calculate total sales this month (sum of received orders)
        $totalSalesThisMonth = Order::where('buyer_id', $user->id)
            ->where('status', 'received')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with('items')
            ->get()
            ->sum(function ($order) {
                return $order->items->sum(function ($item) {
                    return $item->quantity * $item->price;
                });
            });

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
            $weeklySales = Order::where('buyer_id', $user->id)
                ->where('status', 'received')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->with('items')
                ->get()
                ->sum(function ($order) {
                    return $order->items->sum(function ($item) {
                        return $item->quantity * $item->price;
                    });
                });
            $salesChartData[] = $weeklySales;

            // Weekly orders count
            $weeklyOrders = Order::where('buyer_id', $user->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
            $ordersChartData[] = $weeklyOrders;
        }

        // Get recent orders with seller information
        $recentOrders = Order::where('buyer_id', $user->id)
            ->with(['seller', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                $totalAmount = $order->items->sum(function ($item) {
                    return $item->quantity * $item->price;
                });

                return (object) [
                    'id' => $order->id,
                    'customer_name' => $order->seller ? $order->seller->name : 'Unknown Seller',
                    'total_amount' => $totalAmount,
                    'status' => $order->status,
                    'created_at' => $order->created_at
                ];
            });

        // Get products that need reordering (low stock items)
        $productsToReorder = Inventory::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', 10) // Assuming reorder point is 10
            ->with('product')
            ->get()
            ->map(function ($inventory) {
                return (object) [
                    'name' => $inventory->product ? $inventory->product->name : 'Unknown Product',
                    'sku' => $inventory->product ? $inventory->product->sku : 'N/A',
                    'current_stock' => $inventory->quantity,
                    'reorder_point' => 10, // You can make this dynamic later
                    'image_url' => 'assets/img/icons/misc/product-placeholder.png'
                ];
            });

        // Get key suppliers (sellers from whom the retailer has ordered most)
        $keySuppliers = Order::where('buyer_id', $user->id)
            ->with('seller')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select('seller_id', DB::raw('count(*) as order_count'))
            ->groupBy('seller_id')
            ->orderBy('order_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($orderGroup) {
                $seller = User::find($orderGroup->seller_id);
                return (object) [
                    'name' => $seller ? $seller->name : 'Unknown Supplier',
                    'contact_person' => $seller ? $seller->name : 'Unknown',
                    'logo_url' => 'assets/img/icons/misc/company-placeholder.png',
                    'total_orders_this_month' => $orderGroup->order_count
                ];
            });

        return view('dashboard.retailer', [
            'user' => $user,
            'pendingOrdersCount' => $pendingOrdersCount,
            'lowStockProductsCount' => $lowStockProductsCount,
            'totalSalesThisMonth' => $totalSalesThisMonth,
            'newOrdersToday' => $newOrdersToday,
            'totalUniqueProducts' => $totalUniqueProducts,
            'outOfStockProductsCount' => $outOfStockProductsCount,
            'recentOrders' => $recentOrders,
            'productsToReorder' => $productsToReorder,
            'keySuppliers' => $keySuppliers,
            'salesChartLabels' => $salesChartLabels,
            'salesChartData' => $salesChartData,
            'ordersChartLabels' => $ordersChartLabels,
            'ordersChartData' => $ordersChartData,
        ]);
    }
}
