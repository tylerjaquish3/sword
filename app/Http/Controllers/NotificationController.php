<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        UserNotification::unread()->update(['read_at' => now()]);

        $notifications = UserNotification::latest()->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(UserNotification $notification): RedirectResponse
    {
        $notification->markAsRead();

        $redirect = $notification->url ?? route('home.index');

        return redirect($redirect);
    }

    public function markAllRead(): RedirectResponse
    {
        UserNotification::unread()->update(['read_at' => now()]);

        return back();
    }
}
