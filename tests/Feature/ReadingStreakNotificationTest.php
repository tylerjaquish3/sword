<?php

namespace Tests\Feature;

use App\Jobs\GenerateNotifications;
use App\Models\Book;
use App\Models\Translation;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserRead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingStreakNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_milestone_does_not_refire_on_a_later_run_of_the_same_unbroken_streak(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $book = Book::create(['name' => 'Genesis', 'abbr' => 'Gen', 'new_testament' => 0]);
        $translation = Translation::create(['name' => 'KJV']);

        $chapterNumber = 1;
        $record = function () use (&$chapterNumber, $user, $book, $translation) {
            return fn ($date) => UserRead::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'chapter_number' => $chapterNumber++,
                'translation_id' => $translation->id,
                'read_at' => $date,
            ]);
        };
        $recordRead = $record();

        // First run: the streak has just hit exactly 100 days (today not yet read).
        $this->travelTo(now()->parse('2026-08-11'));
        for ($i = 1; $i <= 100; $i++) {
            $recordRead(now()->subDays($i));
        }

        GenerateNotifications::dispatchSync($user->id);

        $this->assertSame(
            1,
            UserNotification::withoutGlobalScopes()->where('user_id', $user->id)->where('type', 'reading_streak')->count(),
            'The 100-day milestone should notify once when first reached.'
        );

        // The user keeps reading every day for 51 more days without a gap, so by
        // 2026-10-01 the same unbroken streak is 151 days (still under the 200 milestone).
        for ($day = now()->copy(); $day->lt(now()->parse('2026-10-01')); $day->addDay()) {
            $recordRead($day->copy());
        }
        $this->travelTo(now()->parse('2026-10-01'));

        GenerateNotifications::dispatchSync($user->id);

        $streakNotificationCount = UserNotification::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('type', 'reading_streak')
            ->count();

        $this->assertSame(
            1,
            $streakNotificationCount,
            'A milestone already reached earlier in an unbroken streak should not re-fire just because a later cron run falls in a new calendar month.'
        );
    }
}
