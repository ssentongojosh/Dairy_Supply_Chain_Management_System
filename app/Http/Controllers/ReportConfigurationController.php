<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
 use Illuminate\Support\Facades\Storage;


class ReportConfigurationController extends Controller
{
    // Inject the service via constructor for better testability (recommended)
    protected $reportGeneratorService;

    public function __construct(ReportGeneratorService $reportGeneratorService)
    {
        $this->reportGeneratorService = $reportGeneratorService;
    }

    /**
     * Display the report settings form.
     */
    public function index()
    {
        $user = Auth::user();

        // Get existing configuration for the user, or create default values
        $configuration = ReportConfiguration::where('user_id', $user->id)->first();

        if (!$configuration) {
            // Create default configuration
            $configuration = new ReportConfiguration([
                'frequency' => 'weekly',
                'send_time' => '09:00',
                'day_of_week' => 1, // Monday
                'report_types' => ['sales'],
                'format' => 'excel',
                'notification_channels' => ['email'],
                'is_active' => true,
            ]);
        }

        return view('reports.reportsettings', compact('configuration'));
    }

    /**
     * Store or update report configuration settings.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate the request
        $validatedData = $request->validate([
            'frequency' => 'required|in:daily,weekly,biweekly,monthly',
            'send_time' => 'required|date_format:H:i',
            'day_of_week' => 'nullable|integer|between:0,6',
            'day_of_month' => 'nullable|integer|between:1,31',
            'report_types' => 'required|array|min:1',
            'report_types.*' => 'in:sales,inventory,suppliers,customers',
            'format' => 'required|in:excel,pdf',
            'notification_channels' => 'nullable|array',
            'notification_channels.*' => 'in:email,database',
            'is_active' => 'boolean',
        ], [
            'frequency.required' => 'Please select a report frequency.',
            'send_time.required' => 'Please specify a send time.',
            'report_types.required' => 'Please select at least one report type.',
            'report_types.min' => 'Please select at least one report type.',
            'format.required' => 'Please select a report format.',
        ]);

        // Handle boolean conversion for is_active
        $validatedData['is_active'] = $request->has('is_active');

        // Set notification_channels default if not provided
        if (!isset($validatedData['notification_channels'])) {
            $validatedData['notification_channels'] = [];
        }

        try {
            // Update or create configuration
            $configuration = ReportConfiguration::updateOrCreate(
                ['user_id' => $user->id],
                $validatedData
            );

            // Create detailed success message based on settings
            $reportTypesText = implode(', ', array_map('ucfirst', $validatedData['report_types']));
            $statusText = $validatedData['is_active'] ? 'activated' : 'saved as draft';
            $frequencyText = ucfirst($validatedData['frequency']);

            $successMessage = "Report settings {$statusText} successfully! ";
            $successMessage .= "Configuration: {$frequencyText} {$reportTypesText} reports in {$validatedData['format']} format";
            if ($validatedData['is_active']) {
                $successMessage .= " will be generated automatically at {$validatedData['send_time']}.";
            } else {
                $successMessage .= " (saved as draft - not yet active).";
            }

            return back()->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Failed to save report configuration: ' . $e->getMessage());
            return back()->with('error', 'Failed to save report settings. Please check your input and try again.');
        }
    }

    /**
     * Handles on-demand report generation and download.
     */
    public function downloadOnDemand(Request $request)
    {
        $user = Auth::user();

        // --- 1. Determine Report Configuration for On-Demand ---
        $selectedReportFormat = $request->input('format', 'excel');
        $selectedReportTypes = $request->input('report_types', ['sales']);

        $allowedFormats = ['excel', 'pdf'];
        if (!in_array($selectedReportFormat, $allowedFormats)) {
            $selectedReportFormat = 'excel';
        }

        // Enhanced validation with better error messages
        $allowedTypes = ['sales', 'inventory', 'suppliers', 'customers'];
        $reportTypesToGenerate = array_intersect($selectedReportTypes, $allowedTypes);

        // Check if no report types are selected
        if (empty($reportTypesToGenerate)) {
            return back()->with('error', 'Please select at least one report type (Sales, Inventory, Suppliers, or Customers) before generating the report.');
        }

        // Check if no format is explicitly selected (when coming from form)
        if (empty($request->input('format'))) {
            return back()->with('error', 'Please select a report format (Excel or PDF) before generating the report.');
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

        // Set appropriate headers for different file types
        $headers = [];
        if (strpos($result['fileName'], '.xlsx') !== false) {
            $headers['Content-Type'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } elseif (strpos($result['fileName'], '.pdf') !== false) {
            $headers['Content-Type'] = 'application/pdf';
        }

        return response()->download($filePathToDownload, $result['fileName'], $headers);
    }
}
