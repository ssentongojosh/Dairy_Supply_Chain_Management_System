<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\ReportConfiguration;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Services\ReportGeneratorService; // <-- NEW: Import your new service

// No longer need individual reporters or Excel/Pdf facades here, as the service handles it
// use App\Services\Reports\SalesReporter;
// use App\Services\Reports\InventoryReporter;
// use Maatwebsite\Excel\Facades\Excel;
// use Barryvdh\DomPDF\Facade\Pdf;
// use Illuminate\Support\Facades\Storage;


class ReportConfigurationController extends Controller
{
    // Inject the service via constructor for better testability (recommended)
    protected $reportGeneratorService;

    public function __construct(ReportGeneratorService $reportGeneratorService)
    {
        $this->reportGeneratorService = $reportGeneratorService;
    }

    // ... (your existing index() and store() methods remain the same) ...

    /**
     * Handles on-demand report generation and download.
     */
    public function downloadOnDemand(Request $request)
    {
        $user = Auth::user();

        // --- 1. Determine Report Configuration for On-Demand ---
        $selectedReportFormat = $request->input('report_format', 'excel');
        $selectedReportTypes = $request->input('report_types', ['sales']);

        $allowedFormats = ['excel', 'pdf'];
        if (!in_array($selectedReportFormat, $allowedFormats)) {
            $selectedReportFormat = 'excel';
        }

        $allowedTypes = ['sales', 'inventory', 'suppliers', 'customers'];
        $reportTypesToGenerate = array_intersect($selectedReportTypes, $allowedTypes);
        if (empty($reportTypesToGenerate)) {
            return back()->with('error', 'Please select at least one valid Report Type.');
        }

        // --- 2. Determine Report Period for On-Demand ---
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();
        $reportPeriodName = "Last 7 Days";


        // --- NEW: 3. Use the ReportGeneratorService to generate and store the report ---
        $result = $this->reportGeneratorService->generateAndStoreReport(
            $user->id,
            $reportTypesToGenerate,
            $selectedReportFormat,
            $startDate,
            $endDate,
            $reportPeriodName,
            'on-demand' // This instance is always 'on-demand'
        );

        // --- 4. Log the Generated Report to the 'reports' Table (using the service's result) ---
        // Note: We are still logging to the DB here as the ReportGeneratorService focuses on file operations.
        // It returns the data needed for the DB log.
        Report::create([
            'user_id' => $user->id,
            'report_name' => $result['reportNameForDB'],
            'frequency' => 'on-demand',
            'report_types' => $reportTypesToGenerate,
            'format' => $selectedReportFormat,
            'file_path' => $result['filePath'],
            'file_name' => $result['fileName'],
            'file_size' => $result['fileSize'],
            'report_start_date' => $startDate->toDateString(),
            'report_end_date' => $endDate->toDateString(),
            'generated_at' => Carbon::now(),
            'status' => $result['status'],
            'error_message' => $result['errorMessage'],
        ]);


        if ($result['status'] === 'failed' || !$result['filePath']) {
            return back()->with('error', $result['errorMessage'] ?: 'Failed to generate report file. Please check logs and try again.');
        }

        // --- 5. Serve the File for Download (without deleting) ---
        $filePathToDownload = Storage::disk('local')->path($result['filePath']);
        return response()->download($filePathToDownload, $result['fileName']);
    }
}
