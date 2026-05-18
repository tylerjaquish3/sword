<?php

namespace App\Jobs;

use App\Models\Memory;
use App\Models\Prayer;
use App\Models\SharedDigest;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserRead;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GenerateNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly ?int $userId = null)
    {
    }

    public function handle(): void
    {
        $users = $this->userId
            ? User::where('id', $this->userId)->get()
            : User::where('is_active', true)->get();

        foreach ($users as $user) {
            $this->generateForUser($user);
        }
    }

    private function generateForUser(User $user): void
    {
        $this->checkReadingStreak($user);
        $this->checkDefaultTranslation($user);
        $this->checkPrayerReminder($user);
        $this->checkExpiringMemoryVerses($user);
        $this->checkDigestReminder($user);
    }

    private function checkReadingStreak(User $user): void
    {
        $allReadDates = UserRead::where('user_id', $user->id)
            ->selectRaw('DATE(read_at) as date')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->startOfDay());

        $streak = 0;
        $checkDate = now()->startOfDay();
        // If today hasn't been read yet, the streak should start from yesterday
        // so a user who read the prior N days still sees their N-day streak.
        if (!$allReadDates->contains(fn($d) => $d->eq($checkDate))) {
            $checkDate->subDay();
        }
        while ($allReadDates->contains(fn($d) => $d->eq($checkDate))) {
            $streak++;
            $checkDate->subDay();
        }

        $milestones = [
            7  => ['title' => '7-day reading streak!', 'message' => "You've read the Bible 7 days in a row. Keep it up!", 'icon' => 'mdi-fire', 'icon_color' => 'bg-warning'],
            14 => ['title' => '2-week reading streak!', 'message' => "Fourteen days straight — your consistency is inspiring.", 'icon' => 'mdi-fire', 'icon_color' => 'bg-warning'],
            30 => ['title' => '30-day reading streak!', 'message' => "A full month of daily reading. That's remarkable dedication.", 'icon' => 'mdi-trophy', 'icon_color' => 'bg-success'],
            100 => ['title' => '100-day reading streak!', 'message' => "100 consecutive days in the Word. Truly exceptional.", 'icon' => 'mdi-trophy', 'icon_color' => 'bg-success'],
        ];

        $monthKey = now()->format('Y-m');

        foreach ($milestones as $days => $meta) {
            if ($streak >= $days) {
                $key = "reading_streak_{$days}_{$monthKey}";
                $this->createIfNotExists($user->id, 'reading_streak', $key, $meta['title'], $meta['message'], $meta['icon'], $meta['icon_color'], route('translations.index'));
            }
        }
    }

    private function checkDefaultTranslation(User $user): void
    {
        if (is_null($user->default_translation_id)) {
            $this->createIfNotExists(
                $user->id,
                'profile_incomplete',
                'profile_no_translation',
                'Set a default Bible translation',
                "You haven't chosen a default translation yet. Set one in your profile to personalize your reading experience.",
                'mdi-book-open-variant',
                'bg-info',
                route('profile.index')
            );
        } else {
            // Auto-dismiss this notification once the user has set a translation
            DB::table('user_notifications')
                ->where('user_id', $user->id)
                ->where('unique_key', 'profile_no_translation')
                ->delete();
        }
    }

    private function checkPrayerReminder(User $user): void
    {
        // Give the week a chance to start — only nudge from Wednesday onward.
        if (now()->dayOfWeekIso < 3) {
            return;
        }

        $hasPrayerHistory = Prayer::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasPrayerHistory) {
            return;
        }

        $thisWeekPrayers = Prayer::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfWeek())
            ->exists();

        if (!$thisWeekPrayers) {
            $weekKey = 'prayer_reminder_' . now()->format('Y_W');
            $this->createIfNotExists(
                $user->id,
                'prayer_reminder',
                $weekKey,
                'No prayers logged this week',
                "You haven't recorded any prayers this week. Take a moment to write down what's on your heart.",
                'mdi-hands-pray',
                'bg-primary',
                route('prayers.index')
            );
        }
    }

    private function checkExpiringMemoryVerses(User $user): void
    {
        $expiringSoon = Memory::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->whereNull('completed_at')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays(3))
            ->where('end_date', '>=', now())
            ->get();

        foreach ($expiringSoon as $memory) {
            $daysLeft = (int) now()->diffInDays($memory->end_date, false);
            $daysText = $daysLeft === 0 ? 'today' : ($daysLeft === 1 ? 'tomorrow' : "in {$daysLeft} days");

            $this->createIfNotExists(
                $user->id,
                'memory_expiring',
                "memory_expiring_{$memory->id}",
                "Memory verse \"{$memory->title}\" ends {$daysText}",
                "Your memory verse set \"{$memory->title}\" is ending {$daysText}. Mark it complete or extend the end date.",
                'mdi-brain',
                'bg-warning',
                route('memory.index')
            );
        }
    }

    private function checkDigestReminder(User $user): void
    {
        if (!now()->isSaturday()) return;

        $weekStart = now()->startOfWeek()->toDateString();

        $alreadySaved = SharedDigest::where('user_id', $user->id)
            ->where('week_start', $weekStart)
            ->exists();

        if ($alreadySaved) return;

        $uniqueKey = 'digest_reminder_' . now()->format('Y_W');
        $this->createIfNotExists(
            $user->id,
            'digest_reminder',
            $uniqueKey,
            'Weekly Digest Ready',
            "Take a few minutes to reflect on this week — your digest is waiting to be filled out.",
            'mdi-book-open-page-variant',
            'bg-info',
            '/digest/complete'
        );
    }

    private function createIfNotExists(
        int $userId,
        string $type,
        string $uniqueKey,
        string $title,
        string $message,
        string $icon,
        string $iconColor,
        ?string $url = null
    ): void {
        UserNotification::withoutGlobalScopes()->firstOrCreate(
            ['user_id' => $userId, 'unique_key' => $uniqueKey],
            compact('type', 'title', 'message', 'icon', 'url') + ['icon_color' => $iconColor]
        );
    }

    /**
     * Broadcast a one-off announcement to all active users (no deduplication key).
     * Call via: GenerateNotifications::announce('Title', 'Message')
     */
    public static function announce(string $title, string $message, ?string $url = null): void
    {
        User::where('is_active', true)->each(function (User $user) use ($title, $message, $url) {
            UserNotification::withoutGlobalScopes()->create([
                'user_id'    => $user->id,
                'type'       => 'app_update',
                'title'      => $title,
                'message'    => $message,
                'icon'       => 'mdi-bullhorn',
                'icon_color' => 'bg-success',
                'url'        => $url,
                'unique_key' => null,
            ]);
        });
    }
}
