<?php

namespace App\Services;

use App\Exports\UserReportsExport; // For Excel exports
use Maatwebsite\Excel\Facades\Excel; // For Excel exports
use Barryvdh\DomPDF\Facade\Pdf; // For PDF exports
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
                        $reportData['sales'] = $this->getSalesData($startDate, $endDate);
                        $reportTypesIncludedNames[] = 'Sales';
                        break;
                    case 'inventory':
                        $reportData['inventory'] = $this->getInventoryData();
                        $reportTypesIncludedNames[] = 'Inventory';
                        break;
                    case 'suppliers':
                        $reportData['suppliers'] = $this->getSupplierData();
                        $reportTypesIncludedNames[] = 'Suppliers';
                        break;
                    case 'customers':
                        $reportData['customers'] = $this->getCustomerData($startDate, $endDate);
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
            $attachmentName = "{$fileNameBase}_" . Carbon::now()->format('YmdHis') . ".{$format}";
            $fullFilePath = $reportStorageDir . '/' . $attachmentName;

            // --- 3. Generate and Save the File ---
            try {
                if ($format === 'excel') {
                    Excel::store(new UserReportsExport($reportData, $reportPeriodName), $fullFilePath, 'local');
                } elseif ($format === 'pdf') {
                    // Ensure you have 'pdfs.user_report' view created for PDF generation
                    $pdf = Pdf::loadView('pdfs.user_report', compact('reportData', 'reportPeriodName'));
                    Storage::disk('local')->put($fullFilePath, $pdf->output());
                } else {
                    $reportStatus = 'failed';
                    $errorMessage = "Unsupported report format: {$format}";
                }

                if ($reportStatus === 'success') {
                    $reportFileSize = Storage::disk('local')->size($fullFilePath);
                }

            } catch (\Exception $e) {
                $reportStatus = 'failed';
                $errorMessage = "File creation failed: " . $e->getMessage();
                Log::error($errorMessage);
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

    // --- Private methods for data retrieval (simulated here) ---
    // In a real app, these might be calls to dedicated repository or service classes
    private function getSalesData(Carbon $startDate, Carbon $endDate): array
    {
        // Simulate fetching sales data from your database or API
        // Example: return Sale::whereBetween('date', [$startDate, $endDate])->get()->toArray();
        return [
            ['date' => '2025-06-23', 'product' => 'Laptop', 'quantity' => 1, 'price' => 1200, 'total' => 1200],
            ['date' => '2025-06-25', 'product' => 'Mouse', 'quantity' => 2, 'price' => 25, 'total' => 50],
        ];
    }

    private function getInventoryData(): array
    {
        // Simulate fetching inventory data
        // Example: return InventoryItem::all()->toArray();
        return [
            ['name' => 'Laptop', 'stock' => 15, 'last_updated' => '2025-06-29'],
            ['name' => 'Keyboard', 'stock' => 30, 'last_updated' => '2025-06-28'],
        ];
    }

    private function getSupplierData(): array
    {
        // Simulate fetching supplier data
        // Example: return Supplier::all()->toArray();
        return [
            ['name' => 'Tech Supplies Inc.', 'contact' => 'john@example.com'],
            ['name' => 'Office Goods Ltd.', 'contact' => 'jane@example.com'],
        ];
    }

    private function getCustomerData(Carbon $startDate, Carbon $endDate): array
    {
        // Simulate fetching customer data
        // Example: return Customer::whereHas('orders', function($query) use ($startDate, $endDate) {
        //     $query->whereBetween('order_date', [$startDate, $endDate]);
        // })->get()->toArray();
        return [
            ['name' => 'Alice Smith', 'email' => 'alice@example.com', 'total_purchases' => 500],
            ['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'total_purchases' => 750],
        ];
    }
}