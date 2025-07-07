<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Report; // Import your Report model
use Illuminate\Support\Facades\Storage; // For file operations
use Symfony\Component\HttpFoundation\StreamedResponse; // For file downloads

class ReportHistoryController extends Controller
{
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

        // Prepare report data for preview (this is simulation since we don't store the actual data)
        // In a real app, you might want to store the report data separately or regenerate it
        $reportData = [
            'sales' => [
                ['date' => '2025-06-23', 'product' => 'Laptop', 'quantity' => 1, 'price' => 1200, 'total' => 1200],
                ['date' => '2025-06-25', 'product' => 'Mouse', 'quantity' => 2, 'price' => 25, 'total' => 50],
            ],
            'inventory' => [
                ['name' => 'Laptop', 'stock' => 15, 'last_updated' => '2025-06-29'],
                ['name' => 'Keyboard', 'stock' => 30, 'last_updated' => '2025-06-28'],
            ],
            'suppliers' => [
                ['name' => 'Tech Supplies Inc.', 'contact' => 'john@example.com', 'total_orders' => 5],
                ['name' => 'Office Goods Ltd.', 'contact' => 'jane@example.com', 'total_orders' => 3],
            ],
            'customers' => [
                ['name' => 'Alice Smith', 'email' => 'alice@example.com', 'total_purchases' => 500],
                ['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'total_purchases' => 750],
            ]
        ];

        $reportPeriodName = "Last 30 Days - Preview";

        // Determine which view to use based on format
        if ($report->format === 'pdf') {
            return view('pdfs.user_report', compact('reportData', 'reportPeriodName'));
        } else {
            return view('exports.user_reports_export', compact('reportData', 'reportPeriodName'));
        }
    }
}
