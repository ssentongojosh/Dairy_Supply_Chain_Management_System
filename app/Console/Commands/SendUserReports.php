<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportConfiguration;
use App\Models\Report;
use App\Services\ReportGeneratorService; // Your new service
use App\Services\ReportNotificationService; // Our new notification service
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Support\Facades\Mail; // For sending emails
use App\Mail\ReportNotification; // You'll create this Mailable next

class SendUserReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends scheduled reports to users based on their configurations.';

    protected $reportGeneratorService;
    protected $reportNotificationService;

    /**
     * Create a new command instance.
     *
     * @param ReportGeneratorService $reportGeneratorService
     * @param ReportNotificationService $reportNotificationService
     * @return void
     */
    public function __construct(ReportGeneratorService $reportGeneratorService, ReportNotificationService $reportNotificationService)
    {
        parent::__construct();
        $this->reportGeneratorService = $reportGeneratorService;
        $this->reportNotificationService = $reportNotificationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting scheduled report generation...');
        Log::info('Scheduled report command started.');

        // Get all active report configurations
        $activeConfigs = ReportConfiguration::where('is_active', true)->get();

        if ($activeConfigs->isEmpty()) {
            $this->info('No active report configurations found.');
            Log::info('No active report configurations found.');
            return Command::SUCCESS;
        }

        $reportsGeneratedCount = 0;
        $reportsFailedCount = 0;

        foreach ($activeConfigs as $config) {
            // Check if the report is due
            if ($this->isReportDue($config)) {
                $this->info("Generating report for user ID: {$config->user_id} (Frequency: {$config->frequency})");
                Log::info("Generating scheduled report for user ID: {$config->user_id}, Config ID: {$config->id}");

                // Determine report period based on frequency
                list($startDate, $endDate, $reportPeriodName) = $this->getReportPeriod($config->frequency);

                // Use the ReportGeneratorService
                $result = $this->reportGeneratorService->generateAndStoreReport(
                    $config->user_id,
                    $config->report_types,
                    $config->format,
                    $startDate,
                    $endDate,
                    $reportPeriodName,
                    $config->frequency // Pass the scheduled frequency
                );

                // Log the report in the 'reports' table
                $report = Report::create([
                    'user_id' => $config->user_id,
                    'report_name' => $result['reportNameForDB'], // From service result
                    'frequency' => $config->frequency,
                    'report_types' => $config->report_types,
                    'format' => $config->format,
                    'file_path' => $result['filePath'],
                    'file_name' => $result['fileName'],
                    'file_size' => $result['fileSize'],
                    'report_start_date' => $startDate->toDateString(),
                    'report_end_date' => $endDate->toDateString(),
                    'generated_at' => Carbon::now(),
                    'status' => $result['status'],
                    'error_message' => $result['errorMessage'],
                ]);

                if ($result['status'] === 'success') {
                    $reportsGeneratedCount++;
                    // Update last generated timestamp on configuration
                    $config->update(['last_generated_at' => Carbon::now()]);
                    $this->info("Report generated successfully for user ID: {$config->user_id}");
                    Log::info("Report generated successfully for user ID: {$config->user_id}, File: {$result['fileName']}");

                    // Send notifications
                    $this->sendNotifications($config, $report);

                } else {
                    $reportsFailedCount++;
                    $this->error("Failed to generate report for user ID: {$config->user_id}. Error: {$result['errorMessage']}");
                    Log::error("Failed to generate report for user ID: {$config->user_id}, Config ID: {$config->id}. Error: {$result['errorMessage']}");
                }
            } else {
                $this->line("Report for user ID: {$config->user_id} not due yet.");
            }
        }

        $this->info("Scheduled report generation finished. Successfully generated: {$reportsGeneratedCount}, Failed: {$reportsFailedCount}.");
        Log::info("Scheduled report command finished. Generated: {$reportsGeneratedCount}, Failed: {$reportsFailedCount}.");

        return Command::SUCCESS;
    }

    /**
     * Determines if a report is due based on its configuration.
     *
     * @param ReportConfiguration $config
     * @return bool
     */
    protected function isReportDue(ReportConfiguration $config): bool
    {
        $now = Carbon::now();
        $lastGenerated = $config->last_generated_at;
        $sendTime = Carbon::createFromFormat('H:i:s', $config->send_time);

        // Check if the current time has passed the send_time for today
        if ($now->lessThan($sendTime)) {
            // If the current time is before the send time, it's not due yet today.
            // But what if it was due yesterday and failed? We need a window.
            // Let's assume the command runs once a day after the send_time.
            // For now, simple check if last_generated_at is today and after send_time
            // Or if last_generated_at is null (first run)
            return false; // Not due if current time is before configured send time
        }

        // Has it already been generated today (after its send time)?
        if ($lastGenerated && $lastGenerated->isSameDay($now) && $lastGenerated->greaterThanOrEqualTo($sendTime)) {
            return false; // Already generated for today/this period
        }

        // Calculate the next due date based on frequency
        $nextDueDate = null;

        switch ($config->frequency) {
            case 'daily':
                // It's due if the current time is past send_time and it hasn't been generated today
                return true; // Already checked lastGenerated and sendTime above

            case 'weekly':
                // Check if today is the configured day of the week AND time is past send_time
                if ($now->dayOfWeek === $config->day_of_week && $now->greaterThanOrEqualTo($sendTime)) {
                    // Check if it was generated this week on the correct day after the send time
                    if ($lastGenerated && $lastGenerated->isSameWeek($now) && $lastGenerated->dayOfWeek === $config->day_of_week && $lastGenerated->greaterThanOrEqualTo($sendTime)) {
                        return false;
                    }
                    return true;
                }
                break;

            case 'biweekly':
                // For biweekly, we need to track weeks since a fixed point (e.g., start of year or config creation)
                // This logic can be tricky without a 'start_date' for the biweekly cycle.
                // A simpler approach: is it the correct day of week, and has it been two weeks since last generated?
                if ($now->dayOfWeek === $config->day_of_week && $now->greaterThanOrEqualTo($sendTime)) {
                     if (!$lastGenerated) return true; // First run

                     $weeksSinceLast = $lastGenerated->diffInWeeks($now);
                     return $weeksSinceLast >= 2 && $now->greaterThanOrEqualTo($sendTime);
                }
                break;

            case 'monthly':
                // Check if today is the configured day of the month AND time is past send_time
                if ($now->day === $config->day_of_month && $now->greaterThanOrEqualTo($sendTime)) {
                    // Check if it was generated this month on the correct day after the send time
                    if ($lastGenerated && $lastGenerated->isSameMonth($now) && $lastGenerated->day === $config->day_of_month && $lastGenerated->greaterThanOrEqualTo($sendTime)) {
                        return false;
                    }
                    return true;
                }
                break;
        }

        return false;
    }


    /**
     * Get the report period (start date, end date, and name) based on frequency.
     *
     * @param string $frequency
     * @return array [Carbon $startDate, Carbon $endDate, string $periodName]
     */
    protected function getReportPeriod(string $frequency): array
    {
        $endDate = Carbon::now(); // End of the period is generally "now" or "yesterday"
        $startDate = null;
        $periodName = '';

        switch ($frequency) {
            case 'daily':
                $startDate = Carbon::yesterday()->startOfDay(); // Data for yesterday
                $endDate = Carbon::yesterday()->endOfDay();
                $periodName = "Daily Report (" . $startDate->format('Y-m-d') . ")";
                break;
            case 'weekly':
                $startDate = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY); // Last full week (Mon-Sun)
                $endDate = Carbon::now()->subWeek()->endOfWeek(Carbon::SUNDAY);
                $periodName = "Weekly Report (Week of " . $startDate->format('Y-m-d') . ")";
                break;
            case 'biweekly':
                // This assumes current week is week 2, so data is from week 1 & 2 before that.
                $startDate = Carbon::now()->subWeeks(2)->startOfWeek(Carbon::MONDAY);
                $endDate = Carbon::now()->subWeek()->endOfWeek(Carbon::SUNDAY);
                $periodName = "Bi-Weekly Report (Period ending " . $endDate->format('Y-m-d') . ")";
                break;
            case 'monthly':
                $startDate = Carbon::now()->subMonth()->startOfMonth(); // Data for last full month
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                $periodName = "Monthly Report (" . $startDate->format('F Y') . ")";
                break;
            default:
                // Fallback for on-demand or unknown
                $startDate = Carbon::now()->subDays(7)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $periodName = "Report (" . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . ")";
                break;
        }

        return [$startDate, $endDate, $periodName];
    }


    /**
     * Send notifications based on the configuration.
     *
     * @param ReportConfiguration $config
     * @param Report $report The generated report record.
     * @return void
     */
    protected function sendNotifications(ReportConfiguration $config, Report $report): void
    {
        $user = $config->user; // Access the related user model

        if (!$user) {
            Log::warning("User not found for report configuration ID: {$config->id}. Cannot send notifications.");
            return;
        }

        foreach ($config->notification_channels as $channel) {
            try {
                if ($channel === 'email' && $user->email) {
                    Mail::to($user->email)->send(new ReportNotification($report, $user));
                    Log::info("Email notification sent for report ID: {$report->id} to {$user->email}");
                }
                if ($channel === 'database') {
                    // Legacy database notification
                    Log::info("Database notification logged for report ID: {$report->id} for user {$user->id}");
                }
                if ($channel === 'system') {
                    // New system notification with navbar integration
                    $reportNotification = $this->reportNotificationService->createNotification(
                        $user,
                        $report->report_name,
                        $report->report_types,
                        $report->format,
                        $report->file_path,
                        $report->file_name,
                        $report->file_size
                    );
                    
                    $this->reportNotificationService->sendSystemNotification($reportNotification);
                    Log::info("System notification sent for report ID: {$report->id} to user {$user->id}, notification ID: {$reportNotification->id}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send notification via {$channel} for report ID: {$report->id}. Error: " . $e->getMessage());
            }
        }
    }
}