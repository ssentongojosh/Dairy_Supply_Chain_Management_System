<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\View;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.sections.navbar.navbar', function ($view) {
            if (Auth::check()) {
                // Message notifications
                $unreadMessagesCount = Message::where('recipient_id', Auth::id())
                    ->where('is_read', false)
                    ->count();

                $unreadMessages = Message::where('recipient_id', Auth::id())
                    ->where('is_read', false)
                    ->with('sender')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                // Report notifications
                $unreadReportNotificationsCount = \App\Models\ReportNotification::where('user_id', Auth::id())
                    ->where('is_read', false)
                    ->count();

                $unreadReportNotifications = \App\Models\ReportNotification::where('user_id', Auth::id())
                    ->where('is_read', false)
                    ->orderBy('generated_at', 'desc')
                    ->limit(5)
                    ->get();

                // Total notifications count
                $totalUnreadCount = $unreadMessagesCount + $unreadReportNotificationsCount;
            } else {
                $unreadMessagesCount = 0;
                $unreadMessages = collect();
                $unreadReportNotificationsCount = 0;
                $unreadReportNotifications = collect();
                $totalUnreadCount = 0;
            }

            $view->with([
                'unreadMessagesCount' => $unreadMessagesCount,
                'unreadMessages' => $unreadMessages,
                'unreadReportNotificationsCount' => $unreadReportNotificationsCount,
                'unreadReportNotifications' => $unreadReportNotifications,
                'totalUnreadCount' => $totalUnreadCount
            ]);
        });
    }
}
