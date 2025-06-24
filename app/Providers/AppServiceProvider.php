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
                $unreadMessagesCount = Message::where('recipient_id', Auth::id())
                    ->where('is_read', false)
                    ->count();

                $unreadMessages = Message::where('recipient_id', Auth::id())
                    ->where('is_read', false)
                    ->with('sender')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            } else {
                $unreadMessagesCount = 0;
                $unreadMessages = collect();
            }

            $view->with([
                'unreadMessagesCount' => $unreadMessagesCount,
                'unreadMessages' => $unreadMessages
            ]);
        });
    }
}
