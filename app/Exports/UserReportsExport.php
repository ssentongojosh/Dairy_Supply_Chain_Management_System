<?php

namespace App\Exports;

use Illuminate\Contracts\View\View; // Required for FromView
use Maatwebsite\Excel\Concerns\FromView; // Trait to export data from a Blade view
use Maatwebsite\Excel\Concerns\WithTitle; // Trait to set the sheet title
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Trait to auto-size columns for better readability

class UserReportsExport implements FromView, WithTitle, ShouldAutoSize
{
    protected $reportData;
    protected $reportPeriodName;

    /**
     * Constructor to receive the data and report period name.
     *
     * @param array $reportData The data for different report types (sales, inventory, etc.)
     * @param string $reportPeriodName A descriptive name for the report period (e.g., "Last 7 Days")
     */
    public function __construct(array $reportData, string $reportPeriodName)
    {
        $this->reportData = $reportData;
        $this->reportPeriodName = $reportPeriodName;
    }

    /**
     * This method specifies which Blade view will be used to render the Excel sheet.
     * Maatwebsite\Excel converts the HTML table structure in the Blade view into an Excel file.
     *
     * @return View
     */
    public function view(): View
    {
        // This will look for a Blade file at resources/views/exports/user_reports_export.blade.php
        return view('exports.user_reports_export', [
            'reportData' => $this->reportData,
            'reportPeriodName' => $this->reportPeriodName,
        ]);
    }

    /**
     * This method defines the title for the Excel sheet (tab name).
     *
     * @return string
     */
    public function title(): string
    {
        return 'Report - ' . $this->reportPeriodName;
    }
}