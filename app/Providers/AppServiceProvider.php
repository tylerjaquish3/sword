<?php

namespace App\Providers;

use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('base.navbar', function ($view) {
            $count = Auth::check()
                ? UserNotification::unread()->count()
                : 0;

            $view->with('unreadNotificationCount', $count);
        });
    }
}
