<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportNotification;
use App\Services\ReportNotificationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ReportNotificationController extends Controller
{
    protected $reportNotificationService;

    public function __construct(ReportNotificationService $reportNotificationService)
    {
        $this->reportNotificationService = $reportNotificationService;
        $this->middleware('auth');
    }

    /**
     * Download a report file
     */
    public function download(Request $request, $id)
    {
        try {
            $notification = ReportNotification::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$notification) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Report not found'], 404);
                }
                abort(404, 'Report not found');
            }

            if ($notification->status !== 'success') {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Report generation failed'], 400);
                }
                abort(400, 'Report generation failed');
            }

            if (!$this->reportNotificationService->isReportFileAccessible($notification)) {
                Log::warning('Report file not accessible', [
                    'notification_id' => $id,
                    'file_path' => $notification->file_path,
                    'user_id' => Auth::id()
                ]);

                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Report file not available'], 404);
                }
                abort(404, 'Report file not available');
            }

            // Mark notification as read when downloaded
            $this->reportNotificationService->markAsRead($notification->id, Auth::id());            // Return file download
            return Storage::download($notification->file_path, $notification->file_name);

        } catch (\Exception $e) {
            Log::error('Report download failed', [
                'notification_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Download failed'], 500);
            }
            abort(500, 'Download failed');
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        try {
            $success = $this->reportNotificationService->markAsRead($id, Auth::id());

            if (!$success) {
                return response()->json(['error' => 'Notification not found'], 404);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to mark as read'], 500);
        }
    }

    /**
     * Get user's report notifications (API only)
     */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $notifications = $this->reportNotificationService->getUserNotifications($user);

            return response()->json($notifications);

        } catch (\Exception $e) {
            Log::error('Failed to load notifications', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load notifications'], 500);
        }
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(): JsonResponse
    {
        try {
            $user = Auth::user();
            $count = ReportNotification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json(['count' => $count]);

        } catch (\Exception $e) {
            Log::error('Failed to get unread count', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['count' => 0]);
        }
    }

    /**
     * Get recent unread notifications for navbar
     */
    public function recent(): JsonResponse
    {
        try {
            $user = Auth::user();
            $notifications = $this->reportNotificationService->getUnreadNotifications($user, 5);

            $formattedNotifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => 'Report Generated',
                    'message' => "Your {$notification->report_name} report is ready",
                    'time' => Carbon::parse($notification->generated_at)->diffForHumans(),
                    'icon' => 'fas fa-file-alt',
                    'download_url' => route('reports.download', $notification->id),
                    'status' => $notification->status,
                    'format' => strtoupper($notification->format),
                    'file_size' => $notification->formatted_file_size
                ];
            });

            return response()->json($formattedNotifications);

        } catch (\Exception $e) {
            Log::error('Failed to get recent notifications', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([]);
        }
    }

    /**
     * Mark all notifications as read for the current user
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Mark all Laravel notifications as read
            $user->unreadNotifications->markAsRead();

            // Mark all report notifications as read
            ReportNotification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            Log::info('All notifications marked as read', [
                'user_id' => $user->id
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'error' => 'Failed to mark notifications as read'], 500);
        }
    }
}
