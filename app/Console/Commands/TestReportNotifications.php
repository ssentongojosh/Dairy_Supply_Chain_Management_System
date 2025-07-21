<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Report;
use App\Services\ReportNotificationService;

class TestReportNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:report-notifications {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the report notification system';

    protected $reportNotificationService;

    public function __construct(ReportNotificationService $reportNotificationService)
    {
        parent::__construct();
        $this->reportNotificationService = $reportNotificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        /** @var User|null $user */
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return Command::FAILURE;
        }

        $this->info("Testing report notification system for user: {$user->name} ({$user->email})");

        try {
            // First create a report entry (like the real system does)
            $report = \App\Models\Report::create([
                'user_id' => $user->id,
                'report_name' => 'Weekly Sales Report (Test)',
                'frequency' => 'daily',
                'report_types' => ['sales', 'inventory'],
                'format' => 'pdf',
                'file_path' => 'reports/test_report.pdf',
                'file_name' => 'weekly_sales_report_test.pdf',
                'file_size' => 245760,
                'report_start_date' => now()->subDays(7)->toDateString(),
                'report_end_date' => now()->toDateString(),
                'generated_at' => now(),
                'status' => 'success',
                'error_message' => null,
            ]);

            $this->info("✓ Report created in reports table (ID: {$report->id})");

            // Then create a notification for it
            $notification = $this->reportNotificationService->createNotification(
                $user,
                $report->report_name,
                $report->report_types,
                $report->format,
                $report->file_path,
                $report->file_name,
                $report->file_size
            );

            $this->info("✓ Report notification created successfully (ID: {$notification->id})");

            // Send system notification
            $this->reportNotificationService->sendSystemNotification($notification);
            $this->info("✓ System notification sent successfully");

            // Test unread count
            $unreadCount = $this->reportNotificationService->getUnreadNotifications($user)->count();
            $this->info("✓ User has {$unreadCount} unread notifications");

            $this->info("🎉 All tests passed! Report will appear in both Report History and navbar notifications.");

        } catch (\Exception $e) {
            $this->error("❌ Test failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
