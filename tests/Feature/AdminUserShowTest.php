<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\Memory;
use App\Models\Prayer;
use App\Models\PrayerType;
use App\Models\SharedDigest;
use App\Models\Topic;
use App\Models\Translation;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\UserRead;
use App\Models\Verse;
use App\Models\VerseComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_view_a_user_profile(): void
    {
        $viewer = User::factory()->create(['is_admin' => false]);
        $target = User::factory()->create();

        $response = $this->actingAs($viewer)->get(route('admin.users.show', $target));

        $response->assertRedirect(route('home.index'));
    }

    public function test_admin_sees_a_monthly_activity_breakdown_with_a_vs_last_month_comparison(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $lastMonthStart = now()->copy()->subMonths(1)->startOfMonth();
        $thisMonthStart = now()->copy()->startOfMonth();

        $target = User::factory()->create(['name' => 'Jamie Reader']);
        $target->forceFill(['created_at' => $lastMonthStart])->save();

        $book = Book::create(['name' => 'Genesis', 'abbr' => 'Gen', 'new_testament' => 0]);
        $chapter = Chapter::create(['book_id' => $book->id, 'number' => 1]);
        $translation = Translation::create(['name' => 'KJV']);
        $verse = Verse::create([
            'chapter_id' => $chapter->id,
            'translation_id' => $translation->id,
            'number' => 1,
            'reference' => 'Genesis 1:1',
            'text' => 'In the beginning...',
        ]);
        $prayerType = PrayerType::first();

        $lastMonthDate = $lastMonthStart->copy()->addDays(4);
        $thisMonthDate = $thisMonthStart->copy()->addDays(min(2, now()->day - 1));

        // Last month: 2 distinct chapters read, 3 prayers, 2 pieces of commentary, 2 topics, 1 completed memory, 1 digest.
        foreach ([1, 2] as $chapterNumber) {
            UserRead::create([
                'user_id' => $target->id,
                'book_id' => $book->id,
                'chapter_number' => $chapterNumber,
                'translation_id' => $translation->id,
                'read_at' => $lastMonthDate,
            ]);
        }
        for ($i = 0; $i < 3; $i++) {
            Prayer::create([
                'user_id' => $target->id,
                'date' => $lastMonthDate->toDateString(),
                'content' => 'Private prayer content from last month.',
                'prayer_type_id' => $prayerType->id,
                'created_at' => $lastMonthDate,
            ]);
        }
        ChapterComment::create([
            'user_id' => $target->id,
            'chapter_id' => $chapter->id,
            'comment' => 'Private chapter comment from last month.',
            'created_at' => $lastMonthDate,
        ]);
        VerseComment::create([
            'user_id' => $target->id,
            'verse_id' => $verse->id,
            'chapter_id' => $chapter->id,
            'verse_number' => 1,
            'comment' => 'Private verse comment from last month.',
            'created_at' => $lastMonthDate,
        ]);
        Topic::create(['user_id' => $target->id, 'name' => 'Faith', 'created_at' => $lastMonthDate]);
        Topic::create(['user_id' => $target->id, 'name' => 'Hope', 'created_at' => $lastMonthDate]);
        Memory::create([
            'user_id' => $target->id,
            'title' => 'Memorized last month',
            'start_date' => $lastMonthDate,
            'completed_at' => $lastMonthDate,
        ]);
        SharedDigest::create([
            'user_id' => $target->id,
            'uuid' => 'digest-last-month',
            'week_start' => $lastMonthDate->copy()->startOfWeek(),
            'week_end' => $lastMonthDate->copy()->endOfWeek(),
            'snapshot' => [],
            'created_at' => $lastMonthDate,
        ]);

        // This month: 5 distinct chapters read, 1 prayer, 3 pieces of commentary, 1 topic, 0 completed memories, 3 digests.
        foreach ([3, 4, 5, 6, 7] as $chapterNumber) {
            UserRead::create([
                'user_id' => $target->id,
                'book_id' => $book->id,
                'chapter_number' => $chapterNumber,
                'translation_id' => $translation->id,
                'read_at' => $thisMonthDate,
            ]);
        }
        Prayer::create([
            'user_id' => $target->id,
            'date' => $thisMonthDate->toDateString(),
            'content' => 'Private prayer content from this month.',
            'prayer_type_id' => $prayerType->id,
            'created_at' => $thisMonthDate,
        ]);
        ChapterComment::create([
            'user_id' => $target->id,
            'chapter_id' => $chapter->id,
            'comment' => 'Private chapter comment from this month.',
            'created_at' => $thisMonthDate,
        ]);
        ChapterComment::create([
            'user_id' => $target->id,
            'chapter_id' => $chapter->id,
            'comment' => 'Another private chapter comment from this month.',
            'created_at' => $thisMonthDate,
        ]);
        VerseComment::create([
            'user_id' => $target->id,
            'verse_id' => $verse->id,
            'chapter_id' => $chapter->id,
            'verse_number' => 1,
            'comment' => 'Private verse comment from this month.',
            'created_at' => $thisMonthDate,
        ]);
        Topic::create(['user_id' => $target->id, 'name' => 'Grace', 'created_at' => $thisMonthDate]);
        for ($i = 0; $i < 3; $i++) {
            SharedDigest::create([
                'user_id' => $target->id,
                'uuid' => "digest-this-month-{$i}",
                'week_start' => $thisMonthDate->copy()->startOfWeek(),
                'week_end' => $thisMonthDate->copy()->endOfWeek(),
                'snapshot' => [],
                'created_at' => $thisMonthDate,
            ]);
        }

        UserLogin::create(['user_id' => $target->id, 'logged_in_at' => $thisMonthDate]);

        $response = $this->actingAs($admin)->get(route('admin.users.show', $target));

        $response->assertStatus(200);
        $response->assertSee('Jamie Reader');

        // Both months are labeled and shown as separate rows.
        $response->assertSeeInOrder([
            $thisMonthStart->format('F Y'),
            $lastMonthStart->format('F Y'),
        ]);

        // This month's per-metric totals.
        $response->assertSee('5', false); // chapters read
        $response->assertSee('3', false); // commentary this month / digests this month

        // Last month's per-metric totals.
        $response->assertSee('2', false); // chapters read last month / topics last month

        // All-time totals (sum across both months): chapters 7, prayers 4, commentary 5, topics 3, memory 1, digests 4.
        $response->assertSee('All-Time');
        $response->assertSee('7', false);
        $response->assertSee('4', false);

        // No vs-last-month delta indicators — the numbers are already visible side by side.
        // (Deltas from the prior design would have been chapters +3, prayers -2, commentary +1, topics -1, memory -1, digests +2.)
        $this->assertStringNotContainsString('(+3)', $response->getContent());
        $this->assertStringNotContainsString('(-2)', $response->getContent());
        $this->assertStringNotContainsString('(+1)', $response->getContent());
        $this->assertStringNotContainsString('(-1)', $response->getContent());
        $this->assertStringNotContainsString('(+2)', $response->getContent());

        $response->assertDontSee('Private prayer content');
        $response->assertDontSee('Private chapter comment');
        $response->assertDontSee('Private verse comment');
    }

    public function test_monthly_rows_are_paginated_to_the_twelve_most_recent_with_older_months_on_page_two(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $target = User::factory()->create();
        $target->forceFill(['created_at' => now()->copy()->subMonths(12)->startOfMonth()])->save();

        $currentMonthLabel = now()->format('F Y');
        $oldestMonthLabel = now()->copy()->subMonths(12)->format('F Y');

        $pageOne = $this->actingAs($admin)->get(route('admin.users.show', $target));
        $pageOne->assertStatus(200);
        $pageOne->assertSee($currentMonthLabel);
        $pageOne->assertDontSee($oldestMonthLabel);
        $pageOne->assertSee('page=2', false);
        $pageOne->assertSee('All-Time');

        $pageTwo = $this->actingAs($admin)->get(route('admin.users.show', $target).'?page=2');
        $pageTwo->assertStatus(200);
        $pageTwo->assertSee($oldestMonthLabel);
        $pageTwo->assertDontSee($currentMonthLabel);
        $pageTwo->assertSee('All-Time');
    }
}
