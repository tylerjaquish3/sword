<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\Prayer;
use App\Models\PrayerType;
use App\Models\Translation;
use App\Models\User;
use App\Models\UserVersePreference;
use App\Models\Verse;
use App\Models\VerseComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineBundleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_bundle_includes_all_translations_and_verse_text(): void
    {
        $user = User::factory()->create(['offline_enabled' => true]);
        $book = Book::create(['name' => 'John', 'abbr' => 'JHN', 'new_testament' => 1, 'sort_order' => 43]);
        $chapter = Chapter::create(['book_id' => $book->id, 'number' => 1]);
        $translation = Translation::create(['name' => 'KJV']);
        Verse::create(['chapter_id' => $chapter->id, 'translation_id' => $translation->id, 'number' => 1, 'reference' => 'John 1:1', 'text' => 'In the beginning.']);

        $response = $this->actingAs($user)->getJson(route('offline.bundle'));

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'KJV']);
        $response->assertJsonFragment(['text' => 'In the beginning.']);
        $responseData = $response->json();
        $this->assertGreaterThanOrEqual(1, count($responseData['translations']));
        $this->assertGreaterThanOrEqual(1, count($responseData['verses']));
    }

    public function test_bundle_only_includes_the_authenticated_users_own_content(): void
    {
        $me = User::factory()->create(['offline_enabled' => true]);
        $other = User::factory()->create();
        $book = Book::create(['name' => 'John', 'abbr' => 'JHN', 'new_testament' => 1, 'sort_order' => 43]);
        $chapter = Chapter::create(['book_id' => $book->id, 'number' => 1]);

        $this->actingAs($me)->postJson(route('commentary.store'), [
            'chapter_id' => $chapter->id, 'type' => 'chapter', 'comment' => 'Mine',
        ]);
        $this->actingAs($other)->postJson(route('commentary.store'), [
            'chapter_id' => $chapter->id, 'type' => 'chapter', 'comment' => 'Not mine',
        ]);
        Prayer::create(['user_id' => $other->id, 'date' => '2026-10-01', 'content' => 'Not mine', 'prayer_type_id' => PrayerType::create(['name' => 'X'])->id]);
        UserVersePreference::create(['user_id' => $other->id, 'chapter_id' => $chapter->id, 'verse_number' => 1, 'highlight_color' => 'red']);

        $response = $this->actingAs($me)->getJson(route('offline.bundle'));

        $response->assertOk();
        $response->assertJsonCount(1, 'chapterComments');
        $response->assertJsonFragment(['comment' => 'Mine']);
        $response->assertJsonMissing(['comment' => 'Not mine']);
        $response->assertJsonCount(0, 'prayers');
        $response->assertJsonCount(0, 'highlights');
    }

    public function test_bundle_requires_authentication(): void
    {
        $response = $this->getJson(route('offline.bundle'));

        $response->assertStatus(401);
    }

    public function test_bundle_is_forbidden_when_offline_mode_is_not_enabled(): void
    {
        $user = User::factory()->create(['offline_enabled' => false]);

        $response = $this->actingAs($user)->getJson(route('offline.bundle'));

        $response->assertStatus(403);
    }
}
