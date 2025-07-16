<?php

namespace App\Services;

use App\Exports\UserReportsExport; // For Excel exports
use Maatwebsite\Excel\Facades\Excel; // For Excel exports
use Barryvdh\DomPDF\Facade\Pdf; // For PDF exports
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Import your individual data reporters (these will reside within the service)


class ReportGeneratorService
{
    /**
     * Generates a report file (Excel or PDF) and saves it permanently.
     *
     * @param int $userId The ID of the user requesting the report.
     * @param array $reportTypes The types of data to include (e.g., ['sales', 'inventory']).
     * @param string $format The desired format ('excel' or 'pdf').
     * @param \Carbon\Carbon $startDate The start date for the report data.
     * @param \Carbon\Carbon $endDate The end date for the report data.
     * @param string $reportPeriodName A user-friendly name for the report period (e.g., "Last 7 Days").
     * @param string $frequency The report's frequency ('on-demand', 'daily', 'weekly', etc.).
     * @return array An array containing 'status', 'filePath', 'fileName', 'fileSize', 'errorMessage', 'reportNameForDB'.
     */
    public function generateAndStoreReport(
        int $userId,
        array $reportTypes,
        string $format,
        Carbon $startDate,
        Carbon $endDate,
        string $reportPeriodName,
        string $frequency // Used for the DB log
    ): array {
        $reportData = [];
        $reportTypesIncludedNames = []; // For the 'report_name' column in DB
        $reportStatus = 'success';
        $errorMessage = null;
        $reportFileSize = 0;
        $fullFilePath = null;
        $attachmentName = null;

        // --- 1. Gather Report Data for each specified type ---
        foreach ($reportTypes as $type) {
            try {
                switch ($type) {
                    case 'sales':
                        // In a real application, you might inject/resolve SalesReporter here
                        // For simplicity, we'll simulate data or keep it simple.
                        // If you want separate services, make sure they are included.
                        // Example: $reportData['sales'] = (new SalesReporter())->generate($startDate, $endDate);
                        $reportData['sales'] = $this->getSalesData($startDate, $endDate, $userId);
                        $reportTypesIncludedNames[] = 'Sales';
                        break;
                    case 'inventory':
                        $reportData['inventory'] = $this->getInventoryData($userId);
                        $reportTypesIncludedNames[] = 'Inventory';
                        break;
                    case 'suppliers':
                        $reportData['suppliers'] = $this->getSupplierData($userId);
                        $reportTypesIncludedNames[] = 'Suppliers';
                        break;
                    case 'customers':
                        $reportData['customers'] = $this->getCustomerData($startDate, $endDate, $userId);
                        $reportTypesIncludedNames[] = 'Customers';
                        break;
                    default:
                        // Log unknown type, but don't fail entire report
                        Log::warning("Unknown report type requested: {$type}");
                }
            } catch (\Exception $e) {
                // Log the error for this specific data type, but try to continue with others
                $errorMessage = "Error generating {$type} data: " . $e->getMessage();
                Log::error($errorMessage);
                $reportStatus = 'failed'; // Mark overall status as failed if any data fails
            }
        }

        if (empty($reportData) && $reportStatus === 'success') { // If no data but no error, it means no data for selected types
            $reportStatus = 'failed';
            $errorMessage = "No data generated for any selected report types.";
        }

        // Only proceed to file generation if data gathering was successful or partially successful
        if (!empty($reportData) && $reportStatus !== 'failed') { // If some data was gathered
            // --- 2. Define Storage Path and File Name ---
            $reportStorageDir = 'reports/' . $userId; // e.g., storage/app/reports/1/
            Storage::disk('local')->makeDirectory($reportStorageDir); // Ensure the user's report directory exists

            $fileNameBase = (!empty($reportTypesIncludedNames) ? implode('_', $reportTypesIncludedNames) : 'Report') . '_' . str_replace(' ', '_', $reportPeriodName);

            // Set proper file extension based on format
            $fileExtension = ($format === 'excel') ? 'xlsx' : $format;
            $attachmentName = "{$fileNameBase}_" . Carbon::now()->format('YmdHis') . ".{$fileExtension}";
            $fullFilePath = $reportStorageDir . '/' . $attachmentName;

            // --- 3. Generate and Save the File ---
            try {
                if ($format === 'excel') {
                    // Use specific XLSX writer for better compatibility
                    Excel::store(new UserReportsExport($reportData, $reportPeriodName), $fullFilePath, 'local', \Maatwebsite\Excel\Excel::XLSX);
                } elseif ($format === 'pdf') {
                    // Ensure you have 'pdfs.user_report' view created for PDF generation
                    $pdf = Pdf::loadView('pdfs.user_report', compact('reportData', 'reportPeriodName'));
                    Storage::disk('local')->put($fullFilePath, $pdf->output());
                } else {
                    $reportStatus = 'failed';
                    $errorMessage = "Unsupported report format: {$format}";
                }

                if ($reportStatus === 'success') {
                    // Verify file was actually created
                    if (Storage::disk('local')->exists($fullFilePath)) {
                        $reportFileSize = Storage::disk('local')->size($fullFilePath);
                    } else {
                        $reportStatus = 'failed';
                        $errorMessage = "File was not created successfully at: {$fullFilePath}";
                        Log::error("File creation verification failed", ['path' => $fullFilePath]);
                    }
                }

            } catch (\Exception $e) {
                $reportStatus = 'failed';
                $errorMessage = "File creation failed for {$format}: " . $e->getMessage();
                Log::error("Report generation error", [
                    'format' => $format,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'file_path' => $fullFilePath ?? 'unknown'
                ]);
            }
        } else if ($reportStatus === 'failed') {
            // If data gathering failed, no file path/name
            $fullFilePath = null;
            $attachmentName = null;
        }


        return [
            'status' => $reportStatus,
            'filePath' => $fullFilePath,
            'fileName' => $attachmentName,
            'fileSize' => $reportFileSize,
            'errorMessage' => $errorMessage,
            'reportNameForDB' => (!empty($reportTypesIncludedNames) ? implode(', ', $reportTypesIncludedNames) : 'Unknown') . ' ' . $reportPeriodName,
            'reportData' => $reportData // Return reportData if needed for email content, etc.
        ];
    }

    // --- Private methods for data retrieval using real database queries ---
    private function getSalesData(Carbon $startDate, Carbon $endDate, int $userId = null): array
    {
        try {
            // Fetch sales data from orders and order items within the date range
            $query = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
                ->leftJoin('users as sellers', 'orders.seller_id', '=', 'sellers.id')
                ->leftJoin('users as buyers', 'orders.buyer_id', '=', 'buyers.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('orders.status', '!=', 'cancelled');

            if ($userId) {
                // Filter orders where the current user is either buyer or seller
                $query->where(function($q) use ($userId) {
                    $q->where('orders.buyer_id', $userId)
                      ->orWhere('orders.seller_id', $userId);
                });
            }

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

            Log::info('Sales data retrieved successfully', ['user_id' => $userId, 'count' => count($salesData)]);

            // Add fallback message if no data found
            if (empty($salesData)) {
                $salesData = [
                    [
                        'date' => 'N/A',
                        'product' => 'No sales data found for the selected period',
                        'quantity' => 0,
                        'price' => '0.00',
                        'total' => '0.00',
                        'seller' => 'N/A',
                        'buyer' => 'N/A',
                        'status' => 'N/A'
                    ]
                ];
            }

            return $salesData;
        } catch (\Exception $e) {
            Log::error('Error retrieving sales data', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function getInventoryData(int $userId = null): array
    {
        try {
            // Fetch inventory data from the inventory table
            $query = Inventory::with(['product', 'user'])
                ->select([
                    'id',
                    'quantity',
                    'reorder_point',
                    'unit_cost',
                    'selling_price',
                    'location',
                    'last_restocked_at',
                    'updated_at',
                    'product_id',
                    'user_id'
                ]);

            if ($userId) {
                // Filter inventory by the current user
                $query->where('user_id', $userId);
            }

            $inventoryData = $query->get()
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
                        'status' => $inventory->auto_status, // Uses the accessor from the model
                        'last_restocked' => $inventory->last_restocked_at ?
                            (string) $inventory->last_restocked_at : 'Never',
                        'last_updated' => $inventory->updated_at->format('Y-m-d'),
                        'owner' => $inventory->user->name ?? 'Unknown Owner'
                    ];
                })
                ->toArray();

            Log::info('Inventory data retrieved successfully', ['user_id' => $userId, 'count' => count($inventoryData)]);

            // Add fallback message if no data found
            if (empty($inventoryData)) {
                $inventoryData = [
                    [
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
                    ]
                ];
            }

            return $inventoryData;
        } catch (\Exception $e) {
            Log::error('Error retrieving inventory data', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function getSupplierData(?int $userId = null): array
    {
        try {
            // Get unique seller IDs from orders where the current user was the buyer
            $query = DB::table('orders')->whereNotNull('seller_id');

            if ($userId) {
                // Show suppliers (sellers) from orders where current user was the buyer
                $query->where('buyer_id', $userId);
            }

            $sellerIds = $query->distinct()
                ->pluck('seller_id')
                ->toArray();

            Log::info('Found seller IDs for user', ['user_id' => $userId, 'seller_ids' => $sellerIds]);

            if (empty($sellerIds)) {
                // If no sellers found, return fallback data
                $supplierData = [
                    [
                        'name' => 'No supplier data found',
                        'email' => 'N/A',
                        'role' => 'N/A',
                        'total_orders' => 0,
                        'total_revenue' => '0.00',
                        'products_sold' => 0,
                        'last_order' => 'Never',
                        'status' => 'Inactive'
                    ]
                ];
            } else {
                // Get supplier data for each seller
                $supplierData = [];

                foreach ($sellerIds as $sellerId) {
                    $user = User::find($sellerId);
                    if (!$user) continue;

                    // Calculate statistics for this supplier with user context
                    $statsQuery = DB::table('orders')
                        ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                        ->where('orders.seller_id', $sellerId);

                    // Apply same user filtering as the main query
                    if ($userId) {
                        $statsQuery->where('orders.buyer_id', $userId);
                    }

                    $stats = $statsQuery->select(
                            DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                            DB::raw('COALESCE(SUM(order_items.quantity * order_items.unit_price), 0) as total_revenue'),
                            DB::raw('COUNT(DISTINCT order_items.product_id) as products_sold'),
                            DB::raw('MAX(orders.created_at) as last_order_date')
                        )
                        ->first();

                    $supplierData[] = [
                        'name' => $user->name ?? 'Unknown Name',
                        'email' => $user->email ?? 'No Email',
                        'role' => $user->role ? $user->role->value : 'Unknown',
                        'total_orders' => $stats->total_orders ?? 0,
                        'total_revenue' => number_format($stats->total_revenue ?? 0, 2),
                        'products_sold' => $stats->products_sold ?? 0,
                        'last_order' => $stats->last_order_date ?
                            Carbon::parse($stats->last_order_date)->format('Y-m-d') : 'Never',
                        'status' => ($stats->total_orders ?? 0) > 0 ? 'Active' : 'Inactive'
                    ];

                    Log::info('Added supplier to data', [
                        'name' => $user->name,
                        'email' => $user->email,
                        'has_email' => !empty($user->email)
                    ]);
                }

                // Sort by total revenue descending
                usort($supplierData, function($a, $b) {
                    return (float)str_replace(',', '', $b['total_revenue']) <=> (float)str_replace(',', '', $a['total_revenue']);
                });
            }

            Log::info('Supplier data retrieved successfully', ['user_id' => $userId, 'count' => count($supplierData)]);
            return $supplierData;
        } catch (\Exception $e) {
            Log::error('Error retrieving supplier data', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function getCustomerData(Carbon $startDate, Carbon $endDate, int $userId = null): array
    {
        try {
            // Get unique buyer IDs from orders where the current user was the seller
            $query = DB::table('orders')
                ->whereNotNull('buyer_id')
                ->whereBetween('created_at', [$startDate, $endDate]);

            if ($userId) {
                // Show customers (buyers) from orders where current user was the seller
                $query->where('seller_id', $userId);
            }

            $buyerIds = $query->distinct()
                ->pluck('buyer_id')
                ->toArray();

            Log::info('Found buyer IDs for user and date range', ['user_id' => $userId, 'buyer_ids' => $buyerIds, 'date_range' => [$startDate, $endDate]]);

            if (empty($buyerIds)) {
                // If no buyers found, return fallback data
                $customerData = [
                    [
                        'name' => 'No customer data found for the selected period',
                        'email' => 'N/A',
                        'role' => 'N/A',
                        'total_orders' => 0,
                        'total_purchases' => '0.00',
                        'products_purchased' => 0,
                        'last_order' => 'Never',
                        'first_order' => 'Never',
                        'customer_status' => 'New'
                    ]
                ];
            } else {
                // Get customer data for each buyer
                $customerData = [];

                foreach ($buyerIds as $buyerId) {
                    $user = User::find($buyerId);
                    if (!$user) continue;

                    // Calculate statistics for this customer within the date range and user context
                    $statsQuery = DB::table('orders')
                        ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                        ->where('orders.buyer_id', $buyerId)
                        ->whereBetween('orders.created_at', [$startDate, $endDate]);

                    // Apply user filtering if specified
                    if ($userId) {
                        $statsQuery->where('orders.seller_id', $userId);
                    }

                    $stats = $statsQuery->select(
                            DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                            DB::raw('COALESCE(SUM(order_items.quantity * order_items.unit_price), 0) as total_purchases'),
                            DB::raw('COUNT(DISTINCT order_items.product_id) as products_purchased'),
                            DB::raw('MAX(orders.created_at) as last_order_date'),
                            DB::raw('MIN(orders.created_at) as first_order_date')
                        )
                        ->first();

                    $customerData[] = [
                        'name' => $user->name ?? 'Unknown Name',
                        'email' => $user->email ?? 'No Email',
                        'role' => $user->role ? $user->role->value : 'Unknown',
                        'total_orders' => $stats->total_orders ?? 0,
                        'total_purchases' => number_format($stats->total_purchases ?? 0, 2),
                        'products_purchased' => $stats->products_purchased ?? 0,
                        'last_order' => $stats->last_order_date ?
                            Carbon::parse($stats->last_order_date)->format('Y-m-d') : 'Never',
                        'first_order' => $stats->first_order_date ?
                            Carbon::parse($stats->first_order_date)->format('Y-m-d') : 'Never',
                        'customer_status' => ($stats->total_orders ?? 0) > 5 ? 'VIP' :
                            (($stats->total_orders ?? 0) > 0 ? 'Regular' : 'New')
                    ];

                    Log::info('Added customer to data', [
                        'name' => $user->name,
                        'email' => $user->email,
                        'has_email' => !empty($user->email)
                    ]);
                }

                // Sort by total purchases descending
                usort($customerData, function($a, $b) {
                    return (float)str_replace(',', '', $b['total_purchases']) <=> (float)str_replace(',', '', $a['total_purchases']);
                });
            }

            Log::info('Customer data retrieved successfully', ['user_id' => $userId, 'count' => count($customerData)]);
            return $customerData;
        } catch (\Exception $e) {
            Log::error('Error retrieving customer data', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
