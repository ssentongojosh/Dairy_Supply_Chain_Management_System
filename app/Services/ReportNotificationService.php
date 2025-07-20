<?php

namespace App\Services;

use App\Models\ReportNotification;
use App\Models\User;
use App\Notifications\ReportGeneratedSystemNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportNotificationService
{
    /**
     * Create a new report notification record
     */
    public function createNotification(
        User $user,
        string $reportName,
        array $reportTypes,
        string $format,
        string $filePath,
        string $fileName,
        int $fileSize
    ): ReportNotification {
        try {
            $notification = ReportNotification::create([
                'user_id' => $user->id,
                'report_name' => $reportName,
                'report_types' => $reportTypes,
                'format' => $format,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'status' => 'success',
                'generated_at' => now(),
                'is_read' => false
            ]);

            Log::info('Report notification created', [
                'user_id' => $user->id,
                'report_name' => $reportName,
                'notification_id' => $notification->id
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create report notification', [
                'user_id' => $user->id,
                'report_name' => $reportName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create a failed report notification record
     */
    public function createFailedNotification(
        User $user,
        string $reportName,
        array $reportTypes,
        string $format,
        string $errorMessage
    ): ReportNotification {
        try {
            $notification = ReportNotification::create([
                'user_id' => $user->id,
                'report_name' => $reportName,
                'report_types' => $reportTypes,
                'format' => $format,
                'status' => 'failed',
                'error_message' => $errorMessage,
                'generated_at' => now(),
                'is_read' => false
            ]);

            Log::info('Failed report notification created', [
                'user_id' => $user->id,
                'report_name' => $reportName,
                'notification_id' => $notification->id,
                'error' => $errorMessage
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create failed report notification', [
                'user_id' => $user->id,
                'report_name' => $reportName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send system notification to user
     */
    public function sendSystemNotification(ReportNotification $reportNotification): void
    {
        try {
            $user = $reportNotification->user;

            if (!$user) {
                Log::warning('Cannot send notification - user not found', [
                    'report_notification_id' => $reportNotification->id
                ]);
                return;
            }

            // Send Laravel notification to appear in navbar
            $user->notify(new ReportGeneratedSystemNotification($reportNotification));

            Log::info('System notification sent', [
                'user_id' => $user->id,
                'report_notification_id' => $reportNotification->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send system notification', [
                'report_notification_id' => $reportNotification->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        try {
            $notification = ReportNotification::where('id', $notificationId)
                ->where('user_id', $userId)
                ->first();

            if (!$notification) {
                return false;
            }

            $notification->markAsRead();

            Log::info('Report notification marked as read', [
                'notification_id' => $notificationId,
                'user_id' => $userId
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get user's unread report notifications
     */
    public function getUnreadNotifications(User $user, int $limit = 10)
    {
        return ReportNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('generated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's report notifications with pagination
     */
    public function getUserNotifications(User $user, int $perPage = 15)
    {
        return ReportNotification::where('user_id', $user->id)
            ->orderBy('generated_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Check if report file exists and is accessible
     */
    public function isReportFileAccessible(ReportNotification $notification): bool
    {
        if (!$notification->file_path) {
            return false;
        }

        try {
            return Storage::exists($notification->file_path);
        } catch (\Exception $e) {
            Log::error('Error checking report file accessibility', [
                'notification_id' => $notification->id,
                'file_path' => $notification->file_path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clean up old report notifications and files
     */
    public function cleanupOldNotifications(int $daysToKeep = 30): int
    {
        try {
            $cutoffDate = now()->subDays($daysToKeep);

            $oldNotifications = ReportNotification::where('generated_at', '<', $cutoffDate)->get();

            $deletedCount = 0;
            foreach ($oldNotifications as $notification) {
                // Delete the file if it exists
                if ($notification->file_path && Storage::exists($notification->file_path)) {
                    Storage::delete($notification->file_path);
                }

                // Delete the notification record
                $notification->delete();
                $deletedCount++;
            }

            Log::info('Cleaned up old report notifications', [
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate
            ]);

            return $deletedCount;
        } catch (\Exception $e) {
            Log::error('Failed to cleanup old notifications', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
