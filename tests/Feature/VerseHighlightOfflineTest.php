<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Translation;
use App\Models\User;
use App\Models\UserVersePreference;
use App\Models\Verse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerseHighlightOfflineTest extends TestCase
{
    use RefreshDatabase;

    private function makeChapter(): Chapter
    {
        $book = Book::create(['name' => 'John', 'abbr' => 'JHN', 'new_testament' => 1, 'sort_order' => 43]);

        return Chapter::create(['book_id' => $book->id, 'number' => 1]);
    }

    public function test_chapter_id_and_verse_number_can_be_used_instead_of_verse_id(): void
    {
        $user = User::factory()->create();
        $chapter = $this->makeChapter();

        $response = $this->actingAs($user)->postJson(route('verse-highlights.toggle'), [
            'chapter_id' => $chapter->id,
            'verse_number' => 3,
            'color' => 'yellow',
        ]);

        $response->assertOk()->assertJson(['color' => 'yellow']);
        $this->assertDatabaseHas('user_verse_preferences', [
            'user_id' => $user->id,
            'chapter_id' => $chapter->id,
            'verse_number' => 3,
            'highlight_color' => 'yellow',
        ]);
    }

    public function test_explicit_mode_sets_the_given_color_regardless_of_current_state(): void
    {
        $user = User::factory()->create();
        $chapter = $this->makeChapter();
        UserVersePreference::create([
            'user_id' => $user->id, 'chapter_id' => $chapter->id, 'verse_number' => 5, 'highlight_color' => 'yellow',
        ]);

        // Same color, explicit mode — must NOT toggle off (unlike the normal click-to-toggle behavior).
        $response = $this->actingAs($user)->postJson(route('verse-highlights.toggle'), [
            'chapter_id' => $chapter->id,
            'verse_number' => 5,
            'color' => 'yellow',
            'explicit' => true,
        ]);

        $response->assertOk()->assertJson(['color' => 'yellow']);
        $this->assertDatabaseHas('user_verse_preferences', [
            'user_id' => $user->id, 'chapter_id' => $chapter->id, 'verse_number' => 5, 'highlight_color' => 'yellow',
        ]);
    }

    public function test_explicit_mode_can_clear_a_highlight_with_a_null_color(): void
    {
        $user = User::factory()->create();
        $chapter = $this->makeChapter();
        UserVersePreference::create([
            'user_id' => $user->id, 'chapter_id' => $chapter->id, 'verse_number' => 5, 'highlight_color' => 'yellow',
        ]);

        $response = $this->actingAs($user)->postJson(route('verse-highlights.toggle'), [
            'chapter_id' => $chapter->id,
            'verse_number' => 5,
            'explicit' => true,
        ]);

        $response->assertOk()->assertJson(['color' => null]);
        $this->assertDatabaseHas('user_verse_preferences', [
            'user_id' => $user->id, 'chapter_id' => $chapter->id, 'verse_number' => 5, 'highlight_color' => null,
        ]);
    }

    public function test_missing_both_verse_id_and_chapter_verse_number_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('verse-highlights.toggle'), ['color' => 'yellow']);

        $response->assertStatus(422);
    }

    public function test_existing_verse_id_toggle_behavior_is_unchanged(): void
    {
        $user = User::factory()->create();
        $book = Book::create(['name' => 'John', 'abbr' => 'JHN', 'new_testament' => 1, 'sort_order' => 43]);
        $chapter = Chapter::create(['book_id' => $book->id, 'number' => 1]);
        $translation = Translation::create(['name' => 'KJV']);
        $verse = Verse::create(['chapter_id' => $chapter->id, 'translation_id' => $translation->id, 'number' => 1, 'reference' => 'John 1:1', 'text' => 'Text']);

        $first = $this->actingAs($user)->postJson(route('verse-highlights.toggle'), [
            'verse_id' => $verse->id,
            'color' => 'blue',
        ]);
        $first->assertJson(['color' => 'blue']);

        // Clicking the same color again toggles it off, same as before this change.
        $second = $this->actingAs($user)->postJson(route('verse-highlights.toggle'), [
            'verse_id' => $verse->id,
            'color' => 'blue',
        ]);
        $second->assertJson(['color' => null]);
    }
}
