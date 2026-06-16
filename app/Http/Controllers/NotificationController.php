<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    const PER_PAGE = 10;

    public function index(): View|JsonResponse
    {
        $offset = max(0, (int) request('offset', 0));

        if ($offset === 0) {
            UserNotification::unread()->update(['read_at' => now()]);
        }

        $total = UserNotification::count();
        $notifications = UserNotification::latest()->skip($offset)->take(self::PER_PAGE)->get();
        $hasMore = ($offset + self::PER_PAGE) < $total;

        if (request()->ajax()) {
            return response()->json([
                'notifications' => $notifications->map(fn($n) => [
                    'title'      => $n->title,
                    'message'    => $n->message,
                    'icon'       => $n->icon,
                    'icon_color' => $n->icon_color,
                    'url'        => $n->url,
                    'time'       => $n->created_at->diffForHumans(),
                ]),
                'hasMore'    => $hasMore,
                'nextOffset' => $offset + self::PER_PAGE,
            ]);
        }

        return view('notifications.index', compact('notifications', 'hasMore'));
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
