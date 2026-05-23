<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\GrokAIService::class);
    }

    public function boot(): void
    {
        View::composer(['layouts.app','layouts.admin'], function ($view) {
            $notifications = [];
            $unread = 0;
            if (Auth::check()) {
                $notifications = NotificationLog::where('user_id', Auth::id())
                    ->latest()->limit(8)->get();
                $unread = NotificationLog::where('user_id', Auth::id())->whereNull('read_at')->count();
            }
            $view->with(compact('notifications','unread'));
        });
    }
}
