<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Report; // Import your Report model
use Illuminate\Support\Facades\Storage; // For file operations
use Symfony\Component\HttpFoundation\StreamedResponse; // For file downloads
use App\Services\ReportGeneratorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Inventory;
use App\Models\User;

class ReportHistoryController extends Controller
{
    protected $reportGeneratorService;

    public function __construct(ReportGeneratorService $reportGeneratorService)
    {
        $this->reportGeneratorService = $reportGeneratorService;
    }

    //
    public function index()
    {
        $user = Auth::user();

        // Fetch reports for the authenticated user, ordered by generation date (newest first)
        // You might add pagination here later if there are many reports: ->paginate(10)
        $reports = Report::where('user_id', $user->id)
                         ->orderBy('generated_at', 'desc')
                         ->get(); // or ->paginate(15); for pagination

        return view('reports.reporthistory', compact('reports'));
    }

    /**
     * Download a specific report from history.
     *
     * @param  \App\Models\Report  $report
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function download(Report $report)
    {
        // 1. Authorization: Ensure the logged-in user owns this report
        if (Auth::id() !== $report->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Check if the file actually exists on storage
        if (!Storage::disk('local')->exists($report->file_path)) {
            // Log this error, as the database record exists but the file is gone
            // Log::error("Report file not found for DB ID: {$report->id}, Path: {$report->file_path}");
            return back()->with('error', 'The report file could not be found. It may have been deleted or never generated successfully.');
        }

        // 3. Increment download count (optional)
        $report->increment('download_count');

        // 4. Serve the file for download
        // We use Storage::download() which automatically sets headers and streams the file.
        // Importantly, NO deleteFileAfterSend(true) here, as these are permanent.
        return Storage::download($report->file_path, $report->file_name);
    }

    /**
     * Preview a report in the browser
     */
    public function preview(Report $report)
    {
        // Authorization check
        if (Auth::id() !== $report->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if file exists
        if (!Storage::disk('local')->exists($report->file_path)) {
            return back()->with('error', 'The report file could not be found.');
        }

        // Generate real data based on the report's date range and types
        $reportTypes = $report->report_types; // This should be an array from the database
        $startDate = Carbon::parse($report->report_start_date ?? now()->subDays(30));
        $endDate = Carbon::parse($report->report_end_date ?? now());
        $userId = $report->user_id;

        $reportData = [];

        // Generate data based on what report types were originally requested
        foreach ($reportTypes as $type) {
            switch ($type) {
                case 'sales':
                    $reportData['sales'] = $this->getSalesData($startDate, $endDate, $userId);
                    break;
                case 'inventory':
                    $reportData['inventory'] = $this->getInventoryData($userId);
                    break;
                case 'suppliers':
                    $reportData['suppliers'] = $this->getSupplierData($userId);
                    break;
                case 'customers':
                    $reportData['customers'] = $this->getCustomerData($startDate, $endDate, $userId);
                    break;
            }
        }

        $reportPeriodName = $report->report_name ?? "Report Preview";

        // Determine which view to use based on format
        if ($report->format === 'pdf') {
            return view('pdfs.user_report', compact('reportData', 'reportPeriodName'));
        } else {
            return view('exports.user_reports_export', compact('reportData', 'reportPeriodName'));
        }
    }

    /**
     * Get sales data for the report preview
     */
    private function getSalesData(Carbon $startDate, Carbon $endDate, int $userId): array
    {
        // Use the same logic as ReportGeneratorService
        try {
            $query = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
                ->leftJoin('users as sellers', 'orders.seller_id', '=', 'sellers.id')
                ->leftJoin('users as buyers', 'orders.buyer_id', '=', 'buyers.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('orders.status', '!=', 'cancelled')
                ->where(function($q) use ($userId) {
                    $q->where('orders.buyer_id', $userId)
                      ->orWhere('orders.seller_id', $userId);
                });

            $salesData = $query->select(
                    'orders.created_at as date',
                    'products.name as product',
                    'order_items.quantity',
                    'order_items.unit_price as price',
                    DB::raw('(order_items.quantity * order_items.unit_price) as total'),
                    'sellers.name as seller_name',
                    'buyers.name as buyer_name',
                    'orders.status'
                )
                ->orderBy('orders.created_at', 'desc')
                ->limit(10) // Limit for preview
                ->get()
                ->map(function ($sale) {
                    return [
                        'date' => Carbon::parse($sale->date)->format('Y-m-d'),
                        'product' => $sale->product ?? 'Unknown Product',
                        'quantity' => $sale->quantity,
                        'price' => number_format($sale->price, 2),
                        'total' => number_format($sale->total, 2),
                        'seller' => $sale->seller_name ?? 'Unknown Seller',
                        'buyer' => $sale->buyer_name ?? 'Unknown Buyer',
                        'status' => ucfirst($sale->status)
                    ];
                })
                ->toArray();

            if (empty($salesData)) {
                return [[
                    'date' => 'N/A',
                    'product' => 'No sales data found for the selected period',
                    'quantity' => 0,
                    'price' => '0.00',
                    'total' => '0.00',
                    'seller' => 'N/A',
                    'buyer' => 'N/A',
                    'status' => 'N/A'
                ]];
            }

            return $salesData;
        } catch (\Exception $e) {
            Log::error('Error retrieving sales data for preview', ['error' => $e->getMessage()]);
            return [[
                'date' => 'Error',
                'product' => 'Failed to load sales data',
                'quantity' => 0,
                'price' => '0.00',
                'total' => '0.00',
                'seller' => 'Error',
                'buyer' => 'Error',
                'status' => 'Error'
            ]];
        }
    }

    /**
     * Get inventory data for the report preview
     */
    private function getInventoryData(int $userId): array
    {
        try {
            $inventoryData = Inventory::with(['product', 'user'])
                ->where('user_id', $userId)
                ->limit(10) // Limit for preview
                ->get()
                ->map(function ($inventory) {
                    return [
                        'name' => $inventory->product->name ?? 'Unknown Product',
                        'product_name' => $inventory->product->name ?? 'Unknown Product',
                        'stock' => $inventory->quantity,
                        'reorder_point' => $inventory->reorder_point,
                        'unit_cost' => number_format((float)$inventory->unit_cost, 2),
                        'selling_price' => number_format((float)$inventory->selling_price, 2),
                        'unit' => $inventory->product->unit ?? 'pcs',
                        'location' => $inventory->location ?? 'N/A',
                        'status' => $inventory->auto_status ?? 'Unknown',
                        'last_restocked' => $inventory->last_restocked_at ?
                            (string) $inventory->last_restocked_at : 'Never',
                        'last_updated' => $inventory->updated_at->format('Y-m-d'),
                        'owner' => $inventory->user->name ?? 'Unknown Owner'
                    ];
                })
                ->toArray();

            if (empty($inventoryData)) {
                return [[
                    'name' => 'No inventory data found',
                    'product_name' => 'N/A',
                    'stock' => 0,
                    'reorder_point' => 0,
                    'unit_cost' => '0.00',
                    'selling_price' => '0.00',
                    'unit' => 'N/A',
                    'location' => 'N/A',
                    'status' => 'N/A',
                    'last_restocked' => 'Never',
                    'last_updated' => 'N/A',
                    'owner' => 'N/A'
                ]];
            }

            return $inventoryData;
        } catch (\Exception $e) {
            Log::error('Error retrieving inventory data for preview', ['error' => $e->getMessage()]);
            return [[
                'name' => 'Error loading inventory data',
                'product_name' => 'Error',
                'stock' => 0,
                'reorder_point' => 0,
                'unit_cost' => '0.00',
                'selling_price' => '0.00',
                'unit' => 'Error',
                'location' => 'Error',
                'status' => 'Error',
                'last_restocked' => 'Error',
                'last_updated' => 'Error',
                'owner' => 'Error'
            ]];
        }
    }

    /**
     * Get supplier data for the report preview
     */
    private function getSupplierData(int $userId): array
    {
        try {
            $sellerIds = DB::table('orders')
                ->whereNotNull('seller_id')
                ->where('buyer_id', $userId)
                ->distinct()
                ->pluck('seller_id')
                ->toArray();

            if (empty($sellerIds)) {
                return [[
                    'name' => 'No supplier data found',
                    'email' => 'N/A',
                    'role' => 'N/A',
                    'total_orders' => 0,
                    'total_revenue' => '0.00',
                    'products_sold' => 0,
                    'last_order' => 'Never',
                    'status' => 'Inactive'
                ]];
            }

            $supplierData = [];
            foreach (array_slice($sellerIds, 0, 5) as $sellerId) { // Limit to 5 for preview
                $user = User::find($sellerId);
                if (!$user) continue;

                $stats = DB::table('orders')
                    ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.seller_id', $sellerId)
                    ->where('orders.buyer_id', $userId)
                    ->select(
                        DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                        DB::raw('COALESCE(SUM(order_items.quantity * order_items.unit_price), 0) as total_revenue'),
                        DB::raw('COUNT(DISTINCT order_items.product_id) as products_sold'),
                        DB::raw('MAX(orders.created_at) as last_order_date')
                    )
                    ->first();

                $supplierData[] = [
                    'name' => $user->name ?? 'Unknown Name',
                    'email' => $user->email ?? 'No email',
                    'role' => $user->role ? $user->role->value : 'Unknown',
                    'total_orders' => $stats->total_orders ?? 0,
                    'total_revenue' => number_format($stats->total_revenue ?? 0, 2),
                    'products_sold' => $stats->products_sold ?? 0,
                    'last_order' => $stats->last_order_date ?
                        Carbon::parse($stats->last_order_date)->format('Y-m-d') : 'Never',
                    'status' => ($stats->total_orders ?? 0) > 0 ? 'Active' : 'Inactive'
                ];
            }

            return $supplierData;
        } catch (\Exception $e) {
            Log::error('Error retrieving supplier data for preview', ['error' => $e->getMessage()]);
            return [[
                'name' => 'Error loading supplier data',
                'email' => 'Error',
                'role' => 'Error',
                'total_orders' => 0,
                'total_revenue' => '0.00',
                'products_sold' => 0,
                'last_order' => 'Error',
                'status' => 'Error'
            ]];
        }
    }

    /**
     * Get customer data for the report preview
     */
    private function getCustomerData(Carbon $startDate, Carbon $endDate, int $userId): array
    {
        try {
            $buyerIds = DB::table('orders')
                ->whereNotNull('buyer_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('seller_id', $userId)
                ->distinct()
                ->pluck('buyer_id')
                ->toArray();

            if (empty($buyerIds)) {
                return [[
                    'name' => 'No customer data found for the selected period',
                    'email' => 'N/A',
                    'role' => 'N/A',
                    'total_purchases' => '0.00',
                    'total_orders' => 0,
                    'avg_order_value' => '0.00',
                    'last_order' => 'Never',
                    'status' => 'Inactive'
                ]];
            }

            $customerData = [];
            foreach (array_slice($buyerIds, 0, 5) as $buyerId) { // Limit to 5 for preview
                $user = User::find($buyerId);
                if (!$user) continue;

                $stats = DB::table('orders')
                    ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.buyer_id', $buyerId)
                    ->where('orders.seller_id', $userId)
                    ->whereBetween('orders.created_at', [$startDate, $endDate])
                    ->select(
                        DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                        DB::raw('COALESCE(SUM(order_items.quantity * order_items.unit_price), 0) as total_purchases'),
                        DB::raw('MAX(orders.created_at) as last_order_date')
                    )
                    ->first();

                $avgOrderValue = ($stats->total_orders ?? 0) > 0 ?
                    ($stats->total_purchases ?? 0) / $stats->total_orders : 0;

                $customerData[] = [
                    'name' => $user->name ?? 'Unknown Name',
                    'email' => $user->email ?? 'No email',
                    'role' => $user->role ? $user->role->value : 'Unknown',
                    'total_purchases' => number_format($stats->total_purchases ?? 0, 2),
                    'total_orders' => $stats->total_orders ?? 0,
                    'avg_order_value' => number_format($avgOrderValue, 2),
                    'last_order' => $stats->last_order_date ?
                        Carbon::parse($stats->last_order_date)->format('Y-m-d') : 'Never',
                    'status' => ($stats->total_orders ?? 0) > 0 ? 'Active' : 'Inactive'
                ];
            }

            return $customerData;
        } catch (\Exception $e) {
            Log::error('Error retrieving customer data for preview', ['error' => $e->getMessage()]);
            return [[
                'name' => 'Error loading customer data',
                'email' => 'Error',
                'role' => 'Error',
                'total_purchases' => '0.00',
                'total_orders' => 0,
                'avg_order_value' => '0.00',
                'last_order' => 'Error',
                'status' => 'Error'
            ]];
        }
    }
}
