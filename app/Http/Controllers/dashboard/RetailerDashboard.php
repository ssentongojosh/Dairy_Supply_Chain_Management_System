<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RetailerDashboard extends Controller
{
    /**
     * Display the retailer dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Dashboard metrics placeholders (replace with real queries)
        $pendingOrdersCount         = 0;
        $lowStockProductsCount      = 0;
        $totalSalesThisMonth        = 0.00;
        $newOrdersToday             = 0;
        $totalUniqueProducts        = 0;
        $outOfStockProductsCount    = 0;

        // Sample chart data (replace with dynamic data)
        $salesChartLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        $salesChartData   = [12000, 15000, 13000, 17000];

        $ordersChartLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        $ordersChartData   = [50, 60, 55, 80];

        // Mock recent orders data
        $recentOrders = collect([
            (object) ['id' => 1001, 'customer_name' => 'John Doe', 'total_amount' => 250000, 'status' => 'pending', 'created_at' => Carbon::now()->subDays(1)],
            (object) ['id' => 1002, 'customer_name' => 'Jane Smith', 'total_amount' => 475000, 'status' => 'shipped', 'created_at' => Carbon::now()->subDays(2)],
            (object) ['id' => 1003, 'customer_name' => 'Acme Corp', 'total_amount' => 1250000, 'status' => 'delivered', 'created_at' => Carbon::now()->subDays(3)],
        ]);

        // Mock products to reorder
        $productsToReorder = collect([
            (object)[ 'name' => 'Dairy Milk 1L', 'sku' => 'MILK001', 'current_stock' => 8,  'reorder_point' => 20, 'image_url' => 'assets/img/icons/misc/product-placeholder.png' ],
            (object)[ 'name' => 'Yogurt Pack',   'sku' => 'YOGT123','current_stock' => 3,  'reorder_point' => 10, 'image_url' => 'assets/img/icons/misc/product-placeholder.png' ],
            (object)[ 'name' => 'Cheddar Cheese','sku' => 'CHED789','current_stock' => 0,  'reorder_point' => 5,  'image_url' => 'assets/img/icons/misc/product-placeholder.png' ],
        ]);

        // Mock key suppliers data
        $keySuppliers = collect([
            (object)[ 'name' => 'Sunrise Farms', 'contact_person' => 'Alice Johnson', 'logo_url' => 'assets/img/icons/misc/company-placeholder.png', 'total_orders_this_month' => 12 ],
            (object)[ 'name' => 'Green Valley', 'contact_person' => 'Bob Williams', 'logo_url' => 'assets/img/icons/misc/company-placeholder.png', 'total_orders_this_month' => 8  ],
            (object)[ 'name' => 'Happy Dairy',   'contact_person' => 'Carol Smith',   'logo_url' => 'assets/img/icons/misc/company-placeholder.png', 'total_orders_this_month' => 15 ],
        ]);

        return view('dashboard.retailer', [
            'user'                       => $user,
            'pendingOrdersCount'         => $pendingOrdersCount,
            'lowStockProductsCount'      => $lowStockProductsCount,
            'totalSalesThisMonth'        => $totalSalesThisMonth,
            'newOrdersToday'             => $newOrdersToday,
            'totalUniqueProducts'        => $totalUniqueProducts,
            'outOfStockProductsCount'    => $outOfStockProductsCount,
            'recentOrders'               => $recentOrders,
            'productsToReorder'          => $productsToReorder,
            'keySuppliers'               => $keySuppliers,
            'salesChartLabels'           => $salesChartLabels,
            'salesChartData'             => $salesChartData,
            'ordersChartLabels'          => $ordersChartLabels,
            'ordersChartData'            => $ordersChartData,
        ]);
    }
}
